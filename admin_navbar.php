<?php
$admin_name = "Admin Librarian";
$admin_initials = "AL";
?>
<header class="admin-navbar">
    <div class="navbar-left">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h2 class="page-header-title"><?= htmlspecialchars($page_title ?? 'Dashboard') ?></h2>
    </div>

    <div class="navbar-right">
        <!-- Notifications -->
        <button class="icon-btn">
            <i class="far fa-bell"></i>
            <span class="notification-dot"></span>
        </button>

        <!-- Profile Dropdown -->
        <div class="user-dropdown-container" id="adminDropdownBtn">
            <button type="button" class="user-btn">
                <div class="user-avatar" style="background: var(--accent);"><?php echo $admin_initials; ?></div>
                <span class="user-name-text"><?php echo $admin_name; ?></span>
                <i class="fas fa-chevron-down dropdown-icon"></i>
            </button>
            
            <div class="dropdown-menu">
                <div class="dropdown-list">
                    <a href="admin_profile" class="dropdown-item"><i class="fas fa-id-badge"></i> My Profile</a>
                    <div class="dropdown-divider"></div>
                    <a href="logout" class="dropdown-item danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>
</header>