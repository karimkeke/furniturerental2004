<?php
session_start();
include('connection.php');



// Handle logout
if (isset($_GET['logout'])) {
         
    session_destroy(); 
    
    header('location: login.php?message=You have been successfully logged out');
    exit;
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
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Furniture Rental - Premium Furniture Rental Services</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<style>
 
    .categories-tabs {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 40px;
    margin-bottom: 30px;
}

.tab-button {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    background-color: #f8f9fa;
    border: 2px solid transparent;
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #555;
    font-weight: 600;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.tab-button i {
    margin-right: 8px;
    font-size: 1.1rem;
}

.tab-button:hover {
    background-color: #f1f1f1;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.tab-button.active {
    background-color: #000;
    color: white;
    border-color: #000;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
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
}.cart-icon {
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
    filter: brightness(90%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(230, 126, 34, 0.3);
}

.register-btn:active {
    transform: translateY(0);
}

.method-tip {
    font-size: 14px;
    color: #888;
    font-style: italic;
    margin-top: 5px;
}

.contact-card-header h4 {
    margin: 0;
}

.contact-info .form-header {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.contact-info .form-header h3 {
    color: #333;
    margin-bottom: 10px;
    font-size: 24px;
    font-weight: 700;
}

.contact-info .form-header p {
    color: #666;
    font-size: 16px;
    margin: 0;
}

.contact-card {
    margin-bottom: 20px;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.contact-card:hover {
    border-left: 3px solid #e86a33;
}

.contact-card-content {
    padding-left: 60px;
}

.map-container {
    margin-top: 15px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    border-radius: 10px;
    overflow: hidden;
    position: relative;
    transition: all 0.3s ease;
    z-index: 1;
}

.map-container:hover {
    box-shadow: 0 12px 25px rgba(0, 0, 0, 0.12);
    transform: translateY(-3px);
}

.map-container iframe {
    display: block;
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

        <a href="product.php">Products</a>
    

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
            <a href="accountdetails.php?tab=loyalty">Loyalty Program</a>
                <a href="account.php?logout=1" name="logout" id="logout-btn">Logout</a>
            </div>
        </div>
    <?php else : ?>
        <button class="register-btn" onclick="window.location.href='register.php'">Register</button>
    <?php endif; ?>
</div>

         
    </div>
</nav>         
    <div class="menu-backdrop"></div>


    <header id="home" class="hero">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <div class="hero-text">
                <h1 class="hero-title">Rent Furniture for Your Home Easily</h1>
                <p class="hero-description">We offer a wide range of premium furniture rentals at affordable prices. Choose from our diverse collection of living rooms, bedrooms, and dining tables.</p>
                <div class="hero-buttons">
                    <a href="#categories" class="primary-btn">Browse Products</a>
                    <a href="#contact" class="secondary-btn">Contact Us</a>
                </div>
                <div class="hero-features">
                    <div class="feature">
                        <i class="fas fa-truck"></i>
                        <span>Fast Delivery</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-shield-alt"></i>
                        <span>Quality Guarantee</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span>Competitive Prices</span>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <img src="https://images.unsplash.com/photo-1556228453-efd6c1ff04f6?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" alt="Luxurious Furniture">
            </div>
        </div>
    </header>

    <section id="categories" class="products-section">
    <div class="container">
        <h2>Our Featured Products</h2>

        <div class="categories-tabs">
            <button class="tab-button active" data-category="all">
                <i class="fas fa-border-all"></i>
                <span>All Products</span>
            </button>
            <button class="tab-button" data-category="living">
                <i class="fas fa-couch"></i>
                <span>Living Room</span>
            </button>
            <button class="tab-button" data-category="dining">
                <i class="fas fa-utensils"></i>
                <span>Dining Room</span>
            </button>
            <button class="tab-button" data-category="bed">
                <i class="fas fa-bed"></i>
                <span>Bedroom</span>
            </button>
            <button class="tab-button" data-category="office">
                <i class="fas fa-briefcase"></i>
                <span>Office</span>
            </button>
        </div>
        <div class="products-grid">
            <?php 
                include('get_sofas.php'); 
                while ($row = $sofas->fetch_assoc()) { 
            ?>
            <div class="product-card" data-category="living">
                <div class="product-image">
                    <img src="assets/imgs/<?php echo htmlspecialchars($row['product_image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                    <div class="product-overlay">
                        <button class="rent-button" onclick="window.location.href='product.php?category=sofas'">
                            <i class="fas fa-arrow-right"></i>
                            View More
                        </button>
                    </div>
                    
                </div>
                <div class="product-info">
                    <h3><i class="fas fa-couch"></i> <?php echo htmlspecialchars($row['product_name']); ?></h3>
                </div>
            </div>
            <?php } ?>

            <?php 
                include('get_coffeetables.php'); 
                while ($row = $coffeetables->fetch_assoc()) { 
            ?>
            <div class="product-card" data-category="living">
                <div class="product-image">
                    <img src="assets/imgs/<?php echo htmlspecialchars($row['product_image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                    <div class="product-overlay">
                        <button class="rent-button" onclick="window.location.href='product.php?category=coffeetables'">
                            <i class="fas fa-arrow-right"></i>
                            View More
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <h3><i class="fas fa-coffee"></i> <?php echo htmlspecialchars($row['product_name']); ?></h3>
                </div>
            </div>
            <?php } ?>

            <?php 
                include('get_accentchair.php'); 
                while ($row = $accentchair->fetch_assoc()) { 
            ?>
            <div class="product-card" data-category="living">
                <div class="product-image">
                    <img src="assets/imgs/<?php echo htmlspecialchars($row['product_image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                    <div class="product-overlay">
                        <button class="rent-button" onclick="window.location.href='product.php?category=accentchair'">
                            <i class="fas fa-arrow-right"></i>
                            View More
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <h3><i class="fas fa-chair"></i> <?php echo htmlspecialchars($row['product_name']); ?></h3>
                </div>
            </div>
            <?php } ?>
  


     
            <?php 
                include('get_round.php'); 
                while ($row = $round->fetch_assoc()) { 
            ?>
            <div class="product-card" data-category="dining">
                <div class="product-image">
                    <img src="assets/imgs/<?php echo htmlspecialchars($row['product_image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                    <div class="product-overlay">
                        <button class="rent-button" onclick="window.location.href='product.php?category=round'">
                            <i class="fas fa-arrow-right"></i>
                            View More
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <h3><i class="fas fa-circle"></i> <?php echo htmlspecialchars($row['product_name']); ?></h3>
                </div>
            </div>
            <?php } ?>

            <?php 
                include('get_rectangle.php'); 
                while ($row = $rectangle->fetch_assoc()) { 
            ?>
            <div class="product-card" data-category="dining">
                <div class="product-image">
                    <img src="assets/imgs/<?php echo htmlspecialchars($row['product_image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                    <div class="product-overlay">
                        <button class="rent-button" onclick="window.location.href='product.php?category=rectangle'">
                            <i class="fas fa-arrow-right"></i>
                            View More
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <h3><i class="fas fa-border-all"></i> <?php echo htmlspecialchars($row['product_name']); ?></h3>
                </div>
            </div>
            <?php } ?>

            <?php 
                include('get_square.php'); 
                while ($row = $square->fetch_assoc()) { 
            ?>
            <div class="product-card" data-category="dining">
                <div class="product-image">
                    <img src="assets/imgs/<?php echo htmlspecialchars($row['product_image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                    <div class="product-overlay">
                        <button class="rent-button" onclick="window.location.href='product.php?category=square'">
                            <i class="fas fa-arrow-right"></i>
                            View More
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <h3><i class="fas fa-th-large"></i> <?php echo htmlspecialchars($row['product_name']); ?></h3>
                </div>
            </div>
            <?php } ?>
  
      

            <?php 
                include('get_bedframe.php'); 
                while ($row = $bedframe->fetch_assoc()) { 
            ?>
            <div class="product-card" data-category="bed">
                <div class="product-image">
                    <img src="assets/imgs/<?php echo htmlspecialchars($row['product_image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                    <div class="product-overlay">
                        <button class="rent-button" onclick="window.location.href='product.php?category=bedframe'">
                            <i class="fas fa-arrow-right"></i>
                            View More
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <h3><i class="fas fa-bed"></i> <?php echo htmlspecialchars($row['product_name']); ?></h3>
                </div>
            </div>
            <?php } ?>

            <?php 
                include('get_dresser.php'); 
                while ($row = $dresser->fetch_assoc()) { 
            ?>
            <div class="product-card" data-category="bed">
                <div class="product-image">
                    <img src="assets/imgs/<?php echo htmlspecialchars($row['product_image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                    <div class="product-overlay">
                        <button class="rent-button" onclick="window.location.href='product.php?category=dresser'">
                            <i class="fas fa-arrow-right"></i>
                            View More
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <h3><i class="fas fa-archive"></i> <?php echo htmlspecialchars($row['product_name']); ?></h3>
                </div>
            </div>
            <?php } ?>

            <?php 
                include('get_sidetable.php'); 
                while ($row = $sidetable->fetch_assoc()) { 
            ?>
            <div class="product-card" data-category="bed">
                <div class="product-image">
                    <img src="assets/imgs/<?php echo htmlspecialchars($row['product_image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                    <div class="product-overlay">
                        <button class="rent-button" onclick="window.location.href='product.php?category=sidetable'">
                            <i class="fas fa-arrow-right"></i>
                            View More
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <h3><i class="fas fa-table"></i> <?php echo htmlspecialchars($row['product_name']); ?></h3>
                </div>
            </div>
            <?php } ?>
      

              
      
            <?php 
                include('get_officechair.php'); 
                while ($row = $officechair->fetch_assoc()) { 
            ?>
            <div class="product-card" data-category="office">
                <div class="product-image">
                    <img src="assets/imgs/<?php echo htmlspecialchars($row['product_image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                    <div class="product-overlay">
                        <button class="rent-button" onclick="window.location.href='product.php?category=officechair'">
                            <i class="fas fa-arrow-right"></i>
                            View More
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <h3><i class="fas fa-chair"></i> <?php echo htmlspecialchars($row['product_name']); ?></h3>
                </div>
            </div>
            <?php } ?>

            <?php 
                include('get_desk.php'); 
                while ($row = $desk->fetch_assoc()) { 
            ?>
            <div class="product-card" data-category="office">
                <div class="product-image">
                    <img src="assets/imgs/<?php echo htmlspecialchars($row['product_image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                    <div class="product-overlay">
                        <button class="rent-button" onclick="window.location.href='product.php?category=desk'">
                            <i class="fas fa-arrow-right"></i>
                            View More
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <h3><i class="fas fa-desktop"></i> <?php echo htmlspecialchars($row['product_name']); ?></h3>
                </div>
            </div>
            <?php } ?>

            <?php 
                include('get_cornerdesk.php'); 
                while ($row = $cornerdesk->fetch_assoc()) { 
            ?>
            <div class="product-card" data-category="office">
                <div class="product-image">
                    <img src="assets/imgs/<?php echo htmlspecialchars($row['product_image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                    <div class="product-overlay">
                        <button class="rent-button" onclick="window.location.href='product.php?category=cornerdesk'">
                            <i class="fas fa-arrow-right"></i>
                            View More
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <h3><i class="fas fa-th"></i> <?php echo htmlspecialchars($row['product_name']); ?></h3>
                </div>
            </div>
            <?php } ?>
        </div> 

             
                </section>

       <!-- FAQ Section (Previously Contact Us) -->
       <section id="contact" class="contact-section">
        <div class="contact-bg-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
        <div class="container">
            <div class="section-header">
                <span class="section-subtitle">Have Questions?</span>
                <h2>Frequently Asked Questions</h2>
                <p>Find answers to the most common questions about our furniture rental services.</p>
            </div>
            
            <div class="faq-content">
                <div class="faq-accordion">
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>How does furniture rental work?</h3>
                            <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                        </div>
                        <div class="faq-answer">
                            <p>Our furniture rental process is simple: browse our collection, select the pieces you love, choose your rental period (3, 6, or 12 months), and we'll deliver and set up everything in your space. When your rental period ends, you can extend, swap pieces, or we'll pick everything up.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>What are your delivery and setup fees?</h3>
                            <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                        </div>
                        <div class="faq-answer">
                            <p>Delivery and setup fees vary based on your location and the size of your order. Standard delivery within Algiers starts at 2000 DZD. For locations outside Algiers, additional fees may apply. The exact cost will be calculated during checkout.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>Can I customize my furniture rental package?</h3>
                            <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                        </div>
                        <div class="faq-answer">
                            <p>Absolutely! We offer fully customizable packages. You can select individual pieces or choose from our curated room packages. If you need help creating the perfect space, our design consultants are available to assist you at no additional cost.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>What if I damage the furniture?</h3>
                            <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                        </div>
                        <div class="faq-answer">
                            <p>We understand that accidents happen. Minor wear and tear is covered in your rental agreement. For more significant damage, we offer an optional protection plan that covers most accidental damage. Without the protection plan, charges for repairs or replacement will be assessed based on the extent of the damage.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>Can I purchase the furniture I'm renting?</h3>
                            <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                        </div>
                        <div class="faq-answer">
                            <p>Yes! If you fall in love with your rented furniture, you can purchase it anytime during your rental period. We offer a rent-to-own option where a portion of your paid rental fees can be applied toward the purchase price.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>How do I maintain and clean the furniture?</h3>
                            <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                        </div>
                        <div class="faq-answer">
                            <p>Each piece comes with care instructions. Generally, we recommend regular dusting, prompt cleaning of spills, and avoiding direct sunlight for extended periods. For specific cleaning questions, our customer service team is happy to provide guidance.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Loyalty Program Section -->
    <section id="loyalty" class="loyalty-section">
        <div class="container">
            <div class="section-header">
                <span class="section-subtitle">Rewards Program</span>
                <h2>Furniture Rental Fidelity Program</h2>
                <p>Earn points with every rental and enjoy exclusive benefits as our valued customer.</p>
            </div>
            
            <div class="loyalty-content">
                <div class="loyalty-card">
                    <div class="loyalty-icon">
                        <i class="fas fa-gift"></i>
                    </div>
                    <h3>How It Works</h3>
                    <ul class="loyalty-list">
                        <li><i class="fas fa-check-circle"></i> Earn 1 point for every 1000 DZD spent on rentals</li>
                        <li><i class="fas fa-check-circle"></i> Automatic enrollment for all registered users</li>
                        <li><i class="fas fa-check-circle"></i> Track your points easily in your account dashboard</li>
                        <li><i class="fas fa-check-circle"></i> Points never expire as long as your account is active</li>
                    </ul>
                </div>
                
                <div class="loyalty-card">
                    <div class="loyalty-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h3>Membership Tiers</h3>
                    <div class="tier-container">
                        <div class="tier">
                            <div class="tier-badge bronze">
                                <i class="fas fa-medal"></i>
                            </div>
                            <div class="tier-details">
                                <h4>Bronze</h4>
                                <p>0-50 points</p>
                                <span>5% off your next rental</span>
                            </div>
                        </div>
                        
                        <div class="tier">
                            <div class="tier-badge silver">
                                <i class="fas fa-medal"></i>
                            </div>
                            <div class="tier-details">
                                <h4>Silver</h4>
                                <p>51-150 points</p>
                                <span>10% off + free delivery</span>
                            </div>
                        </div>
                        
                        <div class="tier">
                            <div class="tier-badge gold">
                                <i class="fas fa-medal"></i>
                            </div>
                            <div class="tier-details">
                                <h4>Gold</h4>
                                <p>151+ points</p>
                                <span>15% off + free delivery + priority support</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="loyalty-card">
                    <div class="loyalty-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <h3>Redeem Benefits</h3>
                    <ul class="loyalty-list">
                        <li><i class="fas fa-check-circle"></i> Discounts on future rentals</li>
                        <li><i class="fas fa-check-circle"></i> Free delivery and setup</li>
                        <li><i class="fas fa-check-circle"></i> Extended rental periods</li>
                        <li><i class="fas fa-check-circle"></i> Priority customer support</li>
                        <li><i class="fas fa-check-circle"></i> Early access to new furniture collections</li>
                    </ul>
                    <?php if(!isset($_SESSION['logged_in'])) : ?>
                    <div class="loyalty-cta">
                        <p>Join our loyalty program today!</p>
                        <button class="register-btn" onclick="window.location.href='register.php'">Register Now</button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <style>
        /* Contact/FAQ Section Styles */
        .contact-section {
            padding: 80px 0;
            background-color: #f9f9f9;
            position: relative;
        }
        
        .contact-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0) 70%);
            border-radius: 50%;
            z-index: 0;
        }
        
        .contact-section::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(253, 186, 116, 0.1) 0%, rgba(253, 186, 116, 0) 70%);
            border-radius: 50%;
            z-index: 0;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 50px;
            position: relative;
            z-index: 1;
        }
        
        .section-subtitle {
            display: inline-block;
            font-size: 14px;
            font-weight: 600;
            color: var(--secondary-color);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 15px;
            position: relative;
        }
        
        .section-subtitle:before,
        .section-subtitle:after {
            content: '';
            display: inline-block;
            width: 30px;
            height: 1px;
            background-color: var(--secondary-color);
            vertical-align: middle;
            margin: 0 10px;
        }
        
        .section-header h2 {
            font-size: 36px;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
        }
        
        .section-header p {
            font-size: 18px;
            color: #666;
            max-width: 700px;
            margin: 0 auto;
        }
        
        /* FAQ Styles */
        .faq-content {
            position: relative;
            z-index: 1;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .faq-accordion {
            width: 100%;
        }
        
        .faq-item {
            background-color: #fff;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .faq-item:hover {
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.1);
        }
        
        .faq-question {
            padding: 25px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .faq-question h3 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        .faq-icon {
            color: var(--secondary-color);
            transition: transform 0.3s ease;
        }
        
        .faq-item.active .faq-icon {
            transform: rotate(180deg);
        }
        
        .faq-answer {
            padding: 0 25px 25px;
            color: #666;
            line-height: 1.6;
            display: none;
        }
        
        .faq-item.active .faq-answer {
            display: block;
        }
        
        @media screen and (max-width: 768px) {
            .faq-content {
                padding: 0 15px;
            }
        }
        
        @media screen and (max-width: 480px) {
            .faq-question {
                padding: 20px;
            }
            
            .faq-answer {
                padding: 0 20px 20px;
            }
        }

        /* Loyalty Program Styles */
        .loyalty-section {
            padding: 80px 0;
            background-color: #fff;
            position: relative;
        }
        
        .loyalty-content {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .loyalty-card {
            flex: 1;
            min-width: 300px;
            max-width: 350px;
            background-color: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .loyalty-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .loyalty-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--secondary-color, #e67e22) 0%, #f39c12 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            box-shadow: 0 10px 20px rgba(230, 126, 34, 0.2);
        }
        
        .loyalty-icon i {
            font-size: 30px;
            color: #fff;
        }
        
        .loyalty-card h3 {
            font-size: 22px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        
        .loyalty-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .loyalty-list li {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            color: #555;
            line-height: 1.5;
        }
        
        .loyalty-list li i {
            color: var(--secondary-color, #e67e22);
            margin-right: 10px;
            font-size: 18px;
            margin-top: 2px;
        }
        
        .tier-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .tier {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            background-color: #f8f9fa;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .tier:hover {
            background-color: #f0f0f0;
            transform: translateX(5px);
        }
        
        .tier-badge {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .tier-badge i {
            color: #fff;
            font-size: 18px;
        }
        
        .tier-badge.bronze {
            background: linear-gradient(135deg, #CD7F32 0%, #E6BC9E 100%);
            box-shadow: 0 5px 10px rgba(205, 127, 50, 0.2);
        }
        
        .tier-badge.silver {
            background: linear-gradient(135deg, #C0C0C0 0%, #E8E8E8 100%);
            box-shadow: 0 5px 10px rgba(192, 192, 192, 0.2);
        }
        
        .tier-badge.gold {
            background: linear-gradient(135deg, #FFD700 0%, #FFF8DC 100%);
            box-shadow: 0 5px 10px rgba(255, 215, 0, 0.2);
        }
        
        .tier-details {
            flex: 1;
        }
        
        .tier-details h4 {
            margin: 0 0 5px 0;
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        
        .tier-details p {
            margin: 0 0 5px 0;
            font-size: 14px;
            color: #666;
        }
        
        .tier-details span {
            display: block;
            font-size: 14px;
            color: var(--secondary-color, #e67e22);
            font-weight: 500;
        }
        
        .loyalty-cta {
            margin-top: 25px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px dashed rgba(0, 0, 0, 0.1);
        }
        
        .loyalty-cta p {
            margin-bottom: 15px;
            font-weight: 500;
            color: #333;
        }
        
        @media screen and (max-width: 992px) {
            .loyalty-card {
                min-width: 280px;
            }
        }
        
        @media screen and (max-width: 768px) {
            .loyalty-content {
                flex-direction: column;
                align-items: center;
            }
            
            .loyalty-card {
                max-width: 100%;
                width: 100%;
            }
        }
    </style>

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
    <script src="script.js"></script>
</body>
</html>


