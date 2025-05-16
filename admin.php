<?php
session_start();
include('connection.php');
$login_error = $_GET['error'] ?? '';
$setup_message = '';
$logout_success = $_GET['login_success'] ?? '';
$email = '';

// Handle logout
if (isset($_GET['logout'])) {
    // Unset all of the session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Redirect to admin login page with a success message
    header('location: admin.php?login_success=Logged out successfully');
    exit;
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT admin_id, admin_name, admin_email, admin_password FROM admins WHERE admin_email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($admin_id, $adminname, $adminemail, $adminpassword);
        $stmt->fetch();
        if ($password == $adminpassword) { 
            $_SESSION['admin_id'] = $admin_id;
            $_SESSION['admin_email'] = $adminemail;
            $_SESSION['admin_name'] = $adminname;
            $_SESSION['logged_in'] = true;
            
            header('location: dashaboard.php?login_success=logged in successfully');
            exit();
        } else {
            header('location: admin.php?error=password is incorrect');
            exit();
        }
    } else {
        header('location: admin.php?error=email does not exist');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }
        
        .login-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;
            overflow: hidden;
        }
        
        .login-header {
            background-color: #000;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .login-header h1 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }
        
        .login-header p {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .login-form {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Cairo', sans-serif;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: #000;
            outline: none;
        }
        
        .btn-login {
            width: 100%;
            background-color: #000;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            font-family: 'Cairo', sans-serif;
        }
        
        .btn-login:hover {
            background-color: #333;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .alert i {
            margin-right: 8px;
            font-size: 1rem;
        }
        
        .back-to-site {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-to-site a {
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
        }
        
        .back-to-site a:hover {
            color: #000;
        }
        
        .back-to-site a i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Admin Panel</h1>
            <p>Furniture Rental System</p>
        </div>
        
        <div class="login-form">
            <?php if (!empty($login_error)): ?>
                <div class="alert alert-danger">
                    <?php echo $login_error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($setup_message)): ?>
                <div class="alert alert-success">
                    <?php echo $setup_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($logout_success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $logout_success; ?>
                </div>
            <?php endif; ?>
            
            <form action="" method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn-login" name="login">Login</button>
            </form>
            
            <div class="back-to-site">
                <a href="index.php">
                    <i class="fas fa-arrow-left"></i> Back to Main Site
                </a>
            </div>
        </div>
    </div>
</body>
</html> 