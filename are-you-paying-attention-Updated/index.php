<?php

/*
  Plugin Name: Are You Paying Attention Quiz2
  Description: Give your readers a multiple choice question.
  Version: 1.0
  Author: Brad
  Author URI: https://www.udemy.com/user/bradschiff/
*/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AreYouPayingAttention {
  function __construct() {
    add_action('init', array($this, 'adminAssets'));
  }

  function adminAssets() {
    register_block_type(__DIR__, array(
      'render_callback' => array($this, 'theHTML')
    ));//the first parameter points towards the directory where block.json is located.

    // If you are NOT using block.json, then you would need to manually spell out the editor_script etc. values below:
    // register_block_type(OMITTED, array(
    //   'editor_script' => 'ournewblocktype',
    //   'editor_style' => 'quizeditcss',
    //   'render_callback' => array($this, 'theHTML')
    // ));

  }

  function theHTML($attributes) {
    ob_start(); ?>
    <div class="paying-attention-update-me"><pre style="display: none;"><?php echo wp_json_encode($attributes) ?></pre></div>
    <!-- wp_json_encode will encode the $attributes into something like json. It's wrapped into a pre tag with display none so it's hidden. It still exists in the dom, so our JS can access it. (Note that attributes is coming from another file, a js file.) -->
    <?php return ob_get_clean();
  }
}

$areYouPayingAttention = new AreYouPayingAttention();