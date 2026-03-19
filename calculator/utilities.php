<?php
$pageTitle = 'Utilities & Cheat Sheets';
$calcScripts = ['utilities.js'];
require_once 'header.php';
?>

<div class="calc-container">
    <div class="calc-header">
        <h1><i data-lucide="wrench" class="w-7 h-7 inline text-gold"></i> Utilities & Cheat Sheets</h1>
    </div>

    <div class="calc-grid">
        <!-- Hole Sizes -->
        <div class="calc-card">
            <h2>Hole Size Calculator</h2>
            <p class="text-xs text-gray-500 mb-3">Enter a hole measurement to find matching bolt sizes.</p>
            <div class="input-row">
                <label>Hole Size</label>
                <div class="input-group">
                    <input type="number" id="hole-input" value="0.201" min="0.01" step="0.001"
                        style="width:100px;padding:8px;border:1px solid #d1d5db;border-radius:8px;text-align:right;font-size:0.875rem;">
                    <span class="unit">in</span>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Imperial Bolts</h3>
                <div id="hole-imperial" class="text-sm"></div>
            </div>
            <div class="mt-3">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Metric Bolts</h3>
                <div id="hole-metric" class="text-sm"></div>
            </div>
        </div>

        <!-- Spacer Calculator -->
        <div class="calc-card">
            <h2>Spacer Calculator</h2>
            <p class="text-xs text-gray-500 mb-3">Find the best combination of standard spacers to reach a target length.</p>
            <div class="input-row">
                <label>Target Length</label>
                <div class="input-group">
                    <input type="number" id="spacer-input" value="0.875" min="0.01" step="0.001"
                        style="width:100px;padding:8px;border:1px solid #d1d5db;border-radius:8px;text-align:right;font-size:0.875rem;">
                    <span class="unit">in</span>
                </div>
            </div>
            <div class="mt-3" id="spacer-result"></div>
        </div>
    </div>

    <!-- Bearings Table -->
    <div class="calc-card-full">
        <h2>Common FRC Bearings</h2>
        <div style="overflow-x: auto;">
            <table class="calc-table" id="bearings-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Bore</th>
                        <th>ID <span class="text-xs font-normal text-gray-400">(in)</span></th>
                        <th>OD <span class="text-xs font-normal text-gray-400">(in)</span></th>
                        <th>Flanged OD <span class="text-xs font-normal text-gray-400">(in)</span></th>
                        <th>Height <span class="text-xs font-normal text-gray-400">(in)</span></th>
                    </tr>
                </thead>
                <tbody id="bearings-body">
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mounting Cheat Sheet -->
    <div class="calc-card-full">
        <h2>Electrical Mounting Cheat Sheet</h2>
        <p class="text-sm text-gray-500 mb-3">Common FRC electrical component mounting dimensions.</p>
        <div style="overflow-x: auto;">
            <table class="calc-table" id="mounting-table">
                <thead>
                    <tr>
                        <th>Vendor</th>
                        <th>Product</th>
                        <th>Hole Dia <span class="text-xs font-normal text-gray-400">(in)</span></th>
                        <th>Screw</th>
                        <th>Mount W <span class="text-xs font-normal text-gray-400">(in)</span></th>
                        <th>Mount H <span class="text-xs font-normal text-gray-400">(in)</span></th>
                        <th>Full W <span class="text-xs font-normal text-gray-400">(in)</span></th>
                        <th>Full H <span class="text-xs font-normal text-gray-400">(in)</span></th>
                    </tr>
                </thead>
                <tbody id="mounting-body">
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
