<aside class="sidebar" id="sidebar">

    <!-- Logo Section -->
    <nav class="menu" role="navigation">
        <a href="staff.php" class="menu-item <?php echo (strpos($_SERVER['REQUEST_URI'], 'staff.php') !== false) ? 'active' : ''; ?>">
            <i class="fas fa-house-chimney"></i> 
            <span>Home</span>
        </a>
        <div class="menu-item clients">
            <a href="application-list.php" class="list <?php echo (strpos($_SERVER['REQUEST_URI'], 'application-list.php') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-user-friends"></i> 
                <span>Clients</span>
            </a>
            <a href="application-form.php" class="add-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'application-form.php') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-circle-plus"></i> 
                <span>Add</span>
            </a>
        </div>
        <a href="report.php" class="menu-item <?php echo (strpos($_SERVER['REQUEST_URI'], 'report.php') !== false) ? 'active' : ''; ?>">
            <i class="fas fa-chart-pie"></i> 
            <span>Reports</span>
        </a>
        <a href="../logout.php" class="menu-item <?php echo (strpos($_SERVER['REQUEST_URI'], 'logout.php') !== false) ? 'active' : ''; ?>">
            <i class="fas fa-right-from-bracket"></i> 
            <span>Logout</span>
        </a>
    </nav>

    <footer class="sidebar-footer">
        <a href="profile.php" class="footer-item <?php echo (strpos($_SERVER['REQUEST_URI'], 'profile.php') !== false) ? 'active' : ''; ?>">
            <i class="fas fa-user-circle"></i> 
            <span>Profile</span>
        </a>
        <a href="change_password.php" class="footer-item <?php echo (strpos($_SERVER['REQUEST_URI'], 'change_password.php') !== false) ? 'active' : ''; ?>">
            <i class="fa-solid fa-key"></i> 
            <span>Change Password</span>
        </a>
    </footer>
</aside>

<header class="header" id="header">
<button class="toggle-btn" id="toggle-btn"><i class="fa-solid fa-bars"></i></button>
<nav class="header-nav">
    <a href="#" class="header-nav-item">
        <i class="fas fa-book-open"></i> 
        Docs
    </a>
    <a href="#" class="header-nav-item">
        <i class="fas fa-life-ring"></i> 
        Support
    </a>
    <a href="#" class="header-nav-item">
        <i class="fas fa-envelope"></i> 
        Contact
    </a>
</nav>
</header>