<?php
session_start();
include('connection.php');



// Handle logout
if (isset($_GET['logout'])) {
         
    session_destroy(); 
    
    header('location: login.php?message=You have been successfully logged out');
    exit;
}

// Fetch user information
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Update loyalty points from database if column exists
$column_check = $conn->query("SHOW COLUMNS FROM users LIKE 'loyalty_points'");
if ($column_check && $column_check->num_rows > 0 && isset($user['loyalty_points'])) {
    $_SESSION['loyalty_points'] = $user['loyalty_points'];
}

// Count user orders
$orders_query = "SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?";
$stmt = $conn->prepare($orders_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders_result = $stmt->get_result();
$order_data = $orders_result->fetch_assoc();
$order_count = $order_data['order_count'];

// Get unread message count
$unread_count = 0;
$recent_messages = [];

// Check if messages table exists
$table_check = $conn->query("SHOW TABLES LIKE 'messages'");
if ($table_check && $table_check->num_rows > 0) {
    // Count unread messages
    $unread_query = "SELECT COUNT(*) as unread FROM messages WHERE user_id = ? AND is_from_admin = 1 AND is_read = 0";
    $unread_stmt = $conn->prepare($unread_query);
    $unread_stmt->bind_param("i", $user_id);
    $unread_stmt->execute();
    $unread_result = $unread_stmt->get_result();
    $unread_count = $unread_result->fetch_assoc()['unread'];
    
    // Get recent messages (last 3)
    $recent_query = "SELECT * FROM messages WHERE user_id = ? ORDER BY created_at DESC LIMIT 3";
    $recent_stmt = $conn->prepare($recent_query);
    $recent_stmt->bind_param("i", $user_id);
    $recent_stmt->execute();
    $recent_result = $recent_stmt->get_result();
    
    while ($message = $recent_result->fetch_assoc()) {
        $recent_messages[] = $message;
    }
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
     

        .account-container {
            max-width: 900px;
            margin: 100px auto 40px;
            padding: 5px 5px;
        }

        .account-header {
            background-color: #fff;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
        }

        .account-avatar {
            width: 100px;
            height: 100px;
            background-color: #f0f0f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 30px;
        }

        .account-avatar i {
            font-size: 50px;
            color: #000;
        }

        .account-user-info h2 {
            margin: 0 0 5px;
            font-size: 24px;
            color: #000;
        }

        .account-user-info p {
            margin: 0;
            color: #666;
            font-size: 16px;
        }

        .account-sections {
            display:flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .account-section {
            flex: 1;
            min-width: 250px;
            background-color: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .account-section:hover {
            transform: translateY(-5px);
        }

        .account-section h3 {
            margin: 0 0 15px;
            font-size: 18px;
            display: flex;
            align-items: center;
        }

        .account-section h3 i {
            margin-right: 10px;
            font-size: 22px;
            color: #000;
        }

        .account-section p {
            color: #666;
            margin: 0 0 15px;
            font-size: 14px;
        }

        .account-section a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #000;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            transition: background-color 0.2s;
        }

        .account-section a:hover {
            background-color: #333;
        }

        .message-badge {
            display: inline-flex;
            background-color: #dc3545;
            color: white;
            font-size: 12px;
            font-weight: bold;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            align-items: center;
            justify-content: center;
            margin-left: 8px;
        }

        .recent-messages {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .recent-message {
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 13px;
            color: #555;
            position: relative;
        }

        .recent-message.unread {
            background-color: #e9f5ff;
        }

        .recent-message.unread:before {
            content: "";
            position: absolute;
            top: 50%;
            left: -5px;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background-color: #0066cc;
            border-radius: 50%;
        }

        .recent-message p {
            margin: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .recent-message small {
            display: block;
            color: #888;
            font-size: 11px;
            margin-top: 5px;
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
    color: var(--secondary-color, #e67e22);
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
    background: linear-gradient(120deg, var(--secondary-color, #e67e22), transparent, rgba(255,255,255,0.3));
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
    background: linear-gradient(120deg, var(--secondary-color, #e67e22), rgba(230, 126, 34, 0.6), rgba(255,255,255,0.3));
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
    color: var(--secondary-color, #e67e22);
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
                <a href="index.php">Home</a>
                <a href="cart.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count">
                        <?php echo isset($_SESSION['quantity']) ? $_SESSION['quantity'] : 0; ?>
                    </span>
                </a>
              
             
            </div>
        </div>
    </nav>

    <div class="account-container">
        <div class="account-header">
            <div class="account-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="account-user-info">
                <h2>Hello, <?php echo $user['user_name']; ?></h2>
                <p><?php echo $user['user_email']; ?></p>
            </div>
        </div>
        
        <div class="account-sections">
            <div class="account-section">
                <h3><i class="fas fa-box"></i> My Orders</h3>
                <p>You have placed <?php echo $order_count; ?> order(s) with us. Thank you for your trust and loyalty! </p>
                <a href="my_orders.php">View Orders</a>
            </div>
            
            <div class="account-section">
                <h3><i class="fas fa-user-edit"></i> Account Details</h3>
                <p>Update your personal information and password.</p>
                <a href="accountdetails.php">Edit Details</a>
            </div>
            
            <div class="account-section">
                <h3>
                    <i class="fas fa-comments"></i> Messages
                    <?php if ($unread_count > 0): ?>
                        <span class="message-badge"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </h3>
                <p>Communicate with our customer service team.</p>
                
                <a href="user_messages.php">View Messages</a>
            </div>
            
            <div class="account-section">
                <h3><i class="fas fa-sign-out-alt"></i> Logout</h3>
                <p>Securely sign out from your account.</p>
                <a href="account.php?logout=1">Logout</a>
            </div>
        </div>
    </div>

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

    <script src="script.js"></script>
</body>
</html>