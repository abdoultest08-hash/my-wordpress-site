/**
 * screenshot.js
 * Takes a full above-the-fold screenshot of an HTML file using Puppeteer.
 * Usage: node screenshot.js <input.html> <output.png>
 */
const puppeteer = require('puppeteer');
const path = require('path');
const fs   = require('fs');

async function screenshot(htmlFile, outputFile) {
  const browser = await puppeteer.launch({
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-gpu'],
    headless: true,
  });

  try {
    const page = await browser.newPage();

    // Desktop viewport — 1280×900 captures hero + top of services section
    await page.setViewport({ width: 1280, height: 900, deviceScaleFactor: 1.5 });

    const absPath = path.resolve(htmlFile);
    await page.goto(`file://${absPath}`, { waitUntil: 'networkidle0', timeout: 30000 });

    // Wait for fonts & images to settle
    await new Promise(r => setTimeout(r, 2000));

    // Capture hero + first section below (matches typical laptop screen)
    await page.screenshot({
      path: outputFile,
      clip: { x: 0, y: 0, width: 1280, height: 900 },
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
