<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SWP_Label_Studio_WC {

	public function __construct() {
		// Show meta in cart
		add_filter( 'woocommerce_get_item_data', array( $this, 'get_item_data' ), 10, 2 );
		// Persist meta to order
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'checkout_create_order_line_item' ), 10, 4 );
		// Admin order view
		add_action( 'woocommerce_before_order_item_fill_resupply_stocks_html', array( $this, 'display_admin_order_item_meta' ), 10, 3 );
	}

	/**
	 * Display custom item data in the cart
	 */
	public function get_item_data( $item_data, $cart_item ) {
		if ( isset( $cart_item['swp_ls_design_id'] ) ) {
			$item_data[] = array(
				'key'   => __( 'Design ID', 'swp-label-studio' ),
				'value' => $cart_item['swp_ls_design_id'],
			);
			$item_data[] = array(
				'key'   => __( 'Label Preview', 'swp-label-studio' ),
				'value' => '<a href="' . esc_url( $cart_item['swp_ls_design_png_url'] ) . '" target="_blank">' . __( 'View Label', 'swp-label-studio' ) . '</a>',
			);
		}
		return $item_data;
	}

	/**
	 * Add custom meta to order line items
	 */
	public function checkout_create_order_line_item( $item, $cart_item_key, $values, $order ) {
		if ( isset( $values['swp_ls_design_id'] ) ) {
			$item->add_meta_data( '_swp_ls_design_id', $values['swp_ls_design_id'] );
			$item->add_meta_data( '_swp_ls_design_json', $values['swp_ls_design_json'] );
			$item->add_meta_data( '_swp_ls_design_png_url', $values['swp_ls_design_png_url'] );
		}
	}

	/**
	 * Display design preview in admin order view
	 */
	public function display_admin_order_item_meta( $item_id, $item, $order ) {
		$design_id = $item->get_meta( '_swp_ls_design_id' );
		$png_url   = $item->get_meta( '_swp_ls_design_png_url' );

		if ( $design_id ) {
			echo '<div class="swp-ls-order-item-design" style="margin-top: 10px; padding: 10px; border: 1px solid #eee;">';
			echo '<strong>' . __( 'Label Design:', 'swp-label-studio' ) . '</strong><br>';
			echo '<a href="' . esc_url( $png_url ) . '" target="_blank"><img src="' . esc_url( $png_url ) . '" style="max-width: 150px; height: auto; display: block; margin-top: 5px; border: 1px solid #ddd;" /></a>';
			echo '<small>ID: ' . esc_html( $design_id ) . '</small>';
			echo '</div>';
		}
	}
}
