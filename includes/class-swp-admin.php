<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SWP_Label_Studio_Admin {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_settings_page() {
		add_submenu_page(
			'woocommerce',
			__( 'Label Studio Settings', 'swp-label-studio' ),
			__( 'Label Studio', 'swp-label-studio' ),
			'manage_options',
			'swp-label-studio-settings',
			array( $this, 'render_settings_page' )
		);
	}

	public function register_settings() {
		register_setting( 'swp_ls_settings', 'swp_ls_load_bootstrap' );
		register_setting( 'swp_ls_settings', 'swp_ls_load_fontawesome' );
		register_setting( 'swp_ls_settings', 'swp_ls_load_inter' );
		register_setting( 'swp_ls_settings', 'swp_ls_export_scale' );
		register_setting( 'swp_ls_settings', 'swp_ls_default_bottle' );
	}

	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'SWP Label Studio Settings', 'swp-label-studio' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'swp_ls_settings' );
				do_settings_sections( 'swp_ls_settings' );
				?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e( 'Load Bootstrap', 'swp-label-studio' ); ?></th>
						<td>
							<select name="swp_ls_load_bootstrap">
								<option value="off" <?php selected( get_option( 'swp_ls_load_bootstrap' ), 'off' ); ?>><?php _e( 'Off (Recommended if theme has it)', 'swp-label-studio' ); ?></option>
								<option value="on" <?php selected( get_option( 'swp_ls_load_bootstrap' ), 'on' ); ?>><?php _e( 'On', 'swp-label-studio' ); ?></option>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Load FontAwesome', 'swp-label-studio' ); ?></th>
						<td>
							<select name="swp_ls_load_fontawesome">
								<option value="off" <?php selected( get_option( 'swp_ls_load_fontawesome' ), 'off' ); ?>><?php _e( 'Off', 'swp-label-studio' ); ?></option>
								<option value="on" <?php selected( get_option( 'swp_ls_load_fontawesome' ), 'on' ); ?>><?php _e( 'On', 'swp-label-studio' ); ?></option>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Load Inter Font', 'swp-label-studio' ); ?></th>
						<td>
							<select name="swp_ls_load_inter">
								<option value="on" <?php selected( get_option( 'swp_ls_load_inter', 'on' ), 'on' ); ?>><?php _e( 'On', 'swp-label-studio' ); ?></option>
								<option value="off" <?php selected( get_option( 'swp_ls_load_inter' ), 'off' ); ?>><?php _e( 'Off', 'swp-label-studio' ); ?></option>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Default Export Scale', 'swp-label-studio' ); ?></th>
						<td>
							<input type="number" name="swp_ls_export_scale" value="<?php echo esc_attr( get_option( 'swp_ls_export_scale', 2 ) ); ?>" step="1" min="1" max="5" />
							<p class="description"><?php _e( 'Multiplier for high resolution exports (2 is standard).', 'swp-label-studio' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Default Bottle Mockup', 'swp-label-studio' ); ?></th>
						<td>
							<select name="swp_ls_default_bottle">
								<option value="classic" <?php selected( get_option( 'swp_ls_default_bottle', 'classic' ), 'classic' ); ?>><?php _e( 'Classic', 'swp-label-studio' ); ?></option>
								<option value="angle" <?php selected( get_option( 'swp_ls_default_bottle' ), 'angle' ); ?>><?php _e( 'Angle', 'swp-label-studio' ); ?></option>
							</select>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
