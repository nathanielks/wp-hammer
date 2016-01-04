<?php
namespace WP_CLI\Sweep;

use WP_CLI;

class content_formatter {

	protected $dry_run;
	protected $formatters;

	function __construct( $formatters, $dry_run ) {
		$this->dry_run = $dry_run;
		$this->parse_formatters( $formatters );
	}

	function parse_formatters( $formatters ) {
		$this->formatters = array();
		foreach( $formatters as $formatter ) {
			$options = explode( '.', $formatter, 2 );
			// Check we have table.column=type set
			if ( 2 === count( $options ) ) {
				$table = $options[0];
				$generator_options = explode( '=', $options[1] );
				$column = $generator_options[0];
				$generator = isset( $generator_options[1] ) ? $generator_options[1] : null;
				$this->add_formatter( $table, $column, $generator );
			}
		}
	}

	function add_formatter( $table, $column, $generator ) {
		$formatters = is_array( $this->formatters ) ? $this->formatters : array();
		$formatters[ $table ] = isset( $formatters[ $table ] ) && is_array( $formatters[ $table ] ) ? $formatters[ $table ] : array();
		$formatters[ $table ][ $column ] = $generator;
		$this->formatters = $formatters;
	}

	function run() {
		do_action( 'wp_sweep_before_run_formatter' );
		WP_CLI::line( "Running content formatters" );
		foreach ( $this->formatters as $table => $formatters )     {
			if ( $this->dry_run ) {
				WP_CLI::line( "Dry run formatter $table" );
			} else {
				WP_CLI::line( "Running formatters for table: $table" );
				do_action( 'wp_sweep_run_formatter_' . $table, $this->formatters );
				$columns = array_keys( $formatters );
				foreach ( $columns as $column ) {
					do_action( 'wp_sweep_run_formatter_' . $table . '_' . $column , $formatters[ $column ] );
				}
			}

		}
	}
}



