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

	/**
	 * @var array of tables and columns which should have their content modified, and HOW it should be modified.
	 * Specified via -f parameter and comma-separated.
	 *            e.g. array(
	 *              "posts.post_author=random", // randomly assign post author to each post
	 *              "posts.post_title=ipsum", // Use lorem ipsum to generate post titles
	 *              "user_pass=auto", // Auto generate user passwords for all remaining users
	 *            );
	 * The table content formatters are specified in the /generators/ folder, /generators/users.php will handle users table etc.
	 *
	 */
	protected $formats;

	/**
	 * @var array of tables, how many rows should remain in them, and (optional) how we should determine which rows to keep.
	 *            These are specified via -l parameter.
	 *
	 *            e.g. array(
	 *              "users=5", // keep 5 rows of users table
	 *              "posts=100.post_date", // keep 100 posts, using the latest post_date to determine which remain, and which go.
	 *            );
	 * These limits are processed by the pruners, pruners/users.php will handle the users table etc.
	 */
	protected $limits;
	/**
	 * @var bool whether or not we make changes to the database or simply do a dry run.
	 */
	protected $dry_run = false;

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
		do_action( 'wp_sweep_before_parse_arguments', $args, $assoc_args );
		$this->parse_arguments( $args, $assoc_args );
		do_action( 'wp_sweep_after_parse_arguments', $args, $assoc_args );
		$this->run();

	}

	/**
	 * Arguments parser for all supplied arguments
	 * @param $args
	 * @param $assoc_args
	 */
	function parse_arguments( $args, $assoc_args ) {
		while ( count( $args ) ) {
			$arg = array_shift( $args );
			switch ( $arg ) {
				case '-f':
					$this->parse_argument( $args, 'formats' );
					break;
				case '-l':
					$this->parse_argument( $args, 'limits' );
					break;
			}
		}

		$this->dry_run = ! empty( $assoc_args[ 'dry-run' ] );
	}

	/**
	 * Parse an arg for an individual property, if it exists.
	 *
	 * @param $args
	 * @param $property
	 *
	 * @return mixed
	 */
	function parse_argument( $args, $property ) {
		do_action( 'wp_sweep_before_parse_argument_' . $property, $args );
		if ( property_exists( $this, $property ) && count( $args ) && '-' !== substr( $args[0], 0, 1 ) ) {
			$arg_values = explode( ',', array_shift( $args ) );
			$this->{ "$property" } = apply_filters( 'wp_sweep_argument_' . $property, array_unique( array_merge_recursive( (array) $this->{ "$property" }, $arg_values ) ) );
		}
		return $this->{ "$property" };

	}

	/**
	 * Execute the WP Sweep command.
	 */
	function run() {
		ob_end_flush();
		global $wpdb;
		if ( $this->dry_run ) {
			WP_CLI::line( 'Dry run enabled, not modifying the database' );
		}
		if ( false !== $this->limits && ! is_null( $this->limits ) ) {
			$prune = new Prune( $this->limits, $this->dry_run );
			$prune->run();
		}
		if ( false !== $this->formats && ! is_null( $this->formats ) ) {
			$formats = new ContentFormatter( $this->formats, $this->dry_run );
			$formats->run();
		}
		ob_start();
	}
}

