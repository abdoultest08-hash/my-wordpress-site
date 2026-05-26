<?php get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>

<?php
$job_id      = get_the_ID();
$urgent      = winserve_is_urgent( $job_id );
$location    = winserve_get_job_terms( $job_id, 'job_location' );
$dept        = winserve_get_job_terms( $job_id, 'job_department' );
$contract    = winserve_get_job_terms( $job_id, 'job_contract' );
$salary      = get_post_meta( $job_id, '_job_salary',      true );
$ref         = get_post_meta( $job_id, '_job_ref',         true );
$requirements_raw = get_post_meta( $job_id, '_job_requirements', true );
$requirements     = $requirements_raw ? array_filter( array_map( 'trim', explode( "\n", $requirements_raw ) ) ) : [];

// Application feedback
$apply_status = isset( $_GET['apply'] ) ? sanitize_text_field( wp_unslash( $_GET['apply'] ) ) : '';
$apply_errors = ( $apply_status === 'error' && isset( $_GET['msg'] ) )
    ? explode( '|', rawurldecode( sanitize_text_field( wp_unslash( $_GET['msg'] ) ) ) )
    : [];
?>

<section class="page-hero page-hero--job-single">
    <div class="container">
        <div class="page-hero__inner">
            <a class="back-link" href="<?php echo esc_url( get_post_type_archive_link( 'winserve_job' ) ); ?>">
                &larr; <?php esc_html_e( 'All Vacancies', 'mybrand' ); ?>
            </a>
            <div class="job-single-header">
                <div>
                    <?php if ( $dept ) : ?>
                        <span class="section-label"><?php echo esc_html( $dept ); ?></span>
                    <?php endif; ?>
                    <h1 class="page-hero__title">
                        <?php the_title(); ?>
                        <?php if ( $urgent ) : ?>
                            <span class="urgent-badge-inline"><?php esc_html_e( 'Urgently Hiring', 'mybrand' ); ?></span>
                        <?php endif; ?>
                    </h1>
                    <div class="job-single-meta">
                        <?php if ( $location ) : ?>
                            <span class="job-meta-item">📍 <?php echo esc_html( $location ); ?></span>
                        <?php endif; ?>
                        <?php if ( $contract ) : ?>
                            <span class="job-meta-item">📅 <?php echo esc_html( $contract ); ?></span>
                        <?php endif; ?>
                        <?php if ( $salary ) : ?>
                            <span class="job-meta-item">💷 <?php echo esc_html( $salary ); ?></span>
                        <?php endif; ?>
                        <?php if ( $ref ) : ?>
                            <span class="job-meta-item">🔖 Ref: <?php echo esc_html( $ref ); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <a class="btn btn-primary" href="#apply-form"><?php esc_html_e( 'Apply Now', 'mybrand' ); ?></a>
            </div>
        </div>
    </div>
</section>

<section class="section job-single-section">
    <div class="container job-single-layout">

        <!-- ── Left: Job details ── -->
        <div class="job-single-content">

            <div class="job-description entry-content">
                <?php the_content(); ?>
            </div>

            <?php if ( ! empty( $requirements ) ) : ?>
                <div class="job-requirements">
                    <h2><?php esc_html_e( 'Requirements / Essential Criteria', 'mybrand' ); ?></h2>
                    <ul class="requirements-list">
                        <?php foreach ( $requirements as $req ) : ?>
                            <li><?php echo esc_html( $req ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- ── Application form ── -->
            <div class="apply-form-wrap" id="apply-form">
                <h2><?php esc_html_e( 'Apply for This Role', 'mybrand' ); ?></h2>
                <p><?php esc_html_e( "Fill in the form below and we'll be in touch. No agencies please.", 'mybrand' ); ?></p>

                <?php if ( $apply_status === 'success' ) : ?>
                    <div class="form-notice form-notice--success" role="alert">
                        <strong><?php esc_html_e( 'Application received!', 'mybrand' ); ?></strong>
                        <?php esc_html_e( "Thank you for applying. We'll review your application and be in touch shortly.", 'mybrand' ); ?>
                    </div>

                <?php elseif ( $apply_status === 'error' && ! empty( $apply_errors ) ) : ?>
                    <div class="form-notice form-notice--error" role="alert">
                        <strong><?php esc_html_e( 'Please fix the following:', 'mybrand' ); ?></strong>
                        <ul>
                            <?php foreach ( $apply_errors as $err ) : ?>
                                <li><?php echo esc_html( $err ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ( $apply_status !== 'success' ) : ?>
                <form
                    class="apply-form"
                    method="post"
                    action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
                    enctype="multipart/form-data"
                    novalidate
                >
                    <?php wp_nonce_field( 'winserve_apply_action', 'winserve_apply_nonce' ); ?>
                    <input type="hidden" name="action"  value="winserve_apply">
                    <input type="hidden" name="job_id"  value="<?php echo esc_attr( $job_id ); ?>">

                    <div class="form-row form-row--2col">
                        <div class="form-group">
                            <label for="applicant_name"><?php esc_html_e( 'Full Name', 'mybrand' ); ?> <span aria-hidden="true">*</span></label>
                            <input type="text" id="applicant_name" name="applicant_name" required
                                placeholder="<?php esc_attr_e( 'Jane Smith', 'mybrand' ); ?>"
                                value="<?php echo esc_attr( $_POST['applicant_name'] ?? '' ); ?>">
                        </div>
                        <div class="form-group">
                            <label for="applicant_email"><?php esc_html_e( 'Email Address', 'mybrand' ); ?> <span aria-hidden="true">*</span></label>
                            <input type="email" id="applicant_email" name="applicant_email" required
                                placeholder="<?php esc_attr_e( 'jane@example.com', 'mybrand' ); ?>"
                                value="<?php echo esc_attr( $_POST['applicant_email'] ?? '' ); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="applicant_phone"><?php esc_html_e( 'Phone Number', 'mybrand' ); ?> <span aria-hidden="true">*</span></label>
                        <input type="tel" id="applicant_phone" name="applicant_phone" required
                            placeholder="<?php esc_attr_e( '07700 900000', 'mybrand' ); ?>"
                            value="<?php echo esc_attr( $_POST['applicant_phone'] ?? '' ); ?>">
                    </div>

                    <div class="form-group">
                        <label for="applicant_cv"><?php esc_html_e( 'Upload CV', 'mybrand' ); ?> <span aria-hidden="true">*</span></label>
                        <div class="file-upload-wrap">
                            <input type="file" id="applicant_cv" name="applicant_cv" accept=".pdf,.doc,.docx" required>
                            <label for="applicant_cv" class="file-upload-label">
                                <span class="file-upload-icon" aria-hidden="true">📎</span>
                                <span class="file-upload-text"><?php esc_html_e( 'Choose file (PDF, DOC, DOCX — max 5MB)', 'mybrand' ); ?></span>
                            </label>
                            <span class="file-name" id="file-name-display"></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="applicant_cover"><?php esc_html_e( 'Cover Letter / Why do you want to join Winserve?', 'mybrand' ); ?> <span aria-hidden="true">*</span></label>
                        <textarea id="applicant_cover" name="applicant_cover" rows="6" required
                            placeholder="<?php esc_attr_e( 'Tell us a bit about yourself and why this role appeals to you…', 'mybrand' ); ?>"><?php echo esc_textarea( $_POST['applicant_cover'] ?? '' ); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">
                        <?php esc_html_e( 'Submit Application', 'mybrand' ); ?>
                    </button>

                    <p class="form-privacy">
                        <?php printf(
                            wp_kses( __( 'By submitting this form you agree to our <a href="%s">Privacy Policy</a>.', 'mybrand' ), [ 'a' => [ 'href' => [] ] ] ),
                            esc_url( home_url( '/privacy-policy/' ) )
                        ); ?>
                    </p>
                </form>
                <?php endif; ?>
            </div>

        </div><!-- .job-single-content -->

        <!-- ── Right: Sidebar ── -->
        <aside class="job-single-sidebar">
            <div class="job-sidebar-card">
                <h3><?php esc_html_e( 'Job Summary', 'mybrand' ); ?></h3>
                <dl class="job-summary-list">
                    <?php if ( $location ) : ?>
                        <dt><?php esc_html_e( 'Location', 'mybrand' ); ?></dt>
                        <dd><?php echo esc_html( $location ); ?></dd>
                    <?php endif; ?>
                    <?php if ( $dept ) : ?>
                        <dt><?php esc_html_e( 'Department', 'mybrand' ); ?></dt>
                        <dd><?php echo esc_html( $dept ); ?></dd>
                    <?php endif; ?>
                    <?php if ( $contract ) : ?>
                        <dt><?php esc_html_e( 'Contract', 'mybrand' ); ?></dt>
                        <dd><?php echo esc_html( $contract ); ?></dd>
                    <?php endif; ?>
                    <?php if ( $salary ) : ?>
                        <dt><?php esc_html_e( 'Salary', 'mybrand' ); ?></dt>
                        <dd><?php echo esc_html( $salary ); ?></dd>
                    <?php endif; ?>
                    <?php if ( $ref ) : ?>
                        <dt><?php esc_html_e( 'Reference', 'mybrand' ); ?></dt>
                        <dd><?php echo esc_html( $ref ); ?></dd>
                    <?php endif; ?>
                </dl>
                <a class="btn btn-primary btn-full" href="#apply-form">
                    <?php esc_html_e( 'Apply for This Role', 'mybrand' ); ?>
                </a>
            </div>

            <div class="job-sidebar-card job-sidebar-card--cta">
                <h3><?php esc_html_e( "Can't see the right role?", 'mybrand' ); ?></h3>
                <p><?php esc_html_e( 'Send us a speculative CV and we\'ll keep you in mind for future opportunities.', 'mybrand' ); ?></p>
                <a class="btn btn-outline" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
                    <?php esc_html_e( 'Get in Touch', 'mybrand' ); ?>
                </a>
            </div>
        </aside>

    </div>
</section>

<?php endwhile; ?>
<?php get_footer(); ?>
