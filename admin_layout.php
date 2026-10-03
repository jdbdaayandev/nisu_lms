<?php
$page_title   = $page_title ?? 'Admin Panel';
$page_styles  = $page_styles ?? '';
$page_content = $page_content ?? '';
$page_scripts = $page_scripts ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - NISU Library</title>
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    
    <style>
        :root {
            --primary: #1a3673;
            --primary-hover: #122652;
            --accent: #5b7de8;
            --background: #f4f7fa;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --radius: 0.75rem;
            
            --success: #10b981; --success-bg: #d1fae5;
            --danger: #ef4444;  --danger-bg: #fee2e2;
            --warning: #f59e0b; --warning-bg: #fef3c7;
            --info: #3b82f6;    --info-bg: #dbeafe;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        /* Hide Scrollbars completely */
        html, body, * { -ms-overflow-style: none; scrollbar-width: none; }
        *::-webkit-scrollbar { display: none; }

        body { 
            background-color: var(--background); 
            color: var(--text-main); 
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex; 
            height: 100vh; 
            overflow: hidden; /* Prevent body scroll, let content area scroll */
        }

        /* --- Layout Grid --- */
        .admin-sidebar {
            width: 260px;
            background: var(--primary);
            color: white;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            transition: transform 0.3s ease;
            z-index: 100;
        }
        
        .main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0; /* Fixes flexbox overflow issues */
        }

        /* --- Sidebar Styling --- */
        .sidebar-brand { padding: 1.5rem; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 1rem; }
        .sidebar-brand .brand-logo-wrapper { width: 36px; height: 36px; background: white; color: var(--primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px; }
        .sidebar-brand .brand-text { display: flex; flex-direction: column; }
        .sidebar-brand .brand-title { font-size: 1.1rem; font-weight: 800; line-height: 1; letter-spacing: -0.025em; }
        .sidebar-brand .brand-subtitle { font-size: 0.7rem; color: var(--accent); font-weight: 600; text-transform: uppercase; margin-top: 4px; }
        
        .sidebar-nav { padding: 0 1rem; display: flex; flex-direction: column; gap: 4px; overflow-y: auto; }
        .nav-section { font-size: 0.7rem; color: rgba(255,255,255,0.5); font-weight: 700; text-transform: uppercase; margin: 1rem 0 0.5rem 0.75rem; letter-spacing: 0.5px; }
        
        .side-link { display: flex; align-items: center; gap: 12px; padding: 0.75rem 1rem; color: rgba(255,255,255,0.7); text-decoration: none; border-radius: 8px; font-size: 0.9rem; font-weight: 500; transition: all 0.2s; }
        .side-link i { font-size: 1rem; width: 20px; text-align: center; }
        .side-link:hover { background: rgba(255,255,255,0.1); color: white; }
        .side-link.active { background: var(--accent); color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.15); }

        /* --- Navbar Styling --- */
        .admin-navbar { height: 70px; background: var(--surface); border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; padding: 0 2rem; flex-shrink: 0; }
        .navbar-left { display: flex; align-items: center; gap: 1rem; }
        .sidebar-toggle { background: transparent; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer; display: none; }
        .page-header-title { font-size: 1.25rem; font-weight: 700; color: var(--primary); letter-spacing: -0.025em; }
        
        .navbar-right { display: flex; align-items: center; gap: 1.5rem; }
        .icon-btn { background: transparent; border: none; font-size: 1.2rem; color: var(--text-muted); cursor: pointer; position: relative; transition: color 0.2s; }
        .icon-btn:hover { color: var(--primary); }
        .notification-dot { position: absolute; top: 0; right: 0; width: 8px; height: 8px; background: var(--danger); border-radius: 50%; border: 2px solid var(--surface); }

        /* Dropdown Reused from Frontend */
        .user-dropdown-container { position: relative; }
        .user-btn { display: flex; align-items: center; gap: 10px; background: transparent; border: none; cursor: pointer; padding: 0; }
        .user-avatar { width: 36px; height: 36px; border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 700; }
        .user-name-text { font-size: 0.9rem; font-weight: 600; color: var(--text-main); }
        .dropdown-icon { font-size: 0.7rem; color: var(--text-muted); }
        .dropdown-menu { position: absolute; top: calc(100% + 15px); right: 0; width: 200px; background: white; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); opacity: 0; visibility: hidden; transform: translateY(-10px); transition: all 0.2s ease; z-index: 50; }
        .user-dropdown-container.active .dropdown-menu { opacity: 1; visibility: visible; transform: translateY(0); }
        .dropdown-list { padding: 0.5rem; display: flex; flex-direction: column; gap: 2px; }
        .dropdown-item { display: flex; align-items: center; gap: 10px; padding: 0.6rem 0.75rem; border-radius: 6px; font-size: 0.875rem; font-weight: 500; color: var(--text-main); text-decoration: none; transition: background 0.2s; }
        .dropdown-item:hover { background: var(--background); color: var(--primary); }
        .dropdown-divider { height: 1px; background: var(--border); margin: 4px 0; }
        .dropdown-item.danger { color: var(--danger); }
        .dropdown-item.danger:hover { background: var(--danger-bg); }

        /* --- Content Area --- */
        .admin-content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }

        /* Mobile Adjustments */
        @media (max-width: 900px) {
            .admin-sidebar { position: fixed; height: 100%; transform: translateX(-100%); }
            .admin-sidebar.open { transform: translateX(0); box-shadow: 10px 0 30px rgba(0,0,0,0.1); }
            .sidebar-toggle { display: block; }
            .user-name-text { display: none; }
        }
    </style>

    <?= $page_styles ?>
</head>
<body>

    <!-- Sidebar -->
    <?php include 'admin_sidebar.php'; ?>

    <div class="main-wrapper">
        <!-- Top Navbar -->
        <?php include 'admin_navbar.php'; ?>

        <!-- Main Scrolling Content -->
        <main class="admin-content">
            <?= $page_content ?>
        </main>
    </div>

    <!-- Global Admin Scripts -->
    <script>
        // Sidebar Toggle for Mobile
        const sidebar = document.getElementById('adminSidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        if(toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('open');
            });
        }

        // Profile Dropdown
        const adminDropdownBtn = document.getElementById('adminDropdownBtn');
        if(adminDropdownBtn) {
            const userBtn = adminDropdownBtn.querySelector('.user-btn');
            userBtn.addEventListener('click', (e) => {
                e.stopPropagation(); 
                adminDropdownBtn.classList.toggle('active');
            });
            document.addEventListener('click', (e) => {
                if (!adminDropdownBtn.contains(e.target)) adminDropdownBtn.classList.remove('active');
            });
        }
    </script>

    <?= $page_scripts ?>
</body>
</html>