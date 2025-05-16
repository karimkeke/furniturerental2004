<?php
session_start();
include('connection.php');
if (isset($_GET['logout'])) {
    session_destroy(); 
    header('location: login.php?message=You have been successfully logged out');
    exit;
}
if (!isset($_SESSION['logged_in'])) {
    header('location: login.php');
    exit;
}

// Get user details including loyalty points from database
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Add loyalty points functionality
// Check if loyalty_points column exists in database
$column_check = $conn->query("SHOW COLUMNS FROM users LIKE 'loyalty_points'");
if ($column_check && $column_check->num_rows > 0) {
    // If column exists, use the value from the database
    if (isset($user['loyalty_points'])) {
        $_SESSION['loyalty_points'] = $user['loyalty_points'];
    } else if (!isset($_SESSION['loyalty_points'])) {
        // Default value if not set
        $_SESSION['loyalty_points'] = 10;
        // Update database
        $update = $conn->prepare("UPDATE users SET loyalty_points = 10 WHERE user_id = ?");
        $update->bind_param("i", $user_id);
        $update->execute();
    }
} else {
    // If column doesn't exist, use session or default
    if(!isset($_SESSION['loyalty_points'])) {
        $_SESSION['loyalty_points'] = 10;
    }
    
    // Redirect to add the column
    header("location: add_loyalty_column.php");
    exit();
}

// Determine loyalty tier based on points
function getLoyaltyTier($points) {
    if($points >= 151) {
        return ['tier' => 'Gold', 'class' => 'gold', 'benefits' => '15% off + free delivery + priority support'];
    } elseif($points >= 51) {
        return ['tier' => 'Silver', 'class' => 'silver', 'benefits' => '10% off + free delivery'];
    } else {
        return ['tier' => 'Bronze', 'class' => 'bronze', 'benefits' => '5% off your next rental'];
    }
}

$loyalty = getLoyaltyTier($_SESSION['loyalty_points']);

if(isset($_POST['change_password'])){
    $password = $_POST['password'];
    $confirmpassword = $_POST['confirmpassword'];
    $user_email = $_SESSION['user_email'];

    if ($password !== $confirmpassword) {
        header('location: accountdetails.php?error=Passwords don\'t match');
        exit;
    } elseif (strlen($password) < 6) {
        header('location: accountdetails.php?error=Password must be at least 6 characters');
        exit;
    } else {
        $stmt = $conn->prepare("UPDATE users SET user_password=? WHERE user_email=?");
        $stmt->bind_param('ss', $password, $user_email);
        if($stmt->execute()) {
            header('location: accountdetails.php?message=Password updated successfully');
            exit;
        } else {
            header('location: accountdetails.php?error=Couldn\'t update password');
            exit;
        }
    }
}

function calculatetotalcart() {
    $total = 0;
    $total_quantity = 0;

    if(isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach($_SESSION['cart'] as $product) {
            $total += $product['product_price'] * $product['product_quantity'];
            $total_quantity += $product['product_quantity'];
        }
    }

    $_SESSION['total'] = $total;
    $_SESSION['quantity'] = $total_quantity;
}

if(isset($_SESSION['cart'])) {
    calculatetotalcart();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Details - Furniture Rental</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Cairo', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            position: relative;
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

        .account-details-section {
            max-width: 1200px;
            margin: 100px auto 80px;
            padding: 0 20px;
            display: flex;
            align-items: flex-start;
            gap: 40px;
        }

        .profile-sidebar {
            flex: 0 0 300px;
            background-color: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 5px;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background-color: #f0f0f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            transition: transform 0.3s ease;
        }

        .profile-avatar:hover {
            transform: scale(1.05);
        }

        .profile-avatar i {
            font-size: 60px;
            color: #000;
        }

        .profile-name {
            font-size: 22px;
            font-weight: 700;
            color: #000;
            margin-bottom: 8px;
        }

        .profile-email {
            font-size: 14px;
            color: #666;
            margin-bottom: 25px;
        }

        .profile-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .profile-menu li {
            margin-bottom: 12px;
        }

        .profile-menu a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #333;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .profile-menu a:hover {
            background-color: #f8f9fa;
            color: #000;
        }

        .profile-menu a.active {
            background-color: #f0f0f0;
            color: #000;
            font-weight: 600;
        }

        .profile-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .password-form-container {
            flex: 1;
            background-color: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 50px;
        }

        .password-form-header {
            margin-bottom: 30px;
        }

        .password-form-header h2 {
            font-size: 24px;
            color: #000;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .password-form-header p {
            color: #666;
            margin-top: 0;
            line-height: 1.5;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-message i {
            margin-right: 10px;
            font-size: 18px;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .success-message i {
            margin-right: 10px;
            font-size: 18px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid black;
            border-radius: 8px;
            font-family: 'Cairo', sans-serif;
            font-size: 15px;
            transition: border 0.3s ease;
        }

        .form-group input:focus {
            border-color: #000;
            outline: none;
        }

        .password-requirements {
            margin-top: 5px;
            font-size: 12px;
            color: #666;
        }
        .form-group input[type="password"] {
    color: #2c3e50;
}


        .update-button {
            width: 100%;
            padding: 14px;
            background-color: #000;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .update-button:hover {
            background-color: #333;
            transform: translateY(-2px);
        }

        .update-button i {
            margin-right: 8px;
        }

        .divider {
            height: 1px;
            background-color: #eee;
            margin: 20px 0;
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

        .cart-icon {
            position: relative;
            font-size: 22px;
            color: #000;
            text-decoration: none;
            margin-left: 20px;
        }

        .cart-icon i {
            font-size: 26px;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -10px;
            background-color: red;
            color: white;
            font-size: 13px;
            font-weight: bold;
            border-radius: 50%;
            padding: 5px 10px;
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

        @media (max-width: 992px) {
            .account-details-section {
                flex-direction: column;
            }
            
            .profile-sidebar {
                width: 100%;
                flex: none;
                margin-bottom: 30px;
            }
            
            .password-form-container {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .password-form-container {
                padding: 30px;
            }
            
            .account-details-section {
                margin: 80px auto 60px;
            }
        }

        @media (max-width: 480px) {
            .password-form-container {
                padding: 20px;
            }
            
            .password-form-header h2 {
                font-size: 22px;
            }
            
            .profile-avatar {
                width: 100px;
                height: 100px;
            }
            
            .profile-avatar i {
                font-size: 50px;
            }
        }

        /* Add tab functionality styles */
        .account-content {
            display: none;
        }
        
        .account-content.active {
            display: block;
        }
        
        #account-settings-content {
            display: block;
        }

        /* Loyalty Program Styles */
        .loyalty-form-container {
            flex: 1;
            background-color: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 50px;
        }
        
        .loyalty-header {
            margin-bottom: 30px;
        }
        
        .loyalty-header h2 {
            font-size: 24px;
            color: #000;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .loyalty-header p {
            color: #666;
            margin-top: 0;
            line-height: 1.5;
        }
        
        .loyalty-status {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .loyalty-points-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .loyalty-points {
            text-align: center;
        }
        
        .points-count {
            font-size: 48px;
            font-weight: 700;
            color: var(--secondary-color, #e67e22);
            display: block;
            line-height: 1;
        }
        
        .points-label {
            font-size: 16px;
            color: #666;
        }
        
        .tier-info {
            display: flex;
            align-items: center;
        }
        
        .tier-badge {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .tier-badge i {
            color: #fff;
            font-size: 25px;
        }
        
        .tier-badge.bronze {
            background: linear-gradient(135deg, #CD7F32 0%, #E6BC9E 100%);
        }
        
        .tier-badge.silver {
            background: linear-gradient(135deg, #C0C0C0 0%, #E8E8E8 100%);
        }
        
        .tier-badge.gold {
            background: linear-gradient(135deg, #FFD700 0%, #FFF8DC 100%);
        }
        
        .tier-details h3 {
            margin: 0 0 5px 0;
            font-size: 20px;
            color: #333;
        }
        
        .tier-details p {
            margin: 0;
            color: #666;
        }
        
        .progress-container {
            margin-top: 20px;
        }
        
        .tier-progress {
            position: relative;
        }
        
        .progress-labels {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .progress-labels span {
            font-size: 14px;
            color: #666;
        }
        
        .progress-bar {
            height: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
            position: relative;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #CD7F32, #C0C0C0, #FFD700);
            border-radius: 5px;
            transition: width 0.3s ease;
        }
        
        .milestone {
            position: absolute;
            width: 3px;
            height: 10px;
            background-color: #fff;
            top: 0;
        }
        
        .progress-values {
            display: flex;
            justify-content: space-between;
            margin-top: 5px;
        }
        
        .progress-values span {
            font-size: 12px;
            color: #666;
        }
        
        .loyalty-benefits {
            margin-top: 40px;
        }
        
        .loyalty-benefits h3 {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .benefits-grid {
            display: flex;
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .benefit-card {
            flex: 1;
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .benefit-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        /* Bronze tier card */
        .benefit-card:nth-child(1) {
            border-color: #CD7F32;
        }
        
        /* Silver tier card */
        .benefit-card:nth-child(2) {
            border-color: #C0C0C0;
        }
        
        /* Gold tier card */
        .benefit-card:nth-child(3) {
            border-color: #FFD700;
        }
        
        .benefit-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }
        
        .benefit-icon i {
            color: #fff;
            font-size: 30px;
        }
        
        .benefit-icon.bronze {
            background: linear-gradient(135deg, #CD7F32 0%, #E6BC9E 100%);
        }
        
        .benefit-icon.silver {
            background: linear-gradient(135deg, #C0C0C0 0%, #E8E8E8 100%);
        }
        
        .benefit-icon.gold {
            background: linear-gradient(135deg, #FFD700 0%, #FFF8DC 100%);
        }
        
        .benefit-card h4 {
            margin: 0 0 5px 0;
            font-size: 18px;
            color: #333;
        }
        
        /* Bronze tier heading */
        .benefit-card:nth-child(1) h4 {
            color: #CD7F32;
        }
        
        /* Silver tier heading */
        .benefit-card:nth-child(2) h4 {
            color: #727272;
        }
        
        /* Gold tier heading */
        .benefit-card:nth-child(3) h4 {
            color: #D4AF37;
        }
        
        .benefit-card p {
            margin: 0 0 15px 0;
            font-size: 14px;
            color: #666;
        }
        
        .benefit-card ul {
            text-align: left;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .benefit-card ul li {
            margin-bottom: 8px;
            color: #555;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        
        .benefit-card ul li i {
            margin-right: 8px;
        }
        
        /* Bronze tier check icons */
        .benefit-card:nth-child(1) ul li i {
            color: #CD7F32;
        }
        
        /* Silver tier check icons */
        .benefit-card:nth-child(2) ul li i {
            color: #727272;
        }
        
        /* Gold tier check icons */
        .benefit-card:nth-child(3) ul li i {
            color: #D4AF37;
        }
        
        .how-to-earn {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
        }
        
        .how-to-earn h3 {
            margin-top: 0;
            text-align: left;
        }
        
        .earn-points-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .earn-points-list li {
            display: flex;
            align-items: center;
            color: #555;
        }
        
        .earn-points-list li i {
            width: 30px;
            height: 30px;
            background-color: #f0f0f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            color: var(--secondary-color, #e67e22);
        }
        
        @media (max-width: 768px) {
            .benefits-grid {
                flex-direction: column;
            }
            
            .loyalty-points-section {
                flex-direction: column;
                gap: 20px;
            }
            
            .earn-points-list {
                grid-template-columns: 1fr;
            }
            
            .loyalty-form-container {
                padding: 30px;
            }
        }
        
        @media (max-width: 480px) {
            .loyalty-form-container {
                padding: 20px;
            }
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
        </div>       

</nav>         
    <section class="account-details-section">
        <div class="profile-sidebar">
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h2 class="profile-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
                <p class="profile-email"><?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
            </div>
            
            <div class="divider"></div>
            
            <ul class="profile-menu">
                <li><a href="account.php"><i class="fas fa-tachometer-alt"></i> Home</a></li>
                <li><a href="my_orders.php"><i class="fas fa-shopping-bag"></i> My Orders</a></li>
                <li><a href="accountdetails.php" class="active"><i class="fas fa-user-cog"></i> Account Settings</a></li>
                <li><a href="#" class="loyalty-tab"><i class="fas fa-award"></i> Loyalty Program</a></li>
                <li><a href="accountdetails.php?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="password-form-container account-content" id="account-settings-content">
            <div class="password-form-header">
                <h2>Change Your Password</h2>
                <p>Update your password to maintain account security. Your new password must be at least 6 characters long.</p>
            </div>
            
            <?php if(isset($_GET['error'])): ?>
                <div class="error-message"> 
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($_GET['error']); ?> 
                </div>
            <?php endif; ?>
            
            <?php if(isset($_GET['message'])): ?>
                <div class="success-message"> 
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($_GET['message']); ?> 
                </div>
            <?php endif; ?>
            
            <form id="account-form" method="POST" action="accountdetails.php">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your new password" required>
                    <p class="password-requirements">Password must be at least 6 characters long</p>
                </div>
                
                <div class="form-group">
                    <label for="confirmpassword">Confirm New Password</label>
                    <input type="password" id="confirmpassword" name="confirmpassword" placeholder="Confirm your new password" required>
                </div>
                
                <button type="submit" class="update-button" name="change_password">
                    <i class="fas fa-key"></i> Update Password
                </button>
            </form>
        </div>

        <!-- Loyalty Program Content -->
        <div class="loyalty-form-container account-content" id="loyalty-content" style="display: none;">
            <div class="loyalty-header">
                <h2>Your Loyalty Program Status</h2>
                <p>Earn points with every rental and enjoy exclusive benefits as a valued customer.</p>
            </div>

            <div class="loyalty-status">
                <div class="loyalty-points-section">
                    <div class="loyalty-points">
                        <span class="points-count"><?php echo $_SESSION['loyalty_points']; ?></span>
                        <span class="points-label">Points</span>
                    </div>
                    <div class="tier-info">
                        <div class="tier-badge <?php echo $loyalty['class']; ?>">
                            <i class="fas fa-medal"></i>
                        </div>
                        <div class="tier-details">
                            <h3><?php echo $loyalty['tier']; ?> Member</h3>
                            <p><?php echo $loyalty['benefits']; ?></p>
                        </div>
                    </div>
                </div>

                <div class="progress-container">
                    <div class="tier-progress">
                        <div class="progress-labels">
                            <span>Bronze</span>
                            <span>Silver</span>
                            <span>Gold</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo min(100, ($_SESSION['loyalty_points'] / 151) * 100); ?>%"></div>
                            <div class="milestone" style="left: 33%"></div>
                            <div class="milestone" style="left: 66%"></div>
                        </div>
                        <div class="progress-values">
                            <span>0</span>
                            <span>51</span>
                            <span>151</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="loyalty-benefits">
                <h3>Loyalty Program Benefits</h3>
                
                <div class="benefits-grid">
                    <div class="benefit-card">
                        <div class="benefit-icon bronze">
                            <i class="fas fa-medal"></i>
                        </div>
                        <h4>Bronze Tier</h4>
                        <p>0-50 points</p>
                        <ul>
                            <li><i class="fas fa-check"></i> 5% off your next rental</li>
                            <li><i class="fas fa-check"></i> Monthly newsletter with exclusive offers</li>
                        </ul>
                    </div>
                    
                    <div class="benefit-card">
                        <div class="benefit-icon silver">
                            <i class="fas fa-medal"></i>
                        </div>
                        <h4>Silver Tier</h4>
                        <p>51-150 points</p>
                        <ul>
                            <li><i class="fas fa-check"></i> 10% off all rentals</li>
                            <li><i class="fas fa-check"></i> Free delivery on all orders</li>
                            <li><i class="fas fa-check"></i> Exclusive early access to new products</li>
                        </ul>
                    </div>
                    
                    <div class="benefit-card">
                        <div class="benefit-icon gold">
                            <i class="fas fa-medal"></i>
                        </div>
                        <h4>Gold Tier</h4>
                        <p>151+ points</p>
                        <ul>
                            <li><i class="fas fa-check"></i> 15% off all rentals</li>
                            <li><i class="fas fa-check"></i> Free delivery on all orders</li>
                            <li><i class="fas fa-check"></i> Priority customer support</li>
                            <li><i class="fas fa-check"></i> Exclusive member events and offers</li>
                            <li><i class="fas fa-check"></i> Flexible rental periods</li>
                        </ul>
                    </div>
                </div>
                
                <div class="how-to-earn">
                    <h3>How to Earn Points</h3>
                    <ul class="earn-points-list">
                        <li><i class="fas fa-user-plus"></i> <strong>Registration:</strong> Earn 10 points when you create an account</li>
                        <li><i class="fas fa-shopping-cart"></i> <strong>Make Purchases:</strong> Earn 1 point for every 1000 DZD spent</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

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

    <script>
        // Add tab switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            const accountSettingsTab = document.querySelector('.profile-menu a[href="accountdetails.php"]');
            const loyaltyTab = document.querySelector('.loyalty-tab');
            const accountSettingsContent = document.getElementById('account-settings-content');
            const loyaltyContent = document.getElementById('loyalty-content');
            
            // Set active tab
            accountSettingsTab.addEventListener('click', function(e) {
                e.preventDefault();
                accountSettingsTab.classList.add('active');
                loyaltyTab.classList.remove('active');
                accountSettingsContent.style.display = 'block';
                loyaltyContent.style.display = 'none';
            });
            
            loyaltyTab.addEventListener('click', function(e) {
                e.preventDefault();
                loyaltyTab.classList.add('active');
                accountSettingsTab.classList.remove('active');
                loyaltyContent.style.display = 'block';
                accountSettingsContent.style.display = 'none';
            });
            
            // Check if we should show loyalty tab based on URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            if(urlParams.get('tab') === 'loyalty') {
                loyaltyTab.click();
            }
        });
    </script>
</body>
</html> 