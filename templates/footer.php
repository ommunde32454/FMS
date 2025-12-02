</main> <footer class="bg-gray-800 text-gray-400 py-6 mt-auto">
        <div class="container mx-auto px-4 flex flex-col md:flex-row justify-between items-center text-sm">
            <div>
                &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>.
            </div>
            <div class="mt-2 md:mt-0">
                <span class="mr-4">v<?php echo APP_VERSION; ?></span>
                <a href="#" class="hover:text-white">Help</a>
            </div>
        </div>
    </footer>

    <script src="<?php echo BASE_URL; ?>public/js/app.js"></script>
    
    <?php if(isset($extra_scripts)) echo $extra_scripts; ?>

</body>
</html>