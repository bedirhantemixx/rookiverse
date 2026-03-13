<?php
$pageTitle = 'Linear Mechanism Calculator';
$calcScripts = ['motors-data.js', 'linear.js'];
require_once 'header.php';
?>

<div class="calc-container">
    <div class="calc-header">
        <h1><i data-lucide="arrow-up-down" class="w-7 h-7 inline text-gold"></i> Linear Mechanism Calculator</h1>
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
                <label>Gear Ratio</label>
                <div class="input-group">
                    <input type="number" data-param="ratio" value="10" min="0.1" step="0.5">
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
                    <input type="number" data-param="eff" value="85" min="1" max="100" step="1">
                    <span class="unit">%</span>
                </div>
            </div>

            <hr class="calc-divider">
            <h2>Mechanism</h2>
            <div class="input-row">
                <label>Spool Diameter</label>
                <div class="input-group">
                    <input type="number" data-param="spoolD" value="1.5" min="0.1" step="0.1">
                    <span class="unit">in</span>
                </div>
            </div>
            <div class="input-row">
                <label>Travel Distance</label>
                <div class="input-group">
                    <input type="number" data-param="travel" value="48" min="1" step="1">
                    <span class="unit">in</span>
                </div>
            </div>
            <div class="input-row">
                <label>Load Mass</label>
                <div class="input-group">
                    <input type="number" data-param="load" value="15" min="0.1" step="0.5">
                    <span class="unit">lb</span>
                </div>
            </div>
            <div class="input-row">
                <label>Angle from Horizontal</label>
                <div class="input-group">
                    <input type="number" data-param="angle" value="90" min="0" max="90" step="5">
                    <span class="unit">deg</span>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">90 = vertical (elevator), 0 = horizontal</p>
        </div>

        <!-- Outputs -->
        <div class="calc-card">
            <h2>Results</h2>

            <div class="result-highlight">
                <div class="result-label">Time to Full Extension</div>
                <div class="result-number" id="out-time">—</div>
                <span class="text-sm text-gray-500">seconds</span>
            </div>

            <div class="mt-4">
                <div class="output-row">
                    <label>Max Speed</label>
                    <div class="input-group">
                        <span class="output-value" id="out-max-speed">—</span>
                        <span class="unit">in/s</span>
                    </div>
                </div>
                <div class="output-row">
                    <label>Stall Load</label>
                    <div class="input-group">
                        <span class="output-value" id="out-stall-load">—</span>
                        <span class="unit">lb</span>
                    </div>
                </div>
                <div class="output-row">
                    <label>Max Gravity Force</label>
                    <div class="input-group">
                        <span class="output-value" id="out-grav-force">—</span>
                        <span class="unit">lbf</span>
                    </div>
                </div>
                <div class="output-row">
                    <label>Can Lift?</label>
                    <span class="output-value" id="out-can-lift">—</span>
                </div>
                <div class="output-row">
                    <label>Peak Current</label>
                    <div class="input-group">
                        <span class="output-value" id="out-peak-current">—</span>
                        <span class="unit">A</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="calc-card-full">
        <h2>Position & Velocity vs Time</h2>
        <div class="chart-container" style="height:350px;">
            <canvas id="linear-chart"></canvas>
        </div>
    </div>
    <div class="calc-card-full">
        <h2>Current vs Time</h2>
        <div class="chart-container" style="height:300px;">
            <canvas id="linear-current-chart"></canvas>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
