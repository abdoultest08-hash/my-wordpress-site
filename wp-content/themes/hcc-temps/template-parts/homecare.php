<?php
/**
 * Link to the sister company for home care (HCC Care & Support). Style "band" (homepage) or "card" (About us, Contact).
 * The address is set in Appearance → Customize → HCC-Temps → Homepage.
 */
defined( 'ABSPATH' ) || exit;
$hc_url   = hcc_opt( 'homecare_url' );
$hc_style = isset( $args['style'] ) ? $args['style'] : 'card';
if ( ! $hc_url ) {
	return;
}
?>
<?php if ( 'band' === $hc_style ) : ?>
<section class="homecare-band" aria-labelledby="homecareTitle">
	<div class="wrap">
		<a class="hc-logo" href="<?php echo esc_url( $hc_url ); ?>" target="_blank" rel="noopener"><img src="<?php echo esc_url( hcc_img( 'hcc-care-support.png' ) ); ?>" alt="HCC Care &amp; Support" width="384" height="210" loading="lazy"></a>
		<div class="hc-text">
			<span class="eyebrow">Looking for home care?</span>
			<h2 id="homecareTitle">Care at home for you or a loved one</h2>
			<p>HCC-Temps supplies staff to care homes, hospitals and kitchens. For care and support in your own home, visit our sister company, HCC Care &amp; Support.</p>
		</div>
		<a class="btn btn-ghost hc-btn" href="<?php echo esc_url( $hc_url ); ?>" target="_blank" rel="noopener">Visit HCC Care &amp; Support <span aria-hidden="true">↗</span><span class="screen-reader-text"> (opens in a new tab)</span></a>
	</div>
</section>
<?php else : ?>
<aside class="homecare-card" aria-label="Home care services">
	<a class="hc-logo" href="<?php echo esc_url( $hc_url ); ?>" target="_blank" rel="noopener"><img src="<?php echo esc_url( hcc_img( 'hcc-care-support.png' ) ); ?>" alt="HCC Care &amp; Support" width="384" height="210" loading="lazy"></a>
	<div>
		<b>Looking for home care?</b>
		<p>For care and support in your own home, visit our sister company HCC Care &amp; Support.</p>
		<a class="link-arrow" href="<?php echo esc_url( $hc_url ); ?>" target="_blank" rel="noopener">hcchome-care.co.uk <span aria-hidden="true">↗</span></a>
	</div>
</aside>
<?php endif; ?>
