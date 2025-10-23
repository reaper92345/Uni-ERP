    </div>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo getBasePath(); ?>/assets/js/main.js"></script>
    <script>
      // Navbar toggle for mobile
      document.getElementById('navbar-toggle').onclick = function() {
        var menu = document.getElementById('navbar-menu');
        menu.classList.toggle('hidden');
      };
    </script>
</body>
</html> 