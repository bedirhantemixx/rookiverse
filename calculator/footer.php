</main>

<div class="bg-gold-light border-t border-gold/20 mt-12">
    <div class="max-w-7xl mx-auto px-4 py-4 text-center text-sm text-gray-600">
        <p><strong>Disclaimer:</strong> <?= __('calc.disclaimer') ?></p>
    </div>
</div>

<?php include __DIR__ . '/../footer.php'; ?>

<script>
window.T = <?= json_encode(array_filter(rv_get_translations(), function($k) { return strpos($k, 'calc.js.') === 0; }, ARRAY_FILTER_USE_KEY), JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="<?= $calcBase ?>/js/shared.js"></script>
<?php if (isset($calcScripts)): ?>
    <?php foreach ($calcScripts as $script): ?>
        <script src="<?= $calcBase ?>/js/<?= $script ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<script>
    // Initialize Lucide icons
    lucide.createIcons();
</script>
</body>
</html>
