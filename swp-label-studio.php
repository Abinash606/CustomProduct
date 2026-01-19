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

/**
 * Main Plugin Class
 */
class SWP_Label_Studio
{

	/**
	 * Instance of this class.
	 * @var SWP_Label_Studio
	 */
	protected static $instance = null;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Return an instance of this class.
	 * @return SWP_Label_Studio
	 */
	public static function get_instance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Define constants
	 */
	private function define_constants()
	{
		define('SWP_LS_VERSION', '1.0.0');
		define('SWP_LS_PATH', plugin_dir_path(__FILE__));
		define('SWP_LS_URL', plugin_dir_url(__FILE__));
		define('SWP_LS_BASENAME', plugin_basename(__FILE__));
		define('SWP_LS_UPLOAD_DIR', 'swp-label-studio');
	}

	/**
	 * Include required files
	 */
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
	}

	/**
	 * Run on plugins_loaded
	 */
	public function plugins_loaded()
	{
		// Initialize components
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
		$load_bootstrap = get_option('swp_ls_load_bootstrap', 'off');
		$load_fa = get_option('swp_ls_load_fontawesome', 'off');
		$load_inter = get_option('swp_ls_load_inter', 'on');

		// Enqueue Inter Font
		if ('on' === $load_inter) {
			wp_enqueue_style('swp-ls-inter', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap', array(), null);
		}

		// Conditional Bootstrap
		if ('on' === $load_bootstrap) {
			// Only enqueue if not already present
			if (! wp_style_is('bootstrap', 'enqueued') && ! wp_style_is('bootstrap-css', 'enqueued')) {
				wp_enqueue_style('swp-ls-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css', array(), '5.3.2');
				wp_enqueue_script('swp-ls-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', array(), '5.3.2', true);
			}
		}

		// Conditional FontAwesome
		if ('on' === $load_fa) {
			if (! wp_style_is('font-awesome', 'enqueued') && ! wp_style_is('fontawesome', 'enqueued')) {
				wp_enqueue_style('swp-ls-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css', array(), '6.5.0');
			}
		}

		// Designer specific styles
		wp_enqueue_style('swp-ls-designer', SWP_LS_URL . 'assets/css/designer.css', array(), SWP_LS_VERSION);

		// Vendor Scripts
		wp_enqueue_script('fabric', SWP_LS_URL . 'assets/vendor/fabric/fabric.min.js', array(), '5.3.1', true);
		wp_enqueue_script('jspdf', SWP_LS_URL . 'assets/vendor/jspdf/jspdf.umd.min.js', array(), '2.5.1', true);

		// Designer JS
		wp_enqueue_script('swp-ls-designer', SWP_LS_URL . 'assets/js/designer.js', array('jquery', 'fabric', 'jspdf'), SWP_LS_VERSION, true);

		// Localize Script
		wp_localize_script('swp-ls-designer', 'swp_ls_vars', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce'    => wp_create_nonce('swp_ls_nonce'),
			'assets_url' => SWP_LS_URL . 'assets/',
			'is_product' => is_product(),
			'current_product_id' => get_the_ID(),
		));
	}
}

// Start the plugin
function swp_label_studio()
{
	return SWP_Label_Studio::get_instance();
}
swp_label_studio();