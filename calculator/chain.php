<?php
$pageTitle = 'Chain Calculator';
$calcScripts = ['chain.js'];
require_once 'header.php';
?>

<div class="calc-container">
    <div class="calc-header">
        <h1><i data-lucide="link-2" class="w-7 h-7 inline text-gold"></i> Chain Calculator</h1>
        <button id="share-btn" class="share-btn"><i data-lucide="share-2" class="w-4 h-4"></i> Share</button>
    </div>

    <div class="calc-grid">
        <!-- Inputs -->
        <div class="calc-card">
            <h2>Inputs</h2>
            <div class="input-row">
                <label>Chain Type</label>
                <div class="input-group">
                    <select data-param="chain">
                        <option value="0.25" selected>#25 (1/4" pitch)</option>
                        <option value="0.375">#35 (3/8" pitch)</option>
                        <option value="0.5">#40 / #41 (1/2" pitch)</option>
                    </select>
                </div>
            </div>
            <div class="input-row">
                <label>Sprocket 1 Teeth</label>
                <div class="input-group">
                    <input type="number" data-param="s1" value="16" min="1" step="1">
                    <span class="unit">T</span>
                </div>
            </div>
            <div class="input-row">
                <label>Sprocket 2 Teeth</label>
                <div class="input-group">
                    <input type="number" data-param="s2" value="32" min="1" step="1">
                    <span class="unit">T</span>
                </div>
            </div>
            <div class="input-row">
                <label>Desired C-C Distance</label>
                <div class="input-group">
                    <input type="number" data-param="cc" value="5" min="0.1" step="0.1">
                    <span class="unit">in</span>
                </div>
            </div>
            <div class="input-row">
                <label>Allow Half Links</label>
                <div class="input-group">
                    <label class="toggle-switch">
                        <input type="checkbox" data-param="half">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Outputs -->
        <div class="calc-card">
            <h2>Results</h2>

            <div class="output-row">
                <label>Sprocket 1 Pitch Diameter</label>
                <div class="input-group">
                    <span class="output-value" id="out-pd1">—</span>
                    <span class="unit">in</span>
                </div>
            </div>
            <div class="output-row">
                <label>Sprocket 2 Pitch Diameter</label>
                <div class="input-group">
                    <span class="output-value" id="out-pd2">—</span>
                    <span class="unit">in</span>
                </div>
            </div>

            <hr class="calc-divider">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Shorter Chain</h3>
            <div class="output-row">
                <label>Links</label>
                <span class="output-value" id="out-sm-links">—</span>
            </div>
            <div class="output-row">
                <label>C-C Distance</label>
                <div class="input-group">
                    <span class="output-value" id="out-sm-cc">—</span>
                    <span class="unit">in</span>
                </div>
            </div>

            <hr class="calc-divider">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Longer Chain</h3>
            <div class="output-row">
                <label>Links</label>
                <span class="output-value" id="out-lg-links">—</span>
            </div>
            <div class="output-row">
                <label>C-C Distance</label>
                <div class="input-group">
                    <span class="output-value" id="out-lg-cc">—</span>
                    <span class="unit">in</span>
                </div>
            </div>

            <hr class="calc-divider">
            <div class="output-row">
                <label>Gear Ratio</label>
                <span class="output-value" id="out-ratio">—</span>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
