<?php
session_start();
include('connection.php');
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product_id = $_GET['delete'];
    $image_query = "SELECT product_image FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($image_query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product_image = $result->fetch_assoc()['product_image'];
        $delete_query = "DELETE FROM products WHERE product_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $product_id);
        
        if ($stmt->execute()) {
            $image_dir = "assets/imgs/";
            if (!empty($product_image) && file_exists($image_dir . $product_image)) {
                unlink($image_dir . $product_image);
            }
            $success_message = "Product has been deleted successfully.";
        } else {
            $error_message = "Failed to delete product.";
        }
    }
}

$results_per_page = 10;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$offset = ($page - 1) * $results_per_page;

$count_query = "SELECT COUNT(*) AS total FROM products";
$count_result = $conn->query($count_query);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $results_per_page);

$query = "SELECT * FROM products ORDER BY created_at DESC LIMIT ?, ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $offset, $results_per_page);
$stmt->execute();
$products = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Products - Furniture Rental</title>
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
        
       .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        
        .pagination a {
            display: inline-block;
            padding: 8px 16px;
            margin: 0 4px;
            text-decoration: none;
            color: var(--primary-color);
            background-color: white;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .pagination a:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .pagination a.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .add-btn {
            background: #4CAF50;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;

        }
        th {
            background-color: #f2f2f2;
        }
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
        }
        .action-btn {
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: -50px;
        }
        .edit-btn {
            background: #2196F3;
            
            color: white;
            margin-right: 5px;

        }
        .delete-btn {
            background: #f44336;
            color: white;
            
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
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
       
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include('admin_sidebar.php'); ?>
        
        <div class="admin-content">
            <div class="admin-header">
                <h1>Product Management</h1>
                <div class="header-actions">
                    <a href="dashaboard.php" class="back-to-dashboard">
                        <i class="fas fa-tachometer-alt"></i> Back to Dashboard
                    </a>
                    <a href="index.php" class="back-to-site">
                        <i class="fas fa-external-link-alt"></i> View Main Site
                    </a>
                </div>
            </div>
            
            <div class="container">
                <div class="header">
                    <a href="admin_add_product.php" class="add-btn">
                        <i class="fas fa-plus"></i> Add Product
                    </a>
                </div>

                <?php if(isset($success_message)): ?>
                    <div class="alert success">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($error_message)): ?>
                    <div class="alert error">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                        <th>Quantity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($products && $products->num_rows > 0): ?>
                            <?php while($product = $products->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <img src="assets/imgs/<?php echo $product['product_image']; ?>" 
                                             class="product-img" 
                                             alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                    </td>
                                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                    <td><?php echo number_format($product['product_price']); ?> DA</td>
                                    <td><?php echo $product['product_quantity']; ?></td>
                                    <td>
                                        <a href="admin_edit_product.php?id=<?php echo $product['product_id']; ?>" 
                                           class="action-btn edit-btn">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <button onclick="confirmDelete(<?php echo $product['product_id']; ?>)" 
                                                class="action-btn delete-btn">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No products found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="admin_products.php?page=<?php echo $page - 1; ?>">&lsaquo; Prev</a>
                    <?php endif; ?>
                    
                    <?php
                    $visible_pages = 5;
                    $start_page = max(1, $page - floor($visible_pages / 2));
                    $end_page = min($total_pages, $start_page + $visible_pages - 1);
                    
                    if ($end_page - $start_page + 1 < $visible_pages) {
                        $start_page = max(1, $end_page - $visible_pages + 1);
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <a href="admin_products.php?page=<?php echo $i; ?>" <?php echo ($i == $page) ? 'class="active"' : ''; ?>>
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="admin_products.php?page=<?php echo $page + 1; ?>">Next &rsaquo;</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
            
    <script>
        function confirmDelete(productId) {
            if(confirm('Are you sure you want to delete this product?')) {
                window.location.href = 'admin_products.php?delete=' + productId;
            }
        }
    </script>
</body>
</html>