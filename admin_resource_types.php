<?php
session_start();

$page_title = 'Resource Types';

// --- Capture Search ---
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

// --- Mock Database of Resource Types ---
$all_types = [
    ['id' => 1, 'name' => 'Book', 'icon' => 'fa-book', 'color' => '#1a3673', 'bg' => '#e0e6ff', 'description' => 'Standard printed books, textbooks, and reference materials.', 'count' => 1245, 'status' => 'Active'],
    ['id' => 2, 'name' => 'Equipment', 'icon' => 'fa-laptop', 'color' => '#e65100', 'bg' => '#fff3e0', 'description' => 'Laptops, Chromebooks, calculators, and hardware.', 'count' => 85, 'status' => 'Active'],
    ['id' => 3, 'name' => 'Media', 'icon' => 'fa-compact-disc', 'color' => '#6a1b9a', 'bg' => '#f3e5f5', 'description' => 'DVDs, CDs, and other digital or optical media.', 'count' => 120, 'status' => 'Active'],
    ['id' => 4, 'name' => 'Journal / Periodical', 'icon' => 'fa-newspaper', 'color' => '#047857', 'bg' => '#d1fae5', 'description' => 'Academic journals, magazines, and newspapers.', 'count' => 430, 'status' => 'Active'],
    ['id' => 5, 'name' => 'Thesis / Dissertation', 'icon' => 'fa-graduation-cap', 'color' => '#b91c1c', 'bg' => '#fee2e2', 'description' => 'Student research papers, masteral thesis, and dissertations.', 'count' => 312, 'status' => 'Active'],
    ['id' => 6, 'name' => 'Map / Atlas', 'icon' => 'fa-map-marked-alt', 'color' => '#b45309', 'bg' => '#fef3c7', 'description' => 'Geographical maps, globes, and architectural blueprints.', 'count' => 0, 'status' => 'Inactive'],
];

// --- Filtering Logic ---
$filtered_types = array_filter($all_types, function($type) use ($search_query) {
    if (empty($search_query)) return true;
    $q = strtolower($search_query);
    return (
        strpos(strtolower($type['name']), $q) !== false ||
        strpos(strtolower($type['description']), $q) !== false
    );
});

// --- Pagination Logic ---
$total_items = count($filtered_types);
$items_per_page = 5; 
$total_pages = ceil($total_items / $items_per_page);

$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;

$offset = ($current_page - 1) * $items_per_page;
$paginated_types = array_slice($filtered_types, $offset, $items_per_page);

$showing_start = $total_items > 0 ? $offset + 1 : 0;
$showing_end = min($offset + $items_per_page, $total_items);

// ==========================================
// 1. PUSH STYLES
// ==========================================
ob_start();
?>
<style>
    /* Toolbar */
    .page-toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
    .search-form { display: flex; align-items: center; gap: 0.75rem; flex: 1; }
    .search-input-wrapper { position: relative; width: 100%; max-width: 350px; }
    .search-input-wrapper i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
    .search-input { width: 100%; padding: 0.6rem 1rem 0.6rem 2.5rem; border: 1px solid var(--border); border-radius: 8px; font-family: inherit; font-size: 0.9rem; color: var(--text-main); outline: none; transition: border-color 0.2s; }
    .search-input:focus { border-color: var(--accent); }
    
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

    /* Type Info Column */
    .type-identity { display: flex; align-items: center; gap: 12px; }
    .type-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
    .type-details { display: flex; flex-direction: column; }
    .type-title { font-weight: 700; color: var(--primary); font-size: 0.95rem; margin-bottom: 2px; }
    .type-desc { font-size: 0.8rem; color: var(--text-muted); max-width: 350px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* Badges */
    .badge { padding: 0.25rem 0.6rem; border-radius: 50px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; }
    .badge-active { background: var(--success-bg); color: var(--success); }
    .badge-inactive { background: var(--background); color: var(--text-muted); border: 1px solid var(--border); }
    
    .count-badge { font-size: 0.8rem; font-weight: 600; color: var(--accent); background: rgba(91, 125, 232, 0.1); padding: 4px 10px; border-radius: 6px; }

    /* Actions */
    .action-cell { display: flex; gap: 8px; justify-content: flex-end; }
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

    /* Modal Styles */
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 1000; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.2s ease; padding: 20px; }
    .modal-overlay.active { display: flex; opacity: 1; }
    .modal-content { background: var(--surface); border-radius: var(--radius); width: 100%; max-width: 480px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); transform: translateY(20px) scale(0.95); transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); overflow: hidden; }
    .modal-overlay.active .modal-content { transform: translateY(0) scale(1); }
    
    .modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: #fafbfc; }
    .modal-title { font-size: 1.1rem; font-weight: 700; color: var(--primary); }
    .modal-close { background: transparent; border: none; font-size: 1.2rem; color: var(--text-muted); cursor: pointer; transition: color 0.2s; }
    .modal-close:hover { color: var(--danger); }
    
    .modal-body { padding: 1.5rem; }
    .input-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 1rem; }
    .input-label { font-size: 0.85rem; font-weight: 600; color: var(--text-main); }
    .form-control { width: 100%; padding: 0.65rem 1rem; border: 1px solid var(--border); border-radius: 8px; font-family: inherit; font-size: 0.95rem; color: var(--text-main); transition: border-color 0.2s; outline: none; }
    .form-control:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(91, 125, 232, 0.15); }
    textarea.form-control { resize: vertical; min-height: 80px; }
    
    .modal-footer { padding: 1.25rem 1.5rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 0.75rem; background: #fafbfc; }
    .btn-outline { background: white; color: var(--text-main); border: 1px solid var(--border); padding: 0.6rem 1.25rem; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
    .btn-outline:hover { background: var(--background); }

    @media (max-width: 768px) {
        .page-toolbar { flex-direction: column; align-items: stretch; }
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
    <!-- Search Form -->
    <form class="search-form" method="GET" action="">
        <div class="search-input-wrapper">
            <i class="fas fa-search"></i>
            <input type="text" name="q" class="search-input" placeholder="Search resource types..." value="<?= htmlspecialchars($search_query) ?>" onchange="this.form.submit()">
        </div>
        <?php if(!empty($search_query)): ?>
            <a href="admin_resource_types" class="btn-icon" style="width: auto; padding: 0 1rem; border-color: transparent;" title="Clear Search">Clear</a>
        <?php endif; ?>
    </form>

    <!-- Action Button triggers Modal -->
    <button class="btn-primary" onclick="openModal('add')">
        <i class="fas fa-plus"></i> Add Resource Type
    </button>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Type Details</th>
                    <th>Linked Items</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($paginated_types)): ?>
                    <?php foreach ($paginated_types as $type): 
                        $badge_class = $type['status'] === 'Active' ? 'badge-active' : 'badge-inactive';
                    ?>
                    <tr>
                        <td>
                            <div class="type-identity">
                                <div class="type-icon" style="background-color: <?= $type['bg'] ?>; color: <?= $type['color'] ?>;">
                                    <i class="fas <?= $type['icon'] ?>"></i>
                                </div>
                                <div class="type-details">
                                    <span class="type-title"><?= htmlspecialchars($type['name']) ?></span>
                                    <span class="type-desc"><?= htmlspecialchars($type['description']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="count-badge"><?= $type['count'] ?> items</span>
                        </td>
                        <td>
                            <span class="badge <?= $badge_class ?>"><?= $type['status'] ?></span>
                        </td>
                        <td>
                            <div class="action-cell">
                                <button class="btn-icon" title="Edit Resource Type" 
                                    onclick="openModal('edit', <?= $type['id'] ?>, '<?= htmlspecialchars(addslashes($type['name'])) ?>', '<?= htmlspecialchars(addslashes($type['icon'])) ?>', '<?= htmlspecialchars(addslashes($type['description'])) ?>', '<?= $type['status'] ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if($type['count'] == 0): ?>
                                    <button class="btn-icon danger" title="Delete Type" onclick="confirmDelete(<?= $type['id'] ?>)">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                <?php else: ?>
                                    <!-- Disabled delete if type has items -->
                                    <button class="btn-icon" style="opacity: 0.3; cursor: not-allowed;" title="Cannot delete type with active items">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">
                            <div class="no-results">
                                <i class="fas fa-layer-group"></i>
                                <h3>No resource types found</h3>
                                <p>Try adjusting your search criteria or add a new type.</p>
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
            Showing <span><?= $showing_start ?></span> to <span><?= $showing_end ?></span> of <span><?= $total_items ?></span> types
        </div>
        
        <?php if ($total_pages > 1): ?>
        <div class="pagination-controls">
            <?php if ($current_page > 1): ?>
                <a href="?q=<?= urlencode($search_query) ?>&page=<?= $current_page - 1 ?>" class="page-link"><i class="fas fa-chevron-left"></i></a>
            <?php else: ?>
                <span class="page-link disabled"><i class="fas fa-chevron-left"></i></span>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?q=<?= urlencode($search_query) ?>&page=<?= $i ?>" class="page-link <?= ($i === $current_page) ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            
            <?php if ($current_page < $total_pages): ?>
                <a href="?q=<?= urlencode($search_query) ?>&page=<?= $current_page + 1 ?>" class="page-link"><i class="fas fa-chevron-right"></i></a>
            <?php else: ?>
                <span class="page-link disabled"><i class="fas fa-chevron-right"></i></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- ADD/EDIT MODAL -->
<div class="modal-overlay" id="typeModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Add Resource Type</h3>
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <form action="" method="POST">
            <div class="modal-body">
                <input type="hidden" name="type_id" id="typeId">
                
                <div class="input-group">
                    <label class="input-label">Type Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name" id="typeName" class="form-control" placeholder="e.g., Book, Equipment" required>
                </div>

                <div class="input-group">
                    <label class="input-label">Icon Class (FontAwesome)</label>
                    <input type="text" name="icon" id="typeIcon" class="form-control" placeholder="e.g., fa-book, fa-laptop">
                </div>
                
                <div class="input-group">
                    <label class="input-label">Description</label>
                    <textarea name="description" id="typeDesc" class="form-control" placeholder="Brief description of the resource type..."></textarea>
                </div>

                <div class="input-group">
                    <label class="input-label">Status <span style="color:var(--danger)">*</span></label>
                    <!-- Using native select for simplicity inside the modal as per original categories design -->
                    <select name="status" id="typeStatus" class="form-control" style="cursor: pointer; appearance: auto;" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-primary" id="modalSubmitBtn"><i class="fas fa-save"></i> Save Type</button>
            </div>
        </form>
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
    const modal = document.getElementById('typeModal');
    
    function openModal(mode, id = '', name = '', icon = '', desc = '', status = 'Active') {
        const title = document.getElementById('modalTitle');
        const submitBtn = document.getElementById('modalSubmitBtn');
        
        document.getElementById('typeId').value = id;
        document.getElementById('typeName').value = name;
        document.getElementById('typeIcon').value = icon;
        document.getElementById('typeDesc').value = desc;
        document.getElementById('typeStatus').value = status;

        if(mode === 'edit') {
            title.innerText = 'Edit Resource Type';
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Type';
        } else {
            title.innerText = 'Add Resource Type';
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Type';
        }

        modal.classList.add('active');
    }

    function closeModal() {
        modal.classList.remove('active');
    }

    // Close modal if clicked outside
    modal.addEventListener('click', function(e) {
        if(e.target === modal) {
            closeModal();
        }
    });

    function confirmDelete(id) {
        if(confirm("Are you sure you want to delete this resource type? This action cannot be undone.")) {
            // Add AJAX or redirection logic here to delete
            alert("Delete triggered for ID: " + id);
        }
    }
</script>
<?php
$page_scripts = ob_get_clean();

// ==========================================
// 4. REQUIRE LAYOUT
// ==========================================
require 'admin_layout.php';
?>