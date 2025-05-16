<?php
session_start();
include('connection.php');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_id'])) {
    header('location: login.php?message=Please login to access your messages');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['user_name'];

// Set default values
$messages = [];
$unread_count = 0;

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

// Mark messages as read
$table_check = $conn->query("SHOW TABLES LIKE 'messages'");
if ($table_check->num_rows > 0 && isset($_GET['mark_read'])) {
    try {
        $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE user_id = ? AND is_from_admin = 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    } catch (Exception $e) {
        // Ignore errors
    }
}

// Send a new message
if (isset($_POST['send_message'])) {
    $message_text = trim($_POST['message_text']);
    
    if (!empty($message_text)) {
        $table_check = $conn->query("SHOW TABLES LIKE 'messages'");
        if ($table_check->num_rows > 0) {
            try {
                $stmt = $conn->prepare("INSERT INTO messages (user_id, message_text, is_from_admin, is_read) VALUES (?, ?, 0, 0)");
                $stmt->bind_param("is", $user_id, $message_text);
                
                if ($stmt->execute()) {
                    // Redirect to prevent form resubmission
                    header('location: user_messages.php?success=Message sent successfully');
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

// Check if messages table exists before running queries
$table_check = $conn->query("SHOW TABLES LIKE 'messages'");
if ($table_check->num_rows > 0) {
    try {
        // Get all messages for the user
        $stmt = $conn->prepare("SELECT * FROM messages WHERE user_id = ? ORDER BY created_at ASC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        
        // Count unread messages
        $stmt = $conn->prepare("SELECT COUNT(*) as unread FROM messages WHERE user_id = ? AND is_from_admin = 1 AND is_read = 0");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $unread_result = $stmt->get_result();
        $unread_count = $unread_result->fetch_assoc()['unread'];
        
        // Update messages as read
        if ($unread_count > 0) {
            $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE user_id = ? AND is_from_admin = 1 AND is_read = 0");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        }
    } catch (Exception $e) {
        // If there's an error, just continue with empty data
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Furniture Rental</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* General page styling */
        body {
            background: #f8f9fa;
            font-family: 'Cairo', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
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
        /* Navbar adjustments to prevent overlap */
        .navbar {
            position: relative;
            z-index: 10;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Main message container */
        .message-page-container {
            max-width: 100%;
            padding: 30px 0;
            display: flex;
            justify-content: center;
            align-items: flex-start; /* Changed from center to flex-start */
            min-height: calc(100vh - 70px); /* Adjusted for navbar height */
            position: relative;
            z-index: 1;
            margin-top: 20px; /* Add space below navbar */
        }

        /* Message card */
        .message-card {
            width: 650px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 6px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin: 0 auto; /* Centers the card horizontally */
            position: relative; /* Add position relative */
        }

        /* Card header */
        .message-header {
            background: #333;
            color: white;
            padding: 22px 25px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .message-header h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 600;
        }

        .message-header p {
            margin: 8px 0 0;
            font-size: 15px;
            opacity: 0.9;
        }

        /* Alerts styling */
        .message-alert {
            padding: 15px;
            margin: 15px auto;
            border-radius: 8px;
            font-size: 15px;
            width: 90%;
            text-align: center;
        }

        .message-alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.2);
        }

        .message-alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.2);
        }

        /* Message content area */
        .message-content {
            height: 400px;
            overflow-y: auto;
            padding: 25px;
            background-color: #f9f9f9;
        }

        /* Empty messages state */
        .empty-message-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #6c757d;
            text-align: center;
            padding: 20px;
        }

        .empty-message-state i {
            font-size: 48px;
            margin-bottom: 20px;
            opacity: 0.5;
            color: #adb5bd;
        }

        .empty-message-state p {
            font-size: 16px;
            max-width: 80%;
        }

        /* Message bubbles container */
        .message-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Individual message container */
        .message-item {
            display: flex;
            max-width: 80%;
            clear: both;
            animation: fadeIn 0.3s ease;
        }

        /* Admin message (left aligned) */
        .message-admin {
            align-self: flex-start;
            margin-right: auto;
        }

        /* User message (right aligned) */
        .message-user {
            align-self: flex-end;
            margin-left: auto;
        }

        /* Message bubble styling */
        .message-bubble {
            padding: 15px 18px;
            border-radius: 18px;
            position: relative;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            word-wrap: break-word;
        }

        /* Admin message bubble */
        .message-admin .message-bubble {
            background-color: #333;
            color: white;
            border-bottom-left-radius: 4px;
        }

        /* User message bubble */
        .message-user .message-bubble {
            background-color: #e8f4fd;
            color: #333;
            border-bottom-right-radius: 4px;
        }

        /* Time display in messages */
        .message-time {
            display: block;
            font-size: 11px;
            margin-top: 5px;
            text-align: right;
            opacity: 0.7;
        }

        /* Message form */
        .message-form-container {
            padding: 18px 20px;
            background: #f7f7f7;
            border-top: 1px solid #eee;
        }

        /* Form layout */
        .message-form {
            display: flex;
            gap: 12px;
        }

        /* Message input field */
        .message-input {
            flex: 1;
            padding: 14px 18px;
            border: 1px solid #ddd;
            border-radius: 30px;
            font-family: inherit;
            font-size: 15px;
            resize: none;
            background: white;
        }

        .message-input:focus {
            outline: none;
            border-color: #333;
            box-shadow: 0 0 0 2px rgba(51,51,51,0.1);
        }

        /* Send button */
        .message-send-button {
            background: #333;
            color: white;
            border: none;
            border-radius: 30px;
            padding: 0 25px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .message-send-button:hover {
            background: #444;
            transform: translateY(-2px);
        }

        /* Animation for messages */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .message-card {
                width: 90%;
                margin: 20px;
            }
            
            .message-item {
                max-width: 85%;
            }
            
            .message-content {
                height: 350px;
            }
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
    </style>
</head>
<body>
    <!-- Navigation -->
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
                <a href="account.php">Home</a>
                <a href="cart.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count">
                        <?php echo isset($_SESSION['quantity']) ? $_SESSION['quantity'] : 0; ?>
                    </span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Message Container -->
    <div class="message-page-container">
        <div class="message-card">
            <!-- Header -->
            <div class="message-header">
                <h1>Messages</h1>
                <p>Communicate with our customer support team</p>
            </div>
            
            <!-- Message Content -->
            <div class="message-body">
                <!-- Alerts -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="message-alert message-alert-success"><?php echo $_GET['success']; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="message-alert message-alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <!-- Messages Area -->
                <div class="message-content" id="message-content">
                    <?php if (empty($messages)): ?>
                        <!-- Empty State -->
                        <div class="empty-message-state">
                            <i class="far fa-comments"></i>
                            <p>No messages yet. Start a conversation with our team!</p>
                        </div>
                    <?php else: ?>
                        <!-- Message List -->
                        <div class="message-list">
                            <?php foreach ($messages as $message): ?>
                                <div class="message-item <?php echo $message['is_from_admin'] ? 'message-admin' : 'message-user'; ?>">
                                    <div class="message-bubble">
                                        <?php echo htmlspecialchars($message['message_text']); ?>
                                        <span class="message-time">
                                            <?php echo date('M d, g:i a', strtotime($message['created_at'])); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Message Form -->
                <div class="message-form-container">
                    <form class="message-form" method="POST" action="">
                        <textarea class="message-input" name="message_text" placeholder="Type your message here..." required></textarea>
                        <button type="submit" name="send_message" class="message-send-button">Send</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-scroll to bottom of messages on page load
        document.addEventListener('DOMContentLoaded', function() {
            const messageContent = document.getElementById('message-content');
            if (messageContent) {
                messageContent.scrollTop = messageContent.scrollHeight;
            }
            
            // Focus on message input
            document.querySelector('.message-input').focus();
        });
    </script>
</body>
</html> 