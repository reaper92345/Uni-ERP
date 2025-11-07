<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-content">
        <div class="hero-text">
            <h1 class="hero-title">ERP Launcher</h1>
            <p class="hero-subtitle">Switch between ERP modules. Use Google to continue.</p>
        </div>
        <div class="hero-actions">
            <?php if (!isLoggedIn()): ?>
                <a href="api/auth-google.php" class="google-signin-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 262" width="18" height="18">
                        <path fill="#4285F4" d="M255.9 133.5c0-10.7-.9-18.5-2.9-26.6H130.6v48.2h71.9c-1.5 12-9.6 30.1-27.6 42.3l-.3 1.9 40.1 31 2.8.3c25.7-23.7 38.4-58.6 38.4-97.1z"/>
                        <path fill="#34A853" d="M130.6 261.1c36.6 0 67.3-12 89.7-32.9l-42.7-33c-11.4 8-26.8 13.6-47 13.6-35.8 0-66.1-23.7-76.9-56.5l-1.6.1-41.8 32.4-.5 1.5c22.3 44.5 68 74.8 121.1 74.8z"/>
                        <path fill="#FBBC05" d="M53.7 152.3c-2.9-8.1-4.6-16.9-4.6-26s1.7-17.9 4.5-26l-.1-1.7L7.1 64.9l-1.3.6C-2 84.6-6.6 105.6-6.6 127.8c0 22.1 4.6 43.1 12.4 62.2l47.9-37.7z"/>
                        <path fill="#EB4335" d="M130.6 50.6c25.5 0 42.7 11 52.5 20.2l38.3-37.4C197.7 13.1 167.2.9 130.6.9 77.5.9 31.8 31.2 9.4 75.7l47.8 37.6c10.8-32.8 41.1-56.5 73.4-56.5z"/>
                    </svg>
                    <span>Sign in with Google</span>
                </a>
            <?php else: ?>
                <div class="user-info">
                    <?php if (!empty($_SESSION['user']['picture'])): ?>
                        <img src="<?php echo htmlspecialchars($_SESSION['user']['picture']); ?>" alt="avatar" class="user-avatar" />
                    <?php endif; ?>
                    <span class="user-name">
                        <?= htmlspecialchars(($_SESSION['user']['name'] ?? $_SESSION['user']['email'] ?? 'Guest')) ?>
                    </span>
                    <a href="/erp-auth/login.php" class="logout-btn">Logout</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Core ERP Modules Section -->
<section class="modules-section">
    <div class="section-header">
        <h2 class="section-title">Core ERP Modules</h2>
    </div>
    <div class="modules-grid">
        <?php
        $coreModules = [
            ['name' => 'Sales & Order Management', 'desc' => 'CRM light, quotations, invoices, receipts, order tracking', 'href' => 'pages/modules/sales_orders.php', 'icon' => 'fas fa-shopping-cart'],
            ['name' => 'Purchase & Supplier Mgmt', 'desc' => 'Vendors, POs, GRN, supplier payments', 'href' => 'pages/modules/purchase_supplier.php', 'icon' => 'fas fa-truck'],
            ['name' => 'Finance & Accounting', 'desc' => 'GL, AR/AP, bank reconciliation, VAT (Nepal)', 'href' => 'pages/modules/finance_accounting.php', 'icon' => 'fas fa-calculator'],
            ['name' => 'HR & Payroll', 'desc' => 'Employees, attendance, payroll & bonuses', 'href' => 'pages/modules/hr_payroll.php', 'icon' => 'fas fa-users'],
            ['name' => 'Production / MFG', 'desc' => 'BOM, work orders, planning, QC', 'href' => 'pages/modules/production.php', 'icon' => 'fas fa-industry'],
            ['name' => 'Reporting & Analytics', 'desc' => 'Inventory, sales vs purchase, dashboards, KPIs', 'href' => 'pages/modules/reporting_analytics.php', 'icon' => 'fas fa-chart-line'],
            ['name' => 'Inventory', 'desc' => 'Stock, items, movements', 'href' => 'pages/products.php', 'icon' => 'fas fa-boxes'],
            ['name' => 'Purchases', 'desc' => 'Procurement workflows', 'href' => 'pages/purchases.php', 'icon' => 'fas fa-shopping-bag'],
            ['name' => 'Sales', 'desc' => 'POS and sales entries', 'href' => 'pages/sales.php', 'icon' => 'fas fa-cash-register'],
        ];

        foreach ($coreModules as $module) {
            echo '<a href="' . htmlspecialchars($module['href']) . '" class="module-card core-module">';
            echo '<div class="module-icon">';
            echo '<i class="' . $module['icon'] . '"></i>';
            echo '</div>';
            echo '<div class="module-content">';
            echo '<h3 class="module-name">' . htmlspecialchars($module['name']) . '</h3>';
            echo '<p class="module-desc">' . htmlspecialchars($module['desc']) . '</p>';
            echo '</div>';
            echo '</a>';
        }
        ?>
    </div>
</section>

<!-- Optional Modules Section -->
<section class="modules-section">
    <div class="section-header">
        <h2 class="section-title">Optional Modules</h2>
    </div>
    <div class="modules-grid">
        <?php
        $optionalModules = [
            ['name' => 'CRM', 'desc' => 'Leads, follow-ups, history', 'href' => 'pages/modules/crm.php', 'icon' => 'fas fa-handshake'],
            ['name' => 'Project Management', 'desc' => 'Tasks and progress', 'href' => 'pages/modules/projects.php', 'icon' => 'fas fa-tasks'],
            ['name' => 'Document Management', 'desc' => 'Store invoices, contracts', 'href' => 'pages/modules/documents.php', 'icon' => 'fas fa-file-alt'],
            ['name' => 'Warehouse Management', 'desc' => 'Bins, locations, lot tracking', 'href' => 'pages/modules/wms.php', 'icon' => 'fas fa-warehouse'],
            ['name' => 'Logistics & Distribution', 'desc' => 'Deliveries and vehicles', 'href' => 'pages/modules/logistics.php', 'icon' => 'fas fa-shipping-fast'],
            ['name' => 'Compliance & Audit', 'desc' => 'Tax compliance, user logs', 'href' => 'pages/modules/compliance_audit.php', 'icon' => 'fas fa-shield-alt'],
        ];

        foreach ($optionalModules as $module) {
            echo '<a href="' . htmlspecialchars($module['href']) . '" class="module-card optional-module">';
            echo '<div class="module-icon">';
            echo '<i class="' . $module['icon'] . '"></i>';
            echo '</div>';
            echo '<div class="module-content">';
            echo '<h3 class="module-name">' . htmlspecialchars($module['name']) . '</h3>';
            echo '<p class="module-desc">' . htmlspecialchars($module['desc']) . '</p>';
            echo '</div>';
            echo '</a>';
        }
        ?>
    </div>
</section>

<!-- Quick Access Section -->
<section class="quick-access-section">
    <div class="quick-access-content">
        <h2 class="quick-access-title">Quick Access</h2>
        <div class="quick-access-grid">
            <a href="index.php" class="quick-access-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="pages/reports.php" class="quick-access-item">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
        </div>
    </div>
</section>

<style>
/* Landing Page Specific Styles */
.hero-section { background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-primary) 100%); padding: 4rem 0; margin-bottom: 3rem; border-radius: 0 0 2rem 2rem; position: relative; overflow: hidden; }
.hero-section::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: radial-gradient(circle at 30% 20%, rgba(59, 130, 246, 0.1) 0%, transparent 50%); pointer-events: none; }
.hero-content { max-width: 1200px; margin: 0 auto; padding: 0 2rem; display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 1; }
.hero-text { flex: 1; }
.hero-title { font-size: 3.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1rem; line-height: 1.1; }
.hero-subtitle { font-size: 1.25rem; color: var(--text-secondary); margin-bottom: 0; }
.hero-actions { display: flex; align-items: center; gap: 1rem; }
.google-signin-btn { display: inline-flex; align-items: center; gap: 0.75rem; background: white; color: #333; padding: 0.875rem 1.5rem; border-radius: 0.75rem; text-decoration: none; font-weight: 500; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); transition: all 0.3s ease; border: 1px solid #e5e7eb; }
.google-signin-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15); color: #333; text-decoration: none; }
.user-info { display: flex; align-items: center; gap: 0.75rem; background: var(--glass-bg); padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid var(--glass-border); }
.user-avatar { width: 2rem; height: 2rem; border-radius: 50%; }
.user-name { color: var(--text-primary); font-weight: 500; }
.logout-btn { color: var(--text-secondary); text-decoration: none; font-size: 0.875rem; transition: color 0.3s ease; }
.logout-btn:hover { color: var(--primary-color); }
.modules-section { margin-bottom: 4rem; }
.section-header { text-align: center; margin-bottom: 3rem; }
.section-title { font-size: 2.5rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.5rem; }
.modules-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem; max-width: 1200px; margin: 0 auto; padding: 0 2rem; }
.module-card { background: var(--glass-bg); backdrop-filter: blur(16px); border: 1px solid var(--glass-border); border-radius: 1rem; padding: 1.5rem; text-decoration: none; color: inherit; transition: all 0.3s ease; display: flex; align-items: flex-start; gap: 1rem; position: relative; overflow: hidden; }
.module-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #3b82f6, #8b5cf6); transform: scaleX(0); transition: transform 0.3s ease; }
.module-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15); border-color: var(--primary-color); }
.module-card:hover::before { transform: scaleX(1); }
.module-icon { width: 3rem; height: 3rem; border-radius: 0.75rem; background: linear-gradient(135deg, #3b82f6, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem; flex-shrink: 0; }
.optional-module .module-icon { background: linear-gradient(135deg, #10b981, #059669); }
.module-content { flex: 1; }
.module-name { font-size: 1.125rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.5rem; line-height: 1.3; }
.module-desc { font-size: 0.875rem; color: var(--text-secondary); line-height: 1.4; margin: 0; }
.quick-access-section { background: var(--glass-bg); backdrop-filter: blur(16px); border: 1px solid var(--glass-border); border-radius: 1.5rem; padding: 2rem; margin: 2rem auto; max-width: 1200px; }
.quick-access-content { text-align: center; }
.quick-access-title { font-size: 1.5rem; font-weight: 600; color: var(--text-primary); margin-bottom: 1.5rem; }
.quick-access-grid { display: flex; justify-content: center; gap: 2rem; flex-wrap: wrap; }
.quick-access-item { display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding: 1rem; border-radius: 0.75rem; text-decoration: none; color: var(--text-primary); transition: all 0.3s ease; min-width: 100px; }
.quick-access-item:hover { background: var(--primary-color); color: white; transform: translateY(-2px); }
.quick-access-item i { font-size: 1.5rem; }
.quick-access-item span { font-size: 0.875rem; font-weight: 500; }
@media (max-width: 768px) { .hero-content { flex-direction: column; text-align: center; gap: 2rem; } .hero-title { font-size: 2.5rem;} .hero-subtitle { font-size: 1.125rem;} .modules-grid { grid-template-columns: 1fr; padding: 0 1rem;} .quick-access-grid { gap: 1rem;} .quick-access-item { min-width: 80px; padding: 0.75rem;} }
@media (max-width: 480px) { .hero-section { padding: 2rem 0;} .hero-title { font-size: 2rem;} .section-title { font-size: 2rem;} .module-card { padding: 1rem;} .module-icon { width: 2.5rem; height: 2.5rem; font-size: 1rem;} }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll('.module-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });

    document.querySelectorAll('.module-card').forEach(card => {
        card.addEventListener('click', function() {
            const moduleName = this.querySelector('.module-name').textContent;
            console.log('Module clicked:', moduleName);
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && document.activeElement.classList.contains('module-card')) {
            document.activeElement.click();
        }
    });

    document.querySelectorAll('.module-card').forEach((card) => {
        card.setAttribute('tabindex', '0');
        card.setAttribute('role', 'button');
        card.setAttribute('aria-label', `Open ${card.querySelector('.module-name').textContent} module`);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
