<?php
/**
 * Winserve Care - Guided Assistant (chat widget)
 *
 * Rule-based, no third-party services, no AI API calls.
 * Everything runs on this server, so no personal data leaves the site.
 * Submissions are emailed to enquiries@winservecare.co.uk via wp_mail().
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------
   Render the widget markup in the footer of every page
------------------------------------------------------------------ */
function winserve_chatbot_markup() {
    $privacy = esc_url( home_url( '/privacy-policy' ) );
    ?>
<div id="ws-chat" class="ws-chat" aria-live="polite">

  <button id="ws-chat-toggle" class="ws-chat-toggle" aria-label="Open the Winserve help assistant" aria-expanded="false">
    <span class="ws-chat-toggle-icon">
      <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 21l1.9-3.8A7.96 7.96 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
      </svg>
    </span>
    <span class="ws-chat-toggle-close">
      <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </span>
    <span class="ws-chat-badge" id="ws-chat-badge">1</span>
  </button>

  <div id="ws-chat-panel" class="ws-chat-panel" role="dialog" aria-label="Winserve help assistant" aria-modal="false">

    <div class="ws-chat-header">
      <div class="ws-chat-header-avatar">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
        </svg>
      </div>
      <div class="ws-chat-header-text">
        <strong>Winserve Care</strong>
        <span id="ws-chat-status">Here to help</span>
      </div>
      <button id="ws-chat-min" class="ws-chat-min" aria-label="Close the assistant">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <div id="ws-chat-body" class="ws-chat-body"></div>

    <div class="ws-chat-foot">
      <span>Not for emergencies. Call 999, or 111 for urgent medical advice.</span>
      <a href="<?php echo $privacy; ?>" target="_blank" rel="noopener">Privacy</a>
    </div>

  </div>
</div>

<script>
window.WS_CHAT = {
  ajax:    <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,
  nonce:   <?php echo wp_json_encode( wp_create_nonce( 'winserve_chat' ) ); ?>,
  privacy: <?php echo wp_json_encode( $privacy ); ?>,
  phone:   "0113 340 8777",
  email:   "enquiries@winservecare.co.uk"
};
</script>
    <?php
}
add_action( 'wp_footer', 'winserve_chatbot_markup' );

/* ------------------------------------------------------------------
   Handle the submission (logged out + logged in)
------------------------------------------------------------------ */
function winserve_chatbot_submit() {

    check_ajax_referer( 'winserve_chat', 'nonce' );

    $pathway = isset( $_POST['pathway'] ) ? sanitize_text_field( wp_unslash( $_POST['pathway'] ) ) : 'General';
    $answers = isset( $_POST['answers'] ) ? wp_unslash( $_POST['answers'] ) : '';
    $answers = json_decode( $answers, true );

    if ( ! is_array( $answers ) ) {
        wp_send_json_error( [ 'message' => 'Sorry, something went wrong. Please call us on 0113 340 8777.' ] );
    }

    // Honeypot: bots fill hidden fields, humans do not.
    if ( ! empty( $_POST['website'] ) ) {
        wp_send_json_success( [ 'message' => 'Thank you.' ] );
    }

    // Basic rate limit: one submission per IP per 30 seconds.
    $ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
    $key = 'ws_chat_' . md5( $ip );
    if ( get_transient( $key ) ) {
        wp_send_json_error( [ 'message' => 'You have just sent an enquiry. Please give us a moment.' ] );
    }
    set_transient( $key, 1, 30 );

    $clean = [];
    foreach ( $answers as $label => $value ) {
        $label = sanitize_text_field( $label );
        $value = is_scalar( $value ) ? sanitize_textarea_field( (string) $value ) : '';
        if ( '' !== $value ) {
            $clean[ $label ] = $value;
        }
    }

    if ( empty( $clean ) ) {
        wp_send_json_error( [ 'message' => 'Please complete the questions before sending.' ] );
    }

    // Reply-To if we captured an email address
    $reply_to = '';
    foreach ( $clean as $label => $value ) {
        if ( is_email( $value ) ) { $reply_to = $value; break; }
    }

    $subject_map = [
        'care'         => 'CARE ENQUIRY',
        'commissioner' => 'COMMISSIONER / CASE MANAGER',
        'job'          => 'JOB APPLICATION',
        'general'      => 'GENERAL ENQUIRY',
    ];
    $tag = isset( $subject_map[ $pathway ] ) ? $subject_map[ $pathway ] : 'WEBSITE ENQUIRY';

    $name    = isset( $clean['Name'] ) ? $clean['Name'] : 'Website visitor';
    $subject = sprintf( '[%s] %s - Winserve website assistant', $tag, $name );

    $lines   = [];
    $lines[] = 'New enquiry from the website assistant.';
    $lines[] = '';
    $lines[] = 'Type: ' . $tag;
    $lines[] = str_repeat( '-', 46 );
    foreach ( $clean as $label => $value ) {
        $lines[] = $label . ': ' . $value;
    }
    $lines[] = str_repeat( '-', 46 );
    $lines[] = 'Submitted: ' . current_time( 'j F Y, H:i' );
    $lines[] = 'Page: ' . ( isset( $_POST['page_url'] ) ? esc_url_raw( wp_unslash( $_POST['page_url'] ) ) : '' );

    // Job applications go to HR, everything else to the enquiries inbox
    $to      = ( 'job' === $pathway ) ? 'hr@winservecare.co.uk' : 'enquiries@winservecare.co.uk';
    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        'From: Winserve Website <enquiries@winservecare.co.uk>',
    ];
    if ( $reply_to ) {
        $headers[] = 'Reply-To: ' . $reply_to;
    }

    $sent = wp_mail( $to, $subject, implode( "\n", $lines ), $headers );

    // Acknowledgement to the enquirer
    if ( $sent && $reply_to ) {
        $ack = "Hello,\n\n"
             . "Thank you for getting in touch with Winserve Care Services.\n\n"
             . "We have received your enquiry and a member of our team will be in touch shortly. "
             . "If your enquiry is urgent, please call us on 0113 340 8777.\n\n"
             . "Kind regards,\n"
             . "The Winserve Care Team\n"
             . "0113 340 8777 | enquiries@winservecare.co.uk";
        wp_mail( $reply_to, 'We have received your enquiry - Winserve Care Services', $ack, [
            'Content-Type: text/plain; charset=UTF-8',
            'From: Winserve Care Services <enquiries@winservecare.co.uk>',
        ] );
    }

    if ( ! $sent ) {
        wp_send_json_error( [
            'message' => 'We could not send that just now. Please call us on 0113 340 8777 or email enquiries@winservecare.co.uk.',
        ] );
    }

    wp_send_json_success( [ 'message' => 'Thank you. Your enquiry is on its way to our team.' ] );
}
add_action( 'wp_ajax_winserve_chat',        'winserve_chatbot_submit' );
add_action( 'wp_ajax_nopriv_winserve_chat', 'winserve_chatbot_submit' );
