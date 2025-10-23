<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Get total products
$conn = getDBConnection();
$sql = "SELECT COUNT(*) as total FROM products";
$result = $conn->query($sql);
$totalProducts = $result->fetch_assoc()['total'];

// Get total sales for current month (uses sales.amount, sales.date)
$sql = "SELECT COALESCE(SUM(amount), 0) as total FROM sales WHERE MONTH(`date`) = MONTH(CURRENT_DATE()) AND YEAR(`date`) = YEAR(CURRENT_DATE())";
$result = $conn->query($sql);
$monthlySales = $result->fetch_assoc()['total'];

// Get total expenses for current month
$sql = "SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE())";
$result = $conn->query($sql);
$monthlyExpenses = $result->fetch_assoc()['total'];

// Get low stock products (less than 10 units)
$sql = "SELECT COUNT(*) as total FROM products WHERE stock < 10";
$result = $conn->query($sql);
$lowStock = $result->fetch_assoc()['total'];

// Get total inventory value
$sql = "SELECT SUM(stock * purchase_price) as total_inventory_value FROM products";
$result = $conn->query($sql);
$totalInventoryValue = $result->fetch_assoc()['total_inventory_value'] ?? 0;

$conn->close();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-content">
        <div class="hero-text">
            <h1 class="hero-title">ERP Dashboard</h1>
            <p class="hero-subtitle">Manage your business operations with our comprehensive ERP solution</p>
        </div>
        <div class="hero-actions">
            <a href="landing.php" class="google-signin-btn">
                <i class="fas fa-rocket"></i>
                <span>Launch ERP Modules</span>
            </a>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="modules-section">
    <div class="section-header">
        <h2 class="section-title">Business Overview</h2>
    </div>
    <div class="modules-grid">
        <div class="module-card core-module">
            <div class="module-icon">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="module-content">
                <h3 class="module-name"><?php echo $totalProducts; ?></h3>
                <p class="module-desc">Total Products</p>
            </div>
        </div>
        <div class="module-card core-module">
            <div class="module-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="module-content">
                <h3 class="module-name">NPR <?php echo number_format($monthlySales, 2); ?></h3>
                <p class="module-desc">Monthly Sales</p>
            </div>
        </div>
        <div class="module-card core-module">
            <div class="module-icon">
                <i class="fas fa-money-bill"></i>
            </div>
            <div class="module-content">
                <h3 class="module-name">NPR <?php echo number_format($monthlyExpenses, 2); ?></h3>
                <p class="module-desc">Monthly Expenses</p>
            </div>
        </div>
        <div class="module-card optional-module">
            <div class="module-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="module-content">
                <h3 class="module-name"><?php echo $lowStock; ?></h3>
                <p class="module-desc">Low Stock Items</p>
            </div>
        </div>
        <div class="module-card optional-module">
            <div class="module-icon">
                <i class="fas fa-coins"></i>
            </div>
            <div class="module-content">
                <h3 class="module-name">NPR <?php echo number_format($totalInventoryValue, 2); ?></h3>
                <p class="module-desc">Total Inventory Value</p>
            </div>
        </div>
    </div>
</section>

<!-- Analytics Section -->
<section class="modules-section">
    <div class="section-header">
        <h2 class="section-title">Analytics & Reports</h2>
    </div>
    <div class="modules-grid">
        <div class="module-card core-module">
            <div class="module-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="module-content">
                <h3 class="module-name">Sales vs Expenses</h3>
                <p class="module-desc">Last 6 months trend analysis</p>
            </div>
        </div>
        <div class="module-card core-module">
            <div class="module-icon">
                <i class="fas fa-chart-bar"></i>
            </div>
            <div class="module-content">
                <h3 class="module-name">Monthly Reports</h3>
                <p class="module-desc">Comprehensive business insights</p>
            </div>
        </div>
        <div class="module-card optional-module">
            <div class="module-icon">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div class="module-content">
                <h3 class="module-name">Performance KPIs</h3>
                <p class="module-desc">Key performance indicators</p>
            </div>
        </div>
    </div>
    <div class="analytics-card">
        <div class="analytics-header">
            <h3 class="analytics-title">Sales vs Expenses (Last 6 Months)</h3>
        </div>
        <div class="analytics-content">
            <div class="chart-container" style="height: 400px;">
                <canvas id="salesExpensesChart"></canvas>
            </div>
        </div>
    </div>
</section>

<!-- Inventory Management Section -->
<section class="modules-section">
    <div class="section-header">
        <h2 class="section-title">Inventory Management</h2>
    </div>
    <div class="modules-grid">
        <div class="module-card core-module">
            <div class="module-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="module-content">
                <h3 class="module-name">Low Stock Alert</h3>
                <p class="module-desc">Products requiring restocking</p>
            </div>
        </div>
        <div class="module-card core-module">
            <div class="module-icon">
                <i class="fas fa-warehouse"></i>
            </div>
            <div class="module-content">
                <h3 class="module-name">Stock Management</h3>
                <p class="module-desc">Inventory tracking and control</p>
            </div>
        </div>
        <div class="module-card optional-module">
            <div class="module-icon">
                <i class="fas fa-sync-alt"></i>
            </div>
            <div class="module-content">
                <h3 class="module-name">Stock Movements</h3>
                <p class="module-desc">Track all inventory changes</p>
            </div>
        </div>
    </div>
    <div class="analytics-card">
        <div class="analytics-header">
            <h3 class="analytics-title">Low Stock Products</h3>
        </div>
        <div class="analytics-content">
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Current Stock</th>
                            <th>Unit</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $conn = getDBConnection();
                        $sql = "SELECT * FROM products WHERE stock < 10 ORDER BY stock ASC";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . $row['stock'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['unit']) . "</td>";
                            echo "<td><a href='pages/products.php?id=" . $row['id'] . "' class='modern-btn'>Update Stock</a></td>";
                            echo "</tr>";
                        }
                        if ($result->num_rows === 0) {
                            echo "<tr><td colspan='4' class='text-center'>No low stock products found</td></tr>";
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 