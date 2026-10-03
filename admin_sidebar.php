<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Define which pages belong to the "Libraries" dropdown to keep it open when active
$library_pages = ['admin_categories', 'admin_resource_types', 'admin_authors', 'admin_genres', 'admin_member_types'];
$is_library_active = in_array($current_page, $library_pages);
?>

<style>
    /* --- ALIGNMENT FIX FOR BRAND LOGO --- */
    /* Overrides the layout.php to match the exact 70px height of the navbar */
    .sidebar-brand {
        height: 70px !important;
        padding: 0 1.5rem !important;
        margin-bottom: 0 !important; /* Remove bottom margin to keep border aligned */
        display: flex;
        align-items: center;
        border-bottom: 1px solid rgba(255,255,255,0.1) !important;
        box-sizing: border-box;
    }
    .sidebar-brand img {
        height: 38px; /* Scaled slightly to fit perfectly centered in the 70px box */
        width: auto;
    }
    
    /* Add margin back to the nav list instead so the border stays aligned */
    .sidebar-nav {
        margin-top: 1rem; 
    }

    /* Dropdown Container */
    .nav-item-dropdown { display: flex; flex-direction: column; }
    
    /* Toggle Button */
    .dropdown-toggle { justify-content: space-between; cursor: pointer; user-select: none; }
    .dropdown-toggle .toggle-icon { width: auto; font-size: 0.75rem; transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); opacity: 0.7; }
    
    /* Open State - Chevron Rotation */
    .nav-item-dropdown.open .toggle-icon { transform: rotate(180deg); opacity: 1; }
    
    /* Smooth Accordion Animation using CSS Grid */
    .dropdown-submenu-wrapper {
        display: grid;
        grid-template-rows: 0fr;
        opacity: 0;
        margin: 0 0 0 1.5rem;
        transition: grid-template-rows 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease, margin 0.3s ease;
    }
    
    .nav-item-dropdown.open .dropdown-submenu-wrapper {
        grid-template-rows: 1fr;
        opacity: 1;
        margin: 4px 0 8px 1.5rem;
    }
    
    /* Inner content to hide overflow during animation */
    .dropdown-submenu { overflow: hidden; }
    
    .submenu-inner { display: flex; flex-direction: column; background: rgba(0, 0, 0, 0.15); border-radius: 8px; padding: 4px 0; }
    
    /* Sub-links Styling */
    .side-sub-link { 
        padding: 0.6rem 1rem; color: rgba(255,255,255,0.6); text-decoration: none; 
        font-size: 0.85rem; transition: all 0.2s ease; border-left: 3px solid transparent; 
        display: flex; align-items: center; gap: 10px;
    }
    .side-sub-link i { font-size: 0.45rem; opacity: 0.4; width: 10px; text-align: center; transition: all 0.2s ease; }
    
    .side-sub-link:hover { color: white; background: rgba(255,255,255,0.05); border-left-color: rgba(255,255,255,0.3); }
    .side-sub-link:hover i { opacity: 0.8; transform: scale(1.2); }
    
    .side-sub-link.active { color: white; border-left-color: var(--accent); background: rgba(255,255,255,0.08); font-weight: 600; }
    .side-sub-link.active i { opacity: 1; color: var(--accent); transform: scale(1.2); }
</style>

<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <!-- Replaced hardcoded height with CSS to ensure perfect vertical centering -->
        <img src="assets/logo2.png" alt="NISU Logo">
        <div class="brand-text">
            <span class="brand-title">NISU Admin</span>
            <span class="brand-subtitle">Library System</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">Main Menu</div>
        
        <a href="admin_dashboard" class="side-link <?php echo $current_page == 'admin_dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-chart-pie"></i> Dashboard
        </a>
        <a href="admin_catalog" class="side-link <?php echo $current_page == 'admin_catalog' ? 'active' : ''; ?>">
            <i class="fas fa-book"></i> Catalog Management
        </a>
        <a href="admin_circulation" class="side-link <?php echo $current_page == 'admin_circulation' ? 'active' : ''; ?>">
            <i class="fas fa-exchange-alt"></i> Circulation
        </a>
        <a href="admin_members" class="side-link <?php echo $current_page == 'admin_members' || $current_page == 'admin_add_member' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Members
        </a>
        
        <!-- LIBRARIES DROPDOWN -->
        <div class="nav-item-dropdown <?php echo $is_library_active ? 'open' : ''; ?>">
            <a href="#" class="side-link dropdown-toggle <?php echo $is_library_active ? 'active' : ''; ?>">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-layer-group"></i> Libraries
                </div>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </a>
            
            <!-- Animated Wrapper -->
            <div class="dropdown-submenu-wrapper">
                <div class="dropdown-submenu">
                    <div class="submenu-inner">
                        <a href="admin_categories" class="side-sub-link <?php echo $current_page == 'admin_categories' ? 'active' : ''; ?>">
                            <i class="fas fa-circle"></i> Catalog Categories
                        </a>
                        <a href="admin_resource_types" class="side-sub-link <?php echo $current_page == 'admin_resource_types' ? 'active' : ''; ?>">
                            <i class="fas fa-circle"></i> Resource Types
                        </a>
                        <a href="admin_authors" class="side-sub-link <?php echo $current_page == 'admin_authors' ? 'active' : ''; ?>">
                            <i class="fas fa-circle"></i> Authors
                        </a>
                        <a href="admin_genres" class="side-sub-link <?php echo $current_page == 'admin_genres' ? 'active' : ''; ?>">
                            <i class="fas fa-circle"></i> Genres
                        </a>
                        <a href="admin_member_types" class="side-sub-link <?php echo $current_page == 'admin_member_types' ? 'active' : ''; ?>">
                            <i class="fas fa-circle"></i> Member Types
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="nav-section" style="margin-top: 1.5rem;">System</div>
        <a href="admin_reports" class="side-link <?php echo $current_page == 'admin_reports' ? 'active' : ''; ?>">
            <i class="fas fa-file-invoice"></i> Reports
        </a>
        <a href="admin_settings" class="side-link <?php echo $current_page == 'admin_settings' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i> Settings
        </a>
    </nav>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
        
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                this.parentElement.classList.toggle('open');
            });
        });
    });
</script>