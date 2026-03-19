<?php
$pageTitle = 'calc.motors.title';
$calcScripts = ['motors-data.js', 'motors-info.js'];
require_once 'header.php';
?>

<div class="calc-container">
    <div class="calc-header">
        <h1><i data-lucide="zap" class="w-7 h-7 inline text-gold"></i> <?= __('calc.motors.title') ?></h1>
    </div>

    <div class="calc-card-full">
        <h2><?= __('calc.motors.db_title') ?></h2>
        <p class="text-sm text-gray-500 mb-4"><?= __('calc.motors.db_desc') ?></p>
        <div style="overflow-x: auto;">
            <table class="calc-table" id="motors-table">
                <thead>
                    <tr>
                        <th data-sort="name"><?= __('calc.motors.col_motor') ?></th>
                        <th data-sort="totalWeight"><?= __('calc.motors.col_weight') ?> <span class="text-xs font-normal text-gray-400">(lb)</span></th>
                        <th data-sort="freeSpeed"><?= __('calc.motors.col_free_speed') ?> <span class="text-xs font-normal text-gray-400">(RPM)</span></th>
                        <th data-sort="stallTorque"><?= __('calc.motors.col_stall_torque') ?> <span class="text-xs font-normal text-gray-400">(N·m)</span></th>
                        <th data-sort="stallCurrent"><?= __('calc.motors.col_stall_i') ?> <span class="text-xs font-normal text-gray-400">(A)</span></th>
                        <th data-sort="freeCurrent"><?= __('calc.motors.col_free_i') ?> <span class="text-xs font-normal text-gray-400">(A)</span></th>
                        <th data-sort="power20">P@20A <span class="text-xs font-normal text-gray-400">(W)</span></th>
                        <th data-sort="power40">P@40A <span class="text-xs font-normal text-gray-400">(W)</span></th>
                        <th data-sort="power60">P@60A <span class="text-xs font-normal text-gray-400">(W)</span></th>
                        <th data-sort="powerToWeight">P/W@40A <span class="text-xs font-normal text-gray-400">(W/lb)</span></th>
                        <th data-sort="resistance">R <span class="text-xs font-normal text-gray-400">(&Omega;)</span></th>
                        <th data-sort="kT">kT <span class="text-xs font-normal text-gray-400">(N·m/A)</span></th>
                        <th data-sort="kV">kV <span class="text-xs font-normal text-gray-400">(RPM/V)</span></th>
                    </tr>
                </thead>
                <tbody id="motors-body">
                </tbody>
            </table>
        </div>
    </div>

    <!-- Motor Performance Chart -->
    <div class="calc-card-full">
        <h2><?= __('calc.motors.chart_title') ?></h2>
        <div class="input-row" style="margin-bottom: 1rem;">
            <label><?= __('calc.motors.compare') ?></label>
            <div class="input-group">
                <select id="motor-chart-select" class="motor-select" style="max-width:250px;"></select>
            </div>
        </div>
        <div class="chart-container" style="height:400px;">
            <canvas id="motor-curves-chart"></canvas>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
