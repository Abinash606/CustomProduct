<?php
if (!defined('ABSPATH')) {
	exit;
}

class SWP_Label_Studio_WC
{
	public function __construct()
	{
		add_action('woocommerce_after_add_to_cart_button', [$this, 'render_launch_designer_button']);
		add_filter('woocommerce_cart_item_name', array($this, 'cart_item_name'), 10, 3);
		add_filter('woocommerce_cart_item_thumbnail', array($this, 'cart_item_thumbnail'), 10, 3);
		add_action('woocommerce_checkout_create_order_line_item', array($this, 'add_order_item_meta'), 10, 4);
		add_filter('woocommerce_order_item_meta_end', array($this, 'display_order_item_meta'), 10, 3);
	}

	public function render_launch_designer_button()
	{
		global $product;

		if (!$product || !$product->get_id()) {
			return;
		}

		echo '<button 
            type="button"
            class="button swp-launch-designer"
            data-product-id="' . esc_attr($product->get_id()) . '"
            style="margin-top:10px;">
            ' . esc_html__('Launch Designer', 'swp-label-studio') . '
        </button>';
	}

	public function cart_item_name($name, $cart_item, $cart_item_key)
	{
		if (isset($cart_item['swp_ls_design_id'])) {
			$name .= '<br><small class="text-muted">' . __('Custom Design', 'swp-label-studio') . '</small>';
		}
		return $name;
	}

	public function cart_item_thumbnail($image, $cart_item, $cart_item_key)
	{
		if (isset($cart_item['swp_ls_design_png_url']) && !empty($cart_item['swp_ls_design_png_url'])) {
			$image = '<img src="' . esc_url($cart_item['swp_ls_design_png_url']) . '" class="attachment-woocommerce_thumbnail" alt="' . __('Custom Design', 'swp-label-studio') . '">';
		}
		return $image;
	}

	public function add_order_item_meta($item, $cart_item_key, $values, $order)
	{
		if (isset($values['swp_ls_design_id'])) {
			$item->add_meta_data('_swp_ls_design_id', $values['swp_ls_design_id'], true);
		}
		if (isset($values['swp_ls_design_json_url'])) {
			$item->add_meta_data('_swp_ls_design_json_url', $values['swp_ls_design_json_url'], true);
		}
		if (isset($values['swp_ls_design_png_url'])) {
			$item->add_meta_data('_swp_ls_design_png_url', $values['swp_ls_design_png_url'], true);
		}
	}

	public function display_order_item_meta($item_id, $item, $order)
	{
		$design_url = $item->get_meta('_swp_ls_design_png_url');
		if ($design_url) {
			echo '<br><a href="' . esc_url($design_url) . '" target="_blank" class="btn btn-sm btn-outline-primary mt-2">' . __('View Design', 'swp-label-studio') . '</a>';
		}
	}
}