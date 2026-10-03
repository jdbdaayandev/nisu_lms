<?php
session_start();

$page_title = 'My Borrows - NISU Library';
$filter = isset($_GET['filter']) ? trim($_GET['filter']) : 'all';

// Mock Data
$all_borrows = [
    [
        'id' => 101, 'title' => 'Advanced Physics for Engineers', 'type_name' => 'Book',
        'barcode' => 'LIB-2026-001', 'borrow_date' => '2026-09-15', 'due_date' => '2026-09-29',
        'return_date' => null, 'status' => 'Overdue'
    ],
    [
        'id' => 102, 'title' => 'Chromebook Model X', 'type_name' => 'Equipment',
        'barcode' => 'EQ-LAP-042', 'borrow_date' => '2026-10-03', 'due_date' => '2026-10-04',
        'return_date' => null, 'status' => 'Active'
    ]
];

$filtered_borrows = array_filter($all_borrows, function($item) use ($filter) {
    if ($filter === 'active') return $item['status'] === 'Active';
    if ($filter === 'overdue') return $item['status'] === 'Overdue';
    if ($filter === 'returned') return $item['status'] === 'Returned';
    return true; 
});

function getItemVisuals($typeName) {
    if (stristr($typeName, 'equipment')) return ['icon' => 'fa-laptop', 'bg' => '#fff3e0', 'color' => '#f57c00'];
    return ['icon' => 'fa-book', 'bg' => '#e0e6ff', 'color' => '#1a3673'];
}

function formatDate($dateStr) {
    return $dateStr ? date('M d, Y', strtotime($dateStr)) : 'N/A';
}

// ==========================================
// 1. PUSH STYLES (@push('styles'))
// ==========================================
ob_start();
?>
<style>
    /* Local colors extending global roots */
    :root {
        --success: #10b981; --success-bg: #d1fae5;
        --danger: #ef4444;  --danger-bg: #fee2e2;
        --info: #3b82f6;    --info-bg: #dbeafe;
    }

    .page-hero { background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); padding: 3.5rem 20px; text-align: center; color: white; }
    .page-hero h1 { font-size: 2.2rem; font-weight: 800; margin-bottom: 0.5rem; }
    
    .main-container { padding: 2.5rem 5%; max-width: 1000px; margin: 0 auto; width: 100%; }

    .filter-tabs { display: flex; gap: 0.5rem; margin-bottom: 2rem; background: var(--surface); padding: 0.5rem; border-radius: 12px; border: 1px solid var(--border); overflow-x: auto; }
    .filter-tab { padding: 0.6rem 1.25rem; border-radius: 8px; font-size: 0.875rem; font-weight: 600; color: var(--text-muted); text-decoration: none; transition: all 0.2s; white-space: nowrap; }
    .filter-tab:hover { background: var(--background); color: var(--text-main); }
    .filter-tab.active { background: var(--primary); color: white; }

    .list-view { display: flex; flex-direction: column; gap: 1rem; }
    .list-item { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.25rem; display: flex; align-items: center; gap: 1.5rem; transition: all 0.2s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .list-item:hover { border-color: var(--accent); transform: translateY(-2px); }
    
    .item-icon-wrapper { width: 56px; height: 56px; border-radius: 12px; display: flex; justify-content: center; align-items: center; font-size: 1.3rem; flex-shrink: 0; }
    .item-content { flex: 1; display: flex; flex-direction: column; gap: 6px; }
    .item-title { font-size: 1.1rem; font-weight: 700; color: var(--primary); }
    .item-barcode { font-size: 0.8rem; font-family: monospace; background: var(--background); padding: 2px 6px; border-radius: 4px; color: var(--text-muted); border: 1px solid var(--border); }
    .item-meta-row { display: flex; gap: 1.5rem; font-size: 0.85rem; color: var(--text-muted); }
    
    .item-actions { display: flex; flex-direction: column; align-items: flex-end; gap: 12px; }
    .status-badge { padding: 0.35rem 0.75rem; border-radius: 50px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; text-transform: uppercase; }
    .status-active { background: var(--info-bg); color: var(--info); }
    .status-overdue { background: var(--danger-bg); color: var(--danger); }
    .status-returned { background: var(--background); color: var(--text-muted); border: 1px solid var(--border); }
    
    .btn-action { background: white; color: var(--primary); border: 1px solid var(--border); padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
    .btn-action:hover { background: var(--background); border-color: var(--primary); }
    .btn-action.renew { background: var(--primary); color: white; border: none; }
    .btn-action.renew:hover { background: var(--primary-hover); }

    .no-results { text-align: center; padding: 4rem 1rem; background: var(--surface); border: 1px dashed var(--border); border-radius: var(--radius); color: var(--text-muted); }
</style>
<?php
$page_styles = ob_get_clean();

// ==========================================
// 2. YIELD CONTENT (@section('content'))
// ==========================================
ob_start();
?>
<div class="page-hero">
    <h1>My Borrows</h1>
    <p>Track your active loans, view your borrowing history, and manage renewals.</p>
</div>

<div class="main-container">
    <div class="filter-tabs">
        <a href="?filter=all" class="filter-tab <?= $filter === 'all' ? 'active' : '' ?>">All Transactions</a>
        <a href="?filter=active" class="filter-tab <?= $filter === 'active' ? 'active' : '' ?>">Currently Borrowed</a>
        <a href="?filter=overdue" class="filter-tab <?= $filter === 'overdue' ? 'active' : '' ?>">Overdue</a>
        <a href="?filter=returned" class="filter-tab <?= $filter === 'returned' ? 'active' : '' ?>">Returned History</a>
    </div>
    
    <div class="list-view">
        <?php if (!empty($filtered_borrows)): ?>
            <?php foreach ($filtered_borrows as $borrow): 
                $visuals = getItemVisuals($borrow['type_name']);
                
                $statusClass = $borrow['status'] === 'Active' ? 'status-active' : ($borrow['status'] === 'Overdue' ? 'status-overdue' : 'status-returned');
                $statusIcon = $borrow['status'] === 'Active' ? 'fa-book-reader' : ($borrow['status'] === 'Overdue' ? 'fa-exclamation-triangle' : 'fa-check');
            ?>
                <div class="list-item">
                    <div class="item-icon-wrapper" style="background: <?= $visuals['bg'] ?>; color: <?= $visuals['color'] ?>;">
                        <i class="fas <?= $visuals['icon'] ?>"></i>
                    </div>
                    
                    <div class="item-content">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <h3 class="item-title"><?= htmlspecialchars($borrow['title']) ?></h3>
                            <span class="item-barcode"><?= htmlspecialchars($borrow['barcode']) ?></span>
                        </div>
                        
                        <div class="item-meta-row">
                            <div class="meta-datum"><i class="fas fa-calendar-alt"></i> <span><strong>Borrowed:</strong> <?= formatDate($borrow['borrow_date']) ?></span></div>
                            <div class="meta-datum">
                                <i class="fas fa-clock"></i> 
                                <span style="<?= $borrow['status'] === 'Overdue' ? 'color: var(--danger); font-weight: 600;' : '' ?>">
                                    <strong>Due:</strong> <?= formatDate($borrow['due_date']) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="item-actions">
                        <span class="status-badge <?= $statusClass ?>"><i class="fas <?= $statusIcon ?>"></i> <?= $borrow['status'] ?></span>
                        <?php if ($borrow['status'] === 'Active' || $borrow['status'] === 'Overdue'): ?>
                            <button class="btn-action renew" onclick="renewItem('<?= $borrow['barcode'] ?>')"><i class="fas fa-sync-alt"></i> Renew</button>
                        <?php else: ?>
                            <button class="btn-action"><i class="fas fa-eye"></i> Details</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-box-open" style="font-size:3rem; opacity:0.2; margin-bottom:1rem;"></i>
                <h3>No items found</h3>
                <p>You don't have any items matching this filter category.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
$page_content = ob_get_clean();

// ==========================================
// 3. PUSH SCRIPTS (@push('scripts'))
// ==========================================
ob_start();
?>
<script>
    // Local script isolate to my_borrows.php
    function renewItem(barcode) {
        if(confirm("Are you sure you want to request a renewal for item " + barcode + "?")) {
            // Ajax logic here
            alert("Renewal request submitted!");
        }
    }
</script>
<?php
$page_scripts = ob_get_clean();

// ==========================================
// 4. RENDER THE LAYOUT
// ==========================================
require 'layout.php';
?>