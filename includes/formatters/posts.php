<?php

namespace WP_CLI\Sweep\Formatters\Posts;
use WP_CLI\Iterators\Query;

/**
 * Action to be run for manipulating posts table, all formatters are passed as a parameter.
 * @param $formatters All formatters for all tables
 */
function posts( $formatters ) {
	global $wpdb;
	$posts_query = "SELECT * FROM $wpdb->posts";
	$posts = new Query( $posts_query );
	while ( $posts->valid() ) {
		$original_post = (array) $posts->current();
		$modified_post = (array) $posts->current();

		foreach( $formatters['posts'] as $column => $formatter ) {
			$modified_post = apply_filters( 'wp_sweep_run_formatter_posts_' . $column, $modified_post, $formatter );
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
	switch ( $formatter ) {
		case 'ipsum':
			$post[ 'post_title' ] = \WP_CLI\Sweep\Generators\Generic\ipsum( 2 );
			break;
		default:
			$post[ 'post_title' ] = apply_filters( 'wp_sweep_run_formatter_posts_post_title_' . $formatter, $formatter );

	}
	return $post;
}

/**
 * @param $post      WP_Query post object
 * @param $formatter post_content generating format
 *
 * @return mixed
 */
function post_content( $post, $formatter ) {
	switch ( $formatter ) {
		case 'ipsum':
			$post[ 'post_content' ] = \WP_CLI\Sweep\Generators\Generic\ipsum( 100 );
			break;
		case 'markov':
			$post[ 'post_content' ] = \WP_CLI\Sweep\Generators\Generic\markov( 100, 'post_content' );
			break;
		default:
			$post[ 'post_content' ] = apply_filters( 'wp_sweep_run_formatter_posts_post_content_' . $formatter, $formatter );

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
	} elseif( isint( $formatter ) ) {
		if ( false !== get_user_by( 'id', $formatter ) ) {
			$post[ 'post_author' ] = $formatter;
		}
	}
	return $post;
}

add_filter( 'wp_sweep_run_formatter_posts_post_title', __NAMESPACE__ . '\post_title' , null , 2 );
add_filter( 'wp_sweep_run_formatter_posts_post_content', __NAMESPACE__ . '\post_content' , null , 2 );
add_filter( 'wp_sweep_run_formatter_posts_post_author', __NAMESPACE__ . '\post_author' , null , 2 );
add_action( 'wp_sweep_run_formatter_posts', __NAMESPACE__ . '\posts' );
