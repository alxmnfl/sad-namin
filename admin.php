<?php
session_start();

// Redirect if not admin
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "hardware_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* ---------------- ADD PRODUCT ---------------- */
if (isset($_POST['add_product'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $category = $conn->real_escape_string($_POST['category']); 
    $desc = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);

    $image = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $img_name = basename($_FILES['image']['name']);
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir);
        $target_file = $target_dir . time() . "_" . $img_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image = $target_file;
        }
    }

    $conn->query("INSERT INTO products (name, category, description, price, stock, image)
                  VALUES ('$name','$category','$desc','$price','$stock','$image')");
    
    header("Location: admin.php");
    exit();
}

/* ---------------- DELETE PRODUCT ---------------- */
if (isset($_POST['delete_product'])) {
    $pid = intval($_POST['product_id']);
    $result = $conn->query("SELECT image FROM products WHERE id = $pid");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $imagePath = $row['image'];
        if (!empty($imagePath)) {
            $fullPath = __DIR__ . '/' . $imagePath;
            if (file_exists($fullPath)) unlink($fullPath);
        }
        $conn->query("DELETE FROM products WHERE id = $pid");
    }
    
    header("Location: admin.php");
    exit();
}

/* ---------------- UPDATE STOCK ---------------- */
if (isset($_POST['update_stock'])) {
    $pid = intval($_POST['product_id']);
    $new_stock = intval($_POST['new_stock']);
    $conn->query("UPDATE products SET stock = stock + $new_stock WHERE id = $pid");
    
    header("Location: admin.php");
    exit();
}

/* ---------------- FETCH PRODUCTS ---------------- */
$products = $conn->query("SELECT * FROM products");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Abeth Hardware</title>
<link rel="stylesheet" href="admin.css?v=<?= time() ?>">
</head>
<body>


<!-- NAVIGATION BAR -->
<nav>
  <div class="logo">Admin Dashboard</div>

  <div class="menu">
    <a href="customer_orders.php">Sales</a>
    <a href="orders.php">Orders</a>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>
</nav>


<script>
function toggleMenu() {
  document.getElementById("navMenu").classList.toggle("active");
}

// Modal Functions
function openAddProductModal() {
  document.getElementById("addProductModal").style.display = "block";
}

function closeAddProductModal() {
  document.getElementById("addProductModal").style.display = "none";
}
</script>

<!-- PRODUCT MANAGEMENT -->
<div class="admin-panel" id="products">


  <h3 style="margin-top:0;">Manage Products</h3>

  <div class="manage-products-header">
    <input type="text" id="searchInput" placeholder="Search by name, category, price..." class="search-bar">
    <button onclick="openAddProductModal()" class="add-product-btn">+ Add Product</button>
  </div>
  <table class="product-table" id="productTable">
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Category</th>
      <th>Price</th>
      <th>Stock</th>
      <th>Image</th>
      <th>Actions</th>
    </tr>
    <?php if ($products && $products->num_rows > 0): ?>
      <?php while ($p = $products->fetch_assoc()): ?>
        <tr>
          <td><?= $p['id'] ?></td>
          <td><?= htmlspecialchars($p['name']) ?></td>
          <td><?= htmlspecialchars($p['category'] ?? 'N/A') ?></td>
          <td>₱<?= number_format($p['price'], 2) ?></td>
          <td><?= $p['stock'] ?></td>
          <td>
            <?php if ($p['image']): ?>
              <img src="<?= htmlspecialchars($p['image']) ?>" width="60" alt="Product Image">
            <?php else: ?>
              No image
            <?php endif; ?>
          </td>
          <td>
            <div class="stock-actions">
              <form method="POST" style="display: inline;">
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                <input type="number" name="new_stock" min="1" placeholder="Qty" required>
                <button type="submit" name="update_stock" class="update-btn">Add Stock</button>
              </form>

              <form method="POST" style="display: inline;">
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                <button type="submit" name="delete_product" class="delete-btn" onclick="return confirm('Delete this product?')">Delete</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="8">No products found.</td></tr>
    <?php endif; ?>
  </table>
</div>

<!-- Add Product Modal -->
<div id="addProductModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Add New Product</h2>
      <span class="close-modal" onclick="closeAddProductModal()">&times;</span>
    </div>
    <form method="POST" enctype="multipart/form-data" class="modal-form">
      <div class="form-group">
        <label for="modal-name">Product Name</label>
        <input type="text" id="modal-name" name="name" placeholder="Product Name" required>
      </div>
      
      <div class="form-group">
        <label for="modal-category">Category</label>
        <select id="modal-category" name="category">>
          <option value="">Select Category</option>
          <option value="Longspan">Longspan</option>
          <option value="Yero">Yero</option>
          <option value="Yero (B)">Yero (B)</option>
          <option value="Gutter">Gutter</option>
          <option value="Flashing">Flashing</option>
          <option value="Plain Sheet G1">Plain Sheet G1</option>
          <option value="Shoa Board">Shoa Board</option>
          <option value="Norine Flywood">Norine Flywood</option>
          <option value="Fly Board">Flyboard</option>
          <option value="Pheno UC Board">Pheno UC Board</option>
          <option value="Coco Lumber">Coco Lumber</option>
          <option value="Flush Boor">Flush Boor</option>
          <option value="Savor Bar">Savor Bar</option>
          <option value="Flot Bar">Flot Bar</option>
          <option value="KD Good Lumber">KD Good Lumber</option>
          <option value="Plain Round Bar">Plain Round Bar</option>
          <option value="Insulation">Insulation</option>
        </select>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="modal-price">Price</label>
          <input type="number" step="0.01" id="modal-price" name="price" placeholder="Price" required>
        </div>

        <div class="form-group">
          <label for="modal-stock">Stock</label>
          <input type="number" id="modal-stock" name="stock" placeholder="Stock" required>
        </div>
      </div>

      <div class="form-group">
        <label for="modal-image">Product Image</label>
        <input type="file" id="modal-image" name="image" accept="image/*">
      </div>

      <div class="modal-footer">
        <button type="button" onclick="closeAddProductModal()" class="cancel-btn">Cancel</button>
        <button type="submit" name="add_product" class="submit-btn">Add Product</button>
      </div>
    </form>
  </div>
</div>

<!-- 🔍 LIVE SEARCH -->
<script>

// Improved search: matches any cell (name, category, price, etc.)
document.getElementById("searchInput").addEventListener("keyup", function() {
  const filter = this.value.trim().toLowerCase();
  const rows = document.querySelectorAll("#productTable tr:not(:first-child)");
  rows.forEach(row => {
    let match = false;
    row.querySelectorAll('td').forEach(cell => {
      if (cell.textContent.toLowerCase().includes(filter)) match = true;
    });
    row.style.display = match ? "" : "none";
  });
});

// Close modal when clicking outside of it
window.onclick = function(event) {
  const modal = document.getElementById("addProductModal");
  if (event.target == modal) {
    closeAddProductModal();
  }
}
</script>

<!-- Floating POS Button -->
<a href="pos.php" class="pos-float-btn" title="Open POS">
  🛒 POS
</a>

</body>
</html>
    