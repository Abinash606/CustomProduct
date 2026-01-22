<?php
if (!defined('ABSPATH')) {
	exit;
}

class SWP_Label_Studio_WC
{
	public function __construct()
	{
		// Hide default Add to Cart button and add custom Launch Designer button
		add_action('woocommerce_single_product_summary', array($this, 'remove_add_to_cart_button'), 1);
		add_action('woocommerce_single_product_summary', array($this, 'render_launch_designer_button'), 30);

		// Enqueue button styles
		add_action('wp_enqueue_scripts', array($this, 'enqueue_button_styles'));

		// Cart hooks - CRITICAL for preserving design data
		add_filter('woocommerce_add_cart_item_data', array($this, 'add_cart_item_data'), 10, 3);
		add_filter('woocommerce_get_cart_item_from_session', array($this, 'get_cart_item_from_session'), 10, 3);
		add_filter('woocommerce_cart_item_name', array($this, 'cart_item_name'), 10, 3);
		add_filter('woocommerce_cart_item_thumbnail', array($this, 'cart_item_thumbnail'), 10, 3);

		// Checkout hooks
		add_action('woocommerce_checkout_create_order_line_item', array($this, 'add_order_item_meta'), 10, 4);

		// Order display hooks
		add_filter('woocommerce_order_item_name', array($this, 'order_item_name'), 10, 2);
		add_action('woocommerce_order_item_meta_end', array($this, 'display_order_item_meta'), 10, 3);

		// Admin order hooks
		add_action('woocommerce_admin_order_item_headers', array($this, 'admin_order_item_header'));
		add_action('woocommerce_admin_order_item_values', array($this, 'admin_order_item_values'), 10, 3);
	}

	/**
	 * Remove default Add to Cart button
	 */
	public function remove_add_to_cart_button()
	{
		remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
	}

	/**
	 * Enqueue custom button styles
	 */
	public function enqueue_button_styles()
	{
		if (is_product()) {
			wp_add_inline_style('swp-ls-designer', '
				.swp-launch-designer-wrapper {
					margin: 25px 0;
					padding: 20px 0;
					border-top: 1px solid #e5e7eb;
					border-bottom: 1px solid #e5e7eb;
				}
				
				.swp-launch-designer {
					display: inline-flex;
					align-items: center;
					justify-content: center;
					gap: 12px;
					background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
					color: #ffffff !important;
					font-size: 18px;
					font-weight: 700;
					padding: 18px 40px;
					border: none;
					border-radius: 50px;
					cursor: pointer;
					transition: all 0.3s ease;
					box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
					text-transform: uppercase;
					letter-spacing: 1px;
					position: relative;
					overflow: hidden;
				}
				
				.swp-launch-designer:before {
					content: "";
					position: absolute;
					top: 0;
					left: -100%;
					width: 100%;
					height: 100%;
					background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
					transition: left 0.5s;
				}
				
				.swp-launch-designer:hover:before {
					left: 100%;
				}
				
				.swp-launch-designer:hover {
					transform: translateY(-2px);
					box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
					background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
				}
				
				.swp-launch-designer:active {
					transform: translateY(0);
					box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
				}
				
				.swp-launch-designer i {
					font-size: 20px;
					animation: pulse 2s ease-in-out infinite;
				}
				
				@keyframes pulse {
					0%, 100% { opacity: 1; }
					50% { opacity: 0.7; }
				}
				
				.swp-designer-features {
					display: flex;
					flex-wrap: wrap;
					gap: 15px;
					margin-top: 20px;
					padding: 15px;
					background: #f8f9fa;
					border-radius: 12px;
				}
				
				.swp-feature-item {
					display: flex;
					align-items: center;
					gap: 8px;
					font-size: 14px;
					color: #64748b;
				}
				
				.swp-feature-item i {
					color: #667eea;
					font-size: 16px;
				}
				
				@media (max-width: 768px) {
					.swp-launch-designer {
						width: 100%;
						font-size: 16px;
						padding: 16px 30px;
					}
					
					.swp-designer-features {
						flex-direction: column;
					}
				}
			');
		}
	}

	/**
	 * Render styled Launch Designer button
	 */
	public function render_launch_designer_button()
	{
		global $product;

		if (!$product || !$product->get_id()) {
			return;
		}

?>
<div class="swp-launch-designer-wrapper">
    <button type="button" class="swp-launch-designer" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
        <i class="fa-solid fa-wand-magic-sparkles"></i>
        <span><?php esc_html_e('Design Your Label', 'swp-label-studio'); ?></span>
        <i class="fa-solid fa-arrow-right"></i>
    </button>

    <div class="swp-designer-features">
        <div class="swp-feature-item">
            <i class="fa-solid fa-palette"></i>
            <span><?php esc_html_e('Custom Templates', 'swp-label-studio'); ?></span>
        </div>
        <div class="swp-feature-item">
            <i class="fa-solid fa-images"></i>
            <span><?php esc_html_e('Add Your Photos', 'swp-label-studio'); ?></span>
        </div>
        <div class="swp-feature-item">
            <i class="fa-solid fa-text"></i>
            <span><?php esc_html_e('Custom Text & Fonts', 'swp-label-studio'); ?></span>
        </div>
        <div class="swp-feature-item">
            <i class="fa-solid fa-qrcode"></i>
            <span><?php esc_html_e('QR Codes & Graphics', 'swp-label-studio'); ?></span>
        </div>
        <div class="swp-feature-item">
            <i class="fa-solid fa-eye"></i>
            <span><?php esc_html_e('Live Preview', 'swp-label-studio'); ?></span>
        </div>
    </div>
</div>
<?php
	}

	/**
	 * Add custom data to cart item when added
	 */
	public function add_cart_item_data($cart_item_data, $product_id, $variation_id)
	{
		if (isset($cart_item_data['swp_ls_design_id'])) {
			$cart_item_data['unique_key'] = md5(microtime() . rand());
			error_log('SWP Label Studio: Adding cart item data - Design ID: ' . $cart_item_data['swp_ls_design_id']);
		}
		return $cart_item_data;
	}

	/**
	 * Get cart item from session
	 */
	public function get_cart_item_from_session($cart_item, $values, $key)
	{
		if (isset($values['swp_ls_design_id'])) {
			$cart_item['swp_ls_design_id'] = $values['swp_ls_design_id'];
		}

		if (isset($values['swp_ls_design_json_url'])) {
			$cart_item['swp_ls_design_json_url'] = $values['swp_ls_design_json_url'];
		}

		if (isset($values['swp_ls_design_png_url'])) {
			$cart_item['swp_ls_design_png_url'] = $values['swp_ls_design_png_url'];
		}

		return $cart_item;
	}

	/**
	 * Display custom design label in cart
	 */
	public function cart_item_name($name, $cart_item, $cart_item_key)
	{
		if (isset($cart_item['swp_ls_design_id'])) {
			$name .= '<br><small class="text-muted" style="font-size:0.85em;">
				<i class="fa-solid fa-paint-brush"></i> ' . __('Custom Design', 'swp-label-studio') . '
			</small>';

			if (isset($cart_item['swp_ls_design_png_url']) && !empty($cart_item['swp_ls_design_png_url'])) {
				$name .= '<br><a href="' . esc_url($cart_item['swp_ls_design_png_url']) . '" target="_blank" class="button btn-sm" style="font-size:0.8em; padding:4px 8px; margin-top:4px;">
					<i class="fa-solid fa-eye"></i> ' . __('View Design', 'swp-label-studio') . '
				</a>';
			}
		}
		return $name;
	}

	/**
	 * Replace product thumbnail with design preview in cart
	 */
	public function cart_item_thumbnail($image, $cart_item, $cart_item_key)
	{
		if (isset($cart_item['swp_ls_design_png_url']) && !empty($cart_item['swp_ls_design_png_url'])) {
			$image = '<img src="' . esc_url($cart_item['swp_ls_design_png_url']) . '" 
				class="attachment-woocommerce_thumbnail" 
				alt="' . esc_attr__('Custom Design', 'swp-label-studio') . '" 
				style="border: 2px solid #667eea; border-radius: 8px;">';
		}
		return $image;
	}

	/**
	 * Add design data to order line item meta
	 */
	public function add_order_item_meta($item, $cart_item_key, $values, $order)
	{
		if (isset($values['swp_ls_design_id'])) {
			$item->add_meta_data('_swp_ls_design_id', $values['swp_ls_design_id'], true);
			$item->add_meta_data('Design ID', $values['swp_ls_design_id'], false);
			error_log('SWP Label Studio: Saved design ID to order: ' . $values['swp_ls_design_id']);
		}

		if (isset($values['swp_ls_design_json_url'])) {
			$item->add_meta_data('_swp_ls_design_json_url', $values['swp_ls_design_json_url'], true);
		}

		if (isset($values['swp_ls_design_png_url'])) {
			$item->add_meta_data('_swp_ls_design_png_url', $values['swp_ls_design_png_url'], true);
			$item->add_meta_data('Design Preview', $values['swp_ls_design_png_url'], false);
		}
	}

	/**
	 * Display custom design label in order items
	 */
	public function order_item_name($name, $item)
	{
		if ($item->get_meta('_swp_ls_design_id')) {
			$name .= '<br><small class="text-muted">
				<i class="fa-solid fa-paint-brush"></i> ' . __('Custom Design', 'swp-label-studio') . '
			</small>';
		}
		return $name;
	}

	/**
	 * Display design preview link in order items
	 */
	public function display_order_item_meta($item_id, $item, $order)
	{
		$design_url = $item->get_meta('_swp_ls_design_png_url');
		if ($design_url) {
			echo '<div style="margin-top:10px;">
				<a href="' . esc_url($design_url) . '" target="_blank" class="button btn-sm" style="font-size:0.9em;">
					<i class="fa-solid fa-eye"></i> ' . __('View Design', 'swp-label-studio') . '
				</a>
			</div>';
		}
	}

	/**
	 * Add custom column header in admin order items table
	 */
	public function admin_order_item_header()
	{
		echo '<th class="item-design">' . __('Design', 'swp-label-studio') . '</th>';
	}

	/**
	 * Display design preview in admin order items table
	 */
	public function admin_order_item_values($product, $item, $item_id)
	{
		$design_png = $item->get_meta('_swp_ls_design_png_url');
		$design_json = $item->get_meta('_swp_ls_design_json_url');

		echo '<td class="item-design">';

		if ($design_png) {
			echo '<a href="' . esc_url($design_png) . '" target="_blank">
				<img src="' . esc_url($design_png) . '" style="max-width:80px; border:1px solid #ddd; border-radius:4px;">
			</a><br>';
		}

		if ($design_json) {
			echo '<a href="' . esc_url($design_json) . '" target="_blank" class="button button-small" style="margin-top:4px;">
				<i class="fa-solid fa-download"></i> ' . __('Download JSON', 'swp-label-studio') . '
			</a>';
		}

		if (!$design_png && !$design_json) {
			echo '<span class="description">' . __('No design', 'swp-label-studio') . '</span>';
		}

		echo '</td>';
	}
}