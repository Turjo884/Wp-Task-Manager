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

class WP_Task_Manager {
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

    // Register REST API Endpoints
    public function register_rest_routes() {
        register_rest_route('smart-task-manager/v1', '/tasks', [
            'methods' => 'GET',
            'callback' => [$this, 'get_tasks'],
            'permission_callback' => '__return_true'
        ]);

        register_rest_route('smart-task-manager/v1', '/tasks', [
            'methods' => 'POST',
            'callback' => [$this, 'add_task'],
            'permission_callback' => function () {
                return current_user_can('manage_options');
            }
        ]);

        register_rest_route('smart-task-manager/v1', '/tasks/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'delete_task'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('smart-task-manager/v1', '/tasks/(?P<id>\d+)', [
            'methods' => 'PUT',
            'callback' => [$this, 'update_task_status'],
            'permission_callback' => '__return_true',
        ]);        
        

    }

     // Fetch all tasks
     public function get_tasks() {
        global $wpdb;
        $table_name = WP_REACT_PLUGIN_TABLE;

        $tasks = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

        return rest_ensure_response($tasks);
    }


    // Add a new task
    public function add_task(WP_REST_Request $request) {
        global $wpdb;
        $table_name = WP_REACT_PLUGIN_TABLE;
        $params = $request->get_params();

        $inserted = $wpdb->insert(
            $table_name,
            [
                'title'       => sanitize_text_field($params['title']),
                'description' => sanitize_textarea_field($params['description']),
                'status'      => 'Pending'
            ],
            ['%s', '%s', '%s']
        );

        if (!$inserted) {
            return rest_ensure_response(['error' => 'Failed to add task']);
        }

        return rest_ensure_response(['success' => 'Task added', 'task_id' => $wpdb->insert_id]);
    }


    // Delete Task
    public function delete_task($request) {
        global $wpdb;
        $table_name = WP_REACT_PLUGIN_TABLE;
        $id = intval($request['id']);
    
        $deleted = $wpdb->delete($table_name, ['id' => $id]);
    
        if ($deleted) {
            return rest_ensure_response(['success' => true, 'message' => 'Task deleted successfully']);
        } else {
            return new WP_Error('delete_failed', 'Failed to delete task', ['status' => 500]);
        }
    }
    

    // Mark As Complete
    public function update_task_status($request) {
        global $wpdb;
        $table_name = WP_REACT_PLUGIN_TABLE;
        $id = intval($request['id']);
        $status = sanitize_text_field($request['status']);
    
        $updated = $wpdb->update($table_name, ['status' => $status], ['id' => $id]);
    
        if ($updated !== false) {
            return rest_ensure_response(['success' => true, 'message' => 'Task updated successfully']);
        } else {
            return new WP_Error('update_failed', 'Failed to update task', ['status' => 500]);
        }
    }
    
    


    public function admin_enqueue() {
        wp_enqueue_script('wp-task-manager-plugin-js', plugins_url('src/build/admin.js', __FILE__), ['wp-element'], '1.0', true);
        wp_localize_script('wp-task-manager-plugin-js', 'wpTaskManagerPlugin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'rest_url' => get_rest_url(null, 'smart-task-manager/v1/tasks'),
            'nonce' => wp_create_nonce('wp_rest')
        ]);
    }

    // Admin menu
    public function admin_menu_callback() {
        add_menu_page(
            __('Wp Task Manager', 'wp-task-manager'),
            __('Task Manager', 'wp-task-manager'),
            'manage_options',
            'wp-task-manager',
            [$this, 'admin_menu_callback_'],
            '',
            5
        );
    }

    public function admin_menu_callback_() {
        ?>
        <div class="wrap">
            <div id="wp-task-manager-plugin-id"></div>
        </div>
        <?php
    }
}

// Initialize the plugin
new WP_Task_Manager();
