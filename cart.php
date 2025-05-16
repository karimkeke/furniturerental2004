<?php
session_start();
include('connection.php');

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy(); 
    header('location: cart.php?message=You have been successfully logged out');
    exit;
}

// Handle add to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_image = $_POST['product_image'];
    $quantity = $_POST['quantity'];
    $rental_length = isset($_POST['rental_length']) && is_numeric($_POST['rental_length']) && $_POST['rental_length'] > 0 
                    ? $_POST['rental_length'] 
                    : 1;

    // Check available stock
    $stmt = $conn->prepare("SELECT product_quantity FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    $available_stock = $product['product_quantity'];
    $requested_quantity = $quantity;
    
    // If item already in cart, add to existing quantity
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            if ($item['product_id'] == $product_id) {
                $requested_quantity += $item['product_quantity'];
                break;
            }
        }
    }
    
    if ($requested_quantity > $available_stock) {
        $_SESSION['error'] = "Only $available_stock items available in stock";
        header("Location: product_details.php?id=$product_id");
        exit();
    }

    $cart_item = [
        'product_id' => $product_id,
        'product_name' => $product_name,
        'product_price' => $product_price,
        'product_image' => $product_image,
        'product_quantity' => $quantity,
        'rental_length' => $rental_length 
    ];
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['product_id'] == $product_id) {
            $item['product_quantity'] += $quantity;
            $item['rental_length'] = $rental_length; 
            $found = true;
            break;
        }
    }

    if (!$found) {
        $_SESSION['cart'][] = $cart_item; 
    }

    calculatetotalcart();
    $_SESSION['message'] = "$product_name added to cart successfully";
    header("Location: cart.php");
    exit();
}

// Handle edit_quantity action
if (isset($_POST['edit_quantity'])) {
    $product_id = $_POST['product_id'];
    $new_quantity = $_POST['product_quantity'];
    
    // Get available stock from database
    $stmt = $conn->prepare("SELECT product_quantity FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if ($new_quantity > 0) {
        if ($new_quantity <= $product['product_quantity']) {
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['product_id'] == $product_id) {
                    $item['product_quantity'] = $new_quantity;
                    // Ensure rental_length is valid
                    if (!isset($item['rental_length']) || empty($item['rental_length']) || !is_numeric($item['rental_length']) || $item['rental_length'] <= 0) {
                        $item['rental_length'] = 1;
                    }
                    break;
                }
            }
            
            calculatetotalcart();
            $_SESSION['message'] = "Quantity updated successfully";
        } else {
            $_SESSION['error'] = "Only " . $product['product_quantity'] . " items available in stock";
        }
    } else {
        $_SESSION['error'] = "Quantity must be at least 1";
    }
    
    header("Location: cart.php");
    exit();
}

// Handle remove_product action
if (isset($_POST['remove_product'])) {
    $product_id = $_POST['product_id'];
    
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['product_id'] == $product_id) {
            unset($_SESSION['cart'][$key]);
            break;
        }
    }
    
    // Reindex the array after removal
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    
    calculatetotalcart();
    $_SESSION['message'] = "Product removed from cart";
    header("Location: cart.php");
    exit();
}

function calculatetotalcart() {
    $total = 0;
    $total_quantity = 0;

    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $product) {
            // Ensure rental_length is valid
            if (!isset($product['rental_length']) || empty($product['rental_length']) || !is_numeric($product['rental_length']) || $product['rental_length'] <= 0) {
                $_SESSION['cart'][$key]['rental_length'] = 1;
                $rental_length = 1;
            } else {
                $rental_length = $product['rental_length'];
            }
            
            $total += $product['product_price'] * $product['product_quantity'] * $rental_length;
            $total_quantity += $product['product_quantity'];
        }
    }

    // Apply loyalty discount if user is logged in and has loyalty points
    if (isset($_SESSION['logged_in']) && isset($_SESSION['loyalty_points'])) {
        $points = $_SESSION['loyalty_points'];
        
        // Determine discount percentage based on loyalty tier
        if ($points >= 151) {
            // Gold tier - 15% discount
            $discount_percentage = 15;
        } elseif ($points >= 51) {
            // Silver tier - 10% discount
            $discount_percentage = 10;
        } elseif ($points > 0) {
            // Bronze tier - 5% discount
            $discount_percentage = 5;
        } else {
            $discount_percentage = 0;
        }
        
        // Calculate discount amount
        $discount_amount = ($total * $discount_percentage) / 100;
        
        // Store discount information in session for display
        $_SESSION['discount_percentage'] = $discount_percentage;
        $_SESSION['discount_amount'] = $discount_amount;
        
        // Apply discount to total
        $total -= $discount_amount;
    } else {
        // No loyalty discount
        $_SESSION['discount_percentage'] = 0;
        $_SESSION['discount_amount'] = 0;
    }

    $_SESSION['total'] = $total;
    $_SESSION['quantity'] = $total_quantity;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

 <style>
    :root {
        --primary-color: #000;
        --secondary-color: #e67e22;
        --accent-color: #e67e22;
        --background-color: #f9f9f9;
        --card-bg: #ffffff;
        --text-color: #333;
        --border-radius: 12px;
        --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        --transition: all 0.3s ease;
    }

    body {
        background-color: var(--background-color);
    }
  

    .cart-container {
        background-color: var(--card-bg);
        padding: 40px;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        max-width: 1000px;
        margin: 120px auto 60px;
        transition: var(--transition);
    }

    .cart-container:hover {
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
    }

    .cart-title {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-color); 
        margin-bottom: 30px;
        text-align: center;
        position: relative;
        padding-bottom: 15px;
    }

    .cart-title:after {
        content: '';
        position: absolute;
        width: 60px;
        height: 3px;
        background-color: var(--accent-color);
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
    }

    .cart-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 15px;
        background-color: transparent;
    }

    .cart-table th {
        background-color: var(--accent-color);
        color: white;
        padding: 16px;
        font-size: 16px;
        font-weight: 600;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .cart-table th:first-child {
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
    }

    .cart-table th:last-child {
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
    }

    .cart-table tr td {
        background-color: #F5F5F5;
        padding: 20px 15px;
        text-align: center;
        font-size: 14px;
        color: var(--text-color);
        position: relative;
        transition: var(--transition);
        transform-origin: center;
    }

    .cart-table tr:hover td {
        background-color: #f0f0f0;
        transform: scale(1.01);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .cart-table tr td:first-child {
        border-top-left-radius: var(--border-radius);
        border-bottom-left-radius: var(--border-radius);
    }

    .cart-table tr td:last-child {
        border-top-right-radius: var(--border-radius);
        border-bottom-right-radius: var(--border-radius);
    }

    .cart-table img {
        width: 90px;
        height: 90px;
        object-fit: cover;
        border-radius: 10px;
        margin-right: 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: var(--transition);
    }
    
    .cart-table img:hover {
        transform: scale(1.08);
    }

    .btn-remove {
        background-color: var(--accent-color); 
        color: white;
        padding: 10px 18px;
        border: none;
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--transition);
        font-weight: 600;
    }

    .btn-remove:hover {
        background-color:var(--accent-color);
        transform: translateY(-2px);
    }

    .btn-edit {
        background-color: var(--accent-color);
        color: white;
        padding: 10px 18px;
        border: none;
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--transition);
        font-weight: 600;
    }

    .btn-edit:hover {
        background-color: var(--accent-color);
        transform: translateY(-2px);
    }

    .cart-total {
        font-size: 26px;
        font-weight: 700;
        color: var(--primary-color);
        text-align: right;
        margin: 30px 0;
        padding: 15px;
        background-color: #f5f5f5;
        border-radius: var(--border-radius);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }

    .subtotal, .final-total {
        margin: 10px 0;
    }
    
    .subtotal {
        font-size: 20px;
        color: #555;
    }
    
    .loyalty-discount {
        color: #1b9e3e;
        font-size: 18px;
        margin: 12px 0;
        display: flex;
        justify-content: flex-end;
        align-items: center;
    }
    
    .discount-label {
        margin-right: 10px;
    }
    
    .discount-amount {
        font-weight: bold;
    }

    .checkout-btn {
        background-color: var(--accent-color);
        color: white;
        padding: 16px 30px;
        border: none;
        border-radius: 50px;
        font-size: 18px;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
        transition: var(--transition);
        letter-spacing: 1px;
        text-transform: uppercase;
        position: relative;
        overflow: hidden;
        z-index: 1;
    }

    .checkout-btn:before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: 0.5s;
        z-index: -1;
    }

    .checkout-btn:hover:before {
        left: 100%;
    }

    .checkout-btn:hover {
        background-color: var(--accent-color);
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
    }

    .empty-cart-message {
        font-size: 18px;
        color: #666;
        text-align: center;
        padding: 40px 0;
        background-color: #f8f8f8;
        border-radius: var(--border-radius);
        position: relative;
    }

    .empty-cart-message:before {
        content: '\f07a';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        font-size: 36px;
        color: #ccc;
        display: block;
        margin-bottom: 15px;
    }

    .product-info {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        text-align: left;
    }

    .product-info p {
        margin: 0;
        font-weight: 600;
        color: var(--primary-color);
        font-size: 16px;
    }

    .product-info small {
        color: #666;
        font-size: 14px;
        display: block;
        margin-top: 5px;
    }

    .quantity-input {
        width: 60px;
        padding: 10px 5px;
        border: 2px solid #ddd;
        border-radius: 10px;
        text-align: center;
        font-size: 16px;
        font-weight: 600;
        transition: var(--transition);
    }

    .quantity-input:focus {
        border-color: var(--accent-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(205, 133, 63, 0.2);
    }

    .error-message {
        background-color: #FFEBEE;
        color: #D32F2F;
        padding: 15px;
        border-radius: var(--border-radius);
        margin-bottom: 20px;
        animation: shake 0.5s ease-in-out;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-5px); }
        40%, 80% { transform: translateX(5px); }
    }

    .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            min-width: 160px; 
            padding: 8px 0;
            z-index: 100;
        }

        .dropdown-content a {
            display: block;
            padding: 8px 12px;
            font-size: 14px; 
            color: #333;
            text-decoration: none;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        .dropdown-content p {
            padding: 8px 12px;
            font-weight: bold;
            font-size: 14px;
            color: #555;
            margin: 0;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }
        .account-icon {
    position: relative;
    font-size: 22px;
    color: #000;
    text-decoration: none;
    margin-left: 20px;
}

.account-icon i {
    font-size: 26px;
}

   

    .cart-icon {
    position: relative;
    font-size: 22px;
    color: #000;
    text-decoration: none;
    margin-left: 20px;
    transition: all 0.3s ease;
}

.cart-icon:hover {
    color: var(--secondary-color);
    transform: translateY(-2px);
}

.cart-icon i {
    font-size: 26px;
}

.cart-count {
    position: absolute;
    top: -8px;
    right: -10px;
    background-color: var(--secondary-color);
    color: white;
    font-size: 13px;
    font-weight: bold;
    border-radius: 50%;
    padding: 5px 10px;
    box-shadow: 0 3px 8px rgba(230, 126, 34, 0.3);
    transition: all 0.3s ease;
}

.cart-icon:hover .cart-count {
    transform: scale(1.1);
}
   
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(211, 47, 47, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(211, 47, 47, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(211, 47, 47, 0);
        }
    }

    .register-btn {
    background-color: var(--secondary-color);
    color: white;
    border: none;
    padding: 0.7rem 1.8rem;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(230, 126, 34, 0.2);
}

.register-btn:hover {
    background-color: var(--secondary-color);
    filter: brightness(90%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(230, 126, 34, 0.3);
}

.register-btn:active {
    transform: translateY(0);
}

    /* Responsive styles */
    @media (max-width: 768px) {
        .cart-container {
            padding: 20px;
            margin: 100px 15px 40px;
        }

        .cart-table th, .cart-table td {
            padding: 10px;
        }

        .product-info {
            flex-direction: column;
            text-align: center;
        }

        .cart-table img {
            margin-right: 0;
            margin-bottom: 10px;
        }
    }

    /* Modern Logo Styles */
    .modern-logo {
        display: flex;
        align-items: center;
        text-decoration: none;
        gap: 12px;
        position: relative;
    }

    .logo-mark {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .logo-icon-wrapper {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        border-radius: 12px;
        color: var(--secondary-color);
        font-size: 20px;
        overflow: hidden;
        transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
    }

    .logo-icon-wrapper:before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        padding: 2px;
        background: linear-gradient(120deg, var(--secondary-color), transparent, rgba(255,255,255,0.3));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        opacity: 0.7;
        transition: all 0.3s ease;
    }

    .modern-logo:hover .logo-icon-wrapper {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(205, 133, 63, 0.25);
    }

    .modern-logo:hover .logo-icon-wrapper:before {
        opacity: 1;
        background: linear-gradient(120deg, var(--secondary-color), rgba(205, 133, 63, 0.6), rgba(255,255,255,0.3));
    }

    
    .logo-type {
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .logo-text {
        font-weight: 700;
        letter-spacing: 0.5px;
        font-size: 22px;
        line-height: 1.1;
        position: relative;
        transform: translateZ(0);
        transition: var(--transition);
    }

    .logo-text.primary {
        color: var(--text-color);
    }

    .logo-text.accent {
        color: var(--secondary-color);
        transform: translateX(8px);
    }

    .modern-logo:hover .logo-text.accent {
        transform: translateX(12px);
    }

    .logo-text:after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: 0;
        left: 0;
        background-color: currentColor;
        transition: width 0.4s cubic-bezier(0.19, 1, 0.22, 1);
    }

    .modern-logo:hover .logo-text:after {
        width: 100%;
    }

    .proceed-btn:hover {
        background-color: var(--secondary-color);
        filter: brightness(90%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(230, 126, 34, 0.3);
    }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="container">
        <div class="logo">
            <a href="index.php" class="modern-logo">
                <div class="logo-mark">
                    <div class="logo-icon-wrapper">
                        <i class="fas fa-couch"></i>
                    </div>
                </div>
                <div class="logo-type">
                    <span class="logo-text primary">Furniture</span>
                    <span class="logo-text accent">Rental</span>
                </div>
            </a>
        </div>
        <div class="nav-links">
        
    <?php if(!isset($_SESSION['logged_in'])) : ?>
        <a href="index.php#contact">Contact Us</a>
    <?php endif; ?>

    <a href="cart.php" class="cart-icon">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-count">
            <?php echo isset($_SESSION['quantity']) ? $_SESSION['quantity'] : 0; ?>
        </span>
    </a>

    <?php if(isset($_SESSION['logged_in'])) : ?>
        <div class="dropdown" style="display: inline-block;">
            <a href="#" class="account-icon">
                <i class="fas fa-user"></i>
            </a>
            <div class="dropdown-content">
            <a href="account.php">Account Details</a>

                <a href="account.php?logout=1" name="logout" id="logout-btn">Logout</a>
            </div>
        </div>
    <?php else : ?>
        <button class="register-btn" onclick="window.location.href='register.php'">Register</button>
    <?php endif; ?>
</div>

         
    </div>
</nav>         



    <div class="cart-container">
        <h2 class="cart-title">Your Shopping Cart</h2>
        
        <?php if(isset($_SESSION['checkout_error'])): ?>
            <div class="error-message">
                <?php 
                    echo $_SESSION['checkout_error']; 
                    unset($_SESSION['checkout_error']); 
                ?>
                <p style="margin-top: 10px;">
                    <a href="add_payment_column.php" style="background-color: var(--primary-color); color: white; padding: 8px 15px; text-decoration: none; border-radius: 6px; display: inline-block; transition: all 0.3s ease;">
                        Fix Payment System
                    </a>
                </p>
            </div>
        <?php endif; ?>
        
        <table class="cart-table">
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Action</th>
            </tr>

            <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                <?php foreach($_SESSION['cart'] as $key => $value): ?>
                    <tr>
                        <td>
                            <div class="product-info">
                                <img src="assets/imgs/<?php echo htmlspecialchars($value['product_image']); ?>">
                                <div>
                                    <p><?php echo htmlspecialchars($value['product_name']); ?></p>
                                    <small><?php echo number_format($value['product_price']); ?> DA</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <form method="POST" action="cart.php">
                                <input type="hidden" name="product_id" value="<?php echo $value['product_id']; ?>">
                                <input type="number" 
       name="product_quantity" 
       value="<?php echo $value['product_quantity']; ?>" 
       min="1" 
       max="<?php 
           // Get current stock for this product
           $stmt = $conn->prepare("SELECT product_quantity FROM products WHERE product_id = ?");
           $stmt->bind_param("i", $value['product_id']);
           $stmt->execute();
           $result = $stmt->get_result();
           $product = $result->fetch_assoc();
           echo $product['product_quantity'];
       ?>" 
       class="quantity-input">
                                       <input type="submit" name="edit_quantity" class="btn-edit" value="Update">
                            </form>
                        </td>
                        <td>
                            <form method="POST" action="cart.php">
                                <input type="hidden" name="product_id" value="<?php echo $value['product_id']; ?>">
                                <input type="submit" name="remove_product" class="btn-remove" value="Remove">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="empty-cart-message">Your cart is empty</td>
                </tr>
            <?php endif; ?>
        </table>

        <div class="cart-total">
            <?php if(isset($_SESSION['discount_percentage']) && $_SESSION['discount_percentage'] > 0): ?>
                <div class="subtotal">
                    Subtotal: <?php echo isset($_SESSION['total']) ? number_format($_SESSION['total'] + $_SESSION['discount_amount']) : '0'; ?> DA
                </div>
                <div class="loyalty-discount">
                    <span class="discount-label">Loyalty Discount (<?php echo $_SESSION['discount_percentage']; ?>%):</span>
                    <span class="discount-amount">-<?php echo isset($_SESSION['discount_amount']) ? number_format($_SESSION['discount_amount']) : '0'; ?> DA</span>
                </div>
            <?php endif; ?>
            <div class="final-total">
                Total: <?php echo isset($_SESSION['total']) ? number_format($_SESSION['total']) : '0'; ?> DA
            </div>
        </div>

        <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
            <form method="POST" action="payment_methods.php">
                <input type="submit" class="checkout-btn" name="proceed_to_payment" value="Proceed to Payment">
            </form>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <p><i class="fas fa-phone"></i> +213 XX XXX XXXX</p>
                    <p><i class="fas fa-envelope"></i> info@furniture-rental.dz</p>
                </div>
                <div class="footer-section">
                    <h3>Follow Us</h3>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Furniture Rental. All Rights Reserved</p>
            </div>
        </div>
    </footer>
</body>
</html>