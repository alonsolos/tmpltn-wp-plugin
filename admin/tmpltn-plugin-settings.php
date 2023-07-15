<?php
// tmpltn-plugin-settings.php

// Add a new submenu page under the Settings menu
function tmpltn_plugin_add_submenu_page() {
    add_submenu_page(
        'options-general.php',     // Parent menu slug
        'Tmpltn Plugin Settings',  // Page title
        'Tmpltn Plugin',           // Menu title
        'manage_options',          // Capability required
        'tmpltn-plugin-settings',  // Menu slug
        'tmpltn_plugin_render_settings_page' // Callback function to render the settings page
    );
}
add_action('admin_menu', 'tmpltn_plugin_add_submenu_page');

// Render the settings page
function tmpltn_plugin_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Tmpltn Plugin Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('tmpltn_plugin_settings'); // Output the settings fields
            do_settings_sections('tmpltn-plugin-settings'); // Output the settings sections
            submit_button(); // Output the submit button
            ?>
        </form>
    </div>
    <?php
}

// Register and initialize the plugin settings
function tmpltn_plugin_register_settings() {
    // Define the settings field
    add_settings_section(
        'tmpltn_plugin_general_settings',     // Section ID
        'General Settings',                   // Section title
        'tmpltn_plugin_render_general_section', // Callback function to render the section
        'tmpltn-plugin-settings'              // Page slug
    );

    add_settings_field(
        'tmpltn_plugin_square_access_token',      // Field ID
        'Square Access Token',                    // Field label
        'tmpltn_plugin_render_square_access_token_field', // Callback function to render the field
        'tmpltn-plugin-settings',                 // Page slug
        'tmpltn_plugin_general_settings'          // Section ID
    );

    // Register the settings field
    register_setting(
        'tmpltn_plugin_settings',         // Option group
        'tmpltn_plugin_square_access_token',  // Option name
        'sanitize_callback_function'      // Sanitization callback function
    );
}
add_action('admin_init', 'tmpltn_plugin_register_settings');

// Render the general settings section
function tmpltn_plugin_render_general_section() {
    echo '<p>Enter your Square Access Token below:</p>';
}

// Render the Square Access Token field
function tmpltn_plugin_render_square_access_token_field() {
    $access_token = get_option('tmpltn_plugin_square_access_token');
    ?>
    <input type="text" name="tmpltn_plugin_square_access_token" value="<?php echo esc_attr($access_token); ?>" />
    <?php
}

// Sanitization callback function
function sanitize_callback_function($input) {
    // Perform sanitization/validation logic here
    return $input;
}