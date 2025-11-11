<?php
session_start();

// Initialize customer_id for guests
$customer_id = isset($_SESSION['id']) ? $_SESSION['id'] : 0;

$conn = new mysqli("localhost", "root", "", "hardware_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* ---------------- FETCH USER EMAIL ---------------- */
$user_email = "";
$user_query = $conn->query("SELECT email, fname, lname FROM users WHERE id='$customer_id'");
if ($user_query && $user_query->num_rows > 0) {
    $user = $user_query->fetch_assoc();
    $user_email = $user['email'];
    $user_name = $user['fname'] . ' ' . $user['lname'];
}

/* ---------------- ADD TO CART ---------------- */
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    
    // If user is not logged in, store cart in session
    if (!isset($_SESSION['username'])) {
        if (!isset($_SESSION['temp_cart'])) {
            $_SESSION['temp_cart'] = array();
        }
        
        $product_exists = false;
        foreach ($_SESSION['temp_cart'] as &$item) {
            if ($item['product_id'] == $product_id) {
                $item['quantity']++;
                $product_exists = true;
                break;
            }
        }
        
        if (!$product_exists) {
            $_SESSION['temp_cart'][] = array(
                'product_id' => $product_id,
                'quantity' => 1
            );
        }
    } else {
        // If user is logged in, store cart in database
        $check = $conn->query("SELECT * FROM cart WHERE customer_id='$customer_id' AND product_id='$product_id'");
        if ($check->num_rows > 0) {
            $conn->query("UPDATE cart SET quantity = quantity + 1 WHERE customer_id='$customer_id' AND product_id='$product_id'");
        } else {
            $conn->query("INSERT INTO cart (customer_id, product_id, quantity) VALUES ('$customer_id','$product_id','1')");
        }
    }
}

/* ---------------- UPDATE CART ---------------- */
if (isset($_POST['increase_qty']) || isset($_POST['decrease_qty'])) {
    $cart_id = intval($_POST['cart_id']);
    $result = $conn->query("SELECT quantity FROM cart WHERE cart_id='$cart_id' AND customer_id='$customer_id'");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $qty = $row['quantity'];
        if (isset($_POST['increase_qty'])) $qty++;
        if (isset($_POST['decrease_qty'])) $qty--;
        if ($qty > 0) {
            $conn->query("UPDATE cart SET quantity='$qty' WHERE cart_id='$cart_id'");
        } else {
            $conn->query("DELETE FROM cart WHERE cart_id='$cart_id'");
        }
    }
}

/* ---------------- REMOVE FROM CART ---------------- */
if (isset($_POST['remove_from_cart'])) {
    $cart_id = $_POST['cart_id'];
    $conn->query("DELETE FROM cart WHERE cart_id='$cart_id' AND customer_id='$customer_id'");
}

/* ---------------- CHECKOUT ---------------- */
if (isset($_POST['checkout'])) {
    // Ensure user is logged in before checkout
    if (!isset($_SESSION['username'])) {
        header("Location: index.php#login-modal");
        exit();
    }
    
    // If there was a temporary cart, transfer it to the user's actual cart
    if (isset($_SESSION['temp_cart']) && !empty($_SESSION['temp_cart'])) {
        foreach ($_SESSION['temp_cart'] as $item) {
            $pid = $item['product_id'];
            $qty = $item['quantity'];
            $check = $conn->query("SELECT * FROM cart WHERE customer_id='$customer_id' AND product_id='$pid'");
            if ($check->num_rows > 0) {
                $conn->query("UPDATE cart SET quantity = quantity + $qty WHERE customer_id='$customer_id' AND product_id='$pid'");
            } else {
                $conn->query("INSERT INTO cart (customer_id, product_id, quantity) VALUES ('$customer_id','$pid','$qty')");
            }
        }
        // Clear the temporary cart
        unset($_SESSION['temp_cart']);
    }

    $order_type = $_POST['order_type'] ?? '';
    $delivery_address = $_POST['delivery_address'] ?? '';
    $contact_number = $_POST['contact_number'] ?? '';

    $cart_items = $conn->query("
        SELECT c.product_id, c.quantity, p.price, p.stock, p.name
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.customer_id='$customer_id'
    ");

    if ($cart_items->num_rows > 0) {
        $total = 0;
        while ($i = $cart_items->fetch_assoc()) {
            $total += $i['quantity'] * $i['price'];
        }

        $conn->query("INSERT INTO transactions (user_id, total_amount, transaction_date, order_type, delivery_address, contact_number) 
                      VALUES ('$customer_id','$total',NOW(),'$order_type','$delivery_address','$contact_number')");
        $transaction_id = $conn->insert_id;

        $cart_items = $conn->query("
            SELECT c.product_id, c.quantity, p.price, p.name
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.customer_id='$customer_id'
        ");

        while ($i = $cart_items->fetch_assoc()) {
            $pid = $i['product_id'];
            $conn->query("INSERT INTO transaction_items (transaction_id, product_id, product_name, quantity, price)
                          VALUES ('$transaction_id', '$pid', '{$i['name']}', '{$i['quantity']}', '{$i['price']}')");
            $conn->query("UPDATE products SET stock = stock - {$i['quantity']} WHERE id='$pid'");
        }

        $conn->query("DELETE FROM cart WHERE customer_id='$customer_id'");

        /* ---------------- EMAIL RECEIPT ---------------- */
        $receipt_body = "
        <div style='font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px;'>
          <div style='max-width: 600px; margin: 0 auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); overflow: hidden;'>
            <div style='background-color: #004080; color: white; padding: 15px; text-align: center;'>
              <h2 style='margin: 0;'>Abeth Hardware</h2>
              <p style='margin: 0; font-size: 14px;'>Your Trusted Partner for Quality Tools</p>
            </div>
            <div style='padding: 20px;'>
              <h3 style='color: #004080;'>Thank you for your purchase, $user_name!</h3>
              <p style='font-size: 14px; color: #333;'>Below is a summary of your transaction:</p>
              <p><strong>Order Type:</strong> $order_type</p>
              " . ($order_type === 'delivery' ? "<p><strong>Address:</strong> $delivery_address<br><strong>Contact:</strong> $contact_number</p>" : "") . "
              <table style='width: 100%; border-collapse: collapse; margin-top: 15px;'>
                <thead>
                  <tr style='background-color: #004080; color: white; text-align: left;'>
                    <th style='padding: 8px;'>Product</th>
                    <th style='padding: 8px;'>Qty</th>
                    <th style='padding: 8px;'>Price</th>
                    <th style='padding: 8px;'>Subtotal</th>
                  </tr>
                </thead><tbody>";

        $items_result = $conn->query("SELECT product_name, quantity, price FROM transaction_items WHERE transaction_id='$transaction_id'");
        $total = 0;
        while ($row = $items_result->fetch_assoc()) {
            $subtotal = $row['quantity'] * $row['price'];
            $total += $subtotal;
            $receipt_body .= "
              <tr style='border-bottom: 1px solid #ddd;'>
                <td style='padding: 8px;'>" . htmlspecialchars($row['product_name']) . "</td>
                <td style='padding: 8px; text-align: center;'>{$row['quantity']}</td>
                <td style='padding: 8px;'>₱" . number_format($row['price'], 2) . "</td>
                <td style='padding: 8px;'>₱" . number_format($subtotal, 2) . "</td>
              </tr>";
        }

        $receipt_body .= "
                </tbody>
              </table>
              <p style='text-align:right;'><strong>Total:</strong> ₱" . number_format($total, 2) . "</p>
              <hr>
              <p>Transaction Date: " . date('F d, Y h:i A') . "</p>
              <p>Transaction ID: #$transaction_id</p>
            </div>
          </div>
        </div>";

        // Email functionality temporarily disabled
        // Will be implemented when PHPMailer is properly installed
        if (!empty($user_email)) {
            // Store the receipt in a file for now
            $receipt_file = 'receipts/' . $transaction_id . '_receipt.html';
            if (!is_dir('receipts')) {
                mkdir('receipts');
            }
            file_put_contents($receipt_file, $receipt_body);
        }
    }
}

/* ---------------- FETCH DATA ---------------- */
$selected_category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
if (!empty($selected_category)) {
    $products = $conn->query("SELECT * FROM products WHERE category='$selected_category'");
} else {
    $products = $conn->query("SELECT * FROM products");
}
$categories = $conn->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != ''");

$cart = $conn->query("SELECT c.cart_id, p.name, p.price, c.quantity FROM cart c JOIN products p ON c.product_id = p.id WHERE c.customer_id='$customer_id'");
$transactions_result = $conn->query("
    SELECT t.transaction_id, t.transaction_date, t.total_amount, ti.product_name, ti.quantity, ti.price
    FROM transactions t
    JOIN transaction_items ti ON t.transaction_id = ti.transaction_id
    WHERE t.user_id='$customer_id'
    ORDER BY t.transaction_date DESC
");
$transactions = [];
if ($transactions_result) {
    while ($row = $transactions_result->fetch_assoc()) {
        $id = $row['transaction_id'];
        $transactions[$id]['date'] = $row['transaction_date'];
        $transactions[$id]['total'] = $row['total_amount'];
        $transactions[$id]['items'][] = [
            'name' => $row['product_name'],
            'quantity' => $row['quantity'],
            'price' => $row['price']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Abeth Hardware - Customer</title>
<link rel="stylesheet" href="products.css?v=<?php echo filemtime(__DIR__ . '/products.css'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script>
    // AJAX Add to Cart Function
    function addToCartAjax(productId, event) {
      event.preventDefault();
      
      // Send AJAX request
      fetch('add_to_cart_ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Update cart content
          document.querySelector('.cart-content').innerHTML = data.cart_html;
          
          // Update total if exists
          const totalElement = document.querySelector('.cart-total strong:last-child');
          if (totalElement) {
            totalElement.textContent = '₱' + data.total;
          }
          
          // Update modal total
          const modalTotal = document.getElementById('modalTotal');
          if (modalTotal) {
            modalTotal.textContent = parseFloat(data.total).toFixed(2);
          }
          
          // Update cart badge
          updateCartBadge();
          
          // Show brief success indication
          const btn = event.target;
          
          btn.textContent = '✓ Added!';
          btn.classList.add('btn-success');
          
          setTimeout(() => {
            btn.textContent = 'Added to cart';
            btn.classList.remove('btn-success');
          }, 1000);
        }
      })
      .catch(error => {
        console.error('Error:', error);
      });
      
      return false;
    }

    function showProductDetail(id, name, price, description, stock) {
      const detailPanel = document.getElementById('product-detail-panel');
      const detailContent = document.getElementById('detail-content');
      const productCard = document.querySelector(`[data-product-id="${id}"]`);
      const productImage = productCard.querySelector('.product-image');
      
      detailContent.innerHTML = `
        <div class="detail-image-section">
          <div class="product-image">
            ${productImage.innerHTML}
          </div>
        </div>
        <div class="detail-info-section">
          <div class="detail-header">
            <h3>${name}</h3>
            <p class="price">₱${price}</p>
          </div>
          <div class="detail-body">
            <div class="info-grid">
              <div class="info-item">
                <span class="label">Category:</span>
                <span class="value">${productCard.getAttribute('data-category')}</span>
              </div>
              <div class="info-item">
                <span class="label">Stock:</span>
                <span class="value ${stock > 0 ? 'in-stock' : 'out-of-stock'}">
                  ${stock > 0 ? `${stock} units available` : 'Out of Stock'}
                </span>
              </div>
            </div>
            <div class="description-section">
              <h4>Description</h4>
              <p>${description || 'No description available.'}</p>
            </div>
          </div>
          <div class="detail-footer">
            <form method="post" class="detail-cart-form" onsubmit="return addToCartAjax(${id}, event);">
              <input type="hidden" name="product_id" value="${id}">
              <div class="quantity-control">
                <label for="qty-${id}">Quantity:</label>
                <input type="number" id="qty-${id}" name="quantity" value="1" min="1" max="${stock}" ${stock <= 0 ? 'disabled' : ''}>
              </div>
              <button type="submit" name="add_to_cart" class="detail-add-cart-btn" ${stock <= 0 ? 'disabled' : ''}>
                ${stock > 0 ? 'Add to Cart' : 'Out of Stock'}
              </button>
            </form>
          </div>
        </div>
      `;
      
      // Use setTimeout to prevent immediate closing from click event propagation
      setTimeout(() => {
        document.body.classList.add('detail-panel-open');
        detailPanel.style.display = 'block';
      }, 10);
    }

    function closeProductDetail() {
      const detailPanel = document.getElementById('product-detail-panel');
      detailPanel.style.display = 'none';
      document.body.classList.remove('detail-panel-open');
    }

    // Close detail panel when clicking the overlay
    document.addEventListener('DOMContentLoaded', function() {
      const detailPanel = document.getElementById('product-detail-panel');
      
      document.addEventListener('click', function(e) {
        // Only proceed if detail panel is open
        if (detailPanel && detailPanel.style.display === 'block') {
          // Check if click is outside the detail panel content
          if (!detailPanel.contains(e.target) && !e.target.closest('.product-footer')) {
            closeProductDetail();
          }
        }
      });

      // Prevent clicks inside the panel from closing it
      if (detailPanel) {
        detailPanel.addEventListener('click', function(e) {
          e.stopPropagation();
        });
      }
    });
</script>
</head>
<body>

<!-- Floating Cart Button for Mobile -->
<button id="floatingCartBtn" class="floating-cart-btn" onclick="toggleCart()">
  🛒 <span class="cart-badge" id="cartBadge">0</span>
</button>

<nav class="header">
  <div class="logo"><a href="index.php" class="logo-link"><strong>Abeth Hardware</strong></a></div>
  <div class="burger" onclick="toggleMenu()">
    <div></div>
    <div></div>
    <div></div>
  </div>
  <div class="top-right">
    <button class="home-btn" onclick="window.location.href='index.php'">Home</button>
    <?php if (isset($_SESSION['username'])): ?>
      <button class="history-btn" onclick="toggleHistory()">History</button>
      <button type="button" class="logout-btn" onclick="openLogoutModal()">Logout</button>
    <?php else: ?>
      <!-- guest: no extra button (single Home already present) -->
    <?php endif; ?>
  </div>
</nav>

<div class="layout">
  <div class="left-panel detail-panel" id="product-detail-panel" style="display: none;">
    <div class="panel-header">
      <h2>Product Details</h2>
      <button class="close-detail-btn" onclick="closeProductDetail()">&times;</button>
    </div>
    <div class="product-detail-grid" id="detail-content">
    </div>
  </div>

  <div class="middle-panel">
    <h2>Available Products</h2>

    <!-- Category Filter -->
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
          <div class="product-card" data-product-id="<?= $p['id'] ?>" data-category="<?= htmlspecialchars($p['category']) ?>">
            <div class="product-image">
              <?php if (!empty($p['image'])): ?>
                <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
              <?php else: ?>
                <div class="no-image">No Image</div>
              <?php endif; ?>
            </div>
            <div class="product-footer" onclick="showProductDetail(
                '<?= $p['id'] ?>', 
                '<?= htmlspecialchars(addslashes($p['name'])) ?>', 
                '<?= number_format($p['price'], 2) ?>', 
                '<?= htmlspecialchars(addslashes($p['description'] ?? '')) ?>', 
                '<?= $p['stock'] ?>'
              )">
              <h4><?= htmlspecialchars($p['name']) ?></h4>
              <?= htmlspecialchars($p['category']) ?>
              <p><strong>₱<?= number_format($p['price'], 2) ?></strong></p>
              <p class="stock-info" style="color: <?= $p['stock'] > 0 ? '#007700' : '#cc0000' ?>; margin: 5px 0;">
                <?php if ($p['stock'] > 0): ?>
                  In Stock: <?= $p['stock'] ?> units
                <?php else: ?>
                  Out of Stock
                <?php endif; ?>
              </p>
              <form method="POST" onclick="event.stopPropagation()" onsubmit="return addToCartAjax(<?= $p['id'] ?>, event);">
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                <?php if ($p['stock'] > 0): ?>
                  <button type="submit" class="add-cart-btn" name="add_to_cart">Add to Cart</button>
                <?php else: ?>
                  <button type="button" class="add-cart-btn" disabled style="background-color: #999; cursor: not-allowed;">Out of Stock</button>
                <?php endif; ?>
              </form>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No products available.</p>
      <?php endif; ?>
    </div>
  </div>

  <div class="right-panel">
    <button class="close-cart-btn" style="display: none !important; visibility: hidden !important; opacity: 0 !important;">&times;</button>
    <div class="cart-header">Your Cart</div>
    <div class="cart-content">
      <?php
      $total = 0;
      if (!isset($_SESSION['username'])) {
          // Display temporary cart for non-logged-in users
          if (isset($_SESSION['temp_cart']) && !empty($_SESSION['temp_cart'])) {
              foreach ($_SESSION['temp_cart'] as $cart_item) {
                  $pid = $cart_item['product_id'];
                  $result = $conn->query("SELECT name, price FROM products WHERE id = $pid");
                  if ($result && $row = $result->fetch_assoc()) {
                      $subtotal = $row['price'] * $cart_item['quantity'];
                      $total += $subtotal;
                      ?>
                      <div class="cart-row">
                          <div>
                              <strong><?= htmlspecialchars($row['name']) ?></strong><br>
                              ₱<?= number_format($row['price'], 2) ?>
                          </div>
                          <div class="cart-controls">
                              <span><?= $cart_item['quantity'] ?></span>
                          </div>
                      </div>
                      <?php
                  }
              }
          } else {
              echo "<p>Your cart is empty.</p>";
          }
      } else {
          // Display cart for logged-in users
          if ($cart && $cart->num_rows > 0) {
              while ($item = $cart->fetch_assoc()) {
                  $subtotal = $item['price'] * $item['quantity'];
                  $total += $subtotal;
                  ?>
                  <div class="cart-row">
                      <div>
                          <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                          ₱<?= number_format($item['price'], 2) ?>
                      </div>

                      <div class="cart-controls">
                          <form method="POST" style="display:inline;">
                              <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                              <button class="qty-btn" name="decrease_qty">-</button>
                          </form>

                          <span><?= $item['quantity'] ?></span>

                          <form method="POST" style="display:inline;">
                              <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                              <button class="qty-btn" name="increase_qty">+</button>
                          </form>

                          <form method="POST" style="display:inline;">
                              <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                              <button class="remove-btn" name="remove_from_cart">×</button>
                          </form>
                      </div>
                  </div>
                  <?php
              }
              ?>
              <?php
          } else {
              echo "<p>Your cart is empty.</p>";
          }
      }
      ?>
    </div>
    
    <!-- Checkout Section - Outside scrollable area -->
    <?php if ($total > 0): ?>
    <div class="checkout-section">
      <div class="total-section">
          <p><strong>Subtotal:</strong> ₱<?= number_format($total, 2) ?></p>
      </div>

      <?php if (isset($_SESSION['username'])): ?>
      <!-- Mobile: Show Checkout Button -->
      <button type="button" onclick="openCheckoutModal()" class="add-cart-btn mobile-checkout-btn" style="width: 100%; padding: 18px; font-size: 18px; font-weight: bold;">Checkout</button>
      
      <!-- Desktop: Show Inline Checkout Form -->
      <form method="POST" class="desktop-checkout-form">
        <div style="margin-bottom: 15px;">
          <label for="desktopCashInput" style="display: block; margin-bottom: 5px;"><strong>Cash Amount:</strong></label>
          <input type="number" id="desktopCashInput" name="cash" placeholder="Enter cash amount" step="0.01" min="0" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
        </div>
        
        <div style="margin-bottom: 15px;">
          <p><strong>Change:</strong> ₱<span id="desktopChangeDisplay">0.00</span></p>
        </div>

        <div style="margin-bottom: 15px;">
          <label for="desktopOrderType" style="display: block; margin-bottom: 5px;"><strong>Order Type:</strong></label>
          <select name="order_type" id="desktopOrderType" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
            <option value="" disabled selected>-- Choose --</option>
            <option value="pickup">Pickup</option>
            <option value="delivery">Delivery</option>
          </select>
        </div>

        <div id="desktopDeliveryFields" style="display: none;">
          <div style="margin-bottom: 15px;">
            <input type="text" name="delivery_address" placeholder="Enter Delivery Address" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
          </div>
          <div style="margin-bottom: 15px;">
            <input type="text" name="contact_number" placeholder="Enter Contact Number" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
          </div>
        </div>

        <button type="submit" name="checkout" style="width: 100%; padding: 12px; background: #004080; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px;">Complete Checkout</button>
      </form>
      <?php else: ?>
      <button onclick="openLoginModal()" class="checkout-btn" style="width: 100%;">Login to Checkout</button>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Checkout Modal -->
<div id="checkout-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
  <div style="background: white; padding: 30px; border-radius: 8px; max-width: 400px; width: 90%; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-height: 90vh; overflow-y: auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h2 style="color: #004080; margin: 0;">Checkout</h2>
      <span onclick="event.stopPropagation(); closeCheckoutModal();" style="font-size: 28px; cursor: pointer; color: red; font-weight: bold; transition: color 0.2s;" onmouseover="this.style.color='darkred'" onmouseout="this.style.color='red'">&times;</span>
    </div>
    
    <div style="margin-bottom: 20px;">
      <p><strong>Total Amount:</strong> ₱<span id="modalTotal"><?= number_format($total, 2) ?></span></p>
    </div>

    <form method="POST" id="checkoutForm">
      <div style="margin-bottom: 15px;">
        <label for="cashInput" style="display: block; margin-bottom: 5px;"><strong>Cash Amount:</strong></label>
        <input type="number" id="cashInput" placeholder="Enter cash amount" step="0.01" min="0" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
      </div>
      
      <div style="margin-bottom: 15px;">
        <p><strong>Change:</strong> ₱<span id="changeDisplay">0.00</span></p>
      </div>

      <div style="margin-bottom: 15px;">
        <label for="orderType" style="display: block; margin-bottom: 5px;"><strong>Order Type:</strong></label>
        <select name="order_type" id="orderType" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
          <option value="" disabled selected>-- Choose --</option>
          <option value="pickup">Pickup</option>
          <option value="delivery">Delivery</option>
        </select>
      </div>

      <div id="deliveryFields" style="display: none;">
        <div style="margin-bottom: 15px;">
          <input type="text" name="delivery_address" placeholder="Enter Delivery Address" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
        </div>
        <div style="margin-bottom: 15px;">
          <input type="text" name="contact_number" placeholder="Enter Contact Number" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
        </div>
      </div>

      <button type="submit" name="checkout" style="width: 100%; padding: 18px; background: #004080; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 18px; margin-top: 10px;">Complete Checkout</button>
    </form>
  </div>
</div>


<div id="historyPanel" class="history-container">
  <button class="close-btn" onclick="toggleHistory()">×</button>
  <h3>Purchase History</h3>
  <?php if (!empty($transactions)): ?>
    <?php foreach ($transactions as $id => $t): ?>
      <div class="history-item">
        <p><strong>Date:</strong> <?= htmlspecialchars($t['date']) ?></p>
        <ul>
          <?php foreach ($t['items'] as $item): ?>
            <li><?= htmlspecialchars($item['name']) ?> (x<?= $item['quantity'] ?>) - ₱<?= number_format($item['price'], 2) ?></li>
          <?php endforeach; ?>
        </ul>
        <p><strong>Total:</strong> ₱<?= number_format($t['total'], 2) ?></p>
        <hr>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p>No transaction history yet.</p>
  <?php endif; ?>
</div>

<script>
function toggleMenu() {
  const topRight = document.querySelector('.top-right');
  topRight.classList.toggle('active');
}

function toggleCart() {
  const rightPanel = document.querySelector('.right-panel');
  const closeCartBtn = document.querySelector('.close-cart-btn');
  const floatingCartBtn = document.getElementById('floatingCartBtn');
  rightPanel.classList.toggle('active');
  
  // Show/hide close button based on cart state
  if (rightPanel.classList.contains('active')) {
    closeCartBtn.style.display = 'flex';
    closeCartBtn.style.visibility = 'visible';
    closeCartBtn.style.opacity = '1';
    // Hide floating cart button when cart is open
    if (floatingCartBtn) {
      floatingCartBtn.style.setProperty('display', 'none', 'important');
      floatingCartBtn.style.setProperty('visibility', 'hidden', 'important');
      floatingCartBtn.style.setProperty('opacity', '0', 'important');
    }
  } else {
    closeCartBtn.style.display = 'none';
    closeCartBtn.style.visibility = 'hidden';
    closeCartBtn.style.opacity = '0';
    // Show floating cart button when cart is closed
    if (floatingCartBtn) {
      floatingCartBtn.style.setProperty('display', 'flex', 'important');
      floatingCartBtn.style.setProperty('visibility', 'visible', 'important');
      floatingCartBtn.style.setProperty('opacity', '1', 'important');
    }
  }
}

function updateCartBadge() {
  const cartRows = document.querySelectorAll('.cart-row');
  const badge = document.getElementById('cartBadge');
  let totalItems = 0;
  
  cartRows.forEach(row => {
    const qtyElement = row.querySelector('.cart-controls span');
    if (qtyElement) {
      totalItems += parseInt(qtyElement.textContent) || 0;
    }
  });
  
  badge.textContent = totalItems;
  badge.style.display = totalItems > 0 ? 'flex' : 'none';
}

// Update badge on page load
document.addEventListener('DOMContentLoaded', updateCartBadge);

function toggleHistory() {
  const historyPanel = document.getElementById('historyPanel');
  historyPanel.classList.toggle('active');
  document.body.classList.toggle('history-panel-open');
}

// Close history panel when clicking the overlay
document.addEventListener('DOMContentLoaded', function() {
  const historyPanel = document.getElementById('historyPanel');
  const rightPanel = document.querySelector('.right-panel');
  const closeCartBtn = document.querySelector('.close-cart-btn');
  
  // Add explicit handler for close cart button to prevent event bubbling
  if (closeCartBtn) {
    closeCartBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      e.preventDefault();
      // Only toggle cart if cart is actually active
      if (rightPanel && rightPanel.classList.contains('active')) {
        toggleCart();
      }
    });
  }
  
  document.addEventListener('click', function(e) {
    // Skip if clicking the close cart button
    if (e.target.closest('.close-cart-btn')) {
      return;
    }
    
    // Close history panel if clicking outside
    if (historyPanel && historyPanel.classList.contains('active')) {
      // Ignore clicks on history button, cart panel, and floating cart button
      if (!historyPanel.contains(e.target) && 
          !e.target.closest('.history-btn') && 
          !e.target.closest('.right-panel') &&
          !e.target.closest('.floating-cart-btn')) {
        toggleHistory();
      }
    }
    
    // Close cart panel if clicking outside (but not on floating cart button)
    if (rightPanel && rightPanel.classList.contains('active')) {
      if (!rightPanel.contains(e.target) && 
          !e.target.closest('.floating-cart-btn')) {
        toggleCart();
      }
    }
  });

  // Prevent clicks inside the panels from closing them
  if (historyPanel) {
    historyPanel.addEventListener('click', function(e) {
      e.stopPropagation();
    });
  }
  
  if (rightPanel) {
    rightPanel.addEventListener('click', function(e) {
      e.stopPropagation();
    });
  }
});

// Checkout modal functions
function openCheckoutModal() {
  document.getElementById('checkout-modal').style.display = 'flex';
}

function closeCheckoutModal() {
  document.getElementById('checkout-modal').style.display = 'none';
}

// Order type change handler - for both mobile modal and desktop form
document.addEventListener('DOMContentLoaded', function() {
  // Prevent checkout modal clicks from closing cart
  const checkoutModal = document.getElementById('checkout-modal');
  if (checkoutModal) {
    checkoutModal.addEventListener('click', function(e) {
      // Only close modal if clicking the overlay, not the content
      if (e.target === checkoutModal) {
        closeCheckoutModal();
      }
      // Stop propagation to prevent cart from closing
      e.stopPropagation();
    });
  }

  // Mobile modal order type
  const orderType = document.getElementById('orderType');
  if (orderType) {
    orderType.addEventListener('change', function() {
      const deliveryFields = document.getElementById('deliveryFields');
      deliveryFields.style.display = this.value === 'delivery' ? 'block' : 'none';
    });
  }

  // Desktop form order type
  const desktopOrderType = document.getElementById('desktopOrderType');
  if (desktopOrderType) {
    desktopOrderType.addEventListener('change', function() {
      const desktopDeliveryFields = document.getElementById('desktopDeliveryFields');
      desktopDeliveryFields.style.display = this.value === 'delivery' ? 'block' : 'none';
    });
  }
});

// Mobile modal cash input
const cashInput = document.getElementById('cashInput');
const changeDisplay = document.getElementById('changeDisplay');
if (cashInput) {
  cashInput.addEventListener('input', () => {
    const modalTotal = document.getElementById('modalTotal');
    const subtotal = modalTotal ? parseFloat(modalTotal.textContent) : <?= json_encode($total ?? 0) ?>;
    const cash = parseFloat(cashInput.value) || 0;
    const change = cash - subtotal;
    changeDisplay.textContent = change >= 0 ? change.toFixed(2) : "0.00";
  });
}

// Desktop form cash input
const desktopCashInput = document.getElementById('desktopCashInput');
const desktopChangeDisplay = document.getElementById('desktopChangeDisplay');
if (desktopCashInput) {
  desktopCashInput.addEventListener('input', () => {
    const subtotal = <?= json_encode($total ?? 0) ?>;
    const cash = parseFloat(desktopCashInput.value) || 0;
    const change = cash - subtotal;
    desktopChangeDisplay.textContent = change >= 0 ? change.toFixed(2) : "0.00";
  });
}

// Logout modal functions
function openLogoutModal() {
  document.getElementById('logout-modal').style.display = 'flex';
}

function closeLogoutModal() {
  document.getElementById('logout-modal').style.display = 'none';
}

// Login/Signup modal functions
function openLoginModal() {
  document.getElementById('login-modal').style.display = 'flex';
}

function closeLoginModal() {
  document.getElementById('login-modal').style.display = 'none';
}

function openSignupModal() {
  closeLoginModal();
  document.getElementById('signup-modal').style.display = 'flex';
}

function closeSignupModal() {
  document.getElementById('signup-modal').style.display = 'none';
}

function switchToLogin() {
  closeSignupModal();
  openLoginModal();
}
</script>

<!-- LOGIN MODAL -->
<div id="login-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
  <div style="background: white; padding: 30px; border-radius: 8px; max-width: 400px; width: 90%; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <div style="position: relative; margin-bottom: 20px;">
      <h2 style="color: #004080; margin: 0; text-align: center;">Login</h2>
      <span onclick="closeLoginModal()" style="position: absolute; top: -5px; right: -10px; font-size: 28px; cursor: pointer; color: #666;">&times;</span>
    </div>
    <form method="POST" action="login.php">
      <input type="text" name="username" placeholder="Username or Email" required style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
      <input type="password" name="password" placeholder="Password" required style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
      <button type="submit" style="width: 100%; padding: 12px; background: #004080; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px;">Login</button>
      <p style="text-align: center; margin-top: 15px; color: #666;">Don't have an account? <a href="#" onclick="openSignupModal(); return false;" style="color: #004080; font-weight: bold;">Sign Up</a></p>
    </form>
  </div>
</div>

<!-- SIGNUP MODAL -->
<div id="signup-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
  <div style="background: white; padding: 30px; border-radius: 8px; max-width: 400px; width: 90%; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-height: 90vh; overflow-y: auto;">
    <div style="position: relative; margin-bottom: 20px;">
      <h2 style="color: #004080; margin: 0; text-align: center;">Sign Up</h2>
      <span onclick="closeSignupModal()" style="position: absolute; top: -5px; right: -10px; font-size: 28px; cursor: pointer; color: #666;">&times;</span>
    </div>
    <form method="POST" action="register.php">
      <input type="text" name="fname" placeholder="First Name" required style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
      <input type="text" name="lname" placeholder="Last Name" required style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
      <input type="text" name="address" placeholder="Address" required style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
      <input type="email" name="email" placeholder="Email" required style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
      <input type="text" name="username" placeholder="Username" required style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
      <input type="password" name="password" placeholder="Password" required style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
      <input type="password" name="password_confirm" placeholder="Confirm Password" required style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
      <button type="submit" style="width: 100%; padding: 12px; background: #004080; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px;">Create Account</button>
      <p style="text-align: center; margin-top: 15px; color: #666;">Already have an account? <a href="#" onclick="switchToLogin(); return false;" style="color: #004080; font-weight: bold;">Login</a></p>
    </form>
  </div>
</div>

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

</body>
</html>