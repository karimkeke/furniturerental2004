<?php
session_start();
include('connection.php');

if (isset($_GET['logout'])) {
         
    session_destroy(); 
    
    header('location: login.php?message=You have been successfully logged out');
    exit;
}

if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id=?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result();
} else {
    header("Location: product.php");
    exit();
}

function calculatetotalcart() {
    $total = 0;
    $total_quantity = 0;

    if(isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach($_SESSION['cart'] as $product) {
            $total += $product['product_price'] * $product['product_quantity'];
            $total_quantity += $product['product_quantity'];
        }
    }

    $_SESSION['total'] = $total;
    $_SESSION['quantity'] = $total_quantity;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Single Product</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

<style>
:root {
    --primary-color: #000;
    --secondary-color: #e67e22;
    --secondary-gradient: linear-gradient(135deg, var(--secondary-color) 0%, var(--secondary-color) 100%);
    --primary-gradient: linear-gradient(135deg, var(--primary-color) 0%, #2c3e50 100%);
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

.register-btn {
    background-color: var(--secondary-color);
    color: white;
    border: none;
    padding: 0.7rem 1.8rem;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(230, 126, 34, 0.2);
}

.register-btn:hover {
    background-color: var(--secondary-color);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(230, 126, 34, 0.3);
}

.register-btn:active {
    transform: translateY(0);
}

/* Breadcrumb styles */
.breadcrumb {
    display: flex;
    align-items: center;
    margin-bottom: 30px;
    font-size: 14px;
    color: #777;
    flex-wrap: wrap;
}

.breadcrumb a {
    color: var(--primary-color);
    text-decoration: none;
    transition: color 0.2s ease;
}

.breadcrumb a:hover {
    color: var(--secondary-color);
}

.breadcrumb-separator {
    margin: 0 10px;
    color: #ccc;
}

.single-product {
    margin: 120px auto 60px;
    background-color: #fff;
    padding: 40px;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.product-layout {
    display: flex;
    align-items: flex-start;
    gap: 60px;
    flex-wrap: wrap;
}

.col-6 {
    flex: 1;
    min-width: 300px;
}

.img-container {
    position: relative;
    overflow: hidden;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    width: 100%;
    max-width: 425px;
    margin: 0 auto;
    display: block;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.img-container:hover {
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.main-img {
    width: 100%;
    height: 425px;
    object-fit: cover;
    transition: transform 0.5s ease;
    cursor: zoom-in;
    display: block;
}

.img-container:hover .main-img {
    transform: scale(1.05);
}

.img-overlay {
    position: absolute;
    top: 15px;
    left: 15px;
    z-index: 2;
}

.product-badge {
    display: inline-block;
    padding: 8px 15px;
    background: var(--secondary-gradient);
    color: white;
    font-weight: 600;
    font-size: 13px;
    border-radius: 30px;
    margin-right: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.img-zoom-lens {
    position: absolute;
    border: 2px solid var(--secondary-color);
    width: 100px;
    height: 100px;
    background-color: rgba(255, 255, 255, 0.4);
    cursor: none;
    display: none;
    border-radius: 50%;
    pointer-events: none;
    z-index: 5;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.small-img-group {
    display: flex;
    gap: 15px;
    margin-top: 25px;
    flex-wrap: wrap;
    justify-content: center;
    max-width: 425px;
    margin-left: auto;
    margin-right: auto;
}

.small-img {
    width: 90px;
    height: 90px;
    cursor: pointer;
    border-radius: 12px;
    object-fit: cover;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    border: 2px solid transparent;
    opacity: 0.8;
}

.small-img:hover, .small-img.active {
    transform: translateY(-5px);
    border-color: var(--secondary-color);
    opacity: 1;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.product-details {
    flex: 1;
    max-width: 500px;
    position: relative;
}

.product-category {
    display: inline-block;
    color: var(--secondary-color);
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 10px;
}

.product-details h3 {
    font-size: 32px;
    margin-bottom: 15px;
    color: var(--primary-color);
    font-weight: 700;
    position: relative;
    padding-bottom: 15px;
}

.product-details h3::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 3px;
    background-color: var(--secondary-color);
    border-radius: 3px;
}

.product-rating {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.stars {
    color: #ffc107;
    margin-right: 10px;
}

.review-count {
    color: #777;
    font-size: 14px;
}

.product-details h2 {
    font-size: 28px;
    color: var(--secondary-color);
    margin: 25px 0;
    font-weight: 600;
    display: inline-block;
    padding: 8px 16px;
    background-color: rgba(230, 126, 34, 0.1);
    border-radius: 8px;
}

.product-details p {
    font-size: 16px;
    color: #555;
    line-height: 1.8;
    margin-bottom: 30px;
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 10px;
    border-left: 4px solid var(--accent-color);
}

.product-features {
    margin-bottom: 30px;
}

.features-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 15px;
}

.features-list {
    list-style: none;
    padding: 0;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
}

.feature-item {
    display: flex;
    align-items: center;
    padding: 8px 0;
}

.feature-icon {
    color: var(--accent-color);
    margin-right: 10px;
    font-size: 16px;
}

.availability {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    background-color: #e8f5e9;
    color: #2e7d32;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 20px;
}

.availability i {
    margin-right: 6px;
}

.input-group {
    display: flex;
    align-items: center;
    gap: 30px; 
    margin-bottom: 35px;
    flex-wrap: wrap;
}

.input-field {
    display: flex;
    flex-direction: column;
    background-color: #f9f9f9;
    padding: 15px;
    border-radius: 10px;
    min-width: 170px;
    transition: all 0.3s ease;
}

.input-field:hover {
    background-color: #f0f0f0;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.input-field label {
    font-size: 16px;
    color: var(--primary-color);
    font-weight: bold;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
}

.input-field label::before {
    content: '\f534';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    margin-right: 8px;
    color: var(--secondary-color);
}

.input-field:nth-child(2) label::before {
    content: '\f073';
}

.input-field select {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 8px;
    cursor: pointer;
    background-color: white;
    transition: all 0.3s ease;
    color: var(--dark-color);
}

.input-field select:focus {
    outline: none;
    border-color: var(--secondary-color);
    box-shadow: 0 0 0 2px rgba(230, 126, 34, 0.2);
}

.action-buttons {
    display: flex;
    gap: 15px;
}

.buy-btn {
    background: var(--secondary-gradient);
    color: white;
    padding: 16px 30px;
    border: none;
    cursor: pointer;
    font-size: 18px;
    border-radius: 10px;
    transition: all 0.3s ease;
    width: 100%;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 1px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(230, 126, 34, 0.3);
}

.buy-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: 0.5s;
}

.buy-btn:hover {
    background: linear-gradient(135deg, var(--secondary-color) 0%, var(--secondary-color) 100%);
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(230, 126, 34, 0.3);
}

.buy-btn:hover::before {
    left: 100%;
}

.buy-btn:active {
    transform: translateY(0);
}

.wishlish-btn {
    background: #f0f0f0;
    color: var(--primary-color);
    border: none;
    border-radius: 10px;
    padding: 0 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.wishlish-btn:hover {
    background: #e0e0e0;
    color: var(--secondary-color);
}

.share-product {
    margin-top: 30px;
    display: flex;
    align-items: center;
}

.share-label {
    font-size: 14px;
    color: #777;
    margin-right: 15px;
}

.social-share {
    display: flex;
    gap: 10px;
}

.social-share a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    color: white;
    font-size: 14px;
    transition: all 0.3s ease;
}

.facebook {
    background-color: #3b5998;
}

.twitter {
    background-color: #1da1f2;
}

.pinterest {
    background-color: #bd081c;
}

.social-share a:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
}

/* Related products section */
.related-products {
    margin-top: 80px;
}

.section-title {
    text-align: center;
    font-size: 28px;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 40px;
    position: relative;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background-color: var(--secondary-color);
    border-radius: 3px;
}

.products-slider {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 30px;
    margin-top: 30px;
}

.product-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.product-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}

.product-card-img {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.product-card-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.product-card:hover .product-card-img img {
    transform: scale(1.1);
}

.product-card-body {
    padding: 20px;
}

.product-card-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 10px;
}

.product-card-price {
    font-size: 16px;
    font-weight: 700;
    color: var(--secondary-color);
    margin-bottom: 15px;
}

.product-card-btn {
    display: block;
    width: 100%;
    padding: 10px;
    background: var(--secondary-color);
    color: white;
    text-align: center;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.product-card-btn:hover {
    background: var(--secondary-color);
}

.dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            min-width: 160px; 
            padding: 8px 0;
            z-index: 100;
        }

        .dropdown-content a {
            display: block;
            padding: 8px 12px;
            font-size: 14px; 
            color: #333;
            text-decoration: none;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        .dropdown-content p {
            padding: 8px 12px;
            font-weight: bold;
            font-size: 14px;
            color: #555;
            margin: 0;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }
        .account-icon {
    position: relative;
    font-size: 22px;
    color: #000;
    text-decoration: none;
    margin-left: 20px;
}

.account-icon i {
    font-size: 26px;
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

#zoom-result {
    position: fixed;
    border: 3px solid var(--secondary-color);
    width: 350px;
    height: 350px;
    background-repeat: no-repeat;
    z-index: 1000;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    border-radius: 10px;
    pointer-events: none;
    display: none;
    background-color: white;
    opacity: 1;
}

@media (max-width: 992px) {
    .social-share {
        flex-wrap: wrap;
    }
    
    .products-slider {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    }
    
    .img-container {
        max-width: 100%;
    }
    
    .main-img {
        height: auto;
        aspect-ratio: 1/1;
    }
}

@media (max-width: 768px) {
    .single-product {
        padding: 20px;
    }
    
    .product-layout {
        gap: 30px;
    }
    
    .col-6 {
        width: 100%;
        max-width: 100%;
    }
    
    .img-container {
        margin: 0 auto;
    }
    
    .product-details h3 {
        font-size: 24px;
    }
    
    .product-details h2 {
        font-size: 22px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .wishlish-btn {
        width: 100%;
        padding: 12px;
        margin-top: 10px;
    }
    
    .products-slider {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
    
    .breadcrumb {
        margin-bottom: 20px;
    }
}

@media (max-width: 576px) {
    .input-group {
        flex-direction: column;
        gap: 15px;
    }
    
    .input-field {
        width: 100%;
    }
    
    .small-img {
        width: 70px;
        height: 70px;
    }
    
    .products-slider {
        grid-template-columns: 1fr;
    }
}

.add-to-cart:hover {
    background-color: var(--secondary-color);
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(230, 126, 34, 0.3);
}

.image-gallery-pagination .active {
    background: linear-gradient(135deg, var(--secondary-color) 0%, var(--secondary-color) 100%);
    color: white;
}

.checkout-btn:hover {
    background: var(--secondary-color);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
}
</style>
</head>
<body>
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
     
    <?php if(!isset($_SESSION['logged_in'])) : ?>
        <a href="index.php#contact">Contact Us</a>
    <?php endif; ?>

    <a href="cart.php" class="cart-icon">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-count">
            <?php echo isset($_SESSION['quantity']) ? $_SESSION['quantity'] : 0; ?>
        </span>
    </a>

    <?php if(isset($_SESSION['logged_in'])) : ?>
        <div class="dropdown" style="display: inline-block;">
            <a href="#" class="account-icon">
                <i class="fas fa-user"></i>
            </a>

            <div class="dropdown-content">
            <a href="account.php">Account Details</a>

                <a href="account.php?logout=1" name="logout" id="logout-btn">Logout</a>
            </div>
        </div>
    <?php else : ?>
        <button class="register-btn" onclick="window.location.href='register.php'">Register</button>
    <?php endif; ?>
</div>

         
    </div>
</nav>         
<section class="container single-product">
    <?php while ($row = $product->fetch_assoc()) { 
        
    ?>
    <!-- Breadcrumb navigation -->
   

    <div class="row product-layout">
        <div class="col-6">
            <div class="img-container">
                <div class="img-zoom-lens"></div>
                <img class="main-img" src="assets/imgs/<?php echo $row['product_image']; ?>" id="mainImg">
            </div>
            <div class="small-img-group">
                <img src="assets/imgs/<?php echo $row['product_image']; ?>" class="small-img active">
                <img src="assets/imgs/<?php echo $row['product_image2']; ?>" class="small-img">
                <img src="assets/imgs/<?php echo $row['product_image3']; ?>" class="small-img">
                <img src="assets/imgs/<?php echo $row['product_image4']; ?>" class="small-img">
            </div>
        </div>

        <div class="col-6 product-details">
            <?php if(isset($category['category_name'])): ?>
            <span class="product-category"><?php echo $category['category_name']; ?></span>
            <?php endif; ?>
            
            <h3><?php echo $row['product_name']; ?></h3>
            
            <div class="product-rating">
                <div class="stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                </div>
                <span class="review-count">4.5/5 (24 reviews)</span>
            </div>
            
            <h2><i class="fas fa-tag"></i> Rent for <?php echo number_format($row['product_price']); ?> DA/MONTH</h2>
            
            <p><i class="fas fa-info-circle"></i> <?php echo $row['product_description']; ?></p>
            
            <div class="availability">
                <i class="fas fa-check-circle"></i> In Stock (<?php echo $row['product_quantity']; ?> available)
            </div>
            
            <div class="product-features">
                <h4 class="features-title">Key Features</h4>
                <ul class="features-list">
                    <li class="feature-item">
                        <i class="fas fa-check feature-icon"></i>
                        <span>Free Delivery</span>
                    </li>
                    <li class="feature-item">
                        <i class="fas fa-check feature-icon"></i>
                        <span>Easy Returns</span>
                    </li>
                    <li class="feature-item">
                        <i class="fas fa-check feature-icon"></i>
                        <span>Installation Service</span>
                    </li>
                    <li class="feature-item">
                        <i class="fas fa-check feature-icon"></i>
                        <span>Quality Warranty</span>
                    </li>
                </ul>
            </div>
            
            <form method="POST" action="cart.php" class="form-group">
                <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                <input type="hidden" name="product_image" value="<?php echo $row['product_image']; ?>">
                <input type="hidden" name="product_name" value="<?php echo $row['product_name']; ?>">
                <input type="hidden" name="product_price" value="<?php echo $row['product_price']; ?>">

                <div class="input-group">
                    <div class="input-field">
                        <label for="quantity">Quantity:</label>
                        <select id="quantity" name="quantity">
                            <?php
                            $product_id = $_GET['product_id']; 
                            $query = "SELECT product_quantity FROM products WHERE product_id = $product_id";
                            $result = $conn->query($query);
                            $row = $result->fetch_assoc();
                            
                            $available_quantity = $row['product_quantity'];

                            for ($i = 1; $i <= $available_quantity; $i++) {
                                echo "<option value='$i'>$i</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="input-field">
                        <label for="rental_length">Rental Length:</label>
                        <select name="rental_length" id="rental_length">
                            <?php 
                            $rental_options = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 18, 24]; 
                            foreach ($rental_options as $months) {
                                echo "<option value='$months'>$months months</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="action-buttons">
                    <button class="buy-btn" type="submit" name="add_to_cart">
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                    <button type="button" class="wishlish-btn">
                        <i class="far fa-heart"></i>
                    </button>
                </div>
            </form>
            
        </div>
    </div>

    <!-- Image zoom result container -->
    <div id="zoom-result"></div>

    <?php } ?>
</section>

<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p><i class="fas fa-phone"></i> +213 XX XXX XXXX</p>
                <p><i class="fas fa-envelope"></i> info@furniture-rental.dz</p>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Furniture Rental. All Rights Reserved</p>
        </div>
    </div>
</footer>

<script>
// Image gallery functionality
var mainImg = document.getElementById("mainImg");
var smallImgs = document.querySelectorAll(".small-img");
var zoomLens = document.querySelector(".img-zoom-lens");
var zoomResult = document.getElementById("zoom-result");

smallImgs.forEach(img => {
    img.addEventListener("click", function() {
        // Remove active class from all thumbnails
        smallImgs.forEach(thumb => thumb.classList.remove('active'));
        // Add active class to clicked thumbnail
        this.classList.add('active');
        
        mainImg.src = img.src;
        
        // Add smooth transition effect
        mainImg.style.opacity = "0.8";
        setTimeout(() => {
            mainImg.style.opacity = "1";
        }, 100);
        
        // Update zoom background image when changing thumbnails
        if (zoomResult) {
            zoomResult.style.backgroundImage = "url('" + img.src + "')";
            if (typeof updateZoomSize === 'function') {
                updateZoomSize();
            }
        }
    });
});

// Image zoom functionality
function imageZoom() {
    var img = document.getElementById("mainImg");
    var imgContainer = document.querySelector(".img-container");
    var lens = document.querySelector(".img-zoom-lens");
    var result = document.getElementById("zoom-result");
    
    if (!img || !result) return;
    
    // Create lens if it doesn't exist
    if (!lens) {
        lens = document.createElement("div");
        lens.setAttribute("class", "img-zoom-lens");
        imgContainer.appendChild(lens);
    }
    
    // Calculate the ratio between result DIV and lens for higher quality zoom
    var cx = 4; // Increased from 3 to 4 for better quality
    var cy = 4;
    
    function updateZoomSize() {
        // Set background properties for the result DIV
        result.style.backgroundImage = "url('" + img.src + "')";
        result.style.backgroundSize = (img.width * cx) + "px " + (img.height * cy) + "px";
        result.style.backgroundRepeat = "no-repeat";
        result.style.backgroundOrigin = "border-box";
    }
    
    // Make updateZoomSize globally accessible
    window.updateZoomSize = updateZoomSize;
    
    // Initial setup
    updateZoomSize();
    
    // Handle window resize
    window.addEventListener('resize', updateZoomSize);
    
    // Mouse event listeners
    img.addEventListener("mousemove", moveLens);
    
    img.addEventListener("mouseenter", function() {
        lens.style.display = "block";
        result.style.display = "block";
        updateZoomSize(); // Ensure zoom is correctly sized
    });
    
    img.addEventListener("mouseleave", function() {
        lens.style.display = "none";
        result.style.display = "none";
    });
    
    function moveLens(e) {
        var pos, x, y;
        
        // Prevent any other actions that may occur when moving over the image
        e.preventDefault();
        
        // Get the cursor's position on the image
        pos = getCursorPos(e);
        
        // Calculate the position of the lens
        x = pos.x - (lens.offsetWidth / 2);
        y = pos.y - (lens.offsetHeight / 2);
        
        // Prevent the lens from being positioned outside the image
        if (x > img.width - lens.offsetWidth) {x = img.width - lens.offsetWidth;}
        if (x < 0) {x = 0;}
        if (y > img.height - lens.offsetHeight) {y = img.height - lens.offsetHeight;}
        if (y < 0) {y = 0;}
        
        // Set the position of the lens
        lens.style.left = x + "px";
        lens.style.top = y + "px";
        
        // Display what the lens "sees" with higher quality
        result.style.backgroundPosition = "-" + (x * cx) + "px -" + (y * cy) + "px";
        
        // Position the result near the cursor but ensure it stays on screen
        var windowWidth = window.innerWidth;
        var resultWidth = result.offsetWidth;
        
        // Calculate left position to keep zoom result on screen
        var leftPos = (e.pageX + 20);
        if (leftPos + resultWidth > windowWidth - 20) {
            leftPos = e.pageX - resultWidth - 20;
        }
        
        result.style.left = leftPos + "px";
        result.style.top = Math.max(70, e.pageY - 150) + "px"; // Keep away from top navigation
    }
    
    function getCursorPos(e) {
        var a, x = 0, y = 0;
        e = e || window.event;
        
        // Get the x and y positions of the image
        a = img.getBoundingClientRect();
        
        // Calculate the cursor's x and y coordinates, relative to the image
        x = e.pageX - a.left - window.pageXOffset;
        y = e.pageY - a.top - window.pageYOffset;
        
        return {x : x, y : y};
    }
}

// Initialize zoom when the page is loaded
window.addEventListener("load", imageZoom);
window.addEventListener("resize", imageZoom);

// Wishlist button click animation
document.querySelector('.wishlish-btn').addEventListener('click', function() {
    const icon = this.querySelector('i');
    if (icon.classList.contains('far')) {
        icon.classList.remove('far');
        icon.classList.add('fas');
        icon.style.color = '#e74c3c';
        
        // Add animation
        this.style.transform = 'scale(1.2)';
        setTimeout(() => {
            this.style.transform = 'scale(1)';
        }, 200);
    } else {
        icon.classList.remove('fas');
        icon.classList.add('far');
        icon.style.color = '';
    }
});
</script>
</body>
</html>
