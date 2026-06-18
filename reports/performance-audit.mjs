import fs from 'node:fs/promises';
import path from 'node:path';
import { chromium } from 'playwright';

const ROOT = process.cwd();
const DATE = new Date().toISOString().slice(0, 10);
const OUT_DIR = path.join(ROOT, 'reports', `performance-audit-${DATE}`);
const PRODUCT_ID = process.env.LFK_AUDIT_PRODUCT_ID || '32848';
const NAVIGATION_TIMEOUT_MS = Number(process.env.LFK_AUDIT_NAV_TIMEOUT_MS || 120000);

const BASES = {
  prod: 'https://www.learningforkidz.com',
  local: 'http://100.109.57.34:8085',
};

const VIEWPORTS = {
  desktop: { width: 1365, height: 1400, isMobile: false, deviceScaleFactor: 1 },
  mobile: { width: 390, height: 1200, isMobile: true, deviceScaleFactor: 2 },
};

const ROUTES = {
  home: '/',
  shop: '/shop/',
  'product-category': '/product-category/series/fine-motor/',
  'product-brand': '/product-brand/learning-resources/',
  'age-taxonomy': '/age/3-4-years/',
  'single-product': '/product/kanoodle-20th-anniversary-magenta/',
  'article-archive': '/article/',
  'single-post': '/%e0%b8%87%e0%b8%b2%e0%b8%99-alpha-skills-summit-expo-2026/',
  search: '/?s=kanoodle',
  cart: '/cart/',
  checkout: '/checkout/',
  'my-account': '/my-account/',
  wishlist: '/wishlists/',
  contact: '/contact-us/',
  promotion: '/promotion/',
  'ages-page': '/ages/',
  'brands-page': '/brands/',
  'about-page': '/about-us/',
  'refund-page': '/refund/',
  'how-to-orders': '/how-to-orders/',
  'privacy-page': '/privacy-policy/',
};

const requestedRoutes = (process.env.LFK_PARITY_ROUTES || '')
  .split(',')
  .map((route) => route.trim())
  .filter(Boolean);
const ROUTE_KEYS = requestedRoutes.length ? requestedRoutes : Object.keys(ROUTES);

function round(value, digits = 1) {
  const factor = 10 ** digits;
  return Math.round(value * factor) / factor;
}

function median(values) {
  const sorted = values.filter((value) => Number.isFinite(value)).slice().sort((a, b) => a - b);
  if (!sorted.length) return 0;
  const middle = Math.floor(sorted.length / 2);
  return sorted.length % 2 ? sorted[middle] : round((sorted[middle - 1] + sorted[middle]) / 2, 2);
}

function pctReduced(prodValue, localValue) {
  if (!Number.isFinite(prodValue) || !Number.isFinite(localValue) || prodValue <= 0) return 0;
  return round(((prodValue - localValue) / prodValue) * 100, 1);
}

async function gotoRoute(page, base, routeKey) {
  if (routeKey === 'cart' || routeKey === 'checkout') {
    await page.goto(`${base}/cart/?add-to-cart=${encodeURIComponent(PRODUCT_ID)}`, {
      waitUntil: 'domcontentloaded',
      timeout: NAVIGATION_TIMEOUT_MS,
    });
    await page.waitForTimeout(1800);
  }

  const response = await page.goto(`${base}${ROUTES[routeKey]}`, {
    waitUntil: 'domcontentloaded',
    timeout: NAVIGATION_TIMEOUT_MS,
  });
  await page.waitForLoadState('networkidle', { timeout: 15000 }).catch(() => {});
  await page.waitForTimeout(1000);
  return response;
}

async function capturePerformance(browser, viewportName, viewport, target, routeKey) {
  const context = await browser.newContext({
    viewport: { width: viewport.width, height: viewport.height },
    isMobile: viewport.isMobile,
    deviceScaleFactor: viewport.deviceScaleFactor,
    ignoreHTTPSErrors: true,
  });
  await context.addInitScript(() => {
    window.__lfkVitals = {
      cls: 0,
      lcpMs: 0,
      tbtProxyMs: 0,
      maxEventDurationMs: 0,
    };

    const observe = (type, handler, options = { type, buffered: true }) => {
      try {
        const observer = new PerformanceObserver((list) => {
          list.getEntries().forEach(handler);
        });
        observer.observe(options);
      } catch {
        // Some browsers/contexts do not expose every web-vital entry type.
      }
    };

    observe('largest-contentful-paint', (entry) => {
      window.__lfkVitals.lcpMs = Math.round(entry.startTime || 0);
    });
    observe('layout-shift', (entry) => {
      if (!entry.hadRecentInput) {
        window.__lfkVitals.cls += entry.value || 0;
      }
    });
    observe('longtask', (entry) => {
      window.__lfkVitals.tbtProxyMs += Math.max(0, (entry.duration || 0) - 50);
    });
    observe('event', (entry) => {
      window.__lfkVitals.maxEventDurationMs = Math.max(
        window.__lfkVitals.maxEventDurationMs,
        entry.duration || 0,
      );
    }, { type: 'event', buffered: true, durationThreshold: 16 });
  });
  const page = await context.newPage();
  const cdp = await context.newCDPSession(page);
  await cdp.send('Network.enable');
  await cdp.send('Network.setCacheDisabled', { cacheDisabled: true });

  const requests = new Map();
  cdp.on('Network.responseReceived', (event) => {
    requests.set(event.requestId, {
      url: event.response.url,
      status: event.response.status,
      type: event.type,
      encodedBytes: 0,
    });
  });
  cdp.on('Network.loadingFinished', (event) => {
    const request = requests.get(event.requestId);
    if (request) {
      request.encodedBytes = event.encodedDataLength || 0;
    }
  });

  const started = Date.now();
  let status = 0;
  let error = '';
  try {
    const response = await gotoRoute(page, BASES[target], routeKey);
    status = response?.status() || 0;
  } catch (err) {
    error = err.message;
  }
  const wallMs = Date.now() - started;

  const perf = await page.evaluate(() => {
    const navigation = performance.getEntriesByType('navigation')[0];
    const resources = performance.getEntriesByType('resource');
    const vitals = window.__lfkVitals || {};
    const byInitiator = {};
    let transferSize = 0;
    let encodedBodySize = 0;
    let decodedBodySize = 0;
    resources.forEach((entry) => {
      byInitiator[entry.initiatorType] = (byInitiator[entry.initiatorType] || 0) + 1;
      transferSize += entry.transferSize || 0;
      encodedBodySize += entry.encodedBodySize || 0;
      decodedBodySize += entry.decodedBodySize || 0;
    });
    return {
      domContentLoadedMs: navigation ? Math.round(navigation.domContentLoadedEventEnd) : 0,
      loadMs: navigation ? Math.round(navigation.loadEventEnd) : 0,
      resourceEntries: resources.length,
      transferSize,
      encodedBodySize,
      decodedBodySize,
      lcpMs: Math.round(vitals.lcpMs || 0),
      cls: Math.round((vitals.cls || 0) * 1000) / 1000,
      tbtProxyMs: Math.round(vitals.tbtProxyMs || 0),
      maxEventDurationMs: Math.round(vitals.maxEventDurationMs || 0),
      byInitiator,
    };
  }).catch(() => ({
    domContentLoadedMs: 0,
    loadMs: 0,
    resourceEntries: 0,
    transferSize: 0,
    encodedBodySize: 0,
    decodedBodySize: 0,
    lcpMs: 0,
    cls: 0,
    tbtProxyMs: 0,
    maxEventDurationMs: 0,
    byInitiator: {},
  }));

  const requestList = [...requests.values()];
  const requestCount = requestList.length;
  const encodedBytes = requestList.reduce((sum, request) => sum + request.encodedBytes, 0);
  const byResourceType = requestList.reduce((acc, request) => {
    acc[request.type] = (acc[request.type] || 0) + 1;
    return acc;
  }, {});
  const byResourceTypeBytes = requestList.reduce((acc, request) => {
    acc[request.type] = (acc[request.type] || 0) + request.encodedBytes;
    return acc;
  }, {});
  const resourceKB = (type) => Math.round((byResourceTypeBytes[type] || 0) / 1024);

  await context.close();

  return {
    target,
    viewport: viewportName,
    label: routeKey,
    url: `${BASES[target]}${ROUTES[routeKey]}`,
    status,
    error,
    wallMs,
    requestCount,
    encodedBytes,
    encodedKB: Math.round(encodedBytes / 1024),
    transferKB: Math.round((perf.transferSize || encodedBytes) / 1024),
    decodedKB: Math.round((perf.decodedBodySize || encodedBytes) / 1024),
    domContentLoadedMs: perf.domContentLoadedMs,
    loadMs: perf.loadMs,
    lcpMs: perf.lcpMs,
    cls: perf.cls,
    tbtProxyMs: perf.tbtProxyMs,
    maxEventDurationMs: perf.maxEventDurationMs,
    resourceEntries: perf.resourceEntries,
    byResourceType,
    byResourceTypeBytes,
    scriptKB: resourceKB('Script'),
    cssKB: resourceKB('Stylesheet'),
    imageKB: resourceKB('Image'),
    fontKB: resourceKB('Font'),
    byInitiator: perf.byInitiator,
  };
}

function makeSummaryRow(viewportName, routeKey, prod, local) {
  return {
    viewport: viewportName,
    label: routeKey,
    prodReq: prod.requestCount,
    localReq: local.requestCount,
    reqReductionPct: pctReduced(prod.requestCount, local.requestCount),
    prodKB: prod.encodedKB,
    localKB: local.encodedKB,
    kbReductionPct: pctReduced(prod.encodedKB, local.encodedKB),
    prodDCL: prod.domContentLoadedMs,
    localDCL: local.domContentLoadedMs,
    dclReductionPct: pctReduced(prod.domContentLoadedMs, local.domContentLoadedMs),
    prodLoad: prod.loadMs,
    localLoad: local.loadMs,
    loadReductionPct: pctReduced(prod.loadMs, local.loadMs),
    prodLCP: prod.lcpMs,
    localLCP: local.lcpMs,
    lcpReductionPct: pctReduced(prod.lcpMs, local.lcpMs),
    prodCLS: prod.cls,
    localCLS: local.cls,
    clsReductionPct: pctReduced(prod.cls, local.cls),
    prodTBTProxy: prod.tbtProxyMs,
    localTBTProxy: local.tbtProxyMs,
    tbtProxyReductionPct: pctReduced(prod.tbtProxyMs, local.tbtProxyMs),
    prodJSKB: prod.scriptKB,
    localJSKB: local.scriptKB,
    jsReductionPct: pctReduced(prod.scriptKB, local.scriptKB),
    prodCSSKB: prod.cssKB,
    localCSSKB: local.cssKB,
    cssReductionPct: pctReduced(prod.cssKB, local.cssKB),
    prodImageKB: prod.imageKB,
    localImageKB: local.imageKB,
    imageReductionPct: pctReduced(prod.imageKB, local.imageKB),
    prodWall: prod.wallMs,
    localWall: local.wallMs,
    wallReductionPct: pctReduced(prod.wallMs, local.wallMs),
  };
}

async function writeReadme(result) {
  const lines = [
    '# Performance Audit',
    '',
    `Created: ${result.createdAt}`,
    '',
    'Percentages mean percent reduced from production: `(prod - local) / prod * 100`.',
    '',
    '## Aggregate',
    '',
    `- Median request reduction: ${result.aggregate.requestReductionMedianPct}%`,
    `- Median encoded KB reduction: ${result.aggregate.kbReductionMedianPct}%`,
    `- Median DOMContentLoaded reduction: ${result.aggregate.dclReductionMedianPct}%`,
    `- Median load event reduction: ${result.aggregate.loadReductionMedianPct}%`,
    `- Median LCP reduction: ${result.aggregate.lcpReductionMedianPct}%`,
    `- Median TBT proxy reduction: ${result.aggregate.tbtProxyReductionMedianPct}%`,
    `- Median JS KB reduction: ${result.aggregate.jsReductionMedianPct}%`,
    `- Median CSS KB reduction: ${result.aggregate.cssReductionMedianPct}%`,
    `- Median image KB reduction: ${result.aggregate.imageReductionMedianPct}%`,
    '',
    '## Routes',
    '',
  ];
  result.summaryRows.forEach((row) => {
    lines.push(`- ${row.viewport}/${row.label}: requests ${row.reqReductionPct}%, KB ${row.kbReductionPct}%, DCL ${row.dclReductionPct}%, load ${row.loadReductionPct}%, LCP ${row.lcpReductionPct}%, TBT proxy ${row.tbtProxyReductionPct}%`);
  });
  await fs.writeFile(path.join(OUT_DIR, 'README.md'), `${lines.join('\n')}\n`);
}

async function run() {
  await fs.mkdir(OUT_DIR, { recursive: true });
  const browser = await chromium.launch({ headless: true });
  const pages = {};
  const summaryRows = [];

  for (const [viewportName, viewport] of Object.entries(VIEWPORTS)) {
    pages[viewportName] = {};
    for (const routeKey of ROUTE_KEYS) {
      pages[viewportName][routeKey] = {};
      for (const target of Object.keys(BASES)) {
        console.error(`measuring ${viewportName}/${routeKey}/${target}`);
        pages[viewportName][routeKey][target] = await capturePerformance(browser, viewportName, viewport, target, routeKey);
      }
      summaryRows.push(makeSummaryRow(
        viewportName,
        routeKey,
        pages[viewportName][routeKey].prod,
        pages[viewportName][routeKey].local,
      ));
    }
  }

  await browser.close();

  const aggregate = {
    requestReductionMedianPct: median(summaryRows.map((row) => row.reqReductionPct)),
    kbReductionMedianPct: median(summaryRows.map((row) => row.kbReductionPct)),
    dclReductionMedianPct: median(summaryRows.map((row) => row.dclReductionPct)),
    loadReductionMedianPct: median(summaryRows.map((row) => row.loadReductionPct)),
    lcpReductionMedianPct: median(summaryRows.map((row) => row.lcpReductionPct)),
    clsReductionMedianPct: median(summaryRows.map((row) => row.clsReductionPct)),
    tbtProxyReductionMedianPct: median(summaryRows.map((row) => row.tbtProxyReductionPct)),
    jsReductionMedianPct: median(summaryRows.map((row) => row.jsReductionPct)),
    cssReductionMedianPct: median(summaryRows.map((row) => row.cssReductionPct)),
    imageReductionMedianPct: median(summaryRows.map((row) => row.imageReductionPct)),
    wallReductionMedianPct: median(summaryRows.map((row) => row.wallReductionPct)),
    prodReqAvg: Math.round(summaryRows.reduce((sum, row) => sum + row.prodReq, 0) / summaryRows.length),
    localReqAvg: Math.round(summaryRows.reduce((sum, row) => sum + row.localReq, 0) / summaryRows.length),
    prodKBAvg: Math.round(summaryRows.reduce((sum, row) => sum + row.prodKB, 0) / summaryRows.length),
    localKBAvg: Math.round(summaryRows.reduce((sum, row) => sum + row.localKB, 0) / summaryRows.length),
  };

  const result = {
    createdAt: new Date().toISOString(),
    note: 'Percentages mean percent reduced from production. Negative values mean local was slower/larger for that metric in this run.',
    prodBase: BASES.prod,
    localBase: BASES.local,
    viewports: VIEWPORTS,
    routeKeys: ROUTE_KEYS,
    pages,
    summaryRows,
    aggregate,
  };

  await fs.writeFile(path.join(OUT_DIR, 'performance.json'), JSON.stringify(result, null, 2));
  await writeReadme(result);
  console.log(JSON.stringify({ outDir: OUT_DIR, aggregate }, null, 2));
}

run().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
