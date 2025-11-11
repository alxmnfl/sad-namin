<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Initialize customer_id for guests
$customer_id = isset($_SESSION['id']) ? $_SESSION['id'] : 0;

$conn = getDatabaseConnection();

if (isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    
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
    
    // Get updated cart HTML
    ob_start();
    
    // Fetch cart items
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
        // Display cart from database for logged-in users
        $cart = $conn->query("SELECT c.cart_id, p.name, p.price, c.quantity FROM cart c JOIN products p ON c.product_id = p.id WHERE c.customer_id='$customer_id'");
        if ($cart && $cart->num_rows > 0) {
            while ($row = $cart->fetch_assoc()) {
                $subtotal = $row['price'] * $row['quantity'];
                $total += $subtotal;
                ?>
                <div class="cart-row">
                    <div>
                        <strong><?= htmlspecialchars($row['name']) ?></strong><br>
                        ₱<?= number_format($row['price'], 2) ?>
                    </div>
                    <div class="cart-controls">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="cart_id" value="<?= $row['cart_id'] ?>">
                            <button type="submit" name="decrease_qty" class="qty-btn">-</button>
                        </form>
                        <span><?= $row['quantity'] ?></span>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="cart_id" value="<?= $row['cart_id'] ?>">
                            <button type="submit" name="increase_qty" class="qty-btn">+</button>
                        </form>
                        <form method="POST" style="display: inline; margin-left: 8px;">
                            <input type="hidden" name="cart_id" value="<?= $row['cart_id'] ?>">
                            <button type="submit" name="remove_from_cart" class="remove-btn">×</button>
                        </form>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<p>Your cart is empty.</p>";
        }
    }
    
    $cart_html = ob_get_clean();
    
    // Count total items
    $item_count = 0;
    if (!isset($_SESSION['username'])) {
        if (isset($_SESSION['temp_cart'])) {
            foreach ($_SESSION['temp_cart'] as $item) {
                $item_count += $item['quantity'];
            }
        }
    } else {
        $cart_count = $conn->query("SELECT SUM(quantity) as total FROM cart WHERE customer_id='$customer_id'");
        if ($cart_count && $row = $cart_count->fetch_assoc()) {
            $item_count = $row['total'] ?? 0;
        }
    }
    
    echo json_encode([
        'success' => true,
        'cart_html' => $cart_html,
        'total' => number_format($total, 2),
        'item_count' => $item_count
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$conn->close();
?>
