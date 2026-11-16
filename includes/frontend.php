<?php
/**
 * Front end output: button, inline CSS and inline JS in the footer.
 *
 * @package Codifigata_Back_To_Top
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_footer', 'codifigata_btt_render', 99 );

/**
 * Tells whether the current request is a page builder editor preview.
 *
 * @return bool
 */
function codifigata_btt_is_builder_preview() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read only checks, nothing is processed.
	$keys = array( 'elementor-preview', 'fl_builder', 'et_fb', 'ct_builder', 'brizy-edit-iframe' );
	foreach ( $keys as $key ) {
		if ( isset( $_GET[ $key ] ) ) {
			return true;
		}
	}
	if ( isset( $_GET['bricks'] ) && 'run' === $_GET['bricks'] ) {
		return true;
	}
	// phpcs:enable
	return false;
}

/**
 * Whether the button should be printed on this request.
 *
 * Developers can hide it on specific pages with the "codifigata_btt_enabled" filter.
 *
 * @return bool
 */
function codifigata_btt_should_render() {
	$show = ! ( is_admin() || wp_doing_ajax() || is_feed() || is_embed() || codifigata_btt_is_builder_preview() );
	return (bool) apply_filters( 'codifigata_btt_enabled', $show );
}

/**
 * Prints the button with its inline CSS and JS.
 */
function codifigata_btt_render() {
	if ( ! codifigata_btt_should_render() ) {
		return;
	}

	$s = codifigata_btt_get_settings();
	?>
<style id="codifigata-btt-css"><?php echo wp_strip_all_tags( codifigata_btt_css( $s ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built only from sanitized values. ?></style>
<button type="button" id="codifigata-btt" aria-label="<?php esc_attr_e( 'Back to top of page', 'codifigata-back-to-top' ); ?>" title="<?php esc_attr_e( 'Back to top', 'codifigata-back-to-top' ); ?>">
	<?php echo codifigata_btt_icon( $s['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
</button>
<script id="codifigata-btt-js">
(function(){
	var btn = document.getElementById('codifigata-btt');
	if (!btn) return;
	var offset = <?php echo (int) $s['offset']; ?>;
	var ticking = false;
	var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	function update(){
		var y = window.pageYOffset || document.documentElement.scrollTop;
		btn.classList.toggle('is-visible', y > offset);
		ticking = false;
	}

	window.addEventListener('scroll', function(){
		if (!ticking){
			ticking = true;
			window.requestAnimationFrame(update);
		}
	}, { passive: true });

	btn.addEventListener('click', function(){
		try {
			window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
		} catch (e) {
			window.scrollTo(0, 0);
		}
		btn.blur();
	});

	update();
})();
</script>
	<?php
}
