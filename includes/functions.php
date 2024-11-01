<?php
function wpas_wp_title() {
	global $wp_locale;

	$m        = get_query_var( 'm' );
	$year     = get_query_var( 'year' );
	$monthnum = get_query_var( 'monthnum' );
	$day      = get_query_var( 'day' );
	$search   = get_query_var( 's' );
	$title    = '';

	// If is home
	if ( is_home() || is_front_page() ) {
		$title = get_bloginfo( 'name' );
	}

	// If there is a post
	if ( is_single() || ( is_home() && ! is_front_page() ) || ( is_page() && ! is_front_page() ) ) {
		$title = single_post_title( '', false );
	}

	// If there's a post type archive
	if ( is_post_type_archive() ) {
		$post_type = get_query_var( 'post_type' );
		if ( is_array( $post_type ) ) {
			$post_type = reset( $post_type );
		}
		$post_type_object = get_post_type_object( $post_type );
		if ( ! $post_type_object->has_archive ) {
			$title = post_type_archive_title( '', false );
		}
	}

	// If there's a category or tag
	if ( is_category() || is_tag() ) {
		$title = single_term_title( '', false );
	}

	// If there's a taxonomy
	if ( is_tax() ) {
		$term = get_queried_object();
		if ( $term ) {
			$tax   = get_taxonomy( $term->taxonomy );
			$title = single_term_title( $tax->labels->name, false );
		}
	}

	// If there's an author
	if ( is_author() && ! is_post_type_archive() ) {
		$author = get_queried_object();
		if ( $author ) {
			$title = $author->display_name;
		}
	}

	// Post type archives with has_archive should override terms.
	if ( is_post_type_archive() && $post_type_object->has_archive ) {
		$title = post_type_archive_title( '', false );
	}

	// If there's a month
	if ( is_archive() && ! empty( $m ) ) {
		$my_year  = substr( $m, 0, 4 );
		$my_month = $wp_locale->get_month( substr( $m, 4, 2 ) );
		$my_day   = intval( substr( $m, 6, 2 ) );
		$title    = $my_year . ( $my_month ? $my_month : '' ) . ( $my_day ? $my_day : '' );
	}

	// If there's a year
	if ( is_archive() && ! empty( $year ) ) {
		$title = $year;
		if ( ! empty( $monthnum ) ) {
			$title .= $wp_locale->get_month( $monthnum );
		}
		if ( ! empty( $day ) ) {
			$title .= zeroise( $day, 2 );
		}
	}

	// If it's a search
	if ( is_search() ) {
		$title = sprintf( esc_html__( 'Search results for "%s"', 'wpas' ), strip_tags( $search ) );
	}

	// If it's a 404 page
	if ( is_404() ) {
		$title = esc_html__( 'Page not found', 'wpas' );
	}

	// Send it out
	return $title;
}