    </main>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Custom JS -->
    <script src="<?= BASE_URL ?>/public/js/app.js"></script>
    
    <?php if (isset($additionalJs)): ?>
        <?= $additionalJs ?>
    <?php endif; ?>
</body>
</html>