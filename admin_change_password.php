<?php
session_start();
include('connection.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('location: admin.php');
    exit();
}

$message = '';
$error = '';

// Get total unread messages for sidebar
$total_unread = 0;
$table_check = $conn->query("SHOW TABLES LIKE 'messages'");
if ($table_check && $table_check->num_rows > 0) {
    $unread_query = "SELECT COUNT(*) as unread FROM messages WHERE is_from_admin = 0 AND is_read = 0";
    $unread_result = $conn->query($unread_query);
    $unread_messages = $unread_result->fetch_assoc()['unread'];
    $total_unread = $unread_messages;
}

// Handle password change form submission
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate form input
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All fields are required";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords don't match";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters";
    } else {
        // Verify current password
        $admin_id = $_SESSION['admin_id'];
        $stmt = $conn->prepare("SELECT admin_password FROM admins WHERE admin_id = ?");
        $stmt->bind_param('i', $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            if ($current_password === $admin['admin_password']) {
                // Update the password
                $update_stmt = $conn->prepare("UPDATE admins SET admin_password = ? WHERE admin_id = ?");
                $update_stmt->bind_param('si', $new_password, $admin_id);
                
                if ($update_stmt->execute()) {
                    $message = "Password updated successfully";
                } else {
                    $error = "Failed to update password: " . $conn->error;
                }
            } else {
                $error = "Current password is incorrect";
            }
        } else {
            $error = "Admin account not found";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Admin Password - Furniture Rental</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin_styles.css">
    <style>
        .password-form {
            background-color: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .form-title {
            margin-bottom: 20px;
            font-size: 1.5rem;
            color: var(--primary-color);
            position: relative;
            padding-bottom: 10px;
        }
        
        .form-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary-color);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
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
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
        }
        
        .password-requirements {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }
        
        .btn-submit {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 12px 20px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            font-family: 'Cairo', sans-serif;
        }
        
        .btn-submit:hover {
            background-color: #333;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.9rem;
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
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include('admin_sidebar.php'); ?>
        
        <div class="admin-content">
            <div class="admin-header">
                <h1>Change Admin Password</h1>
                <a href="index.php" class="back-to-site">
                    <i class="fas fa-home"></i> Back to Site
                </a>
            </div>
            
            <div class="password-form">
                <h2 class="form-title">Update Your Password</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                        <p class="password-requirements">Password must be at least 6 characters</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn-submit" name="change_password">
                        <i class="fas fa-key"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 