/**
 * screenshot.js
 * Takes a screenshot of an HTML file matching a real laptop screen (1440×900).
 * Usage: node screenshot.js <input.html> <output.png>
 */
const puppeteer = require('puppeteer');
const path = require('path');

async function screenshot(htmlFile, outputFile) {
  const browser = await puppeteer.launch({
    args: [
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--disable-gpu',
      '--disable-web-security',          // allows external images/fonts to load
      '--allow-file-access-from-files',
    ],
    headless: true,
  });

  try {
    const page = await browser.newPage();

    // 1440×900 = standard MacBook Pro / laptop viewport
    await page.setViewport({ width: 1440, height: 900, deviceScaleFactor: 1 });

    const absPath = path.resolve(htmlFile);
    await page.goto(`file://${absPath}`, { waitUntil: 'networkidle0', timeout: 30000 });

    // Wait extra time for external images (Unsplash) and Google Fonts to load
    await new Promise(r => setTimeout(r, 4000));

    // Full 1440×900 — matches what the business owner sees on a laptop
    await page.screenshot({
      path: outputFile,
      clip: { x: 0, y: 0, width: 1440, height: 900 },
    });

    console.log(`Screenshot saved: ${outputFile}`);
  } finally {
    await browser.close();
  }
}

const [,, htmlFile, outputFile] = process.argv;
if (!htmlFile || !outputFile) {
  console.error('Usage: node screenshot.js <input.html> <output.png>');
  process.exit(1);
}

screenshot(htmlFile, outputFile).catch(e => { console.error(e); process.exit(1); });
