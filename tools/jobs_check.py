"""Build the simple client check workbook (2 tabs: Instructions, Jobs to check) from the job import CSV.

Usage: python3 tools/jobs_check.py client-docs/HCC-Jobs-Import.csv client-docs/HCC-Jobs-Review.xlsx client-docs/HCC-Jobs-Check.xlsx
After building, re-save it through LibreOffice so Excel opens it reliably:
  soffice --headless --calc --convert-to 'xlsx:Calc MS Excel 2007 XML' --outdir <dir> client-docs/HCC-Jobs-Check.xlsx
The import CSV holds the jobs as they are on the site; the older review workbook supplies the date each
advert was first posted and its link on the current website. Read the returned file back with
tools/jobs_from_review.py.
"""
import csv, re, sys, datetime
from openpyxl import Workbook, load_workbook
from openpyxl.styles import Font, PatternFill, Alignment, Border, Side
from openpyxl.worksheet.datavalidation import DataValidation
from openpyxl.formatting.rule import FormulaRule
from openpyxl.utils import get_column_letter

SRC, OLD, OUT = sys.argv[1], sys.argv[2], sys.argv[3]
NLW = 12.71  # UK National Living Wage from April 2026 (age 21+)
TODAY = datetime.date(2026, 11, 1)
SECTORS = ['Healthcare', 'Chefs', 'Hospitality', 'Housekeeping & Cleaning', 'Admin & Reception']

jobs = list(csv.DictReader(open(SRC, encoding='utf-8-sig')))
old = {}
ws_old = load_workbook(OLD)['Jobs']
h = [c.value for c in ws_old[1]]
for r in ws_old.iter_rows(min_row=2, values_only=True):
    d = dict(zip(h, r))
    old[d['Job ref (do not change)']] = d


def flags(j, o):
    out = []
    title = j['title'].lower()
    posted = o.get('First posted')
    if posted:
        p = datetime.datetime.strptime(posted, '%d/%m/%Y').date()
        months = (TODAY - p).days // 30
        if months >= 6:
            out.append('First posted %s (%d months ago): is it still open?' % (p.strftime('%b %Y'), months))
    try:
        pmin = float(j['pay_min'])
    except ValueError:
        pmin = None
    if not j['pay']:
        out.append('No pay rate: please add one')
    elif pmin is not None and pmin < NLW and 'annum' not in j['pay'] and 'year' not in j['pay']:
        out.append('Pay is below the minimum wage (£%.2f an hour): please update' % NLW)
    if not j['location']:
        out.append('No location: please add one')
    elif 'Location taken from the title' in (o.get('Please check (from us)') or ''):
        out.append('Location: please check it is right')
    want = 'Healthcare' if re.search(r'nurse|rgn|rmn|care assistant|carer|healthcare|support worker', title) else \
        'Chefs' if re.search(r'\bchef|\bcook', title) else \
        'Housekeeping & Cleaning' if re.search(r'clean|housekeep|domestic|laundry', title) else None
    if want and j['sector'] != want:
        out.append('Sector says "%s" but this looks like a %s job: please check' % (j['sector'], want))
    return out


wb = Workbook()
# ---------- Tab 1: Instructions ----------
ins = wb.active
ins.title = 'Instructions'
ins.sheet_view.showGridLines = False
import math
ins.column_dimensions['A'].width = 3
for col, w in zip('BCDEF', (34, 20, 13, 30, 19)):
    ins.column_dimensions[col].width = w
navy, ink = '171A45', '1E2140'
rows = [
    ('title', 'Job adverts: please check before they go live'),
    ('text', 'There are %d job adverts on the new website. Please check each one on the "Jobs to check" tab (the second tab at the bottom), then send this file back to us.' % len(jobs)),
    ('progress', ''),
    ('gap', ''),
    ('head', 'What to do for each job (one row = one job)'),
    ('step', '1.  Still live?  Choose Yes if the job is still open, or No if it has been filled or is no longer needed. Jobs marked No are taken off the website, and you only need steps 2 and 3 for jobs marked Yes.'),
    ('step', '2.  The grey columns show what the website says now. If something is wrong, type the correct value in the white "New ..." column next to it. Leave a "New ..." column empty if the current value is right.'),
    ('step', '3.  Read the yellow "Please check" column. It lists what we spotted for that job, for example an old advert, missing pay, pay below the minimum wage, or the wrong sector.'),
    ('gap', ''),
    ('head', 'How to write things'),
    ('step', 'New pay:  as it should appear on the advert, e.g.  £13.50 – £14.00 per hour'),
    ('step', 'Lowest hourly rate:  the lowest number only, e.g.  13.50  (it is used for the pay filter on the jobs page)'),
    ('step', 'New location:  the town or city, e.g.  Leeds.  For more than one, separate them with  |   e.g.  Leeds | Bradford'),
    ('step', 'New sector and Contract:  pick from the drop-down list in the cell.'),
    ('step', 'Anything else (for example the advert text needs changing):  write it in "Notes for us".'),
    ('gap', ''),
    ('head', 'Good to know'),
    ('step', '•  Please don\'t change the "Job ref" column: it links each row to its advert on the website.'),
    ('step', '•  To add a brand new job, add a row at the bottom, leave "Job ref" empty and fill in the "New ..." columns.'),
    ('step', '•  The minimum wage (National Living Wage, age 21+) is £%.2f an hour from April 2026.' % NLW),
    ('step', '•  A row turns grey when you choose No. That is normal: nothing else is needed for that job.'),
    ('gap', ''),
    ('head', 'Example'),
]
r = 1
for kind, text in rows:
    ins.merge_cells(start_row=r, start_column=2, end_row=r, end_column=6)
    c = ins.cell(row=r, column=2, value=text)
    c.alignment = Alignment(wrap_text=True, vertical='top')
    if kind == 'title':
        c.font = Font(bold=True, size=18, color=navy); ins.row_dimensions[r].height = 34
    elif kind == 'head':
        c.font = Font(bold=True, size=13, color=navy); ins.row_dimensions[r].height = 26
    elif kind in ('step', 'text'):
        c.font = Font(size=12 if kind == 'text' else 11, color=ink); ins.row_dimensions[r].height = 16 * math.ceil(len(text) / 105) + 8
    elif kind == 'progress':
        progress_row = r
    r += 1
# example table, coloured like the Jobs to check tab
ex = [('Job title (now)', 'Pay (now)', 'Still live?', 'New pay', 'Lowest hourly rate'),
      ('Chef – Glasgow', '£11.00 per hour', 'Yes', '£13.50 – £15.00 per hour', 13.50),
      ('Kitchen Assistant – Leeds', '£11.44 per hour', 'No', '', ''),
      ('Healthcare Assistant – Frome', '£13.00 per hour', 'Yes', '(empty: pay is right)', '')]
kinds = ('now', 'now', 'ask', 'new', 'new')
fills = {'now': 'EEF1F4', 'ask': 'FDE7EE', 'new': 'FFFFFF'}
heads = {'now': navy, 'ask': 'E8457A', 'new': '2D338C'}
box = Side(style='thin', color='C9CFD8')
for i, line in enumerate(ex):
    for k, v in enumerate(line):
        c = ins.cell(row=r + i, column=2 + k, value=v)
        c.border = Border(top=box, bottom=box, left=box, right=box)
        c.alignment = Alignment(vertical='center', horizontal='center' if kinds[k] == 'ask' else 'left', wrap_text=True)
        if i == 0:
            c.font = Font(bold=True, color='FFFFFF'); c.fill = PatternFill('solid', fgColor=heads[kinds[k]])
        else:
            no = line[2] == 'No'
            c.fill = PatternFill('solid', fgColor='D9D9D9' if no else ('D7F0E3' if kinds[k] == 'ask' else fills[kinds[k]]))
            c.font = Font(color='808080' if no else ink, bold=kinds[k] == 'ask', italic=str(v).startswith('('))
            if k == 4 and v != '':
                c.number_format = '0.00'
    ins.row_dimensions[r + i].height = 22
r += len(ex) + 1
ins.merge_cells(start_row=r, start_column=2, end_row=r, end_column=6)
ins.cell(row=r, column=2, value='Thank you!').font = Font(italic=True, color='5A6075')

# ---------- Tab 2: Jobs to check ----------
ws = wb.create_sheet('Jobs to check')
cols = [  # key, header, width, kind: ref | now | flag | ask | new | link
    ('ref', 'Job ref\n(don\'t change)', 13, 'ref'),
    ('title', 'Job title (now)', 40, 'now'),
    ('location', 'Location (now)', 18, 'now'),
    ('pay', 'Pay (now)', 22, 'now'),
    ('sector', 'Sector (now)', 18, 'now'),
    ('flags', 'Please check (from us)', 40, 'flag'),
    ('live', 'Still live?\nYes / No', 12, 'ask'),
    ('n_title', 'New job title\n(only if wrong)', 32, 'new'),
    ('n_location', 'New location\n(only if wrong)', 18, 'new'),
    ('n_pay', 'New pay\ne.g. £13.50 – £14.00 per hour', 26, 'new'),
    ('n_pmin', 'Lowest hourly rate\nnumber, e.g. 13.50', 15, 'new'),
    ('n_sector', 'New sector\n(only if wrong)', 20, 'new'),
    ('contract', 'Contract', 15, 'new'),
    ('shifts', 'Shifts (optional)\ne.g. Days, Nights', 18, 'new'),
    ('notes', 'Notes for us', 30, 'new'),
    ('link', 'Advert on the current website', 34, 'link'),
    ('posted', 'First posted', 12, 'now'),
]
F = {'head': PatternFill('solid', fgColor=navy), 'ask_head': PatternFill('solid', fgColor='E8457A'), 'new_head': PatternFill('solid', fgColor='2D338C'),
     'now': PatternFill('solid', fgColor='EEF1F4'), 'flag': PatternFill('solid', fgColor='FFF2CC'), 'ask': PatternFill('solid', fgColor='FDE7EE'), 'new': PatternFill('solid', fgColor='FFFFFF')}
thin = Side(style='thin', color='D5DAE1')
for c, (k, hd, w, kind) in enumerate(cols, 1):
    cell = ws.cell(row=1, column=c, value=hd)
    cell.font = Font(bold=True, color='FFFFFF')
    cell.fill = F['ask_head'] if kind == 'ask' else F['new_head'] if kind == 'new' else F['head']
    cell.alignment = Alignment(wrap_text=True, vertical='center', horizontal='center' if kind == 'ask' else 'left')
    ws.column_dimensions[get_column_letter(c)].width = w
ws.row_dimensions[1].height = 60
for i, j in enumerate(jobs, 2):
    o = old.get(j['ref'], {})
    fl = flags(j, o)
    data = {'ref': j['ref'], 'title': j['title'], 'location': j['location'], 'pay': j['pay'], 'sector': j['sector'], 'flags': '\n'.join(fl),
            'live': None, 'contract': j['contract'], 'link': o.get('Current advert') or '', 'posted': o.get('First posted') or ''}
    for c, (k, hd, w, kind) in enumerate(cols, 1):
        cell = ws.cell(row=i, column=c, value=data.get(k))
        cell.alignment = Alignment(wrap_text=k in ('title', 'flags', 'notes', 'n_title', 'location', 'sector', 'n_location', 'n_pay', 'pay'), vertical='top', horizontal='center' if kind == 'ask' else 'left')
        cell.fill = F['flag'] if (kind == 'flag' and fl) else F['now'] if kind in ('now', 'ref', 'link', 'flag') else F[kind]
        cell.border = Border(bottom=thin, right=thin)
        if kind == 'ask':
            cell.font = Font(bold=True, size=12)
        if k == 'link' and data['link']:
            cell.hyperlink = data['link']; cell.value = 'Open advert'; cell.font = Font(color='2D338C', underline='single')
        if k == 'n_pmin':
            cell.number_format = '0.00'
    ws.row_dimensions[i].height = max(32, 15 * sum(math.ceil(len(f) / 40) for f in fl) + 8, 15 * math.ceil(len(j['title']) / 38) + 8)
n = len(jobs) + 1
ws.freeze_panes = 'C2'
ws.auto_filter.ref = 'A1:%s%d' % (get_column_letter(len(cols)), n)
L = {k: get_column_letter(c) for c, (k, *_r) in enumerate(cols, 1)}
dv = DataValidation(type='list', formula1='"Yes,No"', allow_blank=True, error='Please choose Yes or No', errorTitle='Still live?', showErrorMessage=True)
dv.add('%s2:%s%d' % (L['live'], L['live'], n + 200)); ws.add_data_validation(dv)
dv = DataValidation(type='list', formula1='"%s"' % ','.join(SECTORS), allow_blank=True); dv.add('%s2:%s%d' % (L['n_sector'], L['n_sector'], n + 200)); ws.add_data_validation(dv)
dv = DataValidation(type='list', formula1='"Temporary,Permanent,Temp to perm"', allow_blank=True); dv.add('%s2:%s%d' % (L['contract'], L['contract'], n + 200)); ws.add_data_validation(dv)
dv = DataValidation(type='decimal', operator='between', formula1='0', formula2='500', allow_blank=True, error='Number only, e.g. 13.50', showErrorMessage=True)
dv.add('%s2:%s%d' % (L['n_pmin'], L['n_pmin'], n + 200)); ws.add_data_validation(dv)
# Visual cues: rows answered "No" turn grey; "Yes" turns the answer green.
rng = 'A2:%s%d' % (get_column_letter(len(cols)), n)
ws.conditional_formatting.add(rng, FormulaRule(formula=['$%s2="No"' % L['live']], fill=PatternFill('solid', fgColor='D9D9D9'), font=Font(color='808080')))
ws.conditional_formatting.add('%s2:%s%d' % (L['live'], L['live'], n), FormulaRule(formula=['$%s2="Yes"' % L['live']], fill=PatternFill('solid', fgColor='D7F0E3'), font=Font(bold=True, color='17694A')))
# progress line in the instructions
ins.cell(row=progress_row, column=2).value = '=COUNTIF(\'Jobs to check\'!%s2:%s%d,"Yes")+COUNTIF(\'Jobs to check\'!%s2:%s%d,"No")&" of %d jobs answered so far"' % (L['live'], L['live'], n, L['live'], L['live'], n, len(jobs))
ins.cell(row=progress_row, column=2).font = Font(bold=True, color='E8457A', size=12)
for sh in (ins, ws):  # print: landscape, fit to page width
    sh.page_setup.orientation = 'landscape'
    sh.page_setup.paperSize = sh.PAPERSIZE_A4
    sh.sheet_properties.pageSetUpPr.fitToPage = True
    sh.page_setup.fitToWidth = 1
    sh.page_setup.fitToHeight = 0
ws.print_title_rows = '1:1'
wb.active = 0
wb.save(OUT)
fl_all = [flags(j, old.get(j['ref'], {})) for j in jobs]
print(len(jobs), 'jobs |', sum(any('months ago' in f for f in x) for x in fl_all), 'older than 6 months |', sum(any('minimum wage' in f for f in x) for x in fl_all), 'below min wage |',
      sum(any('No pay' in f for f in x) for x in fl_all), 'no pay |', sum(any('Sector says' in f for f in x) for x in fl_all), 'sector mismatch |', sum(any('ocation' in f for f in x) for x in fl_all), 'location')
