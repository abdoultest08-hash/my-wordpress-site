<?php
/**
 * One-time blog post setup script for Winserve Care.
 * Access via: https://yoursite.com/wp-content/themes/winserve-care-theme/setup-posts.php
 * This file deletes itself after running successfully.
 */
define('ABSPATH', '');
$wp_load = __DIR__ . '/../../../wp-load.php';
if (!file_exists($wp_load)) { die('wp-load.php not found.'); }
require_once($wp_load);

if (!current_user_can('manage_options')) {
    wp_die('You must be logged in as an admin to run this script.');
}

$posts = [
  [
    'title' => 'Why Your Care Provider\'s CQC Rating Matters More Than You Think',
    'date'  => '2026-04-15 09:00:00',
    'content' => '<p>When it comes to choosing a home care provider, the Care Quality Commission (CQC) rating is one of the most reliable and objective measures of quality available. Yet many families overlook it entirely — or don\'t fully understand what it means.</p>

<h2>What Is the CQC?</h2>
<p>The Care Quality Commission is the independent regulator of health and adult social care in England. It inspects and rates every registered care provider across five key lines of enquiry: Is the service <strong>Safe</strong>? Is it <strong>Effective</strong>? Is it <strong>Caring</strong>? Is it <strong>Responsive</strong> to people\'s needs? And is it <strong>Well-led</strong>?</p>
<p>Each area receives a rating of Outstanding, Good, Requires Improvement, or Inadequate — and a provider receives an overall rating based on these five domains.</p>

<h2>What Does a \'Good\' Rating Actually Mean?</h2>
<p>A CQC Good rating means inspectors have observed first-hand evidence that the care being delivered meets a high standard across all five areas. It\'s not a rubber stamp — CQC inspectors speak to service users, their families, and staff. They review care plans, medication records, incident logs, and training documentation. A Good rating is earned, not given.</p>
<p>For context, CQC data consistently shows that around 80% of adult social care services are rated Good or Outstanding — but the remaining 20% that fall short are precisely the providers you want to avoid when trusting someone\'s care to a company.</p>

<h2>What Winserve\'s Good Rating Means for You</h2>
<p>Winserve Care Services received a CQC Good rating following its inspection on 30 June 2025. This confirms that our care is safe, our staff are well-trained and supported, our service users\' needs are being met, and our management team is providing strong leadership and governance.</p>
<p>It also reflects the work of every member of our team — from our coordinators to our frontline carers — who show up every day with the skill, compassion, and professionalism that our service users and their families deserve.</p>

<h2>Questions to Ask Any Provider About Their CQC Status</h2>
<ul>
<li>What is your current CQC rating, and when was your last inspection?</li>
<li>Is your CQC certificate available to view?</li>
<li>Have any requirements or recommendations been made by the CQC?</li>
<li>How have you addressed any areas for improvement since your last inspection?</li>
</ul>
<p>Any reputable provider should be happy to answer these questions openly. If they are evasive or cannot confirm their registration status, treat that as a significant red flag.</p>

<p>At Winserve, we are proud of our Good rating and the hard work behind it. You can verify our registration directly on the <a href="https://www.cqc.org.uk/location/1-8945106634" target="_blank" rel="noopener">CQC website</a>.</p>',
  ],
  [
    'title' => 'What Is Domiciliary Care? A Complete Guide for Families',
    'date'  => '2026-02-20 09:00:00',
    'content' => '<p>If someone you love is struggling to manage everyday tasks at home — whether due to age, illness, or disability — domiciliary care may be exactly the support they need. But what does it actually involve, and how do you know if it\'s the right option?</p>

<h2>What Is Domiciliary Care?</h2>
<p>Domiciliary care (sometimes called home care or home help) is professional care delivered to a person in their own home. Rather than moving into a care home or residential facility, the individual stays in familiar surroundings while a trained carer visits at agreed times to provide support.</p>
<p>Visits can range from 30 minutes to several hours, and frequency can be anything from once a day to multiple visits daily — depending entirely on the individual\'s needs.</p>

<h2>What Does a Domiciliary Carer Do?</h2>
<p>A domiciliary carer provides a wide range of support, tailored entirely to the individual\'s care plan. This typically includes:</p>
<ul>
<li><strong>Personal care</strong> — help with washing, dressing, grooming, and toileting</li>
<li><strong>Medication assistance</strong> — prompting or administering medication at the right times</li>
<li><strong>Meal preparation</strong> — cooking nutritious meals to suit dietary needs and preferences</li>
<li><strong>Mobility support</strong> — helping with moving around the home safely</li>
<li><strong>Companionship</strong> — meaningful social interaction and emotional support</li>
<li><strong>Light housekeeping</strong> — maintaining a clean and safe home environment</li>
<li><strong>Community support</strong> — accompanying to appointments, shopping, or social activities</li>
</ul>

<h2>Who Is Domiciliary Care For?</h2>
<p>Domiciliary care can benefit a wide range of people:</p>
<ul>
<li>Older adults who want to remain independent at home as they age</li>
<li>People recovering from surgery, illness, or a hospital stay</li>
<li>Adults with physical disabilities or long-term health conditions</li>
<li>Individuals living with dementia or Alzheimer\'s</li>
<li>Adults with learning disabilities or mental health needs</li>
<li>Family carers who need respite support</li>
</ul>

<h2>How Do You Get Started?</h2>
<p>The first step is a care needs assessment — a conversation between you (and your loved one if appropriate), and the care provider. At Winserve, we offer a free, no-obligation assessment where we listen carefully, ask the right questions, and build a care plan that genuinely reflects the individual\'s preferences, goals, and needs.</p>
<p>Funding can be arranged privately, through a local authority care package, or via NHS Continuing Healthcare. Our team can help you understand the options available.</p>
<p>If you\'d like to find out more, <a href="/contact">get in touch with the Winserve team</a> — we\'re happy to talk through your situation at no obligation.</p>',
  ],
  [
    'title' => 'How to Choose the Right Home Care Company: 7 Questions to Ask',
    'date'  => '2025-11-10 09:00:00',
    'content' => '<p>Choosing a home care provider is one of the most important decisions a family can make. It\'s not just about price or availability — it\'s about finding a company you can genuinely trust with the wellbeing of someone you love. Here are seven questions that every family should ask before committing.</p>

<h2>1. Are You Registered with the CQC?</h2>
<p>Any company providing regulated personal care in England must be registered with the Care Quality Commission. Ask for their CQC registration number and check their rating online. Avoid any provider who can\'t confirm their CQC status.</p>

<h2>2. What Is Your Staff Turnover Rate?</h2>
<p>High staff turnover is a significant warning sign in the care sector. Consistent, familiar carers are essential for building trust — especially for clients with dementia or complex needs. Ask how long staff have typically been with the company.</p>

<h2>3. How Do You Vet and Train Your Carers?</h2>
<p>All carers should have an enhanced DBS (Disclosure and Barring Service) check before they begin work. Beyond that, ask about induction training, ongoing professional development, and whether the company supports carers through qualifications like the Care Certificate or NVQs.</p>

<h2>4. Will My Loved One Have Consistent Carers?</h2>
<p>Consistency matters enormously in home care. A revolving door of unfamiliar faces can be distressing and disruptive. Ask whether you\'ll be assigned a small, consistent team of carers rather than random staff allocations.</p>

<h2>5. How Is the Care Plan Created?</h2>
<p>A good care provider will create an individualised care plan based on a thorough assessment — not a generic checklist. The care plan should reflect the person\'s preferences, routines, goals, and any medical or support needs. It should also be regularly reviewed and updated.</p>

<h2>6. How Do You Handle Complaints and Concerns?</h2>
<p>Every reputable provider should have a clear, accessible complaints process. Ask how concerns are escalated, how quickly they are addressed, and whether they have an open door policy for families to raise issues at any time.</p>

<h2>7. Can I Speak to Existing Clients or Read Reviews?</h2>
<p>Testimonials and reviews from families who have used the service are one of the best indicators of quality. Check platforms like Homecare.co.uk, where reviews are independently verified. Winserve currently holds a score of 9.8/10 from 74 verified reviews.</p>

<p>Taking the time to ask these questions — and listening carefully to the answers — will help you find a care provider who is not just capable, but genuinely committed to the people they support. If you\'d like to ask us any of these questions directly, <a href="/contact">we\'d love to hear from you</a>.</p>',
  ],
  [
    'title' => 'Supported Living vs. Residential Care: What\'s the Difference?',
    'date'  => '2025-09-03 09:00:00',
    'content' => '<p>When someone needs long-term care and support, the options can feel overwhelming. Two terms that are often confused are "supported living" and "residential care". While both provide support for people with care needs, they are fundamentally different in how they work — and for many people, one option offers significantly more independence than the other.</p>

<h2>What Is Residential Care?</h2>
<p>Residential care means moving into a care home — a shared facility where residents live together and receive support from staff on-site. This can work very well for people with high-level needs who benefit from 24-hour supervision and the social environment of a care home.</p>
<p>However, residential care means leaving your own home, your familiar surroundings, and often giving up a significant degree of independence and personal choice.</p>

<h2>What Is Supported Living?</h2>
<p>Supported living is a very different model. The individual continues to live in their own home — or a home they share with others of their choosing — and receives tailored support from a care provider who visits at agreed times or is available on-site as needed.</p>
<p>Crucially, in a supported living arrangement, the person has a tenancy or ownership of their property. The care and the housing are kept separate. This means the individual has the legal right to choose their care provider independently of their housing — a fundamental protection of their rights and autonomy.</p>

<h2>Who Is Supported Living Suitable For?</h2>
<p>Supported living is particularly well-suited to:</p>
<ul>
<li>Adults with learning disabilities who want to live independently</li>
<li>People with physical disabilities or long-term health conditions</li>
<li>Adults with mental health needs</li>
<li>People with autism who thrive with consistent, individualised support</li>
<li>Younger adults transitioning from children\'s services</li>
</ul>

<h2>Which Is Right for Your Situation?</h2>
<p>The right choice depends on the individual\'s needs, preferences, and goals. Many people who are told they need residential care can actually be supported very effectively in their own home — with the right provider and the right care plan in place.</p>
<p>At Winserve, we specialise in supported living across Leeds and Cornwall. Our team works closely with individuals, families, local authorities, and healthcare professionals to create support packages that genuinely enable people to live the life they choose. <a href="/contact">Contact us to discuss your situation</a>.</p>',
  ],
  [
    'title' => 'The Real Benefits of Person-Centred Care — And Why It Matters',
    'date'  => '2025-07-14 09:00:00',
    'content' => '<p>The phrase "person-centred care" has become something of an industry buzzword in health and social care. But behind the language is a genuinely important principle — one that, when properly applied, makes a measurable difference to the lives of people who receive care.</p>

<h2>What Does Person-Centred Care Actually Mean?</h2>
<p>At its core, person-centred care means recognising the individual behind the care need. It means treating every person as a unique human being with their own history, preferences, values, and goals — not simply as a list of tasks to be completed.</p>
<p>In practice, it means:</p>
<ul>
<li>Involving the person in decisions about their own care</li>
<li>Respecting their preferences, routines, and lifestyle choices</li>
<li>Building genuine relationships between carers and service users</li>
<li>Focusing on what the person can do, not just what they can\'t</li>
<li>Treating dignity and respect as non-negotiable, in every interaction</li>
</ul>

<h2>Why Does It Matter?</h2>
<p>Research consistently shows that person-centred approaches improve outcomes across a wide range of measures. People who feel genuinely heard and involved in their care report higher levels of wellbeing, greater satisfaction, and better mental health outcomes. For those living with dementia, person-centred care has been shown to reduce anxiety and distress, and improve quality of life even in the later stages of the condition.</p>
<p>The alternative — a task-focused, tick-box approach to care — may technically complete the required visits, but it risks treating people as passive recipients of a service rather than active participants in their own lives.</p>

<h2>What It Looks Like at Winserve</h2>
<p>At Winserve, person-centred care isn\'t just a policy statement — it\'s how we operate day to day. Every care plan starts with a thorough, unhurried conversation with the individual (and their family, if appropriate). We ask about preferences, routines, goals, and what matters most to them. We ask who they are, not just what they need.</p>
<p>We also work hard to maintain consistency — matching the same carers to the same service users wherever possible, so genuine relationships can develop over time. Because great care isn\'t just about the tasks. It\'s about the people who deliver them, and the people who receive them.</p>

<p>If you\'d like to find out more about how Winserve can support you or your loved one, <a href="/contact">get in touch with our team today</a>.</p>',
  ],
];

$created = 0;
$admin_id = get_users(['role' => 'administrator', 'number' => 1, 'fields' => 'ID'])[0] ?? 1;

foreach ($posts as $p) {
    $existing = get_posts(['title' => $p['title'], 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => 1]);
    if (!empty($existing)) { echo '⚠️ Skipped (already exists): ' . esc_html($p['title']) . '<br>'; continue; }

    $id = wp_insert_post([
        'post_title'    => $p['title'],
        'post_content'  => $p['content'],
        'post_status'   => 'publish',
        'post_author'   => $admin_id,
        'post_date'     => $p['date'],
        'post_date_gmt' => $p['date'],
    ]);

    if (is_wp_error($id)) {
        echo '❌ Error: ' . esc_html($id->get_error_message()) . '<br>';
    } else {
        $created++;
        echo '✅ Created: ' . esc_html($p['title']) . ' (ID: ' . $id . ')<br>';
    }
}

// Trash the Hello World post if it exists
$hello = get_posts(['title' => 'Hello world!', 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => 1]);
if (!empty($hello)) {
    wp_trash_post($hello[0]->ID);
    echo '🗑️ Trashed: Hello world! post<br>';
}

echo '<br><strong>' . $created . ' post(s) created successfully.</strong><br>';
echo '<br>⚠️ Please delete this file from your server now (wp-content/themes/winserve-care-theme/setup-posts.php).';

// Self-delete
@unlink(__FILE__);
if (!file_exists(__FILE__)) { echo '<br>✅ This file has been automatically deleted.'; }
