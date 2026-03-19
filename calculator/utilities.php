<?php
$pageTitle = 'calc.utilities.title';
$calcScripts = ['utilities.js'];
require_once 'header.php';
?>

<div class="calc-container">
    <div class="calc-header">
        <h1><i data-lucide="wrench" class="w-7 h-7 inline text-gold"></i> <?= __('calc.utilities.title') ?></h1>
    </div>

    <div class="calc-grid">
        <!-- Hole Sizes -->
        <div class="calc-card">
            <h2><?= __('calc.utilities.hole_calc') ?></h2>
            <p class="text-xs text-gray-500 mb-3"><?= __('calc.utilities.hole_desc') ?></p>
            <div class="input-row">
                <label><?= __('calc.utilities.hole_size') ?></label>
                <div class="input-group">
                    <input type="number" id="hole-input" value="0.201" min="0.01" step="0.001"
                        style="width:100px;padding:8px;border:1px solid #d1d5db;border-radius:8px;text-align:right;font-size:0.875rem;">
                    <span class="unit">in</span>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-sm font-semibold text-gray-700 mb-2"><?= __('calc.utilities.imperial_bolts') ?></h3>
                <div id="hole-imperial" class="text-sm"></div>
            </div>
            <div class="mt-3">
                <h3 class="text-sm font-semibold text-gray-700 mb-2"><?= __('calc.utilities.metric_bolts') ?></h3>
                <div id="hole-metric" class="text-sm"></div>
            </div>
        </div>

        <!-- Spacer Calculator -->
        <div class="calc-card">
            <h2><?= __('calc.utilities.spacer_calc') ?></h2>
            <p class="text-xs text-gray-500 mb-3"><?= __('calc.utilities.spacer_desc') ?></p>
            <div class="input-row">
                <label><?= __('calc.utilities.target_length') ?></label>
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
        <h2><?= __('calc.utilities.bearings_title') ?></h2>
        <div style="overflow-x: auto;">
            <table class="calc-table" id="bearings-table">
                <thead>
                    <tr>
                        <th><?= __('calc.utilities.col_type') ?></th>
                        <th><?= __('calc.utilities.col_bore') ?></th>
                        <th><?= __('calc.utilities.col_id') ?> <span class="text-xs font-normal text-gray-400">(in)</span></th>
                        <th><?= __('calc.utilities.col_od') ?> <span class="text-xs font-normal text-gray-400">(in)</span></th>
                        <th><?= __('calc.utilities.col_flanged_od') ?> <span class="text-xs font-normal text-gray-400">(in)</span></th>
                        <th><?= __('calc.utilities.col_height') ?> <span class="text-xs font-normal text-gray-400">(in)</span></th>
                    </tr>
                </thead>
                <tbody id="bearings-body">
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mounting Cheat Sheet -->
    <div class="calc-card-full">
        <h2><?= __('calc.utilities.mounting_title') ?></h2>
        <p class="text-sm text-gray-500 mb-3"><?= __('calc.utilities.mounting_desc') ?></p>
        <div style="overflow-x: auto;">
            <table class="calc-table" id="mounting-table">
                <thead>
                    <tr>
                        <th><?= __('calc.utilities.col_vendor') ?></th>
                        <th><?= __('calc.utilities.col_product') ?></th>
                        <th><?= __('calc.utilities.col_hole_dia') ?> <span class="text-xs font-normal text-gray-400">(in)</span></th>
                        <th><?= __('calc.utilities.col_screw') ?></th>
                        <th><?= __('calc.utilities.col_mount_w') ?> <span class="text-xs font-normal text-gray-400">(in)</span></th>
                        <th><?= __('calc.utilities.col_mount_h') ?> <span class="text-xs font-normal text-gray-400">(in)</span></th>
                        <th><?= __('calc.utilities.col_full_w') ?> <span class="text-xs font-normal text-gray-400">(in)</span></th>
                        <th><?= __('calc.utilities.col_full_h') ?> <span class="text-xs font-normal text-gray-400">(in)</span></th>
                    </tr>
                </thead>
                <tbody id="mounting-body">
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
