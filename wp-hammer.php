<?php
/**
 * Plugin Name: wp hammer
 * Plugin URI: http://example.com
 * Description: This plugin adds a wp-cli ha command to clean your environment and prepare it for staging / development by removing Personally Identifiable Information
 * Version: 1.0.0
 * Author: Ivan Kruchkoff
 * License: BSD
 */

if ( ! defined('WP_CLI') || ! WP_CLI ) {
	return;
}

require_once 'vendor/autoload.php';

WP_CLI::add_command( 'ha', 'WP_CLI\Hammer\Command' );
WP_CLI::add_command( 'hammer', 'WP_CLI\Hammer\Command' );

