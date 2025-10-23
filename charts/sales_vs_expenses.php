<?php
require_once '../includes/config.php';

// Ensure JSON-only output (suppress HTML notices that break JSON parsing)
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);
ob_start();

try {
    // Get the last 6 months of data
    $conn = getDBConnection();
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Initialize arrays for the last 6 months
    $labels = array();
    $monetaryData = array();
    $quantityData = array();
    $expenses = array();

    // Define commodity units
    $commodityUnits = array(
        'paddy' => 'kg',
        'wheat' => 'kg',
        'corn' => 'kg',
        'soybean_oil' => 'liters',
        'mustard_oil' => 'liters',
        'sunflower_oil' => 'liters'
    );

    // Get the current date
    $currentDate = new DateTime();
    $currentDate->modify('first day of this month');

    // Loop through the last 6 months
    for ($i = 5; $i >= 0; $i--) {
        $date = clone $currentDate;
        $date->modify("-$i months");
        
        // Format the month for display
        $labels[] = $date->format('M Y');
        
        // Get sales for this month
        $sql = "SELECT 
                    p.category,
                    COALESCE(SUM(s.quantity), 0) as total_quantity,
                    COALESCE(SUM(s.amount), 0) as total_amount
                FROM sales s
                JOIN products p ON s.product_id = p.id
                WHERE MONTH(s.date) = ? AND YEAR(s.date) = ?
                GROUP BY p.category";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare sales query: " . $conn->error);
        }
        
        $month = $date->format('m');
        $year = $date->format('Y');
        $stmt->bind_param("ss", $month, $year);
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute sales query: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception("Failed to get sales result: " . $stmt->error);
        }
        
        $monthMonetaryData = array();
        $monthQuantityData = array();
        while ($row = $result->fetch_assoc()) {
            $category = strtolower(str_replace(' ', '_', $row['category']));
            $monthMonetaryData[$category] = (float)$row['total_amount'];
            $monthQuantityData[$category] = (float)$row['total_quantity'];
        }
        $monetaryData[] = $monthMonetaryData;
        $quantityData[] = $monthQuantityData;
        
        // Get expenses for this month
        $sql = "SELECT COALESCE(SUM(amount), 0) as total 
                FROM expenses 
                WHERE MONTH(date) = ? AND YEAR(date) = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare expenses query: " . $conn->error);
        }
        
        $stmt->bind_param("ss", $month, $year);
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute expenses query: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception("Failed to get expenses result: " . $stmt->error);
        }
        
        $row = $result->fetch_assoc();
        $expenses[] = (float)$row['total'];
    }

    $conn->close();

    // Prepare datasets for monetary view
    $monetaryDatasets = array(
        array(
            'label' => 'Sales',
            'data' => array_map(function($monthData) {
                return array_sum($monthData);
            }, $monetaryData),
            'backgroundColor' => 'rgba(0, 86, 179, 0.7)',
            'borderColor' => 'rgba(0, 86, 179, 1)',
            'borderWidth' => 1
        ),
        array(
            'label' => 'Expenses',
            'data' => $expenses,
            'backgroundColor' => 'rgba(220, 53, 69, 0.7)',
            'borderColor' => 'rgba(220, 53, 69, 1)',
            'borderWidth' => 1
        )
    );

    // Prepare datasets for quantity view
    $quantityDatasets = array();
    $colors = array(
        'paddy' => ['rgba(139, 69, 19, 0.7)', 'rgba(139, 69, 19, 1)'],      // Brown
        'wheat' => ['rgba(210, 180, 140, 0.7)', 'rgba(210, 180, 140, 1)'],  // Tan
        'corn' => ['rgba(255, 215, 0, 0.7)', 'rgba(255, 215, 0, 1)'],      // Gold
        'soybean_oil' => ['rgba(0, 128, 0, 0.7)', 'rgba(0, 128, 0, 1)'],   // Green
        'mustard_oil' => ['rgba(255, 165, 0, 0.7)', 'rgba(255, 165, 0, 1)'], // Orange
        'sunflower_oil' => ['rgba(255, 255, 0, 0.7)', 'rgba(255, 255, 0, 1)'] // Yellow
    );

    foreach ($commodityUnits as $commodity => $unit) {
        $commodityData = array();
        foreach ($quantityData as $monthData) {
            $commodityData[] = isset($monthData[$commodity]) ? $monthData[$commodity] : 0;
        }
        
        $quantityDatasets[] = array(
            'label' => ucwords(str_replace('_', ' ', $commodity)) . ' (' . $unit . ')',
            'data' => $commodityData,
            'backgroundColor' => $colors[$commodity][0],
            'borderColor' => $colors[$commodity][1],
            'borderWidth' => 1
        );
    }

    // Clear any incidental output and return the data as JSON
    if (ob_get_length()) { ob_clean(); }
    echo json_encode([
        'labels' => $labels,
        'monetaryData' => [
            'datasets' => $monetaryDatasets
        ],
        'quantityData' => [
            'datasets' => $quantityDatasets
        ]
    ]);

} catch (Exception $e) {
    // Log the error
    error_log("Chart data error: " . $e->getMessage());
    
    // Clear any incidental output and return error response
    if (ob_get_length()) { ob_clean(); }
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
} 