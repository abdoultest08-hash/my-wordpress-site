<?php
defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------
   1. Register Custom Post Type: winserve_job
------------------------------------------------------------------ */
function winserve_register_jobs_cpt(): void {
    register_post_type( 'winserve_job', [
        'labels' => [
            'name'               => __( 'Vacancies',        'mybrand' ),
            'singular_name'      => __( 'Vacancy',          'mybrand' ),
            'add_new'            => __( 'Add Vacancy',       'mybrand' ),
            'add_new_item'       => __( 'Add New Vacancy',   'mybrand' ),
            'edit_item'          => __( 'Edit Vacancy',      'mybrand' ),
            'new_item'           => __( 'New Vacancy',       'mybrand' ),
            'view_item'          => __( 'View Vacancy',      'mybrand' ),
            'search_items'       => __( 'Search Vacancies',  'mybrand' ),
            'not_found'          => __( 'No vacancies found','mybrand' ),
            'menu_name'          => __( 'Vacancies',         'mybrand' ),
        ],
        'public'            => true,
        'has_archive'       => true,
        'rewrite'           => [ 'slug' => 'vacancies', 'with_front' => false ],
        'supports'          => [ 'title', 'editor', 'thumbnail' ],
        'menu_icon'         => 'dashicons-businessperson',
        'menu_position'     => 5,
        'show_in_rest'      => true,
    ] );
}
add_action( 'init', 'winserve_register_jobs_cpt' );

/* ------------------------------------------------------------------
   2. Register Taxonomies
------------------------------------------------------------------ */
function winserve_register_job_taxonomies(): void {
    $taxonomies = [
        'job_location'   => [ 'Location',    'Locations',   'job-location'   ],
        'job_department' => [ 'Department',  'Departments', 'job-department' ],
        'job_contract'   => [ 'Contract',    'Contracts',   'job-contract'   ],
    ];

    foreach ( $taxonomies as $tax => [ $singular, $plural, $slug ] ) {
        register_taxonomy( $tax, 'winserve_job', [
            'labels'       => [
                'name'          => $plural,
                'singular_name' => $singular,
                'all_items'     => "All {$plural}",
                'edit_item'     => "Edit {$singular}",
                'add_new_item'  => "Add New {$singular}",
            ],
            'hierarchical'  => true,
            'public'        => true,
            'rewrite'       => [ 'slug' => $slug ],
            'show_in_rest'  => true,
        ] );
    }
}
add_action( 'init', 'winserve_register_job_taxonomies' );

/* ------------------------------------------------------------------
   3. Meta Boxes
------------------------------------------------------------------ */
function winserve_job_meta_boxes(): void {
    add_meta_box(
        'winserve_job_details',
        __( 'Job Details', 'mybrand' ),
        'winserve_job_details_cb',
        'winserve_job',
        'side',
        'high'
    );
    add_meta_box(
        'winserve_job_requirements',
        __( 'Requirements / Essential Criteria', 'mybrand' ),
        'winserve_job_requirements_cb',
        'winserve_job',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'winserve_job_meta_boxes' );

function winserve_job_details_cb( WP_Post $post ): void {
    wp_nonce_field( 'winserve_job_save', 'winserve_job_nonce' );
    $salary  = get_post_meta( $post->ID, '_job_salary',  true );
    $urgent  = get_post_meta( $post->ID, '_job_urgent',  true );
    $ref     = get_post_meta( $post->ID, '_job_ref',     true );
    ?>
    <p>
        <label for="job_ref"><strong><?php esc_html_e( 'Job Reference', 'mybrand' ); ?></strong></label><br>
        <input type="text" id="job_ref" name="job_ref" value="<?php echo esc_attr( $ref ); ?>" style="width:100%">
    </p>
    <p>
        <label for="job_salary"><strong><?php esc_html_e( 'Salary / Pay Rate', 'mybrand' ); ?></strong></label><br>
        <input type="text" id="job_salary" name="job_salary" placeholder="e.g. £11.50/hr or £22,000 p/a" value="<?php echo esc_attr( $salary ); ?>" style="width:100%">
    </p>
    <p>
        <label>
            <input type="checkbox" name="job_urgent" value="1" <?php checked( $urgent, '1' ); ?>>
            <strong><?php esc_html_e( 'Mark as Urgently Hiring', 'mybrand' ); ?></strong>
        </label>
    </p>
    <?php
}

function winserve_job_requirements_cb( WP_Post $post ): void {
    $requirements = get_post_meta( $post->ID, '_job_requirements', true );
    ?>
    <p style="color:#666;font-size:12px;"><?php esc_html_e( 'List each requirement on a new line (e.g. Enhanced DBS required, Full driving licence, 1 year care experience)', 'mybrand' ); ?></p>
    <textarea id="job_requirements" name="job_requirements" rows="6" style="width:100%"><?php echo esc_textarea( $requirements ); ?></textarea>
    <?php
}

function winserve_job_save_meta( int $post_id ): void {
    if (
        ! isset( $_POST['winserve_job_nonce'] ) ||
        ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['winserve_job_nonce'] ) ), 'winserve_job_save' ) ||
        defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ||
        ! current_user_can( 'edit_post', $post_id )
    ) return;

    $fields = [
        '_job_ref'          => 'job_ref',
        '_job_salary'       => 'job_salary',
        '_job_requirements' => 'job_requirements',
    ];

    foreach ( $fields as $meta_key => $post_key ) {
        if ( isset( $_POST[ $post_key ] ) ) {
            update_post_meta( $post_id, $meta_key, sanitize_textarea_field( wp_unslash( $_POST[ $post_key ] ) ) );
        }
    }

    update_post_meta( $post_id, '_job_urgent', isset( $_POST['job_urgent'] ) ? '1' : '0' );
}
add_action( 'save_post_winserve_job', 'winserve_job_save_meta' );

/* ------------------------------------------------------------------
   4. Application form handler (admin-post)
------------------------------------------------------------------ */
add_action( 'admin_post_nopriv_winserve_apply', 'winserve_handle_application' );
add_action( 'admin_post_winserve_apply',        'winserve_handle_application' );

function winserve_handle_application(): void {
    // Verify nonce
    if (
        ! isset( $_POST['winserve_apply_nonce'] ) ||
        ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['winserve_apply_nonce'] ) ), 'winserve_apply_action' )
    ) {
        wp_die( esc_html__( 'Security check failed.', 'mybrand' ) );
    }

    $job_id   = isset( $_POST['job_id'] ) ? absint( $_POST['job_id'] ) : 0;
    $job_url  = $job_id ? get_permalink( $job_id ) : home_url( '/vacancies/' );
    $job_title = $job_id ? get_the_title( $job_id ) : 'Unknown Role';

    // Sanitise fields
    $name    = isset( $_POST['applicant_name'] )    ? sanitize_text_field( wp_unslash( $_POST['applicant_name'] ) )    : '';
    $email   = isset( $_POST['applicant_email'] )   ? sanitize_email( wp_unslash( $_POST['applicant_email'] ) )        : '';
    $phone   = isset( $_POST['applicant_phone'] )   ? sanitize_text_field( wp_unslash( $_POST['applicant_phone'] ) )   : '';
    $cover   = isset( $_POST['applicant_cover'] )   ? sanitize_textarea_field( wp_unslash( $_POST['applicant_cover'] ) ) : '';

    // Basic validation
    $errors = [];
    if ( empty( $name ) )                   $errors[] = 'Name is required.';
    if ( ! is_email( $email ) )             $errors[] = 'A valid email address is required.';
    if ( empty( $phone ) )                  $errors[] = 'Phone number is required.';
    if ( empty( $cover ) )                  $errors[] = 'Cover letter / message is required.';

    // CV upload
    $cv_path = '';
    $cv_name = '';
    if ( ! empty( $_FILES['applicant_cv']['name'] ) ) {
        $file     = $_FILES['applicant_cv'];
        $allowed  = [ 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ];
        $ext_ok   = in_array( strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) ), [ 'pdf', 'doc', 'docx' ], true );
        $type_ok  = in_array( $file['type'], $allowed, true );
        $size_ok  = $file['size'] <= 5 * 1024 * 1024; // 5 MB

        if ( ! $ext_ok || ! $type_ok ) {
            $errors[] = 'CV must be a PDF, DOC, or DOCX file.';
        } elseif ( ! $size_ok ) {
            $errors[] = 'CV file size must be under 5 MB.';
        } else {
            $upload = wp_upload_bits( $file['name'], null, file_get_contents( $file['tmp_name'] ) );
            if ( empty( $upload['error'] ) ) {
                $cv_path = $upload['file'];
                $cv_name = $file['name'];
            }
        }
    } else {
        $errors[] = 'Please attach your CV.';
    }

    if ( ! empty( $errors ) ) {
        $err_string = implode( '|', $errors );
        wp_safe_redirect( add_query_arg( [ 'apply' => 'error', 'msg' => rawurlencode( $err_string ) ], $job_url ) );
        exit;
    }

    // Send email to admin
    $to      = get_option( 'admin_email' );
    $subject = sprintf( '[Winserve Application] %s — %s', $job_title, $name );
    $body    = sprintf(
        "New job application received.\n\nRole: %s\nName: %s\nEmail: %s\nPhone: %s\n\nCover Letter:\n%s\n\nCV: %s",
        $job_title, $name, $email, $phone, $cover, $cv_name ?: 'Not attached'
    );
    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        sprintf( 'Reply-To: %s <%s>', $name, $email ),
    ];
    $attachments = $cv_path ? [ $cv_path ] : [];

    wp_mail( $to, $subject, $body, $headers, $attachments );

    // Clean up uploaded file after sending
    if ( $cv_path && file_exists( $cv_path ) ) {
        wp_delete_file( $cv_path );
    }

    wp_safe_redirect( add_query_arg( [ 'apply' => 'success' ], $job_url ) );
    exit;
}

/* ------------------------------------------------------------------
   5. Helper: get job meta nicely
------------------------------------------------------------------ */
function winserve_get_job_terms( int $post_id, string $taxonomy ): string {
    $terms = get_the_terms( $post_id, $taxonomy );
    if ( ! $terms || is_wp_error( $terms ) ) return '';
    return implode( ', ', wp_list_pluck( $terms, 'name' ) );
}

function winserve_is_urgent( int $post_id ): bool {
    return get_post_meta( $post_id, '_job_urgent', true ) === '1';
}
