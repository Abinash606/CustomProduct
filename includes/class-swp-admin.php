<?php
if (!defined('ABSPATH')) {
	exit;
}

class SWP_Label_Studio_Admin
{
	public function __construct()
	{
		add_action('admin_menu', array($this, 'add_admin_menu'));
		add_action('admin_init', array($this, 'register_settings'));
		add_filter('manage_edit-product_columns', array($this, 'add_design_label_column'), 20);
		add_action('manage_posts_custom_column', array($this, 'render_design_label_column'), 10, 2);

		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
		add_action('wp_ajax_swp_toggle_design_label', array($this, 'toggle_design_label'));

		// Add minimal inline styles as fallback only
		add_action('admin_head', array($this, 'add_inline_fallback_styles'));
	}

	public function add_admin_menu()
	{
		add_menu_page(
			__('Label Studio', 'swp-label-studio'),
			__('Label Studio', 'swp-label-studio'),
			'manage_options',
			'swp-label-studio',
			array($this, 'admin_page'),
			'dashicons-art',
			56
		);
	}

	public function register_settings()
	{
		register_setting('swp_ls_settings', 'swp_ls_export_scale');
		register_setting('swp_ls_settings', 'swp_ls_load_bootstrap');
		register_setting('swp_ls_settings', 'swp_ls_load_fontawesome');
	}

	public function admin_page()
	{
?>
<div class="wrap">
    <h1><?php _e('Label Studio Settings', 'swp-label-studio'); ?></h1>
    <form method="post" action="options.php">
        <?php
				settings_fields('swp_ls_settings');
				do_settings_sections('swp_ls_settings');
				?>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Export Scale', 'swp-label-studio'); ?></th>
                <td>
                    <input type="number" name="swp_ls_export_scale"
                        value="<?php echo esc_attr(get_option('swp_ls_export_scale', 2)); ?>" min="1" max="4" />
                    <p class="description">
                        <?php _e('Higher values = better quality, larger file size', 'swp-label-studio'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Load Bootstrap', 'swp-label-studio'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="swp_ls_load_bootstrap" value="on"
                            <?php checked(get_option('swp_ls_load_bootstrap'), 'on'); ?> />
                        <?php _e('Load Bootstrap CSS/JS', 'swp-label-studio'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Load Font Awesome', 'swp-label-studio'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="swp_ls_load_fontawesome" value="on"
                            <?php checked(get_option('swp_ls_load_fontawesome'), 'on'); ?> />
                        <?php _e('Load Font Awesome icons', 'swp-label-studio'); ?>
                    </label>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>

    <hr>
    <h2><?php _e('How to Use', 'swp-label-studio'); ?></h2>
    <p><?php _e('Add this shortcode to any page:', 'swp-label-studio'); ?></p>
    <code>[swp_label_studio product_id="123"]</code>
    <p><?php _e('Or use the button shortcode on product pages:', 'swp-label-studio'); ?></p>
    <code>[swp_label_studio_button page_id="456"]</code>
</div>
<?php
	}

	public function add_design_label_column($columns)
	{
		$new_columns = array();

		foreach ($columns as $key => $label) {
			$new_columns[$key] = $label;
			if ($key === 'product_tag') {
				$new_columns['swp_design_label'] = __('Design Label', 'swp-label-studio');
			}
		}

		if (!isset($new_columns['swp_design_label'])) {
			if (isset($new_columns['date'])) {
				$date_value = $new_columns['date'];
				unset($new_columns['date']);
				$new_columns['swp_design_label'] = __('Design Label', 'swp-label-studio');
				$new_columns['date'] = $date_value;
			} else {
				$new_columns['swp_design_label'] = __('Design Label', 'swp-label-studio');
			}
		}

		return $new_columns;
	}

	public function render_design_label_column($column, $post_id)
	{
		if ($column !== 'swp_design_label') {
			return;
		}

		$enabled = get_post_meta($post_id, '_swp_design_label_enabled', true);
		$checked = ($enabled === 'yes') ? 'checked="checked"' : '';

	?>
<div class="swp-toggle-wrapper">
    <input type="checkbox" class="swp-design-label-toggle" id="swp-toggle-<?php echo esc_attr($post_id); ?>"
        data-product-id="<?php echo esc_attr($post_id); ?>" <?php echo $checked; ?> />
    <label for="swp-toggle-<?php echo esc_attr($post_id); ?>" class="swp-toggle-label" tabindex="0"></label>
</div>
<?php
	}

	public function enqueue_admin_assets($hook)
	{
		if ($hook !== 'edit.php') {
			return;
		}

		$screen = get_current_screen();
		if (!$screen || $screen->post_type !== 'product') {
			return;
		}

		wp_enqueue_style(
			'swp-ls-admin-products',
			SWP_LS_URL . 'assets/admin/admin-products.css',
			array(),
			SWP_LS_VERSION
		);

		// Enqueue JavaScript
		wp_enqueue_script(
			'swp-ls-admin-products',
			SWP_LS_URL . 'assets/admin/admin-products.js',
			array('jquery'),
			SWP_LS_VERSION,
			true
		);
		wp_localize_script('swp-ls-admin-products', 'swp_ls_admin', array(
			'nonce' => wp_create_nonce('swp_ls_admin_nonce'),
			'ajaxurl' => admin_url('admin-ajax.php'),
		));
	}

	/**
	 * Minimal inline fallback styles
	 * Only loads if external CSS fails
	 */
	public function add_inline_fallback_styles()
	{
		$screen = get_current_screen();
		if (!$screen || $screen->id !== 'edit-product') {
			return;
		}

	?>
<style type="text/css">
.column-swp_design_label {
    width: 100px !important;
    text-align: center !important;
}

th.column-swp_design_label {
    writing-mode: horizontal-tb !important;
    white-space: nowrap !important;
}

.swp-design-label-toggle {
    position: absolute !important;
    opacity: 0 !important;
    width: 0 !important;
    height: 0 !important;
}

.swp-toggle-label {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
    background: #dcdcde;
    border-radius: 13px;
    cursor: pointer;
    transition: background 0.3s;
}

.swp-toggle-label::before {
    content: "";
    position: absolute;
    width: 20px;
    height: 20px;
    top: 3px;
    left: 3px;
    background: #fff;
    border-radius: 50%;
    transition: all 0.3s;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.swp-design-label-toggle:checked+.swp-toggle-label {
    background: #2271b1;
}

.swp-design-label-toggle:checked+.swp-toggle-label::before {
    transform: translateX(24px);
}
</style>
<?php
	}

	public function toggle_design_label()
	{
		check_ajax_referer('swp_ls_admin_nonce', 'nonce');

		if (!current_user_can('edit_products')) {
			wp_send_json_error(array('message' => 'Permission denied'));
		}

		$product_id = absint($_POST['product_id'] ?? 0);
		$enabled    = sanitize_text_field($_POST['enabled'] ?? 'no');

		if (!$product_id) {
			wp_send_json_error(array('message' => 'Invalid product ID'));
		}

		$result = update_post_meta(
			$product_id,
			'_swp_design_label_enabled',
			$enabled === 'yes' ? 'yes' : 'no'
		);

		if ($result !== false) {
			wp_send_json_success(array(
				'message' => 'Updated successfully',
				'enabled' => $enabled
			));
		} else {
			wp_send_json_error(array('message' => 'Update failed'));
		}
	}
}