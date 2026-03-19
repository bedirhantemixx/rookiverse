<?php
$pageTitle = 'Ratio Finder';
$calcScripts = ['ratio-finder.js'];
require_once 'header.php';
?>

<div class="calc-container">
    <div class="calc-header">
        <h1><i data-lucide="search" class="w-7 h-7 inline text-gold"></i> Ratio Finder</h1>
        <button id="share-btn" class="share-btn"><i data-lucide="share-2" class="w-4 h-4"></i> Share</button>
    </div>

    <div class="calc-grid">
        <!-- Inputs -->
        <div class="calc-card">
            <h2>Target</h2>
            <div class="input-row">
                <label>Target Ratio</label>
                <div class="input-group">
                    <input type="number" data-param="target" value="5" min="0.1" step="0.1">
                    <span class="unit">:1</span>
                </div>
            </div>
            <div class="input-row">
                <label>Max Error</label>
                <div class="input-group">
                    <input type="number" data-param="maxError" value="5" min="0.1" max="50" step="0.1">
                    <span class="unit">%</span>
                </div>
            </div>

            <hr class="calc-divider">
            <h2>Constraints</h2>
            <div class="input-row">
                <label>Stages</label>
                <div class="input-group">
                    <select data-param="stages" style="width:120px;padding:8px;border:1px solid #d1d5db;border-radius:8px;font-size:0.875rem;">
                        <option value="1">1 Stage</option>
                        <option value="2" selected>2 Stages</option>
                    </select>
                </div>
            </div>
            <div class="input-row">
                <label>Min Teeth</label>
                <div class="input-group">
                    <input type="number" data-param="minTeeth" value="10" min="6" max="100" step="1">
                </div>
            </div>
            <div class="input-row">
                <label>Max Teeth</label>
                <div class="input-group">
                    <input type="number" data-param="maxTeeth" value="84" min="10" max="200" step="1">
                </div>
            </div>

            <hr class="calc-divider">
            <h2>Options</h2>
            <div class="input-row">
                <label>Type</label>
                <div class="input-group">
                    <select data-param="type" style="width:120px;padding:8px;border:1px solid #d1d5db;border-radius:8px;font-size:0.875rem;">
                        <option value="all">All</option>
                        <option value="gears">Gears only</option>
                        <option value="sprockets">Sprockets only</option>
                    </select>
                </div>
            </div>
            <div class="input-row">
                <label>Max Results</label>
                <div class="input-group">
                    <input type="number" data-param="maxResults" value="50" min="10" max="200" step="10">
                </div>
            </div>

            <p class="text-xs text-gray-500 mt-3">Searches all combinations of tooth counts within the specified range. 2-stage search checks pairs of reductions.</p>
        </div>

        <!-- Outputs -->
        <div class="calc-card">
            <h2>Results <span id="result-count" class="text-sm font-normal text-gray-400"></span></h2>
            <div id="results-container" style="max-height: 600px; overflow-y: auto;">
                <p class="text-gray-400 text-sm" id="results-placeholder">Adjust inputs to find gear combinations.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
