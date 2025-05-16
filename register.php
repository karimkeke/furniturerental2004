<?php
 session_start();
 include('connection.php');

 if (isset($_POST['register'])) {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $password = $_POST['password'];
  $confirmpassword = $_POST['confirmpassword'];
  if ($password !== $confirmpassword) {
      header('Location: register.php?error=passwords do not match');
      exit();
  } else if (strlen($password) < 6) {
      header('Location: register.php?error=password must be at least 6 characters');
      exit();
  } else {
      $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_email = ?");
      $stmt->bind_param('s', $email);
      $stmt->execute();
      $result = $stmt->get_result();
      if ($result->num_rows > 0) {
          header('Location: register.php?error=user with this email already exists');
          exit();
      } else {
          // Check if loyalty_points column exists
          $column_check = $conn->query("SHOW COLUMNS FROM users LIKE 'loyalty_points'");
          $column_exists = ($column_check && $column_check->num_rows > 0);
          
          if ($column_exists) {
              // If column exists, include it in the INSERT statement
              $stmt = $conn->prepare("INSERT INTO users (user_name, user_email, user_password, loyalty_points) VALUES (?, ?, ?, 10)");
              $stmt->bind_param('sss', $name, $email, $password);
          } else {
              // If column doesn't exist yet, use the original query
              $stmt = $conn->prepare("INSERT INTO users (user_name, user_email, user_password) VALUES (?, ?, ?)");
              $stmt->bind_param('sss', $name, $email, $password);
          }
          
          if ($stmt->execute()) {
              $user_id = $stmt->insert_id;
              $_SESSION['user_id'] = $user_id;
              $_SESSION['user_email'] = $email;
              $_SESSION['user_name'] = $name;
              $_SESSION['logged_in'] = true;
              
              // Initialize loyalty points for new users (starting with 10 points)
              $_SESSION['loyalty_points'] = 10;
              
              // If loyalty_points column doesn't exist, redirect to create it
              if (!$column_exists) {
                  header('location: add_loyalty_column.php');
                  exit();
              }
              
              // Recalculate cart with loyalty discount if cart exists
              if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                  calculatetotalcart();
              }
              
              header("location: account.php?message=you registered successfully");
              exit();
          } else {
              header('Location: register.php?error=cannot create an account at this moment');
              exit();
          }
      }
  }
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
     

        .decorative-circle {
            position: absolute;
            border-radius: 50%;
            z-index: 0;
            pointer-events: none;
            filter: blur(30px);
            opacity: 0.6;
        }

        .circle-1 {
            width: 400px;
            height: 400px;
            top: -150px;
            left: -100px;
            background: radial-gradient(circle, rgba(212,175,115,0.3) 0%, rgba(212,175,115,0) 70%);
            animation: float 12s ease-in-out infinite;
        }

        .circle-2 {
            width: 300px;
            height: 300px;
            bottom: 100px;
            right: 100px;
            background: radial-gradient(circle, rgba(212,175,115,0.2) 0%, rgba(212,175,115,0) 70%);
            animation: float 8s ease-in-out infinite reverse;
        }

        .circle-3 {
            width: 250px;
            height: 250px;
            top: 50%;
            left: 70%;
            background: radial-gradient(circle, rgba(180,180,180,0.15) 0%, rgba(180,180,180,0) 70%);
            animation: float 10s ease-in-out infinite 2s;
        }

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(20px, -20px) rotate(3deg); }
            66% { transform: translate(-15px, 15px) rotate(-3deg); }
            100% { transform: translate(0, 0) rotate(0deg); }
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd' opacity='0.05'%3E%3Cg fill='%23d4af73' fill-opacity='0.2'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            z-index: -1;
        }


        .register-section {
    max-width: 1200px;
    margin: 60px auto 40px; 
    padding: 30px;
    display: flex;
    align-items: flex-start;
    gap: 80px;
}


        .register-image {
            flex: 1;
    display: none;
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    height: 700px; 
    
    min-height: 700px; 
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);

        }


        @media (min-width: 992px) {
            .register-image {
                display: block;
            }
        }

        .register-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .register-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .register-form-container {
    background-color: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    padding: 30px; 
  height:700px;
    min-width: 500px;
}



.register-form-header {
    text-align: center;
    margin-bottom: 20px;
}


        .register-form-header h2 {
            font-size: 28px;
            color: #000;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .register-form-header p {
            color: #666;
            margin-top: 0;
        }

        .register-form .form-group {
    margin-bottom: 15px; 
}


        .register-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .register-form input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            font-family: 'Cairo', sans-serif;
            font-size: 15px;
            transition: border 0.3s ease;
        }

        .register-form input {
            outline: none;
            color:black;
        }

        .register-button {
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

        .register-button:hover {
            background-color: #333;
            transform: translateY(-2px);
        }

        .register-button i {
            margin-right: 8px;
        }

        .register-footer {
            text-align: center;
            margin-top: 25px;
            color: #666;
        }

        .register-footer a {
            color: #000;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .register-footer a:hover {
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
    </nav>

    
        
        
        <section class="register-section">
        <div class="decorative-circle circle-1"></div>
        <div class="decorative-circle circle-2"></div>
        <div class="decorative-circle circle-3"></div>
      

        <div class="register-content">
            <div class="register-form-container ">
                
                <div class="register-form-header">
                    <h2>Create an Account</h2>
                    <p>Join us and start renting furniture today</p>
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

                <form class="register-form" action="register.php" method="POST">
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Create a password" required>
                        <p class="password-requirements">Password must be at least 6 characters long</p>
                    </div>

                    <div class="form-group">
                        <label for="confirmpassword">Confirm Password</label>
                        <input type="password" id="confirmpassword" name="confirmpassword" placeholder="Confirm your password" required>
                    </div>

                    <button type="submit" class="register-button" name="register">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>

                <div class="register-footer">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
  <div class="register-image">
            <img src="https://i.pinimg.com/736x/8a/45/de/8a45de90410a3fd725648ecd0d5b7e0e.jpg" alt="Furniture Interior">
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


