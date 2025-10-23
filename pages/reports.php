<?php
require_once '../includes/config.php';

// Create database connection first
$conn = getDBConnection();

// Handle export requests BEFORE any output
if (isset($_GET['export'])) {
    $format = $_GET['export'];
    $type = $_GET['type'] ?? 'sales';
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
    
    switch ($format) {
        case 'csv':
            exportToCSV($type, $start_date, $end_date);
            break;
        case 'json':
            exportToJSON($type, $start_date, $end_date);
            break;
        case 'excel':
            exportToExcel($type, $start_date, $end_date);
            break;
        case 'pdf':
            exportToPDF($type, $start_date, $end_date);
            break;
        case 'xml':
            exportToXML($type, $start_date, $end_date);
            break;
    }
}

require_once '../includes/header.php';

// Get date range filters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'sales';

// Get summary statistics
$total_sales = getTotalSales($start_date, $end_date);
$total_expenses = getTotalExpenses($start_date, $end_date);
$total_products = getTotalProducts();
$low_stock_products = getLowStockProducts();

function getTotalSales($start_date, $end_date) {
    global $conn;
    $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM sales WHERE DATE(date) BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['total'];
}

function getTotalExpenses($start_date, $end_date) {
    global $conn;
    $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE date BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['total'];
}

function getTotalProducts() {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM products";
    $result = $conn->query($sql);
    return $result->fetch_assoc()['total'];
}

function getLowStockProducts() {
    global $conn;
    $sql = "SELECT p.*, (p.stock - COALESCE(s.total_sold, 0)) as current_stock 
            FROM products p 
            LEFT JOIN (SELECT product_id, SUM(quantity) as total_sold FROM sales GROUP BY product_id) s 
            ON p.id = s.product_id 
            WHERE (p.stock - COALESCE(s.total_sold, 0)) <= 10";
    $result = $conn->query($sql);
    return $result->num_rows;
}

function exportToCSV($type, $start_date, $end_date) {
    global $conn;
    
    $filename = $type . '_report_' . date('Y-m-d') . '.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    switch ($type) {
        case 'sales':
            $sql = "SELECT s.id, p.name as product_name, s.quantity, s.amount, s.date 
                    FROM sales s 
                    JOIN products p ON s.product_id = p.id 
                    WHERE DATE(s.date) BETWEEN ? AND ? 
                    ORDER BY s.date DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $start_date, $end_date);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // CSV headers
            fputcsv($output, ['ID', 'Product Name', 'Quantity', 'Amount', 'Sale Date']);
            
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, [
                    $row['id'],
                    $row['product_name'],
                    $row['quantity'],
                    $row['amount'],
                    $row['date']
                ]);
            }
            break;
            
        case 'expenses':
            $sql = "SELECT e.id, ec.name as category, e.amount, e.date, e.note 
                    FROM expenses e 
                    JOIN expense_categories ec ON e.category_id = ec.id 
                    WHERE e.date BETWEEN ? AND ? 
                    ORDER BY e.date DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $start_date, $end_date);
            $stmt->execute();
            $result = $stmt->get_result();
            
            fputcsv($output, ['ID', 'Category', 'Amount', 'Date', 'Note']);
            
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, [
                    $row['id'],
                    $row['category'],
                    $row['amount'],
                    $row['date'],
                    $row['note']
                ]);
            }
            break;
            
        case 'products':
            $sql = "SELECT p.*, (p.stock - COALESCE(s.total_sold, 0)) as current_stock 
                    FROM products p 
                    LEFT JOIN (SELECT product_id, SUM(quantity) as total_sold FROM sales GROUP BY product_id) s 
                    ON p.id = s.product_id 
                    ORDER BY p.name";
            $result = $conn->query($sql);
            
            fputcsv($output, ['ID', 'Name', 'Category', 'Current Stock', 'Unit', 'Purchase Price']);
            
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, [
                    $row['id'],
                    $row['name'],
                    $row['category'],
                    $row['current_stock'],
                    $row['unit'],
                    $row['purchase_price']
                ]);
            }
            break;
    }
    
    fclose($output);
    exit;
}

function exportToJSON($type, $start_date, $end_date) {
    global $conn;
    
    $filename = $type . '_report_' . date('Y-m-d') . '.json';
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $data = [];
    
    switch ($type) {
        case 'sales':
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
            break;
            
        case 'expenses':
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
            break;
            
        case 'products':
            $sql = "SELECT p.*, (p.stock - COALESCE(s.total_sold, 0)) as current_stock 
                    FROM products p 
                    LEFT JOIN (SELECT product_id, SUM(quantity) as total_sold FROM sales GROUP BY product_id) s 
                    ON p.id = s.product_id 
                    ORDER BY p.name";
            $result = $conn->query($sql);
            
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            break;
    }
    
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

function exportToExcel($type, $start_date, $end_date) {
    global $conn;
    
    $filename = $type . '_report_' . date('Y-m-d') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // For Excel export, we'll create a simple HTML table that Excel can open
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><meta charset="UTF-8"></head><body>';
    echo '<table border="1">';
    
    switch ($type) {
        case 'sales':
            $sql = "SELECT s.id, p.name as product_name, s.quantity, s.amount, s.date 
                    FROM sales s 
                    JOIN products p ON s.product_id = p.id 
                    WHERE DATE(s.date) BETWEEN ? AND ? 
                    ORDER BY s.date DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $start_date, $end_date);
            $stmt->execute();
            $result = $stmt->get_result();
            
            echo '<tr><th>ID</th><th>Product Name</th><th>Quantity</th><th>Amount</th><th>Sale Date</th></tr>';
            
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $row['id'] . '</td>';
                echo '<td>' . htmlspecialchars($row['product_name']) . '</td>';
                echo '<td>' . $row['quantity'] . '</td>';
                echo '<td>' . $row['amount'] . '</td>';
                echo '<td>' . $row['date'] . '</td>';
                echo '</tr>';
            }
            break;
            
        case 'expenses':
            $sql = "SELECT e.id, ec.name as category, e.amount, e.date, e.note 
                    FROM expenses e 
                    JOIN expense_categories ec ON e.category_id = ec.id 
                    WHERE e.date BETWEEN ? AND ? 
                    ORDER BY e.date DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $start_date, $end_date);
            $stmt->execute();
            $result = $stmt->get_result();
            
            echo '<tr><th>ID</th><th>Category</th><th>Amount</th><th>Date</th><th>Note</th></tr>';
            
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $row['id'] . '</td>';
                echo '<td>' . htmlspecialchars($row['category']) . '</td>';
                echo '<td>' . $row['amount'] . '</td>';
                echo '<td>' . $row['date'] . '</td>';
                echo '<td>' . htmlspecialchars($row['note']) . '</td>';
                echo '</tr>';
            }
            break;
            
        case 'products':
            $sql = "SELECT p.*, (p.stock - COALESCE(s.total_sold, 0)) as current_stock 
                    FROM products p 
                    LEFT JOIN (SELECT product_id, SUM(quantity) as total_sold FROM sales GROUP BY product_id) s 
                    ON p.id = s.product_id 
                    ORDER BY p.name";
            $result = $conn->query($sql);
            
            echo '<tr><th>ID</th><th>Name</th><th>Category</th><th>Current Stock</th><th>Unit</th><th>Purchase Price</th></tr>';
            
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $row['id'] . '</td>';
                echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                echo '<td>' . htmlspecialchars($row['category']) . '</td>';
                echo '<td>' . $row['current_stock'] . '</td>';
                echo '<td>' . htmlspecialchars($row['unit']) . '</td>';
                echo '<td>' . $row['purchase_price'] . '</td>';
                echo '</tr>';
            }
            break;
    }
    
    echo '</table></body></html>';
    exit;
}

function exportToPDF($type, $start_date, $end_date) {
    global $conn;
    
    $filename = $type . '_report_' . date('Y-m-d') . '.html';
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo '<html><head><meta charset="UTF-8"><title>' . ucfirst($type) . ' Report</title>';
    echo '<style>body{font-family:Arial,sans-serif;margin:20px;}table{border-collapse:collapse;width:100%;}th,td{border:1px solid #ddd;padding:8px;text-align:left;}th{background-color:#f2f2f2;}</style></head><body>';
    echo '<h1>' . ucfirst($type) . ' Report (' . $start_date . ' to ' . $end_date . ')</h1>';
    echo '<table>';
    
    switch ($type) {
        case 'sales':
            $sql = "SELECT s.id, p.name as product_name, s.quantity, s.amount, s.date 
                    FROM sales s 
                    JOIN products p ON s.product_id = p.id 
                    WHERE DATE(s.date) BETWEEN ? AND ? 
                    ORDER BY s.date DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $start_date, $end_date);
            $stmt->execute();
            $result = $stmt->get_result();
            
            echo '<tr><th>ID</th><th>Product Name</th><th>Quantity</th><th>Amount</th><th>Sale Date</th></tr>';
            
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $row['id'] . '</td>';
                echo '<td>' . htmlspecialchars($row['product_name']) . '</td>';
                echo '<td>' . $row['quantity'] . '</td>';
                echo '<td>' . $row['amount'] . '</td>';
                echo '<td>' . $row['date'] . '</td>';
                echo '</tr>';
            }
            break;
            
        case 'expenses':
            $sql = "SELECT e.id, ec.name as category, e.amount, e.date, e.note 
                    FROM expenses e 
                    JOIN expense_categories ec ON e.category_id = ec.id 
                    WHERE e.date BETWEEN ? AND ? 
                    ORDER BY e.date DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $start_date, $end_date);
            $stmt->execute();
            $result = $stmt->get_result();
            
            echo '<tr><th>ID</th><th>Category</th><th>Amount</th><th>Date</th><th>Note</th></tr>';
            
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $row['id'] . '</td>';
                echo '<td>' . htmlspecialchars($row['category']) . '</td>';
                echo '<td>' . $row['amount'] . '</td>';
                echo '<td>' . $row['date'] . '</td>';
                echo '<td>' . htmlspecialchars($row['note']) . '</td>';
                echo '</tr>';
            }
            break;
            
        case 'products':
            $sql = "SELECT p.*, (p.stock - COALESCE(s.total_sold, 0)) as current_stock 
                    FROM products p 
                    LEFT JOIN (SELECT product_id, SUM(quantity) as total_sold FROM sales GROUP BY product_id) s 
                    ON p.id = s.product_id 
                    ORDER BY p.name";
            $result = $conn->query($sql);
            
            echo '<tr><th>ID</th><th>Name</th><th>Category</th><th>Current Stock</th><th>Unit</th><th>Purchase Price</th></tr>';
            
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $row['id'] . '</td>';
                echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                echo '<td>' . htmlspecialchars($row['category']) . '</td>';
                echo '<td>' . $row['current_stock'] . '</td>';
                echo '<td>' . htmlspecialchars($row['unit']) . '</td>';
                echo '<td>' . $row['purchase_price'] . '</td>';
                echo '</tr>';
            }
            break;
    }
    
    echo '</table></body></html>';
    exit;
}

function exportToXML($type, $start_date, $end_date) {
    global $conn;
    
    $filename = $type . '_report_' . date('Y-m-d') . '.xml';
    header('Content-Type: application/xml');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<report type="' . $type . '" start_date="' . $start_date . '" end_date="' . $end_date . '">';
    
    switch ($type) {
        case 'sales':
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
                echo '<sale>';
                echo '<id>' . $row['id'] . '</id>';
                echo '<product_name>' . htmlspecialchars($row['product_name']) . '</product_name>';
                echo '<quantity>' . $row['quantity'] . '</quantity>';
                echo '<amount>' . $row['amount'] . '</amount>';
                echo '<sale_date>' . $row['date'] . '</sale_date>';
                echo '</sale>';
            }
            break;
            
        case 'expenses':
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
                echo '<expense>';
                echo '<id>' . $row['id'] . '</id>';
                echo '<category>' . htmlspecialchars($row['category']) . '</category>';
                echo '<amount>' . $row['amount'] . '</amount>';
                echo '<date>' . $row['date'] . '</date>';
                echo '<note>' . htmlspecialchars($row['note']) . '</note>';
                echo '</expense>';
            }
            break;
            
        case 'products':
            $sql = "SELECT p.*, (p.stock - COALESCE(s.total_sold, 0)) as current_stock 
                    FROM products p 
                    LEFT JOIN (SELECT product_id, SUM(quantity) as total_sold FROM sales GROUP BY product_id) s 
                    ON p.id = s.product_id 
                    ORDER BY p.name";
            $result = $conn->query($sql);
            
            while ($row = $result->fetch_assoc()) {
                echo '<product>';
                echo '<id>' . $row['id'] . '</id>';
                echo '<name>' . htmlspecialchars($row['name']) . '</name>';
                echo '<category>' . htmlspecialchars($row['category']) . '</category>';
                echo '<current_stock>' . $row['current_stock'] . '</current_stock>';
                echo '<unit>' . htmlspecialchars($row['unit']) . '</unit>';
                echo '<purchase_price>' . $row['purchase_price'] . '</purchase_price>';
                echo '</product>';
            }
            break;
    }
    
    echo '</report>';
    exit;
}
?>

<section class="modules-section">
    <div class="section-header">
        <h2 class="section-title">Analytics & Reports</h2>
    </div>
    <div class="modules-grid">
        <div class="module-card core-module">
            <div class="module-icon"><i class="fas fa-coins"></i></div>
            <div class="module-content">
                <h3 class="module-name">NPR <?php echo number_format($total_sales, 2); ?></h3>
                <p class="module-desc">Total Sales</p>
            </div>
        </div>
        <div class="module-card core-module">
            <div class="module-icon"><i class="fas fa-money-bill"></i></div>
            <div class="module-content">
                <h3 class="module-name">NPR <?php echo number_format($total_expenses, 2); ?></h3>
                <p class="module-desc">Total Expenses</p>
            </div>
        </div>
        <div class="module-card core-module">
            <div class="module-icon"><i class="fas fa-boxes"></i></div>
            <div class="module-content">
                <h3 class="module-name"><?php echo $total_products; ?></h3>
                <p class="module-desc">Total Products</p>
            </div>
        </div>
        <div class="module-card optional-module">
            <div class="module-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="module-content">
                <h3 class="module-name"><?php echo $low_stock_products; ?></h3>
                <p class="module-desc">Low Stock Items</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="analytics-card">
            <div class="analytics-header">
                <h3 class="analytics-title">Report Filters</h3>
            </div>
            <div class="analytics-content">
            <form method="GET" action="reports.php" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="report_type" class="block text-sm font-medium text-text-primary mb-1">Report Type</label>
                        <select name="report_type" id="report_type" class="form-control w-full">
                            <option value="sales" <?php echo $report_type === 'sales' ? 'selected' : ''; ?>>Sales Report</option>
                            <option value="expenses" <?php echo $report_type === 'expenses' ? 'selected' : ''; ?>>Expenses Report</option>
                            <option value="products" <?php echo $report_type === 'products' ? 'selected' : ''; ?>>Products Report</option>
                        </select>
                    </div>
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-text-primary mb-1">Start Date</label>
                        <input type="date" name="start_date" id="start_date" value="<?php echo $start_date; ?>" class="form-control w-full">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-text-primary mb-1">End Date</label>
                        <input type="date" name="end_date" id="end_date" value="<?php echo $end_date; ?>" class="form-control w-full">
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="modern-btn">Generate Report</button>
                    <button type="button" onclick="window.location.href='reports.php'" class="btn bg-gray-200 text-gray-700 hover:bg-gray-300">Reset</button>
                </div>
            </form>
            </div>
        </div>
        
        <div class="analytics-card">
            <div class="analytics-header">
                <h3 class="analytics-title">Export Options</h3>
            </div>
            <div class="analytics-content">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                <a href="?export=csv&type=<?php echo $report_type; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                   class="modern-btn">
                    <i class="fas fa-file-csv mr-2"></i>CSV
                </a>
                <a href="?export=json&type=<?php echo $report_type; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                   class="modern-btn">
                    <i class="fas fa-file-code mr-2"></i>JSON
                </a>
                <a href="?export=excel&type=<?php echo $report_type; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                   class="modern-btn">
                    <i class="fas fa-file-excel mr-2"></i>Excel
                </a>
                <a href="?export=pdf&type=<?php echo $report_type; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                   class="modern-btn">
                    <i class="fas fa-file-pdf mr-2"></i>PDF
                </a>
                <a href="?export=xml&type=<?php echo $report_type; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                   class="modern-btn">
                    <i class="fas fa-file-code mr-2"></i>XML
                </a>
                <button onclick="printReport()" class="modern-btn">
                    <i class="fas fa-print mr-2"></i>Print
                </button>
            </div>
            </div>
        </div>
    </div>

    <div class="analytics-card">
        <div class="analytics-header">
            <h3 class="analytics-title">
                <?php echo ucfirst($report_type); ?> Report 
                (<?php echo date('M d, Y', strtotime($start_date)); ?> - <?php echo date('M d, Y', strtotime($end_date)); ?>)
            </h3>
        </div>
        <div class="analytics-content">
            <div class="table-responsive">
                <table class="modern-table">
                <thead>
                    <?php if ($report_type === 'sales'): ?>
                        <tr>
                            <th>ID</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Amount</th>
                            <th>Sale Date</th>
                        </tr>
                    <?php elseif ($report_type === 'expenses'): ?>
                        <tr>
                            <th>ID</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Note</th>
                        </tr>
                    <?php elseif ($report_type === 'products'): ?>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Unit</th>
                            <th>Purchase Price</th>
                        </tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                    <?php
                    if ($report_type === 'sales') {
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
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                            echo "<td>" . $row['quantity'] . "</td>";
                            echo "<td>NPR " . number_format($row['amount'], 2) . "</td>";
                            echo "<td>" . date('M d, Y', strtotime($row['date'])) . "</td>";
                            echo "</tr>";
                        }
                    } elseif ($report_type === 'expenses') {
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
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                            echo "<td>NPR " . number_format($row['amount'], 2) . "</td>";
                            echo "<td>" . date('M d, Y', strtotime($row['date'])) . "</td>";
                            echo "<td>" . htmlspecialchars($row['note']) . "</td>";
                            echo "</tr>";
                        }
                    } elseif ($report_type === 'products') {
                        $sql = "SELECT p.*, (p.stock - COALESCE(s.total_sold, 0)) as current_stock 
                                FROM products p 
                                LEFT JOIN (SELECT product_id, SUM(quantity) as total_sold FROM sales GROUP BY product_id) s 
                                ON p.id = s.product_id 
                                ORDER BY p.name";
                        $result = $conn->query($sql);
                        
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                            echo "<td>" . $row['current_stock'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['unit']) . "</td>";
                            echo "<td>NPR " . number_format($row['purchase_price'], 2) . "</td>";
                            echo "</tr>";
                        }
                    }
                    
                    if ($result->num_rows === 0) {
                        echo "<tr><td colspan='6' class='text-center'>No data found for the selected criteria</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</section>

<script>
function printReport() {
    window.print();
}

// Auto-submit form when report type changes
document.getElementById('report_type').addEventListener('change', function() {
    this.form.submit();
});
</script>

<?php
$conn->close();
require_once '../includes/footer.php';
?>
