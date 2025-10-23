<?php
require_once '../includes/config.php';
require_once '../includes/header.php';

$conn = getDBConnection();
$message = '';

// Handle form submission for expenses
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_expense') {
            $category_id = (int)$_POST['category_id'];
            $amount = (float)$_POST['amount'];
            $quantity = isset($_POST['quantity']) ? (float)$_POST['quantity'] : null;
            $unit_price = isset($_POST['unit_price']) ? (float)$_POST['unit_price'] : null;
            $date = formatDate($_POST['date']);
            $note = sanitize($_POST['note']);
            
            $sql = "INSERT INTO expenses (category_id, amount, quantity, unit_price, date, note) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iddsss", $category_id, $amount, $quantity, $unit_price, $date, $note);
            
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Expense recorded successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error recording expense: " . $conn->error . "</div>";
            }
        } elseif ($_POST['action'] === 'add_category') {
            $name = sanitize($_POST['category_name']);
            $description = sanitize($_POST['category_description']);
            
            $sql = "INSERT INTO expense_categories (name, description) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $name, $description);
            
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Category added successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error adding category: " . $conn->error . "</div>";
            }
        }
    }
}

// Get all expense categories
$categories = array();
$sql = "SELECT * FROM expense_categories ORDER BY name ASC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}
?>

<section class="modules-section">
    <div class="section-header">
        <h2 class="section-title">Expenses</h2>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 analytics-card">
            <div class="analytics-header">
                <h3 class="analytics-title">Record Expense</h3>
            </div>
            <div class="analytics-content">
                <?php echo $message; ?>
                <form method="POST" action="expenses.php">
                    <input type="hidden" name="action" value="add_expense">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-text-primary mb-1">Category</label>
                            <select class="form-select w-full" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="amount" class="block text-sm font-medium text-text-primary mb-1">Total Amount (NPR)</label>
                            <input type="number" class="form-control w-full" id="amount" name="amount" step="0.01" min="0" readonly>
                        </div>
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-text-primary mb-1">Quantity (e.g., liters)</label>
                            <input type="number" class="form-control w-full" id="quantity" name="quantity" step="0.01" min="0">
                        </div>
                        <div>
                            <label for="unit_price" class="block text-sm font-medium text-text-primary mb-1">Unit Price (NPR)</label>
                            <input type="number" class="form-control w-full" id="unit_price" name="unit_price" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="date" class="block text-sm font-medium text-text-primary mb-1">Date</label>
                            <input type="date" class="form-control w-full" id="date" name="date" required>
                        </div>
                        <div>
                            <label for="note" class="block text-sm font-medium text-text-primary mb-1">Note</label>
                            <input type="text" class="form-control w-full" id="note" name="note">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="modern-btn">Record Expense</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="analytics-card">
            <div class="analytics-header">
                <h3 class="analytics-title">Add Category</h3>
            </div>
            <div class="analytics-content">
                <form method="POST" action="expenses.php">
                    <input type="hidden" name="action" value="add_category">
                    <div class="mb-3">
                        <label for="category_name" class="block text-sm font-medium text-text-primary mb-1">Category Name</label>
                        <input type="text" class="form-control w-full" id="category_name" name="category_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="category_description" class="block text-sm font-medium text-text-primary mb-1">Description</label>
                        <input type="text" class="form-control w-full" id="category_description" name="category_description">
                    </div>
                    <button type="submit" class="modern-btn">Add Category</button>
                </form>
            </div>
        </div>
    </div>
    <div class="analytics-card lg:col-span-3">
        <div class="analytics-header">
            <h3 class="analytics-title">Recent Expenses</h3>
        </div>
        <div class="analytics-content">
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT e.*, c.name as category_name FROM expenses e JOIN expense_categories c ON e.category_id = c.id ORDER BY e.date DESC, e.id DESC LIMIT 10";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . date('M d, Y', strtotime($row['date'])) . "</td>";
                            echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
                            echo "<td>NPR " . number_format($row['amount'], 2) . "</td>";
                            echo "<td>" . ($row['quantity'] !== null ? $row['quantity'] : '-') . "</td>";
                            echo "<td>" . ($row['unit_price'] !== null ? 'NPR ' . number_format($row['unit_price'], 2) : '-') . "</td>";
                            echo "<td>" . htmlspecialchars($row['note']) . "</td>";
                            echo "</tr>";
                        }
                        if ($result->num_rows === 0) {
                            echo "<tr><td colspan='6' class='text-center'>No expenses recorded yet</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<script>
// Auto-calculate total amount
const quantityInput = document.getElementById('quantity');
const unitPriceInput = document.getElementById('unit_price');
const amountInput = document.getElementById('amount');

function updateAmount() {
    const qty = parseFloat(quantityInput.value) || 0;
    const price = parseFloat(unitPriceInput.value) || 0;
    amountInput.value = (qty * price).toFixed(2);
}

quantityInput.addEventListener('input', updateAmount);
unitPriceInput.addEventListener('input', updateAmount);
</script>
<?php
$conn->close();
require_once '../includes/footer.php';
?> 