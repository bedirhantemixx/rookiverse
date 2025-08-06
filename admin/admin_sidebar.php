<aside class="sidebar">
    <div class="sidebar-header">
        <a href="panel.php"><span class="rookieverse">FRC ROOKIEVERSE</span></a>
    </div>
    <div class="sidebar-search">
        <input type="text" id="menu-search" class="search-input" placeholder="Menüde Ara...">
        <i data-lucide="search" class="search-icon"></i>
    </div>
    <nav class="sidebar-nav">
        <div class="menu-item">
            <div class="menu-title"><div class="flex items-center"><i data-lucide="home" class="menu-icon"></i>Dashboard</div><i data-lucide="chevron-right" class="menu-arrow"></i></div>
            <div class="submenu"><a href="panel.php" class="<?php echo ($current_page == 'panel.php') ? 'active' : ''; ?>">Kontrol Paneli</a></div>
        </div>
        <div class="menu-item">
            <div class="menu-title"><div class="flex items-center"><i data-lucide="user-cog" class="menu-icon"></i>Admin Yönetimi</div><i data-lucide="chevron-right" class="menu-arrow"></i></div>
            <div class="submenu"><a href="profile.php" class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">Bilgileri Güncelle</a><a href="#">Adminleri Yönet</a></div>
        </div>
        <div class="menu-item">
            <div class="menu-title"><div class="flex items-center"><i data-lucide="users" class="menu-icon"></i>Takımlar</div><i data-lucide="chevron-right" class="menu-arrow"></i></div>
            <div class="submenu"><a href="#">Takımları Görüntüle</a><a href="#">Takım Ekle/Çıkar</a></div>
        </div>
        <div class="menu-item">
            <div class="menu-title"><div class="flex items-center"><i data-lucide="book-open" class="menu-icon"></i>Kurslar</div><i data-lucide="chevron-right" class="menu-arrow"></i></div>
            <div class="submenu"><a href="#">Kursları Görüntüle</a><a href="#">Kurs Düzenle</a></div>
        </div>
        </nav>
</aside>