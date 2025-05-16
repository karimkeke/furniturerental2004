<?php
session_start();
include('connection.php');

// Check if admin is logged in
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['admin_id'])) {
    header('location: admin.php?error=Please login to access admin panel');
    exit;
}

$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'];

// Set default values
$users = [];
$total_unread = 0;
$current_user = null;
$messages = [];

// Check if messages table exists and create it if it doesn't
$table_check = $conn->query("SHOW TABLES LIKE 'messages'");
if ($table_check->num_rows == 0) {
    // Create the messages table
    $create_table_sql = "CREATE TABLE IF NOT EXISTS `messages` (
        `message_id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `message_text` text NOT NULL,
        `is_from_admin` tinyint(1) NOT NULL DEFAULT 0,
        `is_read` tinyint(1) NOT NULL DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`message_id`),
        KEY `fk_message_user` (`user_id`),
        CONSTRAINT `fk_message_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    try {
        $conn->query($create_table_sql);
    } catch (Exception $e) {
        // Table creation failed - just continue with empty data
    }
}

// Check again if the table exists before running any queries
$table_check = $conn->query("SHOW TABLES LIKE 'messages'");
if ($table_check->num_rows > 0) {
    // Get all users with messages
    try {
        $users_query = "SELECT DISTINCT u.user_id, u.user_name, u.user_email, 
                        (SELECT COUNT(*) FROM messages WHERE user_id = u.user_id AND is_from_admin = 0 AND is_read = 0) as unread_count,
                        (SELECT MAX(created_at) FROM messages WHERE user_id = u.user_id) as last_message_time
                        FROM users u
                        JOIN messages m ON u.user_id = m.user_id
                        ORDER BY last_message_time DESC";
        $users_result = $conn->query($users_query);
        
        while ($row = $users_result->fetch_assoc()) {
            $users[] = $row;
        }
        
        // Get total unread messages count for all users
        $unread_query = "SELECT COUNT(*) as total_unread FROM messages WHERE is_from_admin = 0 AND is_read = 0";
        $unread_result = $conn->query($unread_query);
        $total_unread = $unread_result->fetch_assoc()['total_unread'];
    } catch (Exception $e) {
        // If there's an error, just continue with empty data
    }
}

// Handle viewing a specific user's messages
if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    
    // Get user details
    $user_query = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    
    if ($user_result->num_rows > 0) {
        $current_user = $user_result->fetch_assoc();
        
        // Check if messages table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'messages'");
        if ($table_check->num_rows > 0) {
            try {
                // Get all messages for this user
                $messages_query = "SELECT * FROM messages WHERE user_id = ? ORDER BY created_at ASC";
                $stmt = $conn->prepare($messages_query);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $messages_result = $stmt->get_result();
                
                while ($row = $messages_result->fetch_assoc()) {
                    $messages[] = $row;
                }
                
                // Mark unread messages as read
                $update_query = "UPDATE messages SET is_read = 1 WHERE user_id = ? AND is_from_admin = 0 AND is_read = 0";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
            } catch (Exception $e) {
                // If there's an error, just continue with empty data
            }
        }
    }
}

// Send a message to user
if (isset($_POST['send_message']) && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    $message_text = trim($_POST['message_text']);
    
    if (!empty($message_text)) {
        // Check if messages table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'messages'");
        if ($table_check->num_rows > 0) {
            try {
                $stmt = $conn->prepare("INSERT INTO messages (user_id, message_text, is_from_admin, is_read) VALUES (?, ?, 1, 0)");
                $stmt->bind_param("is", $user_id, $message_text);
                
                if ($stmt->execute()) {
                    // Redirect to prevent form resubmission
                    header("location: admin_messages.php?user_id=$user_id&success=Message sent successfully");
                    exit;
                } else {
                    $error = "Failed to send message. Please try again.";
                }
            } catch (Exception $e) {
                $error = "An error occurred while sending the message.";
            }
        } else {
            $error = "Message system is not set up yet.";
        }
    } else {
        $error = "Message cannot be empty";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Messages - Furniture Rental</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin_styles.css">
    <style>
        /* Additional styles specific to messages page */
        .messages-container {
            display: flex;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            height: calc(100vh - 150px);
            overflow: hidden;
        }
        
        .users-list {
            width: 300px;
            border-right: 1px solid #eee;
            overflow-y: auto;
        }
        
        .users-list-header {
            padding: 18px 20px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #f3f3f3;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .user-item:hover {
            background-color: #f5f5f5;
        }
        
        .user-item.active {
            background-color: #f0f7ff;
            border-left: 3px solid #0d6efd;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background-color: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #6c757d;
            font-weight: 600;
            flex-shrink: 0;
        }
        
        .user-info {
            flex: 1;
            overflow: hidden;
        }
        
        .user-name {
            font-weight: 600;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .user-email {
            font-size: 0.8rem;
            color: #6c757d;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .user-badge {
            margin-left: 10px;
        }
        
        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .chat-header {
            padding: 18px 25px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        
        .chat-header-info {
            flex: 1;
        }
        
        .chat-header-name {
            font-weight: 600;
            margin-bottom: 2px;
        }
        
        .chat-header-email {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .messages-area {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background-color: #f9f9f9;
        }
        
        .message-date-separator {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }
        
        .message-date-separator span {
            background-color: #f9f9f9;
            padding: 0 10px;
            font-size: 12px;
            color: #6c757d;
            position: relative;
            z-index: 1;
        }
        
        .message-date-separator:before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background-color: #e5e5e5;
            z-index: 0;
        }
        
        .message-item {
            max-width: 75%;
            padding: 12px 15px;
            border-radius: 18px;
            margin-bottom: 5px;
            position: relative;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .message-admin {
            align-self: flex-end;
            background-color: #333;
            color: white;
            border-bottom-right-radius: 5px;
        }
        
        .message-user {
            align-self: flex-start;
            background-color: white;
            color: #333;
            border-bottom-left-radius: 5px;
        }
        
        .message-time {
            font-size: 11px;
            opacity: 0.7;
            margin-top: 3px;
            display: block;
            text-align: right;
        }
        
        .message-form {
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
        }
        
        .message-input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 30px;
            font-family: inherit;
            font-size: 14px;
            resize: none;
        }
        
        .message-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .send-button {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 30px;
            padding: 0 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .send-button:hover {
            background: var(--accent-color);
            transform: translateY(-2px);
        }
        
        .no-user-selected {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            background-color: #f8f9fa;
        }
        
        .no-user-selected i {
            font-size: 50px;
            opacity: 0.3;
            margin-bottom: 15px;
        }
        
        .no-user-selected p {
            font-size: 16px;
        }
        
        .no-messages {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #6c757d;
        }
        
        .no-messages i {
            font-size: 40px;
            opacity: 0.3;
            margin-bottom: 15px;
        }
        
        .alert {
            padding: 12px 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 768px) {
            .messages-container {
                flex-direction: column;
                height: calc(100vh - 120px);
            }
            
            .users-list {
                width: 100%;
                height: 200px;
                border-right: none;
                border-bottom: 1px solid #eee;
            }
            
            .message-item {
                max-width: 85%;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include('admin_sidebar.php'); ?>
        
        <div class="admin-content">
            <div class="admin-header">
                <h1>Customer Messages</h1>
                <?php if ($total_unread > 0): ?>
                    <div class="badge"><?php echo $total_unread; ?> Unread</div>
                <?php endif; ?>
            </div>
            
            <div class="messages-container">
                <div class="users-list">
                    <div class="users-list-header">
                        <span>Customers</span>
                        <span class="badge"><?php echo count($users); ?></span>
                    </div>
                    
                    <?php if (empty($users)): ?>
                        <div class="user-item">
                            <p>No message conversations yet</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <a href="admin_messages.php?user_id=<?php echo $user['user_id']; ?>" class="user-item <?php echo (isset($_GET['user_id']) && $_GET['user_id'] == $user['user_id']) ? 'active' : ''; ?>">
                                <div class="user-avatar">
                                    <?php echo strtoupper(substr($user['user_name'], 0, 1)); ?>
                                </div>
                                <div class="user-info">
                                    <div class="user-name"><?php echo htmlspecialchars($user['user_name']); ?></div>
                                    <div class="user-email"><?php echo htmlspecialchars($user['user_email']); ?></div>
                                </div>
                                <?php if ($user['unread_count'] > 0): ?>
                                    <span class="badge user-badge"><?php echo $user['unread_count']; ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <?php if (isset($current_user)): ?>
                    <div class="chat-container">
                        <div class="chat-header">
                            <div class="chat-header-info">
                                <div class="chat-header-name"><?php echo htmlspecialchars($current_user['user_name']); ?></div>
                                <div class="chat-header-email"><?php echo htmlspecialchars($current_user['user_email']); ?></div>
                            </div>
                        </div>
                        
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success"><?php echo $_GET['success']; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <div class="messages-area" id="messages-area">
                            <?php if (empty($messages)): ?>
                                <div class="no-messages">
                                    <i class="far fa-comments"></i>
                                    <p>No messages in this conversation yet</p>
                                </div>
                            <?php else: ?>
                                <?php 
                                $current_day = "";
                                foreach ($messages as $message): 
                                    $message_day = date('Y-m-d', strtotime($message['created_at']));
                                    if ($message_day != $current_day) {
                                        $current_day = $message_day;
                                        $day_label = date('F j, Y', strtotime($message['created_at']));
                                        echo '<div class="message-date-separator"><span>' . $day_label . '</span></div>';
                                    }
                                ?>
                                    <div class="message-item <?php echo $message['is_from_admin'] ? 'message-admin' : 'message-user'; ?>">
                                        <?php echo htmlspecialchars($message['message_text']); ?>
                                        <span class="message-time">
                                            <?php echo date('g:i a', strtotime($message['created_at'])); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <form class="message-form" method="POST" action="">
                            <input type="hidden" name="user_id" value="<?php echo $current_user['user_id']; ?>">
                            <textarea class="message-input" name="message_text" placeholder="Type your reply here..." required></textarea>
                            <button type="submit" name="send_message" class="send-button">Send</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="no-user-selected">
                        <i class="far fa-comment-dots"></i>
                        <p>Select a customer to view conversation</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-scroll to bottom of messages
        document.addEventListener('DOMContentLoaded', function() {
            const messagesArea = document.getElementById('messages-area');
            if (messagesArea) {
                messagesArea.scrollTop = messagesArea.scrollHeight;
            }
            
            // Focus message input if available
            const messageInput = document.querySelector('.message-input');
            if (messageInput) {
                messageInput.focus();
            }
        });
    </script>
</body>
</html> 