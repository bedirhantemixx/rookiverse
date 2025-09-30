<script>
    tailwind.config = {
        theme: { extend: { colors: { 'custom-yellow': '#E5AE32' } } }
    }
</script>

<footer style="<?php
    if (!isset($index)){
        echo 'margin-top: 10vh';
    }
?>" class="relative bg-white ">
    <!-- üstte ince sarı parıltı -->
    <div class=" pointer-events-none absolute -top-2 left-0 right-0 h-2 bg-gradient-to-r from-transparent via-custom-yellow/40 to-transparent"></div>

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
                    Bu platformun arkasındaki zihinler:<br>
                    <a style="color: #E5AE32" href="https://www.linkedin.com/in/bedirhantemix/"><b>Bedirhan Temiz</b></a> ve
                    <a style="color: #E5AE32" href="https://www.linkedin.com/in/mustafa-deniz-buksur/"><b>Mustafa Deniz Buksur</b></a>


                </p>

            </div>

            <!-- Öğren -->
            <div>
                <h4 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">Öğren</h4>
                <ul class="mt-4 space-y-3 text-gray-600">
                    <li><a class="hover:text-custom-yellow" href="<?php echo BASE_URL; ?>/courses.php">Kurslar</a></li>
                    <li><a class="hover:text-custom-yellow" href="<?php echo BASE_URL; ?>/games">Oyunlar</a></li>
                    <li><a class="hover:text-custom-yellow" href="<?php echo BASE_URL; ?>/teams.php">Takımlar</a></li>
                </ul>
            </div>

            <!-- Topluluk -->
            <div>
                <h4 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">Topluluk</h4>
                <ul class="mt-4 space-y-3 text-gray-600">
                    <li><a class="hover:text-custom-yellow" href="<?php echo BASE_URL; ?>/#contributors">Destekleyen Takımlar</a></li>
                    <li><a class="hover:text-custom-yellow" href="<?php echo BASE_URL; ?>/contact.php#sss">SSS</a></li>
                    <li><a class="hover:text-custom-yellow" href="<?php echo BASE_URL; ?>/contact.php">İletişim</a></li>
                </ul>
            </div>

            <!-- Yasal -->
            <div>
                <h4 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">Yasal</h4>
                <ul class="mt-4 space-y-3 text-gray-600">
                    <li><a class="hover:text-custom-yellow" href="<?php echo BASE_URL; ?>/privacy.php">Gizlilik</a></li>
                    <li><a class="hover:text-custom-yellow" href="<?php echo BASE_URL; ?>/terms.php">Kullanım Şartları</a></li>
                    <li><a class="hover:text-custom-yellow" href="<?php echo BASE_URL; ?>/cookies.php">Çerezler</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>
