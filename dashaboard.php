<?php
session_start();
include('connection.php');


$users_query = "SELECT COUNT(*) as total_users FROM users";
$users_result = $conn->query($users_query);
$user_count = $users_result->fetch_assoc()['total_users'];
$products_query = "SELECT COUNT(*) as total_products FROM products";
$products_result = $conn->query($products_query);
$product_count = $products_result->fetch_assoc()['total_products'];

$orders_query = "SELECT COUNT(*) as total_orders FROM orders";
$orders_result = $conn->query($orders_query);
$order_count = $orders_result->fetch_assoc()['total_orders'];

$revenue_query = "SELECT SUM(total_price) as total_revenue FROM orders";
$revenue_result = $conn->query($revenue_query);
$total_revenue = $revenue_result->fetch_assoc()['total_revenue'];
  
$low_stock_query = "SELECT * FROM products WHERE product_quantity <= 3 ORDER BY product_quantity ASC";
$low_stock_result = $conn->query($low_stock_query);

$recent_orders_query = "SELECT o.id, o.created_at, o.total_price, u.user_name, p.product_name 
                         FROM orders o 
                         JOIN users u ON o.user_id = u.user_id 
                         JOIN products p ON o.product_id = p.product_id 
                         ORDER BY o.created_at  ASC";
$recent_orders_result = $conn->query($recent_orders_query);

// Check if messages table exists and get unread messages count
$unread_messages = 0;
$table_check = $conn->query("SHOW TABLES LIKE 'messages'");
if ($table_check && $table_check->num_rows > 0) {
    $unread_query = "SELECT COUNT(*) as unread FROM messages WHERE is_from_admin = 0 AND is_read = 0";
    $unread_result = $conn->query($unread_query);
    $unread_messages = $unread_result->fetch_assoc()['unread'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Furniture Rental</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin_styles.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --header-height: 60px;
            --primary-color: #000;
            --secondary-color: #f8f9fa;
            --accent-color: #333;
            --text-color: #333;
            --light-text: #666;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --success-color: #28a745;
            --info-color: #17a2b8;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--secondary-color);
            color: var(--text-color);
            min-height: 100vh;
        }
        

        .admin-container {
            display: flex;
            min-height: 100vh;
        }


        .admin-sidebar {
            width: var(--sidebar-width);
            background-color: var(--primary-color);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-header h2 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .sidebar-user {
            font-size: 0.85rem;
            opacity: 0.8;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .sidebar-menu a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 4px solid white;
        }
        
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .badge {
            background-color: #dc3545;
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 0.7rem;
        }
        
        .logout-btn {
            margin-top: 20px;
            padding: 12px 20px;
            background-color: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
        }
        
        .admin-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
        }
        
        .admin-header {
            background-color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-header h1 {
            font-size: 1.5rem;
            color: var(--primary-color);
        }
        
        .back-to-site {
            display: flex;
            align-items: center;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-to-site i {
            margin-right: 5px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.5rem;
        }
        
        .products-icon { background-color: rgba(23, 162, 184, 0.2); color: #17a2b8; }
        .users-icon { background-color: rgba(40, 167, 69, 0.2); color: #28a745; }
        .orders-icon { background-color: rgba(255, 193, 7, 0.2); color: #ffc107; }
        .revenue-icon { background-color: rgba(0, 0, 0, 0.2); color: #000; }
        
        .stat-info h3 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }
        
        .stat-info p {
            color: var(--light-text);
            font-size: 0.9rem;
        }
        
        .dashboard-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .dashboard-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .dashboard-card h2 {
            margin-bottom: 15px;
            color: var(--primary-color);
            font-size: 1.2rem;
            display: flex;
            align-items: center;
        }
        
        .dashboard-card h2 i {
            margin-right: 10px;
        }
        
        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .dashboard-table th, 
        .dashboard-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .dashboard-table th {
            font-weight: 600;
            color: var(--primary-color);
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .dashboard-table tr:last-child td {
            border-bottom: none;
        }
        
        .stock-warning {
            color: var(--danger-color);
            font-weight: 600;
        }
        
        .view-all-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        
        .view-all-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 992px) {
            .dashboard-sections {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                width: 70px;
            }
            
            .sidebar-header h2,
            .sidebar-user,
            .sidebar-menu span {
                display: none;
            }
            
            .admin-content {
                margin-left: 70px;
            }
            
            .sidebar-menu a {
                padding: 15px;
                justify-content: center;
            }
            
            .sidebar-menu i {
                margin-right: 0;
                font-size: 1.2rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include('admin_sidebar.php'); ?>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>Dashboard</h1>
                <a href="index.php" class="back-to-site">
                    <i class="fas fa-external-link-alt"></i> View Main Site
                </a>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(220, 53, 69, 0.1);">
                        <i class="fas fa-user" style="color: #dc3545;"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $user_count; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(40, 167, 69, 0.1);">
                        <i class="fas fa-couch" style="color: #28a745;"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $product_count; ?></h3>
                        <p>Total Products</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(23, 162, 184, 0.1);">
                        <i class="fas fa-shopping-cart" style="color: #17a2b8;"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $order_count; ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(255, 193, 7, 0.1);">
                        <i class="fas fa-dollar-sign" style="color: #ffc107;"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo number_format($total_revenue, 2); ?> MAD</h3>
                        <p>Total Revenue</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(111, 66, 193, 0.1);">
                        <i class="fas fa-comments" style="color: #6f42c1;"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $unread_messages; ?></h3>
                        <p>Unread Messages</p>
                        <?php if ($unread_messages > 0): ?>
                            <a href="admin_messages.php" class="small-link">View Messages</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-sections">
                <div class="dashboard-card">
                    <h2><i class="fas fa-exclamation-triangle"></i> Low Stock Products</h2>
                    
                    <?php if($low_stock_result && $low_stock_result->num_rows > 0): ?>
                        <table class="dashboard-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $low_stock_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                                        <td class="<?php echo $row['product_quantity'] <= 3 ? 'stock-warning' : ''; ?>">
                                            <?php echo $row['product_quantity']; ?> units
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No low stock products found.</p>
                    <?php endif; ?>
                    
                    <a href="lowstocks.php" class="view-all-link">View All Low Stock Products</a>
                </div>
                <div class="dashboard-card">
                    <h2><i class="fas fa-clock"></i> Recent Orders</h2>
                    
                    <?php if($recent_orders_result && $recent_orders_result->num_rows > 0): ?>
                        <table class="dashboard-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Product</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $recent_orders_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                        <td><?php echo number_format($row['total_price']); ?> DA</td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No recent orders found.</p>
                    <?php endif; ?>
                    
                    <a href="admin_orders.php" class="view-all-link">View All Orders</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 