/**
 * screenshot.js — 1440×900 laptop screenshot with guaranteed image rendering.
 * Uses Chrome's own fetch() to embed external images as base64 before capture.
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
    await page.setViewport({ width: 1440, height: 900, deviceScaleFactor: 1 });

    // Load the HTML file
    const absPath = path.resolve(htmlFile);
    await page.goto(`file://${absPath}`, { waitUntil: 'networkidle0', timeout: 30000 });

    // Use Chrome's own fetch to download external images and embed as base64.
    // This bypasses all CORS and file:// restrictions because Chrome is making the request.
    await page.evaluate(async () => {
      async function toDataUri(url) {
        try {
          const resp = await fetch(url, { mode: 'no-cors' });
          const blob = await resp.blob();
          return await new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload  = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(blob);
          });
        } catch (e) {
          return null;
        }
      }

      // Replace CSS background-image URLs
      for (const el of document.querySelectorAll('*')) {
        const computed = window.getComputedStyle(el);
        const bg = computed.backgroundImage;
        if (bg && bg !== 'none' && bg.includes('http')) {
          const match = bg.match(/url\(["']?(https?[^"')]+)["']?\)/);
          if (match) {
            const dataUri = await toDataUri(match[1]);
            if (dataUri) el.style.backgroundImage = `url('${dataUri}')`;
          }
        }
      }

      // Replace broken <img> src URLs
      for (const img of document.images) {
        if (img.src && img.src.startsWith('http') && !img.complete) {
          const dataUri = await toDataUri(img.src);
          if (dataUri) img.src = dataUri;
        }
        // Also try force-reload of logo images
        if (img.src && img.src.startsWith('http')) {
          const dataUri = await toDataUri(img.src);
          if (dataUri) img.src = dataUri;
        }
      }
    });

    // Let everything repaint
    await new Promise(r => setTimeout(r, 1500));

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
