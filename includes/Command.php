<?php

namespace WP_CLI\Sweep;

use WP_CLI;
use WP_CLI\CommandWithDBObject;
use WP_CLI\Sweep\Prune;
use WP_CLI\Sweep\ContentFormatter;

/**
 * wp sweep is a command to sweep your environment and prepare it for a staging / development environment.
 *
 */
class Command extends CommandWithDBObject {

	protected $settings;

	/**
	 * Clean up your site to remove data such as password hashes and email addresses.
	 *
	 * ## OPTIONS
	 *
	 * [-f <format1>,<format2>,...<formatN>]
	 * : Which tables and/or columns to process and how to generate the new content.
	 *
	 * [-l <limit1>,<limit2>,...<limitN>]
	 * : Which tables to limit, the maximum number of rows to keep and the method of determining which rows to keep.
	 *
	 * [--dry-run]
	 * : Whether or not we actually make any changes to the database.
	 *
	 * ## EXAMPLES
	 *
	 *     wp sweep -l users=5
	 *     wp sweep -f posts.post_author=random,users.user_pass=auto,users.user_email='test+user__ID__@example.com'
	 *     wp sweep --dry-run -f posts.post_title=ipsum,posts.post_content=markov -l users=10,posts=100.post_date
	 *
	 * @synopsis [<-f>] [<formats>] [<-l>] [<limits>] [<--dry-run>]
	 */
	function __invoke( $args = array(), $assoc_args = array() ) {
		$this->settings = new Settings();
		$this->settings->parse_arguments( $args, $assoc_args );
		$this->run();

	}

	/**
	 * Execute the WP Sweep command.
	 */
	function run() {
		ob_end_flush();
		global $wpdb;
		if ( $this->settings->dry_run ) {
			WP_CLI::line( 'Dry run enabled, not modifying the database' );
		}
		if ( false !== $this->settings->limits && ! is_null( $this->settings->limits ) ) {
			$prune = new Prune( $this->settings->limits, $this->settings->dry_run );
			$prune->run();
		}
		if ( false !== $this->settings->formats && ! is_null( $this->settings->formats ) ) {
			$formats = new ContentFormatter( $this->settings->formats, $this->settings->dry_run );
			$formats->run();
		}
		ob_start();
	}
}

