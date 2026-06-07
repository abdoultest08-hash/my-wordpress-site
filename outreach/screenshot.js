/**
 * screenshot.js — 1440×900 laptop screenshot.
 * Serves the HTML via a local HTTP server so external images (Unsplash, Google CDN)
 * load exactly as they would in a real browser — no file:// CORS restrictions.
 * Usage: node screenshot.js <input.html> <output.png>
 */
const puppeteer = require('puppeteer');
const http      = require('http');
const fs        = require('fs');
const path      = require('path');

function startServer(htmlFile) {
  return new Promise((resolve) => {
    const server = http.createServer((req, res) => {
      res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
      fs.createReadStream(htmlFile).pipe(res);
    });
    server.listen(0, '127.0.0.1', () => {
      resolve({ server, port: server.address().port });
    });
  });
}

async function screenshot(htmlFile, outputFile) {
  const absPath = path.resolve(htmlFile);
  const { server, port } = await startServer(absPath);

  const browser = await puppeteer.launch({
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-gpu'],
    headless: true,
  });

  try {
    const page = await browser.newPage();
    await page.setViewport({ width: 1440, height: 900, deviceScaleFactor: 1 });

    // Load via HTTP — external images (Unsplash, Google CDN) load without restriction
    await page.goto(`http://127.0.0.1:${port}/`, { waitUntil: 'networkidle0', timeout: 30000 });

    // Wait for all <img> tags to finish loading
    await page.evaluate(() =>
      Promise.all(Array.from(document.images).map(img =>
        img.complete ? Promise.resolve() :
        new Promise(r => { img.onload = r; img.onerror = r; })
      ))
    );

    // Extra time for CSS background images (Unsplash hero)
    await new Promise(r => setTimeout(r, 2500));

    await page.screenshot({
      path: outputFile,
      clip: { x: 0, y: 0, width: 1440, height: 900 },
    });

    console.log(`Screenshot saved: ${outputFile}`);
  } finally {
    await browser.close();
    server.close();
  }
}

const [,, htmlFile, outputFile] = process.argv;
if (!htmlFile || !outputFile) {
  console.error('Usage: node screenshot.js <input.html> <output.png>');
  process.exit(1);
}

screenshot(htmlFile, outputFile).catch(e => { console.error(e); process.exit(1); });
