            </main>
            
            <!-- Footer -->
            <footer class="app-footer">
                <div class="footer-content">
                    <span>&copy; <?= date('Y') ?> <?= APP_NAME ?> — <?= APP_SCHOOL ?></span>
                    <span>v<?= APP_VERSION ?> | <a href="mailto:<?= APP_SUPPORT_EMAIL ?>"><?= APP_SUPPORT_EMAIL ?></a></span>
                </div>
            </footer>
        </div><!-- /.app-main -->
    </div><!-- /.app-layout -->
    
    <!-- Toast Container -->
    <div class="toast-container"></div>
    
    <!-- JavaScript -->
    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/validation.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/charts.js"></script>
    
    <?php if (isset($_SESSION['toast_message'])): ?>
    <script>
        showToast(
            '<?= addslashes($_SESSION['toast_title'] ?? 'Notification') ?>',
            '<?= addslashes($_SESSION['toast_message']) ?>',
            '<?= addslashes($_SESSION['toast_type'] ?? 'info') ?>'
        );
    </script>
    <?php 
        unset($_SESSION['toast_message'], $_SESSION['toast_title'], $_SESSION['toast_type']);
    endif; 
    ?>
</body>
</html>
