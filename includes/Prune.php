<?php
namespace WP_CLI\Sweep;

use WP_CLI;

class Prune {

	protected $dry_run;
	protected $prunes;

	function __construct( $prunes, $dry_run ) {
		$this->dry_run = $dry_run;
		$this->parse_prunes( $prunes );
	}

	function parse_prunes( $prunes ) {
		$this->prunes = array();
		foreach( $prunes as $prune ) {
			$options = explode( '=', $prune );
			// Check we have table=prune.type set
			if ( 2 === count( $options ) ) {
				$table = $options[0];
				$element_prune = explode( '.', $options[1] );
				$number_of_rows = $element_prune[0];
				$sort_type = isset( $element_prune[1] ) ? $element_prune[1] : null;
				$this->add_prune( $table, $number_of_rows, $sort_type );
			}
		}
	}

	function add_prune( $table, $number_of_rows, $sort_type ) {
		$this->prunes[ $table ] = array(
			'prune' => $number_of_rows,
			'sort_type' => $sort_type,
		);
	}

	function run() {
		do_action( 'wp_sweep_before_run_prunes' );
		WP_CLI::line( "Running content limiters" );
		foreach ( $this->prunes as $table => $prune )     {
			if ( $this->dry_run ) {
				WP_CLI::line( "Dry run prune for $table" );
			} else {
				WP_CLI::line( "Limit run for table: $table" );
				do_action( 'wp_sweep_run_prune_' . $table, $prune[ 'prune' ], $prune[ 'sort_type' ] );
			}

		}
	}
}
