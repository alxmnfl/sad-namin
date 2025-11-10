<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

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

        if (!empty($user_email)) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'rogeliomonfielsr@gmail.com';
                $mail->Password = 'kioa rdpq tews rcdx';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->setFrom('rogeliomonfielsr@gmail.com', 'Abeth Hardware');
                $mail->addAddress($user_email, $user_name);
                $mail->isHTML(true);
                $mail->Subject = 'Your Receipt from Abeth Hardware';
                $mail->Body = $receipt_body;
                $mail->send();
            } catch (Exception $e) {
                error_log("Mailer Error: " . $mail->ErrorInfo);
            }
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
<link rel="stylesheet" href="products.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<div class="header">
  <h3>WELCOME TO ABETH HARDWARE!</h3>
  <div class="top-right">
    <button class="home-btn" onclick="window.location.href='index.php'">Home</button>
    <?php if (isset($_SESSION['username'])): ?>
      <button class="history-btn" onclick="toggleHistory()">History</button>
      <form action="logout.php" method="POST" style="display: inline;">
        <button type="submit" class="logout-btn">Logout</button>
      </form>
    <?php else: ?>
      <button class="login-btn" onclick="window.location.href='index.php#login-modal'">Login</button>
    <?php endif; ?>
  </div>
</div>

<div class="layout">
  <div class="left-panel">
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
          <div class="product-card">
            <?php if (!empty($p['image'])): ?>
              <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
            <?php else: ?>
              <div class="no-image">No Image</div>
            <?php endif; ?>
            <div class="product-footer">
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
              <form method="POST">
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
              ?>
              <div class="total-section">
                  <p><strong>Total: ₱<?= number_format($total, 2) ?></strong></p>
                  <button onclick="window.location.href='index.php#login-modal'" class="checkout-btn">Login to Checkout</button>
              </div>
              <?php
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
              <div class="total-section">
                  <p><strong>Subtotal:</strong> ₱<?= number_format($total, 2) ?></p>
                  <div style="display: flex; align-items: center; gap: 10px;">
                      <label for="cashInput"><strong>Cash:</strong></label>
                      <input type="number" id="cashInput" placeholder="Enter cash amount" step="0.01" min="0">
                  </div>
                  <p><strong>Change:</strong> ₱<span id="changeDisplay">0.00</span></p>
              </div>

              <form method="POST" id="checkoutForm">
                  <label><strong>Order Type:</strong></label>
                  <select name="order_type" id="orderType" required style="width: 100%; padding: 8px; margin: 10px 0;">
                      <option value="" disabled selected>-- Choose --</option>
                      <option value="pickup">Pickup</option>
                      <option value="delivery">Delivery</option>
                  </select>

                  <div id="deliveryFields" style="display: none;">
                      <input type="text" name="delivery_address" placeholder="Enter Delivery Address" style="width: 100%; padding: 8px; margin-bottom: 10px;">
                      <input type="text" name="contact_number" placeholder="Enter Contact Number" style="width: 100%; padding: 8px; margin-bottom: 10px;">
                  </div>

                  <button type="submit" class="add-cart-btn" name="checkout" style="width: 100%;">Checkout</button>
              </form>

              <script>
              document.getElementById('orderType').addEventListener('change', function() {
                  const deliveryFields = document.getElementById('deliveryFields');
                  deliveryFields.style.display = this.value === 'delivery' ? 'block' : 'none';
              });
              </script>
              <?php
          } else {
              echo "<p>Your cart is empty.</p>";
          }
      }
      ?>
    </div>
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
function toggleHistory() {
  document.getElementById('historyPanel').classList.toggle('active');
}

const cashInput = document.getElementById('cashInput');
const changeDisplay = document.getElementById('changeDisplay');
const subtotal = <?= json_encode($total ?? 0) ?>;
if (cashInput) {
  cashInput.addEventListener('input', () => {
    const cash = parseFloat(cashInput.value) || 0;
    const change = cash - subtotal;
    changeDisplay.textContent = change >= 0 ? change.toFixed(2) : "0.00";
  });
}
</script>

</body>
</html>