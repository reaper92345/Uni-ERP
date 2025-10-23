<?php
require_once '../../includes/config.php';
require_once '../../includes/header.php';

$moduleTitle = 'General Ledger';
$moduleDesc = 'Chart of accounts, journal entries, and balances';
$moduleIconClass = 'fa-solid fa-book';
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
            <p class="text-gray-700">Template page for <?php echo htmlspecialchars($moduleTitle); ?>.</p>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>


