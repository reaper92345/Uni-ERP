<?php
require_once '../../includes/config.php';

// Access control
requireRole(['admin', 'user']);

$conn = getDBConnection();

// Handle exports early
if (isset($_GET['export'])) {
    $export = $_GET['export'];
    $q = $_GET['q'] ?? '';
    $status = $_GET['status'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';

    $where = [];
    $params = [];
    $types = '';
    if ($q !== '') { $where[] = "(si.invoice_no LIKE ? OR c.name LIKE ?)"; $params[] = "%$q%"; $params[] = "%$q%"; $types .= 'ss'; }
    if ($status !== '') { $where[] = "si.status = ?"; $params[] = $status; $types .= 's'; }
    if ($date_from !== '') { $where[] = "si.invoice_date >= ?"; $params[] = $date_from; $types .= 's'; }
    if ($date_to !== '') { $where[] = "si.invoice_date <= ?"; $params[] = $date_to; $types .= 's'; }
    $whereSql = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

    $sql = "SELECT si.*, c.name AS customer_name FROM sales_invoices si JOIN customers c ON si.customer_id=c.id $whereSql ORDER BY si.invoice_date DESC, si.id DESC";
    $stmt = $conn->prepare($sql);
    if ($types !== '') $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($export === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="invoices.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Invoice #','Date','Customer','Status','Subtotal','Tax','Total','Due Date']);
        while ($row = $result->fetch_assoc()) {
            fputcsv($out, [$row['invoice_no'],$row['invoice_date'],$row['customer_name'],$row['status'],$row['subtotal'],$row['tax'],$row['total'],$row['due_date']]);
        }
        fclose($out);
        exit;
    } elseif ($export === 'json') {
        header('Content-Type: application/json');
        $rows = [];
        while ($row = $result->fetch_assoc()) { $rows[] = $row; }
        echo json_encode($rows);
        exit;
    } elseif ($export === 'excel' || $export === 'pdf') {
        header('Content-Type: text/plain');
        echo 'Export to ' . strtoupper($export) . ' requires library setup (PhpSpreadsheet/Dompdf).';
        exit;
    }
}

// Create/Update/Delete
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create' || $action === 'update') {
        $invoice_no = sanitize($_POST['invoice_no']);
        $customer_id = (int)$_POST['customer_id'];
        $invoice_date = formatDate($_POST['invoice_date']);
        $due_date = $_POST['due_date'] ? formatDate($_POST['due_date']) : null;
        $status = sanitize($_POST['status']);
        $subtotal = (float)$_POST['subtotal'];
        $tax = (float)$_POST['tax'];
        $total = (float)$_POST['total'];
        $notes = sanitize($_POST['notes']);

        if ($action === 'create') {
            $sql = "INSERT INTO sales_invoices (customer_id, invoice_no, invoice_date, due_date, status, subtotal, tax, total, notes) VALUES (?,?,?,?,?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('isssssdds', $customer_id, $invoice_no, $invoice_date, $due_date, $status, $subtotal, $tax, $total, $notes);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Invoice created.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error creating invoice: " . htmlspecialchars($conn->error) . "</div>";
            }
        } else {
            $id = (int)$_POST['id'];
            $sql = "UPDATE sales_invoices SET customer_id=?, invoice_no=?, invoice_date=?, due_date=?, status=?, subtotal=?, tax=?, total=?, notes=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('isssssddsi', $customer_id, $invoice_no, $invoice_date, $due_date, $status, $subtotal, $tax, $total, $notes, $id);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Invoice updated.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error updating invoice: " . htmlspecialchars($conn->error) . "</div>";
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM sales_invoices WHERE id=?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Invoice deleted.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error deleting invoice: " . htmlspecialchars($conn->error) . "</div>";
        }
    }
}

// Fetch customers
$customers = [];
$res = $conn->query("SELECT id, name FROM customers ORDER BY name ASC");
while ($r = $res->fetch_assoc()) { $customers[] = $r; }

// Editing invoice
$editInvoice = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM sales_invoices WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $editInvoice = $stmt->get_result()->fetch_assoc();
}

require_once '../../includes/header.php';

$moduleTitle = 'Sales - Invoices';
$moduleDesc = 'Create, send, and track invoices';
$moduleIconClass = 'fa-solid fa-file-invoice';
?>

<div class="grid grid-cols-1 gap-6 mb-8">
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600">
                    <i class="<?php echo $moduleIconClass; ?>"></i>
                </div>
                <div>
                    <h5 class="text-lg font-semibold mb-0"><?php echo htmlspecialchars($moduleTitle); ?></h5>
                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($moduleDesc); ?></p>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php echo $message; ?>

            <form method="GET" action="sales_invoices.php" class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-4">
                <input type="text" name="q" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';?>" placeholder="Search invoice # or customer" class="form-control w-full" />
                <select name="status" class="form-select w-full">
                    <option value="">All Status</option>
                    <?php $statuses = ['draft','sent','paid','overdue','cancelled']; foreach ($statuses as $st): ?>
                        <option value="<?php echo $st; ?>" <?php echo (($_GET['status'] ?? '')===$st)?'selected':''; ?>><?php echo ucfirst($st); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="date" name="date_from" value="<?php echo htmlspecialchars($_GET['date_from'] ?? ''); ?>" class="form-control w-full" />
                <input type="date" name="date_to" value="<?php echo htmlspecialchars($_GET['date_to'] ?? ''); ?>" class="form-control w-full" />
                <div class="flex gap-2">
                    <button class="btn btn-primary" type="submit">Filter</button>
                    <a class="btn bg-gray-200 text-gray-700 hover:bg-gray-300" href="sales_invoices.php">Reset</a>
                </div>
                <div class="md:col-span-5 flex gap-2">
                    <a class="btn" href="?<?php echo http_build_query(array_merge($_GET,['export'=>'csv'])); ?>">Export CSV</a>
                    <a class="btn" href="?<?php echo http_build_query(array_merge($_GET,['export'=>'json'])); ?>">Export JSON</a>
                    <a class="btn" href="?<?php echo http_build_query(array_merge($_GET,['export'=>'excel'])); ?>">Export Excel</a>
                    <a class="btn" href="?<?php echo http_build_query(array_merge($_GET,['export'=>'pdf'])); ?>">Export PDF</a>
                </div>
            </form>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-1">
                    <h6 class="font-semibold mb-2"><?php echo $editInvoice ? 'Edit Invoice' : 'New Invoice'; ?></h6>
                    <form method="POST" action="sales_invoices.php" class="space-y-3">
                        <input type="hidden" name="action" value="<?php echo $editInvoice ? 'update' : 'create'; ?>">
                        <?php if ($editInvoice): ?>
                            <input type="hidden" name="id" value="<?php echo (int)$editInvoice['id']; ?>">
                        <?php endif; ?>
                        <div>
                            <label class="block text-sm mb-1">Invoice #</label>
                            <input class="form-control w-full" name="invoice_no" required value="<?php echo $editInvoice ? htmlspecialchars($editInvoice['invoice_no']) : ''; ?>" />
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Customer</label>
                            <select class="form-select w-full" name="customer_id" required>
                                <option value="">Select customer</option>
                                <?php foreach ($customers as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php echo ($editInvoice && (int)$editInvoice['customer_id']===(int)$c['id'])?'selected':''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-sm mb-1">Invoice Date</label>
                                <input type="date" class="form-control w-full" name="invoice_date" required value="<?php echo $editInvoice ? htmlspecialchars($editInvoice['invoice_date']) : ''; ?>" />
                            </div>
                            <div>
                                <label class="block text-sm mb-1">Due Date</label>
                                <input type="date" class="form-control w-full" name="due_date" value="<?php echo $editInvoice ? htmlspecialchars($editInvoice['due_date']) : ''; ?>" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Status</label>
                            <select class="form-select w-full" name="status">
                                <?php foreach ($statuses as $st): ?>
                                    <option value="<?php echo $st; ?>" <?php echo ($editInvoice && $editInvoice['status']===$st)?'selected':''; ?>><?php echo ucfirst($st); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-sm mb-1">Subtotal</label>
                                <input type="number" step="0.01" min="0" class="form-control w-full" name="subtotal" required value="<?php echo $editInvoice ? htmlspecialchars($editInvoice['subtotal']) : '0.00'; ?>" />
                            </div>
                            <div>
                                <label class="block text-sm mb-1">Tax</label>
                                <input type="number" step="0.01" min="0" class="form-control w-full" name="tax" required value="<?php echo $editInvoice ? htmlspecialchars($editInvoice['tax']) : '0.00'; ?>" />
                            </div>
                            <div>
                                <label class="block text-sm mb-1">Total</label>
                                <input type="number" step="0.01" min="0" class="form-control w-full" name="total" required value="<?php echo $editInvoice ? htmlspecialchars($editInvoice['total']) : '0.00'; ?>" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Notes</label>
                            <textarea class="form-control w-full" name="notes" rows="3"><?php echo $editInvoice ? htmlspecialchars($editInvoice['notes']) : ''; ?></textarea>
                        </div>
                        <div class="flex gap-2">
                            <button class="btn btn-primary" type="submit"><?php echo $editInvoice ? 'Update' : 'Create'; ?></button>
                            <?php if ($editInvoice): ?>
                                <a class="btn bg-gray-200 text-gray-700 hover:bg-gray-300" href="sales_invoices.php">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                <div class="md:col-span-2">
                    <h6 class="font-semibold mb-2">Invoices</h6>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th class="text-right">Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Filtering
                                $q = $_GET['q'] ?? '';
                                $status = $_GET['status'] ?? '';
                                $date_from = $_GET['date_from'] ?? '';
                                $date_to = $_GET['date_to'] ?? '';
                                $where = [];
                                $params = [];
                                $types = '';
                                if ($q !== '') { $where[] = "(si.invoice_no LIKE ? OR c.name LIKE ?)"; $params[] = "%$q%"; $params[] = "%$q%"; $types .= 'ss'; }
                                if ($status !== '') { $where[] = "si.status = ?"; $params[] = $status; $types .= 's'; }
                                if ($date_from !== '') { $where[] = "si.invoice_date >= ?"; $params[] = $date_from; $types .= 's'; }
                                if ($date_to !== '') { $where[] = "si.invoice_date <= ?"; $params[] = $date_to; $types .= 's'; }
                                $whereSql = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

                                $sql = "SELECT si.*, c.name AS customer_name FROM sales_invoices si JOIN customers c ON si.customer_id=c.id $whereSql ORDER BY si.invoice_date DESC, si.id DESC";
                                $stmt = $conn->prepare($sql);
                                if ($types !== '') { $stmt->bind_param($types, ...$params); }
                                $stmt->execute();
                                $result = $stmt->get_result();
                                while ($row = $result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['invoice_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['invoice_date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                    <td><?php echo ucfirst(htmlspecialchars($row['status'])); ?></td>
                                    <td class="text-right">NPR <?php echo number_format((float)$row['total'], 2); ?></td>
                                    <td class="whitespace-nowrap">
                                        <a class="btn btn-primary btn-sm" href="?id=<?php echo (int)$row['id']; ?>">Edit</a>
                                        <form method="POST" action="sales_invoices.php" style="display:inline" onsubmit="return confirm('Delete this invoice?')">
                                            <input type="hidden" name="action" value="delete" />
                                            <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>" />
                                            <button class="btn bg-red-500 text-white btn-sm hover:bg-red-600" type="submit">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once '../../includes/footer.php';
?>


