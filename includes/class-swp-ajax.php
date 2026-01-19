<?php
if (! defined('ABSPATH')) {
	exit;
}

class SWP_Label_Studio_Ajax
{

	public function __construct()
	{
		add_action('wp_ajax_swp_ls_save_design', array($this, 'save_design'));
		add_action('wp_ajax_nopriv_swp_ls_save_design', array($this, 'save_design'));
	}

	/**
	 * Save design and add to cart
	 */
	public function save_design()
	{
		check_ajax_referer('swp_ls_nonce', 'nonce');

		$product_id   = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
		$variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : 0;
		$qty          = isset($_POST['qty']) ? absint($_POST['qty']) : 1;
		$design_json  = isset($_POST['design_json']) ? stripslashes($_POST['design_json']) : '';
		$design_png   = isset($_POST['design_png']) ? $_POST['design_png'] : '';

		if (! $product_id) {
			wp_send_json_error(__('Invalid product ID.', 'swp-label-studio'));
		}

		if (empty($design_json)) {
			wp_send_json_error(__('No design data provided.', 'swp-label-studio'));
		}

		// Create unique design ID
		$design_id = uniqid('design_' . time() . '_');

		// Setup upload directories
		$upload_dir = wp_upload_dir();
		$base_dir   = $upload_dir['basedir'] . '/' . SWP_LS_UPLOAD_DIR . '/' . $design_id;
		$base_url   = $upload_dir['baseurl'] . '/' . SWP_LS_UPLOAD_DIR . '/' . $design_id;

		// Create directory
		if (! wp_mkdir_p($base_dir)) {
			wp_send_json_error(__('Failed to create upload directory.', 'swp-label-studio'));
		}

		// Save JSON file
		$json_saved = file_put_contents($base_dir . '/design.json', $design_json);
		if (! $json_saved) {
			wp_send_json_error(__('Failed to save design JSON.', 'swp-label-studio'));
		}

		// Save PNG file
		$png_path = '';
		if (! empty($design_png) && strpos($design_png, 'data:image/png;base64,') === 0) {
			$png_data = str_replace('data:image/png;base64,', '', $design_png);
			$png_data = base64_decode($png_data);

			if ($png_data !== false) {
				$png_saved = file_put_contents($base_dir . '/label.png', $png_data);
				if ($png_saved) {
					$png_path = $base_url . '/label.png';
				}
			}
		}

		// Add to WooCommerce Cart
		if (! function_exists('WC')) {
			wp_send_json_error(__('WooCommerce is not active.', 'swp-label-studio'));
		}

		// Prepare cart item data
		$cart_item_data = array(
			'swp_ls_design_id'       => $design_id,
			'swp_ls_design_json_url' => $base_url . '/design.json',
			'swp_ls_design_png_url'  => $png_path,
			'swp_ls_timestamp'       => current_time('mysql'),
		);

		// Add to cart
		$cart_item_key = WC()->cart->add_to_cart(
			$product_id,
			$qty,
			$variation_id,
			array(),
			$cart_item_data
		);

		if (! $cart_item_key) {
			wp_send_json_error(__('Failed to add product to cart.', 'swp-label-studio'));
		}

		// Success response
		wp_send_json_success(array(
			'design_id'  => $design_id,
			'cart_url'   => wc_get_cart_url(),
			'message'    => __('Design saved and added to cart successfully!', 'swp-label-studio'),
			'png_url'    => $png_path,
		));
	}
}
