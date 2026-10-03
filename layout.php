<?php
// Set defaults in case a page doesn't push anything
$page_title   = $page_title ?? 'NISU Library';
$page_styles  = $page_styles ?? '';
$page_content = $page_content ?? '';
$page_scripts = $page_scripts ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    
    <!-- GLOBAL STYLES (Variables, Reset, Navbar, Footer) -->
    <style>
        :root {
            --primary: #1a3673;
            --primary-hover: #122652;
            --accent: #5b7de8;
            --background: #f8fafc;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --radius: 0.75rem;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        /* --- HIDE SCROLLBARS COMPLETELY --- */
        html, body, * {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        *::-webkit-scrollbar {
            display: none; /* Chrome, Safari, Opera */
        }

        body { 
            background-color: var(--background); 
            color: var(--text-main); 
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            display: flex; 
            flex-direction: column; 
            min-height: 100vh; 
            -webkit-font-smoothing: antialiased;
        }

        /* --- Global Navbar Styles --- */
        .navbar { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(12px); border-bottom: 1px solid var(--border); padding: 0.75rem 5%; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 40; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .nav-left, .nav-right { display: flex; align-items: center; flex: 1; }
        .nav-right { justify-content: flex-end; }
        .nav-center { display: flex; align-items: center; justify-content: center; flex: 2; }
        .nav-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .brand-logo-wrapper { width: 38px; height: 38px; background: var(--primary); color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px; }
        .brand-text { display: flex; flex-direction: column; }
        .brand-title { font-size: 1.05rem; font-weight: 800; color: var(--primary); line-height: 1.1; letter-spacing: -0.025em;}
        .brand-subtitle { font-size: 0.7rem; color: var(--accent); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;}
        .nav-links { display: flex; gap: 0.5rem; background: var(--background); padding: 0.35rem; border-radius: 50px; border: 1px solid var(--border); }
        .nav-link { padding: 0.5rem 1.25rem; border-radius: 50px; font-size: 0.875rem; font-weight: 600; color: var(--text-muted); text-decoration: none; display: flex; align-items: center; gap: 8px; transition: all 0.2s; }
        .nav-link:hover { color: var(--primary); background: rgba(91, 125, 232, 0.05); }
        .nav-link.active { background: white; color: var(--primary); box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        
        .user-dropdown-container { position: relative; }
        .user-btn { display: flex; align-items: center; gap: 10px; background: white; border: 1px solid var(--border); padding: 0.35rem 0.75rem 0.35rem 0.35rem; border-radius: 50px; cursor: pointer; transition: all 0.2s; }
        .user-avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700; }
        .user-name-text { font-size: 0.875rem; font-weight: 600; color: var(--text-main); }
        .dropdown-menu { position: absolute; top: calc(100% + 10px); right: 0; width: 240px; background: white; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); opacity: 0; visibility: hidden; transform: translateY(-10px); transition: all 0.2s ease; z-index: 50; }
        .user-dropdown-container.active .dropdown-menu { opacity: 1; visibility: visible; transform: translateY(0); }
        .dropdown-header { padding: 1.25rem 1rem; background: var(--background); border-bottom: 1px solid var(--border); }
        .dh-name { font-weight: 700; font-size: 0.95rem; color: var(--text-main); margin-bottom: 2px; }
        .dh-email { font-size: 0.75rem; color: var(--text-muted); }
        .dropdown-list { padding: 0.5rem; display: flex; flex-direction: column; gap: 2px; }
        .dropdown-item { display: flex; align-items: center; gap: 10px; padding: 0.6rem 0.75rem; border-radius: 6px; font-size: 0.875rem; font-weight: 500; color: var(--text-main); text-decoration: none; transition: background 0.2s; }
        .dropdown-item:hover { background: var(--background); color: var(--primary); }
        .dropdown-item.danger { color: #dc2626; }
        
        /* --- Global Footer Styles --- */
        .footer { background: var(--primary); color: rgba(255,255,255,0.7); text-align: center; padding: 2rem; font-size: 0.875rem; margin-top: auto; }

        @media (max-width: 900px) { .nav-center { display: none; } }
    </style>

    <!-- PAGE SPECIFIC STYLES (Injected here) -->
    <?= $page_styles ?>

</head>
<body>

    <?php include 'navbar.php'; ?>

    <!-- PAGE SPECIFIC CONTENT (Injected here) -->
    <main style="flex: 1; display: flex; flex-direction: column;">
        <?= $page_content ?>
    </main>

    <?php include 'footer.php'; ?>

    <!-- GLOBAL SCRIPTS -->
    <script>
        // Profile Dropdown Logic (Runs everywhere)
        const profileDropdownBtn = document.getElementById('profileDropdownBtn');
        if(profileDropdownBtn) {
            const userBtn = profileDropdownBtn.querySelector('.user-btn');
            userBtn.addEventListener('click', (e) => {
                e.stopPropagation(); 
                profileDropdownBtn.classList.toggle('active');
            });
            document.addEventListener('click', (e) => {
                if (!profileDropdownBtn.contains(e.target)) profileDropdownBtn.classList.remove('active');
            });
        }
    </script>

    <!-- PAGE SPECIFIC SCRIPTS (Injected here) -->
    <?= $page_scripts ?>

</body>
</html>