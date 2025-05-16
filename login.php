<?php
session_start();
include('connection.php');

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, user_name, user_email, user_password, loyalty_points FROM users WHERE user_email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if ($password == $user['user_password']) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_email'] = $user['user_email'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['logged_in'] = true;
            
            // Get loyalty points from database
            if (isset($user['loyalty_points'])) {
                $_SESSION['loyalty_points'] = $user['loyalty_points'];
            } else {
                // If loyalty_points column doesn't exist yet, default to 10
                $_SESSION['loyalty_points'] = 10;
                
                // Check if the loyalty_points column exists, if not, redirect to add it
                $column_check = $conn->query("SHOW COLUMNS FROM users LIKE 'loyalty_points'");
                if (!$column_check || $column_check->num_rows == 0) {
                    header('location: add_loyalty_column.php');
                    exit();
                }
            }
            
            // Recalculate cart with loyalty discount if cart exists
            if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                calculatetotalcart();
            }
            
            if(isset($_POST['remember']) && $_POST['remember'] == 'on') {
                $cookie_expiration = time() + (30 * 24 * 60 * 60); // 30 days
                setcookie('user_email', $email, $cookie_expiration, '/');
                setcookie('user_remember', 'yes', $cookie_expiration, '/');
            }
            
            header('location: account.php?login_success=logged in successfully');
            exit();
        } else {
            header('location: login.php?error=password is incorrect');
            exit();
        }
    } else {
        header('location: login.php?error=email does not exist');
        exit();
    }
}

$remembered_email = '';
if(isset($_COOKIE['user_email']) && isset($_COOKIE['user_remember']) && $_COOKIE['user_remember'] == 'yes') {
    $remembered_email = $_COOKIE['user_email'];
}

function calculatetotalcart() {
    $total = 0;
    $total_quantity = 0;

    if(isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach($_SESSION['cart'] as $key => $product) {
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

if(isset($_SESSION['cart'])) {
    calculatetotalcart();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Furniture Rental</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
      :root {
            --light-bg: #f5f2ed; 
        }

        body {
            background: var(--light-bg);
            font-family: 'Cairo', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            position: relative;
            color: var(--primary-color);
            line-height: 1.6;
            overflow-x: hidden;
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

        .login-section {
            max-width: 1200px;
            margin: 100px auto 80px;
            padding: 0 20px;
            display: flex;
            align-items: flex-start;
            gap: 40px;
        }

        .login-image {
            flex: 1;
            display: none;
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            height: 600px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        @media (min-width: 992px) {
            .login-image {
                display: block;
            }
        }

        .login-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .login-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .login-form-container {
            background-color: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 40px;
            width: 100%;
            height:600px;
            max-width: 450px;
        }

        .login-form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-form-header h2 {
            font-size: 28px;
            color: #000;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .login-form-header p {
            color: #666;
            margin-top: 0;
        }

        .login-form .form-group {
            margin-bottom: 20px;
        }

        .login-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .login-form input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            font-family: 'Cairo', sans-serif;
            font-size: 15px;
            transition: border 0.3s ease;
        }

        .login-form input{
            outline: none;
            color:black;
        }

        .login-button {
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
        }

        .login-button:hover {
            background-color: #333;
            transform: translateY(-2px);
        }

        .login-button i {
            margin-right: 8px;
        }

        .login-footer {
            text-align: center;
            margin-top: 25px;
            color: #666;
        }

        .login-footer a {
            color: #000;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .login-footer a:hover {
            color: #555;
            text-decoration: underline;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .remember-me input {
            width: auto;
            margin-right: 8px;
        }

        .remember-me label {
            margin-bottom: 0;
            font-weight: normal;
        }
        @media (max-width: 768px) {
            .login-form-container {
                padding: 30px;
            }
            
            .login-section {
                margin: 80px auto 60px;
            }
        }

        @media (max-width: 480px) {
            .login-form-container {
                padding: 20px;
            }
            
            .login-form-header h2 {
                font-size: 24px;
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
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="product.php">Products</a>
                <?php if(!isset($_SESSION['logged_in'])) : ?>
                    <a href="index.php#contact">Contact Us</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <section class="login-section">
        <div class="login-image">
            <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1336&q=80" alt="Furniture Interior">
        </div>
        
        <div class="login-content">
            <div class="login-form-container">
                <div class="login-form-header">
                    <h2>Welcome Back</h2>
                    <p>Log in to access your rental account</p>
                </div>

                <?php if(isset($_GET['error'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($_GET['message'])): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['message']); ?>
                    </div>
                <?php endif; ?>

                <form class="login-form" action="login.php" method="POST">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required value="<?php echo htmlspecialchars($remembered_email); ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>

                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember" <?php echo !empty($remembered_email) ? 'checked' : ''; ?>>
                        <label for="remember">Remember me</label>
                    </div>

                    <button type="submit" class="login-button" name="login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>

                <div class="login-footer">
                    <p>Don't have an account? <a href="register.php">Create an account</a></p>
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
        document.addEventListener('DOMContentLoaded', function() {
            const loginFormContainer = document.querySelector('.login-form-container');
            loginFormContainer.style.opacity = 0;
            loginFormContainer.style.transform = 'translateY(20px)';
            loginFormContainer.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            
            setTimeout(() => {
                loginFormContainer.style.opacity = 1;
                loginFormContainer.style.transform = 'translateY(0)';
            }, 200);
        });
    </script>
</body>
</html>