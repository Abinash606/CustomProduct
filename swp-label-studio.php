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

	protected static $instance = null;

	public static function get_instance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct()
	{
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	private function define_constants()
	{
		define('SWP_LS_VERSION', '1.0.0');
		define('SWP_LS_PATH', plugin_dir_path(__FILE__));
		define('SWP_LS_URL', plugin_dir_url(__FILE__));
		define('SWP_LS_BASENAME', plugin_basename(__FILE__));
		define('SWP_LS_UPLOAD_DIR', 'swp-label-studio');
	}

	private function includes()
	{
		require_once SWP_LS_PATH . 'includes/class-swp-admin.php';
		require_once SWP_LS_PATH . 'includes/class-swp-ajax.php';
		require_once SWP_LS_PATH . 'includes/class-swp-shortcode.php';
		require_once SWP_LS_PATH . 'includes/class-swp-wc.php';
	}

	private function init_hooks()
	{
		add_action('plugins_loaded', array($this, 'plugins_loaded'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

		// Page template support from plugin
		add_filter('theme_page_templates', array($this, 'register_page_template'));
		add_filter('template_include', array($this, 'load_page_template'));
	}

	public function plugins_loaded()
	{
		new SWP_Label_Studio_Admin();
		new SWP_Label_Studio_Ajax();
		new SWP_Label_Studio_Shortcode();
		new SWP_Label_Studio_WC();
	}

	/**
	 * Register plugin page template
	 */
	public function register_page_template($templates)
	{
		$templates['swp-label-studio-full.php'] = __('Label Studio – Full Width', 'swp-label-studio');
		return $templates;
	}

	/**
	 * Load plugin page template
	 */
	public function load_page_template($template)
	{
		if (is_page()) {
			$page_template = get_page_template_slug();
			if ('swp-label-studio-full.php' === $page_template) {
				return SWP_LS_PATH . 'templates/swp-label-studio-full.php';
			}
		}
		return $template;
	}

	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue_scripts()
	{

		$load_bootstrap = get_option('swp_ls_load_bootstrap', 'off');
		$load_fa        = get_option('swp_ls_load_fontawesome', 'off');
		$load_inter     = get_option('swp_ls_load_inter', 'on');

		if ('on' === $load_inter) {
			wp_enqueue_style(
				'swp-ls-inter',
				'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
				array(),
				null
			);
		}

		if ('on' === $load_bootstrap) {
			wp_enqueue_style(
				'swp-ls-bootstrap',
				'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
				array(),
				'5.3.2'
			);
			wp_enqueue_script(
				'swp-ls-bootstrap',
				'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
				array(),
				'5.3.2',
				true
			);
		}

		if ('on' === $load_fa) {
			wp_enqueue_style(
				'swp-ls-fontawesome',
				'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css',
				array(),
				'6.5.0'
			);
		}

		wp_enqueue_style(
			'swp-ls-designer',
			SWP_LS_URL . 'assets/css/designer.css',
			array(),
			SWP_LS_VERSION
		);

		wp_enqueue_script(
			'fabric',
			SWP_LS_URL . 'assets/vendor/fabric/fabric.min.js',
			array(),
			'5.3.1',
			true
		);

		wp_enqueue_script(
			'jspdf',
			SWP_LS_URL . 'assets/vendor/jspdf/jspdf.umd.min.js',
			array(),
			'2.5.1',
			true
		);

		wp_enqueue_script(
			'swp-ls-designer',
			SWP_LS_URL . 'assets/js/designer.js',
			array('jquery', 'fabric', 'jspdf'),
			SWP_LS_VERSION,
			true
		);

		wp_localize_script(
			'swp-ls-designer',
			'swp_ls_vars',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce'    => wp_create_nonce('swp_ls_nonce'),
				'assets_url' => SWP_LS_URL . 'assets/',
				'is_product' => is_product(),
				'current_product_id' => get_the_ID(),
			)
		);
	}
}

/**
 * Bootstrap plugin
 */
function swp_label_studio()
{
	return SWP_Label_Studio::get_instance();
}
swp_label_studio();
