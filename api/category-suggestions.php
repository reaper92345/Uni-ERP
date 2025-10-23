<?php
require_once '../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $conn = getDBConnection();
    
    // Get search term if provided
    $searchTerm = isset($_GET['q']) ? sanitize($_GET['q']) : '';
    
    // Build query to get unique categories
    $sql = "SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != ''";
    $params = [];
    $types = "";
    
    if (!empty($searchTerm)) {
        $sql .= " AND category LIKE ?";
        $params[] = "%$searchTerm%";
        $types .= "s";
    }
    
    $sql .= " ORDER BY category ASC LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
    
    // Also include default categories from expense_categories table
    $sql2 = "SELECT DISTINCT name FROM expense_categories WHERE name IS NOT NULL AND name != ''";
    if (!empty($searchTerm)) {
        $sql2 .= " AND name LIKE ?";
    }
    $sql2 .= " ORDER BY name ASC LIMIT 5";
    
    $stmt2 = $conn->prepare($sql2);
    if (!empty($searchTerm)) {
        $stmt2->bind_param("s", "%$searchTerm%");
    }
    
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    
    while ($row = $result2->fetch_assoc()) {
        if (!in_array($row['name'], $categories)) {
            $categories[] = $row['name'];
        }
    }
    
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
