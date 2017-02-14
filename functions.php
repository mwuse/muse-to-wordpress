<?php
/**
 * Muse-to-Wordpress.com functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Muse-to-Wordpress.com
 */

if ( ! function_exists( 'muse_to_wordpress_com_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function muse_to_wordpress_com_setup() {
	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on Muse-to-Wordpress.com, use a find and replace
	 * to change 'muse-to-wordpress' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'muse-to-wordpress', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	//add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );

	add_theme_support( "title-tag" );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary', 'muse-to-wordpress' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	/*
	 * Enable support for Post Formats.
	 * See https://developer.wordpress.org/themes/functionality/post-formats/
	 */
	add_theme_support( 'post-formats', array(
		'aside',
		'image',
		'video',
		'quote',
		'link',
	) );

	add_theme_support( 'woocommerce' );

}
endif;
add_action( 'after_setup_theme', 'muse_to_wordpress_com_setup' );

/**
 * Define charset by language settings
 */
function mtw_init_charset()
{
	echo '<meta http-equiv="content-type" content="text/html; charset=' . get_bloginfo( 'charset' ) . '">';	
}

add_action( 'wp_head', 'mtw_init_charset' );
/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function muse_to_wordpress_com_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'muse_to_wordpress_com_content_width', 640 );
}
add_action( 'after_setup_theme', 'muse_to_wordpress_com_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function muse_to_wordpress_com_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'muse-to-wordpress' ),
		'id'            => 'sidebar-1',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'muse_to_wordpress_com_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function muse_to_wordpress_com_scripts() {
	wp_enqueue_style( 'muse-to-wordpress-com-style', get_stylesheet_uri() );

	wp_enqueue_script( 'muse-to-wordpress-com-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '20151215', true );

	wp_enqueue_script( 'muse-to-wordpress-com-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20151215', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'muse_to_wordpress_com_scripts' );


function mtw_load_custom_admin_styles() {
	//wp_register_style( 'mtw_dashicons', get_template_directory_uri() . '/dashicon/style.css', false, '1.0.0' );
	//wp_enqueue_style( 'mtw_dashicons' );
}
add_action( 'admin_enqueue_scripts', 'mtw_load_custom_admin_styles' );


require_once(ABSPATH . 'wp-admin/includes/file.php');
/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';

/**
 * Load TGM activation.
 */
require get_template_directory() . '/inc/TGM-Plugin-Activation-2.6.1/class-tgm-plugin-activation.php';

/**
 * Load Muse to Wordpress Tools.
 */
require get_template_directory() . '/mtw-lib/muse-wordpress.php';



function mtw_correct_adminbar_pos()
{
	?>
	<style type="text/css">
	html {
	    margin-top: 0px !important;
	}
	#wpadminbar
	{
		opacity: 0.5;
	}
	#wpadminbar:hover
	{
		opacity: 1;
	}
	</style>
	<?php
}

add_action( "wp_head" , "mtw_correct_adminbar_pos", 100 );