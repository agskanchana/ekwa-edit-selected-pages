<?php
/*
Plugin Name: Ekwa Edit Selected Pages
Plugin URI:
Description: Admin can select pages which  user role "editor" can edit rest of the pages and admin menus are hidden
Author URI: www.linkedin.com/in/sameera-kanchana-3b4660198
Version: 1.0.1
Author: Sameera

*/

require 'includes/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/agskanchana/ekwa-edit-selected-pages/',
	__FILE__,
	'ekwa-edit-selected-pages'
);




function custom_pages_selection_menu() {
    // Add a new option page under the "Settings" menu
    add_options_page(
        'Page Selection',        // Page title
        'Page Selection',        // Menu title
        'manage_options',        // Capability required to view the menu
        'custom-page-selection', // Menu slug
        'custom_page_selection_callback'  // Function to display the page content
    );
  }
  add_action('admin_menu', 'custom_pages_selection_menu');

  // Display the content of the settings page
  function custom_page_selection_callback() {
    // Check if user has permission
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save settings when the form is submitted
    if (isset($_POST['custom_pages'])) {
        $selected_pages = $_POST['custom_pages'];
        update_option('custom_selected_pages', $selected_pages); // Save the selected pages in the database
    }

    // Get all pages
    $pages = get_pages();

    // Retrieve the selected pages from the database
    $selected_pages = get_option('custom_selected_pages', []);

    ?>
    <div class="wrap">
        <h1>Select Pages</h1>
        <form method="POST" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Select Pages</th>
                    <td>
                        <select name="custom_pages[]" multiple="multiple" style="width: 300px; height: 200px;">
                            <?php foreach ($pages as $page) : ?>
                                <option value="<?php echo esc_attr($page->ID); ?>"
                                    <?php echo (in_array($page->ID, $selected_pages)) ? 'selected="selected"' : ''; ?>>
                                    <?php echo esc_html($page->post_title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p>Control Click  to select multiple pages.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
  }




// Hide pages in the Pages list except the allowed ones for editors
function hide_unallowed_pages_in_list($query) {
  // Get the current user object
  $user = wp_get_current_user();

  // Check if the user has the role 'editor' and is in the admin area
  if (in_array('editor', (array) $user->roles) && is_admin() && $query->is_main_query() && $query->get('post_type') === 'page') {

    $selected_pages = get_option('custom_selected_pages', []);

    if (!empty($selected_pages)) {

      $allowed_pages = $selected_pages; // Example page IDs

      // Modify the query to show only the allowed pages
      $query->set('post__in', $allowed_pages);
    }
  }
}
add_action('pre_get_posts', 'hide_unallowed_pages_in_list');



function hide_all_menus_except_pages() {
  // Get the current user object
  $user = wp_get_current_user();

  // Check if the user has the role 'editor'
  if (in_array('editor', (array) $user->roles)) {

      // Access the global $menu variable which holds the list of admin menus
      global $menu;

      // Define the allowed menu slug for "Pages"
      $allowed_menu_slug = 'edit.php?post_type=page';

      // Loop through each menu item
      foreach ($menu as $menu_item) {
          $menu_slug = $menu_item[2]; // The slug is the third item in the $menu array

          // Remove the menu if it's not the "Pages" menu
          if ($menu_slug !== $allowed_menu_slug) {
              remove_menu_page($menu_slug);
          }
      }
      remove_submenu_page('edit.php?post_type=page', 'post-new.php?post_type=page');
  }
}
add_action('admin_menu', 'hide_all_menus_except_pages', 999);


