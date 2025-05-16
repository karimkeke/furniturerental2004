<?php
session_start();
include('connection.php');
$low_stock_query = "SELECT * FROM products WHERE product_quantity <= 3 ORDER BY product_quantity ASC";
$low_stock_result = $conn->query($low_stock_query);

$low_stock_count = $low_stock_result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Stock Items - Furniture Rental</title>
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
        
        .header-actions {
            display: flex;
            align-items: center;
        }
        
        .back-to-dashboard {
            display: flex;
            align-items: center;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            margin-right: 20px;
        }
        
        .back-to-dashboard i {
            margin-right: 5px;
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
        
        .content-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .content-card h2 {
            margin-bottom: 20px;
            color: var(--primary-color);
            font-size: 1.3rem;
            display: flex;
            align-items: center;
        }
        
        .content-card h2 i {
            margin-right: 10px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .data-table th, 
        .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .data-table th {
            font-weight: 600;
            color: var(--primary-color);
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .data-table tr:last-child td {
            border-bottom: none;
        }
        
        .stock-warning {
            color: var(--danger-color);
            font-weight: 600;
        }
        
        .action-btn {
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.85rem;
            margin-right: 5px;
            display: inline-flex;
            align-items: center;
        }
        
        .edit-btn {
            background-color: rgba(23, 162, 184, 0.1);
            color: var(--info-color);
        }
        
        .edit-btn:hover {
            background-color: rgba(23, 162, 184, 0.2);
        }
        
        .no-data {
            text-align: center;
            padding: 30px;
            color: var(--light-text);
        }
        
        .count-badge {
            background-color: var(--danger-color);
            color: white;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-left: 10px;
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
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include('admin_sidebar.php'); ?>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>Low Stock Products</h1>
                <div class="header-actions">
                    <a href="dashaboard.php" class="back-to-dashboard">
                        <i class="fas fa-tachometer-alt"></i> Back to Dashboard
                    </a>
                    <a href="index.php" class="back-to-site">
                        <i class="fas fa-external-link-alt"></i> View Main Site
                    </a>
                </div>
            </div>
            
            <div class="content-card">
                <h2>
                    <i class="fas fa-exclamation-triangle"></i> 
                    Low Stock Products
                    <?php if ($low_stock_count > 0): ?>
                        <span class="count-badge"><?php echo $low_stock_count; ?> items</span>
                    <?php endif; ?>
                </h2>
                
                <?php if ($low_stock_result && $low_stock_result->num_rows > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($product = $low_stock_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $product['product_id']; ?></td>
                                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category']); ?></td>
                                    <td><?php echo number_format($product['product_price'], 2); ?> DA</td>
                                    <td class="stock-warning"><?php echo $product['product_quantity']; ?></td>
                                    <td>
                                        <a href="admin_edit_product.php?id=<?php echo $product['product_id']; ?>"  class="action-btn edit-btn">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-data">
                        <p><i class="fas fa-check-circle" style="font-size: 2rem; color: var(--success-color);"></i></p>
                        <p>No low stock products found. All products have sufficient stock.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>