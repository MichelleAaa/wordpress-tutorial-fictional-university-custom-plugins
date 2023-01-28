<?php

/*
  Plugin Name: Our Word Filter Plugin
  Description: Replaces a list of words.
  Version 1.0
  Author: Brad
  Author URI: https://www.udemy.com/user/bradschiff/
*/

// In the first menu in wp-admin for Words Filter there's a place you can enter a list of words.
// In Words List - Options, there's a place where you can enter whatever you want to replace all of those words with (such as s$#!@ or some type of notification of a filtered word.)

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly -- this is a common line used in plugins -- It's a security safeguard.

class OurWordFilterPlugin {
  function __construct() {
    add_action('admin_menu', array($this, 'ourMenu')); // this will update the admin_menu
    add_action('admin_init', array($this, 'ourSettings'));
    if (get_option('plugin_words_to_filter')) add_filter('the_content', array($this, 'filterLogic')); //WP has all sorts of filters that we can hook onto. This will allow us to filter or change the content of a post.
  }

  function ourSettings() {
    add_settings_section('replacement-text-section', null, null, 'word-filter-options'); // 1st is the name of the section. 2nd and 3d are label/description text, and here we leave it null. 4th argument is the slug name of the admin page that you want to display this section on.
    register_setting('replacementFields', 'replacementText'); // 2nd is the option name.
    add_settings_field('replacement-text', 'Filtered Text', array($this, 'replacementFieldHTML'), 'word-filter-options', 'replacement-text-section'); // this adds a field to the section. 
    // 1st argument - will be used as the id attribute for the element. 2nd - text the user will actually see as the label for the field. 3rd - function that will output the html for the field. 4th - slug of the page you want to show this on. 5th - section you want to output the field to (we named it up in add_settings_section())
  }

  function replacementFieldHTML() { ?>
    <input type="text" name="replacementText" value="<?php echo esc_attr(get_option('replacementText', '***')) ?>">
    <p class="description">Leave blank to simply remove the filtered words.</p>
  <?php }

  function filterLogic($content) {
    $badWords = explode(',', get_option('plugin_words_to_filter'));
    $badWordsTrimmed = array_map('trim', $badWords);
    return str_ireplace($badWordsTrimmed, esc_html(get_option('replacementText', '****')), $content); //1st argument is an array of words you want to replace. 2nd is what you want to replace them with. 3rd is the text you are performing this replacement operation on. 
  }

  function ourMenu() {
    $mainPageHook = add_menu_page('Words To Filter', 'Word Filter', 'manage_options', 'ourwordfilter', array($this, 'wordFilterPage'), 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0xMCAyMEMxNS41MjI5IDIwIDIwIDE1LjUyMjkgMjAgMTBDMjAgNC40NzcxNCAxNS41MjI5IDAgMTAgMEM0LjQ3NzE0IDAgMCA0LjQ3NzE0IDAgMTBDMCAxNS41MjI5IDQuNDc3MTQgMjAgMTAgMjBaTTExLjk5IDcuNDQ2NjZMMTAuMDc4MSAxLjU2MjVMOC4xNjYyNiA3LjQ0NjY2SDEuOTc5MjhMNi45ODQ2NSAxMS4wODMzTDUuMDcyNzUgMTYuOTY3NEwxMC4wNzgxIDEzLjMzMDhMMTUuMDgzNSAxNi45Njc0TDEzLjE3MTYgMTEuMDgzM0wxOC4xNzcgNy40NDY2NkgxMS45OVoiIGZpbGw9IiNGRkRGOEQiLz4KPC9zdmc+', 100);//second argument is text that will show up in the admin sidebar, 3rd - permission or capability that the user needs to have in order to see the page (so in this case, they need to be allowed to manage options) - 4th is slug or url name for the ending. 5th - a function that outputs the html for the page itself.  6th is the icon that will appear in the admin menu. (You could use something like 'dashicons-smiley' to use a dashicon. However, here we are using a custom svg file instead.) 7th - a number we give it that controls where our menu appears vertically -- so a small number like 1 will appear at the top, while 100 is down at the bottom. 
    add_submenu_page('ourwordfilter', 'Words To Filter', 'Words List', 'manage_options', 'ourwordfilter', array($this, 'wordFilterPage')); //1st - menu that you want to add this subpage to (so the name in add_menu_page). 2nd - actual document title to see in the tab of the browser. 3rd - text that you will see in the admin sidebar. 4th - capability needed for a user to see this page. 5th - slug or short name for this page. 6th - function that outputs the html for the page.
    // Notice that the last option includes wordFilterPage, which is the same as the main page, as this is the main page technically (the above one, that is.)
    // Notice that both of these, the top and bottom, are for ourwordfilter. The third option is what's actually used in the admin sidebar though. The below is for the options submenu while the above is for the words list main page.
    add_submenu_page('ourwordfilter', 'Word Filter Options', 'Options', 'manage_options', 'word-filter-options', array($this, 'optionsSubPage'));
    add_action("load-{$mainPageHook}", array($this, 'mainPageAssets')); //When we registered our parent page, in add_menu_page(), it returns the hook name. So that's where $mainPageHook comes from. -- Need to use double quotes.
  }

  function mainPageAssets() {
    wp_enqueue_style('filterAdminCss', plugin_dir_url(__FILE__) . 'styles.css'); // 1st is a made up name. 2nd is a path to our css file. -- You can add classes to the styles.css file to impact specific features. If you make a global style such as with * you may end up impacting the entire wp-admin panel (but only when you are on that specific page.)
  }

  function handleForm() {
    if (wp_verify_nonce($_POST['ourNonce'], 'saveFilterWords') AND current_user_can('manage_options')) { //here we are checking the nonce and also ensuring they have admin permissions.
      update_option('plugin_words_to_filter', sanitize_text_field($_POST['plugin_words_to_filter'])); ?>
      <div class="updated">
        <p>Your filtered words were saved.</p>
      </div>
    <?php } else { ?> 
      <!-- Else, we show an error message -->
      <div class="error">
        <p>Sorry, you do not have permission to perform that action.</p>
      </div>
    <?php } 
  }

  function wordFilterPage() { ?>
    <div class="wrap">
      <h1>Word Filter</h1>
      <!-- The below isn't wp specific, it's php code. It lets us look at what was just submitted to the server. -->
      <?php if ($_POST['justsubmitted'] == "true") $this->handleForm() ?>
      <form method="POST">
        <input type="hidden" name="justsubmitted" value="true">
        <?php wp_nonce_field('saveFilterWords', 'ourNonce') ?>
        <!-- wp_nonce_field -- 1st - make up an action name. Second is the name for the nonce value it will create. -->
        <!-- If you use your dev tools to inspect the element, you will see a hidden input with the nonce and there's a value in the value form.  -->
        <label for="plugin_words_to_filter"><p>Enter a <strong>comma-separated</strong> list of words to filter from your site's content.</p></label>
        <div class="word-filter__flex-container">
          <textarea name="plugin_words_to_filter" id="plugin_words_to_filter" placeholder="bad, mean, awful, horrible">
            <!-- Whenever you pull data from a database you will want to escape it.  -->
          <?php echo esc_textarea(get_option('plugin_words_to_filter')) ?></textarea>
        </div>
        <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
      </form>
    </div>
  <?php }

  function optionsSubPage() { ?>
    <div class="wrap">
      <h1>Word Filter Options</h1>
      <form action="options.php" method="POST">
        <?php
          settings_errors(); 
          settings_fields('replacementFields');
          do_settings_sections('word-filter-options'); //this outputs the section.
          submit_button(); //wp function
        ?>
      </form>
    </div>
  <?php }

}

$ourWordFilterPlugin = new OurWordFilterPlugin();