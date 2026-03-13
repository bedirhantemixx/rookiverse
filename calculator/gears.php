<?php
$pageTitle = 'Gear Spacing Calculator';
$calcScripts = ['gears.js'];
require_once 'header.php';
?>

<div class="calc-container">
    <div class="calc-header">
        <h1><i data-lucide="settings" class="w-7 h-7 inline text-gold"></i> Gear Spacing Calculator</h1>
        <button id="share-btn" class="share-btn"><i data-lucide="share-2" class="w-4 h-4"></i> Share</button>
    </div>

    <div class="calc-grid">
        <!-- Inputs -->
        <div class="calc-card">
            <h2>Inputs</h2>
            <div class="input-row">
                <label>Gear 1 Teeth</label>
                <div class="input-group">
                    <input type="number" data-param="g1" value="20" min="1" step="1">
                    <span class="unit">T</span>
                </div>
            </div>
            <div class="input-row">
                <label>Gear 2 Teeth</label>
                <div class="input-group">
                    <input type="number" data-param="g2" value="40" min="1" step="1">
                    <span class="unit">T</span>
                </div>
            </div>
            <div class="input-row">
                <label>Diametral Pitch</label>
                <div class="input-group">
                    <input type="number" data-param="dp" value="20" min="1" step="1">
                    <span class="unit">DP</span>
                </div>
            </div>
            <hr class="calc-divider">
            <p class="text-xs text-gray-500 mt-2">Diametral Pitch (DP) = teeth per inch of pitch diameter. Common FRC values: 20 DP (standard), 32 DP (fine).</p>
        </div>

        <!-- Outputs -->
        <div class="calc-card">
            <h2>Results</h2>
            <div class="output-row">
                <label>Gear 1 Pitch Diameter</label>
                <div class="input-group">
                    <span class="output-value" id="out-pd1">—</span>
                    <span class="unit">in</span>
                </div>
            </div>
            <div class="output-row">
                <label>Gear 2 Pitch Diameter</label>
                <div class="input-group">
                    <span class="output-value" id="out-pd2">—</span>
                    <span class="unit">in</span>
                </div>
            </div>
            <hr class="calc-divider">
            <div class="result-highlight">
                <div class="result-label">Center-to-Center Distance</div>
                <div class="result-number" id="out-cc">—</div>
                <span class="text-sm text-gray-500">inches</span>
            </div>
            <hr class="calc-divider">
            <div class="output-row">
                <label>Gear Ratio</label>
                <span class="output-value" id="out-ratio">—</span>
            </div>
            <div class="output-row">
                <label>Reduction</label>
                <span class="output-value" id="out-reduction">—</span>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
