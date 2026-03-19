<?php
$pageTitle = 'calc.ratio_finder.title';
$calcScripts = ['ratio-finder.js'];
require_once 'header.php';
?>

<div class="calc-container">
    <div class="calc-header">
        <h1><i data-lucide="search" class="w-7 h-7 inline text-gold"></i> <?= __('calc.ratio_finder.title') ?></h1>
        <button id="share-btn" class="share-btn"><i data-lucide="share-2" class="w-4 h-4"></i> <?= __('calc.share') ?></button>
    </div>

    <div class="calc-grid">
        <!-- Inputs -->
        <div class="calc-card">
            <h2><?= __('calc.ratio_finder.target') ?></h2>
            <div class="input-row">
                <label><?= __('calc.ratio_finder.target_ratio') ?></label>
                <div class="input-group">
                    <input type="number" data-param="target" value="5" min="0.1" step="0.1">
                    <span class="unit">:1</span>
                </div>
            </div>
            <div class="input-row">
                <label><?= __('calc.ratio_finder.max_error') ?></label>
                <div class="input-group">
                    <input type="number" data-param="maxError" value="5" min="0.1" max="50" step="0.1">
                    <span class="unit">%</span>
                </div>
            </div>

            <hr class="calc-divider">
            <h2><?= __('calc.ratio_finder.constraints') ?></h2>
            <div class="input-row">
                <label><?= __('calc.ratio_finder.stages') ?></label>
                <div class="input-group">
                    <select data-param="stages" style="width:120px;padding:8px;border:1px solid #d1d5db;border-radius:8px;font-size:0.875rem;">
                        <option value="1"><?= __('calc.ratio_finder.stage_1') ?></option>
                        <option value="2" selected><?= __('calc.ratio_finder.stage_2') ?></option>
                    </select>
                </div>
            </div>
            <div class="input-row">
                <label><?= __('calc.ratio_finder.min_teeth') ?></label>
                <div class="input-group">
                    <input type="number" data-param="minTeeth" value="10" min="6" max="100" step="1">
                </div>
            </div>
            <div class="input-row">
                <label><?= __('calc.ratio_finder.max_teeth') ?></label>
                <div class="input-group">
                    <input type="number" data-param="maxTeeth" value="84" min="10" max="200" step="1">
                </div>
            </div>

            <hr class="calc-divider">
            <h2><?= __('calc.ratio_finder.options') ?></h2>
            <div class="input-row">
                <label><?= __('calc.ratio_finder.type') ?></label>
                <div class="input-group">
                    <select data-param="type" style="width:120px;padding:8px;border:1px solid #d1d5db;border-radius:8px;font-size:0.875rem;">
                        <option value="all"><?= __('calc.ratio_finder.type_all') ?></option>
                        <option value="gears"><?= __('calc.ratio_finder.type_gears') ?></option>
                        <option value="sprockets"><?= __('calc.ratio_finder.type_sprockets') ?></option>
                    </select>
                </div>
            </div>
            <div class="input-row">
                <label><?= __('calc.ratio_finder.max_results') ?></label>
                <div class="input-group">
                    <input type="number" data-param="maxResults" value="50" min="10" max="200" step="10">
                </div>
            </div>

            <p class="text-xs text-gray-500 mt-3"><?= __('calc.ratio_finder.search_desc') ?></p>
        </div>

        <!-- Outputs -->
        <div class="calc-card">
            <h2><?= __('calc.results') ?> <span id="result-count" class="text-sm font-normal text-gray-400"></span></h2>
            <div id="results-container" style="max-height: 600px; overflow-y: auto;">
                <p class="text-gray-400 text-sm" id="results-placeholder"><?= __('calc.ratio_finder.placeholder') ?></p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
