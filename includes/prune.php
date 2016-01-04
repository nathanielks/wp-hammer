<?php
namespace WP_CLI\Sweep;

use WP_CLI;

class prune {

	protected $dry_run;
	protected $limits;

	function __construct( $limits, $dry_run ) {
		$this->dry_run = $dry_run;
		$this->parse_limits( $limits );
	}

	function parse_limits( $limits ) {
		$this->limits = array();
		foreach( $limits as $limit ) {
			$options = explode( '=', $limit );
			// Check we have table=limit.type set
			if ( 2 === count( $options ) ) {
				$table = $options[0];
				$element_limit = explode( '.', $options[1] );
				$number_of_rows = $element_limit[0];
				$sort_type = isset( $element_limit[1] ) ? $element_limit[1] : null;
				$this->add_limit( $table, $number_of_rows, $sort_type );
			}
		}
	}

	function add_limit( $table, $number_of_rows, $sort_type ) {
		$this->limits[ $table ] = array(
			'limit' => $number_of_rows,
			'sort_type' => $sort_type,
		);
	}

	function run() {
		do_action( 'wp_sweep_before_run_limits' );
		WP_CLI::line( "Running content limiters" );
		foreach ( $this->limits as $table => $limit )     {
			if ( $this->dry_run ) {
				WP_CLI::line( "Dry run limit for $table" );
			} else {
				WP_CLI::line( "Limit run for table: $table" );
				do_action( 'wp_sweep_run_limit_' . $table, $limit[ 'limit' ], $limit[ 'sort_type' ] );
			}

		}
	}
}



