<?php

namespace WP_CLI\Sweep\Pruners\Posts;

/**
 * @param            $limit      How many posts to keep
 * @param bool|false $sort_type  How to determine which posts to keep
 */
function pruner( $limit, $sort_type = false ) {
	// @todo update pruner to handle posts
}

add_action( 'wp_sweep_run_limit_posts', __NAMESPACE__ . '\pruner' );
