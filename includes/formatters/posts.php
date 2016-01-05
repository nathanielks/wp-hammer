<?php

namespace WP_CLI\Sweep\Formatters\Posts;
use WP_CLI\Iterators\Query;

/**
 * Action to be run for manipulating posts table, all formatters are passed as a parameter.
 * @param $formatters All formatters for all tables
 */
function posts( $formatters ) {
	global $wpdb;
	$posts_query = "SELECT * FROM $wpdb->posts where post_type in ( 'post', 'page' )";
	$posts = new Query( $posts_query );
	while ( $posts->valid() ) {
		$original_post = (array) $posts->current();
		$modified_post = (array) $posts->current();

		foreach( $formatters['posts'] as $column => $formatter ) {
			$modified_post = apply_filters( 'wp_sweep_run_formatter_filter_posts_' . $column, $modified_post, $formatter );
		}

		$modified = array_diff( $modified_post, $original_post ) ;

		if ( count( $modified ) ) {
			\WP_CLI::line( "Making change to post {$original_post[ 'ID' ]} to contain " . json_encode( $modified ) );
			$wpdb->update(
				"$wpdb->posts",
				$modified,
				array( 'ID' => $original_post[ 'ID' ] ),
				'%s',
				'%d'
			);
		}
		$posts->next();
	}
}

/**
 * @param $post      WP_Query post object
 * @param $formatter post_title generating format
 *
 * @return mixed
 */
function post_title( $post, $formatter ) {
	return column_content( $post, $formatter, 'post_title' );
}

/**
 * @param $post      WP_Query post object
 * @param $formatter post_content generating format
 *
 * @return mixed
 */
function post_content( $post, $formatter ) {
	return column_content( $post, $formatter, 'post_content' );
}

/**
 * Get default content lengths for the specified post column
 *
 * @param $column
 *
 * @return mixed
 */
function get_limits( $column ) {
	$defaults = array(
		'post_content' => array(
			'ipsum' => 100,
			'markov' => 100,
			'random' => 100,
		),
		'post_title' => array(
			'ipsum' => 10,
			'markov' => 10,
			'random' => 10,
		),
		'default' => array(
				'ipsum' => 50,
				'markov' => 50,
				'random' => 50,
		),
	);
	$return = isset( $defaults[ $column ] ) ? $defaults[ $column ] : $defaults[ 'default' ];
	return apply_filters( "wp_sweep_run_formatter_post_default_values", $return );
}

function column_content( $post, $formatter, $column ) {
	$limits = get_limits( $column );
	switch ( $formatter ) {
		case 'ipsum':
			$post[ $column ] = \WP_CLI\Sweep\Generators\Generic\ipsum( $limits[ 'ipsum' ] );
			break;
		case 'markov':
			$post[ $column ] = \WP_CLI\Sweep\Generators\Generic\markov( $limits[ 'markov' ], 'posts', $column );
			break;
		case 'random':
			$post[ $column ] = \WP_CLI\Sweep\Generators\Generic\random( $limits[ 'random' ], $column );
			break;
		default:
			$post[ $column ] = apply_filters( "wp_sweep_run_formatter_filter_posts_{$column}_{$formatter}", $formatter );

	}
	return $post;
}

/**
 * @param $post      WP_Query post object
 * @param $formatter post_author generating format
 *
 * @return mixed
 */
function post_author( $post, $formatter ) {
	if ( 'random' === $formatter ) {
		$post[ 'post_author' ] = 15; // TODO: @random me
	} elseif( is_integer( $formatter ) ) {
		if ( false !== get_user_by( 'id', $formatter ) ) {
			$post[ 'post_author' ] = $formatter;
		}
	}
	return $post;
}

add_filter( 'wp_sweep_run_formatter_filter_posts_post_title', __NAMESPACE__ . '\post_title' , null , 2 );
add_filter( 'wp_sweep_run_formatter_filter_posts_post_content', __NAMESPACE__ . '\post_content' , null , 2 );
add_filter( 'wp_sweep_run_formatter_filter_posts_post_author', __NAMESPACE__ . '\post_author' , null , 2 );
add_action( 'wp_sweep_run_formatter_posts', __NAMESPACE__ . '\posts' );
