<?php
session_start();
include('connection.php');

if (!isset($_SESSION['logged_in'])) {
    header('location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT orders.*, products.product_name, products.product_image 
          FROM orders 
          JOIN products ON orders.product_id = products.product_id
          WHERE orders.user_id = ? 
          ORDER BY orders.created_at DESC";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = $result->fetch_all(MYSQLI_ASSOC);
$total_products = 0;
$total_price = 0;

foreach ($orders as $order) {
    $total_products += $order['quantity'];
    $total_price += $order['total_price'];
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Furniture Rental</title>
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

        /* Order Container Styles */
        .orders-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 80px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }

        .page-header h1 {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .page-header p {
            color:black ;
            max-width: 600px;
            margin: 0 auto;
        }

        .order-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .order-header {
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-id {
            font-weight: 600;
            color: var(--primary-color);
        }

        .order-date {
            color: var(--secondary-color);
            font-size: 14px;
        }

        .order-body {
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
        }

        .product-image {
            width: 120px;
            height: 120px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 20px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }

        .product-details {
            flex: 1;
            min-width: 250px;
        }

        .product-name {
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 8px;
            color: var(--primary-color);
        }

        .order-info {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }

        .info-item {
            flex: 1;
            min-width: 120px;
        }

        .info-label {
            font-size: 13px;
            color: var(--secondary-color);
            margin-bottom: 3px;
        }

        .info-value {
            font-weight: 500;
            font-size: 15px;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-processing {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--processing-color);
        }

        .status-completed {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }

        .status-cancelled {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--cancelled-color);
        }

        .order-footer {
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total-price {
            font-weight: 600;
            font-size: 18px;
            color: var(--primary-color);
        }

        .action-btn {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background-color: #c99d5e;
            color: white;
            transform: translateY(-2px);
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .empty-icon {
            font-size: 60px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .empty-state p {
            color: var(--secondary-color);
            max-width: 400px;
            margin: 0 auto 25px;
        }

        .continue-btn {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 25px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .continue-btn:hover {
            background-color: #1a1a1a;
            color: white;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            body {
                padding-top: 70px; /* Adjusted for mobile */
            }
            
            .order-body {
                flex-direction: column;
            }
            
            .product-image {
                width: 100%;
                height: auto;
                max-height: 200px;
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .order-footer {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
    
     
     
   
    


      
.order-summary {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        
        .summary-item {
            flex: 1;
            min-width: 200px;
            padding: 15px;
            text-align: center;
        }
        
        .summary-label {
            font-size: 16px;
            color: var(--secondary-color);
            margin-bottom: 8px;
        }
        
        .summary-value {
            font-size: 24px;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .order-summary {
                flex-direction: column;
            }
            
            .summary-item {
                margin-bottom: 15px;
            }
        }
  

    
       
        .success-icon {
    font-size: 40px;
    color: #28a745;
    margin-bottom: 15px;
    animation: pop 0.5s ease-in-out;
}

body {
            background: #f8f9fa;
            font-family: 'Cairo', sans-serif;
            margin: 0;
            padding: 0;
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
        color: var(--accent-color);
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
        background: linear-gradient(120deg, var(--accent-color), transparent, rgba(255,255,255,0.3));
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
        background: linear-gradient(120deg, var(--accent-color), rgba(205, 133, 63, 0.6), rgba(255,255,255,0.3));
    }

    .logo-icon-wrapper i {
        position: relative;
        z-index: 2;
        transform: translateZ(0);
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0); }
        25% { transform: translateY(-3px) rotate(-5deg); }
        50% { transform: translateY(0) rotate(0); }
        75% { transform: translateY(2px) rotate(5deg); }
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
        color: var(--accent-color);
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

    <div class="orders-container">
        <div class="page-header">
           
        <i class="fas fa-check-circle success-icon"></i>
        <h2 class="text-success">Order Placed Successfully!</h2>
        <p>Thank you for your order. Your payment has been processed.</p>

        </div>

        <?php if (!empty($orders)): ?>
            <!-- Add the summary section -->
            <div class="order-summary">
                <div class="summary-item">
                    <div class="summary-label">Total Orders</div>
                    <div class="summary-value"><?php echo count($orders); ?></div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Products</div>
                    <div class="summary-value"><?php echo $total_products; ?></div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Spent</div>
                    <div class="summary-value"><?php echo $total_price; ?> DA</div>
                </div>
            </div>

            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-id">Order #<?php echo isset($order['order_id']) ? htmlspecialchars($order['order_id']) : 'N/A'; ?></div>
                        <div class="order-date"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></div>
                    </div>
                    <div class="order-body">
                        <img src="assets/imgs/<?php echo htmlspecialchars($order['product_image']); ?>" alt="Product Image" class="product-image">
                        <div class="product-details">
                            <div class="product-name"><?php echo htmlspecialchars($order['product_name']); ?></div>
                            <div class="order-info">
                                <div class="info-item">
                                    <div class="info-label">Quantity</div>
                                    <div class="info-value"><?php echo htmlspecialchars($order['quantity']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Rental Length</div>
                                    <div class="info-value"><?php echo htmlspecialchars($order['rental_length']); ?> Months</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Payment Method</div>
                                    <div class="info-value"><?php echo isset($order['payment_method']) && $order['payment_method'] ? htmlspecialchars($order['payment_method']) : 'Cash on Delivery'; ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Status</div>
                                    <div class="info-value">
                                        <span class="status-badge status-<?php echo strtolower(htmlspecialchars($order['status'])); ?>">
                                            <?php echo htmlspecialchars($order['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="order-footer">
                    <div class="info-value"><?php echo htmlspecialchars($order['total_price']); ?> DA </div>                 
                  </div>
                </div>
                <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-box-open"></i>
                </div>
                <h3>No Orders Yet</h3>
                <p>You haven't placed any orders yet. Start browsing our furniture collection to find something you love!</p>
                <a href="product.php" class="continue-btn">Browse Furniture</a>
            </div>
        <?php endif; ?>
    </div>


    
   
      

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>


