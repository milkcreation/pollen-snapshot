const puppeteer = require('puppeteer'),
    args = process.argv.splice(2),
    url = args[0] || 'https://google.com',
    file = args[1] || 'screenshot.png',
    format = args[2] || 'img';

(async () => {
  const browser = await puppeteer.launch();
  const page = await browser.newPage();
  await page.goto(url, {
    /**
     * @see node_modules/puppeteer/lib/esm/puppeteer/common/LifecycleWatcher.d.ts
     *
     * @type {String} referer
     * @typye {Number} timeout
     * @type {String} waitUntil 'load'|'domcontentloaded'|'networkidle0'|'networkidle2'
     */
    waitUntil: 'networkidle2',
  });

  if (format === 'pdf') {
    // @see node_modules/puppeteer/lib/cjs/puppeteer/common/PDFOptions.d.ts
    await page.pdf({
      path: file,
      format: 'a4'
    });
  } else {
    await page.screenshot({path: file, fullPage: true});
  }

  await browser.close();
})();
