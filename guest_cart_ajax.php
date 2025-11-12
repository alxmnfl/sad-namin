<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['temp_cart'])) {
    $_SESSION['temp_cart'] = array();
}

$action = $_POST['action'] ?? '';
$product_id = intval($_POST['product_id'] ?? 0);

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

// Update cart based on action
if (isset($_SESSION['temp_cart']) && !empty($_SESSION['temp_cart'])) {
    $updated_cart = array();
    
    foreach ($_SESSION['temp_cart'] as $item) {
        if ($item['product_id'] == $product_id) {
            // This is the item we want to modify
            if ($action === 'increase') {
                $item['quantity']++;
                $updated_cart[] = $item;
            } elseif ($action === 'decrease') {
                $item['quantity']--;
                if ($item['quantity'] > 0) {
                    $updated_cart[] = $item;
                }
                // If quantity is 0 or less, don't add (removes it)
            } elseif ($action === 'remove') {
                // Don't add to updated_cart (removes it)
            } else {
                // Unknown action, keep item as-is
                $updated_cart[] = $item;
            }
        } else {
            // Keep other items unchanged
            $updated_cart[] = $item;
        }
    }
    
    $_SESSION['temp_cart'] = $updated_cart;
}

// Generate updated cart HTML
$conn = getDatabaseConnection();
ob_start();
$total = 0;

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
                    <button class="qty-btn" onclick="updateGuestCart(<?= $pid ?>, 'decrease'); return false;">-</button>
                    <span id="guest-qty-<?= $pid ?>"><?= $cart_item['quantity'] ?></span>
                    <button class="qty-btn" onclick="updateGuestCart(<?= $pid ?>, 'increase'); return false;">+</button>
                    <button class="remove-btn" onclick="updateGuestCart(<?= $pid ?>, 'remove'); return false;">×</button>
                </div>
            </div>
            <?php
        }
    }
} else {
    echo "<p>Your cart is empty.</p>";
}

$cart_html = ob_get_clean();

// Count total items
$item_count = 0;
if (isset($_SESSION['temp_cart'])) {
    foreach ($_SESSION['temp_cart'] as $item) {
        $item_count += $item['quantity'];
    }
}

echo json_encode([
    'success' => true,
    'cart_html' => $cart_html,
    'total' => number_format($total, 2),
    'item_count' => $item_count
]);

$conn->close();
?>
