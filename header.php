<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Furniture Rental</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
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
    position: relative;
}

.dropdown-content a:hover {
    background-color: #f1f1f1;
}

.dropdown-content p {
    padding: 8px 12px;
    font-weight: bold;
    font-size: 14px; 
    color: #555;
}

.dropdown:hover .dropdown-content {
    display: block;
}

.badge {
    display: inline-flex;
    background-color: #dc3545;
    color: white;
    font-size: 10px;
    font-weight: bold;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    align-items: center;
    justify-content: center;
    margin-left: 5px;
    position: relative;
    top: -1px;
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
    color: #e86a33;
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
    background: linear-gradient(120deg, #e86a33, transparent, rgba(255,255,255,0.3));
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: 0.7;
    transition: all 0.3s ease;
}

.modern-logo:hover .logo-icon-wrapper {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(232, 106, 51, 0.25);
}

.modern-logo:hover .logo-icon-wrapper:before {
    opacity: 1;
    background: linear-gradient(120deg, #e86a33, rgba(232, 106, 51, 0.6), rgba(255,255,255,0.3));
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
    transition: all 0.3s ease;
}

.logo-text.primary {
    color: #333;
}

.logo-text.accent {
    color: #e86a33;
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
                <div class="icons">
                    <div class="dropdown">
                        <a href="#" class="account-icon"><i class="fas fa-user"></i></a>
                        <div class="dropdown-content">
                            <?php if(isset($_SESSION['user_name'])): ?>
                                <p>Hi, <?php echo $_SESSION['user_name']; ?></p>
                                <a href="account.php">My Account</a>
                                <a href="accountdetails.php">Account Details</a>
                                <a href="my_orders.php">My Orders</a>
                                <?php 
                                // Check for unread messages
                                $unread_badge = '';
                                if (isset($_SESSION['user_id'])) {
                                    $user_id = $_SESSION['user_id'];
                                    
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
                                        
                                        if ($unread_count > 0) {
                                            $unread_badge = '<span class="badge">' . $unread_count . '</span>';
                                        }
                                    }
                                }
                                ?>
                                <a href="user_messages.php">Messages <?php echo $unread_badge; ?></a>
                                <a href="account.php?logout=1">Sign Out</a>
                            <?php else: ?>
                                <a href="login.php">Login</a>
                                <a href="register.php">Create an Account</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="search.php"><i class="fas fa-search"></i></a>
                    <a href="cart.php"><i class="fas fa-shopping-cart"></i></a>
                </div>
            </div>
        </div>
    </nav>