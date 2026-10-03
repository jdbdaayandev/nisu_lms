<?php
require_once(__DIR__ . '/../config/db.php');

function conn()
{
    global $conn;
    return $conn;
}

// ==========================================
// CATEGORY FUNCTIONS
// ==========================================

/**
 * Get total count of categories based on search query
 */
function getCategoriesCount($search_query = '') {
    $db = conn(); // Get the connection using your wrapper
    $search_param = "%{$search_query}%";
    $sql = "SELECT COUNT(*) FROM lib_catalog_categories WHERE category_name LIKE ? OR category_description LIKE ?";
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ss", $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $count = $result->fetch_row()[0];
    $stmt->close();
    
    return $count;
}

/**
 * Get paginated array of categories
 */
function getCategories($search_query = '', $limit = 5, $offset = 0) {
    $db = conn(); // Get the connection using your wrapper
    $search_param = "%{$search_query}%";
    
    // NOTE: Change 'FROM books b' to your actual catalog/items table name
    $sql = "SELECT c.category_id, c.category_name, c.category_description, c.category_status, 
            (SELECT COUNT(*) FROM catalogs b WHERE b.category_id = c.category_id) as count 
            FROM lib_catalog_categories c 
            WHERE c.category_name LIKE ? OR c.category_description LIKE ? 
            ORDER BY c.category_name ASC 
            LIMIT ? OFFSET ?";
            
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ssii", $search_param, $search_param, $limit, $offset);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $categories = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return $categories;
}
?>