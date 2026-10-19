<?php
/**
 * Short application form: details + CV. Used on job pages and the Register page.
 *
 * $args: job_id (int), title (string heading), role_picker (bool).
 */
defined( 'ABSPATH' ) || exit;

$job_id = isset( $args['job_id'] ) ? (int) $args['job_id'] : 0;
$picker = ! empty( $args['role_picker'] );
$ok     = isset( $_GET['hcc_ok'] ) && 'applied' === $_GET['hcc_ok'];
$ref    = isset( $_GET['ref'] ) ? sanitize_text_field( wp_unslash( $_GET['ref'] ) ) : '';

if ( $ok ) : ?>
	<div class="form-card success-card" id="apply" tabindex="-1">
		<div class="tick"><?php echo hcc_icon( 'check', 30 ); ?></div>
		<h2><?php echo $picker ? "Thanks, you're registered" : "Thanks, you've applied"; ?></h2>
		<p>Your reference is <b><?php echo esc_html( $ref ); ?></b>. We've emailed you a copy.</p>
		<ol>
			<li>Our team reviews your CV</li>
			<li>If we move forward, we email you an invite to your candidate portal</li>
			<li>You upload your documents there and follow your progress</li>
		</ol>
		<p class="form-note">No email within 10 minutes? Call <?php echo esc_html( hcc_opt( 'phone' ) ); ?>.</p>
	</div>
<?php else : ?>
	<form class="form-card" id="apply" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
		<input type="hidden" name="action" value="hcc_apply">
		<input type="hidden" name="job_id" value="<?php echo (int) $job_id; ?>">
		<?php wp_nonce_field( 'hcc_apply', 'hcc_nonce' ); ?>
		<div class="hp" aria-hidden="true"><label for="website">Leave this empty</label><input type="text" id="website" name="website" tabindex="-1" autocomplete="off"></div>
		<?php if ( $picker ) : ?>
			<h2>2. Your details</h2>
			<p class="sub">Registering as: <b id="chosen" style="color:var(--navy)">choose a role above</b></p>
			<input type="hidden" name="role" id="roleInput" value="">
		<?php else : ?>
			<span class="eyebrow">Takes about a minute</span>
			<h2 style="margin-top:8px"><?php echo esc_html( $args['title'] ); ?></h2>
			<p class="sub">Just the basics and your CV. We'll email you a link to upload your documents afterwards.</p>
		<?php endif; ?>
		<?php echo hcc_notice(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?>
		<div class="form-grid">
			<div class="f"><label for="first">First name *</label><input type="text" id="first" name="first" autocomplete="given-name" required></div>
			<div class="f"><label for="last">Last name *</label><input type="text" id="last" name="last" autocomplete="family-name" required></div>
			<div class="f"><label for="phone">Mobile number *</label><input type="tel" id="phone" name="phone" autocomplete="tel" required></div>
			<div class="f"><label for="email">Email *</label><input type="email" id="email" name="email" autocomplete="email" required></div>
			<div class="f"><label for="postcode">Postcode</label><input type="text" id="postcode" name="postcode" autocomplete="postal-code"></div>
			<?php if ( $picker ) : ?>
				<div class="f"><label for="travel">How far will you travel?</label><select id="travel" name="travel"><option>Up to 5 miles</option><option selected>Up to 10 miles</option><option>Up to 20 miles</option><option>Anywhere</option></select></div>
			<?php else : ?>
				<fieldset class="f"><legend>Right to work in the UK?</legend><div class="radios"><label class="radio"><input type="radio" name="rtw" value="Yes" checked><span>Yes</span></label><label class="radio"><input type="radio" name="rtw" value="No"><span>No</span></label></div></fieldset>
			<?php endif; ?>
			<div class="f full">
				<label class="drop" for="cv"><input type="file" id="cv" name="cv" accept=".pdf,.doc,.docx" required>
					<strong>Upload your CV *</strong> or drag it here<br><span class="form-note">PDF or Word, up to 5MB</span>
					<span class="file-name" aria-live="polite"></span>
				</label>
			</div>
			<label class="consent full"><input type="checkbox" name="consent" value="1" required>I agree HCC-Temps can store my details to find me work, as set out in the privacy policy.</label>
		</div>
		<div class="form-actions"><button class="btn btn-navy" type="submit"><?php echo $picker ? 'Register' : 'Send application'; ?></button><span class="error-msg" role="alert"></span></div>
	</form>
<?php endif; ?>
