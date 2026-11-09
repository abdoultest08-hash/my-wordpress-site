<?php
/**
 * Onboarding pack: four documents signed in the portal, one after another.
 *
 *   1. Employee & Worker Privacy Notice and GDPR Declaration
 *   2. Starter Checklist (HMRC)
 *   3. Workplace Pension Options (opt in / not now)
 *   4. Zero-Hours Employment Contract, with its confidentiality clause
 *
 * Order: a member of staff signs first for HCC-Temps and releases the pack (with the start date
 * and role). The candidate then fills in their parts (pre-filled from registration, all editable),
 * draws a signature for each document and submits. The site makes one PDF with every page and a
 * signing record (who signed, when, from which IP address and device), keeps it encrypted in the
 * candidate's private folder, and emails it to the candidate and the staffing inbox.
 *
 * Wording: data/sign-docs.php. Stored on the candidate record as meta _hcc_pack.
 */

defined( 'ABSPATH' ) || exit;

function hcc_sign_docs() {
	static $d = null;
	if ( null === $d ) {
		$d = include get_template_directory() . '/data/sign-docs.php';
	}
	return $d;
}

function hcc_pack( $id ) {
	$p = hcc_meta( $id, 'pack', array() );
	return is_array( $p ) ? $p : array();
}

/** Fill {{name}}, {{role}}, {{start}}, {{email}} in a piece of document text. */
function hcc_sign_fill( $html, $vals ) {
	foreach ( array( 'name', 'role', 'start', 'email' ) as $k ) {
		$html = str_replace( '{{' . $k . '}}', esc_html( isset( $vals[ $k ] ) ? $vals[ $k ] : '' ), $html );
	}
	return $html;
}

/** A signature drawn in the browser: a PNG data URL, checked and returned without the prefix ('' if not valid). */
function hcc_sign_clean( $data ) {
	$data = (string) $data;
	if ( 0 !== strpos( $data, 'data:image/png;base64,' ) || strlen( $data ) > 400000 ) {
		return '';
	}
	$b64 = substr( $data, 22 );
	$bin = base64_decode( $b64, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	if ( ! $bin || ! ( $info = @getimagesizefromstring( $bin ) ) || IMAGETYPE_PNG !== $info[2] ) { // phpcs:ignore
		return '';
	}
	return $b64;
}

function hcc_sign_ip() {
	return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
}

function hcc_sign_ua() {
	return isset( $_SERVER['HTTP_USER_AGENT'] ) ? mb_substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 200 ) : '';
}

/** A drawing pad. $name is the form field that receives the PNG data URL. */
function hcc_sign_pad( $name, $label, $copy = true ) {
	return '<div class="sig" data-sig><span class="sig-l">' . esc_html( $label ) . '</span><canvas class="sig-c" width="600" height="180" aria-label="' . esc_attr( $label ) . ': draw your signature with your finger or mouse"></canvas><input type="hidden" name="' . esc_attr( $name ) . '" data-sig-val><div class="sig-tools"><button type="button" class="link-btn" data-sig-clear>Clear</button>' . ( $copy ? '<button type="button" class="link-btn" data-sig-copy>Use my previous signature</button>' : '' ) . '<span class="sig-hint">Sign with your finger or mouse</span></div></div>';
}

/* ---------- Staff: release (sign first), withdraw ---------- */

add_action( 'hcc_staff_folder_declaration', function ( $aid ) {
	$p      = hcc_pack( $aid );
	$status = isset( $p['status'] ) ? $p['status'] : '';
	$hidden = '<input type="hidden" name="action" value="hcc_pack_staff"><input type="hidden" name="a" value="' . (int) $aid . '">' . wp_nonce_field( 'hcc_pack_' . $aid, '_wpnonce', true, false );
	echo '<details open class="p-sec p-card" id="declaration"><summary class="p-card-head"><h2>Onboarding documents</h2><span class="p-count">' . esc_html( 'signed' === $status ? 'Signed' : ( 'sent' === $status ? 'Waiting for candidate' : 'Not sent yet' ) ) . '</span></summary>';
	if ( 'signed' === $status && empty( $p['fields'] ) ) {
		echo '<p class="pack-ok">✓ Marked as signed' . ( hcc_meta( $aid, 'dec_signed' ) ? ' on ' . esc_html( wp_date( 'j M Y', (int) hcc_meta( $aid, 'dec_signed' ) ) ) : '' ) . '</p><p class="hint">Recorded outside the portal, so there is no signed PDF here.</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-top:10px">' . $hidden . '<button class="link-btn" name="do" value="reset">Issue the onboarding documents online</button></form>'; // phpcs:ignore
	} elseif ( 'signed' === $status ) {
		echo '<p class="pack-ok">✓ Signed by ' . esc_html( $p['fields']['full_name'] ) . ' on ' . esc_html( wp_date( 'j M Y, H:i', (int) $p['signed_at'] ) ) . '</p>';
		echo '<ul class="pack-sum"><li>Start date: <b>' . esc_html( wp_date( 'j M Y', strtotime( $p['start'] ) ) ) . '</b></li><li>Pension: <b>' . ( 'in' === $p['fields']['pension'] ? 'Opted in' : 'Not joining now' ) . '</b></li><li>Starter checklist: <b>Statement ' . esc_html( $p['fields']['statement'] ) . '</b>' . ( 'Yes' === $p['fields']['q11'] ? ' · student loan: ' . esc_html( implode( ', ', (array) $p['fields']['q13'] ) ? implode( ', ', (array) $p['fields']['q13'] ) : 'see form' ) : '' ) . '</li><li>Company signature: ' . esc_html( $p['company']['name'] . ', ' . $p['company']['title'] ) . '</li></ul>';
		echo '<div class="form-actions"><a class="btn btn-navy btn-sm" href="' . esc_url( hcc_pack_pdf_url( $aid ) ) . '">Download signed PDF</a><a class="btn btn-ghost btn-sm" href="' . esc_url( hcc_pack_pdf_url( $aid, false ) ) . '" target="_blank" rel="noopener">View</a></div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-top:10px">' . $hidden . '<button class="link-btn" name="do" value="reset" onclick="return confirm(\'Issue a new pack? The signed PDF stays in the candidate folder; the candidate will be asked to sign a new pack.\')">Issue a new pack</button></form>'; // phpcs:ignore
	} elseif ( 'sent' === $status ) {
		echo '<p>Released on ' . esc_html( wp_date( 'j M Y, H:i', (int) $p['released_at'] ) ) . ' by ' . esc_html( $p['company']['name'] ) . ' (signed for HCC-Temps). Start date ' . esc_html( wp_date( 'j M Y', strtotime( $p['start'] ) ) ) . '.</p><p class="hint">The candidate has a to-do and an email to sign.</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="form-actions">' . $hidden . '<button class="btn btn-ghost btn-sm" name="do" value="remind">Email a reminder</button><button class="link-btn" name="do" value="reset" onclick="return confirm(\'Withdraw this pack? You can release a new one.\')">Withdraw</button></form>'; // phpcs:ignore
	} else {
		$pct = hcc_progress( $aid );
		echo '<p class="hint">Release the documents once everything is approved. You sign first for HCC-Temps; the candidate then fills in and signs the GDPR declaration, starter checklist, pension form and contract.</p>';
		if ( $pct < 100 ) {
			echo '<p class="form-alert" style="margin:8px 0">Profile ' . (int) $pct . '%: not everything is approved yet.</p>';
		}
		$title = get_user_meta( get_current_user_id(), 'hcc_sign_title', true );
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="pack-release" data-sign-form>' . $hidden . '<input type="hidden" name="do" value="release">'; // phpcs:ignore
		echo '<div class="f"><label for="pk-start">Employment start date</label><input type="date" id="pk-start" name="start" required value="' . esc_attr( wp_date( 'Y-m-d', strtotime( '+1 day' ) ) ) . '"></div>';
		echo '<div class="f"><label for="pk-role">Role</label><input type="text" id="pk-role" name="role" required value="' . esc_attr( hcc_role_name( hcc_meta( $aid, 'role_key' ) ) ) . '"></div>';
		echo '<div class="f"><label for="pk-name">Signing for HCC-Temps: your name</label><input type="text" id="pk-name" name="sname" required value="' . esc_attr( wp_get_current_user()->display_name ) . '"></div>';
		echo '<div class="f"><label for="pk-title">Your job title</label><input type="text" id="pk-title" name="stitle" required value="' . esc_attr( $title ? $title : '' ) . '" placeholder="e.g. Compliance Manager"></div>';
		echo hcc_sign_pad( 'ssig', 'Your signature (for HCC-Temps Ltd)', false ); // phpcs:ignore
		echo '<button class="btn btn-sun btn-sm" type="submit">Sign and send to candidate</button></form>';
	}
	echo '</details>';
} );

add_action( 'admin_post_hcc_pack_staff', function () {
	$aid = isset( $_POST['a'] ) ? absint( $_POST['a'] ) : 0;
	if ( ! $aid || ! hcc_is_staff() || ! check_admin_referer( 'hcc_pack_' . $aid ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	$do   = isset( $_POST['do'] ) ? sanitize_key( $_POST['do'] ) : '';
	$me   = hcc_staff_name();
	$back = hcc_staff_url( array( 'candidate' => $aid ) );
	$p    = hcc_pack( $aid );
	if ( 'release' === $do ) {
		$f     = function ( $k ) { return isset( $_POST[ $k ] ) ? sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) : ''; }; // phpcs:ignore WordPress.Security.NonceVerification
		$sig   = hcc_sign_clean( isset( $_POST['ssig'] ) ? wp_unslash( $_POST['ssig'] ) : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$start = $f( 'start' );
		if ( ! $sig || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $start ) || ! $f( 'sname' ) || ! $f( 'stitle' ) || ! $f( 'role' ) ) {
			wp_safe_redirect( add_query_arg( 'msg', 'pack_need', $back ) . '#declaration' );
			exit;
		}
		update_user_meta( get_current_user_id(), 'hcc_sign_title', $f( 'stitle' ) );
		$old = $p;
		$p   = array( 'status' => 'sent', 'start' => $start, 'role' => $f( 'role' ), 'released_at' => time(), 'company' => array( 'sig' => $sig, 'name' => $f( 'sname' ), 'title' => $f( 'stitle' ), 'at' => time(), 'ip' => hcc_sign_ip(), 'ua' => hcc_sign_ua(), 'by' => $me ) );
		if ( ! empty( $old['history'] ) || ! empty( $old['pdf'] ) ) {
			$p['history'] = array_merge( isset( $old['history'] ) ? (array) $old['history'] : array(), ! empty( $old['pdf'] ) ? array( array( 'pdf' => $old['pdf'], 'signed_at' => $old['signed_at'] ) ) : array() );
		}
		hcc_set( $aid, 'pack', $p );
		hcc_set( $aid, 'dec_sent', time() );
		hcc_set( $aid, 'dec_signed', '' );
		hcc_notify( $aid, array( 'type' => 'action', 'title' => 'Sign your onboarding documents', 'text' => 'Your GDPR declaration, starter checklist, pension form and contract are ready. It takes about 10 minutes.', 'link' => '#sign', 'key' => 'sign:pack', 'todo' => true ) );
		hcc_email_pack( $aid, false );
		hcc_log( $aid, 'Onboarding documents signed for HCC-Temps by ' . $p['company']['name'] . ' and released to the candidate (start date ' . wp_date( 'd/m/Y', strtotime( $start ) ) . ')' );
		wp_safe_redirect( add_query_arg( 'msg', 'pack_sent', $back ) . '#declaration' );
		exit;
	}
	if ( 'remind' === $do && isset( $p['status'] ) && 'sent' === $p['status'] ) {
		hcc_email_pack( $aid, true );
		hcc_log( $aid, 'Reminder emailed: sign the onboarding documents' );
		wp_safe_redirect( add_query_arg( 'msg', 'pack_remind', $back ) . '#declaration' );
		exit;
	}
	if ( 'reset' === $do && $p ) {
		$keep = array( 'history' => array_merge( isset( $p['history'] ) ? (array) $p['history'] : array(), ! empty( $p['pdf'] ) ? array( array( 'pdf' => $p['pdf'], 'signed_at' => $p['signed_at'] ) ) : array() ) );
		hcc_set( $aid, 'pack', $keep );
		hcc_set( $aid, 'dec_sent', '' );
		hcc_set( $aid, 'dec_signed', '' );
		hcc_todo_done( $aid, 'sign:pack' );
		hcc_log( $aid, 'Onboarding documents withdrawn' . ( ! empty( $p['pdf'] ) ? ' (the signed PDF is kept)' : '' ) );
		wp_safe_redirect( add_query_arg( 'msg', 'pack_reset', $back ) . '#declaration' );
		exit;
	}
	wp_safe_redirect( $back );
	exit;
} );

add_filter( 'hcc_staff_msgs', function ( $m ) {
	return array_merge( $m, array( 'pack_sent' => 'Signed and sent. The candidate has been emailed.', 'pack_need' => 'Fill in the start date, role, your name and job title, and draw your signature.', 'pack_remind' => 'Reminder sent.', 'pack_reset' => 'Onboarding documents withdrawn.' ) );
} );

function hcc_email_pack( $id, $reminder ) {
	return hcc_mail( hcc_meta( $id, 'email' ), $reminder ? 'Reminder: please sign your onboarding documents' : 'Your onboarding documents are ready to sign',
		'<p>Hi ' . esc_html( hcc_meta( $id, 'first' ) ) . ',</p><p>' . ( $reminder ? 'Just a reminder: your' : 'Good news: your documents have been checked. Your' ) . ' onboarding documents are ready to sign in your portal: the GDPR declaration, starter checklist, pension form and your contract. It takes about 10 minutes.</p>' . hcc_button( hcc_portal_url() . '#sign', 'Sign my documents' ), array(), hcc_opt( 'email_jobs' ) );
}

/* ---------- Candidate portal: Sign documents ---------- */

add_filter( 'hcc_portal_nav', function ( $nav, $id, $base ) {
	$p    = hcc_pack( $id );
	$wait = isset( $p['status'] ) && 'sent' === $p['status'];
	array_splice( $nav, 4, 0, array( array( 'sign', 'Sign documents', 'pen', $base . '#sign', $wait ? '1' : '', $wait ? 'data-hot="1"' : '' ) ) );
	return $nav;
}, 30, 3 );

/** Values to pre-fill the candidate's form. */
function hcc_pack_prefill( $id ) {
	$a = (array) hcc_meta( $id, 'answers', array() );
	return array(
		'full_name'   => hcc_name( $id ),
		'email'       => hcc_meta( $id, 'email' ),
		'phone'       => hcc_meta( $id, 'phone' ),
		'last_name'   => hcc_meta( $id, 'last' ),
		'first_names' => hcc_meta( $id, 'first' ),
		'sex'         => '',
		'dob'         => isset( $a['dob'] ) ? $a['dob'] : '',
		'address'     => trim( hcc_meta( $id, 'addr1' ) . ', ' . hcc_meta( $id, 'town' ) . ' ' . hcc_meta( $id, 'postcode' ), ', ' ),
		'ni'          => isset( $a['ni'] ) ? strtoupper( $a['ni'] ) : '',
	);
}

add_action( 'hcc_portal_views', function ( $id, $ctx ) {
	$p      = hcc_pack( $id );
	$status = isset( $p['status'] ) ? $p['status'] : '';
	$docs   = hcc_sign_docs();
	echo '<section class="view" data-view="sign" id="sign"><div class="view-head"><h2>Sign documents</h2><p>Your GDPR declaration, starter checklist, pension form and contract.</p></div>';
	if ( 'signed' === $status && empty( $p['pdf'] ) ) {
		echo '<div class="p-card pack-done"><h3>✓ All signed</h3><p>Your onboarding documents are complete. If you need a copy, send us a message.</p></div></section>';
		return;
	}
	if ( 'signed' === $status ) {
		echo '<div class="p-card pack-done"><h3>✓ All signed</h3><p>You signed your onboarding documents on ' . esc_html( wp_date( 'j F Y', (int) $p['signed_at'] ) ) . '. We emailed you a copy.</p><div class="form-actions"><a class="btn btn-navy btn-sm" href="' . esc_url( hcc_pack_pdf_url( $id ) ) . '">Download my signed documents (PDF)</a></div></div></section>';
		return;
	}
	if ( 'sent' !== $status ) {
		echo '<div class="p-card pack-locked">' . hcc_icon( 'lock', 26 ) . '<div><h3>Not ready yet</h3><p>Your documents will be available to sign once our compliance team has approved your registration. We\'ll email you and add a to-do here.</p></div></div></section>'; // phpcs:ignore
		return;
	}
	if ( 'portal' !== $ctx ) {
		echo '<div class="p-card"><p>Released on ' . esc_html( wp_date( 'j M Y', (int) $p['released_at'] ) ) . '. Waiting for the candidate to sign.</p></div></section>';
		return;
	}
	$v    = hcc_pack_prefill( $id );
	$fill = array( 'name' => $v['full_name'], 'role' => $p['role'], 'start' => wp_date( 'j F Y', strtotime( $p['start'] ) ), 'email' => $v['email'] );
	$in   = function ( $name, $label, $val, $type = 'text', $extra = '' ) {
		return '<div class="f"><label for="sg-' . esc_attr( $name ) . '">' . esc_html( $label ) . '</label><input type="' . esc_attr( $type ) . '" id="sg-' . esc_attr( $name ) . '" name="f[' . esc_attr( $name ) . ']" value="' . esc_attr( $val ) . '" required ' . $extra . '></div>';
	};
	$yn = function ( $name, $q, $show = '' ) {
		return '<fieldset class="sg-q"' . ( $show ? ' data-sg-show="' . esc_attr( $show ) . '"' : '' ) . '><legend>' . esc_html( $q ) . '</legend><label><input type="radio" name="f[' . esc_attr( $name ) . ']" value="Yes" required> Yes</label><label><input type="radio" name="f[' . esc_attr( $name ) . ']" value="No"> No</label></fieldset>';
	};
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="pack-form" id="packForm" data-sign-form novalidate><input type="hidden" name="action" value="hcc_pack_sign">' . wp_nonce_field( 'hcc_pack_sign_' . $id, '_wpnonce', true, false ); // phpcs:ignore
	echo '<ol class="sg-steps" aria-label="Steps"><li class="on">GDPR</li><li>Starter checklist</li><li>Pension</li><li>Contract</li></ol>';
	echo '<p class="hint">We have filled in what you told us at registration. Please check everything and change anything that is wrong.</p>';

	// 1. GDPR.
	echo '<div class="sg-step p-card" data-step="1"><h3>1. ' . esc_html( $docs['gdpr']['title'] ) . '</h3><div class="sg-doc">' . wp_kses_post( $docs['gdpr']['intro'] ) . '</div>';
	echo '<div class="sg-grid">' . $in( 'full_name', 'Full name', $v['full_name'] ) . $in( 'email', 'Email', $v['email'], 'email' ) . $in( 'phone', 'Telephone', $v['phone'], 'tel' ) . '</div>'; // phpcs:ignore
	echo '<details class="sg-read"><summary>Read the full privacy notice</summary><div class="sg-doc">' . wp_kses_post( $docs['gdpr']['body'] ) . '</div></details>';
	echo '<p class="sg-h">GDPR declaration: I confirm that</p><ul class="sg-list">';
	foreach ( $docs['gdpr']['confirm'] as $c ) {
		echo '<li>' . esc_html( $c ) . '</li>';
	}
	echo '</ul><label class="consent"><input type="checkbox" name="f[gdpr_ok]" value="1" required> I confirm all of the above</label>' . hcc_sign_pad( 'sig[gdpr]', 'Signature: GDPR declaration', false ) . '</div>'; // phpcs:ignore

	// 2. Starter checklist.
	echo '<div class="sg-step p-card" data-step="2"><h3>2. ' . esc_html( $docs['starter']['title'] ) . '</h3><details class="sg-read"><summary>About this form</summary><div class="sg-doc">' . wp_kses_post( $docs['starter']['intro'] ) . '</div></details>';
	echo '<p class="sg-h">Your personal details</p><div class="sg-grid">' . $in( 'last_name', '1. Last name', $v['last_name'] ) . $in( 'first_names', '2. First names', $v['first_names'] ); // phpcs:ignore
	echo '<div class="f"><label for="sg-sex">3. Sex (as on your HMRC record)</label><select id="sg-sex" name="f[sex]" required><option value="">Choose…</option><option>Male</option><option>Female</option></select></div>';
	echo $in( 'dob', '4. Date of birth', $v['dob'], 'date' ) . '<div class="f sg-wide"><label for="sg-address">5. Address</label><input type="text" id="sg-address" name="f[address]" value="' . esc_attr( $v['address'] ) . '" required></div>' . $in( 'ni', '6. National Insurance number', $v['ni'], 'text', 'pattern="[A-Za-z]{2}\s?\d{2}\s?\d{2}\s?\d{2}\s?[A-Da-d]" title="For example QQ 12 34 56 C" autocapitalize="characters"' ); // phpcs:ignore
	echo '<div class="f"><label>7. Employment start date</label><input type="text" value="' . esc_attr( $fill['start'] ) . '" readonly></div></div>';
	echo '<p class="sg-h">Employee statement</p>' . $yn( 'q8', '8. Do you have another job?' ) . $yn( 'q9', '9. Do you receive payments from a State, workplace or private pension?' ) . $yn( 'q10', '10. Since 6 April have you received payments from another job which has ended, or any taxable benefits (such as Jobseeker\'s Allowance, Employment and Support Allowance or Incapacity Benefit)?', 'q8=No&q9=No' ); // phpcs:ignore
	echo '<p class="sg-statement" id="sgStatement" hidden></p>';
	echo '<p class="sg-h">Student loans</p>' . $yn( 'q11', '11. Do you have a student or postgraduate loan?' ); // phpcs:ignore
	echo '<fieldset class="sg-q" data-sg-show="q11=Yes"><legend>12. Do any of the following statements apply?</legend><ul class="sg-list">';
	foreach ( $docs['starter']['q12'] as $s ) {
		echo '<li>' . esc_html( $s ) . '</li>';
	}
	echo '</ul><label><input type="radio" name="f[q12]" value="Yes"> Yes</label><label><input type="radio" name="f[q12]" value="No"> No</label></fieldset>';
	echo '<fieldset class="sg-q" data-sg-show="q11=Yes&q12=No"><legend>13. Tick the student loan or loans that you have</legend>';
	foreach ( array( 'Plan 1', 'Plan 2', 'Plan 4', 'Plan 5', 'Postgraduate Loan' ) as $pl ) {
		echo '<label><input type="checkbox" name="f[q13][]" value="' . esc_attr( $pl ) . '"> ' . esc_html( $pl ) . '</label>';
	}
	echo '</fieldset>' . hcc_sign_pad( 'sig[starter]', 'Signature: starter checklist' ) . '</div>'; // phpcs:ignore

	// 3. Pension.
	echo '<div class="sg-step p-card" data-step="3"><h3>3. ' . esc_html( $docs['pension']['title'] ) . '</h3><div class="sg-doc">' . wp_kses_post( $docs['pension']['intro'] ) . '</div>';
	echo '<fieldset class="sg-q sg-choice"><legend>Your choice</legend><label><input type="radio" name="f[pension]" value="in" required><span><b>Opt in: join the pension scheme now</b><small>' . esc_html( str_replace( '{{name}}', $v['full_name'], $docs['pension']['optin'] ) ) . '</small></span></label><label><input type="radio" name="f[pension]" value="out"><span><b>Not now</b><small>' . esc_html( $docs['pension']['optout'] ) . '</small></span></label></fieldset>';
	echo hcc_sign_pad( 'sig[pension]', 'Signature: pension form' ) . '</div>'; // phpcs:ignore

	// 4. Contract.
	echo '<div class="sg-step p-card" data-step="4"><h3>4. ' . esc_html( $docs['contract']['title'] ) . '</h3><div class="sg-doc sg-contract">' . wp_kses_post( hcc_sign_fill( $docs['contract']['body'], $fill ) ) . '</div>';
	echo '<div class="sg-company"><b>For and on behalf of HCC-Temps Ltd</b><img src="data:image/png;base64,' . esc_attr( $p['company']['sig'] ) . '" alt="Signature of ' . esc_attr( $p['company']['name'] ) . '"><span>' . esc_html( $p['company']['name'] . ', ' . $p['company']['title'] . ' · ' . wp_date( 'j M Y', (int) $p['company']['at'] ) ) . '</span></div>';
	echo '<label class="consent"><input type="checkbox" name="f[contract_ok]" value="1" required> I have read and agree to this contract</label>' . hcc_sign_pad( 'sig[contract]', 'Signature: employee' ); // phpcs:ignore
	echo '<p class="sg-h">Confidentiality clause</p><p class="sg-doc">' . esc_html( str_replace( '{{name}}', $v['full_name'], $docs['contract']['confidentiality'] ) ) . '</p><label class="consent"><input type="checkbox" name="f[conf_ok]" value="1" required> I agree to the confidentiality clause</label>' . hcc_sign_pad( 'sig[conf]', 'Signature: confidentiality clause' ) . '</div>'; // phpcs:ignore

	echo '<div class="sg-nav"><button type="button" class="btn btn-ghost" data-sg-back hidden>Back</button><button type="button" class="btn btn-navy" data-sg-next>Next</button><button type="submit" class="btn btn-sun" data-sg-submit hidden>Sign and submit all 4 documents</button></div>';
	echo '<p class="hint sg-legal">By signing, you agree that your drawn signature is your legal signature. We record the date, time, IP address and device with each signature.</p></form></section>';
}, 20, 2 );

/* ---------- Candidate submits ---------- */

add_action( 'admin_post_hcc_pack_sign', function () {
	$id = hcc_applicant_for_user( get_current_user_id() );
	if ( ! $id || ! check_admin_referer( 'hcc_pack_sign_' . $id ) ) {
		wp_die( 'Your session has expired. Please log in again.', 403 );
	}
	$p = hcc_pack( $id );
	if ( empty( $p['status'] ) || 'sent' !== $p['status'] ) {
		wp_safe_redirect( hcc_portal_url() . '#sign' );
		exit;
	}
	$raw = isset( $_POST['f'] ) ? (array) wp_unslash( $_POST['f'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$f   = array();
	foreach ( array( 'full_name', 'email', 'phone', 'last_name', 'first_names', 'sex', 'dob', 'address', 'ni', 'q8', 'q9', 'q10', 'q11', 'q12', 'pension', 'gdpr_ok', 'contract_ok', 'conf_ok' ) as $k ) {
		$f[ $k ] = isset( $raw[ $k ] ) ? sanitize_text_field( $raw[ $k ] ) : '';
	}
	$f['q13'] = isset( $raw['q13'] ) ? array_values( array_intersect( (array) $raw['q13'], array( 'Plan 1', 'Plan 2', 'Plan 4', 'Plan 5', 'Postgraduate Loan' ) ) ) : array();
	$f['ni']  = strtoupper( preg_replace( '/\s+/', '', $f['ni'] ) );
	$sigs     = array();
	foreach ( array( 'gdpr', 'starter', 'pension', 'contract', 'conf' ) as $k ) {
		$sigs[ $k ] = hcc_sign_clean( isset( $_POST['sig'][ $k ] ) ? wp_unslash( $_POST['sig'][ $k ] ) : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	}
	$need_q10 = 'No' === $f['q8'] && 'No' === $f['q9'];
	$errors   = array();
	foreach ( array( 'full_name', 'email', 'phone', 'last_name', 'first_names', 'address', 'gdpr_ok', 'contract_ok', 'conf_ok' ) as $k ) {
		if ( '' === $f[ $k ] ) {
			$errors[] = $k;
		}
	}
	if ( ! in_array( $f['sex'], array( 'Male', 'Female' ), true ) ) {
		$errors[] = 'sex';
	}
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $f['dob'] ) ) {
		$errors[] = 'dob';
	}
	if ( ! preg_match( '/^[A-Z]{2}\d{6}[A-D]$/', $f['ni'] ) ) {
		$errors[] = 'ni';
	}
	foreach ( array_merge( array( 'q8', 'q9', 'q11' ), $need_q10 ? array( 'q10' ) : array() ) as $k ) {
		if ( ! in_array( $f[ $k ], array( 'Yes', 'No' ), true ) ) {
			$errors[] = $k;
		}
	}
	if ( 'Yes' === $f['q11'] && ! in_array( $f['q12'], array( 'Yes', 'No' ), true ) ) {
		$errors[] = 'q12';
	}
	if ( 'Yes' === $f['q11'] && 'No' === $f['q12'] && ! $f['q13'] ) {
		$errors[] = 'q13';
	}
	if ( ! in_array( $f['pension'], array( 'in', 'out' ), true ) ) {
		$errors[] = 'pension';
	}
	foreach ( $sigs as $k => $s ) {
		if ( ! $s ) {
			$errors[] = 'sig_' . $k;
		}
	}
	if ( $errors ) {
		wp_safe_redirect( add_query_arg( 'hcc_err', 'pack', hcc_portal_url() ) . '#sign' );
		exit;
	}
	if ( ! $need_q10 ) {
		$f['q10'] = '';
	}
	if ( 'Yes' !== $f['q11'] ) {
		$f['q12'] = '';
		$f['q13'] = array();
	} elseif ( 'Yes' === $f['q12'] ) {
		$f['q13'] = array();
	}
	$f['statement'] = ( 'Yes' === $f['q8'] || 'Yes' === $f['q9'] ) ? 'C' : ( 'Yes' === $f['q10'] ? 'B' : 'A' );
	$now            = time();
	$p['fields']    = $f;
	$p['sigs']      = $sigs;
	$p['signer']    = array( 'at' => $now, 'ip' => hcc_sign_ip(), 'ua' => hcc_sign_ua(), 'user' => get_current_user_id() );
	$p['signed_at'] = $now;
	$p['status']    = 'signed';
	// The PDF.
	$html = hcc_pack_html( $id, $p );
	$hash = hash( 'sha256', $html );
	$pdf  = hcc_pdf( str_replace( '{{HASH}}', $hash, $html ) );
	$dir  = hcc_private_dir( $id );
	$name = '90 - ' . trim( preg_replace( '/[^A-Za-z0-9 \'-]+/', '', remove_accents( hcc_name( $id ) ) ) ) . ' - Onboarding pack signed ' . wp_date( 'Y-m-d', $now ) . '.pdf';
	file_put_contents( $dir . '/' . $name, $pdf ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	hcc_encrypt_file( $dir . '/' . $name );
	$p['pdf']      = $name;
	$p['pdf_hash'] = hash( 'sha256', $pdf );
	$p['doc_hash'] = $hash;
	hcc_set( $id, 'pack', $p );
	hcc_set( $id, 'dec_signed', $now );
	hcc_todo_done( $id, 'sign:pack' );
	hcc_notify( $id, array( 'type' => 'update', 'title' => 'Documents signed', 'text' => 'Thank you. We have emailed you a copy of your signed documents.', 'link' => '#sign', 'key' => 'signed:pack' ) );
	hcc_log( $id, 'Onboarding documents signed by the candidate (pension: ' . ( 'in' === $f['pension'] ? 'opted in' : 'not now' ) . ', starter statement ' . $f['statement'] . ')', 'Candidate' );
	// Emails, with the PDF attached.
	$copy = hcc_decrypted_copy( $dir . '/' . $name );
	$att  = $copy ? array( $copy ) : array();
	hcc_mail( $f['email'], 'Your signed onboarding documents', '<p>Hi ' . esc_html( hcc_meta( $id, 'first' ) ) . ',</p><p>Thank you for signing your onboarding documents. A copy is attached for your records. You can also download it from your portal at any time.</p>' . hcc_button( hcc_portal_url() . '#sign', 'Open my portal' ), $att, hcc_opt( 'email_jobs' ) );
	hcc_mail( hcc_opt( 'email_jobs' ), '[SIGNED] ' . hcc_meta( $id, 'ref' ) . ' ' . hcc_name( $id ) . ': onboarding documents signed', '<p>' . esc_html( hcc_name( $id ) ) . ' has signed the GDPR declaration, starter checklist, pension form and contract. The signed PDF is attached.</p><ul><li>Start date: ' . esc_html( wp_date( 'j M Y', strtotime( $p['start'] ) ) ) . '</li><li>Pension: <b>' . ( 'in' === $f['pension'] ? 'opted in' : 'not joining now' ) . '</b></li><li>Starter checklist: statement <b>' . esc_html( $f['statement'] ) . '</b>' . ( $f['q13'] ? ' · student loan: ' . esc_html( implode( ', ', $f['q13'] ) ) : '' ) . '</li><li>NI number: ' . esc_html( $f['ni'] ) . '</li></ul>' . hcc_button( hcc_staff_url( array( 'candidate' => $id ) ) . '#declaration', 'Open in Staff portal' ), $att, $f['email'] );
	if ( 'in' === $f['pension'] ) {
		$docs = hcc_sign_docs();
		hcc_mail( hcc_opt( 'email_jobs' ), 'Employee Pension Opt In', '<p>' . esc_html( wp_date( 'j F Y', $now ) ) . '</p><p>This email is to confirm that ' . esc_html( $f['full_name'] ) . ', in the role of ' . esc_html( $p['role'] ) . ' has opted in for enrolment in our workplace pension scheme, as of ' . esc_html( wp_date( 'j F Y', strtotime( $p['start'] ) ) ) . '.<br>Their email is: ' . esc_html( $f['email'] ) . '.</p>' . wp_kses_post( $docs['pension']['intro'] ) . '<p>Their agreement is as follows:</p><p>' . esc_html( str_replace( '{{name}}', $f['full_name'], $docs['pension']['optin'] ) ) . '</p><p><b>Consented: I wish to opt in to the pension scheme</b></p><p>Signed: ' . esc_html( $f['full_name'] ) . ' (signature in the attached PDF)<br>Date: ' . esc_html( wp_date( 'd/m/Y', $now ) ) . '</p><p>Please action this email accordingly.</p>', $att, $f['email'] );
	}
	hcc_crypto_cleanup( $att );
	wp_safe_redirect( add_query_arg( 'hcc_ok', 'signed', hcc_portal_url() ) . '#sign' );
	exit;
} );

/* ---------- The PDF ---------- */

function hcc_pack_html( $id, $p ) {
	$docs = hcc_sign_docs();
	$f    = $p['fields'];
	$fill = array( 'name' => $f['full_name'], 'role' => $p['role'], 'start' => wp_date( 'j F Y', strtotime( $p['start'] ) ), 'email' => $f['email'] );
	$day  = wp_date( 'd/m/Y', (int) $p['signed_at'] );
	$img  = function ( $b64 ) { return '<img class="sigimg" src="data:image/png;base64,' . $b64 . '">'; };
	$sig  = function ( $label, $b64, $name, $date ) use ( $img ) {
		return '<table class="sigt"><tr><td class="sl">' . esc_html( $label ) . '</td><td>' . $img( $b64 ) . '</td></tr><tr><td class="sl">Print name</td><td>' . esc_html( $name ) . '</td></tr><tr><td class="sl">Date</td><td>' . esc_html( $date ) . '</td></tr></table>';
	};
	$row  = function ( $l, $v ) { return '<tr><th>' . esc_html( $l ) . '</th><td>' . esc_html( $v ) . '</td></tr>'; };
	$yes  = function ( $v ) { return '' === $v ? 'Not applicable' : $v; };
	$head = function ( $title ) { return '<div class="dh">' . hcc_pdf_logo( 110 ) . '<h1>' . esc_html( $title ) . '</h1></div>'; };
	$css  = 'body{font-family:"DejaVu Sans",sans-serif;font-size:9.6px;line-height:1.45;color:#1E2140}h1{font-size:15px;color:#171A45;margin:8px 0 10px}h4{font-size:10.5px;color:#171A45;margin:10px 0 3px}p{margin:0 0 6px}.dh{border-bottom:2px solid #6DC0B8;padding-bottom:4px;margin-bottom:10px}.pb{page-break-before:always}table.f{width:100%;border-collapse:collapse;margin:6px 0 10px}table.f th{width:38%;text-align:left;background:#F4F8F7;font-weight:bold;padding:5px 7px;border:1px solid #E1E7E6}table.f td{padding:5px 7px;border:1px solid #E1E7E6}table.ret{width:100%;border-collapse:collapse}table.ret th,table.ret td{border:1px solid #E1E7E6;padding:4px 6px;text-align:left}.sigt{border-collapse:collapse;margin:8px 0 12px;page-break-inside:avoid}.sigt td{padding:3px 8px;border-bottom:1px solid #E1E7E6;vertical-align:middle}.sl{font-weight:bold;width:110px}.sigimg{height:46px}.box{background:#F4F8F7;border:1px solid #E1E7E6;padding:7px 9px;margin:6px 0}.foot{position:fixed;bottom:-14px;left:0;right:0;font-size:7.5px;color:#5B6078}';
	$h    = '<html><head><meta charset="utf-8"><style>' . $css . '</style></head><body><div class="foot">' . esc_html( hcc_meta( $id, 'ref' ) . ' · ' . $f['full_name'] . ' · Onboarding pack signed ' . $day . ' · Document fingerprint {{HASH}}' ) . '</div>';
	// 1. GDPR.
	$h .= $head( $docs['gdpr']['title'] ) . wp_kses_post( $docs['gdpr']['intro'] ) . '<table class="f">' . $row( 'Full Name', $f['full_name'] ) . $row( 'Email', $f['email'] ) . $row( 'Telephone', $f['phone'] ) . '</table>' . wp_kses_post( $docs['gdpr']['body'] );
	$h .= '<h4>GDPR Declaration</h4><p>I confirm that:</p><ul>' . implode( '', array_map( function ( $c ) { return '<li>' . esc_html( $c ) . '</li>'; }, $docs['gdpr']['confirm'] ) ) . '</ul>' . $sig( 'Signed', $p['sigs']['gdpr'], $f['full_name'], $day );
	// 2. Starter checklist.
	$h .= '<div class="pb"></div>' . $head( $docs['starter']['title'] ) . wp_kses_post( $docs['starter']['intro'] );
	$h .= '<h4>Contact Details</h4><table class="f">' . $row( 'Full Name', $f['full_name'] ) . $row( 'Mobile No', $f['phone'] ) . $row( 'Email', $f['email'] ) . '</table>';
	$h .= '<h4>Employee\'s personal details</h4><table class="f">' . $row( '1. Last Name', $f['last_name'] ) . $row( '2. First Names', $f['first_names'] ) . $row( '3. Sex', $f['sex'] ) . $row( '4. Date of birth', wp_date( 'd/m/Y', strtotime( $f['dob'] ) ) ) . $row( '5. Address', $f['address'] ) . $row( '6. National Insurance Number', $f['ni'] ) . $row( '7. Employment start date', wp_date( 'd/m/Y', strtotime( $p['start'] ) ) ) . '</table>';
	$h .= '<h4>Employee Statement</h4><table class="f">' . $row( '8. Do you have another job?', $f['q8'] ) . $row( '9. Do you receive payments from a State, workplace or private pension?', $f['q9'] ) . $row( '10. Since 6 April have you received payments from another job which has ended or any of the listed taxable benefits?', $yes( $f['q10'] ) ) . $row( 'Selected statement', 'Statement ' . $f['statement'] ) . '</table>' . wp_kses_post( $docs['starter']['guidance'] );
	$h .= '<h4>Student Loans</h4><table class="f">' . $row( '11. Do you have a student or postgraduate loan?', $f['q11'] ) . $row( '12. Do any of the listed statements apply?', $yes( $f['q12'] ) ) . $row( '13. Student loan(s)', $f['q13'] ? implode( ', ', $f['q13'] ) : 'Not applicable' ) . '</table>';
	$h .= '<h4>Declaration</h4>' . $sig( 'Signature', $p['sigs']['starter'], $f['full_name'], $day );
	// 3. Pension.
	$h .= '<div class="pb"></div>' . $head( $docs['pension']['title'] ) . '<table class="f">' . $row( 'Employee name', $f['full_name'] ) . $row( 'Job role', $p['role'] ) . $row( 'Email', $f['email'] ) . $row( 'Effective date', wp_date( 'd/m/Y', strtotime( $p['start'] ) ) ) . '</table>' . wp_kses_post( $docs['pension']['intro'] );
	$h .= '<div class="box"><b>' . ( 'in' === $f['pension'] ? 'Consented: I wish to opt in to the pension scheme' : 'I do not wish to join the pension scheme at this time' ) . '</b><br>' . esc_html( str_replace( '{{name}}', $f['full_name'], 'in' === $f['pension'] ? $docs['pension']['optin'] : $docs['pension']['optout'] ) ) . '</div>' . $sig( 'Signed', $p['sigs']['pension'], $f['full_name'], $day );
	// 4. Contract.
	$h .= '<div class="pb"></div>' . $head( 'HCC-Temps Ltd · ' . $docs['contract']['title'] ) . wp_kses_post( hcc_sign_fill( $docs['contract']['body'], $fill ) );
	$h .= '<h4>Signatures</h4><p><b>For and on behalf of HCC-Temps Ltd</b></p>' . $sig( 'Signed', $p['company']['sig'], $p['company']['name'] . ', ' . $p['company']['title'], wp_date( 'd/m/Y', (int) $p['company']['at'] ) ) . '<p><b>Employee</b></p>' . $sig( 'Signed', $p['sigs']['contract'], $f['full_name'], $day );
	$h .= '<h4>Confidentiality Clause</h4><p>' . esc_html( str_replace( '{{name}}', $f['full_name'], $docs['contract']['confidentiality'] ) ) . '</p>' . $sig( 'Signed', $p['sigs']['conf'], $f['full_name'], $day );
	// Signing record.
	$h .= '<div class="pb"></div>' . $head( 'Signing record' ) . '<p>This record shows how the documents in this pack were signed electronically through the HCC-Temps candidate portal.</p><table class="f">';
	$h .= $row( 'Candidate', $f['full_name'] . ' (' . hcc_meta( $id, 'ref' ) . ')' ) . $row( 'Candidate login email', hcc_meta( $id, 'email' ) ) . $row( 'Signed by the candidate', wp_date( 'j F Y, H:i:s T', (int) $p['signer']['at'] ) ) . $row( 'Candidate IP address', $p['signer']['ip'] ) . $row( 'Candidate device', $p['signer']['ua'] );
	$h .= $row( 'Signed for HCC-Temps Ltd by', $p['company']['name'] . ', ' . $p['company']['title'] ) . $row( 'Company signature', wp_date( 'j F Y, H:i:s T', (int) $p['company']['at'] ) ) . $row( 'Company IP address', $p['company']['ip'] ) . $row( 'Documents', 'GDPR declaration; starter checklist; pension options (' . ( 'in' === $f['pension'] ? 'opted in' : 'not now' ) . '); zero-hours contract and confidentiality clause' ) . $row( 'Document fingerprint (SHA-256)', '{{HASH}}' ) . '</table>';
	$h .= '<p>The candidate logged in with their password and a one-time code sent to their email, confirmed each document and drew each signature. The signed PDF is stored encrypted in the candidate\'s private folder.</p></body></html>';
	return $h;
}

function hcc_pack_pdf_url( $id, $download = true ) {
	return add_query_arg( array( 'action' => 'hcc_pack_pdf', 'a' => $id, 'dl' => $download ? 1 : 0, '_n' => wp_create_nonce( 'hcc_pack_pdf_' . $id ) ), admin_url( 'admin-post.php' ) );
}

add_action( 'admin_post_hcc_pack_pdf', function () {
	$id  = isset( $_GET['a'] ) ? absint( $_GET['a'] ) : 0;
	$own = $id && hcc_applicant_for_user( get_current_user_id() ) === $id;
	if ( ! $id || ! ( hcc_is_staff() || $own ) || ! isset( $_GET['_n'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_n'] ) ), 'hcc_pack_pdf_' . $id ) ) {
		wp_die( 'You do not have permission to view this file.', 403 );
	}
	$p    = hcc_pack( $id );
	$path = ! empty( $p['pdf'] ) ? hcc_private_dir( $id ) . '/' . basename( $p['pdf'] ) : '';
	if ( ! $path || ! is_readable( $path ) || ! hcc_crypto_can_open( $path ) ) {
		wp_die( 'File not found.', 404 );
	}
	if ( hcc_is_staff() ) {
		hcc_log( $id, ( empty( $_GET['dl'] ) ? 'Viewed ' : 'Downloaded ' ) . basename( $path ) );
	}
	nocache_headers();
	header( 'X-Content-Type-Options: nosniff' );
	header( 'Content-Type: application/pdf' );
	header( 'Content-Disposition: ' . ( empty( $_GET['dl'] ) ? 'inline' : 'attachment' ) . '; filename="' . basename( $path ) . '"' );
	header( 'Content-Length: ' . hcc_plain_size( $path ) );
	hcc_decrypt_file( $path, function ( $s ) {
		echo $s; // phpcs:ignore WordPress.Security.EscapeOutput
	} );
	exit;
} );

/* The signed PDF is part of the "download all" zip. */
add_filter( 'hcc_zip_extra', function ( $files, $id ) {
	$p = hcc_pack( $id );
	if ( ! empty( $p['pdf'] ) ) {
		$files[] = $p['pdf'];
	}
	return $files;
}, 10, 2 );

/* ---------- Staff portal → Signing ---------- */

add_filter( 'hcc_staff_views', function ( $v ) {
	$v[] = 'signing';
	return $v;
} );

add_filter( 'hcc_staff_nav', function ( $nav ) {
	$n = hcc_cached_count( 'sign', function () {
		$n = 0;
		foreach ( hcc_desk_list( 'in_progress' ) as $aid ) {
			$p  = hcc_pack( $aid );
			$n += isset( $p['status'] ) && 'sent' === $p['status'] ? 1 : 0;
		}
		return $n;
	} );
	$nav[] = array( 'signing', 'Signing', 'pen', hcc_staff_url( array( 'view' => 'signing' ) ), $n ? $n : '' );
	return $nav;
}, 40 );

add_action( 'hcc_staff_view_signing', function () {
	$rows = array( 'sent' => array(), 'signed' => array(), 'ready' => array() );
	foreach ( array( 'in_progress', 'completed' ) as $tab ) {
		foreach ( hcc_desk_list( $tab ) as $aid ) {
			$p = hcc_pack( $aid );
			$s = isset( $p['status'] ) ? $p['status'] : '';
			if ( 'sent' === $s || 'signed' === $s ) {
				$rows[ $s ][] = $aid;
			} elseif ( 'in_progress' === $tab && 100 === hcc_progress( $aid ) ) {
				$rows['ready'][] = $aid;
			}
		}
	}
	echo '<div class="s-head" style="margin-top:0"><div><h1>Signing</h1><p>Onboarding documents: ready to release, waiting for the candidate, and signed.</p></div></div>';
	foreach ( array( 'ready' => 'Ready to release (profile 100%)', 'sent' => 'Waiting for the candidate to sign', 'signed' => 'Signed' ) as $k => $t ) {
		echo '<div class="p-card" style="margin-bottom:18px"><div class="p-card-head"><h2>' . esc_html( $t ) . ' <span class="tray-n">' . count( $rows[ $k ] ) . '</span></h2></div>';
		if ( ! $rows[ $k ] ) {
			echo '<p class="hint">None.</p></div>';
			continue;
		}
		echo '<div class="d-table">';
		foreach ( $rows[ $k ] as $aid ) {
			$p = hcc_pack( $aid );
			echo '<div class="d-row"><span><b>' . esc_html( hcc_name( $aid ) ) . '</b><small>' . esc_html( hcc_meta( $aid, 'ref' ) . ' · ' . hcc_role_name( hcc_meta( $aid, 'role_key' ) ) ) . '</small></span><span data-l="Start">' . esc_html( ! empty( $p['start'] ) ? 'Starts ' . wp_date( 'j M Y', strtotime( $p['start'] ) ) : '' ) . '</span><span data-l="When">' . esc_html( 'signed' === $k ? 'Signed ' . hcc_ago( (int) $p['signed_at'] ) : ( 'sent' === $k ? 'Sent ' . hcc_ago( (int) $p['released_at'] ) : '' ) ) . '</span><span></span><span>' . ( 'signed' === $k ? '<a class="btn btn-sm btn-ghost" href="' . esc_url( hcc_pack_pdf_url( $aid ) ) . '">PDF</a> ' : '' ) . '<a class="btn btn-sm btn-navy" href="' . esc_url( hcc_staff_url( array( 'candidate' => $aid ) ) . '#declaration' ) . '">Open</a></span></div>';
		}
		echo '</div></div>';
	}
} );
