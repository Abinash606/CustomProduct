<?php
if (!defined('ABSPATH')) {
	exit;
}

class SWP_Label_Studio_Ajax
{
	public function __construct()
	{
		add_action('wp_ajax_swp_ls_set_product', [$this, 'set_product']);
		add_action('wp_ajax_nopriv_swp_ls_set_product', [$this, 'set_product']);
		add_action('wp_ajax_swp_ls_save_design', array($this, 'save_design'));
		add_action('wp_ajax_nopriv_swp_ls_save_design', array($this, 'save_design'));
	}

	public function set_product()
	{
		check_ajax_referer('swp_ls_nonce', 'nonce');

		$product_id = absint($_POST['product_id'] ?? 0);

		if (!$product_id || !function_exists('WC')) {
			wp_send_json_error(__('Invalid product.', 'swp-label-studio'));
		}

		// Verify product exists
		$product = wc_get_product($product_id);
		if (!$product) {
			wp_send_json_error(__('Product not found.', 'swp-label-studio'));
		}

		// CRITICAL: Initialize WC session if not already started
		if (!WC()->session || !WC()->session->has_session()) {
			WC()->session->set_customer_session_cookie(true);
		}

		// Set the product ID in session
		WC()->session->set('swp_ls_product_id', $product_id);

		// Force save the session data immediately
		WC()->session->save_data();

		// Get designer page URL from settings
		$designer_page_id = get_option('swp_ls_designer_page_id');
		$designer_url = get_permalink($designer_page_id);

		// If no designer page set, find the page with shortcode
		if (!$designer_url) {
			$pages = get_posts(array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'numberposts' => -1,
			));

			foreach ($pages as $page) {
				if (has_shortcode($page->post_content, 'swp_label_studio')) {
					$designer_url = get_permalink($page->ID);
					break;
				}
			}
		}

		// Add product_id to URL as backup
		$designer_url = add_query_arg('product_id', $product_id, $designer_url);

		error_log('SWP Label Studio: Set product ID ' . $product_id . ' in session');
		error_log('SWP Label Studio: Session ID: ' . WC()->session->get_customer_id());
		error_log('SWP Label Studio: Designer URL: ' . $designer_url);

		wp_send_json_success([
			'product_id' => $product_id,
			'product_name' => $product->get_name(),
			'designer_url' => $designer_url,
			'session_id' => WC()->session->get_customer_id() 
		]);
	}

	// Make sure this exactly matches your file

	public function save_design()
	{
		check_ajax_referer('swp_ls_nonce', 'nonce');

		if (!function_exists('WC')) {
			wp_send_json_error(__('WooCommerce not active.', 'swp-label-studio'));
		}

		// Initialize session if needed
		if (!WC()->session || !WC()->session->has_session()) {
			WC()->session->set_customer_session_cookie(true);
		}

		// Get product_id from POST data (most reliable)
		$product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : null;

		// Fallback to session
		if (!$product_id) {
		$product_id = WC()->session->get('swp_ls_product_id');
		}

		$qty         = absint($_POST['qty'] ?? 1);
		$variation_id = absint($_POST['variation_id'] ?? 0);
		$design_json = wp_unslash($_POST['design_json'] ?? '');
		$design_png  = $_POST['design_png'] ?? '';

		if (!$product_id) {
			error_log('SWP Label Studio: No product ID found');
			wp_send_json_error(__('No product found. Please go back and launch designer again.', 'swp-label-studio'));
		}

		// Verify product exists
		$product = wc_get_product($product_id);
		if (!$product) {
			wp_send_json_error(__('Product not found.', 'swp-label-studio'));
		}

		// Validate JSON
		json_decode($design_json);
		if (json_last_error() !== JSON_ERROR_NONE) {
			wp_send_json_error(__('Invalid design JSON.', 'swp-label-studio'));
		}

		// Create unique design ID
		$design_id = uniqid('design_', true);

		// Setup directories
		$upload_dir = wp_upload_dir();
		$base_dir = $upload_dir['basedir'] . '/' . SWP_LS_UPLOAD_DIR . '/' . $design_id;
		$base_url = $upload_dir['baseurl'] . '/' . SWP_LS_UPLOAD_DIR . '/' . $design_id;

		wp_mkdir_p($base_dir);

		// Save JSON file
		file_put_contents($base_dir . '/design.json', $design_json);

		// Save PNG file
		$png_url = '';
		if (strpos($design_png, 'data:image/png;base64,') === 0) {
			$png_data = base64_decode(str_replace('data:image/png;base64,', '', $design_png));
			if ($png_data) {
				file_put_contents($base_dir . '/label.png', $png_data);
				$png_url = $base_url . '/label.png';
			}
		}

		// CRITICAL: Prepare cart item data with design information
		$cart_item_data = array(
			'swp_ls_design_id'        => $design_id,
			'swp_ls_design_json_url'  => $base_url . '/design.json',
			'swp_ls_design_png_url'   => $png_url,
		);

		error_log('SWP Label Studio: Adding to cart - Product ID: ' . $product_id);
		error_log('SWP Label Studio: Cart item data: ' . print_r($cart_item_data, true));

		// Add to cart with custom data
		$cart_item_key = WC()->cart->add_to_cart(
			$product_id,
			$qty,
			$variation_id,
			array(), // No variations
			$cart_item_data // CRITICAL: This passes the design data
		);

		if (!$cart_item_key) {
			error_log('SWP Label Studio: Failed to add to cart');
			wp_send_json_error(__('Failed to add to cart.', 'swp-label-studio'));
		}

		error_log('SWP Label Studio: Successfully added to cart - Key: ' . $cart_item_key);

		// Clear session product ID
		WC()->session->set('swp_ls_product_id', null);

		wp_send_json_success(array(
			'cart_url' => wc_get_cart_url(),
			'png_url'  => $png_url,
			'design_id' => $design_id,
			'message' => __('Design added to cart successfully!', 'swp-label-studio')
		));
	}
}