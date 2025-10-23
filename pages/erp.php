<?php
require_once '../includes/config.php';
require_once '../includes/header.php';
?>

<div class="grid grid-cols-1 gap-6 mb-8">
    <div class="card">
        <div class="card-header">
            <h5 class="text-lg font-semibold mb-0">ERP Launcher</h5>
        </div>
        <div class="card-body">
            <div class="mb-6 flex items-center justify-between gap-4 flex-wrap">
                <p class="text-sm text-gray-600">Switch between ERP modules. Use Google to continue.</p>
                <a href="../api/auth-google.php" class="inline-flex items-center gap-3 border border-gray-200 px-4 py-2 rounded-full hover:shadow-sm hover:border-gray-300 transition">
                    <span class="inline-flex items-center justify-center w-5 h-5">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 262" width="18" height="18">
                            <path fill="#4285F4" d="M255.9 133.5c0-10.7-.9-18.5-2.9-26.6H130.6v48.2h71.9c-1.5 12-9.6 30.1-27.6 42.3l-.3 1.9 40.1 31 2.8.3c25.7-23.7 38.4-58.6 38.4-97.1z"/>
                            <path fill="#34A853" d="M130.6 261.1c36.6 0 67.3-12 89.7-32.9l-42.7-33c-11.4 8-26.8 13.6-47 13.6-35.8 0-66.1-23.7-76.9-56.5l-1.6.1-41.8 32.4-.5 1.5c22.3 44.5 68 74.8 121.1 74.8z"/>
                            <path fill="#FBBC05" d="M53.7 152.3c-2.9-8.1-4.6-16.9-4.6-26s1.7-17.9 4.5-26l-.1-1.7L7.1 64.9l-1.3.6C-2 84.6-6.6 105.6-6.6 127.8c0 22.1 4.6 43.1 12.4 62.2l47.9-37.7z"/>
                            <path fill="#EB4335" d="M130.6 50.6c25.5 0 42.7 11 52.5 20.2l38.3-37.4C197.7 13.1 167.2.9 130.6.9 77.5.9 31.8 31.2 9.4 75.7l47.8 37.6c10.8-32.8 41.1-56.5 73.4-56.5z"/>
                        </svg>
                    </span>
                    <span class="text-sm font-medium">Sign in with Google</span>
                </a>
            </div>

            <h6 class="text-base font-semibold mb-3">Core ERP Modules</h6>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <?php
                $coreModules = [
                    ['name' => 'Sales & Order Management', 'desc' => 'CRM light, quotations, invoices, receipts, order tracking', 'href' => '/inventory/pages/modules/sales_orders.php'],
                    ['name' => 'Purchase & Supplier Mgmt', 'desc' => 'Vendors, POs, GRN, supplier payments', 'href' => '/inventory/pages/modules/purchase_supplier.php'],
                    ['name' => 'Finance & Accounting', 'desc' => 'GL, AR/AP, bank reconciliation, VAT (Nepal)', 'href' => '/inventory/pages/modules/finance_accounting.php'],
                    ['name' => 'HR & Payroll', 'desc' => 'Employees, attendance, payroll & bonuses', 'href' => '/inventory/pages/modules/hr_payroll.php'],
                    ['name' => 'Production / MFG', 'desc' => 'BOM, work orders, planning, QC', 'href' => '/inventory/pages/modules/production.php'],
                    ['name' => 'Reporting & Analytics', 'desc' => 'Inventory, sales vs purchase, dashboards, KPIs', 'href' => '/inventory/pages/modules/reporting_analytics.php'],
                    ['name' => 'Inventory', 'desc' => 'Stock, items, movements', 'href' => '/inventory/pages/products.php'],
                    ['name' => 'Purchases', 'desc' => 'Procurement workflows', 'href' => '/inventory/pages/purchases.php'],
                    ['name' => 'Sales', 'desc' => 'POS and sales entries', 'href' => '/inventory/pages/sales.php'],
                ];

                foreach ($coreModules as $m) {
                    echo '<a href="' . htmlspecialchars($m['href']) . '" class="block rounded-xl border border-gray-200 p-4 hover:shadow-sm hover:border-gray-300 transition">';
                    echo '<div class="flex items-start gap-3">';
                    echo '<div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-500">';
                    echo '<i class="fa-solid fa-circle-notch"></i>';
                    echo '</div>';
                    echo '<div class="flex-1">';
                    echo '<div class="font-medium">' . htmlspecialchars($m['name']) . '</div>';
                    echo '<div class="text-sm text-gray-600">' . htmlspecialchars($m['desc']) . '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '</a>';
                }
                ?>
            </div>

            <h6 class="text-base font-semibold mt-8 mb-3">Optional Modules</h6>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <?php
                $optionalModules = [
                    ['name' => 'CRM', 'desc' => 'Leads, follow-ups, history', 'href' => '/inventory/pages/modules/crm.php'],
                    ['name' => 'Project Management', 'desc' => 'Tasks and progress', 'href' => '/inventory/pages/modules/projects.php'],
                    ['name' => 'Document Management', 'desc' => 'Store invoices, contracts', 'href' => '/inventory/pages/modules/documents.php'],
                    ['name' => 'Warehouse Management', 'desc' => 'Bins, locations, lot tracking', 'href' => '/inventory/pages/modules/wms.php'],
                    ['name' => 'Logistics & Distribution', 'desc' => 'Deliveries and vehicles', 'href' => '/inventory/pages/modules/logistics.php'],
                    ['name' => 'Compliance & Audit', 'desc' => 'Tax compliance, user logs', 'href' => '/inventory/pages/modules/compliance_audit.php'],
                ];

                foreach ($optionalModules as $m) {
                    echo '<a href="' . htmlspecialchars($m['href']) . '" class="block rounded-xl border border-gray-200 p-4 hover:shadow-sm hover:border-gray-300 transition">';
                    echo '<div class="flex items-start gap-3">';
                    echo '<div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-500">';
                    echo '<i class="fa-regular fa-square"></i>';
                    echo '</div>';
                    echo '<div class="flex-1">';
                    echo '<div class="font-medium">' . htmlspecialchars($m['name']) . '</div>';
                    echo '<div class="text-sm text-gray-600">' . htmlspecialchars($m['desc']) . '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '</a>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>


