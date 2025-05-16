<?php
session_start();
include('connection.php');
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_products.php");
    exit();
}

$product_id = (int)$_GET['id'];
$query = "SELECT * FROM products WHERE product_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: admin_products.php");
    exit();
}

$product = $result->fetch_assoc();
$categories = [
    'accentchair',
    'bedframe',
    'coffeetables',
    'cornerdesk',
    'desk',
    'diningtables',
    'dresser',
    'officechair',
    'rectangle',
    'round',
    'sofas',
    'square',
    'sidetable'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $price = (float)$_POST['price'];
    $quantity = (int)$_POST['quantity'];
    $description = $conn->real_escape_string($_POST['description']);
    $category = $conn->real_escape_string($_POST['category']);
    
    // Initialize image names with existing values
    $image1 = $product['product_image'];
    $image2 = $product['product_image2'];
    $image3 = $product['product_image3'];
    $image4 = $product['product_image4'];
    
    // Handle image uploads
    $target_dir = "assets/imgs/";
    
    // Process each image upload
    for ($i = 1; $i <= 4; $i++) {
        $field_name = "image_$i";
        if (!empty($_FILES[$field_name]['name'])) {
            $image = $_FILES[$field_name];
            $new_image_name = time() . '_' . $i . '_' . basename($image['name']);
            $target_file = $target_dir . $new_image_name;
            
            // Delete old image if exists
            $old_image = $product["product_image" . ($i > 1 ? $i : '')];
            if (!empty($old_image) && file_exists($target_dir . $old_image)) {
                unlink($target_dir . $old_image);
            }
            
            // Upload new image
            if (move_uploaded_file($image['tmp_name'], $target_file)) {
                ${"image$i"} = $new_image_name;
            } else {
                $error_message = "Failed to upload image $i.";
            }
        }
    }
    
    // Update product in database
    $update_query = "UPDATE products SET 
                    product_name = '$name', 
                    product_price = $price, 
                    product_quantity = $quantity, 
                    product_description = '$description', 
                    category = '$category',
                    product_image = '$image1',
                    product_image2 = '$image2',
                    product_image3 = '$image3',
                    product_image4 = '$image4'
                    WHERE product_id = $product_id";
    
    if ($conn->query($update_query)) {
        $_SESSION['success_message'] = "Product updated successfully!";
        header("Location: admin_edit_product.php?id=$product_id");
        exit();
    } else {
        $error_message = "Failed to update product: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Admin Panel</title>
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
        
        /* Form styles */
        .edit-form-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .form-header h2 {
            font-size: 1.3rem;
            color: var(--primary-color);
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
            border-radius: 4px;
            font-family: inherit;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .image-preview {
            margin-top: 15px;
            display: flex;
            align-items: center;
        }
        
        .image-preview img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 20px;
            border: 1px solid #eee;
        }
        
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }
        
        .file-input-wrapper input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-input-btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .file-input-btn:hover {
            background-color: var(--accent-color);
        }
        
        .file-name {
            margin-left: 10px;
            color: var(--light-text);
            font-size: 0.9rem;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--accent-color);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
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
        
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .two-columns {
                grid-template-columns: 1fr;
            }
        }
 

        
    
        /* [Keep all your existing CSS styles] */
        
        .image-preview-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 20px;
            max-width: 650px;
           
            margin-left: auto;
            margin-right: auto;
        }
        
        .image-preview-box {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .image-preview-box h4 {
            margin-bottom: 10px;
            color: var(--primary-color);
        }
        
        .image-preview-box img {
            max-width: 150px;
            max-height: 250px;
            object-fit: contain;
            margin-bottom: 10px;
            border: 1px solid #eee;
        }
        
        .file-input-wrapper {
            margin-top: 10px;
        }
        
        @media (max-width: 768px) {
            .image-preview-container {
                grid-template-columns: 1fr;
                max-width: 400px;
            }
        }

      
    </style>

</head>
<body>
<div class="admin-container">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <h2>Admin Panel</h2>
            <div class="sidebar-user">
                <?php echo $_SESSION['admin_name']; ?>
            </div>
        </div>
        
        <ul class="sidebar-menu">
            <li>
                <a href="dashaboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="admin_products.php" class="active">
                    <i class="fas fa-couch"></i>
                    <span>Products</span>
                </a>
            </li>
            <li>
                <a href="admin_categories.php">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
            </li>
            <li>
                <a href="admin_orders.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
            </li>
            <li>
                <a href="admin_users.php">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            <li>
                <a href="admin_messages.php">
                    <i class="fas fa-comments"></i>
                    <span>Messages</span>
                </a>
            </li>
            <li>
                <a href="admin.php?logout=1" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </aside>
    




    
    <main class="admin-content">
        <div class="admin-header">
            <h1>Edit Product</h1>
            <a href="index.php" class="back-to-site">
                <i class="fas fa-external-link-alt"></i> View Main Site
            </a>
        </div>

        <?php if(isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message']; 
                unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="edit-form-container">
            <div class="form-header">
                <h2>Product Information</h2>
                <a href="admin_products.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
            </div>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="two-columns">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="price">Price (DA)</label>
                        <input type="number" id="price" name="price" class="form-control" 
                               value="<?php echo htmlspecialchars($product['product_price']); ?>" step="0.01" min="0" required>
                    </div>
                </div>

                <div class="two-columns">
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" 
                               value="<?php echo htmlspecialchars($product['product_quantity']); ?>" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" class="form-control" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>"
                                    <?php echo ($cat == $product['category']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control"><?php echo htmlspecialchars($product['product_description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Product Images</label>
                    <div class="image-preview-container">
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <?php $image_field = 'product_image' . ($i > 1 ? $i : ''); ?>
                            <div class="image-preview-box">
                                <h4>Image <?php echo $i; ?></h4>
                                <?php if (!empty($product[$image_field])): ?>
                                    <img src="assets/imgs/<?php echo $product[$image_field]; ?>" 
                                         alt="Product image <?php echo $i; ?>" style="max-width: 100%; margin-bottom: 10px;">
                                <?php endif; ?>
                                <div class="file-input-wrapper">
                                    <button type="button" class="file-input-btn">
                                        <i class="fas fa-upload"></i> <?php echo !empty($product[$image_field]) ? 'Change' : 'Upload'; ?> Image
                                    </button>
                                    <input type="file" name="image_<?php echo $i; ?>" id="image_<?php echo $i; ?>" accept="image/*">
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="form-group" style="text-align: right;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </main>


<script>
    // Show selected file names for all image inputs
    for (let i = 1; i <= 4; i++) {
        document.getElementById(`image_${i}`).addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'No file selected';
            document.getElementById(`file-name-${i}`).textContent = fileName;
        });
    }
</script>
</body>
</html>
