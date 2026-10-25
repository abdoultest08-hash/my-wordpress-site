<?php defined( 'ABSPATH' ) || exit; ?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main">Skip to content</a>

<div class="topbar">
	<div class="wrap">
		<span class="slogan"><span class="figs" aria-hidden="true"><i style="background:var(--cocoa)"></i><i style="background:var(--navy)"></i><i style="background:var(--white)"></i><i style="background:var(--coral)"></i><i style="background:var(--sun)"></i></span><?php echo esc_html( hcc_opt( 'slogan' ) ); ?> <em>· <?php echo esc_html( hcc_opt( 'since' ) ); ?></em></span>
		<span class="quick">
			<?php if ( is_user_logged_in() && ( hcc_is_staff() || hcc_applicant_for_user( get_current_user_id() ) ) ) : ?>
				<a href="<?php echo esc_url( hcc_is_staff() ? hcc_staff_url() : hcc_portal_url() ); ?>" class="hide-sm"><?php echo hcc_is_staff() ? 'Staff portal' : 'My portal'; ?></a>
				<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="hide-sm">Log out</a>
			<?php else : ?>
				<a href="<?php echo esc_url( hcc_login_url() ); ?>" class="hide-sm">Candidate login</a>
			<?php endif; ?>
			<a href="<?php echo esc_attr( hcc_tel( hcc_opt( 'phone' ) ) ); ?>"><?php echo hcc_icon( 'phone', 15 ); echo esc_html( hcc_opt( 'phone' ) ); ?></a>
		</span>
	</div>
</div>

<header class="site-header">
	<div class="wrap">
		<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> home"><img src="<?php echo esc_url( hcc_logo_url() ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="266" height="144"></a>
		<nav class="nav" aria-label="Main"><?php hcc_menu(); ?></nav>
		<div class="header-cta">
			<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( hcc_page_url( 'clients', '/for-clients/' ) . '#request' ); ?>">Request cover</a>
			<a class="btn btn-aqua btn-sm" href="<?php echo esc_url( hcc_page_url( 'register', '/register/' ) ); ?>">Register</a>
		</div>
		<button class="menu-toggle" id="menuToggle" aria-expanded="false" aria-controls="mobileNav" aria-label="Open menu"><span></span><span></span><span></span></button>
	</div>
	<nav class="mobile-nav" id="mobileNav" aria-label="Mobile" hidden>
		<?php hcc_menu( true ); ?>
		<a class="btn btn-aqua" href="<?php echo esc_url( hcc_page_url( 'register', '/register/' ) ); ?>">Register as a candidate</a>
		<a class="btn btn-ghost" href="<?php echo esc_url( hcc_page_url( 'clients', '/for-clients/' ) . '#request' ); ?>">Request cover</a>
	</nav>
</header>
