<?php

namespace WP_CLI\Sweep;

use WP_CLI;
use WP_CLI\CommandWithDBObject;
use WP_CLI\Sweep\prune as Prune;
use WP_CLI\Sweep\content_formatter as ContentFormatter;

/**
 * wp sweep is a command to sweep your environment and prepare it for a staging / development environment.
 *
 */
class command extends CommandWithDBObject {

	protected $tables;
	protected $formats;
	protected $limits;
	protected $dry_run = false;


	/**
	 * Call with wp sweep, no need for additional commands, only parameters per README
	 * @param $args
	 * @param $assoc_args
	 */
	function __invoke( $args, $assoc_args ) {
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
				case '-t':
					$this->parse_argument( $args, 'tables' );
					break;
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

	function run() {
		global $wpdb;
		if ( $this->dry_run ) {
			WP_CLI::line( 'Dry run enabled, not modifying the database' );
		}
		if ( false !== $this->limits && ! is_null( $this->limits ) ) {
			var_dump( $this->limits );die();
			$prune = new Prune( $this->limits, $this->dry_run );
			$prune->run();
		}
		if ( false !== $this->formats && ! is_null( $this->formats ) ) {
			$formats = new ContentFormatter( $this->formats, $this->dry_run );
			$formats->run();
		}
	}
}

