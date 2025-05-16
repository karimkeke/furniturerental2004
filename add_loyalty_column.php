<?php
session_start();
include('connection.php');

// Check if the column already exists
$column_check = $conn->query("SHOW COLUMNS FROM users LIKE 'loyalty_points'");
$column_exists = ($column_check && $column_check->num_rows > 0);

if (!$column_exists) {
    // Add the loyalty_points column to users table
    $add_column = $conn->query("ALTER TABLE users ADD COLUMN loyalty_points INT DEFAULT 10 NOT NULL");
    
    if ($add_column) {
        // For existing users, update their loyalty points to match their current session value if available
        // or set to a default of 10 if no session value exists
        $users = $conn->query("SELECT user_id FROM users");
        $updated_users = 0;
        
        while ($user = $users->fetch_assoc()) {
            // Set default loyalty points
            $loyalty_points = 10;
            
            // If this is the logged in user and has points in session, use those instead
            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user['user_id'] && isset($_SESSION['loyalty_points'])) {
                $loyalty_points = $_SESSION['loyalty_points'];
            }
            
            $update = $conn->prepare("UPDATE users SET loyalty_points = ? WHERE user_id = ?");
            $update->bind_param("ii", $loyalty_points, $user['user_id']);
            if ($update->execute()) {
                $updated_users++;
            }
        }
        
        echo "Success: Loyalty points column added to the users table.<br>";
        echo "{$updated_users} users were updated with loyalty points.";
    } else {
        echo "Error: Could not add loyalty points column to the users table. " . $conn->error;
    }
} else {
    echo "The loyalty_points column already exists in the users table.";
}

// Add link to go back to homepage
echo "<p><a href='index.php'>Return to homepage</a></p>";
?> 