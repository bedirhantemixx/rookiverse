<?php
$pageTitle = 'Drivetrain Calculator';
$calcScripts = ['motors-data.js', 'drive.js'];
require_once 'header.php';
?>

<div class="calc-container">
    <div class="calc-header">
        <h1><i data-lucide="truck" class="w-7 h-7 inline text-gold"></i> Drivetrain Calculator</h1>
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
                <label>Motors per Side</label>
                <div class="input-group">
                    <input type="number" data-param="motorsPerSide" value="2" min="1" max="4" step="1">
                </div>
            </div>
            <div class="input-row">
                <label>Gear Ratio</label>
                <div class="input-group">
                    <input type="number" data-param="ratio" value="5.36" min="0.1" step="0.01">
                    <span class="unit">:1</span>
                </div>
            </div>
            <div class="input-row">
                <label>Efficiency</label>
                <div class="input-group">
                    <input type="number" data-param="eff" value="97" min="1" max="100" step="1">
                    <span class="unit">%</span>
                </div>
            </div>
            <div class="input-row">
                <label>Current Limit</label>
                <div class="input-group">
                    <input type="number" data-param="currentLimit" value="60" min="1" step="1">
                    <span class="unit">A</span>
                </div>
            </div>

            <hr class="calc-divider">
            <h2>Robot</h2>
            <div class="input-row">
                <label>Robot Weight</label>
                <div class="input-group">
                    <input type="number" data-param="robotWeight" value="125" min="1" step="1">
                    <span class="unit">lb</span>
                </div>
            </div>
            <div class="input-row">
                <label>Bumper Weight</label>
                <div class="input-group">
                    <input type="number" data-param="bumperWeight" value="15" min="0" step="1">
                    <span class="unit">lb</span>
                </div>
            </div>
            <div class="input-row">
                <label>Wheel Diameter</label>
                <div class="input-group">
                    <input type="number" data-param="wheelD" value="4" min="0.5" step="0.5">
                    <span class="unit">in</span>
                </div>
            </div>
            <div class="input-row">
                <label>Coefficient of Friction</label>
                <div class="input-group">
                    <input type="number" data-param="cof" value="1.1" min="0.1" step="0.05">
                </div>
            </div>

            <hr class="calc-divider">
            <h2>Sprint</h2>
            <div class="input-row">
                <label>Sprint Distance</label>
                <div class="input-group">
                    <input type="number" data-param="sprintDist" value="25" min="1" step="1">
                    <span class="unit">ft</span>
                </div>
            </div>

            <hr class="calc-divider">
            <h2>Battery</h2>
            <div class="input-row">
                <label>Voltage</label>
                <div class="input-group">
                    <input type="number" data-param="voltage" value="12.6" min="10" max="13" step="0.1">
                    <span class="unit">V</span>
                </div>
            </div>
            <div class="input-row">
                <label>Internal Resistance</label>
                <div class="input-group">
                    <input type="number" data-param="battR" value="0.018" min="0.001" step="0.001">
                    <span class="unit">&Omega;</span>
                </div>
            </div>
        </div>

        <!-- Outputs -->
        <div class="calc-card">
            <h2>Results</h2>

            <div class="result-highlight">
                <div class="result-label">Theoretical Max Speed</div>
                <div class="result-number" id="out-max-speed">—</div>
                <span class="text-sm text-gray-500">ft/s</span>
            </div>

            <div class="mt-4">
                <div class="output-row">
                    <label>Max Speed (mph)</label>
                    <div class="input-group">
                        <span class="output-value" id="out-max-mph">—</span>
                        <span class="unit">mph</span>
                    </div>
                </div>
                <div class="output-row">
                    <label>Adjusted Free Speed</label>
                    <div class="input-group">
                        <span class="output-value" id="out-adj-speed">—</span>
                        <span class="unit">ft/s</span>
                    </div>
                </div>
            </div>

            <hr class="calc-divider">
            <div class="result-highlight" style="border-color: #3B82F6;">
                <div class="result-label">Sprint Time</div>
                <div class="result-number" id="out-sprint-time" style="color: #3B82F6;">—</div>
                <span class="text-sm text-gray-500">seconds</span>
            </div>

            <div class="mt-4">
                <div class="output-row">
                    <label>Max Pushing Force</label>
                    <div class="input-group">
                        <span class="output-value" id="out-push-force">—</span>
                        <span class="unit">lbf</span>
                    </div>
                </div>
                <div class="output-row">
                    <label>Max Traction Force</label>
                    <div class="input-group">
                        <span class="output-value" id="out-traction">—</span>
                        <span class="unit">lbf</span>
                    </div>
                </div>
                <div class="output-row">
                    <label>Traction Limited?</label>
                    <span class="output-value" id="out-traction-limited">—</span>
                </div>
                <div class="output-row">
                    <label>Stall Current (per motor)</label>
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
        <h2>Velocity & Position vs Time</h2>
        <div class="chart-container" style="height:350px;">
            <canvas id="drive-chart"></canvas>
        </div>
    </div>
    <div class="calc-card-full">
        <h2>Current Draw vs Time</h2>
        <div class="chart-container" style="height:300px;">
            <canvas id="current-chart"></canvas>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
