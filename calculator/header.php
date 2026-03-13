<?php
require_once __DIR__ . '/../preload.php';
require_once __DIR__ . '/../config.php';
$calcBase = BASE_URL . '/calculator';
?>
<!DOCTYPE html>
<html lang="<?= CURRENT_LANG ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - MechTools' : 'MechTools - FRC Calculator Suite' ?></title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/assets/images/rokiverse_icon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'gold': '#E5AE32',
                        'gold-light': '#FDF6E3',
                        'gold-dark': '#C4922A',
                        'dark': '#1a202c',
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="<?= $calcBase ?>/css/calculator.css">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

<?php include __DIR__ . '/../navbar.php'; ?>

<!-- Calculator sub-nav -->
<div class="bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center h-12 gap-6 overflow-x-auto text-sm font-medium whitespace-nowrap scrollbar-hide">
            <a href="<?= $calcBase ?>/" class="text-gray-600 hover:text-gold transition-colors flex items-center gap-1.5">
                <i data-lucide="grid-3x3" class="w-4 h-4"></i> All Tools
            </a>
            <span class="text-gray-300">|</span>
            <a href="<?= $calcBase ?>/belts.php" class="text-gray-500 hover:text-gold transition-colors">Belts</a>
            <a href="<?= $calcBase ?>/chain.php" class="text-gray-500 hover:text-gold transition-colors">Chain</a>
            <a href="<?= $calcBase ?>/gears.php" class="text-gray-500 hover:text-gold transition-colors">Gears</a>
            <a href="<?= $calcBase ?>/ratio.php" class="text-gray-500 hover:text-gold transition-colors">Ratio</a>
            <a href="<?= $calcBase ?>/ratio-finder.php" class="text-gray-500 hover:text-gold transition-colors">Ratio Finder</a>
            <a href="<?= $calcBase ?>/flywheel.php" class="text-gray-500 hover:text-gold transition-colors">Flywheel</a>
            <a href="<?= $calcBase ?>/intake.php" class="text-gray-500 hover:text-gold transition-colors">Intake</a>
            <a href="<?= $calcBase ?>/arm.php" class="text-gray-500 hover:text-gold transition-colors">Arm</a>
            <a href="<?= $calcBase ?>/linear.php" class="text-gray-500 hover:text-gold transition-colors">Linear</a>
            <a href="<?= $calcBase ?>/drive.php" class="text-gray-500 hover:text-gold transition-colors">Drive</a>
            <a href="<?= $calcBase ?>/pneumatics.php" class="text-gray-500 hover:text-gold transition-colors">Pneumatics</a>
            <span class="text-gray-300">|</span>
            <a href="<?= $calcBase ?>/motors.php" class="text-gray-500 hover:text-gold transition-colors">Motors</a>
            <a href="<?= $calcBase ?>/compressors.php" class="text-gray-500 hover:text-gold transition-colors">Compressors</a>
        </div>
    </div>
</div>

<main class="flex-1">
