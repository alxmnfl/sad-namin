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

    $conn->query("INSERT INTO products (name, category, price, stock, image)
                  VALUES ('$name','$category','$price','$stock','$image')");
    
    header("Location: admin.php");
    exit();
}


/* ---------------- DELETE PRODUCT ---------------- */
if (isset($_POST['delete_product'])) {
  $pid = intval($_POST['product_id']);
  $conn->query("DELETE FROM products WHERE id = $pid");
  header("Location: admin.php");
  exit();
}

/* ---------------- EDIT PRODUCT ---------------- */
if (isset($_POST['edit_product'])) {
  $pid = intval($_POST['product_id']);
  $name = $conn->real_escape_string($_POST['name']);
  $category = $conn->real_escape_string($_POST['category']);
  $price = floatval($_POST['price']);
  $description = $conn->real_escape_string($_POST['description']);
    
  // Handle optional image update
  $image_update_sql = "";
  if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $img_name = basename($_FILES['image']['name']);
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir);
    $target_file = $target_dir . time() . "_" . $img_name;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
      $image_update_sql = ", image='" . $conn->real_escape_string($target_file) . "'";
    }
  }
  $conn->query("UPDATE products SET name='$name', category='$category', price='$price', description='$description' $image_update_sql WHERE id=$pid");
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
$products = $conn->query("SELECT * FROM products ORDER BY id DESC");

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

  <!-- Burger Icon -->
  <div class="burger" onclick="toggleMenu()">
    ☰
  </div>

  <div class="menu" id="navMenu">
    <a href="index.php">Home</a>
    <a href="customer_orders.php">Sales</a>
    <a href="orders.php">Orders</a>
    <a href="#" onclick="openLogoutModal(); return false;" class="logout-btn">Logout</a>
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


  <div class="manage-products-header">
    <h1 class="manage-products-title">Manage Products</h1>
  </div>
  <div class="manage-products-controls">
    <div style="display: flex; gap: 12px; align-items: center;">
      <button onclick="openAddProductModal()" class="add-product-btn">+ Add Product</button>
  <button onclick="openEditCategoryModal()" class="add-product-btn" style="background: #ffcc00; color: #00264d; font-weight: 700;">Edit Category</button>
    </div>
    <div class="search-bar-container">
      <input type="text" id="searchInput" placeholder="Search by name, category, price..." class="search-bar">
    </div>
  </div>
<!-- Edit Category Modal -->
<div id="editCategoryModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Edit Categories</h2>
      <span class="close-modal" onclick="closeEditCategoryModal()">&times;</span>
    </div>
    <form id="addCategoryForm" class="modal-form" onsubmit="addCategory(event)">
      <div class="form-group">
        <label for="new-category">Add Category</label>
        <input type="text" id="new-category" name="new-category" placeholder="Enter category name" required>
        <button type="submit" class="submit-btn" style="margin-top:10px;">Add Category</button>
      </div>
    </form>
    <form id="deleteCategoryForm" class="modal-form" onsubmit="deleteCategory(event)">
      <div class="form-group">
        <label for="delete-category">Delete Category</label>
        <select id="delete-category" name="delete-category" required style="margin-bottom:10px;"></select>
        <button type="submit" class="archive-btn">Delete Category</button>
      </div>
    </form>
    <div class="modal-footer" style="padding-bottom: 30px;">
      <button type="button" onclick="closeEditCategoryModal()" class="cancel-btn">Cancel</button>
    </div>
  </div>
</div>
  <table class="product-table" id="productTable">
    <colgroup>
      <col>
      <col>
      <col>
      <col>
      <col>
      <col>
      <col>
    </colgroup>
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
          <td data-label="ID"><?= $p['id'] ?></td>
          <td data-label="Name"><?= htmlspecialchars($p['name']) ?></td>
          <td data-label="Category"><?= htmlspecialchars($p['category'] ?? 'N/A') ?></td>
          <td data-label="Price">₱<?= number_format($p['price'], 2) ?></td>
          <td data-label="Stock"><?= $p['stock'] ?></td>
          <td data-label="Image">
            <?php if ($p['image']): ?>
              <img src="<?= htmlspecialchars($p['image']) ?>" width="60" alt="Product Image">
            <?php else: ?>
              No image
            <?php endif; ?>
          </td>
          <td>
            <div class="stock-actions">
              <form class="update-stock-form" data-product-id="<?= $p['id'] ?>" style="display: inline;">
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                <input type="number" name="new_stock" class="stock-input" min="1" placeholder="Qty" required>
                <button type="submit" class="update-btn">Add Stock</button>
              </form>


              <form class="delete-product-form" data-product-id="<?= $p['id'] ?>" style="display: inline;">
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                <button type="submit" class="archive-btn">Delete</button>
              </form>

              <button 
                type="button" 
                class="edit-btn" 
                data-id="<?= $p['id'] ?>" 
                data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>" 
                data-category="<?= htmlspecialchars($p['category'] ?? '', ENT_QUOTES) ?>" 
                data-price="<?= $p['price'] ?>" 
                data-stock="<?= $p['stock'] ?>" 
                data-description="<?= isset($p['description']) ? htmlspecialchars($p['description'], ENT_QUOTES) : '' ?>" 
                data-image="<?= $p['image'] ? htmlspecialchars($p['image'], ENT_QUOTES) : '' ?>" 
                onclick="openEditProductModal(this)">Edit</button>

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
  <select id="modal-category" name="category">
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
        <label for="modal-description">Description</label>
        <textarea id="modal-description" name="description" placeholder="Product description (visible in product details)" rows="4" style="width:100%; padding:8px; border-radius:6px; border:1px solid #ccc;"></textarea>
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

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById("searchInput");
  const table = document.getElementById("productTable");
  
  if (!searchInput || !table) {
    console.error('Search input or table not found');
    return;
  }
  
  console.log('Search initialized'); // Debug log
  
  // Improved search: matches any cell (name, category, price, etc.)
  searchInput.addEventListener("input", function() {
    const filter = this.value.trim().toLowerCase();
    const rows = table.querySelectorAll("tr");
    
    console.log('Searching for:', filter); // Debug log
    
    rows.forEach((row, index) => {
      // Skip the header row (first row)
      if (index === 0) return;
      
      let match = false;
      const cells = row.querySelectorAll('td');
      
      cells.forEach(cell => {
        // Get text content including data-label content
        const text = cell.textContent || cell.innerText;
        if (text.toLowerCase().includes(filter)) {
          match = true;
        }
      });
      
      // Show or hide the row using visibility and height for mobile compatibility
      if (filter === '') {
        row.style.display = '';
        row.style.visibility = '';
        row.style.height = '';
        row.style.overflow = '';
        row.style.opacity = '';
      } else {
        if (match) {
          row.style.display = '';
          row.style.visibility = '';
          row.style.height = '';
          row.style.overflow = '';
          row.style.opacity = '';
        } else {
          row.style.display = 'none';
          row.style.visibility = 'hidden';
          row.style.height = '0';
          row.style.overflow = 'hidden';
          row.style.opacity = '0';
        }
      }
    });
  });
  
  // Also support keyup for better compatibility
  searchInput.addEventListener("keyup", function() {
    searchInput.dispatchEvent(new Event('input'));
  });
});

// Close modal when clicking outside of it
window.onclick = function(event) {
  const modal = document.getElementById("addProductModal");
  const editModal = document.getElementById("editProductModal");
  if (event.target == modal) {
    closeAddProductModal();
  }
  if (event.target == editModal) {
    closeEditProductModal();
  }
}

function openEditProductModal(el){
  const m = document.getElementById('editProductModal');
  if (!el || !m) { console.error('Edit modal or trigger element not found'); return; }
  m.style.display = 'flex';
  document.getElementById('edit-product-id').value = el.dataset.id || '';
  document.getElementById('edit-name').value = el.dataset.name || '';
  document.getElementById('edit-category').value = el.dataset.category || '';
  document.getElementById('edit-price').value = el.dataset.price || '';
  document.getElementById('edit-description').value = el.dataset.description || '';
  const preview = document.getElementById('edit-image-preview');
  if (el.dataset.image){
    preview.src = el.dataset.image;
    preview.style.display = 'block';
  } else {
    preview.style.display = 'none';
  }
  console.log('Opened edit modal for product', el.dataset.id);
}

// Fallback: attach listeners in case inline onclick not firing
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.edit-btn').forEach(btn => {
    if (!btn.dataset.bound){
      btn.addEventListener('click', function(){ openEditProductModal(this); });
      btn.dataset.bound = '1';
    }
  });
  console.log('Edit buttons bound (DOMContentLoaded). Count:', document.querySelectorAll('.edit-btn').length);
});

// Event delegation fallback (covers dynamically added rows or if initial binding failed)
const productTable = document.getElementById('productTable');
if (productTable){
  productTable.addEventListener('click', (e) => {
    const btn = e.target.closest('.edit-btn');
    if (btn){
      console.log('Delegated click on edit button id', btn.dataset.id);
      openEditProductModal(btn);
    }
  });
} else {
  console.warn('productTable not found for delegation');
}

function closeEditProductModal(){
  document.getElementById('editProductModal').style.display='none';
}

// AJAX Edit Product Handler
document.addEventListener('DOMContentLoaded', function() {
  const editForm = document.getElementById('editProductForm');
  if (editForm) {
    editForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      const productId = document.getElementById('edit-product-id').value;
      const submitBtn = this.querySelector('.submit-btn');
      const originalBtnText = submitBtn.textContent;
      
      // Disable button and show loading state
      submitBtn.disabled = true;
      submitBtn.textContent = 'Saving...';
      
      fetch('edit_product_ajax.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Update the table row with new data
          const row = document.querySelector(`button.edit-btn[data-id="${productId}"]`).closest('tr');
          if (row) {
            // Update table cells
            row.querySelector('td[data-label="Name"]').textContent = data.product.name;
            row.querySelector('td[data-label="Category"]').textContent = data.product.category || 'N/A';
            row.querySelector('td[data-label="Price"]').textContent = '₱' + parseFloat(data.product.price).toFixed(2);
            row.querySelector('td[data-label="Stock"]').textContent = data.product.stock;
            
            // Update image if changed
            if (data.product.image) {
              const imgCell = row.querySelector('td[data-label="Image"]');
              if (imgCell) {
                imgCell.innerHTML = `<img src="${data.product.image}" width="60" alt="Product Image">`;
              }
            }
            
            // Update the edit button's data attributes
            const editBtn = row.querySelector('.edit-btn');
            editBtn.setAttribute('data-name', data.product.name);
            editBtn.setAttribute('data-category', data.product.category || '');
            editBtn.setAttribute('data-price', data.product.price);
            editBtn.setAttribute('data-description', data.product.description || '');
            if (data.product.image) {
              editBtn.setAttribute('data-image', data.product.image);
            }
          }
          
          // Show success message
          submitBtn.textContent = '✓ Saved!';
          submitBtn.style.background = '#28a745';
          
          // Close modal after 1 second
          setTimeout(() => {
            closeEditProductModal();
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
            submitBtn.style.background = '#004080';
          }, 1000);
          
        } else {
          alert('Error: ' + data.message);
          submitBtn.disabled = false;
          submitBtn.textContent = originalBtnText;
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the product.');
        submitBtn.disabled = false;
        submitBtn.textContent = originalBtnText;
      });
    });
  }
});

// AJAX Update Stock Handler
document.addEventListener('DOMContentLoaded', function() {
  document.addEventListener('submit', function(e) {
    if (e.target.classList.contains('update-stock-form')) {
      e.preventDefault();
      
      const form = e.target;
      const productId = form.dataset.productId;
      const stockInput = form.querySelector('.stock-input');
      const submitBtn = form.querySelector('.update-btn');
      const originalBtnText = submitBtn.textContent;
      const row = form.closest('tr');
      
      if (!stockInput.value || stockInput.value <= 0) {
        alert('Please enter a valid quantity');
        return;
      }
      
      // Disable button and show loading state
      submitBtn.disabled = true;
      submitBtn.textContent = 'Adding...';
      
      const formData = new FormData(form);
      
      fetch('update_stock_ajax.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Update stock display in table
          const stockCell = row.querySelector('td[data-label="Stock"]');
          if (stockCell) {
            stockCell.textContent = data.new_stock;
          }
          
          // Update edit button data attribute
          const editBtn = row.querySelector('.edit-btn');
          if (editBtn) {
            editBtn.setAttribute('data-stock', data.new_stock);
          }
          
          // Clear input and show success
          stockInput.value = '';
          submitBtn.textContent = '✓ Added!';
          submitBtn.style.background = '#28a745';
          
          // Reset button after 1.5 seconds
          setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
            submitBtn.style.background = '#004080';
          }, 1500);
          
        } else {
          alert('Error: ' + data.message);
          submitBtn.disabled = false;
          submitBtn.textContent = originalBtnText;
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating stock.');
        submitBtn.disabled = false;
        submitBtn.textContent = originalBtnText;
      });
    }
  });
});

// AJAX Delete Product Handler
document.addEventListener('DOMContentLoaded', function() {
  document.addEventListener('submit', function(e) {
    if (e.target.classList.contains('delete-product-form')) {
      e.preventDefault();
      
      const form = e.target;
      const productId = form.dataset.productId;
      const submitBtn = form.querySelector('.archive-btn');
      const originalBtnText = submitBtn.textContent;
      const row = form.closest('tr');
      
      // Confirm deletion
      if (!confirm('Delete this product? This action cannot be undone.')) {
        return;
      }
      
      // Disable button and show loading state
      submitBtn.disabled = true;
      submitBtn.textContent = 'Deleting...';
      
      const formData = new FormData(form);
      
      fetch('delete_product_ajax.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Fade out and remove the row
          row.style.transition = 'opacity 0.5s';
          row.style.opacity = '0';
          
          setTimeout(() => {
            row.remove();
            
            // Check if table is now empty
            const tbody = document.querySelector('.product-table tbody');
            if (tbody && tbody.querySelectorAll('tr').length === 0) {
              tbody.innerHTML = '<tr><td colspan="8">No products found.</td></tr>';
            }
          }, 500);
          
        } else {
          alert('Error: ' + data.message);
          submitBtn.disabled = false;
          submitBtn.textContent = originalBtnText;
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the product.');
        submitBtn.disabled = false;
        submitBtn.textContent = originalBtnText;
      });
    }
  });
});
</script>

<!-- Floating POS Button -->
<a href="pos.php" class="pos-float-btn" title="Open POS">
  🛒 POS
</a>

<!-- LOGOUT CONFIRMATION MODAL -->
<div id="logout-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
  <div style="background: white; padding: 30px; border-radius: 8px; max-width: 400px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <h2 style="color: #004080; margin-bottom: 20px;">Confirm Logout</h2>
    <p style="margin: 20px 0; color: #333;">Are you sure you want to logout?</p>
    <div style="display: flex; gap: 10px; justify-content: center; margin-top: 20px;">
      <button onclick="window.location.href='logout.php'" style="background: #004080; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">Yes, Logout</button>
      <button onclick="closeLogoutModal()" style="background: #ccc; color: #333; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">Cancel</button>
    </div>
  </div>
</div>

<!-- EDIT PRODUCT MODAL (moved outside logout-modal to ensure visibility) -->
<div id="editProductModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:9999;">
  <div class="modal-content" style="background:#fff; padding:25px; border-radius:10px; width:500px; max-width:95%; box-shadow:0 4px 12px rgba(0,0,0,0.15);">
    <div class="modal-header" style="position:relative; text-align:center; margin-bottom:15px;">
      <h2 style="margin:0; color:#fff;">Edit Product</h2>
      <span class="close-modal" onclick="closeEditProductModal()" style="position:absolute; top:8px; right:12px; cursor:pointer; font-size:28px; color:#666;">&times;</span>
    </div>
    <form id="editProductForm" method="POST" enctype="multipart/form-data" class="modal-form">
      <input type="hidden" name="product_id" id="edit-product-id">
      <div class="form-group" style="margin-bottom:12px;">
        <label for="edit-name">Product Name</label>
        <input type="text" id="edit-name" name="name" required style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
      </div>
      <div class="form-group" style="margin-bottom:12px;">
        <label for="edit-category">Category</label>
        <select id="edit-category" name="category" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
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
<script>
// Edit Category Modal Functions
function openEditCategoryModal() {
  document.getElementById('editCategoryModal').style.display = 'block';
  // Populate delete dropdown with current categories (skip empty and default)
  const modalCat = document.getElementById('modal-category');
  const editCat = document.getElementById('edit-category');
  const deleteSel = document.getElementById('delete-category');
  if (deleteSel) {
    deleteSel.innerHTML = '';
    let seen = new Set();
    [modalCat, editCat].forEach(sel => {
      if (sel) {
        [...sel.options].forEach(opt => {
          if (opt.value && opt.value !== '' && !seen.has(opt.value)) {
            let o = document.createElement('option');
            o.value = opt.value;
            o.textContent = opt.textContent;
            deleteSel.appendChild(o);
            seen.add(opt.value);
          }
        });
      }
    });
  }
}
function closeEditCategoryModal() {
  document.getElementById('editCategoryModal').style.display = 'none';
  document.getElementById('addCategoryForm').reset();
}

// Add new category and update dropdowns
function addCategory(e) {
  e.preventDefault();
  const newCat = document.getElementById('new-category').value.trim();
  if (!newCat) return;
  [document.getElementById('modal-category'), document.getElementById('edit-category')].forEach(sel => {
    if (sel && ![...sel.options].some(opt => opt.value.toLowerCase() === newCat.toLowerCase())) {
      const opt = document.createElement('option');
      opt.value = newCat;
      opt.textContent = newCat;
      sel.appendChild(opt);
    }
  });
  openEditCategoryModal(); // repopulate delete dropdown
  document.getElementById('addCategoryForm').reset();
}

// Delete category and update dropdowns
function deleteCategory(e) {
  e.preventDefault();
  const delCat = document.getElementById('delete-category').value;
  if (!delCat) return;
  
  // Confirmation dialog
  if (!confirm(`Are you sure you want to delete the category "${delCat}"? This action cannot be undone.`)) {
    return;
  }
  
  [document.getElementById('modal-category'), document.getElementById('edit-category')].forEach(sel => {
    if (sel) {
      [...sel.options].forEach(opt => {
        if (opt.value === delCat) sel.removeChild(opt);
      });
    }
  });
  openEditCategoryModal(); // repopulate delete dropdown
}
</script>
      </div>
      <div class="form-group" style="margin-bottom:12px;">
        <label for="edit-price">Price</label>
        <input type="number" step="0.01" id="edit-price" name="price" required style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
      </div>
      <div class="form-group" style="margin-bottom:12px;">
        <label for="edit-description">Description</label>
        <textarea id="edit-description" name="description" rows="4" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;"></textarea>
      </div>
      <div class="form-group" style="margin-bottom:12px;">
        <label for="edit-image">Product Image (choose to replace)</label>
        <input type="file" id="edit-image" name="image" accept="image/*">
        <img id="edit-image-preview" src="" alt="Current Image" style="max-width:120px; margin-top:10px; display:none; border:1px solid #ddd; padding:4px; border-radius:6px;">
      </div>
      <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:10px; margin-top:10px;">
        <button type="button" onclick="closeEditProductModal()" class="cancel-btn" style="background:#ccc; color:#333; padding:8px 16px; border:none; border-radius:6px; cursor:pointer; font-weight:600;">Cancel</button>
        <button type="submit" name="edit_product" class="submit-btn" style="background:#004080; color:#fff; padding:8px 16px; border:none; border-radius:6px; cursor:pointer; font-weight:600;">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function openLogoutModal() {
  document.getElementById('logout-modal').style.display = 'flex';
}

function closeLogoutModal() {
  document.getElementById('logout-modal').style.display = 'none';
}
</script>

</body>
</html>
    