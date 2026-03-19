<?php
$pageTitle = 'calc.drive.title';
$calcScripts = ['motors-data.js', 'drive.js'];
require_once 'header.php';
?>

<div class="calc-container">
    <div class="calc-header">
        <h1><i data-lucide="truck" class="w-7 h-7 inline text-gold"></i> <?= __('calc.drive.title') ?></h1>
        <button id="share-btn" class="share-btn"><i data-lucide="share-2" class="w-4 h-4"></i> <?= __('calc.share') ?></button>
    </div>

    <div class="calc-grid">
        <!-- Inputs -->
        <div class="calc-card">
            <h2><?= __('calc.drive.motor_gearing') ?></h2>
            <div class="input-row">
                <label><?= __('calc.drive.motor') ?></label>
                <div class="input-group">
                    <select data-param="motor" class="motor-select"></select>
                </div>
            </div>
            <div class="input-row">
                <label><?= __('calc.drive.motors_per_side') ?></label>
                <div class="input-group">
                    <input type="number" data-param="motorsPerSide" value="2" min="1" max="4" step="1">
                </div>
            </div>
            <div class="input-row">
                <label><?= __('calc.drive.gear_ratio') ?></label>
                <div class="input-group">
                    <input type="number" data-param="ratio" value="5.36" min="0.1" step="0.01">
                    <span class="unit">:1</span>
                </div>
            </div>
            <div class="input-row">
                <label><?= __('calc.drive.efficiency') ?></label>
                <div class="input-group">
                    <input type="number" data-param="eff" value="97" min="1" max="100" step="1">
                    <span class="unit">%</span>
                </div>
            </div>
            <div class="input-row">
                <label><?= __('calc.drive.current_limit') ?></label>
                <div class="input-group">
                    <input type="number" data-param="currentLimit" value="60" min="1" step="1">
                    <span class="unit">A</span>
                </div>
            </div>

            <hr class="calc-divider">
            <h2><?= __('calc.drive.robot') ?></h2>
            <div class="input-row">
                <label><?= __('calc.drive.robot_weight') ?></label>
                <div class="input-group">
                    <input type="number" data-param="robotWeight" value="125" min="1" step="1">
                    <span class="unit">lb</span>
                </div>
            </div>
            <div class="input-row">
                <label><?= __('calc.drive.bumper_weight') ?></label>
                <div class="input-group">
                    <input type="number" data-param="bumperWeight" value="15" min="0" step="1">
                    <span class="unit">lb</span>
                </div>
            </div>
            <div class="input-row">
                <label><?= __('calc.drive.wheel_diameter') ?></label>
                <div class="input-group">
                    <input type="number" data-param="wheelD" value="4" min="0.5" step="0.5">
                    <span class="unit">in</span>
                </div>
            </div>
            <div class="input-row">
                <label><?= __('calc.drive.cof') ?></label>
                <div class="input-group">
                    <input type="number" data-param="cof" value="1.1" min="0.1" step="0.05">
                </div>
            </div>

            <hr class="calc-divider">
            <h2><?= __('calc.drive.sprint') ?></h2>
            <div class="input-row">
                <label><?= __('calc.drive.sprint_distance') ?></label>
                <div class="input-group">
                    <input type="number" data-param="sprintDist" value="25" min="1" step="1">
                    <span class="unit">ft</span>
                </div>
            </div>

            <hr class="calc-divider">
            <h2><?= __('calc.drive.battery') ?></h2>
            <div class="input-row">
                <label><?= __('calc.drive.voltage') ?></label>
                <div class="input-group">
                    <input type="number" data-param="voltage" value="12.6" min="10" max="13" step="0.1">
                    <span class="unit">V</span>
                </div>
            </div>
            <div class="input-row">
                <label><?= __('calc.drive.internal_resistance') ?></label>
                <div class="input-group">
                    <input type="number" data-param="battR" value="0.018" min="0.001" step="0.001">
                    <span class="unit">&Omega;</span>
                </div>
            </div>
        </div>

        <!-- Outputs -->
        <div class="calc-card">
            <h2><?= __('calc.results') ?></h2>

            <div class="result-highlight">
                <div class="result-label"><?= __('calc.drive.max_speed') ?></div>
                <div class="result-number" id="out-max-speed">—</div>
                <span class="text-sm text-gray-500">ft/s</span>
            </div>

            <div class="mt-4">
                <div class="output-row">
                    <label><?= __('calc.drive.max_mph') ?></label>
                    <div class="input-group">
                        <span class="output-value" id="out-max-mph">—</span>
                        <span class="unit">mph</span>
                    </div>
                </div>
                <div class="output-row">
                    <label><?= __('calc.drive.adj_speed') ?></label>
                    <div class="input-group">
                        <span class="output-value" id="out-adj-speed">—</span>
                        <span class="unit">ft/s</span>
                    </div>
                </div>
            </div>

            <hr class="calc-divider">
            <div class="result-highlight" style="border-color: #3B82F6;">
                <div class="result-label"><?= __('calc.drive.sprint_time') ?></div>
                <div class="result-number" id="out-sprint-time" style="color: #3B82F6;">—</div>
                <span class="text-sm text-gray-500"><?= __('calc.drive.seconds') ?></span>
            </div>

            <div class="mt-4">
                <div class="output-row">
                    <label><?= __('calc.drive.push_force') ?></label>
                    <div class="input-group">
                        <span class="output-value" id="out-push-force">—</span>
                        <span class="unit">lbf</span>
                    </div>
                </div>
                <div class="output-row">
                    <label><?= __('calc.drive.traction_force') ?></label>
                    <div class="input-group">
                        <span class="output-value" id="out-traction">—</span>
                        <span class="unit">lbf</span>
                    </div>
                </div>
                <div class="output-row">
                    <label><?= __('calc.drive.traction_limited') ?></label>
                    <span class="output-value" id="out-traction-limited">—</span>
                </div>
                <div class="output-row">
                    <label><?= __('calc.drive.stall_current') ?></label>
                    <div class="input-group">
                        <span class="output-value" id="out-stall-current">—</span>
                        <span class="unit">A</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="calc-card-full">
        <h2><?= __('calc.drive.chart_vel_pos') ?></h2>
        <div class="chart-container" style="height:350px;">
            <canvas id="drive-chart"></canvas>
        </div>
    </div>
    <div class="calc-card-full">
        <h2><?= __('calc.drive.chart_current') ?></h2>
        <div class="chart-container" style="height:300px;">
            <canvas id="current-chart"></canvas>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
