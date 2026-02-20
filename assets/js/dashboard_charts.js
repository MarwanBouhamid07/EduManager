/**
 * Financial Charts Handler for EduManager
 */
console.log("dashboard_charts.js: File loaded successfully.");

// 1. Revenue Trend Chart (Line)
window.initFinanceChart = function (labels, values) {
    const canvas = document.getElementById('financeChart');
    if (!canvas) return;

    try {
        const ctx = canvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Collected Revenue (MAD)',
                    data: values,
                    borderColor: '#2563eb',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#2563eb',
                    pointBorderColor: '#fff',
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function (context) {
                                return ' MAD ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)', drawBorder: false },
                        ticks: { callback: function (value) { return 'MAD ' + value.toLocaleString(); } }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    } catch (e) {
        console.error("Error initializing Finance Chart:", e);
    }
};

// 2. Student Payment Status Chart (Pie)
window.initPaymentStatusChart = function (labels, values) {
    const canvas = document.getElementById('paymentStatusChart');
    if (!canvas) return;

    try {
        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: ['#10b981', '#ef4444'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    } catch (e) {
        console.error("Error initializing Payment Status Chart:", e);
    }
};

// 3. Payment Method Chart (Bar)
window.initPaymentMethodChart = function (labels, values) {
    const canvas = document.getElementById('paymentMethodChart');
    if (!canvas) return;

    try {
        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Amount (MAD)',
                    data: values,
                    backgroundColor: '#3b82f6',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return 'MAD ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    } catch (e) {
        console.error("Error initializing Payment Method Chart:", e);
    }
};
