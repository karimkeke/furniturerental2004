<?php
// Get current page filename
$current_page = basename($_SERVER['PHP_SELF']);

// Get total unread messages if available
$total_unread = isset($total_unread) ? $total_unread : 0;

// Get admin name
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : '';
?>

<div class="admin-sidebar">
    <div class="sidebar-header">
        <h2>Admin Panel</h2>
        <div class="sidebar-user"><?php echo $admin_name; ?></div>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="dashaboard.php" <?php echo ($current_page == 'dashaboard.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="admin_products.php" <?php echo ($current_page == 'admin_products.php' || $current_page == 'admin_add_product.php' || $current_page == 'admin_edit_product.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-couch"></i>
                <span>Products</span>
            </a>
        </li>
        <li>
            <a href="admin_orders.php" <?php echo ($current_page == 'admin_orders.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-shopping-cart"></i>
                <span>Orders</span>
            </a>
        </li>
        <li>
            <a href="admin_messages.php" <?php echo ($current_page == 'admin_messages.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-envelope"></i>
                <span>Messages</span>
                <?php if ($total_unread > 0): ?>
                    <span class="badge ms-auto"><?php echo $total_unread; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li>
            <a href="lowstocks.php" <?php echo ($current_page == 'lowstocks.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-exclamation-triangle"></i>
                <span>Low Stock</span>
            </a>
        </li>
        <li>
            <a href="admin_change_password.php" <?php echo ($current_page == 'admin_change_password.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-key"></i>
                <span>Change Password</span>
            </a>
        </li>
        <li>
            <a href="admin.php?logout=1">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div> 