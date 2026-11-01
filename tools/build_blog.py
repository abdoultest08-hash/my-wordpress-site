"""Build wp-content/themes/hcc-temps/data/blog-posts.json: the 8 posts from the current hcctemps.co.uk
blog (cleaned, images bundled) plus the 8 new 2026 articles in tools/blog_new_posts.py.

Usage: python3 tools/build_blog.py <posts.json from /wp-json/wp/v2/posts?_embed> <img-map.json>
Images: the old posts' images were downloaded and resized into data/blog-img/; img-map.json maps the
original file names to the bundled ones. In post content they become {{img:<file>}} and are replaced
with Media Library URLs when the posts are imported.
"""
import html
import json
import os
import re
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from blog_new_posts import POSTS as NEW  # noqa: E402

SRC, MAP = sys.argv[1], sys.argv[2]
OUT = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'wp-content', 'themes', 'hcc-temps', 'data', 'blog-posts.json')
imgmap = json.load(open(MAP))

# Tidied categories for the original posts, and their Google descriptions.
OLD = {
    'dementia-care-homes-with-specialised-nutrition-programs-in-the-uk-why-expert-chefs-make-the-difference': ('Chefs & hospitality', 'How specialised nutrition programmes support residents in UK dementia care homes, and why experienced chefs who understand texture-modified and fortified diets make the difference.'),
    'why-school-meals-matter-nutrition-learning-the-role-of-hospitality-agencies': ('Chefs & hospitality', 'Why nutritious school meals matter for children\'s health, wellbeing and learning, and how skilled temporary kitchen staff help school catering teams keep standards high.'),
    'agency-chefs-for-care-homes-in-london': ('Chefs & hospitality', 'A day in the life of an agency chef in a London care home: menus, special diets and why care homes use chef recruitment agencies like HCC-Temps for flexible cover.'),
    'have-you-put-your-enhanced-dbs-certificate-on-the-update-service': ('For candidates', 'Why care workers should register a new enhanced DBS certificate on the DBS Update Service within 30 days, and how it speeds up starting work with an agency.'),
    'welcome-to-the-hcc-temps-family-your-guide-to-professional-uniforms': ('For candidates', 'Your guide to professional uniforms at HCC-Temps: what healthcare and hospitality staff should wear on shift to look professional and stay safe.'),
    'coshh-essential-guidelines-for-safe-kitchen-operations': ('Chefs & hospitality', 'COSHH explained for care home kitchens: how to store, use and record hazardous cleaning substances safely and keep kitchen staff and residents protected.'),
    'understanding-news2-why-it-matters-and-what-were-doing-about-it': ('For care home managers', 'What NEWS2, the National Early Warning Score, means for care home managers, why it matters for spotting deterioration early, and how HCC-Temps prepares its nurses.'),
    'oliver-mcgowan-mandatory-training-a-must-know-for-care-home-managers': ('For care home managers', 'The Oliver McGowan Mandatory Training on learning disability and autism: what care home managers need to know and how HCC-Temps makes sure staff are trained.'),
}


def base_name(u):
    return os.path.basename(u.split('?')[0])


def bundled(u):
    """Bundled file for an image URL from the old site (also matches resized -300x300 copies)."""
    for cand in (re.sub(r'-\d+x\d+(\.\w+)$', r'\1', base_name(u)), base_name(u)):
        if cand in imgmap:
            return imgmap[cand]
    return None


def clean(content):
    c = content
    c = re.sub(r'\s(srcset|sizes|loading|decoding|fetchpriority)="[^"]*"', '', c)
    c = re.sub(r'<img([^>]*?)\sclass="([^"]*)"', lambda m: '<img%s class="%s"' % (m.group(1), ' '.join(x for x in m.group(2).split() if x.startswith('align') or x == 'size-large')), c)

    def img(m):
        tag = m.group(0)
        src = re.search(r'src="([^"]+)"', tag)
        f = bundled(src.group(1)) if src else None
        return tag.replace(src.group(1), '{{img:%s}}' % f) if f else tag
    c = re.sub(r'<img[^>]+>', img, c)
    c = re.sub(r'<p>(\s|&nbsp;)*</p>', '', c)
    c = c.replace('<p>&nbsp;</p>', '')
    return c.strip()


posts = []
for p in json.load(open(SRC)):
    if p['slug'] not in OLD:
        continue
    cat, desc = OLD[p['slug']]
    fm = (p.get('_embedded', {}).get('wp:featuredmedia') or [{}])[0].get('source_url', '')
    content = clean(p['content']['rendered'])
    image = bundled(fm) if fm else None
    if not image:  # no featured image on the old site: use the first image in the post
        m = re.search(r'\{\{img:([^}]+)\}\}', content)
        image = m.group(1) if m else None
    posts.append({
        'slug': p['slug'],
        'title': html.unescape(p['title']['rendered']),
        'date': p['date'].replace('T', ' '),
        'category': cat,
        'image': ('bundle:' + image) if image else '',
        'excerpt': desc,
        'content': content,
    })

for n in NEW:
    posts.append(dict(n, content=n['content'].strip()))

posts.sort(key=lambda x: x['date'])
json.dump(posts, open(OUT, 'w'), ensure_ascii=False, indent=1)
missing = [p['slug'] for p in posts if '{{img:' not in p['content'] and p['image'].startswith('bundle:') is False and not p['image']]
print(len(posts), 'posts written to', os.path.normpath(OUT))
for p in posts:
    print(p['date'][:10], p['category'][:24].ljust(24), p['image'][:40].ljust(40), len(re.sub(r'<[^>]+>', ' ', p['content']).split()), 'words |', p['title'][:60])
