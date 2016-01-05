<?php

namespace WP_CLI\Sweep\Generators\Generic;
use WP_CLI\Iterators\Query;

/**
 * Lorem Ipsum content generator
 * @param $length
 *
 * @return string of ipsum text of specified length
 */
function ipsum( $length ) {
	$content = '';
	if ( $length > 0 ) {
		while ( $length -- > 0 ) {
			$content .= 'Lorem ipsum dolar sit amet';
		}
	}
	return $content;
}

/**
 * Random string content generator
 * @param $length
 *
 * @return string random string of content
 */
function random( $length ) {
	$content = '';
	if ( $length > 0 ) {
		while ( $length -- > 0 ) {
			$content .= 'r';
		}
	}
	return $content;
}

/**
 * Markov chain content generator
 * @param $length
 *
 * @return string
 */
function markov( $length, $table ) {
	$content = '';
	if ( $length > 0 ) {
		while ( $length -- > 0 ) {
			$content .= 'markov';
		}
	}
	return $content;
}
