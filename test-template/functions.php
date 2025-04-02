<?php
    function enqueue_theme_styles() {
        wp_enqueue_style('theme-style', get_stylesheet_directory_uri() . '/style.css', array(), '1.0', 'all');
    }
    add_action('wp_enqueue_scripts', 'enqueue_theme_styles');
?>