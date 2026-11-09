<?php
/**
 * App layout for the two portals (candidate portal and Staff portal): a sidebar on the left
 * that can be collapsed to icons on a computer, and a ☰ menu on a phone. The public site's
 * header and footer are hidden on these pages so the portals feel like an app.
 */

defined( 'ABSPATH' ) || exit;

/** Is this one of the portal pages (app layout)? */
function hcc_is_app_page() {
	return is_page_template( 'templates/portal.php' ) || is_page_template( 'templates/staff.php' );
}

add_filter( 'body_class', function ( $c ) {
	if ( hcc_is_app_page() ) {
		$c[] = 'app-body';
	}
	return $c;
} );

/**
 * Open the app layout.
 *
 * @param array $a title (shown in the phone top bar), sub (small line under the logo),
 *                 items (list of [key, label, icon, url, badge, attrs]), current (key),
 *                 who (name shown at the bottom), logout (URL), kind ('cand' or 'staff').
 */
function hcc_app_open( $a ) {
	$a = wp_parse_args( $a, array( 'title' => '', 'sub' => '', 'items' => array(), 'current' => '', 'who' => '', 'logout' => '', 'kind' => 'cand' ) );
	?>
	<script>try{document.documentElement.classList.add('js-views');if(localStorage.getItem('hccSideMini')==='1'){document.documentElement.classList.add('side-mini-pre');}}catch(e){}</script>
	<div class="app app-<?php echo esc_attr( $a['kind'] ); ?>" id="app">
		<aside class="app-side" id="appSide" aria-label="Portal menu">
			<div class="side-top">
				<a class="side-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="HCC-Temps website"><img src="<?php echo esc_url( hcc_logo_url() ); ?>" alt="HCC-Temps" width="266" height="144"></a>
				<button class="side-btn side-collapse" type="button" id="sideCollapse" aria-label="Collapse menu" title="Collapse menu"><?php echo hcc_icon( 'side', 20 ); // phpcs:ignore ?></button>
				<button class="side-btn side-close" type="button" id="sideClose" aria-label="Close menu"><?php echo hcc_icon( 'close', 20 ); // phpcs:ignore ?></button>
			</div>
			<?php if ( $a['sub'] ) : ?><p class="side-sub"><?php echo esc_html( $a['sub'] ); ?></p><?php endif; ?>
			<nav class="side-nav">
				<?php foreach ( $a['items'] as $it ) :
					$it = array_pad( $it, 6, '' );
					if ( 'sep' === $it[0] ) {
						echo '<span class="side-sep">' . esc_html( $it[1] ) . '</span>';
						continue;
					}
					$cur = $a['current'] === $it[0];
					?>
					<a href="<?php echo esc_url( $it[3] ); ?>" class="side-link<?php echo $cur ? ' is-on' : ''; ?>" data-view-link="<?php echo esc_attr( $it[0] ); ?>"<?php echo $cur ? ' aria-current="page"' : ''; ?> title="<?php echo esc_attr( $it[1] ); ?>" <?php echo $it[5]; // phpcs:ignore ?>>
						<span class="side-ico"><?php echo hcc_icon( $it[2], 20 ); // phpcs:ignore ?></span>
						<span class="side-label"><?php echo esc_html( $it[1] ); ?></span>
						<?php if ( $it[4] ) : ?><span class="side-badge"><?php echo esc_html( $it[4] ); ?></span><?php endif; ?>
					</a>
				<?php endforeach; ?>
			</nav>
			<div class="side-foot">
				<?php if ( $a['who'] ) : ?><span class="side-who" title="<?php echo esc_attr( $a['who'] ); ?>"><span class="side-ava"><?php echo esc_html( strtoupper( mb_substr( $a['who'], 0, 1 ) ) ); ?></span><span class="side-label"><?php echo esc_html( $a['who'] ); ?></span></span><?php endif; ?>
				<?php if ( $a['logout'] ) : ?><a class="side-link" href="<?php echo esc_url( $a['logout'] ); ?>" title="Log out"><span class="side-ico"><?php echo hcc_icon( 'logout', 20 ); // phpcs:ignore ?></span><span class="side-label">Log out</span></a><?php endif; ?>
			</div>
		</aside>
		<div class="side-shade" id="sideShade" hidden></div>
		<div class="app-main">
			<div class="app-top">
				<button class="side-btn" type="button" id="sideOpen" aria-label="Open menu" aria-controls="appSide" aria-expanded="false"><?php echo hcc_icon( 'menu', 22 ); // phpcs:ignore ?></button>
				<a class="app-top-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( hcc_logo_url() ); ?>" alt="HCC-Temps" width="266" height="144"></a>
				<span class="app-top-title" id="appTitle"><?php echo esc_html( $a['title'] ); ?></span>
			</div>
			<div class="app-content" id="main">
	<?php
}

function hcc_app_close() {
	echo '</div></div></div>';
}

/**
 * Sidebar counts that need every candidate (expiring dates, payments, signing) are kept for a
 * minute so staff pages stay fast, and recalculated as soon as a candidate's dates, payments,
 * signing pack or status change.
 */
function hcc_cached_count( $key, $fn ) {
	$v = get_transient( 'hcc_cnt_' . $key );
	if ( false === $v ) {
		$v = $fn();
		set_transient( 'hcc_cnt_' . $key, $v, MINUTE_IN_SECONDS );
	}
	return $v;
}

function hcc_cached_counts_clear( $meta_id = 0, $object_id = 0, $meta_key = '' ) {
	if ( '' === $meta_key || in_array( $meta_key, array( '_hcc_files', '_hcc_checks', '_hcc_orders', '_hcc_pack', '_hcc_closed', '_hcc_completed' ), true ) ) {
		foreach ( array( 'exp', 'pay', 'sign' ) as $k ) {
			delete_transient( 'hcc_cnt_' . $k );
		}
	}
}
foreach ( array( 'added_post_meta', 'updated_post_meta', 'deleted_post_meta' ) as $hook ) {
	add_action( $hook, 'hcc_cached_counts_clear', 10, 3 );
}
add_action( 'deleted_post', function () {
	hcc_cached_counts_clear();
} );

