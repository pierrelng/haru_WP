<?php
/**
 * Twenty Seventeen functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 */

/**
 * Twenty Seventeen only works in WordPress 4.7 or later.
 */
if ( version_compare( $GLOBALS['wp_version'], '4.7-alpha', '<' ) ) {
	require get_template_directory() . '/inc/back-compat.php';
	return;
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function twentyseventeen_setup() {
	/*
	 * Make theme available for translation.
	 * Translations can be filed at WordPress.org. See: https://translate.wordpress.org/projects/wp-themes/twentyseventeen
	 * If you're building a theme based on Twenty Seventeen, use a find and replace
	 * to change 'twentyseventeen' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'twentyseventeen' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );

	add_image_size( 'twentyseventeen-featured-image', 2000, 1200, true );

	add_image_size( 'twentyseventeen-thumbnail-avatar', 100, 100, true );

	// Set the default content width.
	$GLOBALS['content_width'] = 525;

	// This theme uses wp_nav_menu() in two locations.
	register_nav_menus( array(
		'top'    => __( 'Top Menu', 'twentyseventeen' ),
		'social' => __( 'Social Links Menu', 'twentyseventeen' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	/*
	 * Enable support for Post Formats.
	 *
	 * See: https://codex.wordpress.org/Post_Formats
	 */
	add_theme_support( 'post-formats', array(
		'aside',
		'image',
		'video',
		'quote',
		'link',
		'gallery',
		'audio',
	) );

	// Add theme support for Custom Logo.
	add_theme_support( 'custom-logo', array(
		'width'       => 250,
		'height'      => 250,
		'flex-width'  => true,
	) );

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/*
	 * This theme styles the visual editor to resemble the theme style,
	 * specifically font, colors, and column width.
 	 */
	add_editor_style( array( 'assets/css/editor-style.css', twentyseventeen_fonts_url() ) );

	// Define and register starter content to showcase the theme on new sites.
	$starter_content = array(
		'widgets' => array(
			// Place three core-defined widgets in the sidebar area.
			'sidebar-1' => array(
				'text_business_info',
				'search',
				'text_about',
			),

			// Add the core-defined business info widget to the footer 1 area.
			'sidebar-2' => array(
				'text_business_info',
			),

			// Put two core-defined widgets in the footer 2 area.
			'sidebar-3' => array(
				'text_about',
				'search',
			),
		),

		// Specify the core-defined pages to create and add custom thumbnails to some of them.
		'posts' => array(
			'home',
			'about' => array(
				'thumbnail' => '{{image-sandwich}}',
			),
			'contact' => array(
				'thumbnail' => '{{image-espresso}}',
			),
			'blog' => array(
				'thumbnail' => '{{image-coffee}}',
			),
			'homepage-section' => array(
				'thumbnail' => '{{image-espresso}}',
			),
		),

		// Create the custom image attachments used as post thumbnails for pages.
		'attachments' => array(
			'image-espresso' => array(
				'post_title' => _x( 'Espresso', 'Theme starter content', 'twentyseventeen' ),
				'file' => 'assets/images/espresso.jpg', // URL relative to the template directory.
			),
			'image-sandwich' => array(
				'post_title' => _x( 'Sandwich', 'Theme starter content', 'twentyseventeen' ),
				'file' => 'assets/images/sandwich.jpg',
			),
			'image-coffee' => array(
				'post_title' => _x( 'Coffee', 'Theme starter content', 'twentyseventeen' ),
				'file' => 'assets/images/coffee.jpg',
			),
		),

		// Default to a static front page and assign the front and posts pages.
		'options' => array(
			'show_on_front' => 'page',
			'page_on_front' => '{{home}}',
			'page_for_posts' => '{{blog}}',
		),

		// Set the front page section theme mods to the IDs of the core-registered pages.
		'theme_mods' => array(
			'panel_1' => '{{homepage-section}}',
			'panel_2' => '{{about}}',
			'panel_3' => '{{blog}}',
			'panel_4' => '{{contact}}',
		),

		// Set up nav menus for each of the two areas registered in the theme.
		'nav_menus' => array(
			// Assign a menu to the "top" location.
			'top' => array(
				'name' => __( 'Top Menu', 'twentyseventeen' ),
				'items' => array(
					'link_home', // Note that the core "home" page is actually a link in case a static front page is not used.
					'page_about',
					'page_blog',
					'page_contact',
				),
			),

			// Assign a menu to the "social" location.
			'social' => array(
				'name' => __( 'Social Links Menu', 'twentyseventeen' ),
				'items' => array(
					'link_yelp',
					'link_facebook',
					'link_twitter',
					'link_instagram',
					'link_email',
				),
			),
		),
	);

	/**
	 * Filters Twenty Seventeen array of starter content.
	 *
	 * @since Twenty Seventeen 1.1
	 *
	 * @param array $starter_content Array of starter content.
	 */
	$starter_content = apply_filters( 'twentyseventeen_starter_content', $starter_content );

	add_theme_support( 'starter-content', $starter_content );
}
add_action( 'after_setup_theme', 'twentyseventeen_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function twentyseventeen_content_width() {

	$content_width = $GLOBALS['content_width'];

	// Get layout.
	$page_layout = get_theme_mod( 'page_layout' );

	// Check if layout is one column.
	if ( 'one-column' === $page_layout ) {
		if ( twentyseventeen_is_frontpage() ) {
			$content_width = 644;
		} elseif ( is_page() ) {
			$content_width = 740;
		}
	}

	// Check if is single post and there is no sidebar.
	if ( is_single() && ! is_active_sidebar( 'sidebar-1' ) ) {
		$content_width = 740;
	}

	/**
	 * Filter Twenty Seventeen content width of the theme.
	 *
	 * @since Twenty Seventeen 1.0
	 *
	 * @param int $content_width Content width in pixels.
	 */
	$GLOBALS['content_width'] = apply_filters( 'twentyseventeen_content_width', $content_width );
}
add_action( 'template_redirect', 'twentyseventeen_content_width', 0 );

/**
 * Register custom fonts.
 */
function twentyseventeen_fonts_url() {
	$fonts_url = '';

	/*
	 * Translators: If there are characters in your language that are not
	 * supported by Libre Franklin, translate this to 'off'. Do not translate
	 * into your own language.
	 */
	$libre_franklin = _x( 'on', 'Libre Franklin font: on or off', 'twentyseventeen' );

	if ( 'off' !== $libre_franklin ) {
		$font_families = array();

		$font_families[] = 'Libre Franklin:300,300i,400,400i,600,600i,800,800i';

		$query_args = array(
			'family' => urlencode( implode( '|', $font_families ) ),
			'subset' => urlencode( 'latin,latin-ext' ),
		);

		$fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
	}

	return esc_url_raw( $fonts_url );
}

/**
 * Add preconnect for Google Fonts.
 *
 * @since Twenty Seventeen 1.0
 *
 * @param array  $urls           URLs to print for resource hints.
 * @param string $relation_type  The relation type the URLs are printed.
 * @return array $urls           URLs to print for resource hints.
 */
function twentyseventeen_resource_hints( $urls, $relation_type ) {
	if ( wp_style_is( 'twentyseventeen-fonts', 'queue' ) && 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href' => 'https://fonts.gstatic.com',
			'crossorigin',
		);
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'twentyseventeen_resource_hints', 10, 2 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function twentyseventeen_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Blog Sidebar', 'twentyseventeen' ),
		'id'            => 'sidebar-1',
		'description'   => __( 'Add widgets here to appear in your sidebar on blog posts and archive pages.', 'twentyseventeen' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => __( 'Footer 1', 'twentyseventeen' ),
		'id'            => 'sidebar-2',
		'description'   => __( 'Add widgets here to appear in your footer.', 'twentyseventeen' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => __( 'Footer 2', 'twentyseventeen' ),
		'id'            => 'sidebar-3',
		'description'   => __( 'Add widgets here to appear in your footer.', 'twentyseventeen' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'twentyseventeen_widgets_init' );

/**
 * Replaces "[...]" (appended to automatically generated excerpts) with ... and
 * a 'Continue reading' link.
 *
 * @since Twenty Seventeen 1.0
 *
 * @param string $link Link to single post/page.
 * @return string 'Continue reading' link prepended with an ellipsis.
 */
function twentyseventeen_excerpt_more( $link ) {
	if ( is_admin() ) {
		return $link;
	}

	$link = sprintf( '<p class="link-more"><a href="%1$s" class="more-link">%2$s</a></p>',
		esc_url( get_permalink( get_the_ID() ) ),
		/* translators: %s: Name of current post */
		sprintf( __( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'twentyseventeen' ), get_the_title( get_the_ID() ) )
	);
	return ' &hellip; ' . $link;
}
add_filter( 'excerpt_more', 'twentyseventeen_excerpt_more' );

/**
 * Handles JavaScript detection.
 *
 * Adds a `js` class to the root `<html>` element when JavaScript is detected.
 *
 * @since Twenty Seventeen 1.0
 */
function twentyseventeen_javascript_detection() {
	echo "<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>\n";
}
add_action( 'wp_head', 'twentyseventeen_javascript_detection', 0 );

/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 */
function twentyseventeen_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">' . "\n", get_bloginfo( 'pingback_url' ) );
	}
}
add_action( 'wp_head', 'twentyseventeen_pingback_header' );

/**
 * Display custom color CSS.
 */
function twentyseventeen_colors_css_wrap() {
	if ( 'custom' !== get_theme_mod( 'colorscheme' ) && ! is_customize_preview() ) {
		return;
	}

	require_once( get_parent_theme_file_path( '/inc/color-patterns.php' ) );
	$hue = absint( get_theme_mod( 'colorscheme_hue', 250 ) );
?>
	<style type="text/css" id="custom-theme-colors" <?php if ( is_customize_preview() ) { echo 'data-hue="' . $hue . '"'; } ?>>
		<?php echo twentyseventeen_custom_colors_css(); ?>
	</style>
<?php }
add_action( 'wp_head', 'twentyseventeen_colors_css_wrap' );

/**
 * Enqueue scripts and styles.
 */
function twentyseventeen_scripts() {
	// Add custom fonts, used in the main stylesheet.
	wp_enqueue_style( 'twentyseventeen-fonts', twentyseventeen_fonts_url(), array(), null );

	// Theme stylesheet.
	wp_enqueue_style( 'twentyseventeen-style', get_stylesheet_uri() );

	// Load the dark colorscheme.
	if ( 'dark' === get_theme_mod( 'colorscheme', 'light' ) || is_customize_preview() ) {
		wp_enqueue_style( 'twentyseventeen-colors-dark', get_theme_file_uri( '/assets/css/colors-dark.css' ), array( 'twentyseventeen-style' ), '1.0' );
	}

	// Load the Internet Explorer 9 specific stylesheet, to fix display issues in the Customizer.
	if ( is_customize_preview() ) {
		wp_enqueue_style( 'twentyseventeen-ie9', get_theme_file_uri( '/assets/css/ie9.css' ), array( 'twentyseventeen-style' ), '1.0' );
		wp_style_add_data( 'twentyseventeen-ie9', 'conditional', 'IE 9' );
	}

	// Load the Internet Explorer 8 specific stylesheet.
	wp_enqueue_style( 'twentyseventeen-ie8', get_theme_file_uri( '/assets/css/ie8.css' ), array( 'twentyseventeen-style' ), '1.0' );
	wp_style_add_data( 'twentyseventeen-ie8', 'conditional', 'lt IE 9' );

	// Load the html5 shiv.
	wp_enqueue_script( 'html5', get_theme_file_uri( '/assets/js/html5.js' ), array(), '3.7.3' );
	wp_script_add_data( 'html5', 'conditional', 'lt IE 9' );

	wp_enqueue_script( 'twentyseventeen-skip-link-focus-fix', get_theme_file_uri( '/assets/js/skip-link-focus-fix.js' ), array(), '1.0', true );

	$twentyseventeen_l10n = array(
		'quote'          => twentyseventeen_get_svg( array( 'icon' => 'quote-right' ) ),
	);

	if ( has_nav_menu( 'top' ) ) {
		wp_enqueue_script( 'twentyseventeen-navigation', get_theme_file_uri( '/assets/js/navigation.js' ), array( 'jquery' ), '1.0', true );
		$twentyseventeen_l10n['expand']         = __( 'Expand child menu', 'twentyseventeen' );
		$twentyseventeen_l10n['collapse']       = __( 'Collapse child menu', 'twentyseventeen' );
		$twentyseventeen_l10n['icon']           = twentyseventeen_get_svg( array( 'icon' => 'angle-down', 'fallback' => true ) );
	}

	wp_enqueue_script( 'twentyseventeen-global', get_theme_file_uri( '/assets/js/global.js' ), array( 'jquery' ), '1.0', true );

	wp_enqueue_script( 'jquery-scrollto', get_theme_file_uri( '/assets/js/jquery.scrollTo.js' ), array( 'jquery' ), '2.1.2', true );

	wp_localize_script( 'twentyseventeen-skip-link-focus-fix', 'twentyseventeenScreenReaderText', $twentyseventeen_l10n );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'twentyseventeen_scripts' );

/**
 * Add custom image sizes attribute to enhance responsive image functionality
 * for content images.
 *
 * @since Twenty Seventeen 1.0
 *
 * @param string $sizes A source size value for use in a 'sizes' attribute.
 * @param array  $size  Image size. Accepts an array of width and height
 *                      values in pixels (in that order).
 * @return string A source size value for use in a content image 'sizes' attribute.
 */
function twentyseventeen_content_image_sizes_attr( $sizes, $size ) {
	$width = $size[0];

	if ( 740 <= $width ) {
		$sizes = '(max-width: 706px) 89vw, (max-width: 767px) 82vw, 740px';
	}

	if ( is_active_sidebar( 'sidebar-1' ) || is_archive() || is_search() || is_home() || is_page() ) {
		if ( ! ( is_page() && 'one-column' === get_theme_mod( 'page_options' ) ) && 767 <= $width ) {
			 $sizes = '(max-width: 767px) 89vw, (max-width: 1000px) 54vw, (max-width: 1071px) 543px, 580px';
		}
	}

	return $sizes;
}
add_filter( 'wp_calculate_image_sizes', 'twentyseventeen_content_image_sizes_attr', 10, 2 );

/**
 * Filter the `sizes` value in the header image markup.
 *
 * @since Twenty Seventeen 1.0
 *
 * @param string $html   The HTML image tag markup being filtered.
 * @param object $header The custom header object returned by 'get_custom_header()'.
 * @param array  $attr   Array of the attributes for the image tag.
 * @return string The filtered header image HTML.
 */
function twentyseventeen_header_image_tag( $html, $header, $attr ) {
	if ( isset( $attr['sizes'] ) ) {
		$html = str_replace( $attr['sizes'], '100vw', $html );
	}
	return $html;
}
add_filter( 'get_header_image_tag', 'twentyseventeen_header_image_tag', 10, 3 );

/**
 * Add custom image sizes attribute to enhance responsive image functionality
 * for post thumbnails.
 *
 * @since Twenty Seventeen 1.0
 *
 * @param array $attr       Attributes for the image markup.
 * @param int   $attachment Image attachment ID.
 * @param array $size       Registered image size or flat array of height and width dimensions.
 * @return string A source size value for use in a post thumbnail 'sizes' attribute.
 */
function twentyseventeen_post_thumbnail_sizes_attr( $attr, $attachment, $size ) {
	if ( is_archive() || is_search() || is_home() ) {
		$attr['sizes'] = '(max-width: 767px) 89vw, (max-width: 1000px) 54vw, (max-width: 1071px) 543px, 580px';
	} else {
		$attr['sizes'] = '100vw';
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'twentyseventeen_post_thumbnail_sizes_attr', 10, 3 );

/**
 * Use front-page.php when Front page displays is set to a static page.
 *
 * @since Twenty Seventeen 1.0
 *
 * @param string $template front-page.php.
 *
 * @return string The template to be used: blank if is_home() is true (defaults to index.php), else $template.
 */
function twentyseventeen_front_page_template( $template ) {
	return is_home() ? '' : $template;
}
add_filter( 'frontpage_template',  'twentyseventeen_front_page_template' );

/**
 * Implement the Custom Header feature.
 */
require get_parent_theme_file_path( '/inc/custom-header.php' );

/**
 * Custom template tags for this theme.
 */
require get_parent_theme_file_path( '/inc/template-tags.php' );

/**
 * Additional features to allow styling of the templates.
 */
require get_parent_theme_file_path( '/inc/template-functions.php' );

/**
 * Customizer additions.
 */
require get_parent_theme_file_path( '/inc/customizer.php' );

/**
 * SVG icons functions and filters.
 */
require get_parent_theme_file_path( '/inc/icon-functions.php' );

// ---------------------------------------------------------------------------
// ---------------------------------------------------------------------------
// ---------------------------------------------------------------------------
// Custom functions ----------------------------------------------------------
// ---------------------------------------------------------------------------
// ---------------------------------------------------------------------------
// ---------------------------------------------------------------------------

// Show metadata when GET /wp-json/wp/v2/events ------------------------------
// ---------------------------------------------------------------------------

register_rest_field( 'events', 'details', array(
    'get_callback' => function ( $data ) {
		// var_dump($data);
        return get_post_meta( $data['id'] );
    }
));

// New endpoint POST /wp-json/haru/v1/events ---------------------------------
// ---------------------------------------------------------------------------

add_action( 'rest_api_init', 'haru_register_routes' );

/**
 * Register the /wp-json/haru/v1/events route
 */
function haru_register_routes() {
    register_rest_route( 'haru/v1', 'events', array(
        'methods'  => WP_REST_Server::CREATABLE,
        'callback' => 'haru_insert_events',
    ) );
	register_rest_route( 'haru/v1', 'organizers/facebook', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'haru_get_organizers_facebook'
    ) );
    register_rest_route( 'haru/v1', 'venues/facebook', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'haru_get_venues_facebook'
    ) );
    register_rest_route( 'haru/v1', 'venues/unwanted', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'haru_get_venues_unwanted'
    ) );
	register_rest_route( 'haru/v1', 'events/facebook', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'haru_get_events_facebook'
    ) );
}

/**
 * Generate results for the /wp-json/haru/v1/events route.
 *
 * @param WP_REST_Request $request Full details about the request.
 *
 * @return WP_REST_Response|WP_Error The response for the request.
 */
function haru_insert_events( WP_REST_Request $request ) {
	// get sent json body
	$item = $request->get_json_params();

	if (
		empty( $item['name'] ) ||
		empty( $item['description'] ) ||
		empty( $item['id'] ) ||
		empty( $item['start_time'] ) ||
		empty( $item['end_time'] ) ||
		// empty( $item['place']['name'] ) ||
		// empty( $item['place']['id'] ) ||
		// empty( $item['place']['location']['country'] ) ||
		// empty( $item['place']['location']['city'] ) ||
		// empty( $item['place']['location']['latitude'] ) ||
		// empty( $item['place']['location']['longitude'] ) ||
		// empty( $item['place']['location']['street'] ) ||
		// empty( $item['place']['location']['zip'] ) ||
		empty( $item['cover']['source'] ) ||
		empty( $item['cover']['height'] ) ||
		empty( $item['cover']['width'] )
	) {
		return new WP_Error( 'missing_data', 'Incomplete json data', array( 'status' => 400 ) );
	}

	// vars
	$my_post = array(
		'post_title'	=> $item['name'],
		'post_type'		=> 'events',
		'post_status'	=> 'pending'
	);
	// insert the post into the database
	$post_id = wp_insert_post( $my_post );

	// save Facebook link
	$field_key = "field_5975ec6ef72b2";
	$value = "https://www.facebook.com/events/".$item['id'];
	update_field( $field_key, $value, $post_id );

	// save description
	$field_key = "field_5964cbd27567d";
	$value = $item['description'];
	update_field( $field_key, $value, $post_id );

	// save Dateheure début
	$field_key = "field_5975ebaaf72b0";
	$myDateTime = new DateTime($item['start_time']);
	$value = $myDateTime->format('Y-m-d H:i:s'); // https://support.advancedcustomfields.com/forums/topic/date-time-picker-and-post-loop/
	// return $value;
	update_field( $field_key, $value, $post_id );

	// save Dateheure fin
	$field_key = "field_5975ec47f72b1";
	$myDateTime = new DateTime($item['end_time']);
	$value = $myDateTime->format('Y-m-d H:i:s');
	update_field( $field_key, $value, $post_id );

	// Id meta
	add_post_meta( $post_id, 'facebook_id', $item['id'] );

	// Place metas
	add_post_meta( $post_id, 'place_name', $item['place']['name'] );
	add_post_meta( $post_id, 'place_id', $item['place']['id'] );
	add_post_meta( $post_id, 'location_country', $item['place']['location']['country'] );
	add_post_meta( $post_id, 'location_city', $item['place']['location']['city'] );
	add_post_meta( $post_id, 'location_latitude', $item['place']['location']['latitude'] );
	add_post_meta( $post_id, 'location_longitude', $item['place']['location']['longitude'] );
	add_post_meta( $post_id, 'location_street', $item['place']['location']['street'] );
	add_post_meta( $post_id, 'location_zip', $item['place']['location']['zip'] );

	// Cover metas
	add_post_meta( $post_id, 'cover_source', $item['cover']['source'] );
	add_post_meta( $post_id, 'cover_height', $item['cover']['height'] );
	add_post_meta( $post_id, 'cover_width', $item['cover']['width'] );

    // Return either a WP_REST_Response or WP_Error object
    // return $post_id;
	return new WP_REST_Response($post_id, 200);
}

function haru_get_organizers_facebook() {

	global $wpdb;
	$querystr = "
		SELECT $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->postmeta.meta_id, $wpdb->postmeta.meta_key, $wpdb->postmeta.meta_value
		FROM $wpdb->posts
		INNER JOIN $wpdb->postmeta
		ON $wpdb->posts.ID = $wpdb->postmeta.post_id
		WHERE $wpdb->posts.post_type = 'organizers'
		AND $wpdb->posts.post_status = 'publish'
		AND $wpdb->postmeta.meta_key = 'facebook_page'
    ";
	$results = $wpdb->get_results($querystr);

	if (empty($results)) {
		return new WP_Error( 'haru_no_data', 'No data returned', array( 'status' => 400 ) );
	}
	return $results;
}

function haru_get_venues_facebook() {

	global $wpdb;
	$querystr = "
		SELECT $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->postmeta.meta_id, $wpdb->postmeta.meta_key, $wpdb->postmeta.meta_value
		FROM $wpdb->posts
		INNER JOIN $wpdb->postmeta
		ON $wpdb->posts.ID = $wpdb->postmeta.post_id
		WHERE $wpdb->posts.post_type = 'venues'
		AND $wpdb->posts.post_status = 'publish'
		AND $wpdb->postmeta.meta_key = 'facebook_page'
    ";
	$results = $wpdb->get_results($querystr);

	if (empty($results)) {
		return new WP_Error( 'haru_no_data', 'No data returned', array( 'status' => 400 ) );
	}
	return $results;
}

function haru_get_venues_unwanted() {

	global $wpdb;
	$querystr = "
		SELECT $wpdb->posts.ID
		FROM $wpdb->posts
		INNER JOIN $wpdb->postmeta
		ON $wpdb->posts.ID = $wpdb->postmeta.post_id
		WHERE $wpdb->posts.post_type = 'venues'
		AND $wpdb->posts.post_status = 'publish'
		AND $wpdb->postmeta.meta_key = 'type'
		AND ($wpdb->postmeta.meta_value LIKE '%public%' OR $wpdb->postmeta.meta_value LIKE '%culturel%')
    ";
	$results = $wpdb->get_results($querystr);

	if (empty($results)) {
		return new WP_Error( 'haru_no_data', 'No data returned', array( 'status' => 400 ) );
	}
	return $results;
}

function haru_get_events_facebook() {

	global $wpdb;
	$querystr = "
		SELECT $wpdb->posts.ID, $wpdb->postmeta.meta_value
		FROM $wpdb->posts
		INNER JOIN $wpdb->postmeta
		ON $wpdb->posts.ID = $wpdb->postmeta.post_id
		WHERE $wpdb->posts.post_type = 'events'
		AND $wpdb->postmeta.meta_key = 'facebook_event_url'
    ";
	$results = $wpdb->get_results($querystr);

	if (empty($results)) {
		return new WP_Error( 'haru_no_data', 'No data returned', array( 'status' => 400 ) );
	}
	return $results;
}

// Add custom columns in Events dashboard and sort them ----------------------
// ---------------------------------------------------------------------------

// http://justintadlock.com/archives/2011/06/27/custom-columns-for-custom-post-types
// https://www.ractoon.com/2016/11/wordpress-custom-sortable-admin-columns-for-custom-posts/
// https://code.tutsplus.com/articles/quick-tip-make-your-custom-column-sortable--wp-25095

add_filter( 'manage_events_posts_columns', 'haru_custom_events_columns' );
function haru_custom_events_columns( $columns ) {
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Nom' ),
		'start_time' => __( 'Début de l\'event' ),
		'date' => __( 'Date d\'ajout' )
	);
	return $columns;
}

add_action( 'manage_events_posts_custom_column', 'haru_manage_events_columns', 10, 2 );
function haru_manage_events_columns( $column, $post_id ) {
	global $post;
	switch( $column ) {
		case 'start_time' :
			$start_time = get_post_meta( $post_id, 'start_time', true );
			if (empty($start_time)) {
				echo __('Inconnu');
			} else {
				$datetime = new DateTime($start_time);
				$value = $datetime->format('d/m/y - H:i');
				printf($value);
			}
			break;

		/* Just break out of the switch statement for everything else. */
		default :
			break;
	}
}

add_filter( 'manage_edit-events_sortable_columns', 'haru_set_custom_events_sortable_columns' );
function haru_set_custom_events_sortable_columns( $columns ) {
	$columns['start_time'] = 'start_time';
	return $columns;
}

add_action('admin_head', 'haru_admin_column_width'); // https://wordpress.stackexchange.com/a/85045
function haru_admin_column_width() {
    echo '<style type="text/css">
        .column-start_time { text-align: left; width:200px !important; overflow:hidden }
        .column-date { text-align: left; width:150px !important; overflow:hidden }
    </style>';
}

add_action( 'pre_get_posts', 'haru_events_custom_orderby' ); // https://wordpress.stackexchange.com/a/85045
function haru_events_custom_orderby( $query ) {
	if ( ! is_admin() )
		return;

	$orderby = $query->get( 'orderby');

	if ( 'start_time' == $orderby ) {
		$query->set( 'meta_key', 'start_time' );
		$query->set( 'orderby', 'meta_value' );
	}
}
