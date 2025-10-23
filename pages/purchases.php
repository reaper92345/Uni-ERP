<?php
require_once '../includes/config.php';
require_once '../includes/header.php';

$conn = getDBConnection();
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $purchase_price = (float)$_POST['purchase_price'];
    $purchase_date = formatDate($_POST['purchase_date']);

    $sql = "INSERT INTO purchases (product_id, quantity, purchase_price, purchase_date) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iids", $product_id, $quantity, $purchase_price, $purchase_date);
    if ($stmt->execute()) {
        // Update product stock and purchase_price
        $sql2 = "UPDATE products SET stock = stock + ?, purchase_price = ? WHERE id = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("idi", $quantity, $purchase_price, $product_id);
        $stmt2->execute();
        $message = "<div class='alert alert-success'>Purchase recorded and stock updated!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error recording purchase: " . $conn->error . "</div>";
    }
}

// Get all products for dropdown
$products = array();
$sql = "SELECT id, name, unit FROM products ORDER BY name ASC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
?>
<section class="modules-section">
    <div class="section-header">
        <h2 class="section-title">Purchases</h2>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="analytics-card">
            <div class="analytics-header">
                <h3 class="analytics-title">Record Purchase</h3>
            </div>
            <div class="analytics-content">
                <?php echo $message; ?>
                <form method="POST" action="purchases.php">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                            <label for="purchase_price" class="block text-sm font-medium text-text-primary mb-1">Purchase Price</label>
                            <input type="number" class="form-control w-full" id="purchase_price" name="purchase_price" required step="0.01" min="0">
                        </div>
                        <div>
                            <label for="purchase_date" class="block text-sm font-medium text-text-primary mb-1">Date</label>
                            <input type="date" class="form-control w-full" id="purchase_date" name="purchase_date" required>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="modern-btn">Record Purchase</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="analytics-card">
            <div class="analytics-header">
                <h3 class="analytics-title">Recent Purchases</h3>
            </div>
            <div class="analytics-content">
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Purchase Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT p.*, pr.name, pr.unit FROM purchases p JOIN products pr ON p.product_id = pr.id ORDER BY p.purchase_date DESC, p.id DESC LIMIT 10";
                            $result = $conn->query($sql);
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . date('M d, Y', strtotime($row['purchase_date'])) . "</td>";
                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td>" . $row['quantity'] . " " . htmlspecialchars($row['unit']) . "</td>";
                                echo "<td>NPR " . number_format($row['purchase_price'], 2) . "</td>";
                                echo "</tr>";
                            }
                            if ($result->num_rows === 0) {
                                echo "<tr><td colspan='4' class='text-center'>No purchases recorded yet</td></tr>";
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