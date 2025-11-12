<?php
session_start();

if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['staff', 'admin'])) {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "hardware_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$selected_category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
if (!empty($selected_category)) {
    $products = $conn->query("SELECT * FROM products_ko WHERE archive = 0 AND category = '$selected_category' ORDER BY id DESC");
} else {
    $products = $conn->query("SELECT * FROM products_ko WHERE archive = 0 ORDER BY id DESC");
}
$categories = $conn->query("SELECT DISTINCT category FROM products_ko WHERE category IS NOT NULL AND category != ''");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Point of Sale (POS) - Abeth Hardware</title>
<link rel="stylesheet" href="pos.css">
</head>
<body>

<nav>
  <div class="logo">Abeth Hardware POS</div>
  <div class="nav-buttons">
    <a href="admin.php"><button class="back-btn">⬅ Back to Dashboard</button></a>
    <a href="onsite_transaction.php"><button class="onsite-btn">Onsite Transactions</button></a>
    <button id="toggleCartBtn" class="floating-cart-btn">🛒</button>
  </div>
</nav>

<!-- ✅ Category Filter -->
<form method="GET" style="margin-bottom: 20px;">
  <label for="category"><strong>Filter by Category:</strong></label>
  <select name="category" id="category" onchange="this.form.submit()" style="padding: 8px; margin-left: 10px;">
    <option value="">All</option>
    <?php if ($categories && $categories->num_rows > 0): ?>
      <?php while ($cat = $categories->fetch_assoc()): ?>
        <option value="<?= htmlspecialchars($cat['category']) ?>" 
          <?= ($selected_category === $cat['category']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($cat['category']) ?>
        </option>
      <?php endwhile; ?>
    <?php endif; ?>
  </select>
</form>

<div class="product-grid">
  <?php if ($products && $products->num_rows > 0): ?>
    <?php while ($p = $products->fetch_assoc()): ?>
      <!-- ✅ Added data attributes -->
      <div class="product-card"
           data-id="<?= $p['id'] ?>"
           data-name="<?= htmlspecialchars($p['name']) ?>"
           data-category="<?= htmlspecialchars($p['category']) ?>"
           data-price="<?= $p['price'] ?>">
        <?php if (!empty($p['image'])): ?>
          <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
        <?php else: ?>
          <div class="no-image">No Image</div>
        <?php endif; ?>

        <div class="product-footer">
          <h4><?= htmlspecialchars($p['name']) ?></h4>
          <?= htmlspecialchars($p['category']) ?>
          <p><strong>₱<?= number_format($p['price'], 2) ?></strong></p>

          <?php if ($p['stock'] > 0): ?>
            <button type="button" class="add-cart-btn">Add</button>
          <?php else: ?>
            <button type="button" class="add-cart-btn" disabled style="background-color: #999; cursor: not-allowed;">Out of Stock</button>
          <?php endif; ?>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No products available.</p>
  <?php endif; ?>
</div>

<!-- 🛒 Floating Cart -->
<div class="cart-container">
  <h3>🛒 Transaction</h3>
  <table class="cart-table" id="cartTable">
    <thead>
      <tr>
        <th>Item</th>
        <th>Qty</th>
        <th>Price</th>
      </tr>
    </thead>
    <tbody id="cartBody"></tbody>
  </table>

  <div class="total" id="cartTotal">Total: ₱0.00</div>
<button type="button" class="checkout-btn" onclick="checkout()">Checkout</button>
</div>

<script>
let cart = {};

function updateCartDisplay() {
  const tbody = document.getElementById('cartBody');
  tbody.innerHTML = '';
  let total = 0;

  for (const id in cart) {
    const item = cart[id];
    total += item.price * item.qty;
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        ${item.name}<br>
        <small style="color:gray;">${item.category}</small>
      </td>
      <td>
        <div class="qty-controls">
<button onclick="changeQty('${id}', -1)">-</button>
<span>${item.qty}</span>
<button onclick="changeQty('${id}', 1)">+</button>
        </div>
      </td>
      <td>₱${(item.price * item.qty).toFixed(2)}</td>
    `;
    tbody.appendChild(tr);
  }

  document.getElementById('cartTotal').textContent = `Total: ₱${total.toFixed(2)}`;
}

function changeQty(id, delta) {
  if (!cart[id]) return;
  cart[id].qty += delta;
  if (cart[id].qty <= 0) delete cart[id];
  updateCartDisplay();
}

document.querySelectorAll('.add-cart-btn').forEach(btn => {
  btn.addEventListener('click', e => {
    const card = e.target.closest('.product-card');
    const id = card.dataset.id;
    const name = card.dataset.name;
    const category = card.dataset.category;
    const price = parseFloat(card.dataset.price);

    if (!cart[id]) {
      cart[id] = { name, category, price, qty: 1 };
    } else {
      cart[id].qty++;
    }

    updateCartDisplay();
  });
});

function checkout() {
  if (Object.keys(cart).length === 0) {
    alert('No items in cart!');
    return;
  }

  const items = [];
  let total = 0;

  for (const id in cart) {
    total += cart[id].price * cart[id].qty;
    items.push({
      id: id,
      name: cart[id].name,
      qty: cart[id].qty,
      price: cart[id].price
    });
  }

  // Package data for POST
  const transaction = JSON.stringify({
    total: total,
    items: items
  });

  document.getElementById('transactionData').value = transaction;
  document.getElementById('checkoutForm').submit();
}
</script>


<form id="checkoutForm" action="onsite_transaction.php" method="POST" style="display:none;">
  <input type="hidden" name="transaction_data" id="transactionData">
</form>

</body>
</html>
