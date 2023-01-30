<?php

/*
  Plugin Name: Are You Paying Attention Quiz
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
    wp_register_script('ournewblocktype', plugin_dir_url(__FILE__) . 'build/index.js', array('wp-blocks', 'wp-element')); // Third argument is a list of dependencies.-- note that we are using wp.blocks.registerBlockType() in the js file. To use it, we need the wp-blocks added here. -- we add wp-element to ensure that it's already loaded/exists before we load the js file.
    // Note that the second is pointing towards which folder should load. So this is pointing to the build folder, as we need to bundle with npm run build for the JSX to convert.
    register_block_type('ourplugin/are-you-paying-attention', array(
      'editor_script' => 'ournewblocktype', // this says which JS object to load.
      'render_callback' => array($this, 'theHTML')
    )); 
    // This has to match the JS file -- (from the wp.blocks.registerBlockType() first parameter matches the first parameter of this function)
    // Second parameter is an array of options
  }

  function theHTML($attributes) {
    ob_start(); ?>
    <h3>Today the sky is <?php echo esc_html($attributes['skyColor']) ?> and the grass is <?php echo esc_html($attributes['grassColor']) ?>!</h3>
    <?php return ob_get_clean();
  } // 
}

$areYouPayingAttention = new AreYouPayingAttention();