<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Oyunlar • Rookieverse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'custom-yellow': '#E5AE32',
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/navbar.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/index.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --rv-card: 255 255 255; --rv-muted: 248 250 252; }
        body { font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; }
        .rv-container { max-width: 1100px; }
        .rv-card { background: rgb(var(--rv-card)); box-shadow: 0 10px 25px rgba(2,6,23,0.08); }
        .rv-chip { border: 1px solid #e5e7eb; }
        .rv-btn-primary { background: #E5AE32; color:#111827; }
        .rv-btn-primary:hover { filter: brightness(0.95); }
        .rv-ring:hover { box-shadow: 0 0 0 3px rgba(229,174,50,0.35); }
    </style>
</head>
<body class="bg-slate-50">
<?php require_once 'navbar.php'?>
<div class="rv-container mx-auto px-4 py-10">
    <div class="flex items-center gap-3 mb-6">
        <div class="h-10 w-10 rounded-2xl bg-custom-yellow/20 grid place-items-center">
            <i data-lucide="gamepad-2" class="w-6 h-6 text-custom-yellow"></i>
        </div>
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">Oyunlar</h1>
            <p class="text-slate-600">FRC terimleriyle öğrenirken eğlen. İstediğin oyunu seç ve hemen başla.</p>
        </div>
    </div>

    <div class="rv-card rounded-2xl p-4 sm:p-5 mb-6">
        <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
            <div class="flex-1 relative">
                <input id="search" type="text" placeholder="Oyun ara (ör. harf, quiz, tahmin, eşleştirme)" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 pr-10 outline-none focus:border-custom-yellow" />
                <i data-lucide="search" class="w-5 h-5 absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            </div>
            <div class="flex flex-wrap gap-2" id="filters">
                <button data-filter="all" class="px-3 py-2 rounded-xl bg-white rv-chip text-slate-700 hover:bg-slate-50">Tümü</button>
                <button data-filter="kelime" class="px-3 py-2 rounded-xl bg-white rv-chip text-slate-700 hover:bg-slate-50">Kelime</button>
                <button data-filter="tahmin" class="px-3 py-2 rounded-xl bg-white rv-chip text-slate-700 hover:bg-slate-50">Tahmin</button>
                <button data-filter="eslestirme" class="px-3 py-2 rounded-xl bg-white rv-chip text-slate-700 hover:bg-slate-50">Eşleştirme</button>
                <button data-filter="hafiza" class="px-3 py-2 rounded-xl bg-white rv-chip text-slate-700 hover:bg-slate-50">Hafıza</button>
            </div>
        </div>
    </div>

    <div id="game-grid" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5"></div>
</div>

<template id="game-card-template">
    <div class="rv-card rounded-3xl overflow-hidden rv-ring transition-all">
        <div class="p-5">
            <div class="flex items-start gap-4">
                <div class="h-12 w-12 rounded-2xl bg-slate-100 grid place-items-center shrink-0">
                    <i data-lucide="sparkles" class="w-6 h-6 text-slate-700"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-lg font-semibold leading-tight truncate" data-el="title">Oyun Başlığı</h3>
                    <p class="text-slate-600 text-sm mt-1 line-clamp-2" data-el="desc">Kısa açıklama buraya gelir.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 mt-4" data-el="tags"></div>
            <div class="flex items-center justify-between mt-5">
                <div class="flex items-center gap-4 text-sm text-slate-600" data-el="meta">
                    <!-- örn: <span class="inline-flex items-center gap-1"><i data-lucide=clock></i> 3-5 dk</span> -->
                </div>
                <a class="rv-btn-primary inline-flex items-center gap-2 px-4 py-2 rounded-xl font-semibold" data-el="cta">
                    <i data-lucide="play"></i>
                    <span>Oyna</span>
                </a>
            </div>
        </div>
    </div>
</template>
<?php require_once 'footer.php'?>

<script>
    lucide.createIcons();



    window.GAMES = [
        {
            slug: 'wordGuesser',
            title: 'Harf Tahmin',
            desc: 'FRC terimlerini harf harf bularak puan topla.',
            type: 'kelime',
            minutes: '3-5',
            players: 'Tek',
            icon: 'letter-text'
        },
        {
            slug: 'multipleChoice',
            title: 'Terim Quiz',
            desc: 'Çoktan seçmeli sorularla bilgini ölç, hızlı puan kap.',
            type: 'quiz',
            minutes: '4-6',
            players: 'Tek',
            icon: 'help-circle'
        },
        {
            slug: 'guessGame',
            title: 'Takım Numarası Tahmin',
            desc: 'FRC takımlarının isimlerinden doğru takım numarasını tahmin et.',
            type: 'tahmin',
            minutes: '4-6',
            players: 'Tek / İki',
            icon: 'hash'
        },
        {
            slug: 'flipGame',
            title: 'Hafıza Kartları',
            desc: 'Kartları çevir, aynı terimleri yakala.',
            type: 'hafiza',
            minutes: '3-5',
            players: 'Tek / İki',
            icon: 'gallery-vertical-end'
        }
    ];

    const grid = document.getElementById('game-grid');
    const template = document.getElementById('game-card-template');
    const search = document.getElementById('search');
    const filters = document.getElementById('filters');

    function tag(text) {
        const s = document.createElement('span');
        s.className = 'text-xs px-2.5 py-1 rounded-full bg-slate-100 text-slate-700';
        s.textContent = text;
        return s;
    }

    function meta(icon, text) {
        const w = document.createElement('span');
        w.className = 'inline-flex items-center gap-1';
        const i = document.createElement('i');
        i.setAttribute('data-lucide', icon);
        i.className = 'w-4 h-4';
        w.appendChild(i);
        const t = document.createTextNode(' ' + text);
        w.appendChild(t);
        return w;
    }

    function render(list) {
        grid.innerHTML = '';
        list.forEach(g => {
            const clone = template.content.cloneNode(true);
            const titleEl = clone.querySelector('[data-el="title"]');
            const descEl = clone.querySelector('[data-el="desc"]');
            const tagsEl = clone.querySelector('[data-el="tags"]');
            const metaEl = clone.querySelector('[data-el="meta"]');
            const ctaEl = clone.querySelector('[data-el="cta"]');
            const iconEl = clone.querySelector('[data-lucide="sparkles"]');

            titleEl.textContent = g.title;
            descEl.textContent = g.desc;
            tagsEl.appendChild(tag(g.type));
            metaEl.appendChild(meta('clock', `${g.minutes} dk`));
            metaEl.appendChild(meta('users', g.players));
            ctaEl.href = g.slug + '.php'; // Rookieverse yönlendirme

            if (g.icon) {
                iconEl.setAttribute('data-lucide', g.icon);
            }

            grid.appendChild(clone);
        });
        lucide.createIcons();
    }

    function applyFilters() {
        const q = (search.value || '').toLowerCase();
        const active = filters.querySelector('button[aria-pressed="true"]');
        const f = active ? active.dataset.filter : 'all';

        const out = window.GAMES.filter(g => {
            const matchesText = [g.title, g.desc, g.type].join(' ').toLowerCase().includes(q);
            const matchesFilter = f === 'all' ? true : g.type === f;
            return matchesText && matchesFilter;
        });
        render(out);
    }

    // Filtre butonları davranışı
    filters.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-filter]');
        if (!btn) return;
        filters.querySelectorAll('button').forEach(b => b.removeAttribute('aria-pressed'));
        btn.setAttribute('aria-pressed', 'true');
        applyFilters();
    });

    search.addEventListener('input', applyFilters);

    // Başlangıç: Tümü seçili
    filters.querySelector('button[data-filter="all"]').setAttribute('aria-pressed', 'true');
    render(window.GAMES);
</script>
</body>
</html>
