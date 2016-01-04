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

$content_manipulators = glob( __DIR__ . '/includes/{pruners,formatters,generators}/*.php', GLOB_BRACE);

require_once 'autoload.php';

foreach ( $content_manipulators as $content_manipulator ) {
	require_once $content_manipulator;
}

WP_CLI::add_command( 'sweep', 'WP_CLI\Sweep\command' );
