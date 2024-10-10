<?php
/*
Plugin Name: Ekwa Edit Selected Pages
Plugin URI:
Description: Admin can select pages which  user role "editor" can edit rest of the pages and admin menus are hidden
Author URI: www.linkedin.com/in/sameera-kanchana-3b4660198
Version: 1.0.2
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

    add_options_page(
        'Page Selection',
        'Page Selection',
        'manage_options',
        'custom-page-selection',
        'custom_page_selection_callback'
    );
  }
  add_action('admin_menu', 'custom_pages_selection_menu');


  function custom_page_selection_callback() {

    if (!current_user_can('manage_options')) {
        return;
    }


    if (isset($_POST['custom_pages'])) {
        $selected_pages = $_POST['custom_pages'];
        update_option('custom_selected_pages', $selected_pages); // Save the selected pages in the database
    }


    $pages = get_pages();


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





  function hide_unallowed_pages_in_list($query) {

  $user = wp_get_current_user();

  if (in_array('editor', (array) $user->roles) && is_admin() && $query->is_main_query() && $query->get('post_type') === 'page') {

    $selected_pages = get_option('custom_selected_pages', []);

    if (!empty($selected_pages)) {

      $allowed_pages = $selected_pages;


      $query->set('post__in', $allowed_pages);
    }
  }
  }
  add_action('pre_get_posts', 'hide_unallowed_pages_in_list');



  function hide_all_menus_except_pages() {

  $user = wp_get_current_user();


  if (in_array('editor', (array) $user->roles)) {

      global $menu;


      $allowed_menu_slug = 'edit.php?post_type=page';


      foreach ($menu as $menu_item) {
          $menu_slug = $menu_item[2];


          if ($menu_slug !== $allowed_menu_slug) {
              remove_menu_page($menu_slug);
          }
      }
    //   remove_submenu_page('edit.php?post_type=page', 'post-new.php?post_type=page');
  }
  }
  add_action('admin_menu', 'hide_all_menus_except_pages', 999);


