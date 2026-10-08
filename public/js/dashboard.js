/**
 * Horticulture CRM - Dashboard Charts & Realtime Analytics
 */

let charts = {};

document.addEventListener('DOMContentLoaded', () => {
    initDashboard();

    const refreshBtn = document.getElementById('refresh-stats-btn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => {
            refreshDashboardStats();
        });
    }
});

async function initDashboard() {
    try {
        const response = await ajaxRequest('/dashboard/stats');
        if (response.success && response.data) {
            renderDashboardCharts(response.data.charts);
        }
    } catch (err) {
        console.error('Failed to load dashboard data:', err);
    }
}

async function refreshDashboardStats() {
    const refreshBtn = document.getElementById('refresh-stats-btn');
    const originalText = refreshBtn ? refreshBtn.innerHTML : '';
    if (refreshBtn) {
        refreshBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Refreshing...';
        refreshBtn.disabled = true;
    }

    try {
        const response = await ajaxRequest('/dashboard/stats');
        if (response.success && response.data) {
            updateKpiCards(response.data.metrics);
            updateCharts(response.data.charts);
            renderTodo(response.data.todo);
            showToast('Dashboard metrics refreshed successfully', 'success');
        }
    } catch (err) {
        showToast('Error refreshing metrics', 'danger');
    } finally {
        if (refreshBtn) {
            refreshBtn.innerHTML = originalText;
            refreshBtn.disabled = false;
        }
    }
}

/**
 * Rebuild the "Needs Your Attention" strip from /dashboard/stats.
 * Uses textContent-based escaping so server data can never inject markup.
 */
function renderTodo(items) {
    const list = document.getElementById('todo-list');
    const countBadge = document.getElementById('todo-count');
    if (!list) return;

    const safe = (value) => {
        const div = document.createElement('div');
        div.textContent = value === null || value === undefined ? '' : String(value);
        return div.innerHTML;
    };

    const todo = Array.isArray(items) ? items : [];

    if (countBadge) countBadge.innerText = String(todo.length);

    if (!todo.length) {
        list.innerHTML =
            '<div class="col-12"><div class="todo-empty">' +
            '<i class="bi bi-check2-circle"></i>' +
            '<div class="fw-semibold text-dark">Nothing needs attention</div>' +
            '<small>Overdue invoices, due visits and tasks will appear here automatically.</small>' +
            '</div></div>';
        return;
    }

    list.innerHTML = todo.map((item) =>
        '<div class="col-12 col-md-6 col-xl-4">' +
        '<a href="' + safe(item.url) + '" class="todo-item severity-' + safe(item.severity) + '">' +
        '<span class="todo-icon sev-' + safe(item.severity) + '"><i class="bi ' + safe(item.icon) + '"></i></span>' +
        '<span class="todo-body">' +
        '<span class="todo-title">' + safe(item.title) + '</span>' +
        '<span class="todo-meta">' + safe(item.meta) + '</span>' +
        '<span class="todo-label sev-text-' + safe(item.severity) + '">' + safe(item.label) + '</span>' +
        '</span>' +
        '<i class="bi bi-arrow-right todo-go"></i>' +
        '</a></div>'
    ).join('');
}

function updateKpiCards(metrics) {
    if (!metrics) return;
    for (const [key, val] of Object.entries(metrics)) {
        const el = document.getElementById(`kpi-${key}`);
        if (el) {
            if (key.includes('amount') || key.includes('revenue') || key.includes('value')) {
                el.innerText = formatCurrency(val);
            } else {
                el.innerText = formatNumber(val);
            }
        }
    }
}

function renderDashboardCharts(chartData) {
    if (!chartData || typeof Chart === 'undefined') return;

    // Palette (matches :root tokens in css/app-custom.css)
    const colors = {
        primary: '#16a34a',
        forest: '#166534',
        accent: '#0ea5e9',
        coral: '#fb923c',
        blue: '#2563eb',
        cyan: '#06b6d4',
        purple: '#8b5cf6',
        gray: '#94a3b8',
        lightGreen: '#86efac',
        yellow: '#f59e0b',
        danger: '#ef4444',
    };

    // 1. Leads by Source (Doughnut)
    const ctxSource = document.getElementById('chartLeadsSource');
    if (ctxSource && chartData.leads_by_source) {
        charts.source = new Chart(ctxSource, {
            type: 'doughnut',
            data: {
                labels: chartData.leads_by_source.labels,
                datasets: [{
                    data: chartData.leads_by_source.data,
                    backgroundColor: [colors.primary, colors.blue, colors.accent, colors.purple, colors.coral, colors.cyan, colors.gray],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15 } }
                }
            }
        });
    }

    // 2. Leads by Status (Bar)
    const ctxStatus = document.getElementById('chartLeadsStatus');
    if (ctxStatus && chartData.leads_by_status) {
        charts.status = new Chart(ctxStatus, {
            type: 'bar',
            data: {
                labels: chartData.leads_by_status.labels,
                datasets: [{
                    label: 'Leads',
                    data: chartData.leads_by_status.data,
                    backgroundColor: colors.primary,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f0f3f0' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // 3. Sales Pipeline (Horizontal Bar)
    const ctxPipeline = document.getElementById('chartSalesPipeline');
    if (ctxPipeline && chartData.sales_pipeline) {
        charts.pipeline = new Chart(ctxPipeline, {
            type: 'bar',
            data: {
                labels: chartData.sales_pipeline.labels,
                datasets: [{
                    label: 'Deals / Count',
                    data: chartData.sales_pipeline.data,
                    backgroundColor: [
                        '#16a34a', '#22c55e', '#0ea5e9', '#2563eb', '#8b5cf6', '#166534'
                    ],
                    borderRadius: 6
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { color: '#f0f3f0' } },
                    y: { grid: { display: false } }
                }
            }
        });
    }

    // 4. Monthly Quotation Value (Line)
    const ctxMonthlyQuote = document.getElementById('chartMonthlyQuotation');
    if (ctxMonthlyQuote && chartData.monthly_quotations) {
        charts.monthlyQuote = new Chart(ctxMonthlyQuote, {
            type: 'line',
            data: {
                labels: chartData.monthly_quotations.labels,
                datasets: [{
                    label: 'Quotation Value (₹)',
                    data: chartData.monthly_quotations.data,
                    borderColor: colors.accent,
                    backgroundColor: 'rgba(14, 165, 233, 0.15)', // accent (#0ea5e9) @ 15% — keep in sync with colors.accent
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointBackgroundColor: colors.accent
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => '₹' + (value / 1000) + 'k'
                        },
                        grid: { color: '#f0f3f0' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // 5. Monthly Revenue vs Expenses (Grouped Bar)
    const ctxRevenue = document.getElementById('chartMonthlyRevenue');
    if (ctxRevenue && chartData.monthly_revenue) {
        charts.revenue = new Chart(ctxRevenue, {
            type: 'bar',
            data: {
                labels: chartData.monthly_revenue.labels,
                datasets: [
                    {
                        label: 'Revenue (₹)',
                        data: chartData.monthly_revenue.revenue,
                        backgroundColor: colors.primary,
                        borderRadius: 5
                    },
                    {
                        label: 'Expenses (₹)',
                        data: chartData.monthly_revenue.expenses,
                        backgroundColor: colors.coral,
                        borderRadius: 5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => '₹' + (value / 1000) + 'k'
                        },
                        grid: { color: '#f0f3f0' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // 6. Project Status (Doughnut)
    const ctxProjectStatus = document.getElementById('chartProjectStatus');
    if (ctxProjectStatus && chartData.project_status) {
        charts.projectStatus = new Chart(ctxProjectStatus, {
            type: 'doughnut',
            data: {
                labels: chartData.project_status.labels,
                datasets: [{
                    data: chartData.project_status.data,
                    backgroundColor: [colors.gray, colors.blue, colors.yellow, colors.danger, colors.primary],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12 } }
                }
            }
        });
    }

    // 7. AMC Status (Pie)
    const ctxAmc = document.getElementById('chartAmcStatus');
    if (ctxAmc && chartData.amc_status) {
        charts.amc = new Chart(ctxAmc, {
            type: 'pie',
            data: {
                labels: chartData.amc_status.labels,
                datasets: [{
                    data: chartData.amc_status.data,
                    backgroundColor: [colors.primary, colors.yellow, colors.accent, colors.gray],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12 } }
                }
            }
        });
    }

    // 8. Pending Payments Aging (Bar)
    const ctxAging = document.getElementById('chartPendingAging');
    if (ctxAging && chartData.pending_payments) {
        charts.aging = new Chart(ctxAging, {
            type: 'bar',
            data: {
                labels: chartData.pending_payments.labels,
                datasets: [{
                    label: 'Outstanding (₹)',
                    data: chartData.pending_payments.data,
                    backgroundColor: [colors.lightGreen, colors.yellow, colors.accent, colors.danger],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => '₹' + (value / 1000) + 'k'
                        },
                        grid: { color: '#f0f3f0' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }
}

function updateCharts(chartData) {
    if (!chartData) return;

    if (charts.source && chartData.leads_by_source) {
        charts.source.data.labels = chartData.leads_by_source.labels;
        charts.source.data.datasets[0].data = chartData.leads_by_source.data;
        charts.source.update();
    }

    if (charts.status && chartData.leads_by_status) {
        charts.status.data.labels = chartData.leads_by_status.labels;
        charts.status.data.datasets[0].data = chartData.leads_by_status.data;
        charts.status.update();
    }

    if (charts.pipeline && chartData.sales_pipeline) {
        charts.pipeline.data.labels = chartData.sales_pipeline.labels;
        charts.pipeline.data.datasets[0].data = chartData.sales_pipeline.data;
        charts.pipeline.update();
    }

    if (charts.monthlyQuote && chartData.monthly_quotations) {
        charts.monthlyQuote.data.labels = chartData.monthly_quotations.labels;
        charts.monthlyQuote.data.datasets[0].data = chartData.monthly_quotations.data;
        charts.monthlyQuote.update();
    }

    if (charts.revenue && chartData.monthly_revenue) {
        charts.revenue.data.labels = chartData.monthly_revenue.labels;
        charts.revenue.data.datasets[0].data = chartData.monthly_revenue.revenue;
        charts.revenue.data.datasets[1].data = chartData.monthly_revenue.expenses;
        charts.revenue.update();
    }

    if (charts.projectStatus && chartData.project_status) {
        charts.projectStatus.data.labels = chartData.project_status.labels;
        charts.projectStatus.data.datasets[0].data = chartData.project_status.data;
        charts.projectStatus.update();
    }

    if (charts.amc && chartData.amc_status) {
        charts.amc.data.labels = chartData.amc_status.labels;
        charts.amc.data.datasets[0].data = chartData.amc_status.data;
        charts.amc.update();
    }

    if (charts.aging && chartData.pending_payments) {
        charts.aging.data.labels = chartData.pending_payments.labels;
        charts.aging.data.datasets[0].data = chartData.pending_payments.data;
        charts.aging.update();
    }
}
