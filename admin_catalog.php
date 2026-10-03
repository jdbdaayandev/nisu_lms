<?php
session_start();

$page_title = 'Catalog Management';

// --- Capture Search & Filters ---
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$filter_category = isset($_GET['category']) ? trim($_GET['category']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';

// --- Mock Database of Catalog Items ---
$all_items = [
    ['id' => 1, 'accession' => 'ACC-2026-001', 'title' => 'The Pragmatic Programmer', 'author' => 'David Thomas, Andrew Hunt', 'category' => 'Computer Science', 'type' => 'Book', 'status' => 'Available', 'added' => '2026-01-15'],
    ['id' => 2, 'accession' => 'ACC-2026-002', 'title' => 'Introduction to Algorithms', 'author' => 'Thomas H. Cormen', 'category' => 'Computer Science', 'type' => 'Book', 'status' => 'Borrowed', 'added' => '2026-02-10'],
    ['id' => 3, 'accession' => 'ACC-2026-003', 'title' => 'University Physics with Modern Physics', 'author' => 'Hugh D. Young', 'category' => 'Science & Engineering', 'type' => 'Book', 'status' => 'Available', 'added' => '2026-03-05'],
    ['id' => 4, 'accession' => 'EQP-2026-014', 'title' => 'Lenovo ThinkPad T14', 'author' => 'Lenovo', 'category' => 'Computing Devices', 'type' => 'Equipment', 'status' => 'Maintenance', 'added' => '2026-05-20'],
    ['id' => 5, 'accession' => 'ACC-2026-005', 'title' => 'To Kill a Mockingbird', 'author' => 'Harper Lee', 'category' => 'Classic Literature', 'type' => 'Book', 'status' => 'Available', 'added' => '2026-06-12'],
    ['id' => 6, 'accession' => 'MED-2026-009', 'title' => 'Planet Earth Complete Series', 'author' => 'BBC Earth', 'category' => 'General Reference', 'type' => 'Media', 'status' => 'Lost', 'added' => '2026-07-01'],
    ['id' => 7, 'accession' => 'ACC-2026-007', 'title' => 'Clean Code', 'author' => 'Robert C. Martin', 'category' => 'Computer Science', 'type' => 'Book', 'status' => 'Borrowed', 'added' => '2026-08-15'],
];

// --- Filtering Logic ---
$filtered_items = array_filter($all_items, function($item) use ($search_query, $filter_category, $filter_status) {
    $match_cat = empty($filter_category) || strtolower($item['category']) === strtolower($filter_category);
    $match_status = empty($filter_status) || strtolower($item['status']) === strtolower($filter_status);
    
    $match_text = true;
    if (!empty($search_query)) {
        $q = strtolower($search_query);
        $match_text = (
            strpos(strtolower($item['title']), $q) !== false ||
            strpos(strtolower($item['accession']), $q) !== false ||
            strpos(strtolower($item['author']), $q) !== false
        );
    }
    
    return $match_cat && $match_status && $match_text;
});

// --- Pagination Logic ---
$total_items = count($filtered_items);
$items_per_page = 5; 
$total_pages = ceil($total_items / $items_per_page);

$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;

$offset = ($current_page - 1) * $items_per_page;
$paginated_items = array_slice($filtered_items, $offset, $items_per_page);

$showing_start = $total_items > 0 ? $offset + 1 : 0;
$showing_end = min($offset + $items_per_page, $total_items);

function buildPaginationUrl($page, $q, $cat, $status) {
    return "?q=" . urlencode($q) . "&category=" . urlencode($cat) . "&status=" . urlencode($status) . "&page=" . $page;
}

// Labels for Custom Dropdowns
$cat_label = empty($filter_category) ? 'All Categories' : $filter_category;
$status_label = empty($filter_status) ? 'All Statuses' : $filter_status;

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

    .btn-primary { background: var(--primary); color: white; border: none; padding: 0.6rem 1.25rem; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s; text-decoration: none; }
    .btn-primary:hover { background: var(--primary-hover); }

    /* Table Container */
    .table-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); overflow: hidden; }
    .table-responsive { width: 100%; overflow-x: auto; }
    
    .admin-table { width: 100%; border-collapse: collapse; text-align: left; }
    .admin-table th { padding: 1rem 1.25rem; background: var(--background); font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border); white-space: nowrap; }
    .admin-table td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); font-size: 0.9rem; color: var(--text-main); vertical-align: middle; }
    .admin-table tbody tr { transition: background 0.2s; }
    .admin-table tbody tr:hover { background: rgba(244, 247, 250, 0.5); }
    .admin-table tbody tr:last-child td { border-bottom: none; }

    /* Item Identity Cell */
    .item-identity { display: flex; align-items: flex-start; gap: 12px; }
    .item-cover-placeholder { width: 38px; height: 50px; background: #e2e8f0; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 1.2rem; flex-shrink: 0; }
    .item-cover-placeholder.book { background: #e0e6ff; color: var(--primary); }
    .item-cover-placeholder.equipment { background: #fff3e0; color: #e65100; }
    .item-cover-placeholder.media { background: #f3e5f5; color: #6a1b9a; }
    
    .item-details { display: flex; flex-direction: column; gap: 2px; }
    .item-title { font-weight: 700; color: var(--primary); font-size: 0.95rem; line-height: 1.3; }
    .item-author { font-size: 0.8rem; color: var(--text-muted); }

    /* Badges */
    .badge { padding: 0.25rem 0.6rem; border-radius: 50px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; }
    .badge-available { background: var(--success-bg); color: var(--success); }
    .badge-borrowed { background: var(--warning-bg); color: #b45309; }
    .badge-maintenance { background: var(--background); color: var(--text-muted); border: 1px solid var(--border); }
    .badge-lost { background: var(--danger-bg); color: var(--danger); }
    
    .type-badge { font-size: 0.75rem; font-weight: 600; color: var(--text-main); background: var(--background); padding: 2px 8px; border-radius: 6px; border: 1px solid var(--border); white-space: nowrap; display: inline-block; margin-top: 4px;}

    /* Actions */
    .action-cell { display: flex; gap: 8px; }
    .btn-icon { width: 32px; height: 32px; border-radius: 6px; border: 1px solid var(--border); background: white; color: var(--text-muted); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; text-decoration: none; }
    .btn-icon:hover { border-color: var(--primary); color: var(--primary); background: var(--background); }
    .btn-icon.danger:hover { border-color: var(--danger); color: var(--danger); background: var(--danger-bg); }

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
            <input type="text" name="q" class="search-input" placeholder="Search title, author, or accession..." value="<?= htmlspecialchars($search_query) ?>" onchange="this.form.submit()">
        </div>
        
        <!-- Category Filter -->
        <div class="custom-select-wrapper filter-dropdown">
            <input type="hidden" name="category" value="<?= htmlspecialchars($filter_category) ?>">
            <button type="button" class="custom-select-toggle">
                <span class="select-label"><?= $cat_label ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="custom-select-menu">
                <div class="custom-select-item <?= empty($filter_category) ? 'selected' : '' ?>" data-value="">All Categories</div>
                <div class="custom-select-item <?= $filter_category === 'Computer Science' ? 'selected' : '' ?>" data-value="Computer Science">Computer Science</div>
                <div class="custom-select-item <?= $filter_category === 'Science & Engineering' ? 'selected' : '' ?>" data-value="Science & Engineering">Science & Engineering</div>
                <div class="custom-select-item <?= $filter_category === 'Classic Literature' ? 'selected' : '' ?>" data-value="Classic Literature">Classic Literature</div>
                <div class="custom-select-item <?= $filter_category === 'Computing Devices' ? 'selected' : '' ?>" data-value="Computing Devices">Computing Devices</div>
                <div class="custom-select-item <?= $filter_category === 'General Reference' ? 'selected' : '' ?>" data-value="General Reference">General Reference</div>
            </div>
        </div>
        
        <!-- Status Filter -->
        <div class="custom-select-wrapper filter-dropdown">
            <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>">
            <button type="button" class="custom-select-toggle">
                <span class="select-label"><?= $status_label ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="custom-select-menu">
                <div class="custom-select-item <?= empty($filter_status) ? 'selected' : '' ?>" data-value="">All Statuses</div>
                <div class="custom-select-item <?= $filter_status === 'Available' ? 'selected' : '' ?>" data-value="Available">Available</div>
                <div class="custom-select-item <?= $filter_status === 'Borrowed' ? 'selected' : '' ?>" data-value="Borrowed">Borrowed</div>
                <div class="custom-select-item <?= $filter_status === 'Maintenance' ? 'selected' : '' ?>" data-value="Maintenance">Maintenance</div>
                <div class="custom-select-item <?= $filter_status === 'Lost' ? 'selected' : '' ?>" data-value="Lost">Lost</div>
            </div>
        </div>
        
        <!-- Clear Filters -->
        <?php if(!empty($search_query) || !empty($filter_category) || !empty($filter_status)): ?>
            <a href="admin_catalog" class="btn-icon" style="width: auto; padding: 0 1rem; border-color: transparent;" title="Clear Filters">Clear</a>
        <?php endif; ?>
    </form>

    <!-- Add Button -> Routes to a dedicated Add Item page -->
    <a href="admin_add_catalog" class="btn-primary">
        <i class="fas fa-plus"></i> Add Item
    </a>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Item Details</th>
                    <th>Accession No.</th>
                    <th>Category & Type</th>
                    <th>Status</th>
                    <th>Date Added</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($paginated_items)): ?>
                    <?php foreach ($paginated_items as $item): 
                        // Determine Badge Class
                        $badge_class = 'badge-available';
                        if ($item['status'] === 'Borrowed') $badge_class = 'badge-borrowed';
                        if ($item['status'] === 'Maintenance') $badge_class = 'badge-maintenance';
                        if ($item['status'] === 'Lost') $badge_class = 'badge-lost';
                        
                        // Determine Icon/Style based on type
                        $icon_class = 'fa-book';
                        $cover_class = 'book';
                        if ($item['type'] === 'Equipment') { $icon_class = 'fa-laptop'; $cover_class = 'equipment'; }
                        if ($item['type'] === 'Media') { $icon_class = 'fa-compact-disc'; $cover_class = 'media'; }
                    ?>
                    <tr>
                        <td>
                            <div class="item-identity">
                                <div class="item-cover-placeholder <?= $cover_class ?>">
                                    <i class="fas <?= $icon_class ?>"></i>
                                </div>
                                <div class="item-details">
                                    <span class="item-title"><?= htmlspecialchars($item['title']) ?></span>
                                    <span class="item-author"><?= htmlspecialchars($item['author']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td style="font-family: monospace; font-size: 0.85rem; font-weight: 600;">
                            <?= htmlspecialchars($item['accession']) ?>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem; color: var(--text-main); font-weight: 500;">
                                <?= htmlspecialchars($item['category']) ?>
                            </div>
                            <span class="type-badge"><?= htmlspecialchars($item['type']) ?></span>
                        </td>
                        <td>
                            <span class="badge <?= $badge_class ?>"><?= $item['status'] ?></span>
                        </td>
                        <td style="color: var(--text-muted); font-size: 0.85rem;">
                            <?= date('M d, Y', strtotime($item['added'])) ?>
                        </td>
                        <td>
                            <div class="action-cell" style="justify-content: flex-end;">
                                <a href="#" class="btn-icon" title="View Details"><i class="fas fa-eye"></i></a>
                                <a href="#" class="btn-icon" title="Edit Item"><i class="fas fa-edit"></i></a>
                                <a href="#" class="btn-icon danger" title="Delete Item"><i class="fas fa-trash-alt"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">
                            <div class="no-results">
                                <i class="fas fa-book-open"></i>
                                <h3>No catalog items found</h3>
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
            Showing <span><?= $showing_start ?></span> to <span><?= $showing_end ?></span> of <span><?= $total_items ?></span> items
        </div>
        
        <?php if ($total_pages > 1): ?>
        <div class="pagination-controls">
            <?php if ($current_page > 1): ?>
                <a href="<?= buildPaginationUrl($current_page - 1, $search_query, $filter_category, $filter_status) ?>" class="page-link"><i class="fas fa-chevron-left"></i></a>
            <?php else: ?>
                <span class="page-link disabled"><i class="fas fa-chevron-left"></i></span>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="<?= buildPaginationUrl($i, $search_query, $filter_category, $filter_status) ?>" class="page-link <?= ($i === $current_page) ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            
            <?php if ($current_page < $total_pages): ?>
                <a href="<?= buildPaginationUrl($current_page + 1, $search_query, $filter_category, $filter_status) ?>" class="page-link"><i class="fas fa-chevron-right"></i></a>
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

        // Delete confirmation logic
        document.querySelectorAll('.btn-icon.danger').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if(confirm('Are you sure you want to permanently delete this catalog item?')) {
                    alert('Item deletion triggered.');
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