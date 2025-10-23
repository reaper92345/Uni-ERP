<?php
require_once '../../includes/config.php';
require_once '../../includes/header.php';

$moduleTitle = 'Finance & Accounting';
$moduleDesc = 'General ledger, AR/AP, bank reconciliation, VAT';
$moduleIconClass = 'fa-solid fa-file-invoice-dollar';
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
            <div class="mb-4 flex flex-wrap gap-2">
                <a href="/inventory/pages/modules/gl.php" class="px-3 py-1.5 rounded-full border border-gray-200 hover:border-gray-300 text-sm">General Ledger</a>
                <a href="/inventory/pages/modules/ar_ap.php" class="px-3 py-1.5 rounded-full border border-gray-200 hover:border-gray-300 text-sm">AR/AP</a>
                <a href="/inventory/pages/modules/bank_reconciliation.php" class="px-3 py-1.5 rounded-full border border-gray-200 hover:border-gray-300 text-sm">Bank Reconciliation</a>
                <a href="/inventory/pages/modules/tax_vat.php" class="px-3 py-1.5 rounded-full border border-gray-200 hover:border-gray-300 text-sm">Tax / VAT</a>
            </div>
            <p class="text-gray-700">This is a template page for <?php echo htmlspecialchars($moduleTitle); ?>. Customize layout, forms, and reports as needed.</p>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>


