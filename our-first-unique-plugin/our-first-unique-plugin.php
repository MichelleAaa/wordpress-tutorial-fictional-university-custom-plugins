<?php

/*
    Plugin Name: Our Test Plugin
    Description: A truly amazing plugin.
    Version: 1.0
    Author: Brad
    Author URI: https://www.udemy.com/user/bradschiff/
    Text Domain: wcpdomain
    Domain Path: /languages
*/
// for text domain, that's a made up name that's unique to your plugin.
// Domain path will point towards a folder in our plugin folder called languages. it will contain the translation template and any translated versions we create.

class WordCountAndTimePlugin {
  function __construct() {
    add_action('admin_menu', array($this, 'adminPage')); // This will update the admin_menu in wp-admin. -- adminPage is a function name we make up.
    add_action('admin_init', array($this, 'settings'));
    add_filter('the_content', array($this, 'ifWrap')); // we pass a reference to the method as the 2nd parameter, and at the right moment, the 1st parameter, wp will call the function.
    add_action('init', array($this, 'languages')); // we need to add this so wp knows that we have translations available.
  }

  function languages() {
    load_plugin_textdomain('wcpdomain', false, dirname(plugin_basename(__FILE__)) . '/languages'); //this directs to the location of the file folder where the translated files are located.
  }

  function ifWrap($content) {
    if (is_main_query() AND is_single() AND
    (
      // IF the user is using the default option, the '1' as the second parameter is a fallback
      get_option('wcp_wordcount', '1') OR
      get_option('wcp_charactercount', '1') OR
      get_option('wcp_readtime', '1')
    )) {
      // We are passing the content to the createHTML function
      return $this->createHTML($content);
    }
    // Otherwise, if the if doesn't apply, then:
    return $content;
  }

  function createHTML($content) {
    $html = '<h3>' . esc_html(get_option('wcp_headline', 'Post Statistics')) . '</h3><p>';

    // get word count once because both wordcount and read time will need it.
    // default value of 1 for yes.
    if (get_option('wcp_wordcount', '1') OR get_option('wcp_readtime', '1')) {
      $wordCount = str_word_count(strip_tags($content)); // strip_tags will not count individual html element tags, only actual content. 
    }

    // Note that below we have added a space with . " " instead of just adding it to the end of the line. Here are the translations below, with __('This post has', 'wcpdomain') -- we are just escaping it. -- in this way, we are only translating specific words/parts of the statements.
    if (get_option('wcp_wordcount', '1')) {
      $html .= esc_html__('This post has', 'wcpdomain') . ' ' . $wordCount . ' ' . __('words', 'wcpdomain') . '.<br>';
    }

    if (get_option('wcp_charactercount', '1')) {
      $html .= 'This post has ' . strlen(strip_tags($content)) . ' characters.<br>';
    }

    if (get_option('wcp_readtime', '1')) {
      $html .= 'This post will take about ' . round($wordCount/225) . ' minute(s) to read.<br>';
    }

    $html .= '</p>';

    if (get_option('wcp_location', '0') == '0') { //if wcp_location value is 0
      return $html . $content;
    } // Otherwise:
    return $content . $html;
  }

  function settings() {
    add_settings_section('wcp_first_section', null, null, 'word-count-settings-page'); //First -- name of the section (we choose wcp_first_section below in the fifth parameter of add_settings_field()). -- Second -- the title for the section. (If your settings page would have three diffeernt sections you could add subtitles here. We are listing null since we don't need a visible subtitle.) -- Third -- Allows you to have a little bit of content at the top of the section, such as a paragraph of text/html content. If you don't want any content, then you can select null. Fourth - the page slug that we want to add this section to. It's listed below in add_settings_field() as well.

    add_settings_field('wcp_location', 'Display Location', array($this, 'locationHTML'), 'word-count-settings-page', 'wcp_first_section'); // This function builds out the HTML input field for our form. (Ties HTML to the wcp_location that we just created below in register_setting)
    // First - name of the setting we registered this to in register_setting. Second -- html label text (what users will see on the front end). Third - function responsible for outputting the HTML. Fourth - page slug for the settings page we are working with. 'word-count-settings-page' was selected in add_options_page() below, and we must match it here. -- Fifth - wcp_first_section 

    // Only trusted Admin users are going to have access to this specific page, however, it’s possible to inspect the admin page, select the input field, and then change the value to another number, and entering save. – So if you wanted to make sure that the user can’t enter a value other than the two options, 0 and 1, when you use register_setting – the third argument is an array. We have sanitize_callback in there with sanitize_text_field. Instead of this, we could use our own function instead, sanitize_callback.
    register_setting('wordcountplugin', 'wcp_location', array('sanitize_callback' => array($this, 'sanitizeLocation'), 'default' => '0')); // We will use register_setting once for each of the items we will list, but for now we are just handling display location. -- First argument - name of the group this setting belongs to. -- Second -- the name for this specific setting. (You can name it whatever you want). -- Third - an array. It includes a default value in case one isn't provided or there yet.

    //  --- wcp_headline

    add_settings_field('wcp_headline', 'Headline Text', array($this, 'headlineHTML'), 'word-count-settings-page', 'wcp_first_section');
    register_setting('wordcountplugin', 'wcp_headline', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Post Statistics'));

    // wcp_wordcount
    add_settings_field('wcp_wordcount', 'Word Count', array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_wordcount'));
    register_setting('wordcountplugin', 'wcp_wordcount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

    add_settings_field('wcp_charactercount', 'Character Count', array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_charactercount'));
    register_setting('wordcountplugin', 'wcp_charactercount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

    add_settings_field('wcp_readtime', 'Read Time', array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_readtime'));
    register_setting('wordcountplugin', 'wcp_readtime', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));
  }

  function sanitizeLocation($input) {
    // There are only two inputs allowed, 0 and 1. So we are trying to block users from inspecting the webpage and changing the value before clicking save.
    if ($input != '0' AND $input != '1') {
      add_settings_error('wcp_location', 'wcp_location_error', 'Display location must be either beginning or end.'); // this will add a red error message. -- first - name of the option this is associated with -- second is the slug for the error (it will add in id='wcp_location_error'). Third - message that will be displayed to the user.
      return get_option('wcp_location'); // this returns the previous value entered in the database.
    }
    //Otherwise, if the user entered a 0 or 1, then we can just return the input and let it be.
    return $input;
  }

  /*
  The below is basically three copies of the same function, with just the variable names different. Instead of doing this, we created a single reusable function called checkboxHTML that receives a parameter. 

  function wordcountHTML() { ?>
    <input type="checkbox" name="wcp_wordcount" value="1" <?php checked(get_option('wcp_wordcount'), '1') ?>>
  <?php }

  function charactercountHTML() { ?>
    <input type="checkbox" name="wcp_charactercount" value="1" <?php checked(get_option('wcp_charactercount'), '1') ?>>
  <?php }

  function readtimeHTML() { ?>
    <input type="checkbox" name="wcp_readtime" value="1" <?php checked(get_option('wcp_readtime'), '1') ?>>
  <?php }
  */

  // reusable checkbox function
  // What's passed in is an array so we are referencing the key names below. -- note that the array comes from add_settings_field -- the last argument is the array.
  function checkboxHTML($args) { ?>
    <input type="checkbox" name="<?php echo $args['theName'] ?>" value="1" <?php checked(get_option($args['theName']), '1') ?>>
  <?php }

//note that the name of the input field matches the name listed above in add_settings_field's first parameter.
  function headlineHTML() { ?>
    <input type="text" name="wcp_headline" value="<?php echo esc_attr(get_option('wcp_headline')) ?>">
  <?php }

// the select has to have a name field that matches the second parameter of the register_setting field.
  function locationHTML() { ?>
    <select name="wcp_location">
      <!-- in selected() below - first is the option -- second -- the value for the option's value field -->
      <option value="0" <?php selected(get_option('wcp_location'), '0') ?>>Beginning of post</option>
      <option value="1" <?php selected(get_option('wcp_location'), '1') ?>>End of post</option>
    </select>
  <?php }

  function adminPage() {
    add_options_page('Word Count Settings', __('Word Count', 'wcpdomain'), 'manage_options', 'word-count-settings-page', array($this, 'ourHTML')); //the __('Word Count', 'wcpdomain') -- makes the text translatable. (see above in the heading comment that we use the wcpdomain name for the Text Domain.)
  } //This would add an options page in the wp-admin panel. The first is the title. The second is the title for the settings menu (meaning the black tab -- so the title should be short.) Third - what capability does the user need to have to see this page. 'manage_options' means that only if the user has permissions to manage options in wp can they see this new panel. Fourth - the short/name or slug to be used at the end of the url for the page.(word-count-settings-page). Final - a function to run.

  // note that we aren't returning anything out of this function with the return keyword. We just output HTML. -- This is outputting the h1 for the wp-admin custom page we are adding to the settings panel. 

  function ourHTML() { ?>
    <div class="wrap">
      <h1>Word Count Settings</h1>
      <form action="options.php" method="POST">
      <?php
        settings_fields('wordcountplugin'); //give the name of the field group we made up earlier in register_setting -- the first parameter. -- this will see this and add the appropriate hidden HTML fields with the nonce value, action value, security, and permissions for us.
        // do_settings_sections takes the slug/url of the settings page you are creating. (WP will automatically look through any sections that were added.)
        do_settings_sections('word-count-settings-page');
        submit_button(); // this is a wp function and is a submit button for the admin panel.
      ?>
      </form>
    </div>
  <?php }
}

$wordCountAndTimePlugin = new WordCountAndTimePlugin();