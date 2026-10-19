"""Assemble the mockup pages from _src/pages + shared partials.

index.html is published as the Artifact's main page, which gets its own
document skeleton, so it is written without <!doctype>/<head>. Every other
page is a standalone document.

Run: python3 _src/build.py   (from mockups/hcc-temps)
"""
import pathlib
import re

ROOT = pathlib.Path(__file__).resolve().parent.parent
SRC = ROOT / "_src"
HEADER = (SRC / "partials/header.html").read_text()
FOOTER = (SRC / "partials/footer.html").read_text()

HEAD = """<meta name="claude:viewport-fit-sides" content="cover">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,800&family=Figtree:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="assets/site.css">"""

for page in sorted((SRC / "pages").glob("*.html")):
    text = page.read_text()
    meta = dict(kv.split(":", 1) for kv in re.match(r"<!--(.*?)-->", text).group(1).split("|"))
    body = text.split("-->", 1)[1].lstrip("\n")
    header = HEADER.replace(f'data-key="{meta["key"]}"', f'data-key="{meta["key"]}" aria-current="page"')
    content = (
        f"<title>{meta['title']}</title>\n{HEAD}\n\n{header}\n{body.split('<script>', 1)[0]}"
        f"\n{FOOTER}\n<script src=\"assets/site.js\"></script>\n"
    )
    if "<script>" in body:
        content += "<script>" + body.split("<script>", 1)[1]
    if page.name != "index.html":
        content = (
            '<!doctype html>\n<html lang="en-GB">\n<head>\n<meta charset="utf-8">\n'
            '<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">\n'
            + content.replace(f"{HEAD}\n\n", f"{HEAD}\n</head>\n<body>\n", 1)
            + "</body>\n</html>\n"
        )
    (ROOT / page.name).write_text(content)
    print("built", page.name)
