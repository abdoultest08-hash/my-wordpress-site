<?php
/**
 * Template Name: Modern Slavery Policy
 * The client's Modern Slavery Policy (April 2025), taken word for word from their signed PDF.
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main">
	<section class="page-hero">
		<div class="wrap">
			<div class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> / <a href="<?php echo esc_url( hcc_page_url( 'about', '/about-us/' ) ); ?>">About us</a> / Modern Slavery Policy</div>
			<h1>Modern Slavery Policy</h1>
			<p class="lead">Our zero-tolerance approach to modern slavery and human trafficking, in our business and our supply chains. HCC-Temps Ltd · Effective date: April 2025.</p>
		</div>
	</section>
	<section class="section">
		<div class="wrap legal carbon">
			<nav class="legal-toc" aria-label="On this page">
				<b>On this page</b>
				<a href="#introduction">Introduction</a>
				<a href="#scope">Scope</a>
				<a href="#definition">Definition of modern slavery</a>
				<a href="#commitment">Our commitment</a>
				<a href="#responsibilities">Responsibilities</a>
				<a href="#reporting">Reporting concerns</a>
				<a href="#compliance">Compliance and monitoring</a>
				<a href="#review">Review of policy</a>
				<a href="#conclusion">Conclusion and sign-off</a>
			</nav>
			<div class="legal-body">
				<div class="carbon-badge slavery-badge"><img src="<?php echo esc_url( hcc_img( 'accred/modern-slavery.svg' ) ); ?>" alt="" width="120" height="120"><p><b>Zero tolerance.</b> Every candidate's identity and right to work is checked, we never charge work-seekers for finding them work, and we expect the same standards from everyone in our supply chain.</p></div>

				<h2 id="introduction">1. Introduction</h2>
				<p>HCC-Temps Ltd is committed to preventing modern slavery and human trafficking in our business activities and supply chains. We take our responsibility seriously to ensure that our operations and the people we work with uphold the highest standards of ethics and integrity. This policy outlines our approach to identifying, preventing, and addressing the risk of modern slavery in all aspects of our business.</p>

				<h2 id="scope">2. Scope</h2>
				<p>This policy applies to all employees, workers, contractors, suppliers, and other business partners engaged in any activities with HCC-Temps Ltd within the UK and internationally. We expect our entire supply chain to comply with the principles outlined in this policy.</p>

				<h2 id="definition">3. Definition of modern slavery</h2>
				<p>Modern slavery is a crime and a violation of fundamental human rights. It includes various forms of exploitation, such as:</p>
				<ul>
					<li>Human trafficking</li>
					<li>Forced labour</li>
					<li>Debt bondage</li>
					<li>Child labour</li>
					<li>Servitude</li>
					<li>Forced marriage</li>
				</ul>

				<h2 id="commitment">4. Our commitment</h2>
				<p>HCC-Temps Ltd is committed to:</p>
				<ul>
					<li><b>Ensuring transparency:</b> we are dedicated to ensuring transparency in our business and approach to tackling modern slavery throughout our supply chains.</li>
					<li><b>Risk assessment:</b> we regularly assess and monitor our operations and supply chains to identify and mitigate the risks of modern slavery.</li>
					<li><b>Supplier due diligence:</b> we conduct rigorous due diligence on all suppliers and partners, ensuring they comply with our standards and legal obligations regarding modern slavery.</li>
					<li><b>Training:</b> we provide training to our employees and key stakeholders to raise awareness of modern slavery and to help them identify and report any potential risks.</li>
					<li><b>Reporting and whistleblowing:</b> we encourage all employees and stakeholders to report any concerns related to modern slavery, ensuring that they can do so confidentially and without fear of retaliation.</li>
					<li><b>Continuous improvement:</b> we are committed to continuously improving our practices to combat modern slavery. This includes reviewing and updating our policies and procedures as necessary.</li>
				</ul>

				<h2 id="responsibilities">5. Responsibilities</h2>
				<h3>Management</h3>
				<p>The senior management team is responsible for ensuring the implementation and effectiveness of this policy. They will also oversee compliance with relevant legislation, including the UK Modern Slavery Act 2015.</p>
				<h3>Employees</h3>
				<p>All employees have a responsibility to familiarise themselves with this policy and adhere to its principles. Employees must report any suspicions or evidence of modern slavery within our operations or supply chains.</p>
				<h3>Suppliers and partners</h3>
				<p>Our suppliers and business partners are expected to adopt the same high standards as HCC-Temps Ltd in their own business practices. We require all suppliers to sign our Supplier Code of Conduct, which includes specific commitments related to modern slavery.</p>

				<h2 id="reporting">6. Reporting concerns</h2>
				<p>If any employee, supplier, or stakeholder has concerns regarding modern slavery or human trafficking related to HCC-Temps Ltd, they are encouraged to report it immediately. Concerns can be reported through:</p>
				<ul>
					<li><b>Confidential whistleblowing hotline:</b> <a href="tel:03303213124">0330 321 3124</a></li>
					<li><b>Email:</b> <a href="mailto:david@hcctemps.co.uk">david@hcctemps.co.uk</a></li>
				</ul>
				<p>All reports will be treated seriously, investigated promptly, and appropriate actions will be taken.</p>
				<p class="hint">If someone is in immediate danger, call 999. You can also call the national Modern Slavery &amp; Exploitation Helpline on <a href="tel:08000121700">08000 121 700</a>, open 24 hours.</p>

				<h2 id="compliance">7. Compliance and monitoring</h2>
				<p>HCC-Temps Ltd will monitor compliance with this policy regularly through audits, assessments, and reviews. Any supplier found to be in violation of this policy will face remedial actions, which may include the termination of contracts.</p>

				<h2 id="review">8. Review of policy</h2>
				<p>This policy will be reviewed annually and updated as necessary to ensure its effectiveness. We will communicate any changes to all employees and relevant stakeholders.</p>

				<h2 id="conclusion">9. Conclusion</h2>
				<p>HCC-Temps Ltd is fully committed to playing its part in the global effort to eradicate modern slavery. We believe that ethical business practices and a commitment to human rights are fundamental to our success and the well-being of our society.</p>
				<div class="policy-sign">
					<p>Signed: <b>David Duyile-Knight</b>, Director<br>April 2025</p>
					<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( hcc_modern_slavery_url() ); ?>" target="_blank" rel="noopener">Download the signed policy (PDF)</a>
				</div>
				<p class="hint">HCC-Temps Ltd · Company no. 11486985 · C/O O'Meara Fitzmaurice &amp; Co, Brimstage Hall, Brimstage Road, Wirral, CH63 6JA</p>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
