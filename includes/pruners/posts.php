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
		}
		$post_ids[] = $post_id;
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


	\WP_CLI::line( "Deleting " . ( max( $total_posts - $limit, 0 ) ) . " posts" );
	$post_ids_to_delete = array_diff( $post_ids, $keep_ids );
	if ( 'query' === apply_filters( 'wp_sweep_prune_post_types_delete_method ', 'query' ) ) {
		$deleted_posts_count = delete_posts_sql( $post_ids_to_delete, $keep_ids );
	} else {
		$deleted_posts_count = delete_posts_wp( $post_ids_to_delete );
	}
	\WP_CLI::line( "Deleted $deleted_posts_count posts." );
}

/**
 * Delete posts using wp_delete_post, allows all of the WP hooks to run, but significantly slower than the SQL method.
 * @param $post_ids array of post_ids to delete
 *
 * @return int number of deleted posts
 */
function delete_posts_wp( $post_ids ) {
	$deleted_posts_count = 0;
	foreach( $post_ids as $post_id ) {
		var_dump( $post_id );die();
		$deleted_post = wp_delete_post( $post_id, true );
		if ( false !== $deleted_post ) {
			\WP_CLI::line( "Deleted post with ID $post_id, type {$deleted_post->post_type}, title {$deleted_post->post_title}" );
			$deleted_posts_count++;
		}
	}
	return $deleted_posts_count;
}

/**
 * Delete posts using raw SQL queries, doesn't run any WP hooks or handle any custom tables, but is significantly faster than the wp_delete_post method.
 * @param $post_ids array of post_ids to delete
 *
 * @return int number of deleted posts
 */
function delete_posts_sql( $post_ids_to_delete, $keep_ids ) {
	global $wpdb;
	$chunked_post_ids = array_chunk( $post_ids_to_delete, 500 ); // Process 500 posts at a time;
	$page_processed = 0;
	foreach ( $chunked_post_ids as $post_ids ) {
		$post_ids_as_string = implode( ",", $post_ids );
		$keep_ids_as_string = implode( ",", $keep_ids );
		// $query = "delete wp_posts, wp_term_relationships, wp_postmeta from wp_posts, wp_term_relationships, wp_postmeta where ( wp_posts.ID in ( $post_ids_as_string ) or ( wp_posts.post_parent in ( $post_ids_as_string ) and post_type in ('revision', 'attachment') ) ) and ( wp_posts.ID = wp_term_relationships.object_id ) and ( wp_posts.ID = wp_postmeta.post_id )";
		$query = "delete wp_posts, wp_term_relationships, wp_postmeta from wp_posts left join wp_term_relationships ON ( wp_posts.ID = wp_term_relationships.object_id ) left join wp_postmeta ON (wp_posts.ID = wp_postmeta.post_id ) where ( wp_posts.ID in ( $post_ids_as_string ) and wp_posts.ID not in ( $keep_ids_as_string) ) or ( wp_posts.post_parent in ( $post_ids_as_string ) and wp_posts.post_parent not in ( $keep_ids_as_string ) )";
		if ( $wpdb->query( $query) > 0 ) {
			$progress = intval( 100 * ( ++ $page_processed / count( $chunked_post_ids ) ), 10 );
			\WP_CLI::line( "Posts deleted, progress = {$progress}%");
		}
	}
	return count( $post_ids_to_delete );
}

add_action( 'wp_sweep_run_prune_posts', __NAMESPACE__ . '\pruner', null, 2 );
