<?php
if (!defined('ABSPATH')) {
	exit;
}

class SWP_Label_Studio_Shortcode
{
	public function __construct()
	{
		add_shortcode('swp_label_studio', [$this, 'render_designer']);
	}

	public function render_designer()
	{
		if (!function_exists('WC')) {
			return '<div class="alert alert-danger">WooCommerce not active.</div>';
		}

		// Make sure session is initialized
		if (!WC()->session) {
			WC()->initialize_session();
		}

		// Try to get product ID from URL parameter first, then session
		$product_id = isset($_GET['product_id']) ? absint($_GET['product_id']) : WC()->session->get('swp_ls_product_id');

		// If product_id is in URL but not in session, save it to session
		if (isset($_GET['product_id']) && absint($_GET['product_id'])) {
			$url_product_id = absint($_GET['product_id']);
			WC()->session->set('swp_ls_product_id', $url_product_id);
			$product_id = $url_product_id;
			error_log('SWP Label Studio: Set product ID from URL: ' . $product_id);
		}

		// Debug
		error_log('SWP Label Studio Shortcode: Product ID: ' . var_export($product_id, true));
		error_log('SWP Label Studio Shortcode: URL param: ' . var_export($_GET['product_id'] ?? 'none', true));
		error_log('SWP Label Studio Shortcode: Session value: ' . var_export(WC()->session->get('swp_ls_product_id'), true));

		if (!$product_id) {
			return '<div class="alert alert-warning">
            <h4>Designer Not Launched</h4>
            <p>Please launch the designer from a product page by clicking the "Launch Designer" button.</p>
            <p><a href="' . esc_url(home_url('/shop')) . '" class="btn btn-primary">Go to Shop</a></p>
        </div>';
		}

		$product = wc_get_product($product_id);

		if (!$product) {
			return '<div class="alert alert-danger">
            <h4>Invalid Product</h4>
            <p>The product you selected is no longer available.</p>
            <p><a href="' . esc_url(home_url('/shop')) . '" class="btn btn-primary">Go to Shop</a></p>
        </div>';
		}

		$image_url = '';
		$has_image = false;
		if ($product->get_image_id()) {
			$image_url = wp_get_attachment_url($product->get_image_id());
			$has_image = !empty($image_url);
		}

		// Debug
		error_log('SWP Label Studio: Rendering designer for product ' . $product_id);
		error_log('SWP Label Studio: Product name: ' . $product->get_name());
		error_log('SWP Label Studio: Product price: ' . $product->get_price());

		ob_start();
?>
		<div id="swp-ls-designer-app" class="swp-ls-container" data-product-id="<?php echo esc_attr($product_id); ?>"
			data-variation-id="0" data-qty="1" data-title="<?php echo esc_attr($product->get_name()); ?>"
			data-price="<?php echo esc_attr($product->get_price()); ?>" data-image="<?php echo esc_url($image_url); ?>"
			data-load-product-image="<?php echo $has_image ? 'true' : 'false'; ?>">
			<?php include SWP_LS_PATH . 'templates/designer.php'; ?>
		</div>

		<script>
			jQuery(document).ready(function($) {
				console.log('Designer App Data:', {
					productId: '<?php echo esc_js($product_id); ?>',
					title: '<?php echo esc_js($product->get_name()); ?>',
					price: '<?php echo esc_js($product->get_price()); ?>',
					image: '<?php echo esc_js($image_url); ?>',
					hasImage: <?php echo $has_image ? 'true' : 'false'; ?>
				});
			});
		</script>
<?php
		return ob_get_clean();
	}
}
