<?php
/**
 * FAQ accordion. $args: items (array of [question, answer]), open_first (bool).
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="faq"<?php echo isset( $args['id'] ) ? ' id="' . esc_attr( $args['id'] ) . '"' : ''; ?><?php echo isset( $args['attrs'] ) ? ' ' . $args['attrs'] : ''; // phpcs:ignore -- static attributes. ?>>
	<?php foreach ( $args['items'] as $i => $qa ) : ?>
		<details<?php echo 0 === $i ? ' open' : ''; ?>><summary><?php echo esc_html( $qa[0] ); ?></summary><p><?php echo esc_html( $qa[1] ); ?></p></details>
	<?php endforeach; ?>
</div>
