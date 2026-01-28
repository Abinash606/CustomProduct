<?php

/**
 * Plugin Name: SWP Label Studio 2026
 * Plugin URI: https://springwaterpromotions.com
 * Description: A Fabric.js based label designer for WooCommerce.
 * Version: 1.0.0
 * Author: TSCAmerica.com
 * Text Domain: swp-label-studio
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 */

if (! defined('ABSPATH')) {
	exit;
}

define('SWP_LS_VERSION', '2.0.0');
define('SWP_LS_PATH', plugin_dir_path(__FILE__));
define('SWP_LS_URL', plugin_dir_url(__FILE__));
define('SWP_LS_BASENAME', plugin_basename(__FILE__));
define('SWP_LS_UPLOAD_DIR', 'swp-label-studio');

class SWP_Label_Studio
{

	/**
	 * Instance of this class.
	 * @var SWP_Label_Studio
	 */
	protected static $instance = null;

	public static function get_instance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct()
	{
		$this->includes();
		$this->init_hooks();
	}

	private function includes()
	{
		require_once SWP_LS_PATH . 'includes/class-swp-admin.php';
		require_once SWP_LS_PATH . 'includes/class-swp-ajax.php';
		require_once SWP_LS_PATH . 'includes/class-swp-shortcode.php';
		require_once SWP_LS_PATH . 'includes/class-swp-wc.php';
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks()
	{
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_action('plugins_loaded', array($this, 'plugins_loaded'));
		register_activation_hook(__FILE__, array($this, 'activate'));
	}

	/**
	 * Run on plugins_loaded
	 */
	public function plugins_loaded()
	{
		if (!class_exists('WooCommerce')) {
			add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
			return;
		}

		new SWP_Label_Studio_Admin();
		new SWP_Label_Studio_Ajax();
		new SWP_Label_Studio_Shortcode();
		new SWP_Label_Studio_WC();
	}

	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue_scripts()
	{
		// Only load on pages with shortcode, designer page, or product pages
		if (!$this->should_load_designer()) {
			return;
		}

		// Bootstrap
		wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css', array(), '5.3.2');
		wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', array(), '5.3.2', true);

		// Font Awesome
		wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', array(), '6.5.1');

		// Designer specific styles
		wp_enqueue_style('swp-ls-designer', SWP_LS_URL . 'assets/css/designer.css', array(), SWP_LS_VERSION);

		// Vendor Scripts
		wp_enqueue_script('fabric', SWP_LS_URL . 'assets/vendor/fabric/fabric.min.js', array(), '5.3.1', true);
		wp_enqueue_script('jspdf', SWP_LS_URL . 'assets/vendor/jspdf/jspdf.umd.min.js', array(), '2.5.1', true);
		wp_enqueue_script(
			'swp-ls-frontend',
			SWP_LS_URL . 'assets/js/frontend.js',
			['jquery'],
			SWP_LS_VERSION,
			true
		);
		// Designer JS
		wp_enqueue_script('swp-ls-designer', SWP_LS_URL . 'assets/js/designer.js', array('jquery', 'fabric', 'jspdf'), SWP_LS_VERSION, true);
		// Checkout / Price / Variant UI CSS
		wp_enqueue_style(
			'swp-ls-checkout',
			SWP_LS_URL . 'assets/css/swp-label-studio-checkout.css',
			array('bootstrap'),
			SWP_LS_VERSION
		);

		// Checkout / Price / Variant UI JS
		wp_enqueue_script(
			'swp-ls-checkout',
			SWP_LS_URL . 'assets/js/swp-label-studio-checkout.js',
			array('jquery'),
			SWP_LS_VERSION,
			true
		);

		// Localize Script
		wp_localize_script('swp-ls-designer', 'swp_ls_vars', array(
			'ajax_url'     => admin_url('admin-ajax.php'),
			'nonce'        => wp_create_nonce('swp_ls_nonce'),
			'export_scale' => get_option('swp_ls_export_scale', 2),
			'assets_url'   => SWP_LS_URL . 'assets/',
			'designer_url' => $this->get_designer_page_url(), // dynamically detect
		));
	}

	// Determine if JS should load
	private function should_load_designer()
	{
		global $post;

		// Load if shortcode exists
		if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'swp_label_studio')) {
			return true;
		}

		// Load on product pages
		if (is_product()) {
			return true;
		}

		// Load if specific GET params
		if (isset($_GET['designer']) || isset($_GET['product_id'])) {
			return true;
		}

		return false;
	}

	// Get the page URL that contains the [swp_label_studio] shortcode
	private function get_designer_page_url()
	{
		$pages = get_posts(array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'numberposts' => 1,
			's'           => '', // search can be skipped
		));

		foreach ($pages as $page) {
			if (has_shortcode($page->post_content, 'swp_label_studio')) {
				return get_permalink($page->ID);
			}
		}

		// fallback to home page if not found
		return home_url('/');
	}

	public function activate()
	{
		$upload_dir = wp_upload_dir();
		$design_dir = $upload_dir['basedir'] . '/' . SWP_LS_UPLOAD_DIR;

		if (!file_exists($design_dir)) {
			wp_mkdir_p($design_dir);
		}

		add_option('swp_ls_export_scale', 2);
		add_option('swp_ls_load_bootstrap', 'on');
		add_option('swp_ls_load_fontawesome', 'on');

		flush_rewrite_rules();
	}

	public function woocommerce_missing_notice()
	{
?>
<div class="notice notice-error">
    <p>
        <strong><?php _e('SWP Label Studio 2026', 'swp-label-studio'); ?></strong>
        <?php _e('requires WooCommerce to be installed and active.', 'swp-label-studio'); ?>
    </p>
</div>
<?php
	}
}

function swp_label_studio()
{
	return SWP_Label_Studio::get_instance();
}

swp_label_studio();

/**
 * Allow SVG uploads (Admin only)
 */
function swp_ls_allow_svg_uploads($mimes)
{
	if (!current_user_can('manage_options')) {
		return $mimes;
	}
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}
add_filter('upload_mimes', 'swp_ls_allow_svg_uploads');

/**
 * Fix SVG upload validation
 */
function swp_ls_fix_svg_mime_type($data, $file, $filename, $mimes)
{
	if (strtolower(substr($filename, -4)) === '.svg') {
		$data['ext']  = 'svg';
		$data['type'] = 'image/svg+xml';
	}
	return $data;
}
add_filter('wp_check_filetype_and_ext', 'swp_ls_fix_svg_mime_type', 10, 4);

/**
 * Fix SVG preview in Media Library
 */
function swp_ls_svg_media_preview()
{
	$screen = get_current_screen();
	if (!$screen || $screen->id !== 'upload') {
		return;
	}

	echo '<style>
        .wp-list-table img[src$=".svg"],
        .attachment img[src$=".svg"] {
            width: 80px;
            height: auto;
        }
    </style>';
}
add_action('admin_head', 'swp_ls_svg_media_preview');