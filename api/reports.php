<?php
require_once '../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $conn = getDBConnection();
    
    $report_type = isset($_GET['type']) ? $_GET['type'] : 'sales';
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
    
    $data = [];
    $summary = [];
    
    switch ($report_type) {
        case 'sales':
            // Get sales data
            $sql = "SELECT s.id, p.name as product_name, s.quantity, s.amount, s.date 
                    FROM sales s 
                    JOIN products p ON s.product_id = p.id 
                    WHERE DATE(s.date) BETWEEN ? AND ? 
                    ORDER BY s.date DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $start_date, $end_date);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            
            // Get summary
            $sql_summary = "SELECT 
                COUNT(*) as total_transactions,
                COALESCE(SUM(amount), 0) as total_revenue,
                COALESCE(SUM(quantity), 0) as total_quantity
                FROM sales 
                WHERE DATE(date) BETWEEN ? AND ?";
            $stmt_summary = $conn->prepare($sql_summary);
            $stmt_summary->bind_param("ss", $start_date, $end_date);
            $stmt_summary->execute();
            $summary = $stmt_summary->get_result()->fetch_assoc();
            break;
            
        case 'expenses':
            // Get expenses data
            $sql = "SELECT e.id, ec.name as category, e.amount, e.date, e.note 
                    FROM expenses e 
                    JOIN expense_categories ec ON e.category_id = ec.id 
                    WHERE e.date BETWEEN ? AND ? 
                    ORDER BY e.date DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $start_date, $end_date);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            
            // Get summary
            $sql_summary = "SELECT 
                COUNT(*) as total_expenses,
                COALESCE(SUM(amount), 0) as total_amount
                FROM expenses 
                WHERE date BETWEEN ? AND ?";
            $stmt_summary = $conn->prepare($sql_summary);
            $stmt_summary->bind_param("ss", $start_date, $end_date);
            $stmt_summary->execute();
            $summary = $stmt_summary->get_result()->fetch_assoc();
            break;
            
        case 'products':
            // Get products data
            $sql = "SELECT p.*, (p.stock - COALESCE(s.total_sold, 0)) as current_stock 
                    FROM products p 
                    LEFT JOIN (SELECT product_id, SUM(quantity) as total_sold FROM sales GROUP BY product_id) s 
                    ON p.id = s.product_id 
                    ORDER BY p.name";
            $result = $conn->query($sql);
            
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            
            // Get summary
            $sql_summary = "SELECT 
                COUNT(*) as total_products,
                COALESCE(SUM(stock), 0) as total_stock,
                COUNT(CASE WHEN (p.stock - COALESCE(s.total_sold, 0)) <= 10 THEN 1 END) as low_stock_count
                FROM products p 
                LEFT JOIN (SELECT product_id, SUM(quantity) as total_sold FROM sales GROUP BY product_id) s 
                ON p.id = s.product_id";
            $summary = $conn->query($sql_summary)->fetch_assoc();
            break;
            
        case 'summary':
            // Get overall summary
            $sql_sales = "SELECT COALESCE(SUM(amount), 0) as total_sales FROM sales WHERE DATE(date) BETWEEN ? AND ?";
            $stmt_sales = $conn->prepare($sql_sales);
            $stmt_sales->bind_param("ss", $start_date, $end_date);
            $stmt_sales->execute();
            $total_sales = $stmt_sales->get_result()->fetch_assoc()['total_sales'];
            
            $sql_expenses = "SELECT COALESCE(SUM(amount), 0) as total_expenses FROM expenses WHERE date BETWEEN ? AND ?";
            $stmt_expenses = $conn->prepare($sql_expenses);
            $stmt_expenses->bind_param("ss", $start_date, $end_date);
            $stmt_expenses->execute();
            $total_expenses = $stmt_expenses->get_result()->fetch_assoc()['total_expenses'];
            
            $sql_products = "SELECT COUNT(*) as total_products FROM products";
            $total_products = $conn->query($sql_products)->fetch_assoc()['total_products'];
            
            $summary = [
                'total_sales' => $total_sales,
                'total_expenses' => $total_expenses,
                'total_products' => $total_products,
                'net_profit' => $total_sales - $total_expenses,
                'period' => [
                    'start_date' => $start_date,
                    'end_date' => $end_date
                ]
            ];
            break;
            
        default:
            throw new Exception('Invalid report type');
    }
    
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'report_type' => $report_type,
        'period' => [
            'start_date' => $start_date,
            'end_date' => $end_date
        ],
        'summary' => $summary,
        'data' => $data,
        'total_records' => count($data),
        'generated_at' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Report generation failed: ' . $e->getMessage()
    ]);
}
?>
