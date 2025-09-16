<?php
?>
<nav class="bg-white shadow-sm border-b sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <a class="flex items-center space-x-2" href="<?php echo BASE_URL; ?>/index.php">
                <span class="rookieverse">FRC ROOKIEVERSE</span>
            </a>
            <div class="hidden md:flex items-center space-x-8">
                <a class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-[#E5AE32] hover:bg-gray-50" href="<?php echo BASE_URL; ?>/courses.php">Kurslar</a>
                <a class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-[#E5AE32] hover:bg-gray-50" href="<?php echo BASE_URL; ?>/games.php">Oyunlar</a>
                <a class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-[#E5AE32] hover:bg-gray-50" href="<?php echo BASE_URL; ?>/season.php">2026 Sezonu</a>
                <a class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-[#E5AE32] hover:bg-gray-50" href="<?php echo BASE_URL; ?>/contact.php">İletişim</a>
                <a href="<?php echo BASE_URL; ?>/team-login.php" class="inline-flex items-center justify-center gap-2 h-9 bg-[#E5AE32] hover:bg-[#E5AE32]/90 text-white font-semibold px-6 py-2 rounded-lg">
                    <?php
                    if (isset($_SESSION['team_logged_in'])) {
                        echo "# Team " . $_SESSION['team_number'];
                    }
                    else{
                        ?>
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        <?php
                        echo 'Takım Girişi';
                    }
                    ?>

                </a>
            </div>
        </div>
    </div>
</nav>