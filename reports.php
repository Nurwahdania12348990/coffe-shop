<?php
$pageTitle = 'Laporan Penjualan';
$activePage = 'reports';
require_once __DIR__ . '/includes/header_admin.php';
requireLogin(['admin','cashier']);

$dateFrom = $_GET['date_from'] ?? date('Y-m-01');
$dateTo   = $_GET['date_to']   ?? date('Y-m-d');
?>
<meta name="app-url" content="<?= APP_URL ?>">

<!-- Filter -->
<div style="display:flex;gap:0.75rem;align-items:center;margin-bottom:1.25rem;flex-wrap:wrap">
    <label style="font-size:0.85rem;font-weight:600;color:var(--text-secondary)">Dari:</label>
    <input type="date" id="dateFrom" value="<?= $dateFrom ?>"
           style="padding:0.5rem 0.75rem;border:1.5px solid var(--border);border-radius:10px;font-family:inherit;font-size:0.85rem">
    <label style="font-size:0.85rem;font-weight:600;color:var(--text-secondary)">Sampai:</label>
    <input type="date" id="dateTo" value="<?= $dateTo ?>"
           style="padding:0.5rem 0.75rem;border:1.5px solid var(--border);border-radius:10px;font-family:inherit;font-size:0.85rem">
    <button onclick="loadReport()" class="btn-sm-custom btn-accent-sm">
        <i class="bi bi-search"></i> Tampilkan
    </button>
    <button onclick="window.print()" class="btn-sm-custom btn-outline-sm">
        <i class="bi bi-printer"></i> Print
    </button>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4" id="summaryCards">
    <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-icon coffee"><i class="bi bi-currency-dollar"></i></div><div><div class="stat-value" id="statRevenue" style="font-size:1rem">-</div><div class="stat-label">Total Pendapatan</div></div></div></div>
    <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-icon success"><i class="bi bi-receipt"></i></div><div><div class="stat-value" id="statOrders">-</div><div class="stat-label">Total Transaksi</div></div></div></div>
    <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-icon primary"><i class="bi bi-bag-fill"></i></div><div><div class="stat-value" id="statItems">-</div><div class="stat-label">Total Item Terjual</div></div></div></div>
    <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-icon info"><i class="bi bi-graph-up"></i></div><div><div class="stat-value" id="statAvg" style="font-size:1rem">-</div><div class="stat-label">Rata-rata per Transaksi</div></div></div></div>
</div>

<div class="row g-3 mb-4">
    <!-- Daily Chart -->
    <div class="col-md-8">
        <div class="card-custom">
            <div class="card-header-custom"><div class="card-title">Grafik Pendapatan Harian</div></div>
            <div style="padding:1rem"><div class="chart-container"><canvas id="dailyChart"></canvas></div></div>
        </div>
    </div>

    <!-- By Payment -->
    <div class="col-md-4">
        <div class="card-custom h-100">
            <div class="card-header-custom"><div class="card-title">Metode Pembayaran</div></div>
            <div style="padding:1rem"><div style="height:200px"><canvas id="paymentChart"></canvas></div></div>
            <div id="paymentList" style="padding:0 1rem 1rem"></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Top Menus -->
    <div class="col-md-6">
        <div class="card-custom">
            <div class="card-header-custom"><div class="card-title">Menu Terlaris</div></div>
            <div id="topMenusList" style="padding:0.5rem"></div>
        </div>
    </div>

    <!-- By Category -->
    <div class="col-md-6">
        <div class="card-custom">
            <div class="card-header-custom"><div class="card-title">Pendapatan per Kategori</div></div>
            <div style="padding:1rem"><div style="height:220px"><canvas id="categoryChart"></canvas></div></div>
        </div>
    </div>
</div>

<script>
const APP_URL = '<?= APP_URL ?>';
let dailyChartInst, paymentChartInst, categoryChartInst;

function fmt(n){ return 'Rp ' + new Intl.NumberFormat('id-ID').format(n); }

function loadReport(){
    const from = document.getElementById('dateFrom').value;
    const to   = document.getElementById('dateTo').value;

    fetch(`${APP_URL}/api/reports.php?action=summary&date_from=${from}&date_to=${to}`)
    .then(r=>r.json())
    .then(d=>{
        if(!d.success) return;

        document.getElementById('statRevenue').textContent = fmt(d.total_revenue);
        document.getElementById('statOrders').textContent  = d.total_orders;
        document.getElementById('statItems').textContent   = d.total_items;
        document.getElementById('statAvg').textContent     = fmt(d.avg_order);

        // Daily Chart
        const labels = d.daily.map(r => {
            const dt = new Date(r.date);
            return dt.toLocaleDateString('id-ID',{day:'numeric',month:'short'});
        });
        const revData = d.daily.map(r => parseFloat(r.revenue)||0);

        if(dailyChartInst) dailyChartInst.destroy();
        dailyChartInst = new Chart(document.getElementById('dailyChart'),{
            type:'line',
            data:{labels:labels.length?labels:['Tidak ada data'],datasets:[{label:'Pendapatan',data:revData.length?revData:[0],borderColor:'#C8813A',backgroundColor:'rgba(200,129,58,0.08)',borderWidth:2.5,pointBackgroundColor:'#C8813A',pointRadius:4,tension:0.4,fill:true}]},
            options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{callbacks:{label:ctx=>fmt(ctx.raw)}}},scales:{y:{beginAtZero:true,ticks:{callback:v=>'Rp '+(v/1000)+'K'},grid:{color:'#f0e8e0'}},x:{grid:{display:false}}}}
        });

        // Payment Chart
        const payLabels = d.by_payment.map(p=>({cash:'Tunai',transfer:'Transfer',qris:'QRIS'}[p.payment_method]||p.payment_method));
        const payData   = d.by_payment.map(p=>parseFloat(p.revenue)||0);

        if(paymentChartInst) paymentChartInst.destroy();
        paymentChartInst = new Chart(document.getElementById('paymentChart'),{
            type:'doughnut',
            data:{labels:payLabels,datasets:[{data:payData,backgroundColor:['#C8813A','#2980B9','#8e44ad'],borderWidth:0}]},
            options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{font:{size:11}}}}}
        });

        // Payment List
        document.getElementById('paymentList').innerHTML = d.by_payment.map(p=>`
            <div style="display:flex;justify-content:space-between;font-size:0.82rem;padding:0.3rem 0;border-bottom:1px dashed var(--border)">
                <span>${{cash:'💵 Tunai',transfer:'🏦 Transfer',qris:'📱 QRIS'}[p.payment_method]||p.payment_method}</span>
                <span class="fw-700">${fmt(p.revenue)} <span style="color:var(--text-muted)">(${p.count}x)</span></span>
            </div>
        `).join('');

        // Top Menus
        document.getElementById('topMenusList').innerHTML = d.top_menus.map((m,i)=>`
            <div style="display:flex;align-items:center;gap:0.75rem;padding:0.5rem 1rem;border-bottom:1px dashed var(--border)">
                <div style="width:24px;height:24px;background:${['#C8813A','#0d6efd','#198754','#6f42c1','#fd7e14','#dc3545','#20c997','#ffc107','#6c757d','#17a2b8'][i]};color:#fff;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:700;flex-shrink:0">${i+1}</div>
                <div style="flex:1;min-width:0"><div class="fw-600" style="font-size:0.82rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${m.menu_name}</div><div style="font-size:0.72rem;color:var(--text-muted)">${m.qty}x terjual</div></div>
                <div class="fw-700 text-accent" style="font-size:0.82rem;white-space:nowrap">${fmt(m.revenue)}</div>
            </div>
        `).join('') || '<div style="text-align:center;padding:2rem;color:var(--text-muted)">Tidak ada data</div>';

        // Category Chart
        const catLabels = d.by_category.map(c=>c.name);
        const catData   = d.by_category.map(c=>parseFloat(c.revenue)||0);
        const catColors = ['#C8813A','#0d6efd','#198754','#6f42c1','#fd7e14','#dc3545'];

        if(categoryChartInst) categoryChartInst.destroy();
        categoryChartInst = new Chart(document.getElementById('categoryChart'),{
            type:'bar',
            data:{labels:catLabels,datasets:[{data:catData,backgroundColor:catColors.slice(0,catLabels.length),borderRadius:8,borderSkipped:false}]},
            options:{responsive:true,maintainAspectRatio:false,indexAxis:'y',plugins:{legend:{display:false},tooltip:{callbacks:{label:ctx=>fmt(ctx.raw)}}},scales:{x:{beginAtZero:true,ticks:{callback:v=>'Rp '+(v/1000)+'K'},grid:{color:'#f0e8e0'}},y:{grid:{display:false}}}}
        });
    });
}

// Auto-load on page open
loadReport();
</script>

<?php require_once __DIR__ . '/includes/footer_admin.php'; ?>
