<?php

/**
 * wp sweep is a command to sweep your environment and prepare it for a staging / development environment.
 *
 */
class WP_Sweep extends WP_CLI_Command {

	protected $tables;
	protected $formats;
	protected $limits;
	protected $dry_run = false;


	function __invoke( $args, $assoc_args ) {
		$this->parse_arguments( $args, $assoc_args );
		$this->run();

	}

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

	function parse_argument( $args, $property ) {
		if ( property_exists( $this, $property ) && count( $args ) && '-' !== substr( $args[0], 0, 1 ) ) {
			$arg_values = explode( ',', array_shift( $args ) );
			$this->{ "$property" } = array_unique( array_merge_recursive( (array) $this->{ "$property" }, $arg_values ) );
		}
		return $this->{ "$property" };

	}

	function run() {
		global $wpdb;
		WP_CLI::success( $wpdb->prefix );
		var_dump( $this->tables );
		var_dump( $this->formats );
		var_dump( $this->limits );
		var_dump( $this->dry_run );
	}
}

WP_CLI::add_command( 'sweep', 'WP_Sweep' );
