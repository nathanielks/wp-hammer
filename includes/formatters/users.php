<?php

namespace WP_CLI\Sweep\Formatters\Users;
use WP_CLI\Iterators\Query;

/**
 * Action to be run for manipulating users table, all formatters are passed as a parameter.
 * @param $formatters All formatters for all tables
 */
function users( $formatters ) {
	global $wpdb;
	$users_query = "SELECT * FROM $wpdb->users";
	$users = new Query( $users_query );
	while ( $users->valid() ) {
		$original_user = (array) $users->current();
		$modified_user = (array) $users->current();

		foreach( $formatters['users'] as $column => $formatter ) {
			$modified_user = apply_filters( 'wp_sweep_run_formatter_users_' . $column, $modified_user, $formatter );
		}

		$modified = array_diff( $modified_user, $original_user ) ;

		if ( count( $modified ) ) {
			\WP_CLI::line( "Making change to user {$original_user[ 'ID' ]} to contain " . json_encode( $modified ) );
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

/**
 * @param $user WP_User_Query user object
 * @param $formatter String for email, with other columns substituted with __COLUMN_NAME__
 *
 * @return mixed
 */
function user_email( $user, $formatter ) {
	preg_match_all( '/__([a-zA-Z0-9-_]*)__/', $formatter, $matches );
	if ( is_array( $matches ) && 2 === count( $matches ) ) {
		foreach( $matches[1] as $match ) {
			if ( isset( $user[ $match ] ) ) {
				$formatter = str_replace( "__{$match}__", $user[ $match ], $formatter );
			}
		}
		$user[ 'user_email' ] = $formatter;
	}
	return $user;
}

/**
 * @param $user WP_User_Query user object
 * @param $formatter password generating format auto = auto generated passwords.
 *
 * @return mixed
 */
function user_pass( $user, $formatter ) {
	if ( 'auto' === $formatter ) {
		$new_password = bin2hex( mcrypt_create_iv( 12, MCRYPT_DEV_URANDOM ) );
		\WP_CLI::line( "New password for user {$user[ 'ID' ]} is {$new_password}" );
		$user[ 'user_pass' ] = wp_hash_password( $new_password );
	}
	return $user;
}

add_filter( 'wp_sweep_run_formatter_users_user_email', __NAMESPACE__ . '\user_email', null , 2 );
add_filter( 'wp_sweep_run_formatter_users_user_pass', __NAMESPACE__ . '\user_pass', null , 2 );
add_action( 'wp_sweep_run_formatter_users', __NAMESPACE__ . '\users' );
