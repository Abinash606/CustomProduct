<?php
if ( ! defined( 'ABSPATH' ) ) exit;

get_header( 'blank' ); ?>

<style>
html, body {
	margin: 0;
	padding: 0;
	height: 100%;
	overflow: hidden;
	font-family: Inter, system-ui, sans-serif;
}
#wpadminbar { display:none; }
.swp-ls-container {
	width: 100vw;
	height: 100vh;
	position: fixed;
	inset: 0;
}
</style>

<?php
$product_id   = isset($_GET['product_id']) ? absint($_GET['product_id']) : 0;
$variation_id = isset($_GET['variation_id']) ? absint($_GET['variation_id']) : 0;
$qty          = isset($_GET['qty']) ? absint($_GET['qty']) : 1;
$bottle       = isset($_GET['bottle']) ? sanitize_text_field($_GET['bottle']) : 'standard';

include SWP_LS_PATH . 'templates/designer.php';

get_footer( 'blank' );
