<?php
session_start();
include('connection.php');
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    header("Location: cart.php");
    exit();
}
foreach ($_SESSION['cart'] as $key => $item) {
    if (!isset($item['rental_length']) || empty($item['rental_length']) || !is_numeric($item['rental_length']) || $item['rental_length'] <= 0) {
        $_SESSION['cart'][$key]['rental_length'] = 1;
    }
}
if (isset($_POST['process_payment'])) {
    $_SESSION['payment_method'] = $_POST['payment_method'];
    if ($_POST['payment_method'] == 'credit_card') {
        if (isset($_POST['card_number']) && !empty($_POST['card_number']) && 
            isset($_POST['card_expiry']) && !empty($_POST['card_expiry']) && 
            isset($_POST['card_cvv']) && !empty($_POST['card_cvv'])) {
            $_SESSION['payment_processed'] = true;
            header("Location: checkout.php");
            exit();
        } else {
            $error = "Please fill in all credit card details.";
        }
    }else if ($_POST['payment_method'] == 'cash_on_delivery') {
        $_SESSION['payment_processed'] = true;
        header("Location: checkout.php");
        exit();
    }
}
$total_price = 0;
foreach ($_SESSION['cart'] as $item) {
    $product_price = $item['product_price'];
    $quantity = $item['product_quantity'];
    $rental_length = $item['rental_length'];
    
    $subtotal = $product_price * $quantity * $rental_length;
    $total_price += $subtotal;
}

// Apply loyalty discount if applicable
$discount_amount = 0;
if (isset($_SESSION['discount_percentage']) && $_SESSION['discount_percentage'] > 0) {
    $discount_amount = ($total_price * $_SESSION['discount_percentage']) / 100;
    $total_price -= $discount_amount;
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
        --background-color: #f9f9f9;
        --card-bg: #ffffff;
        --text-color: #333;
        --border-radius: 12px;
        --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        --transition: all 0.3s ease;
    }

    body {
        background-color: #f8f9fa; 
       }
        .payment-container {
            background-color: #ffffff;
            padding: 70px;
            border-radius: 6px;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 900px;
            margin: 150px auto;
        }

        .payment-title {
            font-size: 32px;
            font-weight: 700;
            color:#000; 
            margin-bottom: 30px;
            text-align: center;
        }

        .payment-methods {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .payment-method {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method:hover {
            border-color: #000;
        }

        .payment-method.active {
            border-color: #000;
            background-color: #f9f9f9;
        }

        .payment-method-header {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .payment-method-header i {
            font-size: 24px;
        }

        .payment-method-body {
            margin-top: 15px;
            display: none;
        }

        .payment-method.active .payment-method-body {
            display: block;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .checkout-btn {
            background-color: #000;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 30px;
            border-radius: 4px;
        }

        .checkout-btn:hover {
            background-color: #333;
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
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
    box-shadow: 0 8px 25px rgba(230, 126, 34, 0.25);
}

.modern-logo:hover .logo-icon-wrapper:before {
    opacity: 1;
    background: linear-gradient(120deg, var(--secondary-color), rgba(230, 126, 34, 0.6), rgba(255,255,255,0.3));
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
    transition: all 0.3s ease;
}

.logo-text.primary {
    color: #333;
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


    .order-summary {
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 5px;
        margin-bottom: 30px;
        border: 1px solid #eee;
    }
    
    .order-summary h2 {
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 20px;
        color: #333;
    }
    
    .order-summary p {
        margin: 10px 0;
    }
    
    .order-summary .discount {
        color: #1b9e3e;
        font-weight: 600;
    }
    
    .order-summary .total {
        font-size: 18px;
        font-weight: 700;
        color: #000;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #ddd;
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



    <div class="payment-container">
        <h1 class="payment-title">Select Payment Method</h1>
        
        <?php if (isset($error)) { ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php } ?>
        
        <div class="order-summary">
            <h2>Order Summary</h2>
            <?php if (isset($_SESSION['discount_percentage']) && $_SESSION['discount_percentage'] > 0): ?>
                <p class="subtotal">Subtotal: <?php echo number_format($total_price + $discount_amount, 2); ?> DA</p>
                <p class="discount">Loyalty Discount (<?php echo $_SESSION['discount_percentage']; ?>%): -<?php echo number_format($discount_amount, 2); ?> DA</p>
            <?php endif; ?>
            <p class="total">Total: <?php echo number_format($total_price, 2); ?> DA</p>
        </div>

        <div class="delivery-notice" style="background-color: #e7f3ff; padding: 15px; border-radius: 5px; margin-bottom: 30px; border-left: 4px solid #0d6efd;">
            <p style="margin: 0; color: #333;"><i class="fas fa-info-circle" style="color: #0d6efd; margin-right: 8px;"></i> <strong>Important:</strong> After completing your order, you will receive an email from our delivery agent to confirm your delivery location and schedule.</p>
        </div>

        <form method="post" id="payment-form">
            <div class="payment-methods">
                <div class="payment-method" data-method="credit_card">
                    <div class="payment-method-header">
                        <input type="radio" name="payment_method" value="credit_card" id="credit_card" required>
                        <label for="credit_card">
                            <i class="fas fa-credit-card"></i> Credit Card
                        </label>
                    </div>
                    <div class="payment-method-body">
                        <div class="form-group">
                            <label for="card_number">Card Number</label>
                            <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" pattern="[0-9\s]{13,19}" title="Card number must be between 13 and 19 digits">
                        </div>
                        <div class="form-group">
                            <label for="card_expiry">Expiry Date</label>
                            <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/YY" pattern="(0[1-9]|1[0-2])\/([0-9]{2})" title="Expiry date in format MM/YY">
                        </div>
                        <div class="form-group">
                            <label for="card_cvv">CVV</label>
                            <input type="text" id="card_cvv" name="card_cvv" placeholder="123" pattern="[0-9]{3,4}" title="CVV must be 3 or 4 digits">
                        </div>
                    </div>
                </div>

                <div class="payment-method active" data-method="cash_on_delivery">
                    <div class="payment-method-header">
                        <input type="radio" name="payment_method" value="cash_on_delivery" id="cash_on_delivery" checked required>
                        <label for="cash_on_delivery">
                            <i class="fas fa-money-bill-wave"></i> Cash on Delivery
                        </label>
                    </div>
                    <div class="payment-method-body" style="display: block;">
                        <p>Pay when you receive your items.</p>
                    </div>
                </div>
            </div>

            <button type="submit" name="process_payment" class="checkout-btn">Proceed to Checkout</button>
        </form>
    </div>

    <?php include('footer.php'); ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const paymentMethods = document.querySelectorAll('.payment-method');
            
            paymentMethods.forEach(method => {
                const radio = method.querySelector('input[type="radio"]');
                
                method.addEventListener('click', () => {
                    // First, deactivate all
                    paymentMethods.forEach(m => m.classList.remove('active'));
                    
                    // Then activate the clicked one
                    method.classList.add('active');
                    radio.checked = true;
                });
            });
        });
    </script>
</body>
</html> 