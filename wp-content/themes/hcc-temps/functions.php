<?php
/**
 * HCC-Temps theme bootstrap.
 */

defined( 'ABSPATH' ) || exit;

define( 'HCC_VERSION', '2.8.0' );
define( 'HCC_DIR', get_template_directory() );
define( 'HCC_URI', get_template_directory_uri() );

require HCC_DIR . '/inc/setup.php';
require HCC_DIR . '/inc/requirements.php';
require HCC_DIR . '/inc/jobs.php';
require HCC_DIR . '/inc/applicants.php';
require HCC_DIR . '/inc/crypto.php';
require HCC_DIR . '/inc/app.php';
require HCC_DIR . '/inc/notify.php';
require HCC_DIR . '/inc/expiry.php';
require HCC_DIR . '/inc/payments.php';
require HCC_DIR . '/inc/signing.php';
require HCC_DIR . '/inc/candidates.php';
require HCC_DIR . '/inc/forms.php';
require HCC_DIR . '/inc/auth.php';
require HCC_DIR . '/inc/emails.php';
require HCC_DIR . '/inc/register.php';
require HCC_DIR . '/inc/staffdesk.php';
require HCC_DIR . '/inc/team.php';
require HCC_DIR . '/inc/importer.php';
require HCC_DIR . '/inc/seo.php';
require HCC_DIR . '/inc/blog.php';
require HCC_DIR . '/inc/seed.php';
