# Running this on your own laptop

Plain-English setup. You do steps 1–3 once; after that every future lead list
is just step 6.

---

## 1. Install Python

**Mac** — open **Terminal** (Cmd+Space, type "Terminal", Enter) and type:

```bash
python3 --version
```

If it prints something like `Python 3.11.5`, you already have it — skip to step 2.
If not, download it from <https://www.python.org/downloads/> and run the installer.

**Windows** — download from <https://www.python.org/downloads/> and run the
installer. **Tick the box that says "Add python.exe to PATH"** on the first
screen. That box is easy to miss and nothing works without it.

Then open **PowerShell** (Start menu, type "PowerShell", Enter) and check:

```powershell
python --version
```

---

## 2. Download the code

Go to the repository on GitHub, switch to the branch
`claude/home-care-leads-clean-segment-t6pbtw`, click the green **Code** button →
**Download ZIP**. Unzip it somewhere easy to find, like your Desktop.

You want the folder that contains `run_pipeline.py` — it'll be inside at
`my-wordpress-site/lead-pipeline`.

---

## 3. Open a terminal in that folder

**Mac** — type `cd ` in Terminal (with a space after `cd`), then drag the
`lead-pipeline` folder from Finder onto the Terminal window and press Enter.

**Windows** — open the `lead-pipeline` folder in File Explorer, click the
address bar, type `powershell` and press Enter.

To check you're in the right place, run `ls` (Mac) or `dir` (Windows). You
should see `run_pipeline.py` in the list.

---

## 4. Install the two packages it needs

**Mac:**
```bash
pip3 install -r requirements.txt
```

**Windows:**
```powershell
pip install -r requirements.txt
```

You only ever do this once.

---

## 5. Put your spreadsheet in the folder

Copy your lead file into the same `lead-pipeline` folder. Rename it to
something short without spaces — `leads.xlsx` is ideal. Spaces in filenames
cause avoidable headaches.

---

## 6. Run it

Start with a small test run — 50 sites, about 30 seconds:

```bash
python3 run_pipeline.py --input leads.xlsx --sample 50
```

(On Windows use `python` instead of `python3` in all of these.)

You'll get a summary on screen: how many leads, how many good/ok/bad websites,
and a preview of both output files. If those numbers look sane, run the lot:

```bash
python3 run_pipeline.py --input leads.xlsx
```

That takes roughly 5–10 minutes for 800 sites. It prints progress as it goes.

If your internet drops or you close the window by accident, just run the same
command again — it remembers every site it already checked and carries on from
where it stopped.

---

## 7. Collect your files

Everything lands in a new `output` folder next to the script. The four you care
about:

| File | What to do with it |
|---|---|
| `segment_a_website_offer.csv` | Import to Instantly — the website offer |
| `segment_b_hr_automation_offer.csv` | Import to Instantly — the HR automation offer |
| `segment_a_website_offer_FRANCHISE.csv` | Same offer, franchise owners — send slowly |
| `segment_b_hr_automation_offer_FRANCHISE.csv` | Same offer, franchise owners — send slowly |

The two `_FRANCHISE` files are separate because everyone in them shares a mail
domain with head office. Drip those out over days rather than all at once.

Also worth a look before you send:

- `site_checks.csv` — the full audit for every site, so you can spot-check that
  a lead really does have the problem the notes claim
- `needs_review.csv` — anything the cleaner refused to trust
- `domain_duplicates.csv` — the extra contacts removed by deduping

---

## Importing to Instantly

Every column becomes a `{{variable}}` you can use in your sequence. The two
most useful:

- `{{personalization_notes}}` — a ready-made sentence about that specific lead
- `{{website_issue}}` — their single most pitchable problem, on its own

One gotcha: Instantly's built-in company field is called `company_name`, but
these files use `company`. Either map it across during import, or rename that
one header in Excel first.

---

## If something goes wrong

**"python: command not found"** — Python isn't installed, or on Windows you
missed the "Add to PATH" tickbox. Reinstall with that box ticked.

**"No such file or directory: leads.xlsx"** — the spreadsheet isn't in the same
folder as `run_pipeline.py`, or the name doesn't match exactly (including the
`.xlsx` on the end).

**Every site comes back "unreachable"** — something is blocking outbound
connections: a corporate network, VPN, or firewall. Try a normal home
connection or a phone hotspot.

**Columns weren't picked up** — the run prints which columns it recognised.
If yours are missing, either add the spelling to `HEADER_ALIASES` in
`common.py`, or pass it in directly:

```bash
python3 run_pipeline.py --input leads.xlsx --map '{"email":"Work Email"}'
```

---

## Re-running on future lists

Steps 5 and 6 only. Point `--input` at the new file and use a fresh output
folder so you don't mix runs together:

```bash
python3 run_pipeline.py --input new-leads.xlsx --outdir output-october
```
