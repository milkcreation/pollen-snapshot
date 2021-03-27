const puppeteer = require('puppeteer'),
    args = process.argv.splice(2),
    url = args[0] || 'https://google.com',
    file = args[1] || 'screenshot.png';

(async () => {
  const browser = await puppeteer.launch();
  const page = await browser.newPage();
  await page.goto(url, {
    waitUntil: 'networkidle2',
  });
  await page.screenshot({path: file, fullPage: true});

  await browser.close();
})();
