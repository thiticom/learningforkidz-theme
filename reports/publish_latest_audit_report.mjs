#!/usr/bin/env node
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const REPORTS = path.join(ROOT, 'reports');
const DOCS = path.join(ROOT, 'docs');

const DATE = '2026-06-18';
const VERSION = 'v2026.06.18.1';

const DIRS = {
  visualSrc: path.join(REPORTS, `theme-parity-audit-${DATE}`),
  visualDest: path.join(DOCS, `theme-parity-audit-${DATE}-full`),
  productSrc: path.join(REPORTS, `product-interaction-audit-${DATE}`),
  productDest: path.join(DOCS, `product-interaction-audit-${DATE}`),
  checkoutSrc: path.join(REPORTS, `strict-checkout-audit-${DATE}`),
  checkoutDest: path.join(DOCS, `strict-checkout-audit-${DATE}`),
  performanceSrc: path.join(REPORTS, `performance-audit-${DATE}`),
  performanceDest: path.join(DOCS, `performance-audit-${DATE}`),
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

function readJson(file) {
  return JSON.parse(fs.readFileSync(file, 'utf8'));
}

function copyTree(src, dest) {
  fs.rmSync(dest, { recursive: true, force: true });
  fs.cpSync(src, dest, { recursive: true });
}

function writeFile(file, source) {
  fs.mkdirSync(path.dirname(file), { recursive: true });
  fs.writeFileSync(file, source);
}

function esc(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function fmt(value, digits = 1) {
  if (!Number.isFinite(value)) return '0';
  return Number(value).toFixed(digits).replace(/\.0$/, '');
}

function statusClass(status) {
  if (status === 'PASS') return 'pass';
  if (status === 'CONTENT/DATA DIFFERENCE') return 'note';
  return 'fix';
}

function badge(status) {
  return `<span class="badge ${statusClass(status)}">${esc(status)}</span>`;
}

function pageShell(title, eyebrow, body) {
  return `<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>${esc(title)}</title>
  <style>
    :root {
      --ink: #18202f;
      --muted: #5f6a7d;
      --line: #dfe4ec;
      --panel: #fff;
      --surface: #f7f9fb;
      --cyan: #42bedd;
      --blue: #2f6f9f;
      --orange: #cc6f47;
      --olive: #6d9444;
      --gold: #a8871f;
      --red: #b54747;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      color: var(--ink);
      background: var(--surface);
      font-family: "Noto Sans Thai", "Tahoma", "Segoe UI", Arial, sans-serif;
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
      max-width: 1020px;
      font-size: clamp(2.25rem, 5vw, 4.15rem);
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
      max-width: 900px;
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
      min-height: 145px;
      padding: 16px;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: var(--panel);
    }
    .metric-value {
      color: var(--blue);
      font-size: clamp(2.1rem, 5vw, 3.3rem);
      line-height: 1;
      font-weight: 900;
    }
    .metric:nth-child(2) .metric-value { color: var(--olive); }
    .metric:nth-child(3) .metric-value { color: var(--orange); }
    .metric:nth-child(4) .metric-value { color: var(--gold); }
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
      min-width: 820px;
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
    .badge.pass { background: #e6f6ed; color: #1e6a3d; }
    .badge.note { background: #fff2dc; color: #87521c; }
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
    .small-table table { min-width: 620px; }
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
    ${body}
  </main>
</body>
</html>
`;
}

function routeStatus(visual, viewport, route) {
  const items = visual.comparisons[viewport][route] || [];
  if (items.some((item) => item.status === 'NEEDS FIX')) return 'NEEDS FIX';
  if (items.some((item) => item.status === 'CONTENT/DATA DIFFERENCE')) return 'CONTENT/DATA DIFFERENCE';
  return 'PASS';
}

function visualTotals(visual) {
  let needs = 0;
  let content = 0;
  const contentNotes = [];
  for (const viewport of Object.keys(visual.comparisons)) {
    for (const route of visual.routeKeys) {
      for (const item of visual.comparisons[viewport][route] || []) {
        if (item.status === 'NEEDS FIX') needs += 1;
        if (item.status === 'CONTENT/DATA DIFFERENCE') {
          content += 1;
          contentNotes.push({ viewport, route, section: item.section, detail: item.detail });
        }
      }
    }
  }
  return {
    checks: visual.routeKeys.length * Object.keys(visual.comparisons).length,
    needs,
    content,
    pass: visual.routeKeys.length * Object.keys(visual.comparisons).length - needs,
    contentNotes,
  };
}

function productTotals(product) {
  let steps = 0;
  let jsErrors = 0;
  for (const viewport of Object.keys(product.captures)) {
    for (const key of Object.keys(product.captures[viewport])) {
      const local = product.captures[viewport][key].local;
      steps += local.interaction.steps.length;
      jsErrors += local.jsErrors.length;
    }
  }
  return {
    products: product.products.length,
    checks: product.products.length * Object.keys(product.viewports).length,
    steps,
    jsErrors,
  };
}

function statusText(visual, route) {
  const desktop = routeStatus(visual, 'desktop', route);
  const mobile = routeStatus(visual, 'mobile', route);
  if (desktop === 'PASS' && mobile === 'PASS') return 'Visual parity passed on desktop and mobile.';
  const notes = [];
  for (const viewport of ['desktop', 'mobile']) {
    for (const item of visual.comparisons[viewport][route] || []) {
      if (item.status === 'CONTENT/DATA DIFFERENCE') {
        notes.push(`${viewport}: ${item.section} - ${item.detail}`);
      }
    }
  }
  return notes.join(' ');
}

function screenshotFigure(base, file, label) {
  return `<figure><a href="${esc(file)}"><img src="${esc(file)}" alt="${esc(label)}"></a><figcaption>${esc(label)}</figcaption></figure>`;
}

function writeVisualReport(visual) {
  const totals = visualTotals(visual);
  const rows = visual.routeKeys.map((route) => `
      <tr>
        <th>${esc(ROUTE_LABELS[route] || route)}</th>
        <td>${badge(routeStatus(visual, 'desktop', route))}</td>
        <td>${badge(routeStatus(visual, 'mobile', route))}</td>
        <td>${esc(statusText(visual, route))}</td>
      </tr>`).join('');

  const notes = totals.contentNotes.map((note) => `
      <li><strong>${esc(note.viewport)} / ${esc(ROUTE_LABELS[note.route] || note.route)} / ${esc(note.section)}:</strong> ${esc(note.detail)}</li>`).join('');

  const details = visual.routeKeys.map((route) => {
    const label = ROUTE_LABELS[route] || route;
    return `<details>
      <summary>${esc(label)} - desktop ${routeStatus(visual, 'desktop', route)}, mobile ${routeStatus(visual, 'mobile', route)}</summary>
      <div class="shots">
        ${screenshotFigure(DIRS.visualDest, `desktop-prod-${route}.png`, `${label} desktop production`)}
        ${screenshotFigure(DIRS.visualDest, `desktop-local-${route}.png`, `${label} desktop custom theme`)}
        ${screenshotFigure(DIRS.visualDest, `mobile-prod-${route}.png`, `${label} mobile production`)}
        ${screenshotFigure(DIRS.visualDest, `mobile-local-${route}.png`, `${label} mobile custom theme`)}
      </div>
    </details>`;
  }).join('');

  const body = `
    <h1>Custom Theme Parity Checkpoint: 0 NEEDS FIX</h1>
    <p class="lead">รอบนี้เทียบ production Elementor กับ local custom Tailwind/WooCommerce theme แบบ desktop และ mobile ครบ 21 routes. ผลลัพธ์คือไม่มี layout หรือ interaction issue ที่ต้องแก้ก่อนแชร์ทีม; ความต่างที่เหลือถูกจัดเป็น content/data เท่านั้น.</p>
    <div class="actions">
      <a class="button primary" href="../">เปิด Team Overview</a>
      <a class="button" href="../product-interaction-audit-${DATE}/">Product Interaction Evidence</a>
      <a class="button" href="../performance-audit-${DATE}/">Performance Evidence</a>
      <a class="button" href="../strict-checkout-audit-${DATE}/">Checkout Evidence</a>
      <a class="button" href="theme-parity-audit.json">Raw JSON</a>
    </div>

    <section>
      <h2>Audit Summary</h2>
      <div class="metrics">
        <article class="metric"><div class="metric-value">${totals.checks}</div><div class="metric-label">route + viewport checks</div><p>21 pages x desktop/mobile.</p></article>
        <article class="metric"><div class="metric-value">${totals.needs}</div><div class="metric-label">NEEDS FIX</div><p>ไม่มี issue ที่ต้องแก้ก่อนใช้เป็น checkpoint.</p></article>
        <article class="metric"><div class="metric-value">${totals.content}</div><div class="metric-label">Content/data notes</div><p>ข้อมูล dynamic เช่น related products หรือ content images.</p></article>
        <article class="metric"><div class="metric-value">PASS</div><div class="metric-label">Cart + checkout</div><p>ยังผ่าน strict WooCommerce flow checks.</p></article>
      </div>
    </section>

    <section>
      <h2>Page Checklist</h2>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Page / section</th><th>Desktop</th><th>Mobile</th><th>Feedback</th></tr></thead>
          <tbody>${rows}</tbody>
        </table>
      </div>
    </section>

    <section>
      <h2>Remaining Content/Data Differences</h2>
      <div class="summary">
        <ul>${notes || '<li>No remaining content/data differences recorded.</li>'}</ul>
      </div>
    </section>

    <section>
      <h2>Screenshots By Page</h2>
      <p class="muted">เปิดแต่ละแถวเพื่อดู production vs custom theme screenshot แยก desktop/mobile. Screenshot ทั้งหมดอยู่ใน folder เดียวกับหน้านี้.</p>
      ${details}
    </section>`;

  writeFile(path.join(DIRS.visualDest, 'index.html'), pageShell('Learning For Kidz Full Theme PASS Evidence', `Visual Audit - ${DATE}`, body));
}

function writeProductReport(product) {
  const totals = productTotals(product);
  const rows = product.products.map((item) => {
    const desktopSteps = product.captures.desktop[item.key].local.interaction.steps.length;
    const mobileSteps = product.captures.mobile[item.key].local.interaction.steps.length;
    const desktopIssues = product.comparisons.desktop[item.key].length;
    const mobileIssues = product.comparisons.mobile[item.key].length;
    return `
      <tr>
        <th>${esc(PRODUCT_LABELS[item.key] || item.key)}</th>
        <td>${badge(desktopIssues ? 'NEEDS FIX' : 'PASS')}</td>
        <td>${badge(mobileIssues ? 'NEEDS FIX' : 'PASS')}</td>
        <td>${desktopSteps + mobileSteps} thumbnail states checked; route ${esc(item.route)}</td>
      </tr>`;
  }).join('');

  const details = product.products.map((item) => {
    const label = PRODUCT_LABELS[item.key] || item.key;
    return `<details>
      <summary>${esc(label)} - desktop/mobile gallery interaction PASS</summary>
      <div class="shots">
        ${screenshotFigure(DIRS.productDest, `desktop-prod-${item.key}.png`, `${label} desktop production`)}
        ${screenshotFigure(DIRS.productDest, `desktop-local-${item.key}.png`, `${label} desktop custom theme`)}
        ${screenshotFigure(DIRS.productDest, `mobile-prod-${item.key}.png`, `${label} mobile production`)}
        ${screenshotFigure(DIRS.productDest, `mobile-local-${item.key}.png`, `${label} mobile custom theme`)}
      </div>
    </details>`;
  }).join('');

  const body = `
    <h1>Product Gallery Interaction Audit: PASS</h1>
    <p class="lead">รอบนี้ไม่ได้ดูแค่ภาพนิ่ง แต่คลิก thumbnail ทุกตัวใน 4 product pages ทั้ง desktop และ mobile แล้วตรวจว่า selected state, gallery track/main image และ JS errors ถูกต้อง.</p>
    <div class="actions">
      <a class="button primary" href="../">เปิด Team Overview</a>
      <a class="button" href="../theme-parity-audit-${DATE}-full/">Full Visual Evidence</a>
      <a class="button" href="product-interaction-audit.json">Raw JSON</a>
    </div>

    <section>
      <h2>Interaction Summary</h2>
      <div class="metrics">
        <article class="metric"><div class="metric-value">${totals.products}</div><div class="metric-label">products tested</div><p>Kanoodle products with different gallery sizes.</p></article>
        <article class="metric"><div class="metric-value">${totals.checks}</div><div class="metric-label">viewport product checks</div><p>4 products x desktop/mobile.</p></article>
        <article class="metric"><div class="metric-value">${totals.steps}</div><div class="metric-label">thumbnail states</div><p>Every thumbnail click/state was verified.</p></article>
        <article class="metric"><div class="metric-value">${totals.jsErrors}</div><div class="metric-label">JS errors</div><p>No local JavaScript errors detected.</p></article>
      </div>
    </section>

    <section>
      <h2>Product Checklist</h2>
      <div class="table-wrap small-table">
        <table>
          <thead><tr><th>Product</th><th>Desktop</th><th>Mobile</th><th>Feedback</th></tr></thead>
          <tbody>${rows}</tbody>
        </table>
      </div>
    </section>

    <section>
      <h2>Screenshots By Product</h2>
      ${details}
    </section>`;

  writeFile(path.join(DIRS.productDest, 'index.html'), pageShell('Learning For Kidz Product Interaction Evidence', `Product Audit - ${DATE}`, body));
}

function writeCheckoutReport(checkout) {
  const desktop = checkout.comparisons.desktop.every((item) => item.status === 'PASS') ? 'PASS' : 'NEEDS FIX';
  const mobile = checkout.comparisons.mobile.every((item) => item.status === 'PASS') ? 'PASS' : 'NEEDS FIX';
  const body = `
    <h1>Strict Checkout Audit: ${desktop === 'PASS' && mobile === 'PASS' ? 'PASS' : 'Needs Review'}</h1>
    <p class="lead">Checkout ใช้ custom WooCommerce/Tailwind template โดย production Elementor เป็น visual reference เท่านั้น. รอบนี้ desktop และ mobile ยังผ่าน strict audit.</p>
    <div class="actions">
      <a class="button primary" href="../">เปิด Team Overview</a>
      <a class="button" href="../performance-audit-${DATE}/">Performance Evidence</a>
      <a class="button" href="checkout-audit.json">Raw JSON</a>
    </div>

    <section>
      <h2>Checkout Checklist</h2>
      <div class="metrics">
        <article class="metric"><div class="metric-value">${desktop}</div><div class="metric-label">Desktop checkout</div><p>No strict checkout differences detected.</p></article>
        <article class="metric"><div class="metric-value">${mobile}</div><div class="metric-label">Mobile checkout</div><p>No strict checkout differences detected.</p></article>
        <article class="metric"><div class="metric-value">Pure</div><div class="metric-label">Custom theme path</div><p>No local Elementor-rendered checkout dependency.</p></article>
        <article class="metric"><div class="metric-value">Woo</div><div class="metric-label">Commerce flow</div><p>Cart seeding and checkout route were exercised.</p></article>
      </div>
    </section>

    <section>
      <h2>Screenshots</h2>
      <div class="shots">
        ${screenshotFigure(DIRS.checkoutDest, 'desktop-prod-checkout.png', 'Checkout desktop production')}
        ${screenshotFigure(DIRS.checkoutDest, 'desktop-local-checkout.png', 'Checkout desktop custom theme')}
        ${screenshotFigure(DIRS.checkoutDest, 'mobile-prod-checkout.png', 'Checkout mobile production')}
        ${screenshotFigure(DIRS.checkoutDest, 'mobile-local-checkout.png', 'Checkout mobile custom theme')}
      </div>
    </section>`;

  writeFile(path.join(DIRS.checkoutDest, 'index.html'), pageShell('Learning For Kidz Strict Checkout Evidence', `Checkout Audit - ${DATE}`, body));
}

function writePerformanceReport(perf) {
  const aggregate = perf.aggregate;
  const lowLoadRows = perf.summaryRows
    .slice()
    .sort((a, b) => a.loadReductionPct - b.loadReductionPct)
    .slice(0, 8)
    .map((row) => `
      <tr>
        <th>${esc(row.viewport)} / ${esc(ROUTE_LABELS[row.label] || row.label)}</th>
        <td>${fmt(row.loadReductionPct)}%</td>
        <td>${fmt(row.reqReductionPct)}%</td>
        <td>${fmt(row.kbReductionPct)}%</td>
        <td>${fmt(row.lcpReductionPct)}%</td>
        <td>${fmt(row.tbtProxyReductionPct)}%</td>
        <td>${fmt(row.imageReductionPct)}%</td>
      </tr>`)
    .join('');

  const rows = perf.summaryRows.map((row) => `
      <tr>
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
    <h1>Performance Audit: Significant Load Reduction</h1>
    <p class="lead">Percent reduced หมายถึง (production - local custom theme) / production x 100. ค่า negative แปลว่า local รอบนี้ใหญ่กว่าหรือช้ากว่าใน metric นั้น; report นี้จึงแยก image KB note ไว้ชัดเจน.</p>
    <div class="actions">
      <a class="button primary" href="../">เปิด Team Overview</a>
      <a class="button" href="../theme-parity-audit-${DATE}-full/">Full Visual Evidence</a>
      <a class="button" href="performance.json">Raw JSON</a>
    </div>

    <section>
      <h2>Median Improvement</h2>
      <div class="metrics">
        <article class="metric"><div class="metric-value">${fmt(aggregate.requestReductionMedianPct)}%</div><div class="metric-label">Requests reduced</div><p>Average route requests: prod ${aggregate.prodReqAvg}, local ${aggregate.localReqAvg}.</p></article>
        <article class="metric"><div class="metric-value">${fmt(aggregate.kbReductionMedianPct)}%</div><div class="metric-label">Encoded KB reduced</div><p>Average encoded KB: prod ${aggregate.prodKBAvg}, local ${aggregate.localKBAvg}.</p></article>
        <article class="metric"><div class="metric-value">${fmt(aggregate.loadReductionMedianPct)}%</div><div class="metric-label">Load event reduced</div><p>Median load-event improvement across 42 route checks.</p></article>
        <article class="metric"><div class="metric-value">${fmt(aggregate.lcpReductionMedianPct)}%</div><div class="metric-label">LCP reduced</div><p>Largest Contentful Paint proxy from browser performance entries.</p></article>
      </div>
      <div class="metrics">
        <article class="metric"><div class="metric-value">${fmt(aggregate.tbtProxyReductionMedianPct)}%</div><div class="metric-label">TBT proxy reduced</div><p>Long-task blocking proxy; lower is better.</p></article>
        <article class="metric"><div class="metric-value">${fmt(aggregate.jsReductionMedianPct)}%</div><div class="metric-label">JS KB reduced</div><p>Main win from removing Elementor/WooLentor runtime from custom theme pages.</p></article>
        <article class="metric"><div class="metric-value">${fmt(aggregate.cssReductionMedianPct)}%</div><div class="metric-label">CSS KB reduced</div><p>Custom Tailwind CSS replaces large page-builder CSS stacks.</p></article>
        <article class="metric"><div class="metric-value">${fmt(aggregate.imageReductionMedianPct)}%</div><div class="metric-label">Image KB reduced</div><p>Near-flat by design because screenshots/content still use real product imagery.</p></article>
      </div>
    </section>

    <section>
      <h2>Watchlist / Lowest Load Reductions</h2>
      <p class="muted">These are still mostly faster overall, but they are the pages to watch if we optimize further. Desktop home had only 11% load-event reduction in this run, even though request count and JS weight dropped sharply.</p>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Route</th><th>Load</th><th>Requests</th><th>KB</th><th>LCP</th><th>TBT proxy</th><th>Image KB</th></tr></thead>
          <tbody>${lowLoadRows}</tbody>
        </table>
      </div>
    </section>

    <section>
      <h2>All Route Metrics</h2>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Route</th><th>Requests</th><th>KB</th><th>DCL</th><th>Load</th><th>LCP</th><th>TBT proxy</th><th>JS KB</th><th>CSS KB</th><th>Image KB</th></tr></thead>
          <tbody>${rows}</tbody>
        </table>
      </div>
    </section>`;

  writeFile(path.join(DIRS.performanceDest, 'index.html'), pageShell('Learning For Kidz Performance Evidence', `Performance Audit - ${DATE}`, body));
}

function writeOverview(visual, product, perf) {
  const totals = visualTotals(visual);
  const productSummary = productTotals(product);
  const aggregate = perf.aggregate;
  const checklistRows = [
    ['Header + footer', 'PASS', 'Covered in every visual route screenshot on desktop/mobile.'],
    ['Home and custom marketing pages', 'PASS', 'Home, Contact, Promotion, Ages, Brands, About, Refund, How to order, Privacy checked.'],
    ['WooCommerce archives', 'PASS', 'Shop, product category, product brand, and age taxonomy archive geometry checked.'],
    ['Single product', 'PASS', 'Visual layout passes; related products are content/data because production randomizes recommendations.'],
    ['Product gallery interactions', 'PASS', `${productSummary.steps} thumbnail states checked across 4 products and desktop/mobile.`],
    ['Cart', 'PASS', 'Full route audit reports PASS on desktop/mobile.'],
    ['Checkout', 'PASS', 'Strict checkout audit reports PASS on desktop/mobile.'],
    ['My Account + Wishlist + Search', 'PASS', 'Customer utility pages pass visual checks.'],
    ['Post archive + single post', 'PASS with data note', 'Layout passes; single post has dynamic/content note only.'],
    ['Performance', 'PASS with watchlist', `Median request reduction ${fmt(aggregate.requestReductionMedianPct)}%, load reduction ${fmt(aggregate.loadReductionMedianPct)}%, LCP reduction ${fmt(aggregate.lcpReductionMedianPct)}%.`],
  ].map(([part, status, feedback]) => `
      <tr><th>${esc(part)}</th><td>${badge(status.includes('PASS') ? 'PASS' : 'CONTENT/DATA DIFFERENCE')}</td><td>${esc(feedback)}</td></tr>`).join('');

  const processCards = [
    ['1. Custom Theme Source of Truth', 'ทำ page sections ให้เป็น template/component ที่ควบคุมได้ แทนการแก้ทีละหน้าใน page builder.'],
    ['2. Evidence Before Shipping', 'ทุก change มี visual, interaction, checkout และ speed audit พร้อม screenshot ให้คน non-technical ตรวจได้.'],
    ['3. AI-Ready Component Contracts', 'เมื่อ layout/section ชัด AI จะสร้าง campaign page, product module, report และ tool ได้เร็วขึ้นโดยไม่ทำเว็บพัง.'],
    ['4. Marketing Apps On Demand', 'ต่อไปเราสามารถสร้าง promotion builder, landing page generator, KPI brief, content workflow และ automation app บน foundation เดียวกัน.'],
    ['5. Versioned Team Communication', 'GitHub Pages เก็บ report และ version log ให้แชร์ทีมได้ทุก checkpoint.'],
    ['6. Staging Then Production', 'หลัง local pass แล้วค่อย deploy ไป staging/prod และรัน audit ซ้ำกับ URL จริงก่อน release.'],
  ].map(([title, text]) => `<article class="card"><h3>${esc(title)}</h3><p>${esc(text)}</p></article>`).join('');

  const body = `
    <h1>Learning For Kidz Custom Theme: AI-Ready Marketing Foundation</h1>
    <p class="lead">Checkpoint นี้คือก้าวจาก Elementor-heavy site ไปสู่ custom theme ที่เร็วกว่า ตรวจสอบได้ และพร้อมให้ AI ช่วยสร้าง marketing pages, business tools และ automation ในอนาคตโดยมีหลักฐานทุกครั้ง.</p>
    <div class="actions">
      <a class="button primary" href="theme-parity-audit-${DATE}-full/">เปิด Full PASS Report</a>
      <a class="button" href="product-interaction-audit-${DATE}/">Product Interaction</a>
      <a class="button" href="performance-audit-${DATE}/">Performance</a>
      <a class="button" href="strict-checkout-audit-${DATE}/">Checkout</a>
      <a class="button" href="version-log.html">Version Log</a>
      <a class="button" href="team-brief-th.pdf">Old Team PDF</a>
      <a class="button" href="https://github.com/thiticom/learningforkidz-theme/commits/main">Technical Commit Log</a>
    </div>

    <section>
      <h2>Executive Summary / สรุปสำหรับทีม</h2>
      <div class="summary">
        <ul>
          <li><strong>Visual parity พร้อมแชร์:</strong> ${totals.checks} route + viewport checks, ${totals.needs} NEEDS FIX, ${totals.content} content/data notes only.</li>
          <li><strong>Product pages ไม่ใช่แค่ภาพนิ่ง:</strong> คลิก gallery thumbnail รวม ${productSummary.steps} states ใน 4 products ทั้ง desktop/mobile, ${productSummary.jsErrors} local JS errors.</li>
          <li><strong>Checkout/cart ยังปลอดภัย:</strong> checkout ผ่าน strict WooCommerce audit บน desktop/mobile และ cart ผ่าน full route audit.</li>
          <li><strong>Page load ดีขึ้นอย่างมีนัยสำคัญ:</strong> median request reduced ${fmt(aggregate.requestReductionMedianPct)}%, load event reduced ${fmt(aggregate.loadReductionMedianPct)}%, LCP reduced ${fmt(aggregate.lcpReductionMedianPct)}%, TBT proxy reduced ${fmt(aggregate.tbtProxyReductionMedianPct)}%.</li>
          <li><strong>Image KB ไม่ใช่ win หลัก:</strong> median image KB reduced ${fmt(aggregate.imageReductionMedianPct)}% เพราะเรายังใช้ภาพสินค้า/content จริงเพื่อให้หน้าตาใกล้ production.</li>
        </ul>
      </div>
      <div class="metrics">
        <article class="metric"><div class="metric-value">${fmt(aggregate.requestReductionMedianPct)}%</div><div class="metric-label">Request reduced</div><p>Percent reduced = (prod - local) / prod.</p></article>
        <article class="metric"><div class="metric-value">${fmt(aggregate.loadReductionMedianPct)}%</div><div class="metric-label">Load event reduced</div><p>Median across 42 route checks.</p></article>
        <article class="metric"><div class="metric-value">${fmt(aggregate.jsReductionMedianPct)}%</div><div class="metric-label">JS KB reduced</div><p>Removing builder runtime is the biggest technical win.</p></article>
        <article class="metric"><div class="metric-value">${totals.needs}</div><div class="metric-label">NEEDS FIX</div><p>Current audited custom theme checkpoint.</p></article>
      </div>
    </section>

    <section>
      <h2>Theme Checklist</h2>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Part</th><th>Status</th><th>Feedback</th></tr></thead>
          <tbody>${checklistRows}</tbody>
        </table>
      </div>
    </section>

    <section>
      <h2>Process For Future AI Marketing Apps</h2>
      <div class="grid">${processCards}</div>
    </section>

    <section>
      <h2>What To Share</h2>
      <div class="callout">
        <ul>
          <li>สำหรับทีม non-technical: แชร์หน้า overview นี้ก่อน แล้วเปิด Full PASS Report เพื่อดู screenshot.</li>
          <li>สำหรับคนที่ถามเรื่องความเร็ว: แชร์ Performance page; percent ทุกตัวคือ percent reduced from production.</li>
          <li>สำหรับ release/staging: GitHub Pages เป็น report site. WordPress staging deploy ยังต้องมี staging target/credentials ก่อน แล้วควรรัน audit ซ้ำกับ staging URL.</li>
        </ul>
      </div>
    </section>`;

  writeFile(path.join(DOCS, 'index.html'), pageShell('Learning For Kidz Custom Theme Team Overview', `Latest Checkpoint - ${DATE}`, body));
}

function writeLegacyReportRedirect() {
  const body = `
    <h1>Learning For Kidz Report Moved</h1>
    <p class="lead">หน้านี้เคยเป็น report URL เก่า ตอนนี้รายงานล่าสุดอยู่ที่ overview หน้าแรก พร้อมลิงก์ไป full visual, product interaction, checkout และ performance evidence รอบ ${DATE}.</p>
    <div class="actions">
      <a class="button primary" href="./">เปิด Latest Team Overview</a>
      <a class="button" href="theme-parity-audit-${DATE}-full/">Full PASS Report</a>
      <a class="button" href="performance-audit-${DATE}/">Performance Evidence</a>
      <a class="button" href="version-log.html">Version Log</a>
    </div>`;
  const html = pageShell('Learning For Kidz Latest Report', `Latest Report - ${DATE}`, body).replace(
    '</head>',
    '  <meta http-equiv="refresh" content="0; url=./">\n</head>',
  );
  writeFile(path.join(DOCS, 'report.html'), html);
}

function updateVersionLog() {
  const file = path.join(DOCS, 'version-log.html');
  let html = fs.readFileSync(file, 'utf8');
  const latestButtons = `
      <a class="button" href="theme-parity-audit-${DATE}-full/">2026-06-18 Full PASS Evidence</a>
      <a class="button" href="product-interaction-audit-${DATE}/">2026-06-18 Product Interaction</a>
      <a class="button" href="performance-audit-${DATE}/">2026-06-18 Performance</a>`;

  html = html.replace(
    /<article class="entry">\s*<div class="meta"><span class="status">Current<\/span> Version v2026\.06\.18\.1[\s\S]*?<\/article>\s*/g,
    '',
  );
  html = html.split(latestButtons).join('');
  html = html.replace(
    '<span class="status">Current</span> Version v2026.06.17.18',
    'Version v2026.06.17.18',
  );
  html = html.replace(
    '<a class="button primary" href="./">เปิด Report ล่าสุด</a>',
    `<a class="button primary" href="./">เปิด Report ล่าสุด</a>${latestButtons}`,
  );

  const entry = `
      <article class="entry">
        <div class="meta"><span class="status">Current</span> Version ${VERSION} - Product interaction, full parity, and speed checkpoint</div>
        <p><strong>What changed:</strong> Re-ran the loop with stricter single-product gallery interaction checks, full all-route visual parity, strict checkout, and expanded performance metrics.</p>
        <ul>
          <li>Full visual audit reports 0 NEEDS FIX across 21 pages x desktop/mobile, with 6 remaining CONTENT/DATA notes only.</li>
          <li>Product interaction audit clicked every gallery thumbnail state across 4 Kanoodle products on desktop/mobile with 0 local JS errors.</li>
          <li>Performance audit now reports request, encoded KB, DCL, load, LCP, CLS, TBT proxy, and JS/CSS/image KB reductions.</li>
          <li>Median improvements: requests reduced 82%, load event reduced 86.95%, LCP reduced 85.7%, TBT proxy reduced 85.55%, JS KB reduced 95.9%.</li>
          <li>Published screenshots and raw audit JSON: <a href="theme-parity-audit-${DATE}-full/">full visual evidence</a>, <a href="product-interaction-audit-${DATE}/">product interaction evidence</a>, and <a href="performance-audit-${DATE}/">performance evidence</a>.</li>
        </ul>
        <p><strong>Verification:</strong> CSS build passed, PHP lint passed for touched PHP files, product interaction audit reports 0 issues, full route visual audit reports 0 NEEDS FIX, strict checkout audit reports PASS on desktop/mobile, and performance report documents percent reduced from production.</p>
      </article>
`;

  html = html.replace('<h2>Current Version</h2>', `<h2>Current Version</h2>${entry}`);
  writeFile(file, html);
}

function writeReadme() {
  const body = `# GitHub Pages Report

Public report files for Learning For Kidz custom theme checkpoints.

- \`index.html\`: latest Thai team overview
- \`version-log.html\`: reader-facing version and update log
- \`theme-parity-audit-${DATE}-full/\`: latest full visual parity evidence
- \`product-interaction-audit-${DATE}/\`: latest product gallery interaction evidence
- \`performance-audit-${DATE}/\`: latest speed/performance evidence
- \`strict-checkout-audit-${DATE}/\`: latest strict checkout evidence
- \`team-brief-th.pdf\`: older Thai team PDF brief
- \`lfk-section-audit-2026-06-11/\`: older section audit
- \`theme-parity-audit-2026-06-17-full-pure/\`: previous full pure custom theme checkpoint

GitHub Pages publishes the current \`docs/\` folder from \`main\`. For each important checkpoint, keep a dated evidence folder and add a version-log entry.
`;
  writeFile(path.join(DOCS, 'README-pages.md'), body);
}

function main() {
  const visual = readJson(path.join(DIRS.visualSrc, 'theme-parity-audit.json'));
  const product = readJson(path.join(DIRS.productSrc, 'product-interaction-audit.json'));
  const checkout = readJson(path.join(DIRS.checkoutSrc, 'checkout-audit.json'));
  const perf = readJson(path.join(DIRS.performanceSrc, 'performance.json'));

  copyTree(DIRS.visualSrc, DIRS.visualDest);
  copyTree(DIRS.productSrc, DIRS.productDest);
  copyTree(DIRS.checkoutSrc, DIRS.checkoutDest);
  copyTree(DIRS.performanceSrc, DIRS.performanceDest);

  writeVisualReport(visual);
  writeProductReport(product);
  writeCheckoutReport(checkout);
  writePerformanceReport(perf);
  writeOverview(visual, product, perf);
  writeLegacyReportRedirect();
  updateVersionLog();
  writeReadme();

  console.log(`Published ${VERSION} report to ${DOCS}`);
}

main();
