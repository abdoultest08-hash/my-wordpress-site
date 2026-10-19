<?php
/**
 * Job taxonomy pages (sector, job type, location, contract) use the Job Board layout.
 */
defined( 'ABSPATH' ) || exit;

if ( is_tax( array( 'job_sector', 'job_type', 'location', 'contract', 'job_status' ) ) ) {
	require HCC_DIR . '/archive-job_vacancies.php';
	return;
}
require HCC_DIR . '/index.php';
