<?php
/**
 * Template Name: Registration (candidates)
 */
defined( 'ABSPATH' ) || exit;

if ( hcc_is_staff() ) {
	wp_safe_redirect( hcc_staff_url() );
	exit;
}
$aid = hcc_my_record();
if ( is_user_logged_in() && ! $aid && ! current_user_can( 'edit_posts' ) ) {
	wp_safe_redirect( hcc_portal_url() );
	exit;
}
$max  = $aid ? max( 1, (int) hcc_meta( $aid, 'step', 2 ) ) : 1;
$want = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : $max; // phpcs:ignore WordPress.Security.NonceVerification
$step = $aid ? min( max( 2, $want ), $max ) : 1;
if ( $aid && ! hcc_meta( $aid, 'verified' ) ) {
	$step = 2;
} elseif ( $aid && 2 === $step ) {
	$step = min( 3, $max ); // Email already confirmed: never show the code step again.
}
$submitted = $aid && hcc_meta( $aid, 'submitted' );
if ( $submitted && 4 !== $want && 7 !== $want ) {
	wp_safe_redirect( hcc_portal_url() );
	exit;
}
if ( $submitted ) {
	$step = $want;
}
$role = $aid ? hcc_meta( $aid, 'role_key' ) : ( isset( $_GET['role'] ) && hcc_role( sanitize_key( $_GET['role'] ) ) ? sanitize_key( $_GET['role'] ) : 'nurse' ); // phpcs:ignore WordPress.Security.NonceVerification
$r    = hcc_role( $role );
$a    = $aid ? (array) hcc_meta( $aid, 'answers', array() ) : array();
$g    = function ( $k ) use ( $a ) { return isset( $a[ $k ] ) ? $a[ $k ] : ''; };
$keep = function ( $k ) { return isset( $_GET[ $k ] ) ? sanitize_text_field( wp_unslash( $_GET[ $k ] ) ) : ''; }; // phpcs:ignore WordPress.Security.NonceVerification
$titles = array( 1 => 'Create your account', 2 => 'Verify your email', 3 => 'Confirm your role', 4 => $submitted ? 'Edit your answers' : 'Pre-screening questions', 5 => 'Upload your documents', 6 => 'Training certificates', 7 => 'Registration submitted' );
$errs   = array(
	'rtw_dbs'     => 'Please answer the right to work and DBS questions.',
	'bank'        => 'Please add your bank details: account name, account type, 6-digit sort code and 8-digit account number.',
	'code'        => 'That code isn\'t right. Use the code from the most recent email, or send a new code.',
	'code_old'    => 'That code has expired. Press "send a new code".',
	'code_locked' => 'Too many tries. Press "send a new code".',
	'code_wait'   => 'We sent a code less than 30 seconds ago. Check your inbox and spam folder, then try again.',
	'email_email' => 'Please enter a valid email address.',
	'email_same'  => 'That is already the email on your registration. Check your spam folder, or send a new code.',
	'email_taken' => 'There is already an account with that email. If it\'s yours, log in with it instead, or call us.',
	'email_user'  => 'We could not change the email. Please call us.',
	'email_many'  => 'The email has been changed too many times. Please call us and we\'ll sort it out.',
	'upload'      => isset( $_GET['msg'] ) ? sanitize_text_field( wp_unslash( $_GET['msg'] ) ) : 'The upload did not work. Please try again.', // phpcs:ignore WordPress.Security.NonceVerification
);
$err = isset( $_GET['hcc_err'] ) ? sanitize_key( $_GET['hcc_err'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$alert = isset( $errs[ $err ] ) ? '<p class="form-alert" role="alert">' . esc_html( $errs[ $err ] ) . '</p>' : hcc_notice();
$form_url  = esc_url( admin_url( 'admin-post.php' ) );
$nonce = function ( $a ) { return '<input type="hidden" name="hcc_nonce" value="' . esc_attr( wp_create_nonce( $a ) ) . '">'; };
$radio = function ( $name, $opts, $cur ) {
	$h = '<div class="radios">';
	foreach ( $opts as $v => $l ) {
		$v  = is_int( $v ) ? $l : $v;
		$h .= '<label class="radio"><input type="radio" name="' . esc_attr( $name ) . '" value="' . esc_attr( $v ) . '"' . checked( $cur, $v, false ) . '><span>' . esc_html( $l ) . '</span></label>';
	}
	return $h . '</div>';
};
$select = function ( $name, $opts, $cur, $first = 'Choose' ) {
	$h = '<select id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '"><option value="">' . esc_html( $first ) . '</option>';
	foreach ( $opts as $o ) {
		$h .= '<option' . selected( $cur, $o, false ) . '>' . esc_html( $o ) . '</option>';
	}
	return $h . '</select>';
};
$text = function ( $name, $label, $type = 'text', $extra = '' ) use ( $g ) {
	return '<div class="f"><label for="' . esc_attr( $name ) . '">' . esc_html( $label ) . '</label><input type="' . esc_attr( $type ) . '" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $g( $name ) ) . '"' . $extra . '></div>';
};
get_header();
?>
<main class="wiz-page">
	<section class="wiz-head">
		<div class="wrap">
			<div class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> / <a href="<?php echo esc_url( hcc_page_url( 'register', '/register/' ) ); ?>">For candidates</a> / Registration</div>
			<h1><?php echo esc_html( $titles[ $step ] ); ?></h1>
			<?php if ( $step < 7 && ! $submitted ) : ?>
			<ol class="stepper" aria-label="Registration steps">
				<?php foreach ( array( 1 => 'Account', 2 => 'Verify email', 3 => 'Your role', 4 => 'About you', 5 => 'Documents', 6 => 'Training' ) as $n => $l ) : ?>
					<li class="<?php echo $n < $step ? 'done' : ( $n === $step ? 'on' : '' ); ?>"<?php echo $n === $step ? ' aria-current="step"' : ''; ?>>
						<?php if ( $aid && $n < $step && $n > 2 && $n <= $max ) : ?><a href="<?php echo esc_url( add_query_arg( 'step', $n, hcc_apply_url() ) ); ?>"><span><?php echo (int) $n; ?></span><?php echo esc_html( $l ); ?></a><?php else : ?><span><?php echo (int) $n; ?></span><?php echo esc_html( $l ); ?><?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ol>
			<?php endif; ?>
		</div>
	</section>

	<section class="section wiz-body">
		<div class="wrap wiz-wrap">
			<div class="wiz-main">
				<?php echo $alert; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php if ( 'exists' === $err ) : ?><p class="form-note" style="margin:-6px 0 14px"><a href="<?php echo esc_url( hcc_login_url() ); ?>">Log in to your candidate portal</a> · <a href="<?php echo esc_url( wp_lostpassword_url( hcc_login_url() ) ); ?>">Forgotten your password?</a></p><?php endif; ?>

				<?php if ( 1 === $step ) : ?>
				<form class="form-card wiz-step" method="post" action="<?php echo $form_url; ?>" id="account" novalidate>
					<input type="hidden" name="action" value="hcc_reg_account"><?php echo $nonce( 'hcc_reg_account' ); // phpcs:ignore ?>
					<input type="hidden" name="role" value="<?php echo esc_attr( $role ); ?>">
					<div class="hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
					<h2>Your details</h2>
					<p class="sub">Registering as <b><?php echo esc_html( $r['name'] ); ?></b>. <a href="<?php echo esc_url( hcc_page_url( 'register', '/register/' ) ); ?>#roles">Change role</a></p>
					<div class="form-grid">
						<div class="f"><label for="first">First name</label><input type="text" id="first" name="first" autocomplete="given-name" required value="<?php echo esc_attr( $keep( 'first' ) ); ?>"></div>
						<div class="f"><label for="last">Surname</label><input type="text" id="last" name="last" autocomplete="family-name" required value="<?php echo esc_attr( $keep( 'last' ) ); ?>"></div>
						<div class="f"><label for="email">Email</label><input type="email" id="email" name="email" autocomplete="email" required value="<?php echo esc_attr( $keep( 'email' ) ); ?>"><p class="hint">This is your username.</p></div>
						<div class="f"><label for="phone">Mobile number</label><input type="tel" id="phone" name="phone" autocomplete="tel" required value="<?php echo esc_attr( $keep( 'phone' ) ); ?>"></div>
						<div class="f full"><label for="addr1">Address</label><input type="text" id="addr1" name="addr1" autocomplete="address-line1" placeholder="House number and street" required value="<?php echo esc_attr( $keep( 'addr1' ) ); ?>"></div>
						<div class="f"><label for="town">Town or city</label><input type="text" id="town" name="town" autocomplete="address-level2" value="<?php echo esc_attr( $keep( 'town' ) ); ?>"></div>
						<div class="f"><label for="postcode">Postcode</label><input type="text" id="postcode" name="postcode" autocomplete="postal-code" required value="<?php echo esc_attr( $keep( 'postcode' ) ); ?>"></div>
						<div class="f"><label for="pw">Password</label><input type="password" id="pw" name="pw" autocomplete="new-password" minlength="8" required><p class="hint">At least 8 characters.</p></div>
						<div class="f"><label for="pw2">Repeat password</label><input type="password" id="pw2" name="pw2" autocomplete="new-password" minlength="8" required></div>
						<label class="consent full"><input type="checkbox" name="terms" value="1" required>I accept the <a href="<?php echo esc_url( hcc_page_url( 'terms', '/terms-and-conditions/' ) ); ?>" target="_blank">terms and conditions</a>.</label>
						<label class="consent full"><input type="checkbox" name="privacy" value="1" required>I have read the <a href="<?php echo esc_url( hcc_page_url( 'privacy', '/privacy-policy/' ) ); ?>" target="_blank">privacy policy</a>.</label>
						<?php echo hcc_robot_field(); // phpcs:ignore ?>
					</div>
					<div class="form-actions"><button class="btn btn-navy" type="submit">Create my account</button></div>
					<p class="form-note" style="margin-top:12px">Already registered? <a href="<?php echo esc_url( hcc_login_url() ); ?>">Log in</a></p>
				</form>

				<?php elseif ( 2 === $step ) : ?>
				<div class="form-card wiz-step">
					<h2>Check your email</h2>
					<p class="sub">We've sent a 6-digit code to <b><?php echo esc_html( hcc_meta( $aid, 'email' ) ); ?></b>. Enter it below to confirm your email address. The code works for 10 minutes.</p>
					<?php if ( isset( $_GET['hcc_ok'] ) ) : // phpcs:ignore ?><p class="form-ok"><?php echo 'email_changed' === $_GET['hcc_ok'] ? 'Email changed. ' : ''; // phpcs:ignore ?>A new code is on its way to <?php echo esc_html( hcc_meta( $aid, 'email' ) ); ?>. Only the newest code works.</p><?php endif; ?>
					<form method="post" action="<?php echo $form_url; ?>" class="code-form">
						<input type="hidden" name="action" value="hcc_reg_verify"><?php echo $nonce( 'hcc_reg_verify' ); // phpcs:ignore ?>
						<div class="f" style="max-width:260px"><label for="code">6-digit code</label><input class="code-input" type="text" id="code" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="7" pattern="[0-9 ]*" required></div>
						<div class="form-actions"><button class="btn btn-navy" type="submit">Verify email</button></div>
					</form>
					<form method="post" action="<?php echo $form_url; ?>" style="margin-top:14px">
						<input type="hidden" name="action" value="hcc_reg_resend"><?php echo $nonce( 'hcc_reg_resend' ); // phpcs:ignore ?>
						<p class="form-note">Not arrived? Check your spam folder or <button class="link-btn" type="submit">send a new code</button>.</p>
					</form>
					<details class="email-fix"<?php echo 0 === strpos( $err, 'email_' ) ? ' open' : ''; ?>>
						<summary>Wrong email address? Change it</summary>
						<form method="post" action="<?php echo $form_url; ?>">
							<input type="hidden" name="action" value="hcc_reg_email"><?php echo $nonce( 'hcc_reg_email' ); // phpcs:ignore ?>
							<div class="f" style="max-width:420px"><label for="new_email">Correct email address</label><input type="email" id="new_email" name="new_email" autocomplete="email" required></div>
							<p class="hint">This also becomes your username. We'll send a new code to it.</p>
							<div class="form-actions" style="margin-top:10px"><button class="btn btn-ghost btn-sm" type="submit">Change email and send code</button></div>
						</form>
					</details>
				</div>

				<?php elseif ( 3 === $step ) : ?>
				<form class="form-card wiz-step" method="post" action="<?php echo $form_url; ?>">
					<input type="hidden" name="action" value="hcc_reg_role"><?php echo $nonce( 'hcc_reg_role' ); // phpcs:ignore ?>
					<h2>Confirm your role</h2>
					<p class="sub">You started registration as:</p>
					<div class="role-confirm"><img src="<?php echo esc_url( hcc_img( $r['img'] ) ); ?>" alt="" width="96" height="96"><div><b><?php echo esc_html( $r['name'] ); ?></b><span>Your checklist of documents and training is based on this role.</span></div></div>
					<details class="role-switch">
						<summary>No, change role</summary>
						<div class="f" style="margin-top:12px;max-width:420px"><label for="roleSel">Choose your role</label><select id="roleSel" name="role">
							<?php foreach ( hcc_roles() as $k => $x ) : ?><option value="<?php echo esc_attr( $k ); ?>"<?php selected( $k, $role ); ?>><?php echo esc_html( $x['name'] ); ?></option><?php endforeach; ?>
						</select></div>
					</details>
					<div class="form-actions"><button class="btn btn-navy" type="submit">Confirm and continue</button></div>
				</form>

				<?php elseif ( 4 === $step ) : ?>
				<form class="wiz-step" method="post" action="<?php echo $form_url; ?>" id="prescreen">
					<input type="hidden" name="action" value="hcc_reg_prescreen"><?php echo $nonce( 'hcc_reg_prescreen' ); // phpcs:ignore ?>
					<div class="form-card"><h2>About you</h2><p class="sub">A few questions so we can match you to the right shifts. No uploads yet: that's the next step. Your answers save when you press Submit.</p></div>

					<fieldset class="q-group"><legend>Personal details</legend><div class="form-grid">
						<div class="f"><label for="title">Title</label><?php echo $select( 'title', array( 'Mr', 'Mrs', 'Miss', 'Ms', 'Mx', 'Dr' ), $g( 'title' ) ); // phpcs:ignore ?></div>
						<?php echo $text( 'dob', 'Date of birth', 'date' ) . $text( 'ni', 'National Insurance number', 'text', ' placeholder="QQ 12 34 56 C"' ); // phpcs:ignore ?>
					</div></fieldset>

					<fieldset class="q-group" id="rtw"><legend>Right to work</legend><div class="form-grid">
						<fieldset class="f full"><legend>Are you a British or Irish citizen?</legend><?php echo $radio( 'british', array( 'Yes', 'No' ), $g( 'british' ) ); // phpcs:ignore ?></fieldset>
						<div class="f" data-show="british=No"><label for="visa">Your immigration status or visa</label><?php echo $select( 'visa', hcc_visa_options(), $g( 'visa' ) ); // phpcs:ignore ?></div>
						<div class="f" data-show="british=No"><label for="share">Right to work share code</label><input type="text" id="share" name="share" value="<?php echo esc_attr( $g( 'share' ) ); ?>" placeholder="W12 345 678"><p class="hint">Get it at gov.uk/prove-right-to-work</p></div>
						<p class="note-info full" data-show="visa=Student visa">Student visas limit your hours in term time. In the next step we'll ask for your university term dates letter.</p>
						<p class="note-info full" data-show="visa=Health and Care Worker visa|Skilled Worker visa">Sponsored visas: in the next step we'll ask for your certificate of sponsorship.</p>
						<fieldset class="f"><legend>Do you hold a UK driving licence?</legend><?php echo $radio( 'licence', array( 'Full', 'Provisional', 'No' ), $g( 'licence' ) ); // phpcs:ignore ?></fieldset>
						<fieldset class="f"><legend>Do you have your own vehicle?</legend><?php echo $radio( 'vehicle', array( 'Yes', 'No' ), $g( 'vehicle' ) ); // phpcs:ignore ?></fieldset>
					</div></fieldset>

					<fieldset class="q-group"><legend>DBS</legend>
						<fieldset class="f"><legend>Do you have a valid enhanced DBS certificate?</legend><div class="radio-cards">
							<?php foreach ( array( 'update' => array( 'Yes, and it\'s on the Update Service', 'Best: we can check it online straight away' ), 'cert' => array( 'Yes, but it\'s not on the Update Service', 'We\'ll check whether it can be used' ), 'none' => array( 'No, I don\'t have one', 'No problem, we\'ll apply for one for you' ) ) as $v => $l ) : ?>
								<label><input type="radio" name="dbs" value="<?php echo esc_attr( $v ); ?>"<?php checked( $g( 'dbs' ), $v ); ?>><span><b><?php echo esc_html( $l[0] ); ?></b><small><?php echo esc_html( $l[1] ); ?></small></span></label>
							<?php endforeach; ?>
						</div></fieldset>
						<p class="note-info" data-show="dbs=update">Heads up: in the next step we'll ask you to upload your DBS certificate. Have your certificate number ready too.</p>
						<div class="f" data-show="dbs=update" style="max-width:360px;margin-top:12px"><label for="dbsno">DBS certificate number (12 digits)</label><input type="text" id="dbsno" name="dbsno" inputmode="numeric" maxlength="12" value="<?php echo esc_attr( $g( 'dbsno' ) ); ?>"></div>
						<p class="note-info" data-show="dbs=cert">In the next step, upload your certificate. Our compliance team will tell you if a new one is needed.</p>
						<p class="note-info" data-show="dbs=none">We'll contact you to start a new enhanced DBS application. You can still carry on with your registration.</p>
					</fieldset>

					<fieldset class="q-group"><legend>Education and experience</legend><div class="form-grid">
						<div class="f"><label for="edu">Highest level of education</label><?php echo $select( 'edu', array( 'GCSEs or equivalent', 'A levels or equivalent', 'NVQ / Diploma Level 2', 'NVQ / Diploma Level 3', 'Higher education diploma', 'Degree', 'Postgraduate' ), $g( 'edu' ) ); // phpcs:ignore ?></div>
						<div class="f"><label for="emp">Current employment status</label><?php echo $select( 'emp', array( 'Employed full time', 'Employed part time', 'Agency work', 'Self-employed', 'Student', 'Not working' ), $g( 'emp' ) ); // phpcs:ignore ?></div>
						<div class="f"><label for="exp">Experience in this role</label><?php echo $select( 'exp', array( 'Less than 6 months', '6 to 12 months', '1 to 2 years', '3 to 5 years', 'More than 5 years' ), $g( 'exp' ) ); // phpcs:ignore ?></div>
						<fieldset class="f"><legend>Do you have valid training certificates, dated within the last year?</legend><?php echo $radio( 'trainok', array( 'All' => 'Yes, all', 'Some' => 'Some', 'None' => 'No' ), $g( 'trainok' ) ); // phpcs:ignore ?></fieldset>
						<fieldset class="f"><legend><?php echo preg_match( '/nurse|hca|senior/', $role ) ? 'Do you have a blue tunic?' : ( preg_match( '/chef|kitchen|catering|barista/', $role ) ? 'Do you have chef whites or kitchen uniform?' : 'Do you have a suitable work uniform?' ); ?></legend><?php echo $radio( 'uniform', array( 'Yes', 'No' ), $g( 'uniform' ) ); // phpcs:ignore ?></fieldset>
						<div class="f"><label for="size">Uniform size</label><?php echo $select( 'size', array( 'XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL' ), $g( 'size' ), 'Choose a comfortable fit' ); // phpcs:ignore ?></div>
					</div></fieldset>

					<fieldset class="q-group"><legend>Professional referees</legend>
						<p class="hint" style="margin-bottom:12px">Two people who have managed you at work, ideally your most recent employers.</p>
						<div class="ref-grid">
							<?php foreach ( array( 1, 2 ) as $n ) : ?>
							<div><h4><?php echo 1 === $n ? '1st' : '2nd'; ?> referee</h4><div class="form-grid one">
								<?php echo $text( 'r' . $n . 'n', 'Full name' ) . $text( 'r' . $n . 'p', 'Position and organisation' ) . $text( 'r' . $n . 'e', 'Email', 'email' ) . $text( 'r' . $n . 't', 'Phone', 'tel' ); // phpcs:ignore ?>
							</div></div>
							<?php endforeach; ?>
						</div>
					</fieldset>

					<fieldset class="q-group"><legend>Emergency contact</legend><div class="form-grid">
						<?php echo $text( 'kn', 'Name of next of kin' ) . $text( 'kr', 'Relationship' ) . $text( 'kt', 'Phone', 'tel' ) . '<div class="f full"><label for="ka">Address</label><input type="text" id="ka" name="ka" autocomplete="off" placeholder="House number and street" value="' . esc_attr( $g( 'ka' ) ) . '"></div>' . $text( 'ktown', 'Town or city' ) . $text( 'kpc', 'Postcode', 'text', ' autocapitalize="characters"' ); // phpcs:ignore ?>
					</div></fieldset>

					<fieldset class="q-group" id="bank"><legend>Bank details</legend>
						<p class="hint" style="margin-bottom:12px">Needed to pay you for your shifts. Only HCC managers can see these details.</p>
						<div class="form-grid">
							<?php echo $text( 'ban', 'Account name' ); // phpcs:ignore ?>
							<fieldset class="f"><legend>Personal or limited company?</legend><?php echo $radio( 'btype', array( 'Personal', 'LTD' ), $g( 'btype' ) ); // phpcs:ignore ?></fieldset>
							<?php echo $text( 'bsc', 'Sort code', 'text', ' inputmode="numeric" placeholder="00-00-00"' ) . $text( 'bno', 'Account number', 'text', ' inputmode="numeric" maxlength="8"' ); // phpcs:ignore ?>
						</div>
					</fieldset>

					<div class="form-card"><div class="form-actions" style="margin-top:0"><button class="btn btn-navy" type="submit"><?php echo $submitted ? 'Save my answers' : 'Submit and continue to documents'; ?></button><?php if ( $submitted ) : ?><a class="link-btn" href="<?php echo esc_url( hcc_portal_url() ); ?>">Back to my portal</a><?php endif; ?></div></div>
				</form>

				<?php elseif ( 5 === $step || 6 === $step ) :
					$set   = 5 === $step ? 'docs' : 'train';
					$items = hcc_items( $aid, $set );
					$cnt   = hcc_reg_counts_text( $aid, $set );
					?>
				<div class="form-card wiz-step" data-set="<?php echo esc_attr( $set ); ?>">
					<?php if ( 5 === $step ) : ?>
						<h2>Upload your documents</h2>
						<p class="sub">Upload what you have now. If you don't have something yet, tick "I don't have this yet" and carry on. You can add it later from your portal.</p>
						<div class="tips"><b>Photo tips:</b> lay the document flat in daylight, show all four corners, no flash or glare. PDF, JPG or PNG up to 10MB. Photos from your phone are made smaller automatically.</div>
					<?php else : ?>
						<h2>Training certificates</h2>
						<p class="sub">Certificates for <b><?php echo esc_html( $r['name'] ); ?></b>. They should be in date and show your full name. Missing some? Tick "I don't have this yet". We can help you book training.</p>
						<?php if ( ! empty( $r['train_note'] ) ) : ?><p class="note-info">For this role training is <?php echo esc_html( strtolower( $r['train_note'] ) ); ?>.</p><?php endif; ?>
					<?php endif; ?>
					<div class="progress" data-bar><i style="width:<?php echo (int) $cnt['pct']; ?>%"></i></div>
					<div class="doc-list">
						<?php foreach ( $items as $k => $it ) { echo hcc_item_row( $aid, $k, $it, 'wizard' ); } // phpcs:ignore ?>
					</div>
					<form class="form-actions" method="post" action="<?php echo $form_url; ?>">
						<?php if ( 5 === $step ) : ?>
							<input type="hidden" name="action" value="hcc_reg_next"><input type="hidden" name="to" value="6"><?php echo $nonce( 'hcc_reg_next' ); // phpcs:ignore ?>
							<button class="btn btn-navy" type="submit">Save and continue to training</button>
						<?php else : ?>
							<input type="hidden" name="action" value="hcc_reg_submit"><?php echo $nonce( 'hcc_reg_submit' ); // phpcs:ignore ?>
							<button class="btn btn-navy" type="submit">Submit my registration</button>
						<?php endif; ?>
						<span class="form-note" data-count><?php echo esc_html( $cnt['text'] ); ?></span>
					</form>
				</div>

				<?php else :
					$d = hcc_counts( $aid, hcc_items( $aid, 'docs' ) );
					$t = hcc_counts( $aid, hcc_items( $aid, 'train' ) );
					?>
				<div class="form-card wiz-step">
					<div class="success" style="grid-template-columns:1fr"><div>
						<div class="tick"><?php echo hcc_icon( 'check', 30 ); // phpcs:ignore ?></div>
						<h2>Thank you, <?php echo esc_html( hcc_meta( $aid, 'first' ) ); ?></h2>
						<p>Your registration is with our compliance team. Your reference is <b><?php echo esc_html( hcc_meta( $aid, 'ref' ) ); ?></b>.</p>
						<ol>
							<li>We review your answers and files, usually within 1 working day.</li>
							<li>If anything is missing or needs replacing, we email you and show it in your portal.</li>
							<li>Most candidates are ready to work within 3 days.</li>
						</ol>
						<p style="margin-top:14px">You added <b><?php echo (int) $d['have']; ?> of <?php echo (int) $d['need']; ?></b> documents and <b><?php echo (int) $t['have']; ?> of <?php echo (int) $t['need']; ?></b> training certificates.<?php echo $d['have'] < $d['need'] || $t['have'] < $t['need'] ? ' You can add the rest from your portal at any time.' : ''; ?></p>
						<div class="form-actions"><a class="btn btn-sun" href="<?php echo esc_url( hcc_portal_url() ); ?>">Go to my candidate portal</a></div>
					</div></div>
				</div>
				<?php endif; ?>
			</div>

			<aside class="wiz-side">
				<div class="side-card">
					<h3>Registering as</h3>
					<div class="side-role"><img src="<?php echo esc_url( hcc_img( $r['img'] ) ); ?>" alt="" width="56" height="56"><b><?php echo esc_html( $r['name'] ); ?></b></div>
					<p style="font-size:14px;color:var(--muted)"><?php echo $aid ? 'Your reference is <b>' . esc_html( hcc_meta( $aid, 'ref' ) ) . '</b>.' : 'Your reference is created when you make your account.'; // phpcs:ignore ?></p>
				</div>
				<div class="side-card" style="background:var(--white);border:1px solid var(--line)">
					<div class="lock"><?php echo hcc_icon( 'lock', 18 ); // phpcs:ignore ?><span>Your documents are stored privately. Only our compliance team can see them.</span></div>
					<div class="lock"><?php echo hcc_icon( 'clock', 18 ); // phpcs:ignore ?><span>Stop any time. Log in later and carry on where you left off.</span></div>
				</div>
				<div class="help" style="margin-top:0"><h3>Need help?</h3><p>Call us, 24 hours a day.</p><div class="line"><?php echo esc_html( hcc_opt( 'phone' ) ); ?></div></div>
			</aside>
		</div>
	</section>
</main>
<?php
get_footer();
