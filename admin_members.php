<?php
session_start();

$page_title = 'Members Management';

// --- Capture Search & Filters ---
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$filter_type  = isset($_GET['type']) ? trim($_GET['type']) : '';
$filter_status= isset($_GET['status']) ? trim($_GET['status']) : '';

// --- Mock Database of Members ---
$all_members = [
    ['id' => '2023-0101', 'name' => 'Maria Clara', 'email' => 'm.clara@nisu.edu.ph', 'type' => 'Student', 'status' => 'Active', 'joined' => '2023-08-15', 'borrows' => 2],
    ['id' => '2021-0042', 'name' => 'Juan Dela Cruz', 'email' => 'j.delacruz@nisu.edu.ph', 'type' => 'Student', 'status' => 'Active', 'joined' => '2021-09-01', 'borrows' => 0],
    ['id' => 'FAC-0091',  'name' => 'Dr. Jose Rizal', 'email' => 'j.rizal@nisu.edu.ph', 'type' => 'Faculty', 'status' => 'Active', 'joined' => '2015-06-10', 'borrows' => 5],
    ['id' => '2022-0855', 'name' => 'Andres Bonifacio', 'email' => 'a.bonifacio@nisu.edu.ph', 'type' => 'Student', 'status' => 'Suspended', 'joined' => '2022-08-20', 'borrows' => 1],
    ['id' => '2024-0112', 'name' => 'Emilio Aguinaldo', 'email' => 'e.aguinaldo@nisu.edu.ph', 'type' => 'Student', 'status' => 'Inactive', 'joined' => '2024-01-15', 'borrows' => 0],
    ['id' => 'STF-0012',  'name' => 'Apolinario Mabini', 'email' => 'a.mabini@nisu.edu.ph', 'type' => 'Staff', 'status' => 'Active', 'joined' => '2018-11-05', 'borrows' => 3],
    ['id' => '2025-0304', 'name' => 'Gabriela Silang', 'email' => 'g.silang@nisu.edu.ph', 'type' => 'Student', 'status' => 'Active', 'joined' => '2025-08-10', 'borrows' => 1],
];

// --- Filtering Logic ---
$filtered_members = array_filter($all_members, function($member) use ($search_query, $filter_type, $filter_status) {
    $match_type = empty($filter_type) || strtolower($member['type']) === strtolower($filter_type);
    $match_status = empty($filter_status) || strtolower($member['status']) === strtolower($filter_status);
    
    $match_text = true;
    if (!empty($search_query)) {
        $q = strtolower($search_query);
        $match_text = (
            strpos(strtolower($member['name']), $q) !== false ||
            strpos(strtolower($member['id']), $q) !== false ||
            strpos(strtolower($member['email']), $q) !== false
        );
    }
    
    return $match_type && $match_status && $match_text;
});

// --- Pagination Logic ---
$total_items = count($filtered_members);
$items_per_page = 5; 
$total_pages = ceil($total_items / $items_per_page);

$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;

$offset = ($current_page - 1) * $items_per_page;
$paginated_members = array_slice($filtered_members, $offset, $items_per_page);

$showing_start = $total_items > 0 ? $offset + 1 : 0;
$showing_end = min($offset + $items_per_page, $total_items);

function buildPaginationUrl($page, $q, $type, $status) {
    return "?q=" . urlencode($q) . "&type=" . urlencode($type) . "&status=" . urlencode($status) . "&page=" . $page;
}

// Labels for Custom Dropdowns
$type_label = empty($filter_type) ? 'All Types' : $filter_type;
$status_label = empty($filter_status) ? 'All Statuses' : $filter_status;

// ==========================================
// 1. PUSH STYLES
// ==========================================
ob_start();
?>
<style>
    /* Top Toolbar */
    .page-toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
    
    .filter-group { display: flex; gap: 0.75rem; flex-wrap: wrap; flex: 1; align-items: center; }
    
    .search-input-wrapper { position: relative; width: 100%; max-width: 300px; }
    .search-input-wrapper i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
    .search-input { width: 100%; padding: 0.6rem 1rem 0.6rem 2.5rem; border: 1px solid var(--border); border-radius: 8px; font-family: inherit; font-size: 0.9rem; color: var(--text-main); outline: none; transition: border-color 0.2s; }
    .search-input:focus { border-color: var(--accent); }

    /* --- Custom UI Dropdowns for Filters --- */
    .custom-select-wrapper { position: relative; min-width: 160px; user-select: none; }
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
    
    .custom-select-item {
        padding: 0.5rem 0.75rem; border-radius: 6px; font-size: 0.85rem; font-weight: 500;
        color: var(--text-main); cursor: pointer; transition: background 0.2s, color 0.2s;
    }
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

    /* Member Identity Cell */
    .member-identity { display: flex; align-items: center; gap: 12px; }
    .member-avatar { width: 36px; height: 36px; border-radius: 50%; background: #e0e6ff; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 700; flex-shrink: 0; }
    .member-details { display: flex; flex-direction: column; }
    .member-name { font-weight: 700; color: var(--primary); }
    .member-email { font-size: 0.8rem; color: var(--text-muted); }

    /* Badges */
    .badge { padding: 0.25rem 0.6rem; border-radius: 50px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; }
    .badge-active { background: var(--success-bg); color: var(--success); }
    .badge-suspended { background: var(--danger-bg); color: var(--danger); }
    .badge-inactive { background: var(--background); color: var(--text-muted); border: 1px solid var(--border); }
    
    .type-badge { font-size: 0.8rem; color: var(--text-muted); background: var(--background); padding: 2px 8px; border-radius: 6px; border: 1px solid var(--border); }

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
        <!-- Search Input -->
        <div class="search-input-wrapper">
            <i class="fas fa-search"></i>
            <input type="text" name="q" class="search-input" placeholder="Search ID, Name, or Email..." value="<?= htmlspecialchars($search_query) ?>" onchange="this.form.submit()">
        </div>
        
        <!-- Custom Select for Type -->
        <div class="custom-select-wrapper filter-dropdown">
            <input type="hidden" name="type" value="<?= htmlspecialchars($filter_type) ?>">
            <button type="button" class="custom-select-toggle">
                <span class="select-label"><?= $type_label ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="custom-select-menu">
                <div class="custom-select-item <?= empty($filter_type) ? 'selected' : '' ?>" data-value="">All Types</div>
                <div class="custom-select-item <?= $filter_type === 'Student' ? 'selected' : '' ?>" data-value="Student">Student</div>
                <div class="custom-select-item <?= $filter_type === 'Faculty' ? 'selected' : '' ?>" data-value="Faculty">Faculty</div>
                <div class="custom-select-item <?= $filter_type === 'Staff' ? 'selected' : '' ?>" data-value="Staff">Staff</div>
            </div>
        </div>
        
        <!-- Custom Select for Status -->
        <div class="custom-select-wrapper filter-dropdown">
            <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>">
            <button type="button" class="custom-select-toggle">
                <span class="select-label"><?= $status_label ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="custom-select-menu">
                <div class="custom-select-item <?= empty($filter_status) ? 'selected' : '' ?>" data-value="">All Statuses</div>
                <div class="custom-select-item <?= $filter_status === 'Active' ? 'selected' : '' ?>" data-value="Active">Active</div>
                <div class="custom-select-item <?= $filter_status === 'Suspended' ? 'selected' : '' ?>" data-value="Suspended">Suspended</div>
                <div class="custom-select-item <?= $filter_status === 'Inactive' ? 'selected' : '' ?>" data-value="Inactive">Inactive</div>
            </div>
        </div>
        
        <!-- Clear Filters Button -->
        <?php if(!empty($search_query) || !empty($filter_type) || !empty($filter_status)): ?>
            <a href="admin_members" class="btn-icon" style="width: auto; padding: 0 1rem; border-color: transparent;" title="Clear Filters">Clear</a>
        <?php endif; ?>
    </form>

    <!-- Action Button -->
    <a href="admin_add_member" class="btn-primary">
        <i class="fas fa-plus"></i> Add Member
    </a>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Member Info</th>
                    <th>ID Number</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Active Borrows</th>
                    <th>Date Joined</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($paginated_members)): ?>
                    <?php foreach ($paginated_members as $member): 
                        // Status Badge styling
                        $badge_class = 'badge-inactive';
                        if ($member['status'] === 'Active') $badge_class = 'badge-active';
                        if ($member['status'] === 'Suspended') $badge_class = 'badge-suspended';
                        
                        // Extract Initials
                        $words = explode(" ", $member['name']);
                        $initials = "";
                        foreach ($words as $w) { $initials .= $w[0]; }
                        $initials = strtoupper(substr($initials, 0, 2));
                    ?>
                    <tr>
                        <td>
                            <div class="member-identity">
                                <div class="member-avatar"><?= $initials ?></div>
                                <div class="member-details">
                                    <span class="member-name"><?= htmlspecialchars($member['name']) ?></span>
                                    <span class="member-email"><?= htmlspecialchars($member['email']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td style="font-family: monospace; font-size: 0.85rem; font-weight: 600;">
                            <?= htmlspecialchars($member['id']) ?>
                        </td>
                        <td>
                            <span class="type-badge"><?= htmlspecialchars($member['type']) ?></span>
                        </td>
                        <td>
                            <span class="badge <?= $badge_class ?>"><?= $member['status'] ?></span>
                        </td>
                        <td>
                            <?php if($member['borrows'] > 0): ?>
                                <span style="color: var(--accent); font-weight: 700;"><?= $member['borrows'] ?> Items</span>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">None</span>
                            <?php endif; ?>
                        </td>
                        <td style="color: var(--text-muted); font-size: 0.85rem;">
                            <?= date('M d, Y', strtotime($member['joined'])) ?>
                        </td>
                        <td>
                            <div class="action-cell" style="justify-content: flex-end;">
                                <a href="#" class="btn-icon" title="View Profile"><i class="fas fa-eye"></i></a>
                                <a href="#" class="btn-icon" title="Edit Member"><i class="fas fa-edit"></i></a>
                                <?php if($member['status'] === 'Active'): ?>
                                    <a href="#" class="btn-icon danger" title="Suspend Account"><i class="fas fa-ban"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">
                            <div class="no-results">
                                <i class="fas fa-users-slash"></i>
                                <h3>No members found</h3>
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
            Showing <span><?= $showing_start ?></span> to <span><?= $showing_end ?></span> of <span><?= $total_items ?></span> members
        </div>
        
        <?php if ($total_pages > 1): ?>
        <div class="pagination-controls">
            <?php if ($current_page > 1): ?>
                <a href="<?= buildPaginationUrl($current_page - 1, $search_query, $filter_type, $filter_status) ?>" class="page-link"><i class="fas fa-chevron-left"></i></a>
            <?php else: ?>
                <span class="page-link disabled"><i class="fas fa-chevron-left"></i></span>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="<?= buildPaginationUrl($i, $search_query, $filter_type, $filter_status) ?>" class="page-link <?= ($i === $current_page) ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            
            <?php if ($current_page < $total_pages): ?>
                <a href="<?= buildPaginationUrl($current_page + 1, $search_query, $filter_type, $filter_status) ?>" class="page-link"><i class="fas fa-chevron-right"></i></a>
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

            // Toggle Dropdown menu
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
                    
                    // Update input
                    input.value = value;
                    label.innerText = item.innerText;

                    // Close menu
                    wrapper.classList.remove('active');

                    // Automatically submit the form to apply the filter
                    filterForm.submit();
                });
            });
        });

        // Close dropdowns if clicked outside
        document.addEventListener('click', () => {
            document.querySelectorAll('.filter-dropdown').forEach(w => w.classList.remove('active'));
        });

        // Delete/Suspend confirmation logic
        document.querySelectorAll('.btn-icon.danger').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if(confirm('Are you sure you want to suspend this member account?')) {
                    alert('Account suspended.');
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