<?php
require_once '../includes/config.php';
require_once '../includes/header.php';

$conn = getDBConnection();
$message = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $amount = (float)$_POST['amount'];
    $date = formatDate($_POST['date']);
    
 
    $currentStock = getCurrentStock($product_id);
    if ($currentStock >= $quantity) {
        $sql = "INSERT INTO sales (product_id, quantity, amount, date) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iids", $product_id, $quantity, $amount, $date);
        
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Sale recorded successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error recording sale: " . $conn->error . "</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Not enough stock available! Current stock: $currentStock</div>";
    }
}

$products = array();
$sql = "SELECT id, name, unit FROM products ORDER BY name ASC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
?>

<section class="modules-section">
    <div class="section-header">
        <h2 class="section-title">Sales Management</h2>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="analytics-card">
            <div class="analytics-header">
                <h3 class="analytics-title">Record Sale</h3>
            </div>
            <div class="analytics-content">
                <?php echo $message; ?>
                <form method="POST" action="sales.php">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="product_id" class="block text-sm font-medium text-text-primary mb-1">Product</label>
                            <select class="form-select w-full" id="product_id" name="product_id" required>
                                <option value="">Select Product</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo $product['id']; ?>">
                                        <?php echo htmlspecialchars($product['name']) . ' (' . htmlspecialchars($product['unit']) . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-text-primary mb-1">Quantity</label>
                            <input type="number" class="form-control w-full" id="quantity" name="quantity" required min="1">
                        </div>
                        <div>
                            <label for="amount" class="block text-sm font-medium text-text-primary mb-1">Amount ($)</label>
                            <input type="number" class="form-control w-full" id="amount" name="amount" required step="0.01" min="0">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="date" class="block text-sm font-medium text-text-primary mb-1">Date</label>
                        <input type="date" class="form-control w-full" id="date" name="date" required>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="modern-btn">Record Sale</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="analytics-card">
            <div class="analytics-header">
                <h3 class="analytics-title">Recent Sales</h3>
            </div>
            <div class="analytics-content">
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT s.*, p.name, p.unit FROM sales s JOIN products p ON s.product_id = p.id ORDER BY s.date DESC, s.id DESC LIMIT 10";
                            $result = $conn->query($sql);
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . date('M d, Y', strtotime($row['date'])) . "</td>";
                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td>" . $row['quantity'] . " " . htmlspecialchars($row['unit']) . "</td>";
                                echo "<td>NPR " . number_format($row['amount'], 2) . "</td>";
                                echo "</tr>";
                            }
                            if ($result->num_rows === 0) {
                                echo "<tr><td colspan='4' class='text-center'>No sales recorded yet</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$conn->close();
require_once '../includes/footer.php';
?> 