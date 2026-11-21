// Έλεγχος σε κινητό (iPhone 13): node tests/mobile.js <email> <building_id> <period_id> <out-dir> — οριζόντιο ξεχείλισμα ανά σελίδα + στιγμιότυπα.
const { chromium, devices } = require('playwright');
(async () => {
  const [email, bid, pid, dir] = process.argv.slice(2);
  const b = await chromium.launch(); const ctx = await b.newContext({ ...devices['iPhone 13'] }); const p = await ctx.newPage();
  const U = (q) => 'http://127.0.0.1:8080/index.php?option=com_koinoxrista&tmpl=component&' + q;
  await p.goto(U('view=app')); await p.screenshot({ path: dir + '/00-login.png' });
  await p.fill('[name=username]', email); await p.fill('[name=password]', 'Secret12345');
  await p.waitForTimeout(1200); await Promise.all([p.waitForNavigation(), p.click('button[type=submit]')]);
  const pages = { dashboard: 'view=dashboard', buildings: 'view=buildings', units: 'view=building&id=' + bid + '&tab=units', info: 'view=building&id=' + bid + '&tab=info',
    expenses: 'view=period&id=' + pid, heating: 'view=period&id=' + pid + '&tab=heating', split: 'view=period&id=' + pid + '&tab=split', printtab: 'view=period&id=' + pid + '&tab=print',
    print: 'view=print&period_id=' + pid + '&type=sheet', payments: 'view=payments&building_id=' + bid, templates: 'view=templates', account: 'view=account&tab=billing' };
  let i = 1;
  for (const [n, q] of Object.entries(pages)) {
    await p.goto(U(q)); await p.waitForTimeout(400);
    const ow = await p.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
    console.log(n.padEnd(10), 'horizontal overflow px:', ow);
    await p.screenshot({ path: dir + '/' + String(i++).padStart(2, '0') + '-' + n + '.png' });
  }
  await b.close();
})();
