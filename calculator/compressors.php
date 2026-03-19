<?php
$pageTitle = 'Compressor Specifications';
$calcScripts = ['compressors-data.js', 'compressors-info.js'];
require_once 'header.php';
?>

<div class="calc-container">
    <div class="calc-header">
        <h1><i data-lucide="gauge" class="w-7 h-7 inline text-gold"></i> Compressor Specifications</h1>
    </div>

    <div class="calc-card-full">
        <h2>FRC Compressor Database</h2>
        <p class="text-sm text-gray-500 mb-4">Flow rates at various pressures. Click column headers to sort.</p>
        <div style="overflow-x: auto;">
            <table class="calc-table" id="comp-table">
                <thead>
                    <tr>
                        <th data-sort="name">Compressor</th>
                        <th data-sort="weightLb">Weight <span class="text-xs font-normal text-gray-400">(lb)</span></th>
                        <th data-sort="cfm0">CFM @ 0</th>
                        <th data-sort="cfm50">CFM @ 50</th>
                        <th data-sort="cfm100">CFM @ 100</th>
                        <th data-sort="cfmPerLb">CFM/lb @ 100</th>
                    </tr>
                </thead>
                <tbody id="comp-body">
                </tbody>
            </table>
        </div>
    </div>

    <!-- CFM vs Pressure Chart -->
    <div class="calc-card-full">
        <h2>CFM vs Pressure</h2>
        <div class="chart-container" style="height:400px;">
            <canvas id="comp-chart"></canvas>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
