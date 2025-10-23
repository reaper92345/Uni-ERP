<?php
require_once '../includes/config.php';
require_once '../includes/header.php';

$conn = getDBConnection();
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $name = sanitize($_POST['name']);
        $stock = (int)$_POST['stock'];
        $unit = sanitize($_POST['unit']);
        $purchase_price = isset($_POST['purchase_price']) ? (float)$_POST['purchase_price'] : 0;
        $category = sanitize($_POST['category']);
        
        if ($_POST['action'] === 'add') {
            $sql = "INSERT INTO products (name, stock, unit, purchase_price, category) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sisds", $name, $stock, $unit, $purchase_price, $category);
            
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Product added successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error adding product: " . $conn->error . "</div>";
            }
        } elseif ($_POST['action'] === 'edit' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $sql = "UPDATE products SET name = ?, stock = ?, unit = ?, purchase_price = ?, category = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sisdsi", $name, $stock, $unit, $purchase_price, $category, $id);
            
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Product updated successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error updating product: " . $conn->error . "</div>";
            }
        }
    }
}

// Handle delete request
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Product deleted successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error deleting product: " . $conn->error . "</div>";
    }
}

// Get product for editing if ID is provided
$editProduct = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editProduct = $result->fetch_assoc();
}
?>

<section class="modules-section">
    <div class="section-header">
        <h2 class="section-title">Products</h2>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="analytics-card">
            <div class="analytics-header">
                <h3 class="analytics-title"><?php echo $editProduct ? 'Edit' : 'Add'; ?> Product</h3>
            </div>
            <div class="analytics-content">
                <?php echo $message; ?>
                <form method="POST" action="products.php">
                    <input type="hidden" name="action" value="<?php echo $editProduct ? 'edit' : 'add'; ?>">
                    <?php if ($editProduct): ?>
                        <input type="hidden" name="id" value="<?php echo $editProduct['id']; ?>">
                    <?php endif; ?>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-text-primary mb-1">Product Name</label>
                            <input type="text" class="form-control w-full" id="name" name="name" value="<?php echo $editProduct ? htmlspecialchars($editProduct['name']) : ''; ?>" required>
                        </div>
                        <div>
                            <label for="stock" class="block text-sm font-medium text-text-primary mb-1">Initial Stock</label>
                            <input type="number" class="form-control w-full" id="stock" name="stock" value="<?php echo $editProduct ? $editProduct['stock'] : '0'; ?>" required>
                        </div>
                        <div>
                            <label for="unit" class="block text-sm font-medium text-text-primary mb-1">Unit</label>
                            <input type="text" class="form-control w-full" id="unit" name="unit" value="<?php echo $editProduct ? htmlspecialchars($editProduct['unit']) : ''; ?>" required>
                        </div>
                        <div>
                            <label for="purchase_price" class="block text-sm font-medium text-text-primary mb-1">Purchase Price</label>
                            <input type="number" class="form-control w-full" id="purchase_price" name="purchase_price" step="0.01" min="0" value="<?php echo $editProduct ? $editProduct['purchase_price'] : '0.00'; ?>" required>
                        </div>
                        <div class="relative">
                            <label for="category" class="block text-sm font-medium text-text-primary mb-1">Category</label>
                            <input type="text" class="form-control w-full" id="category" name="category" value="<?php echo $editProduct ? htmlspecialchars($editProduct['category']) : ''; ?>" placeholder="e.g., Pack" autocomplete="off">
                            <div id="category-suggestions" class="hidden absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto mt-1"></div>
                        </div>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <button type="submit" class="modern-btn"><?php echo $editProduct ? 'Update' : 'Add'; ?> Product</button>
                        <?php if ($editProduct): ?>
                            <a href="products.php" class="btn bg-gray-200 text-gray-700 hover:bg-gray-300">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        <div class="analytics-card">
            <div class="analytics-header">
                <h3 class="analytics-title">Product List</h3>
            </div>
            <div class="analytics-content">
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Current Stock</th>
                                <th>Unit</th>
                                <th>Purchase Price</th>
                                <th>Category</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM products ORDER BY name ASC";
                            $result = $conn->query($sql);
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td>" . getCurrentStock($row['id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['unit']) . "</td>";
                                echo "<td>NPR " . number_format($row['purchase_price'], 2) . "</td>";
                                echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                                echo "<td>
                                        <a href=\"products.php?id=" . $row['id'] . "\" class=\"modern-btn mr-2\">Edit</a>
                                        <a href=\"products.php?delete=" . $row['id'] . "\" class=\"btn bg-red-500 text-white btn-sm hover:bg-red-600\" onclick=\"return confirm('Are you sure you want to delete this product?')\">Delete</a>
                                      </td>";
                                echo "</tr>";
                            }
                            if ($result->num_rows === 0) {
                                echo "<tr><td colspan='5' class='text-center'>No products found</td></tr>";
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
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryInput = document.getElementById('category');
    const suggestionsDiv = document.getElementById('category-suggestions');
    let suggestions = [];
    let selectedIndex = -1;
    
    // Fetch category suggestions
    async function fetchSuggestions(searchTerm = '') {
        try {
            const response = await fetch(`../api/category-suggestions.php?q=${encodeURIComponent(searchTerm)}`);
            const data = await response.json();
            if (data.success) {
                suggestions = data.categories;
                displaySuggestions();
            }
        } catch (error) {
            console.error('Error fetching suggestions:', error);
        }
    }
    
    // Display suggestions
    function displaySuggestions() {
        if (suggestions.length === 0) {
            suggestionsDiv.classList.add('hidden');
            return;
        }
        
        suggestionsDiv.innerHTML = '';
        suggestions.forEach((category, index) => {
            const div = document.createElement('div');
            div.className = `px-4 py-2 cursor-pointer transition-colors duration-200 ${index === selectedIndex ? 'bg-blue-500 text-white' : 'hover:bg-blue-500 hover:text-white'}`;
            div.textContent = category;
            div.addEventListener('click', () => selectSuggestion(category));
            div.addEventListener('mouseenter', () => {
                selectedIndex = index;
                displaySuggestions();
            });
            suggestionsDiv.appendChild(div);
        });
        
        suggestionsDiv.classList.remove('hidden');
    }
    
    // Select a suggestion
    function selectSuggestion(category) {
        categoryInput.value = category;
        suggestionsDiv.classList.add('hidden');
        selectedIndex = -1;
    }
    
    // Handle input events
    categoryInput.addEventListener('input', function() {
        const value = this.value.trim();
        if (value.length >= 1) {
            fetchSuggestions(value);
        } else {
            suggestionsDiv.classList.add('hidden');
        }
        selectedIndex = -1;
    });
    
    // Handle keyboard navigation
    categoryInput.addEventListener('keydown', function(e) {
        if (suggestions.length === 0) return;
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, suggestions.length - 1);
                displaySuggestions();
                break;
            case 'ArrowUp':
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, -1);
                displaySuggestions();
                break;
            case 'Enter':
                e.preventDefault();
                if (selectedIndex >= 0 && suggestions[selectedIndex]) {
                    selectSuggestion(suggestions[selectedIndex]);
                }
                break;
            case 'Escape':
                suggestionsDiv.classList.add('hidden');
                selectedIndex = -1;
                break;
        }
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!categoryInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
            suggestionsDiv.classList.add('hidden');
            selectedIndex = -1;
        }
    });
    
    // Load initial suggestions when focusing on the input
    categoryInput.addEventListener('focus', function() {
        if (this.value.trim().length >= 1) {
            fetchSuggestions(this.value.trim());
        } else {
            fetchSuggestions(); // Load all categories
        }
    });
});
</script>

<?php
require_once '../includes/footer.php';
?> 