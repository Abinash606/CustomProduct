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
}
