<?php
if (!isset($teamCourses)):
    ?>
    <script>
        tailwind.config = { theme: { extend: { colors: { 'custom-yellow': '#E5AE32' } } } }
    </script>
<?php endif; ?>

<footer style="<?php if (!isset($index)){ echo 'margin-top: 20vh'; } ?>" class="relative bg-white">

    <!-- üstte ince sarı parıltı -->
    <div class="pointer-events-none absolute -top-2 left-0 right-0 h-2 bg-gradient-to-r from-transparent via-custom-yellow/40 to-transparent"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 lg:py-14">
        <!-- GRID: mobile 1, md 2, lg 5 -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 sm:gap-10">

            <!-- Marka / Açıklama -->
            <div class="lg:col-span-2">
                <a class="flex items-center space-x-2" href="<?= BASE_URL ?>">
                    <span class="rookieverse">FRC ROOKIEVERSE</span>
                </a>
                <p class="mt-4 text-gray-600 max-w-prose leading-relaxed text-sm sm:text-base">
                    FRC takımlarının hazırladığı <b>kurslar</b>, <b>dokümanlar</b> ve <b>oyunlarla</b> robotik öğrenimini hızlandır.
                </p>
                <p class="mt-4 text-gray-600 max-w-prose leading-relaxed text-sm sm:text-base">
                    Made by <br>
                    <a class="text-custom-yellow font-bold hover:underline" href="https://www.linkedin.com/in/bedirhantemix/" rel="noopener" target="_blank">Bedirhan Temiz</a> ve
                    <a class="text-custom-yellow font-bold hover:underline" href="https://www.linkedin.com/in/mustafa-deniz-buksur/" rel="noopener" target="_blank">Mustafa Deniz Buksur</a>
                </p>
            </div>

            <!-- Öğren (accordion on mobile) -->
            <div>
                <details class="group lg:open">
                    <summary class="flex items-center justify-between cursor-pointer select-none py-2 lg:py-0">
                        <h4 class="text-sm sm:text-base font-semibold tracking-wider uppercase" style="color:#E5AE32">Öğren</h4>
                        <svg class="ml-2 h-5 w-5 text-gray-500 lg:hidden transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
                    </summary>
                    <ul class="mt-2 lg:mt-4 space-y-2 sm:space-y-3 text-gray-600">
                        <li><a class="hover:text-custom-yellow inline-flex items-center gap-2" href="<?= BASE_URL ?>/courses.php"><span class="text-custom-yellow">•</span>Kurslar</a></li>
                        <li><a class="hover:text-custom-yellow inline-flex items-center gap-2" href="<?= BASE_URL ?>/games.php"><span class="text-custom-yellow">•</span>Oyunlar</a></li>
                        <li><a class="hover:text-custom-yellow inline-flex items-center gap-2" href="<?= BASE_URL ?>/teams.php"><span class="text-custom-yellow">•</span>Takımlar</a></li>
                        <li><a class="hover:text-custom-yellow inline-flex items-center gap-2" href="<?= BASE_URL ?>/frc-terms.php"><span class="text-custom-yellow">•</span>FRC Sözlük</a></li>
                    </ul>
                </details>
            </div>

            <!-- Topluluk -->
            <div>
                <details class="group lg:open">
                    <summary class="flex items-center justify-between cursor-pointer select-none py-2 lg:py-0">
                        <h4 class="text-sm sm:text-base font-semibold tracking-wider uppercase" style="color:#E5AE32">Topluluk</h4>
                        <svg class="ml-2 h-5 w-5 text-gray-500 lg:hidden transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
                    </summary>
                    <ul class="mt-2 lg:mt-4 space-y-2 sm:space-y-3 text-gray-600">
                        <li><a class="hover:text-custom-yellow inline-flex items-center gap-2" href="<?= BASE_URL ?>/#contributors"><span class="text-custom-yellow">•</span>Destekleyen Takımlar</a></li>
                        <li><a class="hover:text-custom-yellow inline-flex items-center gap-2" href="<?= BASE_URL ?>/contact.php#sss"><span class="text-custom-yellow">•</span>SSS</a></li>
                        <li><a class="hover:text-custom-yellow inline-flex items-center gap-2" href="<?= BASE_URL ?>/contact.php"><span class="text-custom-yellow">•</span>İletişim</a></li>
                    </ul>
                </details>
            </div>

            <!-- Yasal -->
            <div>
                <details class="group lg:open">
                    <summary class="flex items-center justify-between cursor-pointer select-none py-2 lg:py-0">
                        <h4 class="text-sm sm:text-base font-semibold tracking-wider uppercase" style="color:#E5AE32">Yasal</h4>
                        <svg class="ml-2 h-5 w-5 text-gray-500 lg:hidden transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
                    </summary>
                    <ul class="mt-2 lg:mt-4 space-y-2 sm:space-y-3 text-gray-600">
                        <li><a class="hover:text-custom-yellow inline-flex items-center gap-2" href="<?= BASE_URL ?>/privacy.php"><span class="text-custom-yellow">•</span>Gizlilik</a></li>
                        <li><a class="hover:text-custom-yellow inline-flex items-center gap-2" href="<?= BASE_URL ?>/terms.php"><span class="text-custom-yellow">•</span>Kullanım Şartları</a></li>
                        <li><a class="hover:text-custom-yellow inline-flex items-center gap-2" href="<?= BASE_URL ?>/cookies.php"><span class="text-custom-yellow">•</span>Çerezler</a></li>
                    </ul>
                </details>
            </div>
        </div>

        <!-- Sponsor Bölümü -->
        <div id="sponsor" class="mt-8 border-t pt-6 text-center" style="border-top: 1px solid #E5AE32">
            <div class="flex flex-col items-center justify-center gap-4">
                <img
                        src="assets/images/inetmar.png"
                        alt="Sponsor Logo"
                        class="h-16 sm:h-20 lg:h-24 object-contain transition-transform duration-300 ease-in-out hover:cursor-pointer hover:scale-105"
                        loading="lazy"
                >
                <p class="text-gray-600 text-sm sm:text-base px-2">
                    RookieVerse'ün gelişimine katkılarından dolayı değerli sponsorumuz
                    <span id="sponsor-text" class="hover:cursor-pointer font-bold" style="color:#E5AE32">İnetmar</span>'a içten teşekkürler!
                </p>
            </div>
        </div>

        <!-- MAT Robotics Hakları -->
        <p class="text-gray-600 text-center mt-10 sm:mt-12 text-sm sm:text-base">
            Tüm hakları <b>MAT Robotics</b>'e aittir. © 2025
        </p>
    </div>

    <!-- JavaScript -->
    <script>
        // details: desktop'ta otomatik açık tut (lg ve üzeri)
        (function () {
            const sync = () => {
                const wide = window.matchMedia('(min-width: 1024px)').matches; // lg
                document.querySelectorAll('footer details').forEach(d => {
                    if (wide) d.setAttribute('open','');
                    else d.removeAttribute('open');
                });
            };
            sync();
            window.addEventListener('resize', () => { clearTimeout(window.__footerTO); window.__footerTO = setTimeout(sync, 150); });

            // Sponsor link güvenli açılış
            const s1 = document.getElementById('sponsor-text');
            const s2 = document.getElementById('sponsor');
            s1?.addEventListener('click', () => window.open('https://www.inetmar.com/', '_blank','noopener'));
            s2?.addEventListener('click', (e) => {
                // içerdeki linke veya butona tıklandıysa tekrar açma
                if (e.target?.closest('a,button')) return;
                window.open('https://www.inetmar.com/', '_blank','noopener');
            });
        })();
    </script>
</footer>
