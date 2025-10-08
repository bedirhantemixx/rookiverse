<?php
if (!isset($teamCourses)):
?>
<script>
    tailwind.config = {
        theme: { extend: { colors: { 'custom-yellow': '#E5AE32' } } }
    }
</script>
<?php endif; ?>

<footer style="<?php
if (!isset($index)){
    echo 'margin-top: 20vh';
}
?>" class="relative bg-white">

    <!-- üstte ince sarı parıltı -->
    <div class="pointer-events-none absolute -top-2 left-0 right-0 h-2 bg-gradient-to-r from-transparent via-custom-yellow/40 to-transparent"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10">

            <!-- Marka / Açıklama -->
            <div class="lg:col-span-2">
                <a class="flex items-center space-x-2" href="<?php echo BASE_URL; ?>">
                    <span class="rookieverse">FRC ROOKIEVERSE</span>
                </a>
                <p class="mt-4 text-gray-600 max-w-md">
                    FRC takımlarının hazırladığı <b>kurslar</b>, <b>dokümanlar</b> ve <b>oyunlarla</b> robotik öğrenimini hızlandır.
                </p>
                <p class="mt-4 text-gray-600 max-w-md">
                    Made by <br>
                    <a style="color: #E5AE32" href="https://www.linkedin.com/in/bedirhantemix/"><b>Bedirhan Temiz</b></a> ve
                    <a style="color: #E5AE32" href="https://www.linkedin.com/in/mustafa-deniz-buksur/"><b>Mustafa Deniz Buksur</b></a>
                </p>
            </div>

            <!-- Öğren -->
            <div>
                <h4 style="color: #E5AE32; font-weight: 700" class="text-sm font-semibold text-gray-700 tracking-wider uppercase">Öğren</h4>
                <ul class="mt-4 space-y-3 text-gray-600">
                    <li class="flex items-center">
                        <span class="text-custom-yellow mr-2">•</span>
                        <a class="hover:text-custom-yellow" href="<?php echo BASE_URL; ?>/courses.php">Kurslar</a>
                    </li>
                    <li class="flex items-center">
                        <span class="text-custom-yellow mr-2">•</span>
                        <a class="hover:text-custom-yellow" href="<?php echo BASE_URL; ?>/games.php">Oyunlar</a>
                    </li>
                    <li class="flex items-center">
                        <span class="text-custom-yellow mr-2">•</span>
                        <a class="hover:text-custom-yellow" href="<?php echo BASE_URL; ?>/teams.php">Takımlar</a>
                    </li>
                    <li class="flex items-center">
                        <span class="text-custom-yellow mr-2">•</span>
                        <a class="hover:text-custom-yellow" href="<?php echo BASE_URL; ?>/frc-terms.php">FRC Sözlük</a>
                    </li>
                </ul>
            </div>

            <!-- Topluluk -->
            <div>
                <h4 style="color: #E5AE32; font-weight: 700" class="text-sm font-semibold text-gray-700 tracking-wider uppercase">Topluluk</h4>
                <ul class="mt-4 space-y-3 text-gray-600">
                    <li class="flex items-center">
                        <span class="text-custom-yellow mr-2">•</span>
                        <a class="hover:text-custom-yellow" href="<?php echo BASE_URL; ?>/#contributors">Destekleyen Takımlar</a>
                    </li>
                    <li class="flex items-center">
                        <span class="text-custom-yellow mr-2">•</span>
                        <a class="hover:text-custom-yellow" href="<?php echo BASE_URL; ?>/contact.php#sss">SSS</a>
                    </li>
                    <li class="flex items-center">
                        <span class="text-custom-yellow mr-2">•</span>
                        <a class="hover:text-custom-yellow" href="<?php echo BASE_URL; ?>/contact.php">İletişim</a>
                    </li>
                </ul>
            </div>

            <!-- Yasal -->
            <div>
                <h4 style="color: #E5AE32; font-weight: 700" class="text-sm font-semibold text-gray-700 tracking-wider uppercase">Yasal</h4>
                <ul class="mt-4 space-y-3 text-gray-600">
                    <li class="flex items-center">
                        <span class="text-custom-yellow mr-2">•</span>
                        <a class="hover:text-custom-yellow" href="<?php echo BASE_URL; ?>/privacy.php">Gizlilik</a>
                    </li>
                    <li class="flex items-center">
                        <span class="text-custom-yellow mr-2">•</span>
                        <a class="hover:text-custom-yellow" href="<?php echo BASE_URL; ?>/terms.php">Kullanım Şartları</a>
                    </li>
                    <li class="flex items-center">
                        <span class="text-custom-yellow mr-2">•</span>
                        <a class="hover:text-custom-yellow" href="<?php echo BASE_URL; ?>/cookies.php">Çerezler</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Sponsor Bölümü -->
        <div id="sponsor" style="border-top: 1px solid #E5AE32" class="mt-8 border-t text-center">
            <div class="flex justify-center items-center">
                <img
                        src="assets/images/inetmar.png"
                        alt="Sponsor Logo"
                        class="h-24 object-contain transition-transform duration-300 ease-in-out hover:cursor-pointer hover:scale-110"
                >
            </div>

            <p class="text-gray-600 text-sm mb-6">
                RookieVerse'ün gelişimine katkılarından dolayı değerli sponsorumuz <span id="sponsor-text" class="hover:cursor-pointer" style="color: rgb(229 174 50); font-weight: 700">İnetmar</span>'a içten teşekkürler!
            </p>
        </div>

        <!-- MAT Robotics Hakları -->
        <p class="text-gray-600 text-center mt-12">
            Tüm hakları <b>MAT Robotics</b>'e aittir. © 2025
        </p>
    </div>

    <!-- JavaScript -->
    <script>
        document.querySelector('#sponsor-text').addEventListener('click', () => {
            window.open('https://www.inetmar.com/', '_blank').focus();
        });
        document.querySelector('#sponsor').addEventListener('click', () => {
            window.open('https://www.inetmar.com/', '_blank').focus();
        });
    </script>
</footer>
