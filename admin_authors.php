<?php
session_start();

$page_title = 'Authors Management';

// --- Capture Search ---
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

// --- Mock Database of Authors ---
$all_authors = [
    ['id' => 1, 'name' => 'J.K. Rowling', 'biography' => 'British author, best known for the Harry Potter series.', 'count' => 12],
    ['id' => 2, 'name' => 'George R.R. Martin', 'biography' => 'American novelist, known for A Song of Ice and Fire.', 'count' => 5],
    ['id' => 3, 'name' => 'Isaac Asimov', 'biography' => 'American writer and professor of biochemistry, known for hard science fiction.', 'count' => 18],
    ['id' => 4, 'name' => 'Harper Lee', 'biography' => 'American novelist best known for To Kill a Mockingbird.', 'count' => 2],
    ['id' => 5, 'name' => 'Dr. Jose Rizal', 'biography' => 'Filipino nationalist, writer and polymath.', 'count' => 8],
    ['id' => 6, 'name' => 'Stephen King', 'biography' => 'American author of horror, suspense, and fantasy novels.', 'count' => 0],
];

// --- Filtering Logic ---
$filtered_authors = array_filter($all_authors, function($author) use ($search_query) {
    if (empty($search_query)) return true;
    $q = strtolower($search_query);
    return (
        strpos(strtolower($author['name']), $q) !== false ||
        strpos(strtolower($author['biography']), $q) !== false
    );
});

// --- Pagination Logic ---
$total_items = count($filtered_authors);
$items_per_page = 5; 
$total_pages = ceil($total_items / $items_per_page);

$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;

$offset = ($current_page - 1) * $items_per_page;
$paginated_authors = array_slice($filtered_authors, $offset, $items_per_page);

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

    /* Author Info Column */
    .author-identity { display: flex; align-items: center; gap: 12px; }
    .author-avatar { width: 40px; height: 40px; border-radius: 50%; background: #e0e6ff; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 0.9rem; font-weight: 700; flex-shrink: 0; }
    .author-details { display: flex; flex-direction: column; }
    .author-title { font-weight: 700; color: var(--primary); font-size: 0.95rem; margin-bottom: 2px; }
    .author-desc { font-size: 0.8rem; color: var(--text-muted); max-width: 400px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .count-badge { font-size: 0.8rem; font-weight: 600; color: var(--accent); background: rgba(91, 125, 232, 0.1); padding: 4px 10px; border-radius: 6px; }

    /* Actions */
    .action-cell { display: flex; gap: 8px; justify-content: flex-end; }
    .btn-icon { width: 32px; height: 32px; border-radius: 6px; border: 1px solid var(--border); background: white; color: var(--text-muted); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; text-decoration: none; border: none; }
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
            <input type="text" name="q" class="search-input" placeholder="Search authors..." value="<?= htmlspecialchars($search_query) ?>" onchange="this.form.submit()">
        </div>
        <?php if(!empty($search_query)): ?>
            <a href="admin_authors" class="btn-icon" style="width: auto; padding: 0 1rem; border-color: transparent;" title="Clear Search">Clear</a>
        <?php endif; ?>
    </form>

    <!-- Action Button triggers Modal -->
    <button class="btn-primary" onclick="openModal('add')">
        <i class="fas fa-plus"></i> Add Author
    </button>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Author Profile</th>
                    <th>Linked Books</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($paginated_authors)): ?>
                    <?php foreach ($paginated_authors as $author): 
                        // Automatically generate Initials for the avatar
                        $words = explode(" ", $author['name']);
                        $initials = "";
                        foreach ($words as $w) { 
                            if(isset($w[0]) && preg_match('/[a-zA-Z]/', $w[0])) {
                                $initials .= $w[0]; 
                            }
                        }
                        $initials = strtoupper(substr($initials, 0, 2));
                    ?>
                    <tr>
                        <td>
                            <div class="author-identity">
                                <div class="author-avatar"><?= $initials ?></div>
                                <div class="author-details">
                                    <span class="author-title"><?= htmlspecialchars($author['name']) ?></span>
                                    <span class="author-desc"><?= htmlspecialchars($author['biography'] ?: 'No biography provided.') ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="count-badge"><?= $author['count'] ?> books</span>
                        </td>
                        <td>
                            <div class="action-cell">
                                <button type="button" class="btn-icon" title="Edit Author" 
                                    onclick="openModal('edit', <?= $author['id'] ?>, '<?= htmlspecialchars(addslashes($author['name'])) ?>', '<?= htmlspecialchars(addslashes($author['biography'])) ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if($author['count'] == 0): ?>
                                    <button type="button" class="btn-icon danger" title="Delete Author" onclick="confirmDelete(<?= $author['id'] ?>)">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn-icon" style="opacity: 0.3; cursor: not-allowed;" title="Cannot delete author with active books" disabled>
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">
                            <div class="no-results">
                                <i class="fas fa-user-edit"></i>
                                <h3>No authors found</h3>
                                <p>Try adjusting your search criteria or add a new author.</p>
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
            Showing <span><?= $showing_start ?></span> to <span><?= $showing_end ?></span> of <span><?= $total_items ?></span> authors
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
<div class="modal-overlay" id="authorModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Add New Author</h3>
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <form action="" method="POST">
            <div class="modal-body">
                <input type="hidden" name="author_id" id="authorId">
                
                <div class="input-group">
                    <label class="input-label">Author Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name" id="authorName" class="form-control" placeholder="e.g., J.K. Rowling" required>
                </div>
                
                <div class="input-group">
                    <label class="input-label">Biography</label>
                    <textarea name="biography" id="authorBio" class="form-control" placeholder="Brief background of the author..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-primary" id="modalSubmitBtn"><i class="fas fa-save"></i> Save Author</button>
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
    const modal = document.getElementById('authorModal');
    
    function openModal(mode, id = '', name = '', bio = '') {
        const title = document.getElementById('modalTitle');
        const submitBtn = document.getElementById('modalSubmitBtn');
        
        // Populate fields
        document.getElementById('authorId').value = id;
        document.getElementById('authorName').value = name;
        document.getElementById('authorBio').value = bio;

        // Toggle UI states
        if(mode === 'edit') {
            title.innerText = 'Edit Author';
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Author';
        } else {
            title.innerText = 'Add New Author';
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Author';
        }

        modal.classList.add('active');
    }

    function closeModal() {
        modal.classList.remove('active');
    }

    // Close modal if user clicks outside of it
    modal.addEventListener('click', function(e) {
        if(e.target === modal) {
            closeModal();
        }
    });

    function confirmDelete(id) {
        if(confirm("Are you sure you want to delete this author? This action cannot be undone.")) {
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