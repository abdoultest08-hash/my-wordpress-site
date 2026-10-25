<?php
/**
 * What each role needs: documents and training, from HCC's "Mandatory Documents" list (Oct 2026).
 * Used by the Candidates page, job adverts, registration, the candidate portal and the Staff portal.
 *
 * Document keys are stable slugs: they appear in form fields, file links and saved records, so
 * never rename one once candidates have registered. Labels can change freely.
 */

defined( 'ABSPATH' ) || exit;

/** Documents every role needs, in checklist order (the order also numbers the files: 01, 02...). */
function hcc_base_docs() {
	return array(
		'cv'      => array( 'label' => 'CV', 'short' => 'CV', 'hint' => 'PDF or Word.' ),
		'id'      => array( 'label' => 'Valid passport or driving licence', 'short' => 'Passport or driving licence', 'hint' => 'The photo page, all four corners visible.' ),
		'rtw'     => array( 'label' => 'Right to work: share code and supporting documents', 'short' => 'Right to work', 'hint' => 'Non-British nationals, e.g. share code result, university term-time letter or certificate of sponsorship.' ),
		'address' => array( 'label' => '2 x proof of address, dated within the last 3 months', 'short' => 'Proof of address', 'hint' => 'Two different documents, such as a bank statement and a utility bill.', 'multiple' => true ),
		'ni'      => array( 'label' => 'Proof of National Insurance number', 'short' => 'Proof of NI', 'hint' => 'A letter from HMRC or DWP, or a payslip showing your NI number.' ),
		'photo'   => array( 'label' => 'Professional photograph', 'short' => 'Professional photo', 'hint' => 'Head and shoulders, plain background, no filters.' ),
		'dbs'     => array( 'label' => 'Enhanced DBS certificate on the Update Service', 'short' => 'DBS certificate', 'hint' => 'Upload both pages.' ),
		'refs'    => array( 'label' => 'Two references', 'short' => 'References', 'hint' => '', 'kind' => 'refs' ),
	);
}

/** Roles: name, short description for the Candidates page, image, extra documents, training. */
function hcc_roles() {
	$care = array( 'Basic Life Support', 'Manual Handling', 'Safeguarding Adults', 'Safeguarding Children', 'Infection Prevention & Control', 'Health & Safety', 'Food Safety', 'Fire Safety', 'First Aid', 'Equality & Diversity', 'Information Governance / GDPR', 'Mental Capacity Act & DoLS' );
	$care_mh = array_map( function ( $t ) { return 'Manual Handling' === $t ? 'Manual Handling (theory and practical)' : $t; }, $care );
	$food = function ( $level ) { return array( 'Food Safety & Hygiene (' . $level . ')', 'Health & Safety', 'First Aid', 'Allergen Awareness' ); };
	$roles = array(
		'nurse'        => array( 'name' => 'Registered Nurse', 'group' => 'Healthcare', 'sub' => 'RGN / RMN · NMC PIN', 'img' => 'hero-nurse.jpg',
			'extra' => array(
				'immunisation' => array( 'label' => 'Immunisation record (where applicable)', 'short' => 'Immunisation record', 'hint' => '', 'optional' => true ),
				'nmc_pin'      => array( 'label' => 'NMC PIN number', 'short' => 'NMC PIN', 'hint' => 'We check it on the NMC register.', 'kind' => 'pin' ),
				'nmc_soe'      => array( 'label' => 'NMC Statement of Entry', 'short' => 'NMC Statement of Entry', 'hint' => '' ),
				'nursing_deg'  => array( 'label' => 'Nursing diploma or degree', 'short' => 'Nursing diploma or degree', 'hint' => '' ),
			),
			'train' => array_merge( $care, array( 'Medication Administration', 'Autism Awareness & Learning Disabilities Awareness' ) ) ),
		'hca'          => array( 'name' => 'Healthcare Assistant', 'group' => 'Healthcare', 'sub' => 'Care homes and hospitals', 'img' => 'carer.jpg',
			'train' => array_merge( $care_mh, array( 'Autism Awareness & Learning Disabilities Awareness' ) ) ),
		'senior-carer' => array( 'name' => 'Senior Carer', 'group' => 'Healthcare', 'sub' => 'Senior care roles', 'img' => 'hero.jpg',
			'train' => array_merge( $care_mh, array( 'Medication Administration (theory and practical)', 'Autism Awareness & Learning Disabilities Awareness' ) ) ),
		'chef'         => array( 'name' => 'Chef', 'group' => 'Hospitality', 'sub' => 'Care homes, schools, hotels', 'img' => 'hero-chef.jpg', 'train' => $food( 'Level 2 minimum' ) ),
		'head-chef'    => array( 'name' => 'Head Chef', 'group' => 'Hospitality', 'sub' => 'Run the kitchen', 'img' => 'chef.jpg', 'train' => $food( 'Level 2 minimum, Level 3 preferred' ) ),
		'kitchen'      => array( 'name' => 'Kitchen Assistant', 'group' => 'Hospitality', 'sub' => 'Prep and porters', 'img' => 'kitchen.jpg', 'train' => $food( 'Level 1 minimum, Level 2 preferred' ) ),
		'catering'     => array( 'name' => 'Catering Assistant', 'group' => 'Hospitality', 'sub' => 'Events, cafes, front of house', 'img' => 'hospman.jpg', 'train' => $food( 'Level 1 minimum, Level 2 preferred' ) ),
		'barista'      => array( 'name' => 'Barista', 'group' => 'Hospitality', 'sub' => 'Coffee shops and cafes', 'img' => 'barista.jpg', 'train' => $food( 'Level 1 minimum, Level 2 preferred' ) ),
		'domestic'     => array( 'name' => 'Domestic Assistant', 'group' => 'Hospitality', 'sub' => 'Cleaning and housekeeping', 'img' => 'housekeeping.jpg', 'train' => array( 'Health & Safety', 'Infection Control (preferred, not mandatory)', 'COSHH (preferred, not mandatory)' ) ),
		'admin'        => array( 'name' => 'Administrative Assistant', 'group' => 'Admin', 'sub' => 'Reception and office', 'img' => 'reception.jpg', 'train_note' => 'Preferred, not mandatory', 'train' => array( 'GDPR / Data Protection', 'Information Governance', 'Equality & Diversity', 'Health & Safety', 'Fire Safety' ) ),
	);
	return apply_filters( 'hcc_roles', $roles );
}

function hcc_role( $key ) {
	$roles = hcc_roles();
	return isset( $roles[ $key ] ) ? $roles[ $key ] : null;
}

function hcc_role_name( $key ) {
	$r = hcc_role( $key );
	return $r ? $r['name'] : '';
}

/** Best-matching role key for free text such as a job title. */
function hcc_role_key_for( $text ) {
	$t = strtolower( (string) $text );
	$map = array( 'nurse' => '/nurse|rgn|rmn/', 'senior-carer' => '/senior/', 'hca' => '/care|health|hca/', 'head-chef' => '/head chef/', 'chef' => '/chef|cook/', 'kitchen' => '/kitchen|porter/', 'barista' => '/barista/', 'domestic' => '/clean|house|domestic/', 'admin' => '/recep|admin/' );
	foreach ( $map as $k => $re ) {
		if ( preg_match( $re, $t ) ) {
			return $k;
		}
	}
	return 'catering';
}

/** Documents for a role, in order: key => definition (with 'num' for file numbering). */
function hcc_role_docs( $role_key ) {
	$r    = hcc_role( $role_key );
	$docs = hcc_base_docs();
	if ( $r && ! empty( $r['extra'] ) ) {
		$docs = array_merge( $docs, $r['extra'] );
	}
	$n = 0;
	foreach ( $docs as $k => $d ) {
		$docs[ $k ] = wp_parse_args( $d, array( 'kind' => 'file', 'optional' => false, 'multiple' => false ) );
		$docs[ $k ]['num'] = str_pad( (string) ++$n, 2, '0', STR_PAD_LEFT );
	}
	return $docs;
}

/** Training certificates for a role: key => definition (keys "t-..."). */
function hcc_role_training( $role_key ) {
	$r   = hcc_role( $role_key );
	$out = array();
	$n   = 0;
	foreach ( $r ? $r['train'] : array() as $label ) {
		$out[ 't-' . sanitize_title( $label ) ] = array(
			'label'    => $label,
			'short'    => preg_replace( '/\s*\(.*\)$/', '', $label ),
			'hint'     => '',
			'kind'     => 'file',
			'optional' => (bool) preg_match( '/preferred|not mandatory/i', $label ) || ! empty( $r['train_note'] ),
			'multiple' => false,
			'num'      => 'T' . str_pad( (string) ++$n, 2, '0', STR_PAD_LEFT ),
		);
	}
	return $out;
}

/** Visa / immigration options for the right to work question. */
function hcc_visa_options() {
	return array( 'EU Settlement Scheme: settled status', 'EU Settlement Scheme: pre-settled status', 'Indefinite leave to remain', 'Health and Care Worker visa', 'Skilled Worker visa', 'Student visa', 'Graduate visa', 'Dependant visa', 'Family visa', 'Refugee or humanitarian protection', 'Other' );
}

function hcc_dbs_options() {
	return array(
		'update' => 'Yes, and it\'s on the Update Service',
		'cert'   => 'Yes, but it\'s not on the Update Service',
		'none'   => 'No, I don\'t have one',
	);
}

/** Reasons staff can give when asking for a document again. */
function hcc_amend_reasons() {
	return array( 'Photo is blurry', 'Corners are cut off', 'Document has expired', 'Name doesn\'t match your ID', 'Wrong document', 'Please upload both sides' );
}

function hcc_override_reasons() {
	return array( 'Seen the original in the office', 'Not required for this candidate or client', 'Checked by phone or online', 'Held on file from a previous registration', 'Other' );
}
