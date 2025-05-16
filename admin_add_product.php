<?php
session_start();
include('connection.php');
$categories = [
    'accentchair' => 'Accent Chair',
    'bedframe' => 'Bed Frame',
    'coffeetables' => 'Coffee Tables',
    'cornerdesk' => 'Corner Desk',
    'desk' => 'Desk',
    'diningtables' => 'Dining Tables',
    'dresser' => 'Dresser',
    'officechair' => 'Office Chair',
    'rectangle' => 'Rectangle',
    'round' => 'Round',
    'sofas' => 'Sofas',
    'square' => 'Square',
    'sidetable' => 'Side Table'
];

$product_name = $category = $product_price = $product_quantity = $product_description = '';
$errors = [];
$success_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name'] ?? '');
    $category = $_POST['product_category'] ?? '';
    $product_price = (float)($_POST['product_price'] ?? 0);
    $product_quantity = (int)($_POST['product_quantity'] ?? 0);
    $product_description = trim($_POST['product_description'] ?? '');

    if (empty($product_name)) $errors[] = "Product name is required";
    if (empty($category) || !array_key_exists($category, $categories)) $errors[] = "Valid category is required";
    if ($product_price <= 0) $errors[] = "Valid price is required";
    if ($product_quantity < 0) $errors[] = "Valid quantity is required";
    if (empty($product_description)) $errors[] = "Description is required";

    $target_dir = "/Applications/XAMPP/xamppfiles/htdocs/Rental/assets/imgs/";
    $uploaded_images = [];
    $image_fields = ['product_image', 'product_image2', 'product_image3', 'product_image4'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0775, true)) {
            $errors[] = "Failed to create upload directory";
        }
    }

    if (!is_writable($target_dir)) {
        $errors[] = "Upload directory is not writable";
    }

    if (empty($errors)) {
        foreach ($image_fields as $index => $field) {
            if ($index > 0 && (!isset($_FILES[$field])) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
                $uploaded_images[$field] = null;
                continue;
            }

            if ($index === 0 && (!isset($_FILES[$field])) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
                $errors[] = "Primary image is required";
                continue;
            }

            if (isset($_FILES[$field])) {
                $file = $_FILES[$field];
                
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $errors[] = "Upload error for " . ($index === 0 ? "primary" : "additional") . " image";
                    continue;
                }
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                
                if (!in_array($mime_type, $allowed_types)) {
                    $errors[] = "Invalid file type for " . ($index === 0 ? "primary" : "additional") . " image";
                    continue;
                }

                if ($file['size'] > 5000000) {
                    $errors[] = "File too large for " . ($index === 0 ? "primary" : "additional") . " image";
                    continue;
                }

                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename = uniqid('product_', true) . '.' . $ext;
                $target_file = $target_dir . $filename;

              
                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    chmod($target_file, 0664); 
                    $uploaded_images[$field] = $filename;
                } else {
                    $errors[] = "Failed to move uploaded file";
                }
            }
        }
    }

    if (empty($errors)) {
        $query = "INSERT INTO products (product_name, category, product_price, product_quantity, product_description, 
                  product_image, product_image2, product_image3, product_image4, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("sssssssss", 
                $product_name, 
                $category, 
                $product_price, 
                $product_quantity, 
                $product_description, 
                $uploaded_images['product_image'], 
                $uploaded_images['product_image2'], 
                $uploaded_images['product_image3'], 
                $uploaded_images['product_image4']
            );
            
            if ($stmt->execute()) {
                $success_message = "Product added successfully!";
                $product_name = $category = $product_price = $product_quantity = $product_description = '';
            } else {
                $errors[] = "Database error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors[] = "Database prepare error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
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
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 15px;
            background-color: var(--secondary-color);
            color: var(--text-color);
            border-radius: 4px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background-color: #e9ecef;
        }
        
        .back-btn i {
            margin-right: 5px;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .alert i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        .form-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 30px;
            margin-bottom: 20px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.full-width {
            grid-column: span 2;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
        }
        
        .form-input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            font-family: 'Cairo', sans-serif;
            transition: border 0.3s ease;
        }
        
        .form-input:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        .form-textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            font-family: 'Cairo', sans-serif;
            min-height: 150px;
            resize: vertical;
        }
        
        .form-textarea:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        .form-select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            font-family: 'Cairo', sans-serif;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23333' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 12px;
        }
        
        .form-select:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        .custom-file-upload {
            display: block;
            width: 100%;
            padding: 10px 15px;
            text-align: center;
            background-color: #f8f9fa;
            border: 1px dashed #ccc;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 5px;
        }
        
        .custom-file-upload:hover {
            background-color: #e9ecef;
        }
        
        .custom-file-upload i {
            margin-right: 5px;
        }
        
        .image-preview {
            max-width: 100%;
            height: 150px;
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .image-help-text {
            margin-top: 5px;
            font-size: 0.85rem;
            color: var(--light-text);
        }
        
        .submit-btn {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .submit-btn:hover {
            background-color: var(--accent-color);
            transform: translateY(-2px);
        }
        
        .submit-btn i {
            margin-right: 5px;
        }
        
        .field-error {
            color: var(--danger-color);
            font-size: 0.85rem;
            margin-top: 5px;
        }
        
        .error-list {
            list-style-type: none;
            padding-left: 0;
        }
        
        .error-list li {
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }
        
        .error-list li:before {
            content: "\f071";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            margin-right: 8px;
            color: var(--danger-color);
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                width: 70px;
                overflow: visible;
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
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-group.full-width {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
                <div class="sidebar-user">
                    <?php echo $_SESSION['admin_name']; ?>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="admin_dashboard.php">
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
                        <span>Messages <?php if(isset($unread_messages_count) && $unread_messages_count > 0): ?><span class="badge" style="background-color: #dc3545; color: white; padding: 2px 6px; border-radius: 10px; font-size: 0.7rem;"><?php echo $unread_messages_count; ?></span><?php endif; ?></span>
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
                <h1>Add New Product</h1>
                <a href="admin_products.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
            </div>
            
            <?php if(isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if(!empty($errors)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <strong>Please fix the following errors:</strong>
                        <ul class="error-list">
                            <?php foreach($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="form-container">
                <form action="admin_add_product.php" method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="product_name" class="form-label">Product Name*</label>
                            <input type="text" id="product_name" name="product_name" class="form-input" value="<?php echo htmlspecialchars($product_name); ?>" required>
                        </div>
                        
                    
<div class="form-group">
    <label for="product_category" class="form-label">Category*</label>
    <select id="product_category" name="product_category" class="form-select" required>
        <option value="">Select a category</option>
        <?php foreach($categories as $key => $value): ?>
            <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $category == $key ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($value); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
                    
                        <div class="form-group">
                            <label for="product_price" class="form-label">Price (DA)*</label>
                            <input type="number" id="product_price" name="product_price" class="form-input" value="<?php echo htmlspecialchars($product_price); ?>" min="0" step="1" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="product_quantity" class="form-label">Quantity*</label>
                            <input type="number" id="product_quantity" name="product_quantity" class="form-input" value="<?php echo htmlspecialchars($product_quantity); ?>" min="0" step="1" required>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="product_description" class="form-label">Description*</label>
                            <textarea id="product_description" name="product_description" class="form-textarea" required><?php echo htmlspecialchars($product_description); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="product_image" class="form-label">Primary Image*</label>
                            <label for="product_image" class="custom-file-upload">
                                <i class="fas fa-cloud-upload-alt"></i> Choose Primary Image
                            </label>
                            <input type="file" id="product_image" name="product_image" accept="image/*" style="display: none;" onchange="previewImage(this, 'primary-image-preview')">
                            <div class="image-preview" id="primary-image-preview">
                                <i class="fas fa-image" style="font-size: 3rem; color: #ccc;"></i>
                            </div>
                            <div class="image-help-text">
                                Primary image will be used as the main display image. Max size: 5MB.
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="product_image2" class="form-label">Additional Image</label>
                            <label for="product_image2" class="custom-file-upload">
                                <i class="fas fa-cloud-upload-alt"></i> Choose Additional Image
                            </label>
                            <input type="file" id="product_image2" name="product_image2" accept="image/*" style="display: none;" onchange="previewImage(this, 'image-preview2')">
                            <div class="image-preview" id="image-preview2">
                                <i class="fas fa-image" style="font-size: 3rem; color: #ccc;"></i>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="product_image3" class="form-label">Additional Image</label>
                            <label for="product_image3" class="custom-file-upload">
                                <i class="fas fa-cloud-upload-alt"></i> Choose Additional Image
                            </label>
                            <input type="file" id="product_image3" name="product_image3" accept="image/*" style="display: none;" onchange="previewImage(this, 'image-preview3')">
                            <div class="image-preview" id="image-preview3">
                                <i class="fas fa-image" style="font-size: 3rem; color: #ccc;"></i>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="product_image4" class="form-label">Additional Image</label>
                            <label for="product_image4" class="custom-file-upload">
                                <i class="fas fa-cloud-upload-alt"></i> Choose Additional Image
                            </label>
                            <input type="file" id="product_image4" name="product_image4" accept="image/*" style="display: none;" onchange="previewImage(this, 'image-preview4')">
                            <div class="image-preview" id="image-preview4">
                                <i class="fas fa-image" style="font-size: 3rem; color: #ccc;"></i>
                            </div>
                        </div>
                        <div class="form-group full-width" style="text-align: center; margin-top: 20px;">
                            <button type="submit" class="submit-btn">
                                <i class="fas fa-plus-circle"></i> Add Product
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
    
    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Image Preview">`;
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.innerHTML = '<i class="fas fa-image" style="font-size: 3rem; color: #ccc;"></i>';
            }
        }
        
document.getElementById('product_category').addEventListener('change', function() {
    const newCategoryGroup = document.getElementById('new_category_group');
    if (this.value === 'new_category') {
        newCategoryGroup.style.display = 'block';
        document.getElementById('new_category_name').setAttribute('required', 'required');
    } else {
        newCategoryGroup.style.display = 'none';
        document.getElementById('new_category_name').removeAttribute('required');
    }
});
    </script>
</body>
</html> 