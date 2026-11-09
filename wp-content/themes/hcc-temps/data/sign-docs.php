<?php
/**
 * Wording of the onboarding pack (from the client's templates, November 2026; still being finalised).
 * Change the text here; fields and signatures are handled by inc/signing.php.
 * {{name}}, {{role}}, {{start}}, {{email}} are filled in when the pack is shown and in the PDF.
 */

defined( 'ABSPATH' ) || exit;

return array(
	'company' => array(
		'name'    => 'HCC-Temps Ltd',
		'address' => '60 Portland Road, Norwood Junction, SE25 4UN',
	),

	'gdpr' => array(
		'title' => 'Employee & Worker Privacy Notice and GDPR Declaration',
		'intro' => '<p>HCC-Temps Ltd ("we", "us", "the Company") is committed to protecting the privacy and security of your personal data. This Privacy Notice explains how we collect, use, store, share, and protect your personal information as an employee or temporary worker.</p><p>At the end of this document, you will sign to acknowledge that you have received and understood this Notice.</p>',
		'body'  => '
<h4>1. Who We Are (Data Controller)</h4>
<p><b>HCC-Temps Ltd</b><br>60 Portland Road, Norwood Junction, SE25 4UN<br>We are the Data Controller, meaning we determine how your personal data is processed.</p>
<h4>2. Personal Data We Collect</h4>
<p><b>Identity &amp; Contact Information:</b> name, address, telephone, email; date of birth; emergency contact details.</p>
<p><b>Right to Work &amp; Compliance:</b> passport, visa, share code; proof of address; National Insurance number; DBS certificate &amp; Update Service status; mandatory training and qualifications.</p>
<p><b>Employment &amp; Assignment Data:</b> application and registration details; references; assignment locations and duties; timesheets and attendance data; performance or incident reports.</p>
<p><b>Payroll &amp; Financial Information:</b> bank account details; tax codes; pension details (where opted in).</p>
<p><b>Special Category Data:</b> health information related to fitness to work; criminal record information (DBS).</p>
<h4>3. Why We Process Your Data</h4>
<p>We process your data to: verify identity and right to work; determine suitability for assignments; place you on assignments with clients; process payroll and pensions; maintain safeguarding and regulatory compliance (CQC, NHS, Local Authority); maintain accurate personnel and training records; manage performance, incidents, and complaints; meet legal, tax, and employment obligations.</p>
<h4>4. Lawful Bases for Processing</h4>
<p><b>Performance of a Contract:</b> to employ you, assign shifts, and pay you.<br><b>Legal Obligation:</b> right-to-work checks, payroll, safeguarding, health and safety.<br><b>Legitimate Interests:</b> managing workers, sharing required information with clients, ensuring compliance, and fulfilling assignment requirements.<br><b>Substantial Public Interest:</b> safeguarding, DBS checks, and suitability assessments for vulnerable groups.<br><b>Consent:</b> only used where explicit consent is legally required (e.g., accessing DBS Update Service). You may withdraw consent at any time.</p>
<h4>5. Who We Share Your Data With</h4>
<p>We may share your data with: clients where you undertake Assignments; payroll processing provider; pension provider; DBS service; training providers; regulators (CQC, Local Authority, HMRC, Home Office); external auditors; IT and system providers supporting secure data hosting. We do not sell your data.</p>
<h4>6. International Transfers</h4>
<p>If any data is stored or processed outside the UK, we ensure appropriate safeguards such as adequacy decisions, Standard Contractual Clauses, and approved security frameworks.</p>
<h4>7. Data Retention</h4>
<table class="ret"><tr><th>Data Type</th><th>Retention Period</th></tr><tr><td>Personnel file</td><td>6 years after last shift</td></tr><tr><td>Payroll records</td><td>6 years (HMRC obligation)</td></tr><tr><td>Right to work documents</td><td>2 years after employment ends</td></tr><tr><td>DBS information</td><td>2 years after employment ends</td></tr><tr><td>Assignment &amp; timesheet records</td><td>6 years</td></tr><tr><td>Training records</td><td>Duration of employment + 2 years</td></tr></table>
<p>Data is securely deleted after the retention period.</p>
<h4>8. Your Rights Under UK GDPR</h4>
<p>You have the right to: access your personal data; request corrections; request deletion (where applicable); restrict or object to certain processing; data portability; withdraw consent (where used); complain to the ICO (www.ico.org.uk).</p>
<h4>9. Data Accuracy</h4>
<p>You must keep your personal data accurate and promptly inform us of any changes.</p>
<h4>10. Security &amp; Confidentiality</h4>
<p>You must handle client and company information securely and follow all confidentiality and data protection policies.</p>
<h4>11. Audit &amp; Compliance Checks</h4>
<p>You acknowledge that HCC-Temps Ltd may conduct audits to verify right to work, DBS status, training and qualifications, and compliance with client and regulatory requirements. Relevant data may be shared with clients, regulators, or auditors where required.</p>
<h4>12. Contact for Data Protection Queries</h4>
<p>HCC-Temps Ltd - Compliance Team · Tel: 0330 321 3124 · Email: Staffing@hcctemps.co.uk</p>',
		'confirm' => array(
			'I have received, read, and understood the HCC-Temps Ltd Employee & Worker Privacy Notice.',
			'I understand how my personal data will be collected, used, stored, shared, and protected.',
			'I understand my rights under UK GDPR and how to exercise them.',
			'I agree to comply with all Company data protection and confidentiality policies.',
		),
	),

	'starter' => array(
		'title' => 'Starter Checklist',
		'intro' => '<p><b>Instructions for employer:</b> use this starter checklist to gather information about your new employee if they do not have a P45. You can also use this form if they have a student loan (whether or not they have a P45). Use the information to help fill in your first Full Payment Submission (FPS) for this employee. If you have already submitted your first FPS, keep using the tax code in that FPS until HMRC sends you a new tax code. If the employee gives you their P45 after the first FPS submission, use the tax code shown in parts 2 and 3 of the P45. You must keep the information recorded on the starter checklist for the current and next 3 tax years. This form is for your use only.</p>',
		'guidance' => '<p><b>Employer guidance:</b> for information on how to work out your new employee\'s tax code, go to www.gov.uk/new-employee-tax-code. Statement A: use the current personal allowance. Statement B: use the current personal allowance on a "week 1/month 1" basis. Statement C: use tax code BR. For student loans, see www.gov.uk/guidance/special-rules-for-student-loans.</p>',
		'q12'   => array( 'You\'re still studying on a course that your student loan relates to', 'You completed or left your course after the start of the current tax year, which started on 6 April', 'You\'ve already repaid your loan in full', 'You\'re paying the Student Loans Company by Direct Debit from your bank to manage your end of loan repayments' ),
	),

	'pension' => array(
		'title' => 'Workplace Pension Options',
		'intro' => '<p>Pension contributions are based on <b>qualifying earnings</b>, which is the portion of weekly pay <b>between £120 and £967.12</b>. Contributions will be deducted from pay each week.</p><p>As part of the workplace pension scheme:<br><b>Employer contribution (us):</b> 3% of qualifying earnings<br><b>Employee contribution (you):</b> 5% of qualifying earnings</p>',
		'optin'  => 'I, {{name}}, would like to join the workplace pension scheme immediately. I understand that contributions will be made as explained above.',
		'optout' => 'I do not wish to join the workplace pension scheme at this time. I understand that I can ask to opt in at any point during my employment, and that I may be automatically enrolled if I meet the statutory criteria at an assessment point (keeping the right to opt out).',
	),

	'contract' => array(
		'title' => 'Zero-Hours Employment Contract (Temporary Agency Workers)',
		'body'  => '
<h4>1. Parties</h4>
<p>This Employment Contract is made between <b>HCC-Temps Ltd</b>, 60 Portland Road, Norwood Junction, SE25 4UN ("the Employer") and <b>{{name}}</b> ("the Employee").<br>Your employment begins on: <b>{{start}}</b></p>
<h4>2. Employment Status</h4>
<p>2.1 The Company employs you as a zero-hours employee.<br>2.2 The Employer is not obliged to offer you work, and you are not obliged to accept work.<br>2.3 You will undertake temporary assignments ("Assignments") for clients of the Employer.</p>
<h4>3. Continuous Employment</h4>
<p>3.1 Your period of continuous employment begins on the date stated above.<br>3.2 No previous employment counts as continuous unless confirmed in writing by the Employer.</p>
<h4>4. Role and Assignments</h4>
<p>4.1 You will carry out the role for which you have been recruited, based on your qualifications and experience: <b>{{role}}</b>.<br>4.2 Before each Assignment, you will be informed of: the client name and location; the duties required; the expected working hours; your hourly rate.<br>4.3 Once you accept an Assignment, you are expected to attend as agreed.<br>4.4 The Employer or the client may amend or withdraw an Assignment at any time.</p>
<h4>5. Place of Work</h4>
<p>5.1 You will work at various client locations as instructed.<br>5.2 You are not required to work exclusively for the Employer, but you must not accept work that negatively affects your availability or performance on Assignments.</p>
<h4>6. Hours of Work</h4>
<p>6.1 This is a zero-hours contract, and the Employer does not guarantee any minimum number of hours.<br>6.2 You will work only the hours agreed for each Assignment. Working hours may vary depending on client requirements and your availability.<br>6.3 Breaks are unpaid. You must take rest breaks in accordance with the client\'s procedures, which comply with the Working Time Regulations 1998.</p>
<h4>7. Pay</h4>
<p>7.1 Your hourly rate will fall within the following pay ranges, depending on role and client requirements: Healthcare Assistants / Domestic / Admin: £12.21–£13.00 per hour; Commis Chefs: £13.00–£13.50 per hour; Chefs de Partie: £14.00–£14.80 per hour; Sous Chefs: £16.00–£16.50 per hour; Head Chefs: £20.00–£21.00 per hour; Registered Nurses: £26.00–£30.00 per hour.<br>7.2 Pay is made weekly in arrears on Wednesdays via PAYE.<br>7.3 Payment is based on submitted and approved timesheets &amp; hours.<br>7.4 The Employer may make lawful deductions, including PAYE, NI, pension contributions, and recovery of any overpayments.</p>
<h4>8. Holiday Entitlement</h4>
<p>8.1 You accrue holiday at the rate of 12.07% of hours worked.<br>8.2 Holiday pay is calculated in line with statutory requirements.<br>8.3 You must request holiday in advance.<br>8.4 Holiday cannot be taken during an active Assignment without prior agreement.</p>
<h4>9. Pension</h4>
<p>9.1 Upon joining the Employer, you will be asked to complete a Pension Options Form indicating whether you wish to join the Employer\'s workplace pension scheme.<br>9.2 If you choose to opt in, you will be enrolled into the pension scheme, and statutory employee and employer contributions will apply.<br>9.3 If you choose not to join at that time, you may request to opt in at any point during your employment.<br>9.4 The Employer is legally required to assess your eligibility for automatic enrolment at regular intervals. If you meet the statutory criteria at any assessment point, you may be automatically enrolled. You will retain the right to opt out.</p>
<h4>10. Sickness Absence</h4>
<p>10.1 If you are unwell and cannot attend an Assignment, you must notify the Employer immediately.<br>10.2 You may qualify for Statutory Sick Pay (SSP) if you meet statutory eligibility requirements.<br>10.3 The Employer does not provide contractual sick pay.</p>
<h4>11. Maternity, Paternity and Parental Rights</h4>
<p>11.1 You are entitled to statutory family rights if you meet the qualifying conditions.<br>11.2 The Employer does not offer contractual enhancements beyond statutory entitlements.</p>
<h4>12. Training and Compliance</h4>
<p>12.1 You must maintain all mandatory training relevant to your role.<br>12.2 You must comply with all Employer and client policies, including: safeguarding; health and safety; confidentiality; data protection; medication handling (where applicable).<br>12.3 A valid DBS certificate must be maintained where required.</p>
<h4>13. Audit and Compliance Checks</h4>
<p>13.1 The Employee consents to the Employer conducting audits, compliance checks, and reviews of their personnel file, training records, right to work documents, DBS status, performance records, and any other information necessary to meet regulatory, contractual, or safeguarding requirements.<br>13.2 The Employee agrees that the Employer may share relevant compliance information with clients, regulators, or external auditors where required for due diligence, safeguarding, quality assurance, or contractual purposes.<br>13.3 The Employee agrees to cooperate fully with any audit process, including providing updated documents, completing refresher training, or supplying information reasonably required to demonstrate ongoing compliance.<br>13.4 Failure to cooperate with mandatory audits or compliance checks may result in removal from Assignments or disciplinary action.</p>
<h4>14. Confidentiality</h4>
<p>14.1 You must keep all Employer and client information confidential during your employment and after it ends.<br>14.2 Breach of confidentiality may result in disciplinary action or termination.</p>
<h4>15. Data Protection</h4>
<p>15.1 Your personal data will be processed in accordance with UK GDPR.<br>15.2 The Employer may share relevant information with clients for Assignment purposes and with external bodies where legally required.</p>
<h4>16. Company and Client Property</h4>
<p>16.1 You must return all property belonging to the Employer or client immediately when requested.<br>16.2 Failure to return property may result in deductions for the value of unreturned items, where lawful.</p>
<h4>17. Disciplinary and Grievance Procedures</h4>
<p>17.1 The Employer\'s disciplinary and grievance policies apply to your employment.<br>17.2 These procedures are not contractual and may be amended from time to time.</p>
<h4>18. Termination of Employment</h4>
<p>18.1 Either party may end this employment by giving one week\'s notice.<br>18.2 The Employer may terminate employment without notice in cases of gross misconduct.<br>18.3 Assignment cancellations do not constitute termination of employment.</p>
<h4>19. Governing Law</h4>
<p>This contract is governed by the laws of England and Wales.</p>',
		'confidentiality' => 'I, {{name}} ("the Employee"), agree to keep confidential during the term of this contract and for two years after its termination all direct or indirect information I have had access to during my employment with HCC-Temps Ltd.',
	),
);
