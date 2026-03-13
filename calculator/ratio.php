<?php
$pageTitle = 'Ratio Calculator';
$calcScripts = ['ratio.js'];
require_once 'header.php';
?>

<div class="calc-container">
    <div class="calc-header">
        <h1><i data-lucide="git-merge" class="w-7 h-7 inline text-gold"></i> Ratio Calculator</h1>
        <button id="share-btn" class="share-btn"><i data-lucide="share-2" class="w-4 h-4"></i> Share</button>
    </div>

    <div class="calc-grid">
        <!-- Inputs -->
        <div class="calc-card">
            <h2>Gear Stages</h2>
            <div id="stages-container">
                <!-- Stages added by JS -->
            </div>
            <div class="flex gap-2 mt-3">
                <button id="add-stage" class="btn-add"><i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Stage</button>
            </div>
            <hr class="calc-divider">
            <p class="text-xs text-gray-500">Each stage multiplies the overall ratio. Enter driving (input) and driven (output) tooth counts.</p>
        </div>

        <!-- Outputs -->
        <div class="calc-card">
            <h2>Results</h2>
            <div class="result-highlight">
                <div class="result-label">Overall Ratio</div>
                <div class="result-number" id="out-ratio">—</div>
            </div>
            <div class="mt-4">
                <div class="output-row">
                    <label>As Reduction</label>
                    <span class="output-value" id="out-reduction">—</span>
                </div>
                <div class="output-row">
                    <label>Type</label>
                    <span class="output-value" id="out-type">—</span>
                </div>
            </div>
            <hr class="calc-divider">
            <h2 class="mt-4">Per-Stage Breakdown</h2>
            <div id="stage-breakdown"></div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
