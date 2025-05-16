<?php
session_start();
include('connection.php');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['payment_processed']) || !$_SESSION['payment_processed']) {
    header("Location: payment_methods.php");
    exit();
}

if (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    header("Location: cart.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$payment_method = $_SESSION['payment_method'];
$total_price = 0;
$order_ids = []; 
$order_items = [];

foreach ($_SESSION['cart'] as $item) {
    $product_id = $item['product_id'];
    $quantity = $item['product_quantity'];
    $rental_length = $item['rental_length'];
    $product_price = $item['product_price'];

    $subtotal = $product_price * $quantity * $rental_length;
    $total_price += $subtotal;

    $order_items[] = [
        'product_id' => $product_id,
        'quantity' => $quantity,
        'rental_length' => $rental_length,
        'subtotal' => $subtotal
    ];
}

// Apply loyalty discount if available
$discount_percentage = 0;
$discount_amount = 0;

if (isset($_SESSION['discount_percentage']) && $_SESSION['discount_percentage'] > 0) {
    $discount_percentage = $_SESSION['discount_percentage'];
    $discount_amount = ($total_price * $discount_percentage) / 100;
    
    // Update total_price with discount
    $total_price -= $discount_amount;
    
    // Calculate loyalty points for this purchase (1 point per 1000 DZD)
    $original_total = $total_price + $discount_amount; // We use pre-discount amount for points calculation
    $points_earned = floor($original_total / 1000);
    
    // Add earned points to user's loyalty points
    $_SESSION['loyalty_points'] = isset($_SESSION['loyalty_points']) ? $_SESSION['loyalty_points'] + $points_earned : $points_earned;
    
    // Update loyalty points in the database
    if (isset($_SESSION['user_id'])) {
        // Check if loyalty_points column exists
        $column_check = $conn->query("SHOW COLUMNS FROM users LIKE 'loyalty_points'");
        $column_exists = ($column_check && $column_check->num_rows > 0);
        
        if ($column_exists) {
            $update_points = $conn->prepare("UPDATE users SET loyalty_points = ? WHERE user_id = ?");
            $update_points->bind_param("ii", $_SESSION['loyalty_points'], $_SESSION['user_id']);
            $update_points->execute();
        } else {
            // If column doesn't exist, redirect to create it
            $_SESSION['checkout_pending'] = true;
            header('location: add_loyalty_column.php');
            exit();
        }
    }
}

foreach ($order_items as $order) {
    // Calculate item discount proportionally
    $item_discount = 0;
    if ($discount_amount > 0) {
        $item_discount = ($order['subtotal'] / ($total_price + $discount_amount)) * $discount_amount;
        $discounted_subtotal = $order['subtotal'] - $item_discount;
    } else {
        $discounted_subtotal = $order['subtotal'];
    }
    
    $stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, quantity, rental_length, total_price, payment_method, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiiids", $user_id, $order['product_id'], $order['quantity'], $order['rental_length'], $discounted_subtotal, $payment_method);
    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;
        $order_ids[] = $order_id;

        $update_stmt = $conn->prepare("UPDATE products SET product_quantity = product_quantity - ? WHERE product_id = ?");
        $update_stmt->bind_param("ii", $order['quantity'], $order['product_id']);
        $update_stmt->execute();

       
    }
}

// Clear session
unset($_SESSION['cart']);
unset($_SESSION['total']);
unset($_SESSION['quantity']);
unset($_SESSION['payment_processed']);
unset($_SESSION['payment_method']);

header("Location: success.php");
exit();
?>
