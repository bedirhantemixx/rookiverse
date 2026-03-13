<?php
$pageTitle = 'Flywheel Calculator';
$calcScripts = ['motors-data.js', 'flywheel.js'];
require_once 'header.php';
?>

<div class="calc-container">
    <div class="calc-header">
        <h1><i data-lucide="disc" class="w-7 h-7 inline text-gold"></i> Flywheel Calculator</h1>
        <button id="share-btn" class="share-btn"><i data-lucide="share-2" class="w-4 h-4"></i> Share</button>
    </div>

    <div class="calc-grid">
        <!-- Inputs -->
        <div class="calc-card">
            <h2>Motor & Gearing</h2>
            <div class="input-row">
                <label>Motor</label>
                <div class="input-group">
                    <select data-param="motor" class="motor-select"></select>
                </div>
            </div>
            <div class="input-row">
                <label>Motor Count</label>
                <div class="input-group">
                    <input type="number" data-param="qty" value="2" min="1" max="4" step="1">
                </div>
            </div>
            <div class="input-row">
                <label>Ratio (motor:wheel)</label>
                <div class="input-group">
                    <input type="number" data-param="ratio" value="2" min="0.1" step="0.1">
                    <span class="unit">:1</span>
                </div>
            </div>
            <div class="input-row">
                <label>Current Limit</label>
                <div class="input-group">
                    <input type="number" data-param="currentLimit" value="40" min="1" step="1">
                    <span class="unit">A</span>
                </div>
            </div>
            <div class="input-row">
                <label>Efficiency</label>
                <div class="input-group">
                    <input type="number" data-param="eff" value="90" min="1" max="100" step="1">
                    <span class="unit">%</span>
                </div>
            </div>

            <hr class="calc-divider">
            <h2>Shooter Wheel</h2>
            <div class="input-row">
                <label>Wheel Radius</label>
                <div class="input-group">
                    <input type="number" data-param="wheelR" value="2" min="0.1" step="0.1">
                    <span class="unit">in</span>
                </div>
            </div>
            <div class="input-row">
                <label>Wheel Weight</label>
                <div class="input-group">
                    <input type="number" data-param="wheelW" value="0.5" min="0.01" step="0.01">
                    <span class="unit">lb</span>
                </div>
            </div>
            <div class="input-row">
                <label>Target Speed</label>
                <div class="input-group">
                    <input type="number" data-param="targetRPM" value="4000" min="100" step="100">
                    <span class="unit">RPM</span>
                </div>
            </div>
            <div class="input-row">
                <label>Speed Variation</label>
                <div class="input-group">
                    <input type="number" data-param="variation" value="5" min="0" max="50" step="1">
                    <span class="unit">%</span>
                </div>
            </div>

            <hr class="calc-divider">
            <h2>Projectile</h2>
            <div class="input-row">
                <label>Projectile Radius</label>
                <div class="input-group">
                    <input type="number" data-param="projR" value="2.375" min="0.1" step="0.1">
                    <span class="unit">in</span>
                </div>
            </div>
            <div class="input-row">
                <label>Projectile Weight</label>
                <div class="input-group">
                    <input type="number" data-param="projW" value="0.55" min="0.01" step="0.01">
                    <span class="unit">lb</span>
                </div>
            </div>
        </div>

        <!-- Outputs -->
        <div class="calc-card">
            <h2>Results</h2>

            <div class="result-highlight">
                <div class="result-label">Windup Time</div>
                <div class="result-number" id="out-windup">—</div>
                <span class="text-sm text-gray-500">seconds</span>
            </div>

            <div class="mt-4">
                <div class="output-row">
                    <label>Surface Speed</label>
                    <div class="input-group">
                        <span class="output-value" id="out-surface-speed">—</span>
                        <span class="unit">ft/s</span>
                    </div>
                </div>
                <div class="output-row">
                    <label>Max Wheel RPM (limited)</label>
                    <div class="input-group">
                        <span class="output-value" id="out-max-rpm">—</span>
                        <span class="unit">RPM</span>
                    </div>
                </div>
            </div>

            <hr class="calc-divider">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Projectile Launch</h3>
            <div class="output-row">
                <label>Speed Transfer</label>
                <div class="input-group">
                    <span class="output-value" id="out-transfer">—</span>
                    <span class="unit">%</span>
                </div>
            </div>
            <div class="output-row">
                <label>Exit Velocity</label>
                <div class="input-group">
                    <span class="output-value" id="out-exit-vel">—</span>
                    <span class="unit">ft/s</span>
                </div>
            </div>
            <div class="output-row">
                <label>Projectile Energy</label>
                <div class="input-group">
                    <span class="output-value" id="out-proj-energy">—</span>
                    <span class="unit">J</span>
                </div>
            </div>

            <hr class="calc-divider">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Recovery</h3>
            <div class="output-row">
                <label>Flywheel Energy</label>
                <div class="input-group">
                    <span class="output-value" id="out-fw-energy">—</span>
                    <span class="unit">J</span>
                </div>
            </div>
            <div class="output-row">
                <label>Speed After Shot</label>
                <div class="input-group">
                    <span class="output-value" id="out-after-rpm">—</span>
                    <span class="unit">RPM</span>
                </div>
            </div>
            <div class="output-row">
                <label>Recovery Time</label>
                <div class="input-group">
                    <span class="output-value" id="out-recovery">—</span>
                    <span class="unit">s</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="calc-card-full">
        <h2>Speed vs Time</h2>
        <div class="chart-container" style="height:350px;">
            <canvas id="flywheel-chart"></canvas>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
