<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "hardware_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// FIXED: renamed query result variable to $result
$result = $conn->query("SELECT * FROM transactions WHERE source = 'pos' ORDER BY transaction_date DESC");
?>
<!DOCTYPE html>
<html>
<head>
<title>Onsite Transactions</title>

<style>
  body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background-color: #f9f9f9;
  }
  h2 { color: #333; }
  table { width: 100%; border-collapse: collapse; margin-top: 20px; }
  th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
  th { background-color: #eee; }
  .back-btn {
    background-color: #007BFF;
    color: white;
    border: none;
    padding: 10px 15px;
    cursor: pointer;
    text-decoration: none;
    margin-bottom: 20px;
    display: inline-block;
  }
  .back-btn:hover { background-color: #0056b3; }
</style>

<script>
function toggleItems(id) {
  let row = document.getElementById(id);
  row.style.display = (row.style.display === "none") ? "table-row" : "none";
}
</script>

</head>
<body>

<h2>Onsite POS Transactions</h2>
<a href="transactions.php"><button class="back-btn">⬅ Back to All Transactions</button></a>

<table>
  <thead>
    <tr>
      <th>Transaction ID</th>
      <th>User ID</th>
      <th>Total Amount (₱)</th>
      <th>Date</th>
      <th>Items</th>
    </tr>
  </thead>
  <tbody>

    <?php while ($transaction = mysqli_fetch_assoc($result)) { ?>
      <tr>
        <td><?= $transaction['transaction_id'] ?></td>
        <td><?= $transaction['user_id'] ?></td>
        <td>₱<?= number_format($transaction['total_amount'], 2) ?></td>
        <td><?= $transaction['transaction_date'] ?></td>
        <td>
          <button onclick="toggleItems('items<?= $transaction['transaction_id'] ?>')" style="padding:5px 10px; cursor:pointer; background:#004080; color:white; border:none; border-radius:4px;">
            View Items
          </button>
        </td>
      </tr>

      <tr id="items<?= $transaction['transaction_id'] ?>" style="display:none; background:#f9f9f9;">
        <td colspan="5">
          <strong>Purchased Items:</strong>
          <table style="width:100%; margin-top:10px; border-collapse: collapse;">
            <tr style="background:#ddd;">
              <th>Product</th>
              <th>Qty</th>
              <th>Price</th>
              <th>Subtotal</th>
            </tr>

            <?php
              $tid = $transaction['transaction_id'];
              $items_query = "
                SELECT ti.quantity, ti.price, p.name
                FROM transaction_items ti
                JOIN products_ko p ON ti.product_id = p.id
                WHERE ti.transaction_id = '$tid'
              ";
              $items_result = mysqli_query($conn, $items_query);

              while ($item = mysqli_fetch_assoc($items_result)) {
            ?>
              <tr>
                <td><?= $item['name'] ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>₱<?= number_format($item['price'], 2) ?></td>
                <td>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
              </tr>
            <?php } ?>
          </table>
        </td>
      </tr>
    <?php } ?>

  </tbody>
</table>

</body>
</html>
