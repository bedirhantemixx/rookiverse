<?php
$pageTitle = 'Arm Calculator';
$calcScripts = ['motors-data.js', 'arm.js'];
require_once 'header.php';
?>

<div class="calc-container">
    <div class="calc-header">
        <h1><i data-lucide="move-diagonal" class="w-7 h-7 inline text-gold"></i> Arm Calculator</h1>
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
                    <input type="number" data-param="ratio" value="100" min="1" step="1">
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
            <h2>Arm Properties</h2>
            <div class="input-row">
                <label>COM Distance</label>
                <div class="input-group">
                    <input type="number" data-param="comDist" value="20" min="1" step="1">
                    <span class="unit">in</span>
                </div>
            </div>
            <div class="input-row">
                <label>Arm Mass</label>
                <div class="input-group">
                    <input type="number" data-param="mass" value="15" min="0.1" step="0.5">
                    <span class="unit">lb</span>
                </div>
            </div>
            <div class="input-row">
                <label>Start Angle</label>
                <div class="input-group">
                    <input type="number" data-param="startAngle" value="0" min="-180" max="180" step="5">
                    <span class="unit">deg</span>
                </div>
            </div>
            <div class="input-row">
                <label>End Angle</label>
                <div class="input-group">
                    <input type="number" data-param="endAngle" value="90" min="-180" max="180" step="5">
                    <span class="unit">deg</span>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">0 = horizontal, 90 = straight up, -90 = straight down</p>
        </div>

        <!-- Outputs -->
        <div class="calc-card">
            <h2>Results</h2>

            <div class="result-highlight">
                <div class="result-label">Time to Target</div>
                <div class="result-number" id="out-time">—</div>
                <span class="text-sm text-gray-500">seconds</span>
            </div>

            <div class="mt-4">
                <div class="output-row">
                    <label>Max Gravity Torque</label>
                    <div class="input-group">
                        <span class="output-value" id="out-grav-torque">—</span>
                        <span class="unit">N&middot;m</span>
                    </div>
                </div>
                <div class="output-row">
                    <label>Stall Torque (at arm)</label>
                    <div class="input-group">
                        <span class="output-value" id="out-stall-torque">—</span>
                        <span class="unit">N&middot;m</span>
                    </div>
                </div>
                <div class="output-row">
                    <label>Torque Margin at Horizontal</label>
                    <div class="input-group">
                        <span class="output-value" id="out-torque-margin">—</span>
                        <span class="unit">%</span>
                    </div>
                </div>
                <div class="output-row">
                    <label>Can Hold at Horizontal?</label>
                    <span class="output-value" id="out-can-hold">—</span>
                </div>
                <div class="output-row">
                    <label>Peak Motor Current</label>
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
        <h2>Arm Position vs Time</h2>
        <div class="chart-container" style="height:350px;">
            <canvas id="arm-pos-chart"></canvas>
        </div>
    </div>
    <div class="calc-card-full">
        <h2>Torque & Current vs Time</h2>
        <div class="chart-container" style="height:300px;">
            <canvas id="arm-torque-chart"></canvas>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
