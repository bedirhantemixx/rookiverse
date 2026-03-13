</main>

<div class="bg-gold-light border-t border-gold/20 mt-12">
    <div class="max-w-7xl mx-auto px-4 py-4 text-center text-sm text-gray-600">
        <p><strong>Disclaimer:</strong> These calculators are intended as design aids only. Always validate results with real-world testing. Actual performance may vary due to factors not modeled here.</p>
    </div>
</div>

<?php include __DIR__ . '/../footer.php'; ?>

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
