<?php
session_start();
include('connection.php');
if (isset($_GET['logout'])) {
         
    session_destroy(); 
    
    header('location: login.php?message=You have been successfully logged out');
    exit;
}


$category = isset($_GET['category']) ? $_GET['category'] : 'all';


if ($category == "all") {
    $query = "SELECT * FROM products"; 
} else {
    $query = "SELECT * FROM products WHERE category = '$category'"; 
}

$products = $conn->query($query);

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
$search = isset($_GET['search']) ? $_GET['search'] : '';

if ($category == "all") {
    $query = "SELECT * FROM products"; 
} else {
    $query = "SELECT * FROM products WHERE category = '$category'"; 
}

if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query .= (strpos($query, 'WHERE')) === false ? ' WHERE ' : ' AND ';
    $query .= "(product_name LIKE '%$search' OR category LIKE '%$search%')";
}

$products = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($category); ?> - Products</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

<style>
    .limited-badge {
    position: absolute;
    bottom: 10px;
    left: 10px;
    background-color: #1E6370;
    color: white;
    font-size: 14px;
    font-weight: bold;
    padding: 5px 10px;
    border-radius: 5px;
}
.products-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(250px, 1fr)); 
    gap: 20px;
    padding: 30px;
    max-width: 90%;
    margin: auto; 
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
    background-color: #e67e22;
    filter: brightness(90%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(230, 126, 34, 0.3);
}

.register-btn:active {
    transform: translateY(0);
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
    color: var(--secondary-color);
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
    background: linear-gradient(120deg, var(--secondary-color), transparent, rgba(255,255,255,0.3));
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
    background: linear-gradient(120deg, var(--secondary-color), rgba(230, 126, 34, 0.6), rgba(255,255,255,0.3));
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
    color: var(--secondary-color);
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


.search-bar {
    width: 100%;
    padding: 12px 20px;
    padding-left: 45px;
    border: 2px solid #ddd;
    border-radius: 30px;
    font-size: 16px;
    outline: none;
    transition: all 0.3s ease;
}

.search-bar:focus {
    border-color: var(--secondary-color);
    box-shadow: 0 0 10px rgba(30, 99, 112, 0.2);
}

.search-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #777;
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
    <a href="cart.php" class="cart-icon">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-count">
            <?php echo isset($_SESSION['quantity']) ? $_SESSION['quantity'] : 0; ?>
        </span>
    </a>

    <div style="position: relative; display: inline-block;">
        <input type="text" id="search-input" placeholder="Search furniture..." class="search-bar">
        <i class="fas fa-search search-icon"></i>
    </div>


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

    <section class="shop-container">
    

  
        <div class="products-section">
            <h2><?php echo ucfirst($category); ?> Collection</h2>
            <div class="products-grid">
    <?php while ($row = $products->fetch_assoc()) { ?>
        <div class="product-card">
            <div class="product-image">
                <img src="assets/imgs/<?php echo htmlspecialchars($row['product_image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">

                <?php if ($row['product_quantity'] <= 3) { ?>
                    <div class="limited-badge">Limited Quantity</div>
                <?php } ?>

                <div class="product-overlay">
                    <button class="rent-button" onclick="window.location.href='single_product.php?product_id=<?php echo $row['product_id']; ?>'">View More</button>
                </div>
            </div>
            <div class="product-info">
                <h3>
                    <?php 
                    // Add appropriate icon based on category
                    $category = strtolower($row['category']);
                    $icon = 'fa-chair'; // Default icon
                    
                    if (strpos($category, 'sofa') !== false) {
                        $icon = 'fa-couch';
                    } elseif (strpos($category, 'table') !== false || strpos($category, 'desk') !== false) {
                        $icon = 'fa-table';
                    } elseif (strpos($category, 'bed') !== false) {
                        $icon = 'fa-bed';
                    } elseif (strpos($category, 'chair') !== false) {
                        $icon = 'fa-chair';
                    } elseif (strpos($category, 'dresser') !== false) {
                        $icon = 'fa-archive';
                    } 
                    ?>
                    <i class="fas <?php echo $icon; ?>"></i> <?php echo htmlspecialchars($row['product_name']); ?>
                </h3>
                <p class="price"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($row['product_price']); ?> DA/Month</p>
                <p class="stock"><i class="fas fa-boxes"></i> Stock: <?php echo htmlspecialchars($row['product_quantity']); ?></p>
            </div>
        </div>
    <?php } ?>
</div>

           
        </div>
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

    <script src="script.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const productCards = document.querySelectorAll('.product-card');
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        productCards.forEach(card => {
            const productName = card.querySelector('h3').textContent.toLowerCase();
            
            if (productName.includes(searchTerm) ) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
});
</script>
</body>
</html>
