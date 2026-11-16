<?php
/**
 * Settings page under Settings, Back to Top.
 *
 * @package Codifigata_Back_To_Top
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_init', 'codifigata_btt_register_setting' );
add_action( 'admin_menu', 'codifigata_btt_admin_menu' );
add_action( 'admin_enqueue_scripts', 'codifigata_btt_admin_assets' );
add_filter( 'plugin_action_links_' . plugin_basename( CODIFIGATA_BTT_FILE ), 'codifigata_btt_action_links' );

/**
 * Registers the option.
 */
function codifigata_btt_register_setting() {
	register_setting(
		'codifigata_btt',
		CODIFIGATA_BTT_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'codifigata_btt_sanitize',
			'default'           => codifigata_btt_defaults(),
		)
	);
}

/**
 * Adds the page under Settings.
 */
function codifigata_btt_admin_menu() {
	add_options_page(
		__( 'Back to Top', 'codifigata-back-to-top' ),
		__( 'Back to Top', 'codifigata-back-to-top' ),
		'manage_options',
		'codifigata-back-to-top',
		'codifigata_btt_render_page'
	);
}

/**
 * Loads the core color picker, only on our page.
 *
 * @param string $hook Current admin page hook.
 */
function codifigata_btt_admin_assets( $hook ) {
	if ( 'settings_page_codifigata-back-to-top' !== $hook ) {
		return;
	}
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
	wp_add_inline_script( 'wp-color-picker', 'jQuery(function($){$(".codifigata-btt-color").wpColorPicker();});' );
}

/**
 * Adds a Settings link in the plugins list.
 *
 * @param array $links Existing links.
 * @return array
 */
function codifigata_btt_action_links( $links ) {
	$url = admin_url( 'options-general.php?page=codifigata-back-to-top' );
	array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'codifigata-back-to-top' ) . '</a>' );
	return $links;
}

/**
 * Prints a select field.
 *
 * @param string $key     Setting key.
 * @param array  $options Value => label.
 * @param string $current Current value.
 */
function codifigata_btt_select( $key, $options, $current ) {
	printf( '<select id="cbtt-%1$s" name="%2$s[%1$s]">', esc_attr( $key ), esc_attr( CODIFIGATA_BTT_OPTION ) );
	foreach ( $options as $value => $label ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $value ), selected( $current, $value, false ), esc_html( $label ) );
	}
	echo '</select>';
}

/**
 * Prints a number field in pixels.
 *
 * @param string $key     Setting key.
 * @param int    $current Current value.
 * @param int    $step    Step.
 */
function codifigata_btt_number( $key, $current, $step = 1 ) {
	$limits = codifigata_btt_limits();
	printf(
		'<input type="number" id="cbtt-%1$s" name="%2$s[%1$s]" value="%3$d" min="%4$d" max="%5$d" step="%6$d" class="small-text"> px',
		esc_attr( $key ),
		esc_attr( CODIFIGATA_BTT_OPTION ),
		(int) $current,
		(int) $limits[ $key ][0],
		(int) $limits[ $key ][1],
		(int) $step
	);
}

/**
 * Renders the settings page.
 */
function codifigata_btt_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$s    = codifigata_btt_get_settings();
	$name = CODIFIGATA_BTT_OPTION;

	// Static preview: same CSS as the front end, pinned inside the box.
	$preview_css  = codifigata_btt_css( $s, '#codifigata-btt-preview' );
	$preview_css .= '#codifigata-btt-preview{position:static;opacity:1;visibility:visible;transform:none;display:flex!important}';
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Back to Top settings', 'codifigata-back-to-top' ); ?></h1>

		<form method="post" action="options.php">
			<?php settings_fields( 'codifigata_btt' ); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="cbtt-color"><?php esc_html_e( 'Color', 'codifigata-back-to-top' ); ?></label></th>
					<td>
						<input type="text" id="cbtt-color" name="<?php echo esc_attr( $name ); ?>[color]" value="<?php echo esc_attr( $s['color'] ); ?>" class="codifigata-btt-color">
						<p class="description"><?php esc_html_e( 'Leave empty to use the primary color of the site (Elementor global color or theme palette).', 'codifigata-back-to-top' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cbtt-style"><?php esc_html_e( 'Style', 'codifigata-back-to-top' ); ?></label></th>
					<td>
						<?php
						codifigata_btt_select(
							'style',
							array(
								'soft'    => __( 'Soft (light background)', 'codifigata-back-to-top' ),
								'solid'   => __( 'Solid', 'codifigata-back-to-top' ),
								'outline' => __( 'Outline', 'codifigata-back-to-top' ),
							),
							$s['style']
						);
						?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cbtt-shape"><?php esc_html_e( 'Shape', 'codifigata-back-to-top' ); ?></label></th>
					<td>
						<?php
						codifigata_btt_select(
							'shape',
							array(
								'rounded' => __( 'Rounded', 'codifigata-back-to-top' ),
								'circle'  => __( 'Circle', 'codifigata-back-to-top' ),
								'square'  => __( 'Square', 'codifigata-back-to-top' ),
							),
							$s['shape']
						);
						?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cbtt-icon"><?php esc_html_e( 'Icon', 'codifigata-back-to-top' ); ?></label></th>
					<td>
						<?php
						codifigata_btt_select(
							'icon',
							array(
								'chevron' => __( 'Chevron', 'codifigata-back-to-top' ),
								'arrow'   => __( 'Arrow', 'codifigata-back-to-top' ),
							),
							$s['icon']
						);
						?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cbtt-position"><?php esc_html_e( 'Position', 'codifigata-back-to-top' ); ?></label></th>
					<td>
						<?php
						codifigata_btt_select(
							'position',
							array(
								'right' => __( 'Bottom right', 'codifigata-back-to-top' ),
								'left'  => __( 'Bottom left', 'codifigata-back-to-top' ),
							),
							$s['position']
						);
						?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cbtt-size"><?php esc_html_e( 'Size', 'codifigata-back-to-top' ); ?></label></th>
					<td>
						<?php codifigata_btt_number( 'size', $s['size'] ); ?>
						<p class="description"><?php esc_html_e( 'Side of the button on desktop.', 'codifigata-back-to-top' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cbtt-gap"><?php esc_html_e( 'Distance from edge', 'codifigata-back-to-top' ); ?></label></th>
					<td>
						<?php codifigata_btt_number( 'gap', $s['gap'] ); ?>
						<p class="description"><?php esc_html_e( 'Space from the edges of the screen on desktop. On mobile it is reduced automatically.', 'codifigata-back-to-top' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cbtt-offset"><?php esc_html_e( 'Show after', 'codifigata-back-to-top' ); ?></label></th>
					<td>
						<?php codifigata_btt_number( 'offset', $s['offset'], 50 ); ?>
						<p class="description"><?php esc_html_e( 'Scroll distance after which the button appears.', 'codifigata-back-to-top' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Mobile', 'codifigata-back-to-top' ); ?></th>
					<td>
						<label for="cbtt-mobile">
							<input type="checkbox" id="cbtt-mobile" name="<?php echo esc_attr( $name ); ?>[mobile]" value="1" <?php checked( 1, $s['mobile'] ); ?>>
							<?php esc_html_e( 'Show the button on screens narrower than 768px', 'codifigata-back-to-top' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Preview', 'codifigata-back-to-top' ); ?></th>
					<td>
						<style><?php echo wp_strip_all_tags( $preview_css ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built only from sanitized values. ?></style>
						<div style="display:inline-flex;padding:24px 32px;background:#fff;border:1px solid #dcdcde;border-radius:8px">
							<span id="codifigata-btt-preview" aria-hidden="true"><?php echo codifigata_btt_icon( $s['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></span>
						</div>
						<p class="description"><?php esc_html_e( 'Preview of the saved settings. With the automatic color, the site shows its own primary color.', 'codifigata-back-to-top' ); ?></p>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
