<?php
$pageTitle = 'Intake Calculator';
$calcScripts = ['motors-data.js', 'intake.js'];
require_once 'header.php';
?>

<div class="calc-container">
    <div class="calc-header">
        <h1><i data-lucide="arrow-down-to-line" class="w-7 h-7 inline text-gold"></i> Intake Calculator</h1>
        <button id="share-btn" class="share-btn"><i data-lucide="share-2" class="w-4 h-4"></i> Share</button>
    </div>

    <div class="calc-grid">
        <!-- Inputs -->
        <div class="calc-card">
            <h2>Inputs</h2>
            <div class="input-row">
                <label>Motor</label>
                <div class="input-group">
                    <select data-param="motor" class="motor-select"></select>
                </div>
            </div>
            <div class="input-row">
                <label>Motor Count</label>
                <div class="input-group">
                    <input type="number" data-param="qty" value="1" min="1" max="4" step="1">
                </div>
            </div>
            <div class="input-row">
                <label>Gear Ratio</label>
                <div class="input-group">
                    <input type="number" data-param="ratio" value="2" min="0.1" step="0.1">
                    <span class="unit">:1</span>
                </div>
            </div>
            <div class="input-row">
                <label>Roller Diameter</label>
                <div class="input-group">
                    <input type="number" data-param="rollerD" value="2" min="0.1" step="0.1">
                    <span class="unit">in</span>
                </div>
            </div>
            <hr class="calc-divider">
            <div class="input-row">
                <label>Drivetrain Speed</label>
                <div class="input-group">
                    <input type="number" data-param="driveSpeed" value="14" min="0.1" step="0.1">
                    <span class="unit">ft/s</span>
                </div>
            </div>
            <div class="input-row">
                <label>Speed Multiplier</label>
                <div class="input-group">
                    <input type="number" data-param="multiplier" value="2" min="0.5" step="0.5">
                    <span class="unit">x</span>
                </div>
            </div>
        </div>

        <!-- Outputs -->
        <div class="calc-card">
            <h2>Results</h2>
            <div class="result-highlight">
                <div class="result-label">Surface Speed</div>
                <div class="result-number" id="out-speed">—</div>
                <span class="text-sm text-gray-500">ft/s</span>
            </div>

            <div class="mt-4">
                <div class="output-row">
                    <label>Surface Speed (in/s)</label>
                    <div class="input-group">
                        <span class="output-value" id="out-speed-ins">—</span>
                        <span class="unit">in/s</span>
                    </div>
                </div>
                <div class="output-row">
                    <label>Roller RPM</label>
                    <div class="input-group">
                        <span class="output-value" id="out-roller-rpm">—</span>
                        <span class="unit">RPM</span>
                    </div>
                </div>
            </div>

            <hr class="calc-divider">
            <div class="result-highlight" style="border-color: #3B82F6;">
                <div class="result-label">Recommended Ratio</div>
                <div class="result-number" id="out-rec-ratio" style="color: #3B82F6;">—</div>
                <span class="text-sm text-gray-500"></span>
                <span class="text-sm text-gray-500" id="out-rec-label">for target speed</span>
            </div>

            <div class="mt-4">
                <div class="output-row">
                    <label>Target Surface Speed</label>
                    <div class="input-group">
                        <span class="output-value" id="out-target-speed">—</span>
                        <span class="unit">ft/s</span>
                    </div>
                </div>
                <div class="output-row">
                    <label>Speed vs Drivetrain</label>
                    <span class="output-value" id="out-speed-ratio">—</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
