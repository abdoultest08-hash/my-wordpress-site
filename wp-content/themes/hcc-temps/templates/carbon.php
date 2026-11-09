<?php
/**
 * Template Name: Carbon Reduction Plan
 * The client's Carbon Reduction Plan (PPN 06/21), published 30 June 2025, as on their previous website.
 */
defined( 'ABSPATH' ) || exit;
get_header();
$t = function ( $rows ) {
	$h = '<div class="legal-table" role="table"><div role="row" class="lt-head"><span role="columnheader">Emissions</span><span role="columnheader">Total (tCO2e)</span></div>';
	foreach ( $rows as $r ) {
		$h .= '<div role="row"' . ( ! empty( $r[2] ) ? ' class="lt-total"' : '' ) . '><span role="cell">' . $r[0] . '</span><span role="cell">' . $r[1] . '</span></div>';
	}
	return $h . '</div>';
};
?>
<main id="main">
	<section class="page-hero">
		<div class="wrap">
			<div class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> / <a href="<?php echo esc_url( hcc_page_url( 'about', '/about-us/' ) ); ?>">About us</a> / Carbon Reduction Plan</div>
			<h1>Carbon Reduction Plan</h1>
			<p class="lead">Our commitment to reaching Net Zero greenhouse gas emissions by 2045. Carbon Reduction Plan for UK PPN 06/21 · Supplier: HCC-Temps · Published 30 June 2025.</p>
		</div>
	</section>
	<section class="section">
		<div class="wrap legal carbon">
			<nav class="legal-toc" aria-label="On this page">
				<b>On this page</b>
				<a href="#commitment">Commitment to Net Zero</a>
				<a href="#baseline">Baseline emissions</a>
				<a href="#current">Current emissions</a>
				<a href="#targets">Reduction targets</a>
				<a href="#completed">Completed initiatives</a>
				<a href="#planned">Planned initiatives</a>
				<a href="#future">Future reporting</a>
				<a href="#declaration">Declaration and sign-off</a>
			</nav>
			<div class="legal-body">
				<div class="carbon-badge"><img src="<?php echo esc_url( hcc_img( 'accred/net-zero.svg' ) ); ?>" alt="" width="120" height="120"><p><b>Net Zero by 2045.</b> Total emissions down from 3.59 to 3.08 tCO2e since our 2022/23 baseline, with a target of around 90% less by 2045.</p></div>

				<h2 id="commitment">Commitment to achieving Net Zero</h2>
				<p>HCC-Temps is committed to achieving Net Zero greenhouse gas (GHG) emissions by 2045.</p>

				<h2 id="baseline">Baseline emissions footprint</h2>
				<p>Baseline emissions are a record of the greenhouse gases that have been produced in the past and were produced before the introduction of any strategies to reduce emissions. Baseline emissions are the reference point against which emission reductions can be measured.</p>
				<p><b>Baseline year: 1 April 2022 – 31 March 2023.</b> HCC-Temps' carbon footprint baseline covers Scope 1 emissions (direct emissions), Scope 2 emissions (indirect emissions) and the baseline of Scope 3 (indirect, value chain emissions) where available. The data was provided by HCC-Temps and calculated by 5D Net Zero in accordance with the UK Government Greenhouse Gas (GHG) Conversion Factors for Company Reporting, meeting the ISO 14065 standard. The report provided a baseline from which HCC-Temps can plan, adapt and focus its operations to negate and offset all greenhouse gas emissions. Figures are rounded to two decimal places.</p>
				<?php echo $t( array( array( 'Scope 1', 'None to report' ), array( 'Scope 2: UK electricity', '1.51' ), array( 'Scope 3', '2.08' ), array( '&nbsp;&nbsp;Upstream distribution', '0.00' ), array( '&nbsp;&nbsp;Waste disposal', '0.03' ), array( '&nbsp;&nbsp;Business travel', '0.13' ), array( '&nbsp;&nbsp;Commuter miles', '0.24' ), array( '&nbsp;&nbsp;Downstream distribution', 'None reported' ), array( '&nbsp;&nbsp;Homeworking', '1.69' ), array( '<b>Total emissions</b>', '<b>3.59</b>', true ) ) ); // phpcs:ignore ?>

				<h2 id="current">Current emissions footprint</h2>
				<p><b>Reporting year: 1 April 2024 – 31 March 2025.</b></p>
				<?php echo $t( array( array( 'Scope 1', 'None to report' ), array( 'Scope 2: UK electricity', '0.98' ), array( 'Scope 3', '2.10' ), array( '&nbsp;&nbsp;Upstream distribution', '0.02' ), array( '&nbsp;&nbsp;Waste disposal', '0.00' ), array( '&nbsp;&nbsp;Business travel', 'None reported' ), array( '&nbsp;&nbsp;Commuter miles', '0.10' ), array( '&nbsp;&nbsp;Downstream distribution', 'None reported' ), array( '&nbsp;&nbsp;Homeworking', '1.99' ), array( '<b>Total emissions</b>', '<b>3.08</b>', true ) ) ); // phpcs:ignore ?>

				<h2 id="targets">Emissions reduction targets</h2>
				<p>In order to achieve Net Zero, HCC-Temps has adopted the following near-term carbon reduction targets:</p>
				<ul>
					<li>Decrease Scope 1 and Scope 2 carbon emissions to <b>0.15 tCO2e by 2030</b>, a reduction of approximately 90% from the baseline year.</li>
					<li>Decrease Scope 1, 2 and 3 emissions to <b>0.39 tCO2e by 2045</b>, a reduction of approximately 90% from the baseline year.</li>
				</ul>
				<p>HCC-Temps expects its core operational carbon emissions to drop by 61.02% over the next five years, down to 1.40 tCO2e. The target emissions for Scopes 1, 2 and 3 are shown in the graph below, which also shows predicted versus actual emissions, where there has already been a 14.21% reduction (0.51 tCO2e).</p>
				<figure class="carbon-chart"><img src="<?php echo esc_url( hcc_img( 'carbon-chart.png' ) ); ?>" alt="Bar chart of HCC-Temps' projected Scope 2 and Scope 3 emissions falling from about 3.6 tCO2e in 2023 to about 0.2 tCO2e in 2045, with the actual total line falling from 3.59 in 2023 to 3.08 in 2025" width="871" height="486" loading="lazy"><figcaption>HCC-Temps projected and actual emissions since the 2022/23 baseline, to 2045.</figcaption></figure>

				<h2 id="completed">Completed carbon reduction initiatives</h2>
				<p>The following measures have been completed or implemented since the 2022/23 baseline. The reduction achieved equates to 0.51 tCO2e, a 14.21% reduction against the 2022/23 baseline, and the measures will be in effect when performing the contract:</p>
				<ul><li>Increased home working and flexibility by reducing the number of days required in the office.</li></ul>

				<h2 id="planned">Planned carbon reduction initiatives</h2>
				<p>For the initial year of targeted reduction, the following measures will be carried out. The reduction achieved equates to a minimum of 0.84 tCO2e, a 23.40% reduction against the 2023 baseline.</p>
				<h3>Scope 1</h3>
				<p>There are currently no reported emissions for Scope 1. HCC-Temps will work to keep it that way, especially with the move to a new office.</p>
				<h3>Scope 2: UK electricity</h3>
				<p>Scope 2 was 0.98 tCO2e because HCC-Temps is presumed to be on a non-renewable tariff. Switching to a renewable tariff would be the most efficient way of reducing these emissions; this is a medium-term initiative. For the first year, the focus is on reducing consumption and increasing efficiency:</p>
				<ul><li>Collect activity-based data rather than spend, to increase the accuracy of calculations, and take monthly meter kWh readings.</li><li>Encourage cultural and behavioural changes to reduce electricity use and wasted energy: energy efficiency training, signage reminders (for example turning out lights), energy-efficient kitchen practices, and turning applications off rather than leaving them on standby.</li><li>Install energy-efficient devices, switch to LED lighting and install smart meters in both offices.</li><li>Research switching to a renewable tariff, switch to a renewable energy provider, and investigate renewable alternatives such as solar panels.</li></ul>
				<h3>Scope 3</h3>
				<p>The baseline Scope 3 reviewed upstream distribution, waste disposal, business travel, commuter miles, downstream distribution and home working. Due to the nature of HCC-Temps' operations, no data was available for downstream distribution.</p>
				<ul>
					<li><b>Upstream distribution:</b> choose locally sourced products, consolidate shipments to reduce deliveries, purchase thoughtfully, minimise unnecessary purchases, and reuse items we already have.</li>
					<li><b>Waste management:</b> identify areas of high waste, run staff engagement on Reduce, Reuse, Recycle, buy second-hand technology, donate or sell old technology and usable resources, follow local waste management schemes, and use signage to encourage better habits.</li>
					<li><b>Business travel:</b> use public transport where possible and hold virtual meetings where possible.</li>
					<li><b>Commuter miles:</b> keep our "hire locally" values, encourage public transport and active travel, and introduce a cycle to work scheme.</li>
				</ul>

				<h2 id="future">Future reporting</h2>
				<p>HCC-Temps will look to expand Scope 3 reporting to include water usage, purchased goods and services, and fuel and energy-related activities.</p>

				<h2 id="declaration">Declaration and sign-off</h2>
				<p>This Carbon Reduction Plan has been completed in accordance with PPN 06/21 and associated guidance and reporting standards for Carbon Reduction Plans. Emissions have been reported and recorded in accordance with the published reporting standard for Carbon Reduction Plans and the GHG Reporting Protocol Corporate Standard, using the appropriate Government emission conversion factors for greenhouse gas company reporting. Scope 1 and Scope 2 emissions have been reported in accordance with SECR requirements, and the required subset of Scope 3 emissions has been reported in accordance with the published reporting standard for Carbon Reduction Plans and the Corporate Value Chain (Scope 3) Standard.</p>
				<p>This Carbon Reduction Plan has been reviewed and signed off by the board of directors (or equivalent management body) of HCC-Temps.</p>
				<p class="hint">References: <a href="https://ghgprotocol.org/corporate-standard" rel="noopener" target="_blank">GHG Protocol Corporate Standard</a> · <a href="https://www.gov.uk/government/collections/government-conversion-factors-for-company-reporting" rel="noopener" target="_blank">Government conversion factors for company reporting</a> · <a href="https://ghgprotocol.org/standards/scope-3-standard" rel="noopener" target="_blank">Scope 3 Standard</a></p>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
