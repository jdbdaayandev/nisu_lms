<?php
session_start();

$page_title = 'Circulation & Transactions';

// --- Capture Search & Filters ---
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';

// --- Mock Database of Circulation Transactions ---
// Current date context: Oct 2026
$all_transactions = [
    ['id' => 1, 'trans_code' => 'TRX-2026-1001', 'member_name' => 'Maria Clara', 'member_id' => '2023-0101', 'item_title' => 'The Pragmatic Programmer', 'accession' => 'ACC-2026-001', 'borrow_date' => '2026-10-01', 'due_date' => '2026-10-15', 'status' => 'Active'],
    ['id' => 2, 'trans_code' => 'TRX-2026-0942', 'member_name' => 'Juan Dela Cruz', 'member_id' => '2021-0042', 'item_title' => 'Lenovo ThinkPad T14', 'accession' => 'EQP-2026-014', 'borrow_date' => '2026-09-25', 'due_date' => '2026-09-28', 'status' => 'Overdue'],
    ['id' => 3, 'trans_code' => 'TRX-2026-0881', 'member_name' => 'Dr. Jose Rizal', 'member_id' => 'FAC-0091', 'item_title' => 'University Physics with Modern Physics', 'accession' => 'ACC-2026-003', 'borrow_date' => '2026-09-10', 'due_date' => '2026-10-10', 'status' => 'Active'],
    ['id' => 4, 'trans_code' => 'TRX-2026-0955', 'member_name' => 'Andres Bonifacio', 'member_id' => '2022-0855', 'item_title' => 'To Kill a Mockingbird', 'accession' => 'ACC-2026-005', 'borrow_date' => '2026-09-28', 'due_date' => '2026-10-05', 'status' => 'Active'],
    ['id' => 5, 'trans_code' => 'TRX-2026-0712', 'member_name' => 'Emilio Aguinaldo', 'member_id' => '2024-0112', 'item_title' => 'Clean Code', 'accession' => 'ACC-2026-007', 'borrow_date' => '2026-08-15', 'due_date' => '2026-08-29', 'status' => 'Returned'],
    ['id' => 6, 'trans_code' => 'TRX-2026-0990', 'member_name' => 'Gabriela Silang', 'member_id' => '2025-0304', 'item_title' => 'Introduction to Algorithms', 'accession' => 'ACC-2026-002', 'borrow_date' => '2026-09-29', 'due_date' => '2026-10-13', 'status' => 'Active'],
];

// --- Filtering Logic ---
$filtered_transactions = array_filter($all_transactions, function($trans) use ($search_query, $filter_status) {
    $match_status = empty($filter_status) || strtolower($trans['status']) === strtolower($filter_status);
    
    $match_text = true;
    if (!empty($search_query)) {
        $q = strtolower($search_query);
        $match_text = (
            strpos(strtolower($trans['trans_code']), $q) !== false ||
            strpos(strtolower($trans['member_name']), $q) !== false ||
            strpos(strtolower($trans['member_id']), $q) !== false ||
            strpos(strtolower($trans['item_title']), $q) !== false ||
            strpos(strtolower($trans['accession']), $q) !== false
        );
    }
    
    return $match_status && $match_text;
});

// --- Pagination Logic ---
$total_items = count($filtered_transactions);
$items_per_page = 5; 
$total_pages = ceil($total_items / $items_per_page);

$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;

$offset = ($current_page - 1) * $items_per_page;
$paginated_transactions = array_slice($filtered_transactions, $offset, $items_per_page);

$showing_start = $total_items > 0 ? $offset + 1 : 0;
$showing_end = min($offset + $items_per_page, $total_items);

function buildPaginationUrl($page, $q, $status) {
    return "?q=" . urlencode($q) . "&status=" . urlencode($status) . "&page=" . $page;
}

// Labels for Custom Dropdown
$status_label = empty($filter_status) ? 'All Transactions' : $filter_status;

// ==========================================
// 1. PUSH STYLES
// ==========================================
ob_start();
?>
<style>
    /* Toolbar */
    .page-toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
    .filter-group { display: flex; gap: 0.75rem; flex-wrap: wrap; flex: 1; align-items: center; }
    
    .search-input-wrapper { position: relative; width: 100%; max-width: 320px; }
    .search-input-wrapper i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
    .search-input { width: 100%; padding: 0.6rem 1rem 0.6rem 2.5rem; border: 1px solid var(--border); border-radius: 8px; font-family: inherit; font-size: 0.9rem; color: var(--text-main); outline: none; transition: border-color 0.2s; }
    .search-input:focus { border-color: var(--accent); }

    /* Custom Dropdowns */
    .custom-select-wrapper { position: relative; min-width: 170px; user-select: none; }
    .custom-select-toggle {
        display: flex; justify-content: space-between; align-items: center;
        cursor: pointer; text-align: left; width: 100%;
        padding: 0.6rem 1rem; border: 1px solid var(--border); border-radius: 8px;
        font-family: inherit; font-size: 0.9rem; font-weight: 500; color: var(--text-main); background: white;
        transition: border-color 0.2s, box-shadow 0.2s; outline: none;
    }
    .custom-select-toggle i { color: var(--text-muted); font-size: 0.75rem; transition: transform 0.2s; pointer-events: none; margin-left: 8px; }
    .custom-select-wrapper.active .custom-select-toggle { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(91, 125, 232, 0.15); }
    .custom-select-wrapper.active .custom-select-toggle i { transform: rotate(180deg); color: var(--accent); }
    
    .custom-select-menu {
        position: absolute; top: calc(100% + 5px); left: 0; width: 100%;
        background: white; border: 1px solid var(--border); border-radius: 8px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        opacity: 0; visibility: hidden; transform: translateY(-10px);
        transition: all 0.2s ease; z-index: 50; padding: 0.5rem;
    }
    .custom-select-wrapper.active .custom-select-menu { opacity: 1; visibility: visible; transform: translateY(0); }
    
    .custom-select-item { padding: 0.5rem 0.75rem; border-radius: 6px; font-size: 0.85rem; font-weight: 500; color: var(--text-main); cursor: pointer; transition: background 0.2s, color 0.2s; }
    .custom-select-item:hover { background: var(--background); color: var(--primary); }
    .custom-select-item.selected { background: rgba(91, 125, 232, 0.1); color: var(--primary); font-weight: 600; }

    /* Action Buttons */
    .action-buttons { display: flex; gap: 0.75rem; flex-wrap: wrap; }
    
    .btn { padding: 0.6rem 1.25rem; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; text-decoration: none; border: none; }
    .btn-primary { background: var(--primary); color: white; }
    .btn-primary:hover { background: var(--primary-hover); }
    
    .btn-success { background: var(--success); color: white; }
    .btn-success:hover { background: #059669; }

    /* Table Container */
    .table-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); overflow: hidden; }
    .table-responsive { width: 100%; overflow-x: auto; }
    
    .admin-table { width: 100%; border-collapse: collapse; text-align: left; }
    .admin-table th { padding: 1rem 1.25rem; background: var(--background); font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border); white-space: nowrap; }
    .admin-table td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); font-size: 0.9rem; color: var(--text-main); vertical-align: top; }
    .admin-table tbody tr { transition: background 0.2s; }
    .admin-table tbody tr:hover { background: rgba(244, 247, 250, 0.5); }
    .admin-table tbody tr:last-child td { border-bottom: none; }

    /* Two-line Data Cells */
    .data-primary { font-weight: 700; color: var(--primary); font-size: 0.95rem; margin-bottom: 4px; display: block; line-height: 1.2;}
    .data-secondary { font-size: 0.8rem; color: var(--text-muted); font-family: monospace; display: block;}
    
    .date-block { display: flex; flex-direction: column; gap: 4px; }
    .date-label { font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; }
    .date-value { font-size: 0.85rem; font-weight: 500; color: var(--text-main); }
    .date-value.danger { color: var(--danger); font-weight: 700; }

    /* Badges */
    .badge { padding: 0.35rem 0.75rem; border-radius: 50px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; display: inline-block; }
    .badge-active { background: #dbeafe; color: #1d4ed8; }
    .badge-overdue { background: var(--danger-bg); color: var(--danger); }
    .badge-returned { background: var(--background); color: var(--text-muted); border: 1px solid var(--border); }

    /* Actions */
    .action-cell { display: flex; gap: 8px; }
    .btn-icon { width: 32px; height: 32px; border-radius: 6px; border: 1px solid var(--border); background: white; color: var(--text-muted); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; text-decoration: none; }
    .btn-icon:hover { border-color: var(--primary); color: var(--primary); background: var(--background); }
    .btn-icon.success:hover { border-color: var(--success); color: var(--success); background: var(--success-bg); }

    /* Empty State */
    .no-results { text-align: center; padding: 4rem 1rem; color: var(--text-muted); }
    .no-results i { font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.3; }

    /* Pagination */
    .pagination-container { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem; border-top: 1px solid var(--border); background: white; }
    .pagination-info { font-size: 0.85rem; color: var(--text-muted); }
    .pagination-info span { font-weight: 700; color: var(--text-main); }
    .pagination-controls { display: flex; gap: 0.375rem; }
    .page-link { display: inline-flex; align-items: center; justify-content: center; height: 32px; min-width: 32px; padding: 0 0.5rem; font-size: 0.85rem; font-weight: 600; color: var(--text-main); background: white; border: 1px solid var(--border); border-radius: 6px; text-decoration: none; transition: all 0.2s; }
    .page-link:hover:not(.disabled):not(.active) { background: var(--background); }
    .page-link.active { background: var(--primary); color: white; border-color: var(--primary); }
    .page-link.disabled { opacity: 0.5; cursor: not-allowed; background: var(--background); pointer-events: none; }

    @media (max-width: 768px) {
        .page-toolbar { flex-direction: column; align-items: stretch; }
        .filter-group { flex-direction: column; align-items: stretch; }
        .search-input-wrapper { max-width: 100%; }
        .action-buttons { flex-direction: column; }
        .pagination-container { flex-direction: column; gap: 1rem; }
    }
</style>
<?php
$page_styles = ob_get_clean();

// ==========================================
// 2. YIELD CONTENT
// ==========================================
ob_start();
?>
<div class="page-toolbar">
    <!-- Filters Form -->
    <form class="filter-group" id="filterForm" method="GET" action="">
        <!-- Search -->
        <div class="search-input-wrapper">
            <i class="fas fa-search"></i>
            <input type="text" name="q" class="search-input" placeholder="Search member, item, or TRX code..." value="<?= htmlspecialchars($search_query) ?>" onchange="this.form.submit()">
        </div>
        
        <!-- Status Filter -->
        <div class="custom-select-wrapper filter-dropdown">
            <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>">
            <button type="button" class="custom-select-toggle">
                <span class="select-label"><?= $status_label ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="custom-select-menu">
                <div class="custom-select-item <?= empty($filter_status) ? 'selected' : '' ?>" data-value="">All Transactions</div>
                <div class="custom-select-item <?= $filter_status === 'Active' ? 'selected' : '' ?>" data-value="Active">Active Loans</div>
                <div class="custom-select-item <?= $filter_status === 'Overdue' ? 'selected' : '' ?>" data-value="Overdue">Overdue</div>
                <div class="custom-select-item <?= $filter_status === 'Returned' ? 'selected' : '' ?>" data-value="Returned">Returned</div>
            </div>
        </div>
        
        <!-- Clear Filters -->
        <?php if(!empty($search_query) || !empty($filter_status)): ?>
            <a href="admin_circulation" class="btn-icon" style="width: auto; padding: 0 1rem; border-color: transparent;" title="Clear Filters">Clear</a>
        <?php endif; ?>
    </form>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="admin_issue_item" class="btn btn-primary">
            <i class="fas fa-hand-holding"></i> Issue Item
        </a>
        <a href="admin_return_item" class="btn btn-success">
            <i class="fas fa-undo"></i> Process Return
        </a>
    </div>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Transaction</th>
                    <th>Member Details</th>
                    <th>Item Details</th>
                    <th>Timeline</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($paginated_transactions)): ?>
                    <?php foreach ($paginated_transactions as $trans): 
                        // Determine Badge Class
                        $badge_class = 'badge-returned';
                        if ($trans['status'] === 'Active') $badge_class = 'badge-active';
                        if ($trans['status'] === 'Overdue') $badge_class = 'badge-overdue';
                        
                        $is_overdue = ($trans['status'] === 'Overdue');
                    ?>
                    <tr>
                        <td style="font-family: monospace; font-weight: 600; font-size: 0.85rem;">
                            <?= htmlspecialchars($trans['trans_code']) ?>
                        </td>
                        <td>
                            <span class="data-primary"><?= htmlspecialchars($trans['member_name']) ?></span>
                            <span class="data-secondary">ID: <?= htmlspecialchars($trans['member_id']) ?></span>
                        </td>
                        <td>
                            <span class="data-primary"><?= htmlspecialchars($trans['item_title']) ?></span>
                            <span class="data-secondary">ACC: <?= htmlspecialchars($trans['accession']) ?></span>
                        </td>
                        <td>
                            <div class="date-block">
                                <div><span class="date-label">Borrowed:</span> <span class="date-value"><?= date('M d, Y', strtotime($trans['borrow_date'])) ?></span></div>
                                <div><span class="date-label">Due:</span> <span class="date-value <?= $is_overdue ? 'danger' : '' ?>"><?= date('M d, Y', strtotime($trans['due_date'])) ?></span></div>
                            </div>
                        </td>
                        <td style="vertical-align: middle;">
                            <span class="badge <?= $badge_class ?>"><?= $trans['status'] ?></span>
                        </td>
                        <td style="vertical-align: middle;">
                            <div class="action-cell" style="justify-content: flex-end;">
                                <a href="#" class="btn-icon" title="View Details"><i class="fas fa-eye"></i></a>
                                
                                <?php if($trans['status'] !== 'Returned'): ?>
                                    <!-- Mark as Returned Button -->
                                    <button class="btn-icon success btn-mark-returned" title="Mark as Returned">
                                        <i class="fas fa-check"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">
                            <div class="no-results">
                                <i class="fas fa-exchange-alt"></i>
                                <h3>No transactions found</h3>
                                <p>Try adjusting your search or filter criteria.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    <?php if ($total_items > 0): ?>
    <div class="pagination-container">
        <div class="pagination-info">
            Showing <span><?= $showing_start ?></span> to <span><?= $showing_end ?></span> of <span><?= $total_items ?></span> transactions
        </div>
        
        <?php if ($total_pages > 1): ?>
        <div class="pagination-controls">
            <?php if ($current_page > 1): ?>
                <a href="<?= buildPaginationUrl($current_page - 1, $search_query, $filter_status) ?>" class="page-link"><i class="fas fa-chevron-left"></i></a>
            <?php else: ?>
                <span class="page-link disabled"><i class="fas fa-chevron-left"></i></span>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="<?= buildPaginationUrl($i, $search_query, $filter_status) ?>" class="page-link <?= ($i === $current_page) ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            
            <?php if ($current_page < $total_pages): ?>
                <a href="<?= buildPaginationUrl($current_page + 1, $search_query, $filter_status) ?>" class="page-link"><i class="fas fa-chevron-right"></i></a>
            <?php else: ?>
                <span class="page-link disabled"><i class="fas fa-chevron-right"></i></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php
$page_content = ob_get_clean();

// ==========================================
// 3. PUSH SCRIPTS
// ==========================================
ob_start();
?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const filterForm = document.getElementById('filterForm');

        // Logic for custom Dropdowns
        document.querySelectorAll('.filter-dropdown').forEach(wrapper => {
            const toggle = wrapper.querySelector('.custom-select-toggle');
            const label = toggle.querySelector('.select-label');
            const input = wrapper.querySelector('input[type="hidden"]');
            const items = wrapper.querySelectorAll('.custom-select-item');

            // Toggle menu
            toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                // Close others
                document.querySelectorAll('.filter-dropdown.active').forEach(w => {
                    if (w !== wrapper) w.classList.remove('active');
                });
                wrapper.classList.toggle('active');
            });

            // On item select
            items.forEach(item => {
                item.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const value = item.getAttribute('data-value');
                    
                    // Update hidden input
                    input.value = value;
                    label.innerText = item.innerText;

                    // Close menu
                    wrapper.classList.remove('active');

                    // Auto-submit form
                    filterForm.submit();
                });
            });
        });

        // Close dropdowns if clicked outside
        document.addEventListener('click', () => {
            document.querySelectorAll('.filter-dropdown').forEach(w => w.classList.remove('active'));
        });

        // Mark as returned logic
        document.querySelectorAll('.btn-mark-returned').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if(confirm('Are you sure you want to mark this item as returned?')) {
                    // Ajax or redirect call would go here
                    alert('Item marked as returned successfully.');
                }
            });
        });
    });
</script>
<?php
$page_scripts = ob_get_clean();

// ==========================================
// 4. REQUIRE LAYOUT
// ==========================================
require 'admin_layout.php';
?>