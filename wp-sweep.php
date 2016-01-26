<?php
/**
 * Plugin Name: wp cli sweep
 * Plugin URI: http://example.com
 * Description: This plugin adds a wp cli sweep command to sweep your environment and prepare it for staging / development
 * Version: 1.0.0
 * Author: Ivan Kruchkoff
 * License: GPL2
 */

if ( ! defined('WP_CLI') || ! WP_CLI ) {
	return;
}

require_once 'vendor/autoload.php';

WP_CLI::add_command( 'sweep', 'WP_CLI\Sweep\Command' );

