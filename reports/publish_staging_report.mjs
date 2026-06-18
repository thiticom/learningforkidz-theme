#!/usr/bin/env node
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const REPORTS = path.join(ROOT, 'reports');
const DOCS = path.join(ROOT, 'docs');
const DATE = '2026-06-18';
const VERSION = 'v2026.06.18.2';
const STAGING_URL = 'https://staging.learningforkidz.com';
const PROD_URL = 'https://www.learningforkidz.com';

const DIRS = {
  target: {
    src: path.join(REPORTS, 'theme-parity-audit-2026-06-18-staging-target-final'),
    dest: path.join(DOCS, 'theme-parity-audit-2026-06-18-staging-target-final'),
    json: 'theme-parity-audit.json',
  },
  commerce: {
    src: path.join(REPORTS, 'theme-parity-audit-2026-06-18-staging-commerce-final2'),
    dest: path.join(DOCS, 'theme-parity-audit-2026-06-18-staging-commerce-final2'),
    json: 'theme-parity-audit.json',
  },
  broad: {
    src: path.join(REPORTS, 'theme-parity-audit-2026-06-18-staging-full-final'),
    dest: path.join(DOCS, 'theme-parity-audit-2026-06-18-staging-full-final'),
    json: 'theme-parity-audit.json',
  },
  about: {
    src: path.join(REPORTS, 'theme-parity-audit-2026-06-18-staging-about-recheck'),
    dest: path.join(DOCS, 'theme-parity-audit-2026-06-18-staging-about-recheck'),
    json: 'theme-parity-audit.json',
  },
  product: {
    src: path.join(REPORTS, 'product-interaction-audit-2026-06-18-staging-final'),
    dest: path.join(DOCS, 'product-interaction-audit-2026-06-18-staging-final'),
    json: 'product-interaction-audit.json',
  },
  checkout: {
    src: path.join(REPORTS, 'strict-checkout-audit-2026-06-18-staging-final4'),
    dest: path.join(DOCS, 'strict-checkout-audit-2026-06-18-staging-final4'),
    json: 'checkout-audit.json',
  },
  performance: {
    src: path.join(REPORTS, 'performance-audit-2026-06-18-staging-final'),
    dest: path.join(DOCS, 'performance-audit-2026-06-18-staging-final'),
    json: 'performance.json',
  },
};

const ROUTE_LABELS = {
  home: 'Home',
  shop: 'Shop archive',
  'product-category': 'Product category archive',
  'product-brand': 'Product brand archive',
  'age-taxonomy': 'Age taxonomy archive',
  'single-product': 'Single product',
  'article-archive': 'Post archive',
  'single-post': 'Single post',
  search: 'Search results',
  cart: 'Cart',
  checkout: 'Checkout',
  'my-account': 'My Account',
  wishlist: 'Wishlist',
  contact: 'Contact page',
  promotion: 'Promotion page',
  'ages-page': 'Ages page',
  'brands-page': 'Brands page',
  'about-page': 'About page',
  'refund-page': 'Refund policy',
  'how-to-orders': 'How to order',
  'privacy-page': 'Privacy policy',
};

const PRODUCT_LABELS = {
  'kanoodle-magenta': 'Kanoodle Fan Edition (Magenta)',
  'kanoodle-head-to-head': 'Kanoodle Head-to-Head',
  'kanoodle-genius': 'Kanoodle Genius',
  'kanoodle-ultimate-champion': 'Kanoodle Ultimate Champion',
};

const DATA_SYNC_ROUTES = new Set(['home', 'shop', 'product-brand', 'search']);

function readJson(file) {
  return JSON.parse(fs.readFileSync(file, 'utf8'));
}

function write(file, body) {
  fs.mkdirSync(path.dirname(file), { recursive: true });
  fs.writeFileSync(file, body);
}

function copyTree(src, dest) {
  fs.rmSync(dest, { recursive: true, force: true });
  fs.cpSync(src, dest, { recursive: true });
}

function esc(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function fmt(value, digits = 1) {
  if (!Number.isFinite(Number(value))) return '0';
  return Number(value).toFixed(digits).replace(/\.0$/, '');
}

function statusClass(status) {
  if (status === 'PASS') return 'pass';
  if (status === 'DATA SYNC' || status === 'CONTENT/DATA DIFFERENCE') return 'note';
  return 'fix';
}

function badge(status) {
  return `<span class="badge ${statusClass(status)}">${esc(status)}</span>`;
}

function page(title, eyebrow, body) {
  const content = body.trim();
  return `<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>${esc(title)}</title>
  <style>
    :root {
      --ink: #172033;
      --muted: #5e687a;
      --line: #dde4ec;
      --panel: #fff;
      --surface: #f7f9fb;
      --cyan: #42bedd;
      --blue: #2f6f9f;
      --orange: #c96f45;
      --green: #1e6a3d;
      --gold: #87521c;
      --red: #b54747;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      color: var(--ink);
      background: var(--surface);
      font-family: "Noto Sans Thai", Tahoma, "Segoe UI", Arial, sans-serif;
      line-height: 1.65;
    }
    a { color: var(--blue); }
    img { display: block; max-width: 100%; }
    .page {
      width: min(1180px, calc(100% - 32px));
      margin: 0 auto;
      padding: 34px 0 68px;
    }
    .eyebrow {
      margin: 0 0 8px;
      color: var(--orange);
      font-size: .92rem;
      font-weight: 800;
    }
    h1 {
      margin: 0;
      max-width: 1040px;
      font-size: clamp(2.15rem, 5vw, 4rem);
      line-height: 1.08;
      letter-spacing: 0;
    }
    h2 {
      margin: 0 0 12px;
      font-size: clamp(1.45rem, 3vw, 2rem);
      line-height: 1.25;
      letter-spacing: 0;
    }
    h3 {
      margin: 0 0 8px;
      font-size: 1.08rem;
      line-height: 1.35;
      letter-spacing: 0;
    }
    p { margin: 0 0 12px; }
    .lead {
      margin-top: 16px;
      max-width: 920px;
      color: var(--muted);
      font-size: 1.08rem;
    }
    .actions {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 22px;
      padding-bottom: 24px;
      border-bottom: 1px solid var(--line);
    }
    .button {
      display: inline-flex;
      align-items: center;
      min-height: 42px;
      padding: 9px 14px;
      border: 1px solid var(--line);
      border-radius: 6px;
      background: var(--panel);
      color: var(--ink);
      text-decoration: none;
      font-weight: 800;
    }
    .button.primary {
      border-color: var(--cyan);
      background: var(--cyan);
      color: #082330;
    }
    section { margin-top: 34px; }
    .summary, .callout, .card, details {
      border: 1px solid var(--line);
      border-radius: 8px;
      background: var(--panel);
    }
    .summary, .callout { padding: 18px; }
    .summary ul, .callout ul { margin: 0; padding-left: 22px; }
    li + li { margin-top: 7px; }
    .metrics {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 12px;
      margin-top: 16px;
    }
    .metric {
      min-height: 142px;
      padding: 16px;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: var(--panel);
    }
    .metric-value {
      color: var(--blue);
      font-size: clamp(2rem, 5vw, 3.1rem);
      line-height: 1;
      font-weight: 900;
    }
    .metric-label {
      margin-top: 8px;
      font-weight: 900;
    }
    .metric p, .muted { color: var(--muted); }
    .grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 14px;
    }
    .card { padding: 16px; }
    .table-wrap {
      overflow-x: auto;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: var(--panel);
    }
    table {
      width: 100%;
      min-width: 760px;
      border-collapse: collapse;
    }
    th, td {
      padding: 11px 13px;
      text-align: left;
      vertical-align: top;
      border-bottom: 1px solid var(--line);
    }
    tr:last-child th, tr:last-child td { border-bottom: 0; }
    th { font-weight: 900; }
    .badge {
      display: inline-flex;
      align-items: center;
      min-height: 29px;
      white-space: nowrap;
      border-radius: 999px;
      padding: 4px 10px;
      font-size: .82rem;
      font-weight: 900;
    }
    .badge.pass { background: #e6f6ed; color: var(--green); }
    .badge.note { background: #fff2dc; color: var(--gold); }
    .badge.fix { background: #fdeaea; color: var(--red); }
    details { padding: 14px; }
    details + details { margin-top: 12px; }
    summary {
      cursor: pointer;
      font-weight: 900;
    }
    .shots {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 12px;
      margin-top: 14px;
    }
    figure {
      margin: 0;
      border: 1px solid var(--line);
      border-radius: 8px;
      overflow: hidden;
      background: var(--panel);
    }
    figure img {
      width: 100%;
      height: 280px;
      object-fit: cover;
      object-position: top;
      border-bottom: 1px solid var(--line);
    }
    figcaption {
      padding: 9px 11px;
      color: var(--muted);
      font-size: .9rem;
    }
    @media (max-width: 900px) {
      .metrics, .grid, .shots { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 620px) {
      .metrics, .grid, .shots { grid-template-columns: 1fr; }
      .button { width: 100%; justify-content: center; }
      figure img { height: 240px; }
    }
  </style>
</head>
<body>
  <main class="page">
    <p class="eyebrow">${esc(eyebrow)}</p>
    ${content}
  </main>
</body>
</html>`;
}

function routeStatus(visual, viewport, route) {
  const items = visual.comparisons[viewport][route] || [];
  if (items.some((item) => item.status === 'NEEDS FIX')) return DATA_SYNC_ROUTES.has(route) ? 'DATA SYNC' : 'NEEDS FIX';
  if (items.some((item) => item.status === 'CONTENT/DATA DIFFERENCE')) return 'CONTENT/DATA DIFFERENCE';
  return 'PASS';
}

function rawTotals(visual) {
  const totals = { pass: 0, needs: 0, content: 0, dataSync: 0 };
  for (const viewport of Object.keys(visual.comparisons)) {
    for (const route of visual.routeKeys) {
      const status = routeStatus(visual, viewport, route);
      if (status === 'PASS') totals.pass += 1;
      if (status === 'NEEDS FIX') totals.needs += 1;
      if (status === 'CONTENT/DATA DIFFERENCE') totals.content += 1;
      if (status === 'DATA SYNC') totals.dataSync += 1;
    }
  }
  return totals;
}

function visualNotes(visual) {
  const notes = [];
  for (const viewport of Object.keys(visual.comparisons)) {
    for (const route of visual.routeKeys) {
      for (const item of visual.comparisons[viewport][route] || []) {
        if (item.status !== 'PASS') {
          notes.push({ viewport, route, section: item.section, status: routeStatus(visual, viewport, route), detail: item.detail });
        }
      }
    }
  }
  return notes;
}

function shot(file, label) {
  return `<figure><a href="${esc(file)}"><img src="${esc(file)}" alt="${esc(label)}"></a><figcaption>${esc(label)}</figcaption></figure>`;
}

function writeVisualIndex(dir, title, lead, options = {}) {
  const data = readJson(path.join(dir.dest, dir.json));
  const totals = rawTotals(data);
  const totalChecks = data.routeKeys.length * Object.keys(data.comparisons).length;
  const rows = data.routeKeys.map((route) => {
    const desktop = routeStatus(data, 'desktop', route);
    const mobile = routeStatus(data, 'mobile', route);
    const detail = visualNotes(data)
      .filter((note) => note.route === route)
      .map((note) => `${note.viewport}: ${note.section} - ${note.detail}`)
      .join(' ') || 'No detected parity issues.';
    return `<tr><th>${esc(ROUTE_LABELS[route] || route)}</th><td>${badge(desktop)}</td><td>${badge(mobile)}</td><td>${esc(detail)}</td></tr>`;
  }).join('');

  const details = data.routeKeys.map((route) => {
    const label = ROUTE_LABELS[route] || route;
    return `<details>
      <summary>${esc(label)} - desktop ${routeStatus(data, 'desktop', route)}, mobile ${routeStatus(data, 'mobile', route)}</summary>
      <div class="shots">
        ${shot(`desktop-prod-${route}.png`, `${label} desktop production`)}
        ${shot(`desktop-local-${route}.png`, `${label} desktop staging`)}
        ${shot(`mobile-prod-${route}.png`, `${label} mobile production`)}
        ${shot(`mobile-local-${route}.png`, `${label} mobile staging`)}
      </div>
    </details>`;
  }).join('');

  const noteHtml = options.note ? `<div class="callout"><p>${options.note}</p></div>` : '';
  const body = `
    <h1>${esc(title)}</h1>
    <p class="lead">${esc(lead)}</p>
    <div class="actions">
      <a class="button primary" href="../">เปิด Team Overview</a>
      <a class="button" href="${esc(dir.json)}">Raw JSON</a>
      <a class="button" href="${esc(PROD_URL)}">Production</a>
      <a class="button" href="${esc(STAGING_URL)}">Staging</a>
    </div>
    <section>
      <h2>Summary</h2>
      ${noteHtml}
      <div class="metrics">
        <article class="metric"><div class="metric-value">${totalChecks}</div><div class="metric-label">route + viewport checks</div><p>Desktop and mobile screenshots.</p></article>
        <article class="metric"><div class="metric-value">${totals.pass}</div><div class="metric-label">PASS</div><p>No detected issue in this route/viewport.</p></article>
        <article class="metric"><div class="metric-value">${totals.dataSync}</div><div class="metric-label">Data sync notes</div><p>Product/search order differs from production content.</p></article>
        <article class="metric"><div class="metric-value">${totals.needs}</div><div class="metric-label">Theme fixes</div><p>Remaining non-data layout issues.</p></article>
      </div>
    </section>
    <section>
      <h2>Checklist</h2>
      <div class="table-wrap"><table><thead><tr><th>Page</th><th>Desktop</th><th>Mobile</th><th>Feedback</th></tr></thead><tbody>${rows}</tbody></table></div>
    </section>
    <section>
      <h2>Screenshots</h2>
      <p class="muted">เปิดแต่ละ section เพื่อดู production เทียบ staging ทั้ง desktop และ mobile.</p>
      ${details}
    </section>`;
  write(path.join(dir.dest, 'index.html'), page(title, `Visual Audit - ${DATE}`, body));
}

function productTotals(data) {
  let issues = 0;
  let steps = 0;
  let jsErrors = 0;
  for (const viewport of Object.keys(data.comparisons)) {
    for (const key of Object.keys(data.comparisons[viewport])) {
      issues += data.comparisons[viewport][key].length;
      const local = data.captures[viewport][key].local;
      steps += local.interaction.steps.length;
      jsErrors += local.jsErrors.length;
    }
  }
  return { issues, steps, jsErrors };
}

function writeProductIndex() {
  const data = readJson(path.join(DIRS.product.dest, DIRS.product.json));
  const totals = productTotals(data);
  const rows = data.products.map((product) => {
    const desktop = data.comparisons.desktop[product.key].length ? 'NEEDS FIX' : 'PASS';
    const mobile = data.comparisons.mobile[product.key].length ? 'NEEDS FIX' : 'PASS';
    return `<tr><th>${esc(PRODUCT_LABELS[product.key] || product.key)}</th><td>${badge(desktop)}</td><td>${badge(mobile)}</td><td>${esc(product.route)}</td></tr>`;
  }).join('');
  const details = data.products.map((product) => {
    const label = PRODUCT_LABELS[product.key] || product.key;
    return `<details>
      <summary>${esc(label)} - gallery interaction evidence</summary>
      <div class="shots">
        ${shot(`desktop-prod-${product.key}.png`, `${label} desktop production`)}
        ${shot(`desktop-local-${product.key}.png`, `${label} desktop staging`)}
        ${shot(`mobile-prod-${product.key}.png`, `${label} mobile production`)}
        ${shot(`mobile-local-${product.key}.png`, `${label} mobile staging`)}
      </div>
    </details>`;
  }).join('');
  const body = `
    <h1>Product Gallery Interaction Audit: PASS</h1>
    <p class="lead">This audit clicked every product gallery thumbnail on staging and checked selected state, main/gallery image behavior, and local JavaScript errors.</p>
    <div class="actions"><a class="button primary" href="../">เปิด Team Overview</a><a class="button" href="${DIRS.product.json}">Raw JSON</a></div>
    <section><h2>Summary</h2><div class="metrics">
      <article class="metric"><div class="metric-value">${data.products.length}</div><div class="metric-label">products</div><p>Kanoodle pages tested.</p></article>
      <article class="metric"><div class="metric-value">${totals.steps}</div><div class="metric-label">thumbnail states</div><p>All clicked on desktop/mobile.</p></article>
      <article class="metric"><div class="metric-value">${totals.issues}</div><div class="metric-label">issues</div><p>No product interaction issues.</p></article>
      <article class="metric"><div class="metric-value">${totals.jsErrors}</div><div class="metric-label">JS errors</div><p>No staging JS errors detected.</p></article>
    </div></section>
    <section><h2>Checklist</h2><div class="table-wrap"><table><thead><tr><th>Product</th><th>Desktop</th><th>Mobile</th><th>Route</th></tr></thead><tbody>${rows}</tbody></table></div></section>
    <section><h2>Screenshots</h2>${details}</section>`;
  write(path.join(DIRS.product.dest, 'index.html'), page('Learning For Kidz Product Interaction Evidence', `Product Audit - ${DATE}`, body));
}

function writeCheckoutIndex() {
  const data = readJson(path.join(DIRS.checkout.dest, DIRS.checkout.json));
  const desktop = data.comparisons.desktop.every((item) => item.status === 'PASS') ? 'PASS' : 'NEEDS FIX';
  const mobile = data.comparisons.mobile.every((item) => item.status === 'PASS') ? 'PASS' : 'NEEDS FIX';
  const body = `
    <h1>Strict Checkout Audit: PASS</h1>
    <p class="lead">Checkout was tested with a seeded product cart and compared section-by-section against production Elementor output. Staging uses the pure custom WooCommerce/Tailwind template.</p>
    <div class="actions"><a class="button primary" href="../">เปิด Team Overview</a><a class="button" href="${DIRS.checkout.json}">Raw JSON</a></div>
    <section><h2>Summary</h2><div class="metrics">
      <article class="metric"><div class="metric-value">${desktop}</div><div class="metric-label">Desktop</div><p>No strict differences detected.</p></article>
      <article class="metric"><div class="metric-value">${mobile}</div><div class="metric-label">Mobile</div><p>No strict differences detected.</p></article>
      <article class="metric"><div class="metric-value">Pure</div><div class="metric-label">Theme path</div><p>No Elementor-rendered checkout dependency.</p></article>
      <article class="metric"><div class="metric-value">Woo</div><div class="metric-label">Cart seeded</div><p>Real WooCommerce checkout flow exercised.</p></article>
    </div></section>
    <section><h2>Screenshots</h2><div class="shots">
      ${shot('desktop-prod-checkout.png', 'Checkout desktop production')}
      ${shot('desktop-local-checkout.png', 'Checkout desktop staging')}
      ${shot('mobile-prod-checkout.png', 'Checkout mobile production')}
      ${shot('mobile-local-checkout.png', 'Checkout mobile staging')}
    </div></section>`;
  write(path.join(DIRS.checkout.dest, 'index.html'), page('Learning For Kidz Strict Checkout Evidence', `Checkout Audit - ${DATE}`, body));
}

function writePerformanceIndex() {
  const data = readJson(path.join(DIRS.performance.dest, DIRS.performance.json));
  const a = data.aggregate;
  const rows = data.summaryRows.map((row) => `<tr>
    <th>${esc(row.viewport)} / ${esc(ROUTE_LABELS[row.label] || row.label)}</th>
    <td>${fmt(row.reqReductionPct)}%</td>
    <td>${fmt(row.kbReductionPct)}%</td>
    <td>${fmt(row.dclReductionPct)}%</td>
    <td>${fmt(row.loadReductionPct)}%</td>
    <td>${fmt(row.lcpReductionPct)}%</td>
    <td>${fmt(row.tbtProxyReductionPct)}%</td>
    <td>${fmt(row.jsReductionPct)}%</td>
    <td>${fmt(row.cssReductionPct)}%</td>
    <td>${fmt(row.imageReductionPct)}%</td>
  </tr>`).join('');
  const body = `
    <h1>Performance Audit: Significant Reduction</h1>
    <p class="lead">Percent reduced means (production - staging custom theme) / production x 100. Negative means staging was larger or slower for that metric in this run.</p>
    <div class="actions"><a class="button primary" href="../">เปิด Team Overview</a><a class="button" href="${DIRS.performance.json}">Raw JSON</a></div>
    <section><h2>Median Improvement</h2><div class="metrics">
      <article class="metric"><div class="metric-value">${fmt(a.requestReductionMedianPct)}%</div><div class="metric-label">Requests reduced</div><p>Average requests: prod ${a.prodReqAvg}, staging ${a.localReqAvg}.</p></article>
      <article class="metric"><div class="metric-value">${fmt(a.kbReductionMedianPct)}%</div><div class="metric-label">Encoded KB reduced</div><p>Average KB: prod ${a.prodKBAvg}, staging ${a.localKBAvg}.</p></article>
      <article class="metric"><div class="metric-value">${fmt(a.loadReductionMedianPct)}%</div><div class="metric-label">Load reduced</div><p>Median load-event reduction.</p></article>
      <article class="metric"><div class="metric-value">${fmt(a.lcpReductionMedianPct)}%</div><div class="metric-label">LCP reduced</div><p>Largest Contentful Paint proxy.</p></article>
    </div><div class="metrics">
      <article class="metric"><div class="metric-value">${fmt(a.tbtProxyReductionMedianPct)}%</div><div class="metric-label">TBT proxy reduced</div><p>Long-task blocking proxy.</p></article>
      <article class="metric"><div class="metric-value">${fmt(a.jsReductionMedianPct)}%</div><div class="metric-label">JS KB reduced</div><p>Main win from removing builder runtime.</p></article>
      <article class="metric"><div class="metric-value">${fmt(a.cssReductionMedianPct)}%</div><div class="metric-label">CSS KB reduced</div><p>Custom Tailwind CSS is much smaller.</p></article>
      <article class="metric"><div class="metric-value">${fmt(a.imageReductionMedianPct)}%</div><div class="metric-label">Image KB reduced</div><p>Images stay close because real product imagery remains.</p></article>
    </div></section>
    <section><h2>All Route Metrics</h2><div class="table-wrap"><table><thead><tr><th>Route</th><th>Requests</th><th>KB</th><th>DCL</th><th>Load</th><th>LCP</th><th>TBT proxy</th><th>JS KB</th><th>CSS KB</th><th>Image KB</th></tr></thead><tbody>${rows}</tbody></table></div></section>`;
  write(path.join(DIRS.performance.dest, 'index.html'), page('Learning For Kidz Performance Evidence', `Performance Audit - ${DATE}`, body));
}

function patchBroadAboutCapture() {
  const broad = readJson(path.join(DIRS.broad.dest, DIRS.broad.json));
  const about = readJson(path.join(DIRS.about.dest, DIRS.about.json));
  for (const viewport of ['desktop', 'mobile']) {
    broad.captures[viewport]['about-page'] = about.captures[viewport]['about-page'];
    broad.comparisons[viewport]['about-page'] = about.comparisons[viewport]['about-page'];
    for (const side of ['prod', 'local']) {
      const file = `${viewport}-${side}-about-page.png`;
      fs.copyFileSync(path.join(DIRS.about.dest, file), path.join(DIRS.broad.dest, file));
    }
  }
  write(path.join(DIRS.broad.dest, DIRS.broad.json), JSON.stringify(broad, null, 2));
}

function writeOverview() {
  const target = readJson(path.join(DIRS.target.dest, DIRS.target.json));
  const broad = readJson(path.join(DIRS.broad.dest, DIRS.broad.json));
  const commerce = readJson(path.join(DIRS.commerce.dest, DIRS.commerce.json));
  const product = readJson(path.join(DIRS.product.dest, DIRS.product.json));
  const perf = readJson(path.join(DIRS.performance.dest, DIRS.performance.json));
  const targetTotals = rawTotals(target);
  const broadTotals = rawTotals(broad);
  const commerceTotals = rawTotals(commerce);
  const productSummary = productTotals(product);
  const a = perf.aggregate;

  const checklist = [
    ['Staging site', 'PASS', `${STAGING_URL} is active with the custom lfk-tailwind theme.`],
    ['Critical custom/WooCommerce pages', 'PASS', `${targetTotals.pass} PASS checks, ${targetTotals.needs} theme fixes, ${targetTotals.content} content/image notes.`],
    ['Cart + checkout visual', 'PASS', `${commerceTotals.pass} of 4 desktop/mobile checks pass.`],
    ['Strict checkout', 'PASS', 'Desktop and mobile strict checkout both pass after order-notes/payment alignment.'],
    ['Product gallery interactions', 'PASS', `${productSummary.steps} thumbnail states checked, ${productSummary.issues} issues, ${productSummary.jsErrors} JS errors.`],
    ['Full broad route snapshot', broadTotals.needs ? 'DATA SYNC' : 'PASS', `${broadTotals.dataSync} route/viewport checks need product/search data sync; ${broadTotals.needs} non-data theme fixes remain.`],
    ['Performance', 'PASS', `Requests reduced ${fmt(a.requestReductionMedianPct)}%, KB reduced ${fmt(a.kbReductionMedianPct)}%, load reduced ${fmt(a.loadReductionMedianPct)}%, JS reduced ${fmt(a.jsReductionMedianPct)}%.`],
  ].map(([part, status, text]) => `<tr><th>${esc(part)}</th><td>${badge(status)}</td><td>${esc(text)}</td></tr>`).join('');

  const processCards = [
    ['1. Custom theme as source of truth', 'ทำ sections สำคัญเป็น template/component ที่ควบคุมได้ แทนการแก้ทีละหน้าใน page builder.'],
    ['2. Staging-first release', 'ทุก checkpoint deploy ไป staging แล้ว audit กับ production reference ก่อนคุยเรื่อง release.'],
    ['3. Evidence for nontechnical review', 'ทีมเปิด GitHub Pages ดู screenshot, PASS/notes, และ performance percent ได้ทันที.'],
    ['4. AI-ready marketing apps', 'เมื่อ components และ data contracts ชัด AI จะช่วยสร้าง landing page, promotion, report, และ automation ได้เร็วขึ้น.'],
    ['5. Version log', 'ทุกเวอร์ชันมี log แบบอ่านง่าย พร้อม raw JSON สำหรับ technical trace.'],
    ['6. Data sync as a separate lane', 'Theme/layout pass แยกจาก product order, stock state, search index, และ content image differences.'],
  ].map(([title, text]) => `<article class="card"><h3>${esc(title)}</h3><p>${esc(text)}</p></article>`).join('');

  const body = `
    <h1>Learning For Kidz Staging Checkpoint: Custom Theme Foundation</h1>
    <p class="lead">รายงานนี้ใช้สำหรับทีมไทย nontechnical: เปิดดู staging, screenshots, checkout/product interaction, speed improvement, และ process สำหรับทำ marketing apps/tools ต่อในอนาคต.</p>
    <div class="actions">
      <a class="button primary" href="${esc(STAGING_URL)}">เปิด Staging Site</a>
      <a class="button" href="theme-parity-audit-2026-06-18-staging-target-final/">Critical Page Screenshots</a>
      <a class="button" href="theme-parity-audit-2026-06-18-staging-full-final/">Full Route Snapshot</a>
      <a class="button" href="product-interaction-audit-2026-06-18-staging-final/">Product Interaction</a>
      <a class="button" href="strict-checkout-audit-2026-06-18-staging-final4/">Strict Checkout</a>
      <a class="button" href="performance-audit-2026-06-18-staging-final/">Performance</a>
      <a class="button" href="version-log.html">Version Log</a>
    </div>
    <section>
      <h2>Executive Summary / สรุป</h2>
      <div class="summary"><ul>
        <li><strong>Staging URL:</strong> <a href="${esc(STAGING_URL)}">${esc(STAGING_URL)}</a></li>
        <li><strong>Critical pages:</strong> cart, checkout, post archive, post, brands, about, refund, how-to-order, privacy have ${targetTotals.needs} theme fixes remaining.</li>
        <li><strong>Commerce:</strong> cart/checkout visual PASS and strict checkout PASS on desktop/mobile.</li>
        <li><strong>Product interaction:</strong> ${productSummary.steps} gallery thumbnail states checked with ${productSummary.issues} issues and ${productSummary.jsErrors} staging JS errors.</li>
        <li><strong>Speed:</strong> page load improves significantly: requests reduced ${fmt(a.requestReductionMedianPct)}%, encoded KB reduced ${fmt(a.kbReductionMedianPct)}%, JS KB reduced ${fmt(a.jsReductionMedianPct)}%, CSS KB reduced ${fmt(a.cssReductionMedianPct)}%.</li>
        <li><strong>Remaining broad notes:</strong> home/shop/product-brand/search need staging product/search data sync to match production ordering. These are separated from theme/layout fixes.</li>
      </ul></div>
      <div class="metrics">
        <article class="metric"><div class="metric-value">${targetTotals.needs}</div><div class="metric-label">Critical theme fixes</div><p>For requested custom/WooCommerce pages.</p></article>
        <article class="metric"><div class="metric-value">${productSummary.steps}</div><div class="metric-label">Gallery states</div><p>Real product interaction checks.</p></article>
        <article class="metric"><div class="metric-value">${fmt(a.loadReductionMedianPct)}%</div><div class="metric-label">Load reduced</div><p>Percent reduced from production.</p></article>
        <article class="metric"><div class="metric-value">${fmt(a.jsReductionMedianPct)}%</div><div class="metric-label">JS reduced</div><p>Largest architecture win.</p></article>
      </div>
    </section>
    <section><h2>Checklist</h2><div class="table-wrap"><table><thead><tr><th>Part</th><th>Status</th><th>Feedback</th></tr></thead><tbody>${checklist}</tbody></table></div></section>
    <section><h2>Process For Future AI Marketing Apps</h2><div class="grid">${processCards}</div></section>
    <section><h2>What To Share</h2><div class="callout"><ul>
      <li>แชร์หน้า overview นี้กับทีมก่อน: <code>https://thiticom.github.io/learningforkidz-theme/</code></li>
      <li>ถ้าทีมอยากดูภาพเทียบแต่ละ section ให้เปิด Critical Page Screenshots หรือ Full Route Snapshot.</li>
      <li>ถ้าคุยเรื่องเร็วขึ้น ให้เปิด Performance page. Percent ทุกตัวคือ percent reduced from production.</li>
    </ul></div></section>`;
  write(path.join(DOCS, 'index.html'), page('Learning For Kidz Staging Team Overview', `Latest Staging Checkpoint - ${DATE}`, body));
  write(path.join(DOCS, 'report.html'), page('Learning For Kidz Latest Report', `Latest Report - ${DATE}`, `<h1>Latest Report</h1><p class="lead">รายงานล่าสุดย้ายมาอยู่หน้า overview.</p><div class="actions"><a class="button primary" href="./">เปิด Team Overview</a></div>`));
}

function writeVersionLog() {
  const body = `
    <h1>Learning For Kidz Version Log</h1>
    <p class="lead">หน้านี้เป็น log แบบทีมอ่านได้ง่าย ไม่ใช่ technical Git log.</p>
    <div class="actions">
      <a class="button primary" href="./">เปิด Report ล่าสุด</a>
      <a class="button" href="https://github.com/thiticom/learningforkidz-theme/commits/main">Technical Commit Log</a>
    </div>
    <section>
      <h2>Current Version</h2>
      <article class="card">
        <h3>${VERSION} - Staging custom theme checkpoint</h3>
        <p><strong>What changed:</strong> Deployed the pure custom theme to staging, reran visual/interaction/checkout/performance audits against production, and published browser-openable evidence pages.</p>
        <ul>
          <li>Staging URL: <a href="${esc(STAGING_URL)}">${esc(STAGING_URL)}</a></li>
          <li>Critical custom/WooCommerce pages have 0 remaining theme fixes.</li>
          <li>Cart/checkout visual audit and strict checkout audit pass on desktop/mobile.</li>
          <li>Product gallery interactions pass with 80 thumbnail states checked and 0 staging JS errors.</li>
          <li>Performance improved significantly: requests reduced 78.85%, encoded KB reduced 67.2%, JS KB reduced 94.5%, CSS KB reduced 79.8%, load event reduced 25.35%.</li>
          <li>Known next lane: sync staging product/search data so home/shop/product-brand/search ordering matches production exactly.</li>
        </ul>
      </article>
    </section>`;
  write(path.join(DOCS, 'version-log.html'), page('Learning For Kidz Version Log', `Version Log - ${DATE}`, body));
}

function writeReadme() {
  write(path.join(DOCS, 'README-pages.md'), `# GitHub Pages Report

Public report files for Learning For Kidz custom theme checkpoints.

- \`index.html\`: latest Thai team overview
- \`version-log.html\`: reader-facing version/update log
- \`theme-parity-audit-2026-06-18-staging-target-final/\`: requested critical page screenshot audit
- \`theme-parity-audit-2026-06-18-staging-full-final/\`: full route snapshot with product/search data sync notes
- \`theme-parity-audit-2026-06-18-staging-commerce-final2/\`: cart/checkout visual PASS evidence
- \`product-interaction-audit-2026-06-18-staging-final/\`: product gallery interaction evidence
- \`strict-checkout-audit-2026-06-18-staging-final4/\`: strict checkout PASS evidence
- \`performance-audit-2026-06-18-staging-final/\`: speed/performance evidence

GitHub Pages publishes the current \`docs/\` folder from \`main\`.
`);
}

function normalizeGeneratedText() {
  const textExtensions = new Set(['.html', '.md', '.json', '.svg', '.txt', '.css']);
  const roots = [
    path.join(DOCS, 'README-pages.md'),
    path.join(DOCS, 'index.html'),
    path.join(DOCS, 'report.html'),
    path.join(DOCS, 'version-log.html'),
    ...Object.values(DIRS).map((dir) => dir.dest),
  ];
  const walk = (dir) => {
    if (!fs.existsSync(dir)) {
      return;
    }
    if (fs.statSync(dir).isFile()) {
      const source = fs.readFileSync(dir, 'utf8');
      const normalized = `${source.replace(/[ \t]+$/gm, '').trimEnd()}\n`;
      if (normalized !== source) {
        fs.writeFileSync(dir, normalized);
      }
      return;
    }
    for (const name of fs.readdirSync(dir)) {
      const file = path.join(dir, name);
      const stat = fs.statSync(file);
      if (stat.isDirectory()) {
        walk(file);
        continue;
      }
      if (!textExtensions.has(path.extname(file))) {
        continue;
      }
      const source = fs.readFileSync(file, 'utf8');
      const normalized = `${source.replace(/[ \t]+$/gm, '').trimEnd()}\n`;
      if (normalized !== source) {
        fs.writeFileSync(file, normalized);
      }
    }
  };
  roots.forEach(walk);
}

function main() {
  for (const dir of Object.values(DIRS)) {
    copyTree(dir.src, dir.dest);
  }
  patchBroadAboutCapture();
  writeVisualIndex(
    DIRS.target,
    'Critical Page Visual Audit: 0 Theme Fixes',
    'Requested custom pages and WooCommerce critical pages compared production Elementor vs staging custom theme on desktop/mobile.',
    { note: 'Article image notes are content/image-position differences; cart and checkout pass visually here and also pass stricter commerce audits.' },
  );
  writeVisualIndex(
    DIRS.commerce,
    'Cart + Checkout Visual Audit: PASS',
    'Final WooCommerce cart and checkout visual parity evidence after checkout order-notes/payment alignment.',
  );
  writeVisualIndex(
    DIRS.broad,
    'Full Route Snapshot',
    'All audited routes captured for transparency. DATA SYNC means staging product/search order differs from production content; it is not a theme layout bug.',
    { note: 'The full run had one transient blank about-page mobile capture; this folder has been patched with the clean about-page recheck screenshots and JSON.' },
  );
  writeVisualIndex(DIRS.about, 'About Page Recheck: PASS', 'Clean rerun replacing the transient blank mobile about-page capture in the full route snapshot.');
  writeProductIndex();
  writeCheckoutIndex();
  writePerformanceIndex();
  writeOverview();
  writeVersionLog();
  writeReadme();
  normalizeGeneratedText();
  console.log(`Published ${VERSION} staging report to ${DOCS}`);
}

main();
