<?php
$pageTitle = 'Belt Calculator';
$calcScripts = ['belts.js'];
require_once 'header.php';
?>

<div class="calc-container">
    <div class="calc-header">
        <h1><i data-lucide="link" class="w-7 h-7 inline text-gold"></i> Belt Calculator</h1>
        <button id="share-btn" class="share-btn"><i data-lucide="share-2" class="w-4 h-4"></i> Share</button>
    </div>

    <div class="calc-grid">
        <!-- Inputs -->
        <div class="calc-card">
            <h2>Inputs</h2>
            <div class="input-row">
                <label>Belt Pitch</label>
                <div class="input-group">
                    <select data-param="pitch">
                        <option value="3">3mm (GT2/HTD)</option>
                        <option value="5" selected>5mm (HTD)</option>
                        <option value="9">9mm (HTD)</option>
                        <option value="0.0816">XL (1/5")</option>
                    </select>
                </div>
            </div>
            <div class="input-row">
                <label>Pulley 1 Teeth</label>
                <div class="input-group">
                    <input type="number" data-param="p1" value="18" min="1" step="1">
                    <span class="unit">T</span>
                </div>
            </div>
            <div class="input-row">
                <label>Pulley 2 Teeth</label>
                <div class="input-group">
                    <input type="number" data-param="p2" value="36" min="1" step="1">
                    <span class="unit">T</span>
                </div>
            </div>
            <div class="input-row">
                <label>Desired C-C Distance</label>
                <div class="input-group">
                    <input type="number" data-param="cc" value="6" min="0.1" step="0.1">
                    <span class="unit">in</span>
                </div>
            </div>
            <div class="input-row">
                <label>Belt Tooth Increment</label>
                <div class="input-group">
                    <select data-param="incr">
                        <option value="1">1 tooth</option>
                        <option value="5" selected>5 teeth</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Outputs -->
        <div class="calc-card">
            <h2>Results</h2>

            <div class="output-row">
                <label>Pulley 1 Pitch Diameter</label>
                <div class="input-group">
                    <span class="output-value" id="out-pd1">—</span>
                    <span class="unit">in</span>
                </div>
            </div>
            <div class="output-row">
                <label>Pulley 2 Pitch Diameter</label>
                <div class="input-group">
                    <span class="output-value" id="out-pd2">—</span>
                    <span class="unit">in</span>
                </div>
            </div>

            <hr class="calc-divider">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Smaller Belt</h3>
            <div class="output-row">
                <label>Belt Teeth</label>
                <span class="output-value" id="out-sm-teeth">—</span>
            </div>
            <div class="output-row">
                <label>C-C Distance</label>
                <div class="input-group">
                    <span class="output-value" id="out-sm-cc">—</span>
                    <span class="unit">in</span>
                </div>
            </div>
            <div class="output-row">
                <label>Teeth in Mesh (Small Pulley)</label>
                <span class="output-value" id="out-sm-tim">—</span>
            </div>

            <hr class="calc-divider">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Larger Belt</h3>
            <div class="output-row">
                <label>Belt Teeth</label>
                <span class="output-value" id="out-lg-teeth">—</span>
            </div>
            <div class="output-row">
                <label>C-C Distance</label>
                <div class="input-group">
                    <span class="output-value" id="out-lg-cc">—</span>
                    <span class="unit">in</span>
                </div>
            </div>
            <div class="output-row">
                <label>Teeth in Mesh (Small Pulley)</label>
                <span class="output-value" id="out-lg-tim">—</span>
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
