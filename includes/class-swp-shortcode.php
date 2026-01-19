<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SWP_Label_Studio_Shortcode {

	public function __construct() {
		add_shortcode( 'swp_label_studio', array( $this, 'render_designer' ) );
		add_shortcode( 'swp_label_studio_button', array( $this, 'render_designer_button' ) );
	}

	public function render_designer( $atts ) {
		$atts = shortcode_atts( array(
			'product_id'   => '',
			'variation_id' => '',
			'qty'          => '1',
			'bottle'       => get_option( 'swp_ls_default_bottle', 'classic' ),
		), $atts );

		// Auto-detect product if missing on product page
		if ( empty( $atts['product_id'] ) && is_product() ) {
			$atts['product_id'] = get_the_ID();
		}

		if ( empty( $atts['product_id'] ) ) {
			return __( 'No product ID specified for the label studio.', 'swp-label-studio' );
		}

        ob_start();
        include SWP_LS_PATH . 'templates/designer.php';
        return ob_get_clean();
	}

	public function render_designer_button( $atts ) {
		$atts = shortcode_atts( array(
			'label'      => __( 'Design Your Label', 'swp-label-studio' ),
			'product_id' => '',
		), $atts );

		if ( empty( $atts['product_id'] ) && is_product() ) {
			$atts['product_id'] = get_the_ID();
		}

		// This could open a modal, but for now let's just link to a page with the designer or trigger a JS event
		$url = add_query_arg( 'product_id', $atts['product_id'], get_permalink() ); // Placeholder logic

		return '<button class="swp-ls-open-designer btn btn-primary" data-product-id="' . esc_attr( $atts['product_id'] ) . '">' . esc_html( $atts['label'] ) . '</button>';
	}
}
