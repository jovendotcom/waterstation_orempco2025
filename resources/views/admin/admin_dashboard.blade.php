@extends('layout.admin')

@section('title', 'Admin Dashboard')

@section('content')


<!-- Summary Cards -->
<div class="row mt-5 d-flex align-items-stretch">
    <div class="col-md-4">
        <div class="card bg-info text-white mb-4 h-90">
            <div class="card-body">
                <div class="row">
                    <div class="col-9 text-right">
                        <div class="card-title">Daily Sales</div>
                        <div class="display-4"><b>₱{{ number_format($dailySales, 2) }}</b></div>
                    </div>
                    <div class="col-3 mt-4">
                        <i class="fas fa-peso-sign fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-success text-white mb-4 h-90">
            <div class="card-body">
                <div class="row">
                    <div class="col-9 text-right">
                        <div class="card-title">Monthly Sales</div>
                        <div class="display-4"><b>₱{{ number_format($monthlySales, 2) }}</b></div>
                    </div>
                    <div class="col-3 mt-4">
                        <i class="fas fa-chart-line fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-primary text-white mb-4 h-90">
            <div class="card-body">
                <div class="row">
                    <div class="col-9 text-right">
                        <div class="card-title">Total Transactions</div>
                        <div class="display-4"><b>{{ $totalTransactions }}</b></div>
                    </div>
                    <div class="col-3 mt-4">
                        <i class="fas fa-shopping-cart fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sales Graph -->
<div class="card mb-4">
    <div class="card-header">
        <h4 class="mt-3">Sales Graph (Daily)</h4>
    </div>
    <div class="card-body">
        <div class="chart-container" style="width: 100%; height: 500px; overflow: auto; padding: 10px;">
            <canvas id="salesGraph"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const salesData = @json($salesGraphData);

        const currentDate = new Date();
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        const labels = Array.from({ length: daysInMonth }, (_, i) => i + 1);
        const salesMap = new Map(salesData.map(item => [new Date(item.date).getDate(), item.total_sales]));
        
        const dataPoints = labels.map(day => salesMap.get(day) || 0);

        const ctx = document.getElementById('salesGraph').getContext('2d');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Sales (PHP)',
                    data: dataPoints,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: { enabled: true }
                },
                scales: {
                    x: {
                        title: { display: true, text: 'Date' },
                        ticks: { stepSize: 1 }
                    },
                    y: { title: { display: true, text: 'Total Sales (PHP)' } }
                }
            }
        });
    });
</script>
@endsection
