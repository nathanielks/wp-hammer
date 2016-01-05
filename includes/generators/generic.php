<?php

namespace WP_CLI\Sweep\Generators\Generic;
use WP_CLI\Iterators\Query;
use joshtronic\LoremIpsum;

/**
 * Lorem Ipsum content generator
 * @param $length
 *
 * @return string of ipsum text of specified length
 */
function ipsum( $length ) {
	$lipsum = new LoremIpsum();
	// Since all Lorem Ipsum strings start with lorem ipsum dolor sit amet consectetur adipiscing elit, we remove
	// words 3-8, 6 words, so that shorter strings (e.g. titles) have more variety in their words.
	return str_replace( ' dolor sit amet consectetur adipiscing elit', '', $lipsum->words( $length + 6 ) );
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
