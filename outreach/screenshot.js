/**
 * screenshot.js
 * Takes a screenshot matching a real laptop screen (1440×900).
 * Waits for all images to fully load before capturing.
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
      '--disable-web-security',
      '--allow-file-access-from-files',
    ],
    headless: true,
  });

  try {
    const page = await browser.newPage();

    // 1440×900 = standard laptop viewport
    await page.setViewport({ width: 1440, height: 900, deviceScaleFactor: 1 });

    const absPath = path.resolve(htmlFile);
    await page.goto(`file://${absPath}`, { waitUntil: 'networkidle0', timeout: 30000 });

    // Wait until every <img> and CSS background image is fully loaded
    await page.evaluate(() => {
      return Promise.all(
        Array.from(document.images).map(img =>
          img.complete ? Promise.resolve() :
          new Promise(resolve => { img.onload = resolve; img.onerror = resolve; })
        )
      );
    });

    // Extra settle time for background images (CSS bg-image can't be tracked via document.images)
    await new Promise(r => setTimeout(r, 3500));

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
