<?php
session_start();

$page_title = 'Librarian Dashboard - NISU Library';

// --- Mock Admin Data ---
$user_name = "Admin Librarian";

// --- Capture Time Filter ---
$time_range = isset($_GET['range']) ? trim($_GET['range']) : 'today';

// --- Mock Database for Statistics based on Time Range ---
$total_members = 1254; // Persistent total

$mock_stats = [
    'today' => [
        'label' => "Today's",
        'visits' => 87,
        'borrowed' => 34,
        'returned' => 28
    ],
    '7days' => [
        'label' => "Last 7 Days",
        'visits' => 542,
        'borrowed' => 195,
        'returned' => 180
    ],
    '30days' => [
        'label' => "Last 30 Days",
        'visits' => 2150,
        'borrowed' => 840,
        'returned' => 795
    ],
    'year' => [
        'label' => "This Year",
        'visits' => 18450,
        'borrowed' => 6230,
        'returned' => 6100
    ]
];

// Fallback to 'today' if invalid range is passed
$current_stats = isset($mock_stats[$time_range]) ? $mock_stats[$time_range] : $mock_stats['today'];

// --- Mock Recent Transactions ---
$recent_transactions = [
    [
        'user' => 'Maria Clara',
        'item' => 'Advanced Physics for Engineers',
        'action' => 'Borrowed',
        'time' => '10 mins ago',
        'id' => '2026-0041'
    ],
    [
        'user' => 'Juan Dela Cruz',
        'item' => 'Chromebook Model X',
        'action' => 'Returned',
        'time' => '45 mins ago',
        'id' => '2025-1102'
    ],
    [
        'user' => 'Pedro Penduko',
        'item' => 'To Kill a Mockingbird',
        'action' => 'Borrowed',
        'time' => '1 hour ago',
        'id' => '2026-0155'
    ],
    [
        'user' => 'Ana Reyes',
        'item' => 'TI-84 Graphing Calculator',
        'action' => 'Returned',
        'time' => '2 hours ago',
        'id' => '2024-0891'
    ]
];

// ==========================================
// 1. PUSH STYLES
// ==========================================
ob_start();
?>
<style>
    :root {
        --success: #10b981; --success-bg: #d1fae5;
        --danger: #ef4444;  --danger-bg: #fee2e2;
        --info: #3b82f6;    --info-bg: #dbeafe;
        --warning: #f59e0b; --warning-bg: #fef3c7;
    }

    /* Centered Hero matching Catalog and My Borrows */
    .dashboard-hero {
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        padding: 4.5rem 20px 6rem;
        text-align: center;
        color: white;
        border-radius: 0 0 1.5rem 1.5rem;
    }
    .dashboard-hero h1 { font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem; letter-spacing: -0.025em; }
    .dashboard-hero p { font-size: 1.1rem; opacity: 0.9; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto; }

    /* Filter Dropdown UI */
    .filter-form {
        margin-top: 1.5rem;
        display: flex;
        justify-content: center;
    }
    .range-select {
        appearance: none;
        -webkit-appearance: none;
        background: var(--surface);
        border: 2px solid transparent;
        color: var(--text-main);
        font-family: inherit;
        font-size: 0.95rem;
        font-weight: 700;
        padding: 0.75rem 2.5rem 0.75rem 1.5rem;
        border-radius: 50px;
        cursor: pointer;
        outline: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%231a3673' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 1em;
    }
    .range-select:focus { border-color: rgba(255,255,255,0.4); box-shadow: 0 10px 40px rgba(0,0,0,0.25); }

    /* Main Container overlaps hero */
    .dashboard-container {
        max-width: 1200px;
        margin: -3.5rem auto 3rem;
        padding: 0 5%;
        width: 100%;
        position: relative;
        z-index: 10;
    }

    /* Minimalist Stat Cards Grid */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 1.75rem 1.5rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover { 
        transform: translateY(-2px); 
        border-color: var(--accent);
        box-shadow: 0 10px 15px -3px rgba(26, 54, 115, 0.08); 
    }
    
    .stat-value { font-size: 2.2rem; font-weight: 800; color: var(--primary); line-height: 1; margin-bottom: 8px; }
    .stat-label { font-size: 0.85rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

    /* Dashboard Layout */
    .dashboard-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; }

    /* Generic Card Style */
    .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
    .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 2px solid var(--border); padding-bottom: 1rem; }
    .card-title { font-size: 1.2rem; font-weight: 700; color: var(--primary); }
    .card-action { font-size: 0.85rem; color: var(--accent); text-decoration: none; font-weight: 600; }
    .card-action:hover { text-decoration: underline; }

    /* Transactions List */
    .trans-list { display: flex; flex-direction: column; gap: 0.75rem; }
    .trans-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        background: var(--background);
        transition: all 0.2s ease;
    }
    .trans-item:hover { border-color: var(--accent); background: white; box-shadow: 0 4px 10px rgba(26, 54, 115, 0.05); }
    
    .trans-info { display: flex; flex-direction: column; gap: 6px; }
    .trans-user { font-size: 1rem; font-weight: 700; color: var(--text-main); }
    .trans-meta { font-size: 0.85rem; color: var(--text-muted); }
    .trans-meta strong { color: var(--primary); }
    
    .trans-right { display: flex; flex-direction: column; align-items: flex-end; gap: 8px; }
    .trans-time { font-size: 0.8rem; color: var(--text-muted); font-weight: 500; }
    
    .badge-action {
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .badge-borrowed { background: var(--warning-bg); color: #b45309; }
    .badge-returned { background: var(--success-bg); color: var(--success); }

    /* Clean Quick Links */
    .quick-links { display: flex; flex-direction: column; gap: 0.75rem; }
    .q-link {
        display: block;
        padding: 1rem 1.25rem; 
        background: var(--background);
        border: 1px solid var(--border); 
        border-radius: var(--radius);
        color: var(--text-main); 
        text-decoration: none;
        font-weight: 600; 
        font-size: 0.95rem; 
        transition: all 0.2s;
    }
    .q-link:hover { 
        background: white; 
        color: var(--primary); 
        border-color: var(--accent); 
        box-shadow: 0 4px 10px rgba(26, 54, 115, 0.08); 
        transform: translateY(-2px); 
    }

    @media (max-width: 900px) {
        .dashboard-layout { grid-template-columns: 1fr; }
    }
    @media (max-width: 600px) {
        .trans-item { flex-direction: column; align-items: flex-start; gap: 1rem; }
        .trans-right { align-items: flex-start; flex-direction: row-reverse; justify-content: space-between; width: 100%; }
    }
</style>
<?php
$page_styles = ob_get_clean();

// ==========================================
// 2. YIELD CONTENT
// ==========================================
ob_start();
?>
<!-- Admin Hero -->
<div class="dashboard-hero">
    <h1>Librarian Dashboard</h1>
    <p>Welcome back, <?php echo htmlspecialchars($user_name); ?>. Here is the library overview.</p>
    
    <!-- Clean Dropdown Time Filter -->
    <form action="" method="GET" class="filter-form">
        <select name="range" class="range-select" onchange="this.form.submit()">
            <option value="today" <?php if($time_range == 'today') echo 'selected'; ?>>Today</option>
            <option value="7days" <?php if($time_range == '7days') echo 'selected'; ?>>Last 7 Days</option>
            <option value="30days" <?php if($time_range == '30days') echo 'selected'; ?>>Last 30 Days</option>
            <option value="year" <?php if($time_range == 'year') echo 'selected'; ?>>This Year</option>
        </select>
    </form>
</div>

<div class="dashboard-container">
    
    <!-- Top Statistics Cards (Text Focused) -->
    <div class="stat-grid">
        <!-- Members (Persistent metric) -->
        <div class="stat-card">
            <span class="stat-value"><?php echo number_format($total_members); ?></span>
            <span class="stat-label">Registered Members</span>
        </div>
        
        <!-- Visits (Filtered metric) -->
        <div class="stat-card">
            <span class="stat-value"><?php echo number_format($current_stats['visits']); ?></span>
            <span class="stat-label"><?php echo $current_stats['label']; ?> Visits</span>
        </div>

        <!-- Borrowed (Filtered metric) -->
        <div class="stat-card">
            <span class="stat-value"><?php echo number_format($current_stats['borrowed']); ?></span>
            <span class="stat-label"><?php echo $current_stats['label']; ?> Borrowed</span>
        </div>
        
        <!-- Returned (Filtered metric) -->
        <div class="stat-card">
            <span class="stat-value"><?php echo number_format($current_stats['returned']); ?></span>
            <span class="stat-label"><?php echo $current_stats['label']; ?> Returned</span>
        </div>
    </div>

    <!-- Main Dashboard Grid -->
    <div class="dashboard-layout">
        
        <!-- Left Column: Recent Transactions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Transactions</h3>
                <a href="#" class="card-action">View Full Log</a>
            </div>
            
            <div class="trans-list">
                <?php foreach($recent_transactions as $trans): 
                    $badgeClass = $trans['action'] === 'Borrowed' ? 'badge-borrowed' : 'badge-returned';
                ?>
                <div class="trans-item">
                    <div class="trans-info">
                        <span class="trans-user"><?php echo htmlspecialchars($trans['user']); ?> <span style="font-size: 0.8rem; font-weight: 500; color: var(--text-muted); margin-left: 8px;">ID: <?php echo $trans['id']; ?></span></span>
                        <span class="trans-meta">Item: <strong><?php echo htmlspecialchars($trans['item']); ?></strong></span>
                    </div>
                    <div class="trans-right">
                        <span class="badge-action <?php echo $badgeClass; ?>"><?php echo $trans['action']; ?></span>
                        <span class="trans-time"><?php echo $trans['time']; ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Right Column: Admin Quick Links -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="quick-links">
                    <a href="#" class="q-link">Issue / Borrow Item</a>
                    <a href="#" class="q-link">Process Return</a>
                    <a href="#" class="q-link">Register New Member</a>
                    <a href="#" class="q-link">Generate Reports</a>
                </div>
            </div>
        </div>

    </div>
</div>
<?php
$page_content = ob_get_clean();

// ==========================================
// 3. PUSH SCRIPTS
// ==========================================
ob_start();
?>
<script>
    // JS specific to dashboard
</script>
<?php
$page_scripts = ob_get_clean();

// ==========================================
// 4. REQUIRE LAYOUT
// ==========================================
require 'layout.php';
?>