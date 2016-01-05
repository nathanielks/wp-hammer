<?php

namespace WP_CLI\Sweep\Generators\Generic;
use WP_CLI\Iterators\Query;
use joshtronic\LoremIpsum;
use PWGen;

/**
 * Lorem Ipsum content generator
 * @param $length number of words
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
 * @param $length number of words
 *
 * @return string random string of content
 */
function random( $length ) {
	$random = new PWGen();
	$random->setNumerals( false );
	$random->setCapitalize( false );
	$content = '';
	if ( $length > 0 ) {
		while ( $length -- > 0 ) {
			$random->setLength( rand( 4, 8 ) ); // Generate words of 4-8 chars
			$content .= $random->generate() . ' ';
		}
	}
	return rtrim( $content );
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
