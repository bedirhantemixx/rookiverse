<?php
$pageTitle = 'MechTools';
require_once 'header.php';
?>

<div class="calc-container">
    <!-- Hero -->
    <div class="text-center mb-10">
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-3">MechTools</h1>
        <p class="text-gray-600 text-lg max-w-2xl mx-auto">FRC mechanical design calculators to help your team design, iterate, and collaborate faster.</p>
    </div>

    <!-- Calculators Section -->
    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i data-lucide="calculator" class="w-5 h-5 text-gold"></i> Calculators
    </h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 mb-10">
        <a href="<?= $calcBase ?>/belts.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="link" class="w-6 h-6"></i></div>
            <div class="tile-title">Belts</div>
            <div class="tile-desc">Belt & pulley center distance</div>
        </a>
        <a href="<?= $calcBase ?>/chain.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="link-2" class="w-6 h-6"></i></div>
            <div class="tile-title">Chain</div>
            <div class="tile-desc">Chain & sprocket spacing</div>
        </a>
        <a href="<?= $calcBase ?>/gears.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="settings" class="w-6 h-6"></i></div>
            <div class="tile-title">Gears</div>
            <div class="tile-desc">Gear mesh spacing</div>
        </a>
        <a href="<?= $calcBase ?>/ratio.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="git-merge" class="w-6 h-6"></i></div>
            <div class="tile-title">Ratio</div>
            <div class="tile-desc">Compound gear ratio</div>
        </a>
        <a href="<?= $calcBase ?>/ratio-finder.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="search" class="w-6 h-6"></i></div>
            <div class="tile-title">Ratio Finder</div>
            <div class="tile-desc">Find COTS gear combos</div>
        </a>
        <a href="<?= $calcBase ?>/flywheel.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="disc" class="w-6 h-6"></i></div>
            <div class="tile-title">Flywheel</div>
            <div class="tile-desc">Shooter wheel analysis</div>
        </a>
        <a href="<?= $calcBase ?>/intake.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="arrow-down-to-line" class="w-6 h-6"></i></div>
            <div class="tile-title">Intake</div>
            <div class="tile-desc">Intake roller speed</div>
        </a>
        <a href="<?= $calcBase ?>/arm.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="move-diagonal" class="w-6 h-6"></i></div>
            <div class="tile-title">Arm</div>
            <div class="tile-desc">Rotational arm simulator</div>
        </a>
        <a href="<?= $calcBase ?>/linear.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="arrow-up-down" class="w-6 h-6"></i></div>
            <div class="tile-title">Linear</div>
            <div class="tile-desc">Elevator / linear mech</div>
        </a>
        <a href="<?= $calcBase ?>/drive.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="truck" class="w-6 h-6"></i></div>
            <div class="tile-title">Drive</div>
            <div class="tile-desc">Drivetrain simulator</div>
        </a>
        <a href="<?= $calcBase ?>/pneumatics.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="wind" class="w-6 h-6"></i></div>
            <div class="tile-title">Pneumatics</div>
            <div class="tile-desc">Pneumatic system sim</div>
        </a>
    </div>

    <!-- Information Section -->
    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i data-lucide="info" class="w-5 h-5 text-gold"></i> Information
    </h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-10">
        <a href="<?= $calcBase ?>/motors.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="zap" class="w-6 h-6"></i></div>
            <div class="tile-title">Motors</div>
            <div class="tile-desc">FRC motor specifications</div>
        </a>
        <a href="<?= $calcBase ?>/compressors.php" class="calc-tile">
            <div class="tile-icon"><i data-lucide="gauge" class="w-6 h-6"></i></div>
            <div class="tile-title">Compressors</div>
            <div class="tile-desc">Compressor specs & flow</div>
        </a>
    </div>

    <!-- Quick Links -->
    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i data-lucide="external-link" class="w-5 h-5 text-gold"></i> FRC Resources
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="calc-card">
            <h3 class="text-sm font-semibold text-gold uppercase mb-3">Game Resources</h3>
            <ul class="space-y-2 text-sm">
                <li><a href="https://www.firstinspires.org/resource-library/frc/competition-manual-qa-system" target="_blank" rel="noopener" class="text-gray-600 hover:text-gold transition-colors">Game Manual</a></li>
                <li><a href="https://frc-events.firstinspires.org/" target="_blank" rel="noopener" class="text-gray-600 hover:text-gold transition-colors">FRC Events</a></li>
                <li><a href="https://www.statbotics.io/" target="_blank" rel="noopener" class="text-gray-600 hover:text-gold transition-colors">Statbotics</a></li>
            </ul>
        </div>
        <div class="calc-card">
            <h3 class="text-sm font-semibold text-gold uppercase mb-3">Community</h3>
            <ul class="space-y-2 text-sm">
                <li><a href="https://www.chiefdelphi.com/" target="_blank" rel="noopener" class="text-gray-600 hover:text-gold transition-colors">Chief Delphi</a></li>
                <li><a href="https://www.thebluealliance.com/" target="_blank" rel="noopener" class="text-gray-600 hover:text-gold transition-colors">The Blue Alliance</a></li>
                <li><a href="https://openalliance.frc971.org/" target="_blank" rel="noopener" class="text-gray-600 hover:text-gold transition-colors">Open Alliance</a></li>
            </ul>
        </div>
        <div class="calc-card">
            <h3 class="text-sm font-semibold text-gold uppercase mb-3">Vendors</h3>
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
