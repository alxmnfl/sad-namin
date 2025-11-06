<?php
session_start();


if (!isset($_SESSION['username']) ||  !in_array($_SESSION['role'], ['staff', 'admin'])) {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "hardware_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$products = $conn->query("SELECT * FROM products ORDER BY category, name");
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
  </div>
</nav>


<div class="container">
  <?php if ($products && $products->num_rows > 0): ?>
    <?php while ($p = $products->fetch_assoc()): ?>
      <div class="product-card" 
           data-id="<?= $p['id'] ?>" 
           data-name="<?= htmlspecialchars($p['name']) ?>" 
           data-category="<?= htmlspecialchars($p['category']) ?>" 
           data-price="<?= $p['price'] ?>">
        <img src="<?= htmlspecialchars($p['image']) ?>" alt="Product Image">
        <h4><?= htmlspecialchars($p['name']) ?></h4>
        <p><?= htmlspecialchars($p['category']) ?></p>
        <p><strong>₱<?= number_format($p['price'], 2) ?></strong></p>
        <?php if ($p['stock'] > 0): ?>
    <button type="submit" class="add-cart-btn" name="add_to_cart">Buy</button>
  <?php else: ?>
    <button type="button" class="add-cart-btn" disabled style="background-color: #999; cursor: not-allowed;">Out of Stock</button>
  <?php endif; ?>
</form>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p style="padding: 20px;">No products available.</p>
  <?php endif; ?>
</div>

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
  <button class="checkout-btn" onclick="checkout()">Checkout</button>
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
          <button onclick="changeQty(${id}, -1)">-</button>
          <span>${item.qty}</span>
          <button onclick="changeQty(${id}, 1)">+</button>
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

document.querySelectorAll('.product-card button').forEach(btn => {
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
  alert('Transaction recorded successfully!');
  cart = {};
  updateCartDisplay();
}
</script>

</body>
</html>
