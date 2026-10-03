<?php
session_start();

$page_title = 'Dashboard Overview';

// Mock Stats
$total_members = 1254;
$active_loans = 142;
$overdue_items = 18;
$today_visits = 87;

// Mock Transactions
$recent_transactions = [
    ['user' => 'Maria Clara', 'id' => '2026-0041', 'item' => 'Advanced Physics for Engineers', 'action' => 'Borrowed', 'time' => '10 mins ago'],
    ['user' => 'Juan Dela Cruz', 'id' => '2025-1102', 'item' => 'Chromebook Model X', 'action' => 'Returned', 'time' => '45 mins ago'],
    ['user' => 'Pedro Penduko', 'id' => '2026-0155', 'item' => 'To Kill a Mockingbird', 'action' => 'Borrowed', 'time' => '1 hour ago']
];

// 1. PUSH STYLES
ob_start();
?>
<style>
    /* Admin Grid System */
    .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
    
    .stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-3px); border-color: var(--accent); }
    
    .stat-icon { width: 54px; height: 54px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; }
    .icon-blue { background: #e0e6ff; color: var(--primary); }
    .icon-green { background: var(--success-bg); color: var(--success); }
    .icon-orange { background: var(--warning-bg); color: var(--warning); }
    .icon-red { background: var(--danger-bg); color: var(--danger); }

    .stat-details { display: flex; flex-direction: column; }
    .stat-value { font-size: 1.75rem; font-weight: 800; color: var(--text-main); line-height: 1; margin-bottom: 4px; }
    .stat-label { font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }

    .dashboard-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; }

    /* Card Panels */
    .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem; }
    .card-title { font-size: 1.1rem; font-weight: 700; color: var(--text-main); }
    .card-action { font-size: 0.85rem; color: var(--accent); text-decoration: none; font-weight: 600; }

    /* Transactions */
    .trans-list { display: flex; flex-direction: column; gap: 0.75rem; }
    .trans-item { display: flex; justify-content: space-between; align-items: center; padding: 1rem; border: 1px solid var(--border); border-radius: 8px; transition: border-color 0.2s; }
    .trans-item:hover { border-color: var(--accent); }
    .trans-info { display: flex; flex-direction: column; gap: 4px; }
    .trans-user { font-size: 0.95rem; font-weight: 700; color: var(--text-main); }
    .trans-meta { font-size: 0.85rem; color: var(--text-muted); }
    .trans-right { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; }
    .trans-time { font-size: 0.75rem; color: var(--text-muted); }
    
    .badge-action { padding: 0.25rem 0.6rem; border-radius: 4px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
    .badge-borrowed { background: var(--warning-bg); color: #b45309; }
    .badge-returned { background: var(--success-bg); color: var(--success); }

    /* Action Links */
    .quick-links { display: flex; flex-direction: column; gap: 0.75rem; }
    .q-link { display: block; padding: 1rem; background: var(--background); border: 1px solid var(--border); border-radius: 8px; color: var(--text-main); text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: all 0.2s; text-align: center; }
    .q-link:hover { background: var(--primary); color: white; border-color: var(--primary); transform: translateY(-2px); }

    @media (max-width: 1024px) { .dashboard-layout { grid-template-columns: 1fr; } }
</style>
<?php
$page_styles = ob_get_clean();

// 2. YIELD CONTENT
ob_start();
?>
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon icon-blue"><i class="fas fa-users"></i></div>
        <div class="stat-details">
            <span class="stat-value"><?= number_format($total_members) ?></span>
            <span class="stat-label">Total Members</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-green"><i class="fas fa-book-reader"></i></div>
        <div class="stat-details">
            <span class="stat-value"><?= number_format($active_loans) ?></span>
            <span class="stat-label">Active Loans</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-red"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-details">
            <span class="stat-value"><?= number_format($overdue_items) ?></span>
            <span class="stat-label">Overdue Items</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-orange"><i class="fas fa-walking"></i></div>
        <div class="stat-details">
            <span class="stat-value"><?= number_format($today_visits) ?></span>
            <span class="stat-label">Visits Today</span>
        </div>
    </div>
</div>

<div class="dashboard-layout">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Transactions</h3>
            <a href="admin_circulation" class="card-action">View All</a>
        </div>
        <div class="trans-list">
            <?php foreach($recent_transactions as $trans): 
                $badgeClass = $trans['action'] === 'Borrowed' ? 'badge-borrowed' : 'badge-returned';
            ?>
            <div class="trans-item">
                <div class="trans-info">
                    <span class="trans-user"><?= htmlspecialchars($trans['user']) ?> <span style="font-size: 0.8rem; font-weight: 500; color: var(--text-muted); margin-left: 8px;">ID: <?= $trans['id'] ?></span></span>
                    <span class="trans-meta">Item: <strong><?= htmlspecialchars($trans['item']) ?></strong></span>
                </div>
                <div class="trans-right">
                    <span class="badge-action <?= $badgeClass ?>"><?= $trans['action'] ?></span>
                    <span class="trans-time"><?= $trans['time'] ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Quick Actions</h3>
        </div>
        <div class="quick-links">
            <a href="admin_circulation?action=issue" class="q-link">Issue / Borrow Item</a>
            <a href="admin_circulation?action=return" class="q-link">Process Return</a>
            <a href="admin_members?action=add" class="q-link">Register New Member</a>
            <a href="admin_reports" class="q-link">Generate Reports</a>
        </div>
    </div>
</div>
<?php
$page_content = ob_get_clean();

// 3. REQUIRE LAYOUT
require 'admin_layout.php';
?>