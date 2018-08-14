<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'woothemes_get_testimonials' ) ) {
/**
 * Wrapper function to get the testimonials from the WooThemes_Testimonials class.
 * @param  string/array $args  Arguments.
 * @since  1.0.0
 * @return array/boolean       Array if true, boolean if false.
 */
function woothemes_get_testimonials ( $args = '' ) {
	global $woothemes_testimonials;
	return $woothemes_testimonials->get_testimonials( $args );
} // End woothemes_get_testimonials()
}

/**
 * Enable the usage of do_action( 'woothemes_testimonials' ) to display testimonials within a theme/plugin.
 *
 * @since  1.0.0
 */
add_action( 'woothemes_testimonials', 'woothemes_testimonials' );

if ( ! function_exists( 'woothemes_testimonials' ) ) {
/**
 * Display or return HTML-formatted testimonials.
 * @param  string/array $args  Arguments.
 * @since  1.0.0
 * @return string
 */
function woothemes_testimonials ( $args = '' ) {
	global $post, $more;

	$defaults = apply_filters( 'woothemes_testimonials_default_args', array(
		'limit' 			=> 5,
		'per_row' 			=> null,
		'orderby' 			=> 'menu_order',
		'order' 			=> 'DESC',
		'id' 				=> 0,
		'display_author' 	=> true,
		'display_avatar' 	=> true,
		'display_url' 		=> true,
		'effect' 			=> 'fade', // Options: 'fade', 'none'
		'pagination' 		=> false,
		'echo' 				=> true,
		'size' 				=> 50,
		'title' 			=> '',
		'before' 			=> '<div class="widget widget_woothemes_testimonials">',
		'after' 			=> '</div>',
		'before_title' 		=> '<h2>',
		'after_title' 		=> '</h2>',
		'category' 			=> 0,
	) );

	$args = wp_parse_args( $args, $defaults );

	// Allow child themes/plugins to filter here.
	$args = apply_filters( 'woothemes_testimonials_args', $args );
	$html = '';
	do_action( 'woothemes_testimonials_before', $args );

		// The Query.
		$query = woothemes_get_testimonials( $args );

		// The Display.
		if ( ! is_wp_error( $query ) && is_array( $query ) && count( $query ) > 0 ) {

			$class = '';

			if ( is_numeric( $args['per_row'] ) ) {
				$class .= ' columns-' . intval( $args['per_row'] );
			}

			if ( 'none' != $args['effect'] ) {
				$class .= ' effect-' . $args['effect'];
			}

			$html .= $args['before'] . "\n";
			if ( '' != $args['title'] ) {
				$html .= $args['before_title'] . esc_html( $args['title'] ) . $args['after_title'] . "\n";
			}
			$html .= '<div class="testimonials component' . esc_attr( $class ) . '">' . "\n";

			$html .= '<div class="testimonials-list">' . "\n";

			// Begin templating logic.
			$tpl = '<div id="quote-%%ID%%" class="%%CLASS%%" itemprop="review" itemscope itemtype="http://schema.org/Review"><blockquote class="testimonials-text" itemprop="reviewBody">%%TEXT%%</blockquote>%%AVATAR%% %%AUTHOR%%</div>';
			$tpl = apply_filters( 'woothemes_testimonials_item_template', $tpl, $args );

			$count = 0;
			foreach ( $query as $post ) { $count++;
				$template = $tpl;

				$css_class = 'quote';
				if ( ( is_numeric( $args['per_row'] ) && ( $args['per_row'] > 0 ) && ( 0 == ( $count - 1 ) % $args['per_row'] ) ) || 1 == $count ) { $css_class .= ' first'; }
				if ( ( is_numeric( $args['per_row'] ) && ( $args['per_row'] > 0 ) && ( 0 == $count % $args['per_row'] ) ) || count( $query ) == $count ) { $css_class .= ' last'; }

				// Add a CSS class if no image is available.
				if ( isset( $post->image ) && ( '' == $post->image ) ) {
					$css_class .= ' no-image';
				}

				setup_postdata( $post );

				$author = '';
				$author_text = '';
				// If we need to display the author, get the data.
				$post_title = get_the_title( $post );
				$byline = $post->byline;
				$author_url = esc_url( $post->url );
				if ( ( $post_title != '' ) && true == $args['display_author'] ) {
					$author .= '<cite class="author" itemprop="author" itemscope itemtype="http://schema.org/Person">';

					$author_name = '<span itemprop="name">' . $post_title . '</span>';

					$author .= $author_name;

					if ( isset( $byline ) && '' != $byline ) {
						$author .= ' <span class="title" itemprop="jobTitle">' . $byline . '</span><!--/.title-->' . "\n";
					}

					if ( true == $args['display_url'] && '' != $author_url ) {
						$author .= ' <span class="url"><a href="' . esc_url( $author_url ) . '" itemprop="url">' . apply_filters( 'woothemes_testimonials_author_link_text', $text = esc_url( $author_url ) ) . '</a></span><!--/.excerpt-->' . "\n";
					}

					$author .= '</cite><!--/.author-->' . "\n";

					// Templating engine replacement.
					$template = str_replace( '%%AUTHOR%%', $author, $template );
				} else {
					$template = str_replace( '%%AUTHOR%%', '', $template );
				}

				// Templating logic replacement.
				$template = str_replace( '%%ID%%', get_the_ID(), $template );
				$template = str_replace( '%%CLASS%%', esc_attr( $css_class ), $template );

				if ( isset( $post->image ) && ( '' != $post->image ) && true == $args['display_avatar'] && ( '' != $author_url ) ) {
					$template = str_replace( '%%AVATAR%%', '<a href="' . esc_url( $author_url ) . '" class="avatar-link">' . $post->image . '</a>', $template );
				} elseif ( isset( $post->image ) && ( '' != $post->image ) && true == $args['display_avatar'] ) {
					$template = str_replace( '%%AVATAR%%', $post->image, $template );
				} else {
					$template = str_replace( '%%AVATAR%%', '', $template );
				}

				// Remove any remaining %%AVATAR%% template tags.
				$template 	= str_replace( '%%AVATAR%%', '', $template );
				$real_more 	= $more;
			    $more      	= 0;
				$content 	= apply_filters( 'woothemes_testimonials_content', apply_filters( 'the_content', get_the_content( __( 'Read full testimonial...', 'our-team-by-woothemes' ) ) ), $post );
				$more      	= $real_more;
				$template 	= str_replace( '%%TEXT%%', $content, $template );

				// Assign for output.
				$html .= $template;

				if( is_numeric( $args['per_row'] ) && ( $args['per_row'] > 0 ) && ( 0 == $count % $args['per_row'] ) ) {
					$html .= '<div class="fix"></div>' . "\n";
				}
				$html .= woothemes_testimonials_generate_ld_json( $post );
			}

			wp_reset_postdata();

			$html .= '</div><!--/.testimonials-list-->' . "\n";

			if ( $args['pagination'] == true && count( $query ) > 1 && $args['effect'] != 'none' ) {
				$html .= '<div class="pagination">' . "\n";
				$html .= '<a href="#" class="btn-prev">' . apply_filters( 'woothemes_testimonials_prev_btn', '&larr; ' . __( 'Previous', 'woothemes-testimonials' ) ) . '</a>' . "\n";
		        $html .= '<a href="#" class="btn-next">' . apply_filters( 'woothemes_testimonials_next_btn', __( 'Next', 'woothemes-testimonials' ) . ' &rarr;' ) . '</a>' . "\n";
		        $html .= '</div><!--/.pagination-->' . "\n";
			}
				$html .= '<div class="fix"></div>' . "\n";
			$html .= '</div><!--/.testimonials-->' . "\n";
			$html .= $args['after'] . "\n";
		}

		// Allow child themes/plugins to filter here.
		$html = apply_filters( 'woothemes_testimonials_html', $html, $query, $args );
		if ( $args['echo'] != true ) { return $html; }

		// Should only run is "echo" is set to true.
		echo $html;

		do_action( 'woothemes_testimonials_after', $args ); // Only if "echo" is set to true.
} // End woothemes_testimonials()
}

if ( ! function_exists( 'woothemes_testimonials_shortcode' ) ) {
/**
 * The shortcode function.
 * @since  1.0.0
 * @param  array  $atts    Shortcode attributes.
 * @param  string $content If the shortcode is a wrapper, this is the content being wrapped.
 * @return string          Output using the template tag.
 */
function woothemes_testimonials_shortcode ( $atts, $content = null ) {
	$args = (array) $atts;

	$defaults = array(
		'limit' 			=> 5,
		'per_row' 			=> null,
		'orderby' 			=> 'menu_order',
		'order' 			=> 'DESC',
		'id' 				=> 0,
		'display_author' 	=> true,
		'display_avatar' 	=> true,
		'display_url' 		=> true,
		'effect' 			=> 'fade', // Options: 'fade', 'none'
		'pagination' 		=> false,
		'echo' 				=> true,
		'size' 				=> 50,
		'category' 			=> 0,
	);

	$args = shortcode_atts( $defaults, $atts );

	// Make sure we return and don't echo.
	$args['echo'] = false;

	// Fix integers.
	if ( isset( $args['limit'] ) ) $args['limit'] = intval( $args['limit'] );
	if ( isset( $args['size'] ) &&  ( 0 < intval( $args['size'] ) ) ) $args['size'] = intval( $args['size'] );
	if ( isset( $args['category'] ) && is_numeric( $args['category'] ) ) $args['category'] = intval( $args['category'] );

	// Fix booleans.
	foreach ( array( 'display_author', 'display_url', 'pagination', 'display_avatar' ) as $k => $v ) {
		if ( isset( $args[$v] ) && ( 'true' == $args[$v] ) ) {
			$args[$v] = true;
		} else {
			$args[$v] = false;
		}
	}

	return woothemes_testimonials( $args );
} // End woothemes_testimonials_shortcode()
}

add_shortcode( 'woothemes_testimonials', 'woothemes_testimonials_shortcode' );

if ( ! function_exists( 'woothemes_testimonials_content_default_filters' ) ) {
/**
 * Adds default filters to the "woothemes_testimonials_content" filter point.
 * @since  1.3.0
 * @return void
 */
function woothemes_testimonials_content_default_filters () {
	add_filter( 'woothemes_testimonials_content', 'do_shortcode' );
} // End woothemes_testimonials_content_default_filters()

add_action( 'woothemes_testimonials_before', 'woothemes_testimonials_content_default_filters' );
}

/**
 * Generates LD+JSON markup for testimonials.
 *
 * Generates LD+JSON markup for testimonials.
 *
 * @since  1.6.0
 * @param WP_Post $post A WP_Post object.
 * @return string $ld_json_output The JSON+LD markup.
 */
function woothemes_testimonials_generate_ld_json( $post ) {
		$byline = get_post_meta( $post->ID, '_byline', true );
		$author_url = esc_url( get_post_meta( $post->ID, '_url', true ) );
	$post_title = get_the_title( $post );
	$post_permalink = esc_url( get_post_permalink( $post->ID ) );
	$post_published_date = str_replace( ' ', 'T', $post->post_date_gmt );
	$language = get_bloginfo( 'language' );
	$organization = get_bloginfo( 'name' );
	$organization_url = esc_url( get_bloginfo( 'url' ) );
	$content = wp_strip_all_tags( apply_filters( 'the_content', $post->post_content ), true );
	$ld_json_output = <<<EOD
		<script type="application/ld+json">
		{
			"@context": "http://schema.org",
			"@type": "Review",
			"author": {
				"@type": "Person",
				"name": "$post_title",
				"jobTitle": "$byline",
				"sameAs": "$author_url"
			},
			"url": "$post_permalink",
			"datePublished": "$post_published_date",
			"description": "$content",
			"inLanguage": "$language",
			"itemReviewed": {
				"@type": "Organization",
				"name": "$organization",
				"sameAs": "$organization_url",
				"telephone": "+5511975414069"
			},
			"reviewRating": {
				"@type": "Rating",
				"worstRating": 1,
				"bestRating": 5,
				"ratingValue": 5
			}
		}
		</script>
EOD;
	return $ld_json_output;
}

/**
 * Adds LD+JSON markup for single testimonials.
 *
 * Adds LD+JSON markup for single testimonials.
 *
 * @since  1.6.0
 * @param WP_Post $post A WP_Post object.
 * @return string $ld_json_output The JSON+LD markup.
 */
function woothemes_testimonials_single_post_ld_json() {
	if ( ! is_singular( 'testimonial' ) ) {
		return;
	}
	global $post;
	echo woothemes_testimonials_generate_ld_json( $post ); // WPCS: XSS ok.
}
add_action( 'wp_head', 'woothemes_testimonials_single_post_ld_json' );
