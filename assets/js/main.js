// Initialize charts when the document is ready
document.addEventListener('DOMContentLoaded', function () {
    // Initialize sales vs expenses chart if we're on the dashboard
    const salesExpensesChart = document.getElementById('salesExpensesChart');
    if (salesExpensesChart) {
        console.log('Initializing sales vs expenses chart...');
        initializeSalesExpensesChart();
    }
});

// Function to initialize the sales vs expenses chart
function initializeSalesExpensesChart() {
    console.log('Starting chart initialization...');

    // Use relative path instead of absolute
    fetch('charts/sales_vs_expenses.php')
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Chart data received:', data);

            // Check if canvas exists
            const canvas = document.getElementById('salesExpensesChart');
            if (!canvas) {
                throw new Error('Canvas element not found');
            }
            console.log('Canvas found:', canvas);

            const ctx = canvas.getContext('2d');
            if (!ctx) {
                throw new Error('Could not get canvas context');
            }
            console.log('Canvas context obtained');

            // Create chart container if it doesn't exist
            let chartContainer = document.querySelector('.chart-container');
            if (!chartContainer) {
                console.log('Creating chart container');
                chartContainer = document.createElement('div');
                chartContainer.className = 'chart-container';
                canvas.parentNode.appendChild(chartContainer);
            }

            // Create toggle button if it doesn't exist
            let toggleButton = document.getElementById('chartToggle');
            if (!toggleButton) {
                console.log('Creating toggle button');
                toggleButton = document.createElement('button');
                toggleButton.id = 'chartToggle';
                toggleButton.className = 'btn btn-primary mb-3';
                toggleButton.textContent = 'Switch to Quantity View';
                chartContainer.insertBefore(toggleButton, chartContainer.firstChild);
            }

            // Destroy existing chart if it exists
            if (window.salesExpensesChartInstance) {
                console.log('Destroying existing chart instance');
                window.salesExpensesChartInstance.destroy();
            }

            // Helper to create gradient
            function createGradient(ctx, color) {
                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, color.replace(')', ', 0.4)').replace('rgb', 'rgba'));
                gradient.addColorStop(1, color.replace(')', ', 0.0)').replace('rgb', 'rgba'));
                return gradient;
            }

            // Initialize chart with monetary data
            let currentView = 'monetary';
            console.log('Creating new chart instance with monetary data');

            // Define colors
            const salesColor = 'rgb(59, 130, 246)'; // Blue
            const expensesColor = 'rgb(239, 68, 68)'; // Red

            // Prepare datasets with Ubiquiti styling
            const styleDataset = (dataset, color) => ({
                ...dataset,
                borderColor: color,
                backgroundColor: createGradient(ctx, color),
                borderWidth: 2,
                tension: 0.4, // Smooth curves
                fill: true,
                pointRadius: 0,
                pointHoverRadius: 6,
                pointBackgroundColor: color,
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            });

            // Apply styling to initial data
            const styledMonetaryDatasets = data.monetaryData.datasets.map((ds, i) =>
                styleDataset(ds, i === 0 ? salesColor : expensesColor)
            );

            window.salesExpensesChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: styledMonetaryDatasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(200, 200, 200, 0.1)',
                                drawBorder: false,
                            },
                            ticks: {
                                color: '#6c757d',
                                callback: function (value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false,
                            },
                            ticks: {
                                color: '#6c757d'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                padding: 20,
                                color: '#6c757d'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            titleColor: '#1f2937',
                            bodyColor: '#4b5563',
                            borderColor: '#e5e7eb',
                            borderWidth: 1,
                            padding: 12,
                            boxPadding: 6,
                            usePointStyle: true,
                            callbacks: {
                                label: function (context) {
                                    return context.dataset.label + ': $' +
                                        context.raw.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Add toggle functionality
            toggleButton.onclick = function () {
                console.log('Toggle button clicked, current view:', currentView);
                if (currentView === 'monetary') {
                    console.log('Switching to quantity view');

                    // Style quantity datasets
                    const styledQuantityDatasets = data.quantityData.datasets.map((ds, i) =>
                        styleDataset(ds, i === 0 ? salesColor : expensesColor)
                    );

                    window.salesExpensesChartInstance.data.datasets = styledQuantityDatasets;
                    window.salesExpensesChartInstance.options.scales.y.ticks.callback = function (value) {
                        return value.toLocaleString();
                    };
                    window.salesExpensesChartInstance.options.plugins.tooltip.callbacks.label = function (context) {
                        return context.dataset.label + ': ' + context.raw.toLocaleString();
                    };
                    toggleButton.textContent = 'Switch to Monetary View';
                    currentView = 'quantity';
                } else {
                    console.log('Switching to monetary view');
                    window.salesExpensesChartInstance.data.datasets = styledMonetaryDatasets;
                    window.salesExpensesChartInstance.options.scales.y.ticks.callback = function (value) {
                        return '$' + value.toLocaleString();
                    };
                    window.salesExpensesChartInstance.options.plugins.tooltip.callbacks.label = function (context) {
                        return context.dataset.label + ': $' + context.raw.toLocaleString();
                    };
                    toggleButton.textContent = 'Switch to Quantity View';
                    currentView = 'monetary';
                }
                window.salesExpensesChartInstance.update();
            };

            console.log('Chart initialized successfully');
        })
        .catch(error => {
            console.error('Error in chart initialization:', error);
            // Show error message on the chart canvas
            const canvas = document.getElementById('salesExpensesChart');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.font = '14px Arial';
                ctx.fillStyle = 'red';
                ctx.textAlign = 'center';
                ctx.fillText('Error loading chart data: ' + error.message, canvas.width / 2, canvas.height / 2);
            } else {
                console.error('Canvas element not found for error display');
            }
        });
}

// Function to delete a record
function deleteRecord(url, id, redirectUrl) {
    if (confirm('Are you sure you want to delete this record?')) {
        fetch(url + '?id=' + id, {
            method: 'DELETE'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = redirectUrl;
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
    }
}

// Function to format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Function to update product stock
function updateStock(productId) {
    const quantity = document.getElementById('quantity_' + productId).value;
    fetch('/inventory/pages/update_stock.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId + '&quantity=' + quantity
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('current_stock_' + productId).textContent = data.new_stock;
                alert('Stock updated successfully!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

// Function to load expense categories
function loadExpenseCategories() {
    fetch('/inventory/pages/get_expense_categories.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('category_id');
            select.innerHTML = '<option value="">Select Category</option>';
            data.forEach(category => {
                select.innerHTML += `<option value="${category.id}">${category.name}</option>`;
            });
        })
        .catch(error => console.error('Error loading categories:', error));
}