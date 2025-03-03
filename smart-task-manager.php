<?php
/**
 * Plugin Name: Smart Task Manager
 * Plugin URI: http://wordpress.org/
 * Description: A simple WordPress task manager plugin with AJAX and React.
 * Version: 1.0
 * WordPress Requires at least: 5.8
 * Requires PHP: 5.6.20
 * Author: Turjo
 * Text Domain: task-manager-plugin
 * License: turjo007
 */
global $wpdb;
define('WP_REACT_PLUGIN_TABLE', $wpdb->prefix . 'smart_task_manager');
 class WP_Task_Manager{
    function __construct() {
        // Hook to create the table on plugin activation
        register_activation_hook(__FILE__, [$this, 'create_task_table']);
        add_action('admin_menu', [$this, 'admin_menu_callback']);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue']);
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }


    // Create table on plugin activation
    public function create_task_table() {
        global $wpdb;
        $table_name = WP_REACT_PLUGIN_TABLE;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            status ENUM('Pending', 'Completed') DEFAULT 'Pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
    

    public function admin_enqueue() {
        wp_enqueue_script('wp-task-manager-plugin-js', plugins_url('src/build/admin.js', __FILE__), ['wp-element'], '1.0', true);
        wp_localize_script('wp-task-manager-plugin-js', 'wpTaskManagerPlugin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'rest_url' => get_rest_url(null, 'wp-task-manager-plugin-js/v1/tasks'),
            'nonce' => wp_create_nonce('wp_rest')
        ]);
    }

    // admin menue
    public function admin_menu_callback(){
        add_menu_page(
            __( 'Wp Task Manager', 'wp-task-manager'),
            __('Task Manager', 'wp-task-manager'),
            'manage_options',
            'wp-task-manager',
            [$this, 'admin_menu_callback_'],
            '',
            5
        );
    }

    public function admin_menu_callback_(){

        ?>
        
            <div class="wrap">
                <div id="wp-task-manager-plugin-id"></div>
            </div>

        <?php

    }

 }

 new WP_Task_Manager();

