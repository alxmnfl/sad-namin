<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
  header("Location: index.php");
  exit;
}

// If POSTed from pos.php, process & insert transaction + items
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transaction_data'])) {
    $transaction_json = $_POST['transaction_data'];
    $data = json_decode($transaction_json, true);

    if ($data && isset($data['total']) && isset($data['items']) && is_array($data['items'])) {
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $_SESSION['username'];
        $total_amount = floatval($data['total']);
        $source = 'pos';

        // Start database transaction
        $conn->begin_transaction();

        try {
            // Insert into transactions table
            $stmt = $conn->prepare("INSERT INTO transactions (user_id, total_amount, transaction_date, source) VALUES (?, ?, NOW(), ?)");
            $stmt->bind_param("sds", $user_id, $total_amount, $source);
            $stmt->execute();
            $transaction_id = $stmt->insert_id;
            $stmt->close();

            $item_stmt = $conn->prepare("INSERT INTO transaction_items (transaction_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stock_stmt = $conn->prepare("UPDATE products_ko SET stock = stock - ? WHERE id = ? AND stock >= ?");

            foreach ($data['items'] as $it) {
                $product_id = intval($it['id']);
                $qty = intval($it['qty']);
                $price = floatval($it['price']);

                // Insert each item
                $item_stmt->bind_param("iiid", $transaction_id, $product_id, $qty, $price);
                $item_stmt->execute();

                // Decrease stock safely (only if enough stock)
                $stock_stmt->bind_param("iii", $qty, $product_id, $qty);
                $stock_stmt->execute();

                if ($stock_stmt->affected_rows === 0) {
                    // Rollback if stock insufficient
                    throw new Exception("Insufficient stock for product ID: $product_id");
                }
            }

            $item_stmt->close();
            $stock_stmt->close();

            $conn->commit();

            header("Location: onsite_transaction.php?inserted=1");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            die("Transaction failed: " . htmlspecialchars($e->getMessage()));
        }
    } else {
        die("Invalid transaction data.");
    }
}

// Display transactions (onsite/pos)
$records_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $records_per_page;

// Count total transactions
$total_result = $conn->query("SELECT COUNT(*) AS total FROM transactions WHERE source = 'pos'");
$total_records = ($row = $total_result->fetch_assoc()) ? $row['total'] : 0;
$total_pages = max(1, ceil($total_records / $records_per_page));

// Get paginated transactions
$transactions_query = $conn->prepare("SELECT * FROM transactions WHERE source = 'pos' ORDER BY transaction_date DESC LIMIT ? OFFSET ?");
$transactions_query->bind_param("ii", $records_per_page, $offset);
$transactions_query->execute();
$transactions_result = $transactions_query->get_result();

// Monthly sales summary
$monthly_sales_query = "
  SELECT DATE_FORMAT(transaction_date, '%Y-%m') AS month, SUM(total_amount) AS total_sales
  FROM transactions
  WHERE source = 'pos'
  GROUP BY month
  ORDER BY month ASC
";
$monthly_sales_result = $conn->query($monthly_sales_query);

$months = [];
$sales = [];
$total_revenue = 0;
while ($row = $monthly_sales_result->fetch_assoc()) {
  $months[] = $row['month'];
  $sales[] = $row['total_sales'];
  $total_revenue += $row['total_sales'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Onsite Transactions</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="ot.css">
</head>
<body>

<div class="container" style="display:flex; gap:25px; align-items:flex-start;">

    <!-- LEFT SIDE: TRANSACTION TABLE -->
    <div style="flex:2;">
        <a href="pos.php" class="back-btn">⬅ Back</a>
        <h2>Onsite POS Transactions</h2>

        <?php if (isset($_GET['inserted'])): ?>
          <div style="padding:10px;background:#e6ffea;border:1px solid #b7f0c6;margin:10px 0;border-radius:6px;">
            Transaction saved successfully.
          </div>
        <?php endif; ?>

        <table>
          <thead>
            <tr>
              <th>Transaction ID</th>
              <th>User</th>
              <th>Total Amount (₱)</th>
              <th>Date</th>
              <th>Items</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($t = $transactions_result->fetch_assoc()) { ?>
              <?php
                $items = [];
                $item_query = $conn->prepare("
                    SELECT ti.quantity, ti.price, p.name, p.category
                    FROM transaction_items ti
                    LEFT JOIN products_ko p ON ti.product_id = p.id
                    WHERE ti.transaction_id = ?
                ");
                $item_query->bind_param("i", $t['transaction_id']);
                $item_query->execute();
                $res = $item_query->get_result();
                while($i = $res->fetch_assoc()){
                  $items[] = [
                    "name" => $i['name'],
                    "category" => $i['category'],
                    "quantity" => $i['quantity'],
                    "price" => $i['price']
                  ];
                }
                $item_query->close();
              ?>
              <tr>
                <td><?= htmlspecialchars($t['transaction_id']) ?></td>
                <td><?= htmlspecialchars($t['user_id']) ?></td>
                <td>₱<?= number_format($t['total_amount'],2) ?></td>
                <td><?= htmlspecialchars($t['transaction_date']) ?></td>
                <td><button class="view-items" data-items='<?= json_encode($items) ?>'>View Items</button></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>

        <div style="margin-top:12px;">
          <?php if ($current_page > 1): ?>
            <a href="?page=<?= $current_page - 1 ?>" class="back-btn" style="background:#004080;margin-right:8px;">← Previous</a>
          <?php endif; ?>
          Page <?= $current_page ?> of <?= $total_pages ?> (Total: <?= $total_records ?> records)
          <?php if ($current_page < $total_pages): ?>
            <a href="?page=<?= $current_page + 1 ?>" class="back-btn" style="background:#004080;margin-left:8px;">Next →</a>
          <?php endif; ?>
        </div>
    </div>

    <!-- RIGHT SIDE: SALES CHART -->
    <div style="flex:1; background:#fff; padding:20px; border-radius:12px; box-shadow:0 3px 8px rgba(0,0,0,0.08);">
        <h3>Sales Summary (Onsite)</h3>
        <p>Total Revenue: <strong>₱<?= number_format($total_revenue,2) ?></strong></p>
        <canvas id="salesChart" style="width:100%; height:260px;"></canvas>
    </div>

</div>

<!-- MODAL -->
<div id="itemsModal" class="modal-overlay">
  <div class="modal-box">
    <h3>Purchased Items</h3>
    <table style="width:100%; border-collapse:collapse;">
      <thead><tr><th>Product</th><th>Category</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
      <tbody id="modalItemsBody"></tbody>
    </table>
    <button class="close-btn" onclick="closeModal()">Close</button>
  </div>
</div>

<script>
document.querySelectorAll(".view-items").forEach(btn => {
  btn.addEventListener("click", function() {
    let items = JSON.parse(this.dataset.items);
    let body = document.getElementById("modalItemsBody");
    body.innerHTML = "";
    items.forEach(it => {
      body.innerHTML += `
        <tr>
          <td>${it.name}</td>
          <td>${it.category || '-'}</td>
          <td>${it.quantity}</td>
          <td>₱${parseFloat(it.price).toFixed(2)}</td>
          <td>₱${(it.quantity * it.price).toFixed(2)}</td>
        </tr>`;
    });
    document.getElementById("itemsModal").style.display = "flex";
  });
});
function closeModal(){ document.getElementById("itemsModal").style.display = "none"; }

new Chart(document.getElementById('salesChart'), {
  type: 'bar',
  data: { 
    labels: <?= json_encode($months) ?>, 
    datasets: [{
      label: 'Monthly Sales (₱)',
      data: <?= json_encode($sales) ?>,
      backgroundColor: 'rgba(0, 64, 128, 0.7)',
      borderColor: '#003060',
      borderWidth: 1,
      borderRadius: 8
    }]
  },
  options: { scales: { y: { beginAtZero: true } } }
});
</script>

</body>
</html>
