<?php
session_start();

$page_title = 'Catalog - NISU Library';

// 1. Capture search queries if present
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$search_type = isset($_GET['type']) ? trim($_GET['type']) : '';

// 2. Static Mock Data
$all_items = [
    [
        'id' => 1, 'title' => 'Advanced Physics for Engineers', 'creator' => 'Dr. John Smith',
        'type_name' => 'Book', 'category_name' => 'Science & Engineering', 'barcode' => 'LIB-2026-001',
        'call_no' => 'QC311 .S65 2022', 'book_no' => 'B-00142', 'isbn' => '978-0123456789',
        'edition' => '3rd Edition', 'copyright' => '2022', 'total_copies' => 5, 'available_copies' => 5,
        'description' => 'A comprehensive guide to advanced physics concepts tailored specifically for engineering students.'
    ],
    [
        'id' => 2, 'title' => 'Chromebook Model X', 'creator' => 'Google',
        'type_name' => 'Equipment', 'category_name' => 'Computing Devices', 'barcode' => 'EQ-LAP-042',
        'call_no' => 'EQ-LAP-042', 'book_no' => 'N/A', 'isbn' => '', 
        'edition' => 'Model X (2025)', 'copyright' => 'N/A', 'total_copies' => 15, 'available_copies' => 12,
        'description' => 'Standard issue Chromebook for student research and assignments.'
    ],
    [
        'id' => 3, 'title' => 'To Kill a Mockingbird', 'creator' => 'Harper Lee',
        'type_name' => 'Book', 'category_name' => 'Classic Literature', 'barcode' => 'LIB-1960-004',
        'call_no' => 'PS3562.E353 T6', 'book_no' => 'B-00891', 'isbn' => '978-0060935467',
        'edition' => '50th Anniversary', 'copyright' => '1960', 'total_copies' => 3, 'available_copies' => 0,
        'description' => 'The unforgettable novel of a childhood in a sleepy Southern town.'
    ],
    [
        'id' => 4, 'title' => 'Planet Earth II Collection', 'creator' => 'BBC Video',
        'type_name' => 'Media', 'category_name' => 'Documentary / Science', 'barcode' => 'MED-DVD-018',
        'call_no' => 'DVD-SCI-018', 'book_no' => 'N/A', 'isbn' => '', 
        'edition' => 'Standard DVD', 'copyright' => '2017', 'total_copies' => 2, 'available_copies' => 1,
        'description' => 'Experience the world from the perspective of animals themselves.'
    ],
    [
        'id' => 5, 'title' => 'TI-84 Plus CE Graphing Calculator', 'creator' => 'Texas Instruments',
        'type_name' => 'Equipment', 'category_name' => 'Mathematics', 'barcode' => 'EQ-CALC-109',
        'call_no' => 'EQ-CALC-109', 'book_no' => 'N/A', 'isbn' => '',
        'edition' => 'Plus CE', 'copyright' => 'N/A', 'total_copies' => 10, 'available_copies' => 8,
        'description' => 'High-resolution full-color backlit display graphing calculator.'
    ],
    [
        'id' => 6, 'title' => 'Introduction to Algorithms', 'creator' => 'Cormen, Leiserson, Rivest, Stein',
        'type_name' => 'Book', 'category_name' => 'Computer Science', 'barcode' => 'LIB-2026-105',
        'call_no' => 'QA76.6 .C662', 'book_no' => 'B-05512', 'isbn' => '978-0262033848',
        'edition' => '3rd Edition', 'copyright' => '2009', 'total_copies' => 4, 'available_copies' => 3,
        'description' => 'An essential reference for computer programming and algorithm analysis.'
    ]
];

// 3. Search Logic
$catalog_items = array_filter($all_items, function($item) use ($search_query, $search_type) {
    $type_match = empty($search_type) || strtolower($item['type_name']) === strtolower($search_type);
    
    $text_match = true;
    if (!empty($search_query)) {
        $q = strtolower($search_query);
        $text_match = (
            strpos(strtolower($item['title']), $q) !== false ||
            strpos(strtolower($item['creator']), $q) !== false ||
            strpos(strtolower($item['barcode']), $q) !== false ||
            strpos(strtolower($item['category_name']), $q) !== false ||
            strpos(strtolower($item['type_name']), $q) !== false
        );
    }
    return $type_match && $text_match;
});

// 4. Pagination Logic
$total_items = count($catalog_items);
$items_per_page = 3; 
$total_pages = ceil($total_items / $items_per_page);

$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;

$offset = ($current_page - 1) * $items_per_page;
$paginated_items = array_slice($catalog_items, $offset, $items_per_page);

$showing_start = $total_items > 0 ? $offset + 1 : 0;
$showing_end = min($offset + $items_per_page, $total_items);

function buildPaginationUrl($page, $search_query, $search_type) {
    return "?q=" . urlencode($search_query) . "&type=" . urlencode($search_type) . "&page=" . $page;
}

$type_label = "All Resources";
if ($search_type === 'book') $type_label = "Books";
elseif ($search_type === 'equipment') $type_label = "Equipment";
elseif ($search_type === 'media') $type_label = "Media";

function getItemVisuals($typeName) {
    $type = strtolower($typeName ?? '');
    if (stristr($type, 'laptop') || stristr($type, 'equipment') || stristr($type, 'calculator')) {
        return ['icon' => 'fa-laptop', 'bg' => '#fff3e0', 'color' => '#f57c00', 'badgeClass' => 'badge-equipment'];
    } elseif (stristr($type, 'dvd') || stristr($type, 'media') || stristr($type, 'cd')) {
        return ['icon' => 'fa-compact-disc', 'bg' => '#f3e5f5', 'color' => '#8e24aa', 'badgeClass' => 'badge-media'];
    } else {
        return ['icon' => 'fa-book', 'bg' => '#e0e6ff', 'color' => '#1a3673', 'badgeClass' => 'badge-book'];
    }
}

// ==========================================
// 1. PUSH STYLES
// ==========================================
ob_start();
?>
<style>
    /* Search Section */
    .search-hero { background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); padding: 5rem 20px; text-align: center; display: flex; flex-direction: column; align-items: center; color: white; }
    .search-hero h1 { font-size: 2.5rem; font-weight: 800; margin-bottom: 0.75rem; letter-spacing: -0.025em; }
    .search-hero p { font-size: 1.1rem; opacity: 0.9; margin-bottom: 2.5rem; max-width: 600px; }
    .search-container { width: 100%; max-width: 780px; display: flex; align-items: center; background: var(--surface); border-radius: 50px; padding: 6px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); border: 2px solid transparent; transition: all 0.3s ease; }
    .search-container:focus-within { border-color: rgba(255,255,255,0.4); box-shadow: 0 10px 40px rgba(0,0,0,0.3); }

    .search-dropdown-wrapper { position: relative; display: flex; align-items: center; }
    .search-dropdown-toggle { background: transparent; border: none; padding: 0.75rem 2.5rem 0.75rem 1.25rem; font-family: inherit; font-size: 0.95rem; font-weight: 700; color: var(--primary); cursor: pointer; outline: none; display: flex; align-items: center; white-space: nowrap; }
    .search-dropdown-arrow { position: absolute; right: 14px; font-size: 0.75rem; color: var(--primary); transition: transform 0.2s ease; }
    .search-dropdown-wrapper.active .search-dropdown-arrow { transform: rotate(180deg); }
    .search-dropdown-wrapper.active .search-dropdown-toggle { color: var(--accent); }
    
    .search-dropdown-menu { position: absolute; top: calc(100% + 15px); left: 0; width: 220px; background: white; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); opacity: 0; visibility: hidden; transform: translateY(-10px); transition: all 0.2s ease; z-index: 50; padding: 0.5rem; display: flex; flex-direction: column; gap: 2px; }
    .search-dropdown-wrapper.active .search-dropdown-menu { opacity: 1; visibility: visible; transform: translateY(0); }
    .search-dropdown-item { display: flex; align-items: center; gap: 10px; padding: 0.6rem 0.75rem; border-radius: 6px; font-size: 0.875rem; font-weight: 600; color: var(--text-main); cursor: pointer; transition: background 0.2s, color 0.2s; }
    .search-dropdown-item i { font-size: 0.9rem; color: var(--text-muted); width: 16px; text-align: center; transition: color 0.2s; }
    .search-dropdown-item:hover { background: var(--background); color: var(--primary); }
    .search-dropdown-item:hover i { color: var(--primary); }
    .search-dropdown-item.active { background: rgba(91, 125, 232, 0.1); color: var(--primary); }
    .search-dropdown-item.active i { color: var(--primary); }
    
    .search-divider { width: 1px; height: 28px; background: var(--border); margin: 0 4px; }
    .search-container input { flex: 1; padding: 0.75rem 1rem; border: none; background: transparent; font-size: 1rem; color: var(--text-main); outline: none; font-family: inherit; }
    .search-container input::placeholder { color: #94a3b8; }
    .search-btn { background: var(--primary); color: white; border: none; border-radius: 50px; padding: 0 1.75rem; height: 46px; font-size: 0.95rem; font-weight: 700; cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 10px rgba(26, 54, 115, 0.2); }
    .search-btn:hover { background: var(--primary-hover); transform: translateY(-1px); box-shadow: 0 6px 15px rgba(26, 54, 115, 0.3); }

    /* Catalog Layout */
    .catalog-container { flex: 1; padding: 3rem 5%; max-width: 1200px; margin: 0 auto; width: 100%; }
    .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 2px solid var(--border); padding-bottom: 1rem; }
    .section-title-wrapper { display: flex; align-items: center; gap: 1rem; }
    .section-title { font-size: 1.5rem; font-weight: 700; color: var(--primary); }
    .search-badge { background: var(--accent); color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
    .clear-search { color: var(--accent); text-decoration: none; font-size: 0.875rem; font-weight: 600; }
    .clear-search:hover { text-decoration: underline; }

    /* List Items */
    .list-view { display: flex; flex-direction: column; gap: 1rem; }
    .list-item { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.25rem; display: flex; align-items: center; gap: 1.5rem; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .list-item:hover { border-color: var(--accent); box-shadow: 0 10px 15px -3px rgba(26, 54, 115, 0.08); transform: translateY(-2px); }
    .item-icon-wrapper { width: 64px; height: 64px; border-radius: 12px; display: flex; justify-content: center; align-items: center; font-size: 1.5rem; flex-shrink: 0; }
    .item-content { flex: 1; display: flex; flex-direction: column; gap: 4px; min-width: 0; }
    .item-header-row { display: flex; align-items: center; gap: 10px; }
    .item-title { font-size: 1.125rem; font-weight: 700; color: var(--primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .item-creator { font-size: 0.875rem; color: var(--text-muted); }
    .item-desc { font-size: 0.875rem; color: var(--text-muted); display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; margin-top: 4px; }
    .item-actions { display: flex; flex-direction: column; align-items: flex-end; gap: 10px; flex-shrink: 0; min-width: 140px; }
    .badge { padding: 0.25rem 0.625rem; border-radius: 50px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; white-space: nowrap; }
    .badge-book { background: #e0e6ff; color: #1a3673; }
    .badge-equipment { background: #fff3e0; color: #e65100; }
    .badge-media { background: #f3e5f5; color: #6a1b9a; }
    .status { font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 6px; }
    .status.available { color: #16a34a; }
    .status.unavailable { color: #dc2626; }
    .btn-view { background: var(--primary); color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.875rem; font-weight: 600; pointer-events: none; }
    .list-item:hover .btn-view { background: var(--accent); }
    .no-results { text-align: center; padding: 4rem 1rem; background: var(--surface); border: 1px dashed var(--border); border-radius: var(--radius); color: var(--text-muted); }

    /* Pagination */
    .pagination-container { display: flex; justify-content: space-between; align-items: center; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border); }
    .pagination-info { font-size: 0.875rem; color: var(--text-muted); }
    .pagination-info span { font-weight: 700; color: var(--text-main); }
    .pagination-controls { display: flex; gap: 0.375rem; }
    .page-link { display: inline-flex; align-items: center; justify-content: center; height: 36px; min-width: 36px; padding: 0 0.75rem; font-size: 0.875rem; font-weight: 600; color: var(--text-main); background: var(--surface); border: 1px solid var(--border); border-radius: 6px; text-decoration: none; transition: all 0.2s; gap: 6px; }
    .page-link:hover:not(.disabled):not(.active) { background: var(--background); border-color: var(--text-muted); }
    .page-link.active { background: var(--primary); color: white; border-color: var(--primary); }
    .page-link.disabled { opacity: 0.5; cursor: not-allowed; background: var(--background); color: var(--text-muted); pointer-events: none; }

    /* Professional Modal */
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(5px); z-index: 1000; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.25s ease-in-out; padding: 20px; }
    .modal-overlay.active { display: flex; opacity: 1; }
    .modal-content { background: var(--surface); border-radius: var(--radius); width: 100%; max-width: 720px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4); transform: translateY(20px) scale(0.98); transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); overflow: hidden; }
    .modal-overlay.active .modal-content { transform: translateY(0) scale(1); }
    .modal-header { padding: 2rem 2.5rem 1.5rem; border-bottom: 1px solid var(--border); position: relative; background: #ffffff; }
    .modal-close { position: absolute; top: 1.5rem; right: 1.5rem; background: var(--background); border: 1px solid var(--border); color: var(--text-muted); cursor: pointer; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; transition: all 0.2s; font-size: 1.1rem; }
    .modal-close:hover { border-color: var(--text-main); color: var(--text-main); background: white; transform: rotate(90deg); }
    .modal-header-top { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
    .modal-title { font-size: 1.75rem; font-weight: 800; color: var(--primary); margin-bottom: 6px; letter-spacing: -0.025em; line-height: 1.2; padding-right: 40px; }
    .modal-creator { font-size: 0.95rem; color: var(--text-muted); font-weight: 500; display: flex; align-items: center; gap: 8px; }
    .modal-body { padding: 1.5rem 2.5rem; overflow-y: auto; background: var(--surface); }
    .section-heading { font-size: 0.8rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem; border-bottom: 2px solid var(--border); padding-bottom: 0.5rem; display: flex; align-items: center; gap: 8px; }
    .modal-meta-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; background: var(--background); padding: 1.5rem; border-radius: 10px; border: 1px solid var(--border); border-left: 4px solid var(--accent); margin-bottom: 2rem; }
    .meta-group { display: flex; flex-direction: column; gap: 6px; }
    .meta-label { font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px; }
    .meta-value { font-size: 0.95rem; font-weight: 700; color: var(--text-main); }
    .modal-desc { font-size: 1rem; color: var(--text-muted); line-height: 1.7; }
    .modal-footer { padding: 1.5rem 2.5rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 1rem; background: var(--background); }
    .btn { padding: 0.75rem 1.5rem; border-radius: 8px; font-size: 0.95rem; font-weight: 600; text-decoration: none; cursor: pointer; border: none; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; }
    .btn-outline { background: white; border: 1px solid var(--border); color: var(--text-main); }
    .btn-primary { background: var(--primary); color: white; }
    .btn-primary.disabled { opacity: 0.5; pointer-events: none; }

    @media (max-width: 768px) {
        .search-container { flex-direction: column; border-radius: 16px; padding: 8px; gap: 8px; }
        .search-divider { display: none; }
        .search-dropdown-wrapper { width: 100%; border-bottom: 1px solid var(--border); }
        .search-dropdown-toggle { width: 100%; justify-content: space-between; padding: 0.5rem 1rem; }
        .search-dropdown-menu { width: 100%; top: calc(100% + 5px); }
        .search-container input { width: 100%; padding: 0.5rem 1rem; }
        .search-btn { width: 100%; justify-content: center; border-radius: 8px; }
        .list-item { flex-direction: column; align-items: flex-start; gap: 1rem; }
        .item-actions { width: 100%; flex-direction: row; justify-content: space-between; align-items: center; }
        .btn-view { display: none; }
        .pagination-container { flex-direction: column; gap: 1rem; align-items: center; }
        .modal-content { max-height: 95vh; }
        .modal-header, .modal-body, .modal-footer { padding: 1.25rem 1.5rem; }
        .modal-footer { flex-direction: column-reverse; }
        .modal-footer .btn { width: 100%; justify-content: center; }
        .modal-meta-grid { grid-template-columns: repeat(2, 1fr); padding: 1.25rem; gap: 1.25rem; }
    }
    @media (max-width: 480px) {
        .modal-meta-grid { grid-template-columns: 1fr; }
    }
</style>
<?php
$page_styles = ob_get_clean();

// ==========================================
// 2. YIELD CONTENT
// ==========================================
ob_start();
?>
<div class="search-hero">
    <h1>Discover Resources</h1>
    <p>Browse books, equipment, and media available at the NISU library.</p>
    
    <form class="search-container" action="" method="GET">
        <div class="search-dropdown-wrapper" id="searchCustomDropdown">
            <input type="hidden" name="type" id="searchTypeInput" value="<?= htmlspecialchars($search_type) ?>">
            <button type="button" class="search-dropdown-toggle">
                <span id="searchDropdownLabel"><?= $type_label ?></span>
                <i class="fas fa-chevron-down search-dropdown-arrow"></i>
            </button>
            <div class="search-dropdown-menu">
                <div class="search-dropdown-item <?= empty($search_type) ? 'active' : '' ?>" data-value="">
                    <i class="fas fa-layer-group"></i> All Resources
                </div>
                <div class="search-dropdown-item <?= $search_type === 'book' ? 'active' : '' ?>" data-value="book">
                    <i class="fas fa-book"></i> Books
                </div>
                <div class="search-dropdown-item <?= $search_type === 'equipment' ? 'active' : '' ?>" data-value="equipment">
                    <i class="fas fa-laptop"></i> Equipment
                </div>
                <div class="search-dropdown-item <?= $search_type === 'media' ? 'active' : '' ?>" data-value="media">
                    <i class="fas fa-compact-disc"></i> Media
                </div>
            </div>
        </div>
        <div class="search-divider"></div>
        <input type="text" name="q" placeholder="Search by title, author, or keyword..." value="<?= htmlspecialchars($search_query) ?>">
        <button type="submit" class="search-btn"><i class="fas fa-search"></i> Search</button>
    </form>
</div>

<div class="catalog-container">
    <div class="section-header">
        <div class="section-title-wrapper">
            <h2 class="section-title"><?= (!empty($search_query) || !empty($search_type)) ? 'Search Results' : 'Catalog Directory' ?></h2>
            <?php if ((!empty($search_query) || !empty($search_type)) && $total_items > 0): ?>
                <span class="search-badge"><?= $total_items ?> Found</span>
            <?php endif; ?>
        </div>
        <?php if (!empty($search_query) || !empty($search_type)): ?>
            <a href="?" class="clear-search">Clear filter &times;</a>
        <?php endif; ?>
    </div>
    
    <div class="list-view">
        <?php if (!empty($paginated_items)): ?>
            <?php foreach ($paginated_items as $item): 
                $visuals = getItemVisuals($item['type_name']);
                $isAvailable = ($item['available_copies'] > 0);
                $statusText = $isAvailable ? $item['available_copies'] . ' Available' : 'Out of Stock';
                $statusClass = $isAvailable ? 'available' : 'unavailable';
            ?>
                <div class="list-item" 
                    data-title="<?= htmlspecialchars($item['title']) ?>" 
                    data-creator="<?= htmlspecialchars($item['creator'] ?? 'Unknown') ?>" 
                    data-badge="<?= htmlspecialchars($item['type_name']) ?>" 
                    data-type="<?= htmlspecialchars($item['type_name']) ?>"
                    data-badgeclass="<?= $visuals['badgeClass'] ?>"
                    data-icon="<?= $visuals['icon'] ?>"
                    data-iconbg="<?= $visuals['bg'] ?>"
                    data-iconcolor="<?= $visuals['color'] ?>"
                    data-status="<?= $statusText ?>" 
                    data-statusclass="<?= $statusClass ?>"
                    data-barcode="<?= htmlspecialchars($item['barcode']) ?>"
                    data-category="<?= htmlspecialchars($item['category_name'] ?? 'General') ?>"
                    data-desc="<?= htmlspecialchars($item['description'] ?? 'No description') ?>"
                    data-callno="<?= htmlspecialchars($item['call_no'] ?? 'N/A') ?>"
                    data-bookno="<?= htmlspecialchars($item['book_no'] ?? 'N/A') ?>"
                    data-isbn="<?= htmlspecialchars($item['isbn'] ?? '') ?>"
                    data-edition="<?= htmlspecialchars($item['edition'] ?? 'N/A') ?>"
                    data-copyright="<?= htmlspecialchars($item['copyright'] ?? 'N/A') ?>"
                    data-copies="<?= htmlspecialchars($item['available_copies']) . ' / ' . htmlspecialchars($item['total_copies']) ?>"
                    >
                    <div class="item-icon-wrapper" style="background: <?= $visuals['bg'] ?>; color: <?= $visuals['color'] ?>;">
                        <i class="fas <?= $visuals['icon'] ?>"></i>
                    </div>
                    <div class="item-content">
                        <div class="item-header-row">
                            <span class="badge <?= $visuals['badgeClass'] ?>"><?= htmlspecialchars($item['type_name']) ?></span>
                            <span class="item-creator"><i class="fas fa-user-edit" style="margin-right: 4px; font-size: 0.8rem; opacity: 0.7;"></i><?= htmlspecialchars($item['creator'] ?? 'Unknown') ?></span>
                        </div>
                        <h3 class="item-title"><?= htmlspecialchars($item['title']) ?></h3>
                        <p class="item-desc"><?= htmlspecialchars($item['description'] ?? '') ?></p>
                    </div>
                    <div class="item-actions">
                        <span class="status <?= $statusClass ?>">
                            <i class="fas <?= $isAvailable ? 'fa-check-circle' : 'fa-times-circle' ?>"></i> <?= $statusText ?>
                        </span>
                        <span class="btn-view">Preview</span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-folder-open"></i>
                <h3>No items found</h3>
                <p>We couldn't find any items matching your search. Try adjusting your filters.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($total_items > 0): ?>
        <div class="pagination-container">
            <div class="pagination-info">Showing <span><?= $showing_start ?></span> to <span><?= $showing_end ?></span> of <span><?= $total_items ?></span> results</div>
            <?php if ($total_pages > 1): ?>
            <div class="pagination-controls">
                <?php if ($current_page > 1): ?>
                    <a href="<?= buildPaginationUrl($current_page - 1, $search_query, $search_type) ?>" class="page-link"><i class="fas fa-chevron-left"></i> Prev</a>
                <?php else: ?>
                    <span class="page-link disabled"><i class="fas fa-chevron-left"></i> Prev</span>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="<?= buildPaginationUrl($i, $search_query, $search_type) ?>" class="page-link <?= ($i === $current_page) ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                
                <?php if ($current_page < $total_pages): ?>
                    <a href="<?= buildPaginationUrl($current_page + 1, $search_query, $search_type) ?>" class="page-link">Next <i class="fas fa-chevron-right"></i></a>
                <?php else: ?>
                    <span class="page-link disabled">Next <i class="fas fa-chevron-right"></i></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal -->
<div id="itemModal" class="modal-overlay">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        <div class="modal-header">
            <div class="modal-header-top">
                <span id="modalBadge" class="badge">Category</span>
                <span id="modalHeaderStatus" class="status" style="font-size: 0.8rem;"><i class="fas fa-check-circle"></i> Available</span>
            </div>
            <h2 id="modalTitle" class="modal-title">Item Title</h2>
            <p class="modal-creator"><i class="fas fa-user-edit" style="color: var(--accent);"></i> <span id="modalCreator">Author / Creator</span></p>
        </div>
        
        <div class="modal-body">
            <h4 class="section-heading"><i class="fas fa-info-circle"></i> Resource Details</h4>
            <div class="modal-meta-grid">
                <div class="meta-group"><span class="meta-label"><i class="fas fa-barcode"></i> Barcode / ID</span><span class="meta-value" id="modalBarcode">Loading...</span></div>
                <div class="meta-group"><span class="meta-label"><i class="fas fa-bookmark"></i> Call No.</span><span class="meta-value" id="modalCallNo">Loading...</span></div>
                <div class="meta-group"><span class="meta-label"><i class="fas fa-hashtag"></i> Book No.</span><span class="meta-value" id="modalBookNo">Loading...</span></div>
                <div class="meta-group" id="modalIsbnWrapper"><span class="meta-label"><i class="fas fa-swatchbook"></i> ISBN</span><span class="meta-value" id="modalIsbn">Loading...</span></div>
                <div class="meta-group"><span class="meta-label"><i class="fas fa-layer-group"></i> Edition</span><span class="meta-value" id="modalEdition">Loading...</span></div>
                <div class="meta-group"><span class="meta-label"><i class="fas fa-copyright"></i> Copyright</span><span class="meta-value" id="modalCopyright">Loading...</span></div>
                <div class="meta-group"><span class="meta-label"><i class="fas fa-copy"></i> Copies (Avail/Total)</span><span class="meta-value" id="modalCopies">Loading...</span></div>
                <div class="meta-group" style="grid-column: span 2;"><span class="meta-label"><i class="fas fa-tags"></i> Subject / Genre</span><span class="meta-value" id="modalCategory">Loading...</span></div>
            </div>
            <h4 class="section-heading"><i class="fas fa-align-left"></i> About this Item</h4>
            <div class="modal-desc" id="modalDesc">Full item description goes here.</div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal()">Cancel</button>
            <a href="#" id="modalBorrowBtn" class="btn btn-primary">Proceed to Checkout <i class="fas fa-arrow-right"></i></a>
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
    // Search Dropdown Logic
    const searchDropdownWrapper = document.getElementById('searchCustomDropdown');
    if (searchDropdownWrapper) {
        const searchDropdownToggle = searchDropdownWrapper.querySelector('.search-dropdown-toggle');
        const searchTypeInput = document.getElementById('searchTypeInput');
        const searchDropdownLabel = document.getElementById('searchDropdownLabel');
        const searchDropdownItems = searchDropdownWrapper.querySelectorAll('.search-dropdown-item');

        searchDropdownToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            searchDropdownWrapper.classList.toggle('active');
        });

        searchDropdownItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.stopPropagation();
                searchTypeInput.value = item.getAttribute('data-value');
                searchDropdownLabel.innerText = item.innerText.trim();
                searchDropdownItems.forEach(i => i.classList.remove('active'));
                item.classList.add('active');
                searchDropdownWrapper.classList.remove('active');
            });
        });
    }

    document.addEventListener('click', (e) => {
        if (searchDropdownWrapper && !searchDropdownWrapper.contains(e.target)) {
            searchDropdownWrapper.classList.remove('active');
        }
    });

    // Modal Logic
    const modal = document.getElementById('itemModal');
    const body = document.body;
    
    document.querySelectorAll('.list-item').forEach(item => {
        item.addEventListener('click', function() {
            document.getElementById('modalTitle').innerText = this.getAttribute('data-title');
            document.getElementById('modalCreator').innerText = this.getAttribute('data-creator');
            document.getElementById('modalBarcode').innerText = this.getAttribute('data-barcode');
            document.getElementById('modalCategory').innerText = this.getAttribute('data-category');
            document.getElementById('modalDesc').innerText = this.getAttribute('data-desc');
            document.getElementById('modalCallNo').innerText = this.getAttribute('data-callno');
            document.getElementById('modalBookNo').innerText = this.getAttribute('data-bookno');
            document.getElementById('modalEdition').innerText = this.getAttribute('data-edition');
            document.getElementById('modalCopyright').innerText = this.getAttribute('data-copyright');
            document.getElementById('modalCopies').innerText = this.getAttribute('data-copies');
            
            const type = this.getAttribute('data-type');
            const isbnWrapper = document.getElementById('modalIsbnWrapper');
            if (type.toLowerCase() === 'book') {
                isbnWrapper.style.display = 'flex';
                document.getElementById('modalIsbn').innerText = this.getAttribute('data-isbn') || 'N/A';
            } else {
                isbnWrapper.style.display = 'none';
            }
            
            document.getElementById('modalBadge').className = 'badge ' + this.getAttribute('data-badgeclass');
            document.getElementById('modalBadge').innerText = this.getAttribute('data-badge');

            const statusClass = this.getAttribute('data-statusclass');
            const status = this.getAttribute('data-status');
            const headerStatusElem = document.getElementById('modalHeaderStatus');
            headerStatusElem.className = 'status ' + statusClass;
            headerStatusElem.innerHTML = statusClass === 'available' 
                ? `<i class="fas fa-check-circle"></i> ${status}` 
                : `<i class="fas fa-times-circle"></i> ${status}`;

            const borrowBtn = document.getElementById('modalBorrowBtn');
            if (statusClass === 'unavailable') {
                borrowBtn.classList.add('disabled');
                borrowBtn.innerHTML = 'Currently Unavailable <i class="fas fa-lock"></i>';
                borrowBtn.href = "#";
            } else {
                borrowBtn.classList.remove('disabled');
                borrowBtn.innerHTML = 'Proceed to Checkout <i class="fas fa-arrow-right"></i>';
                borrowBtn.href = "actions/checkout_process?item=" + encodeURIComponent(this.getAttribute('data-barcode'));
            }

            modal.classList.add('active');
            body.style.overflow = 'hidden';
        });
    });

    function closeModal() { modal.classList.remove('active'); body.style.overflow = ''; }
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', e => { if (e.key === "Escape" && modal.classList.contains('active')) closeModal(); });
</script>
<?php
$page_scripts = ob_get_clean();

// ==========================================
// 4. REQUIRE LAYOUT
// ==========================================
require 'layout.php';
?>