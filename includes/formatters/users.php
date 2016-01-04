<?php

namespace WP_CLI\Sweep\Formatters\Users;
use WP_CLI\Iterators\Query;

function users( $formatters ) {
	global $wpdb;
	$users_query = "SELECT * FROM $wpdb->users";
	$users = new Query( $users_query );
	while ( $users->valid() ) {
		$original_user = (array) $users->current();
		$modified_user = (array) $users->current();

		var_dump( 'original user');
		var_dump( $original_user );
		foreach( $formatters['users'] as $column => $formatter ) {
			$modified_user = apply_filters( 'wp_sweep_run_formatter_users_' . $column, $modified_user, $formatter );
		}
		var_dump( 'modified user');
		var_dump( $modified_user );

		var_dump( 'modified ALL');

		$modified = array_diff( $modified_user, $original_user ) ;

		if ( count( $modified ) ) {
			$wpdb->update(
				"$wpdb->users",
				$modified,
				array( 'ID' => $original_user[ 'ID' ] ),
				'%s',
				'%d'
			);
		}
		$users->next();
	}


}

function user_email( $user, $formatter) {
	preg_match_all( '/__([a-zA-Z0-9-_]*)__/', $formatter, $matches );
	if ( is_array( $matches ) && 2 === count( $matches ) ) {
		foreach( $matches[1] as $match ) {
			if ( isset( $user[ $match ] ) ) {
				$formatter = str_replace( "__ID__", $user[ $match ], $formatter );
			}
		}
		$user[ 'user_email' ] = $formatter;
	}
	return $user;
}

add_filter( 'wp_sweep_run_formatter_users_user_email', __NAMESPACE__ . '\user_email', null , 2 );
add_action( 'wp_sweep_run_formatter_users', __NAMESPACE__ . '\users' );
