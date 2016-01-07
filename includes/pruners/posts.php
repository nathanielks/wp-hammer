<?php

namespace WP_CLI\Sweep\Pruners\Posts;
use WP_CLI\Iterators\Query;

/**
 * @param            $prune      How many posts to keep
 * @param bool|false $sort_type  How to determine which posts to keep
 */
function pruner( $limit, $sort_type = false ) {
	global $wpdb;
	$post_ids_by_type = array();
	$post_ids = array();
	\WP_CLI::line( "Fetching all posts, we will only keep $limit of them. This could take a while." );
	$posts_query = apply_filters( 'wp_sweep_prune_posts_query', "SELECT ID,post_type FROM {$wpdb->prefix}posts order by post_modified DESC" );
	$posts = new Query( $posts_query );
	$total_posts = 0;
	while ( $posts->valid() ) {
		$total_posts++;
		$post = $posts->current();
		$post_type = $post->post_type;
		$post_id = $post->ID;
		if ( ! in_array( $post_type, apply_filters( 'wp_sweep_prune_post_types_to_completely_remove', array( 'revision' ) ) ) ) {
			$post_ids_by_type[ $post_type ] = isset( $post_ids_by_type[ $post_type ] ) ? $post_ids_by_type[ $post_type ] : array();
			$post_ids_by_type[ $post_type ][] = $post_id;
			$post_ids[] = $post_id;
		}
		$posts->next();
	}

	// Post IDs to keep
	$keep_ids = array();
	$remaining = (int) min( $limit, $total_posts );
	while ( $remaining ) {
		// Iterate post_types, keeping one more of each until we've hit our
		foreach ( $post_ids_by_type as $type => &$ids ) {
			$keep_id = array_shift( $ids );
			if ( ! is_null( $keep_id ) && ! in_array( $keep_id, $keep_ids ) ) {
				$keep_ids[] = $keep_id;
				$remaining--;
				if ( 0 === $remaining ) {
					break;
				}
			}
		}
	}


	$deleted_posts_count = 0;
	\WP_CLI::line( "Deleting " . ( max( $total_posts - $limit, 0 ) ) . " posts" );
	foreach( $post_ids as $post_id ) {
		if ( ! in_array( $post_id, $keep_ids ) ) {
			$deleted_post = wp_delete_post( $post_id, true );
			if ( false !== $deleted_post ) {
				\WP_CLI::line( "Deleted post with ID $post_id, type {$deleted_post->post_type}, title {$deleted_post->post_title}" );
				$deleted_posts_count++;
			}
		}
	}
	\WP_CLI::line( "Deleted $deleted_posts_count posts." );
}

add_action( 'wp_sweep_run_prune_posts', __NAMESPACE__ . '\pruner', null, 2 );
