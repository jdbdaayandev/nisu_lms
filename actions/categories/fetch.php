<?php
// actions/categories/fetch.php

// 1. Go up two levels (out of categories, out of actions) to reach includes
require_once('../../includes/functions.php');

// 2. Get the database connection
$db = conn();

// 3. Query the categories
$sql = "SELECT category_id, category_name FROM lib_catalog_categories ORDER BY category_name ASC";
$result = $db->query($sql);

$categories = [];
if ($result && $result->num_rows > 0) {
    $categories = $result->fetch_all(MYSQLI_ASSOC);
}

// 4. Return the data (Usually as JSON so JavaScript/Ajax can read it easily)
header('Content-Type: application/json');
echo json_encode($categories);
?>