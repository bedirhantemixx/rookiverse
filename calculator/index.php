<?php
require_once 'header.php';
?>

<div class="calc-container">
    <!-- Hero -->
    <div class="text-center mb-10">
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-3"><?= __('calc.index.hero_title') ?></h1>
        <p class="text-gray-600 text-lg max-w-2xl mx-auto"><?= __('calc.index.hero_desc') ?></p>
    </div>

    <!-- Calculators Section -->
    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i data-lucide="calculator" class="w-5 h-5 text-gold"></i> <?= __('calc.index.section_calc') ?>
    </h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 mb-10">
        <a href="<?= $calcBase ?>/belts.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="link" class="w-6 h-6"></i></div>
            <div class="tile-title"><?= __('calc.nav.belts') ?></div>
            <div class="tile-desc"><?= __('calc.index.tile_belts') ?></div>
        </a>
        <a href="<?= $calcBase ?>/chain.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="link-2" class="w-6 h-6"></i></div>
            <div class="tile-title"><?= __('calc.nav.chain') ?></div>
            <div class="tile-desc"><?= __('calc.index.tile_chain') ?></div>
        </a>
        <a href="<?= $calcBase ?>/gears.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="settings" class="w-6 h-6"></i></div>
            <div class="tile-title"><?= __('calc.nav.gears') ?></div>
            <div class="tile-desc"><?= __('calc.index.tile_gears') ?></div>
        </a>
        <a href="<?= $calcBase ?>/ratio.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="git-merge" class="w-6 h-6"></i></div>
            <div class="tile-title"><?= __('calc.nav.ratio') ?></div>
            <div class="tile-desc"><?= __('calc.index.tile_ratio') ?></div>
        </a>
        <a href="<?= $calcBase ?>/ratio-finder.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="search" class="w-6 h-6"></i></div>
            <div class="tile-title"><?= __('calc.nav.ratio_finder') ?></div>
            <div class="tile-desc"><?= __('calc.index.tile_ratio_finder') ?></div>
        </a>
        <a href="<?= $calcBase ?>/flywheel.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="disc" class="w-6 h-6"></i></div>
            <div class="tile-title"><?= __('calc.nav.flywheel') ?></div>
            <div class="tile-desc"><?= __('calc.index.tile_flywheel') ?></div>
        </a>
        <a href="<?= $calcBase ?>/intake.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="arrow-down-to-line" class="w-6 h-6"></i></div>
            <div class="tile-title"><?= __('calc.nav.intake') ?></div>
            <div class="tile-desc"><?= __('calc.index.tile_intake') ?></div>
        </a>
        <a href="<?= $calcBase ?>/arm.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="move-diagonal" class="w-6 h-6"></i></div>
            <div class="tile-title"><?= __('calc.nav.arm') ?></div>
            <div class="tile-desc"><?= __('calc.index.tile_arm') ?></div>
        </a>
        <a href="<?= $calcBase ?>/linear.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="arrow-up-down" class="w-6 h-6"></i></div>
            <div class="tile-title"><?= __('calc.nav.linear') ?></div>
            <div class="tile-desc"><?= __('calc.index.tile_linear') ?></div>
        </a>
        <a href="<?= $calcBase ?>/drive.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="truck" class="w-6 h-6"></i></div>
            <div class="tile-title"><?= __('calc.nav.drive') ?></div>
            <div class="tile-desc"><?= __('calc.index.tile_drive') ?></div>
        </a>
        <a href="<?= $calcBase ?>/pneumatics.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="wind" class="w-6 h-6"></i></div>
            <div class="tile-title"><?= __('calc.nav.pneumatics') ?></div>
            <div class="tile-desc"><?= __('calc.index.tile_pneumatics') ?></div>
        </a>
    </div>

    <!-- Information Section -->
    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i data-lucide="info" class="w-5 h-5 text-gold"></i> <?= __('calc.index.section_info') ?>
    </h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-10">
        <a href="<?= $calcBase ?>/motors.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="zap" class="w-6 h-6"></i></div>
            <div class="tile-title"><?= __('calc.nav.motors') ?></div>
            <div class="tile-desc"><?= __('calc.index.tile_motors') ?></div>
        </a>
        <a href="<?= $calcBase ?>/compressors.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="gauge" class="w-6 h-6"></i></div>
            <div class="tile-title"><?= __('calc.nav.compressors') ?></div>
            <div class="tile-desc"><?= __('calc.index.tile_compressors') ?></div>
        </a>
        <a href="<?= $calcBase ?>/utilities.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="wrench" class="w-6 h-6"></i></div>
            <div class="tile-title"><?= __('calc.nav.utilities') ?></div>
            <div class="tile-desc"><?= __('calc.index.tile_utilities') ?></div>
        </a>
    </div>

    <!-- Quick Links -->
    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i data-lucide="external-link" class="w-5 h-5 text-gold"></i> <?= __('calc.index.section_resources') ?>
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="calc-card">
            <h3 class="text-sm font-semibold text-gold uppercase mb-3"><?= __('calc.index.game_resources') ?></h3>
            <ul class="space-y-2 text-sm">
                <li><a href="https://www.firstinspires.org/resource-library/frc/competition-manual-qa-system" target="_blank" rel="noopener" class="text-gray-600 hover:text-gold transition-colors"><?= __('calc.index.game_manual') ?></a></li>
                <li><a href="https://frc-events.firstinspires.org/" target="_blank" rel="noopener" class="text-gray-600 hover:text-gold transition-colors">FRC Events</a></li>
                <li><a href="https://www.statbotics.io/" target="_blank" rel="noopener" class="text-gray-600 hover:text-gold transition-colors">Statbotics</a></li>
            </ul>
        </div>
        <div class="calc-card">
            <h3 class="text-sm font-semibold text-gold uppercase mb-3"><?= __('calc.index.community') ?></h3>
            <ul class="space-y-2 text-sm">
                <li><a href="https://www.chiefdelphi.com/" target="_blank" rel="noopener" class="text-gray-600 hover:text-gold transition-colors">Chief Delphi</a></li>
                <li><a href="https://www.thebluealliance.com/" target="_blank" rel="noopener" class="text-gray-600 hover:text-gold transition-colors">The Blue Alliance</a></li>
                <li><a href="https://openalliance.frc971.org/" target="_blank" rel="noopener" class="text-gray-600 hover:text-gold transition-colors">Open Alliance</a></li>
            </ul>
        </div>
        <div class="calc-card">
            <h3 class="text-sm font-semibold text-gold uppercase mb-3"><?= __('calc.index.vendors') ?></h3>
            <ul class="space-y-2 text-sm">
                <li><a href="https://www.revrobotics.com/" target="_blank" rel="noopener" class="text-gray-600 hover:text-gold transition-colors">REV Robotics</a></li>
                <li><a href="https://wcproducts.com/" target="_blank" rel="noopener" class="text-gray-600 hover:text-gold transition-colors">WCP</a></li>
                <li><a href="https://www.andymark.com/" target="_blank" rel="noopener" class="text-gray-600 hover:text-gold transition-colors">AndyMark</a></li>
            </ul>
        </div>
    </div>
</div>

<?php
$calcScripts = [];
require_once 'footer.php';
?>
