    <!-- owner_footer.php -->
    </div> <!-- Close container -->
    
    <div style="text-align: center; margin-top: 50px; padding: 20px; color: #666; border-top: 1px solid #eee;">
        <p>Apartment Management System v1.0 © <?php echo date('Y'); ?></p>
        <p style="font-size: 12px; margin-top: 5px;">
            <a href="index.php" style="color: #667eea; text-decoration: none;">Home</a> | 
            <a href="owner_dashboard.php" style="color: #667eea; text-decoration: none;">Dashboard</a> | 
            <a href="logout.php" style="color: #667eea; text-decoration: none;">Logout</a>
        </p>
    </div>
    
    <script>
        // Auto close alerts
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.display = 'none';
            });
        }, 5000);
        
        // Confirm before delete
        document.querySelectorAll('a[onclick*="confirm"]').forEach(function(link) {
            link.addEventListener('click', function(e) {
                if (!confirm('Are you sure?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>