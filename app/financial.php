<?php include 'header.php'; ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid px-0 px-md-2">
    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Financial & Maintenance Expenditure</h1>
            <p class="text-muted small mb-0">Monthly facility maintenance cost tracking and budget overview.</p>
        </div>
        <div>
            <span class="badge bg-white border text-secondary px-3 py-2 rounded-pill small shadow-sm">
                <i class="bi bi-graph-up text-primary me-1"></i> Live Expenditure Analytics
            </span>
        </div>
    </div>

    <!-- Financial KPI Summary Cards -->
    <div class="row g-3 g-md-4 mb-4">
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary text-uppercase fw-semibold" style="font-size: 0.75rem;">Total Maintenance Cost</div>
                        <h3 class="fw-bold text-dark my-1" id="kpi-total-cost">RM 0.00</h3>
                        <div class="text-muted small">All completed repairs to date</div>
                    </div>
                    <div class="p-3 bg-success bg-opacity-10 text-success rounded-4">
                        <i class="bi bi-cash-coin fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary text-uppercase fw-semibold" style="font-size: 0.75rem;">Completed Work Orders</div>
                        <h3 class="fw-bold text-primary my-1" id="kpi-completed-orders">0</h3>
                        <div class="text-muted small">Total resolved equipment tickets</div>
                    </div>
                    <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-4">
                        <i class="bi bi-check2-all fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary text-uppercase fw-semibold" style="font-size: 0.75rem;">Average Cost / Repair</div>
                        <h3 class="fw-bold text-info my-1" id="kpi-avg-cost">RM 0.00</h3>
                        <div class="text-muted small">Per completed maintenance ticket</div>
                    </div>
                    <div class="p-3 bg-info bg-opacity-10 text-info rounded-4">
                        <i class="bi bi-calculator fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Line Chart Container -->
    <div class="card bg-white border border-gray-200 shadow-sm rounded-4 p-3 p-md-4 mb-4">
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 mb-4 pb-2 border-bottom">
            <div>
                <h5 class="fw-bold text-dark mb-1 d-flex align-items-center">
                    <i class="bi bi-activity text-primary me-2"></i>Maintenance Cost Over Time
                </h5>
                <p class="text-muted small mb-0">Monthly expenditure tracking based on completed facility repairs</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 small">
                    <i class="bi bi-calendar-range me-1"></i>Monthly Breakdown
                </span>
            </div>
        </div>

        <!-- Chart Canvas Container -->
        <div class="position-relative" style="min-height: 340px; height: 50vh; max-height: 480px;">
            <canvas id="financialChart"></canvas>
            <div id="chart-loading" class="position-absolute top-50 start-50 translate-middle text-center text-muted">
                <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                <div class="small">Aggregating maintenance data...</div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script type="module">
    import { db } from './js/config.js';
    import { collection, getDocs } from "https://www.gstatic.com/firebasejs/10.10.0/firebase-firestore.js";

    async function initFinancialReport() {
        const loadingEl = document.getElementById('chart-loading');
        let completedCount = 0;
        let totalCostSum = 0;

        const monthCostMap = {};
        const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

        // Pre-populate last 6 months so chart always displays continuous timeline
        const now = new Date();
        for (let i = 5; i >= 0; i--) {
            const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
            const key = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
            monthCostMap[key] = 0;
        }

        try {
            const snap = await getDocs(collection(db, "tickets"));
            snap.forEach(docSnap => {
                const t = docSnap.data();
                const isCompleted = t.status === "Completed" || t.status === "Resolved";
                if (isCompleted) {
                    completedCount++;
                    const costVal = parseFloat(t.cost) || 0;
                    totalCostSum += costVal;

                    let date = null;
                    if (t.completedAt && t.completedAt.toDate) {
                        date = t.completedAt.toDate();
                    } else if (t.createdAt && t.createdAt.toDate) {
                        date = t.createdAt.toDate();
                    } else if (t.reportDate) {
                        date = new Date(t.reportDate);
                    }

                    if (date && !isNaN(date.getTime())) {
                        const key = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
                        monthCostMap[key] = (monthCostMap[key] || 0) + costVal;
                    }
                }
            });

            // Update KPI Cards
            document.getElementById('kpi-total-cost').innerText = `RM ${totalCostSum.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            document.getElementById('kpi-completed-orders').innerText = completedCount;
            const avg = completedCount > 0 ? (totalCostSum / completedCount) : 0;
            document.getElementById('kpi-avg-cost').innerText = `RM ${avg.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

            // Prepare chart labels and data points
            const sortedKeys = Object.keys(monthCostMap).sort();
            const labels = sortedKeys.map(k => {
                const [year, month] = k.split('-');
                return `${monthNames[parseInt(month, 10) - 1]} ${year}`;
            });
            const dataValues = sortedKeys.map(k => monthCostMap[k]);

            // Render Chart.js
            if (loadingEl) loadingEl.classList.add('d-none');
            const ctx = document.getElementById('financialChart').getContext('2d');

            const gradient = ctx.createLinearGradient(0, 0, 0, 350);
            gradient.addColorStop(0, 'rgba(13, 110, 253, 0.25)');
            gradient.addColorStop(1, 'rgba(13, 110, 253, 0.00)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Maintenance Cost (RM)',
                        data: dataValues,
                        borderColor: '#0d6efd',
                        backgroundColor: gradient,
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#0d6efd',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointHoverBackgroundColor: '#0d6efd',
                        pointHoverBorderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#1a1c1e',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                title: function(context) {
                                    return context[0].label;
                                },
                                label: function(context) {
                                    const val = context.parsed.y || 0;
                                    return `Maintenance Cost: RM ${val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#6c757d',
                                font: {
                                    size: 12
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                color: '#6c757d',
                                font: {
                                    size: 12
                                },
                                callback: function(value) {
                                    return 'RM ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

        } catch (err) {
            console.error("Financial report load error:", err);
            if (loadingEl) {
                loadingEl.innerHTML = `<span class="text-danger">Failed to load chart data: ${err.message}</span>`;
            }
        }
    }

    initFinancialReport();
</script>