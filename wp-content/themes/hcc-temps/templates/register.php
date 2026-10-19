<?php
/**
 * Template Name: Register (candidates)
 */
defined( 'ABSPATH' ) || exit;
get_header();
$groups = array(
	'Healthcare'  => array( array( 'Registered Nurse', 'RGN / RMN', 'nurse.jpg' ), array( 'Healthcare Assistant', 'Care homes and hospitals', 'hero.jpg' ), array( 'Senior Care Assistant', 'Senior carer roles', 'carer.jpg' ) ),
	'Hospitality' => array( array( 'Chef', 'Cooks and chefs', 'chef.jpg' ), array( 'Kitchen Assistant', 'Prep and porters', 'kitchen.jpg' ), array( 'Catering / Hospitality', 'Events, cafes, front of house', 'barista.jpg' ), array( 'Housekeeping', 'Hotels and care homes', 'housekeeping.jpg' ), array( 'Cleaner', 'Offices, schools, homes', 'cleaner.jpg' ) ),
	'Admin'       => array( array( 'Receptionist / Admin', 'Front desk and office', 'reception.jpg' ) ),
);
$done = isset( $_GET['hcc_ok'] );
?>
<main id="main">
	<section class="page-hero">
		<div class="wrap">
			<div class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> / Register</div>
			<h1><?php the_title(); ?> with HCC-Temps</h1>
			<p class="lead">Not seen the right job yet? Register once and we'll offer you shifts that match your role and area.</p>
		</div>
	</section>

	<section class="section">
		<div class="wrap">
			<?php if ( ! $done ) : ?>
				<h2 style="font-size:clamp(26px,3vw,36px);color:var(--ink)">1. What do you do?</h2>
				<?php foreach ( $groups as $group => $roles ) : ?>
					<div class="role-group"><?php echo esc_html( $group ); ?></div>
					<div class="roles">
						<?php foreach ( $roles as $r ) : ?>
							<button type="button" class="role-pick" aria-pressed="false" data-role="<?php echo esc_attr( $r[0] ); ?>"><img src="<?php echo esc_url( hcc_img( $r[2] ) ); ?>" alt="" width="96" height="96" loading="lazy"><span><b><?php echo esc_html( $r[0] ); ?></b><small><?php echo esc_html( $r[1] ); ?></small></span></button>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
			<div style="max-width:880px;margin:<?php echo $done ? '0' : '56px'; ?> auto 0">
				<?php get_template_part( 'template-parts/apply-form', null, array( 'role_picker' => true ) ); ?>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
