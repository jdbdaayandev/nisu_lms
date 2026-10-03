<?php
// In a real app, grab from $_SESSION
$user_name = "Juan Dela Cruz";
$user_email = "juan.delacruz@nisu.edu.ph";
$user_initials = "JD";

// Get the current file name WITHOUT the .php extension (e.g., 'catalog', 'dashboard')
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<header class="navbar">
    <div class="nav-left">
        <!-- Removed .php -->
        <a href="index" class="nav-brand">
            <div class="brand-logo-wrapper"><i class="fas fa-layer-group"></i></div>
            <div class="brand-text">
                <span class="brand-title">NISU Library</span>
                <span class="brand-subtitle">Lemery Campus</span>
            </div>
        </a>
    </div>
    
    <div class="nav-center">
        <div class="nav-links">
            <!-- Dashboard -->
            <a href="dashboard" class="nav-link <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a>

            <!-- Catalogs -->
            <a href="catalog" class="nav-link <?php echo ($current_page == 'catalog' || $current_page == 'index' || $current_page == '') ? 'active' : ''; ?>">
                <i class="fas fa-book-open"></i> Catalogs
            </a>
            
            <!-- My Borrows -->
            <a href="my_borrows" class="nav-link <?php echo ($current_page == 'my_borrows') ? 'active' : ''; ?>">
                <i class="fas fa-list-check"></i> My Borrows
            </a>

            <!-- Attendance -->
            <a href="attendance" class="nav-link <?php echo ($current_page == 'attendance') ? 'active' : ''; ?>">
                <i class="fas fa-user-check"></i> Attendance
            </a>
        </div>
    </div>

    <div class="nav-right">
        <div class="user-dropdown-container" id="profileDropdownBtn">
            <button type="button" class="user-btn">
                <div class="user-avatar"><?php echo htmlspecialchars($user_initials); ?></div>
                <span class="user-name-text"><?php echo htmlspecialchars($user_name); ?></span>
                <i class="fas fa-chevron-down dropdown-icon"></i>
            </button>
            
            <div class="dropdown-menu">
                <div class="dropdown-header">
                    <p class="dh-name"><?php echo htmlspecialchars($user_name); ?></p>
                    <p class="dh-email"><?php echo htmlspecialchars($user_email); ?></p>
                </div>
                <div class="dropdown-list">
                    <!-- Removed .php from dropdown links -->
                    <a href="profile" class="dropdown-item"><i class="fas fa-id-badge"></i> Profile</a>
                    <a href="settings" class="dropdown-item"><i class="fas fa-cog"></i> Settings</a>
                    <div class="dropdown-divider" style="height:1px; background:var(--border); margin:4px 0;"></div>
                    <a href="logout" class="dropdown-item danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>
</header>