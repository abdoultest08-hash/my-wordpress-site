<?php
/**
 * HCC-Temps theme bootstrap.
 */

defined( 'ABSPATH' ) || exit;

define( 'HCC_VERSION', '1.1.0' );
define( 'HCC_DIR', get_template_directory() );
define( 'HCC_URI', get_template_directory_uri() );

require HCC_DIR . '/inc/setup.php';
require HCC_DIR . '/inc/jobs.php';
require HCC_DIR . '/inc/applicants.php';
require HCC_DIR . '/inc/forms.php';
require HCC_DIR . '/inc/seed.php';
require HCC_DIR . '/inc/portal.php';
