import fs from 'node:fs/promises';
import path from 'node:path';
import { chromium } from 'playwright';

const ROOT = process.cwd();
const DATE = new Date().toISOString().slice(0, 10);
const OUT_DIR = path.join(ROOT, 'reports', `product-interaction-audit-${DATE}`);
const NAVIGATION_TIMEOUT_MS = Number(process.env.LFK_AUDIT_NAV_TIMEOUT_MS || 120000);

const BASES = {
  prod: 'https://www.learningforkidz.com',
  local: 'http://100.109.57.34:8085',
};

const VIEWPORTS = {
  desktop: { width: 1365, height: 1400, isMobile: false, deviceScaleFactor: 1 },
  mobile: { width: 390, height: 1200, isMobile: true, deviceScaleFactor: 2 },
};

const PRODUCTS = [
  { key: 'kanoodle-magenta', route: '/product/kanoodle-20th-anniversary-magenta/' },
  { key: 'kanoodle-head-to-head', route: '/product/kanoodle-head-to-head/' },
  { key: 'kanoodle-genius', route: '/product/kanoodle-genius/' },
  { key: 'kanoodle-ultimate-champion', route: '/product/kanoodle-ultimate-champion/' },
];

const STYLE_PROPS = [
  'display',
  'gridTemplateColumns',
  'fontSize',
  'fontWeight',
  'lineHeight',
  'color',
  'backgroundColor',
  'borderTopWidth',
  'borderTopColor',
  'borderRadius',
  'paddingTop',
  'paddingRight',
  'paddingBottom',
  'paddingLeft',
  'marginTop',
  'marginBottom',
  'objectFit',
];

function cleanText(value) {
  return String(value || '').replace(/\s+/g, ' ').trim();
}

function slug(value) {
  return String(value).replace(/[^a-z0-9_-]+/gi, '-').replace(/^-+|-+$/g, '').toLowerCase();
}

function compareBoxes(prodBox, localBox, strict = false) {
  if (!prodBox || !localBox) return null;
  const limits = typeof strict === 'object'
    ? strict
    : strict ? { x: 10, y: 12, w: 14, h: 20 } : { x: 18, y: 32, w: 28, h: 72 };
  const delta = {
    x: Math.abs(prodBox.x - localBox.x),
    y: Math.abs(prodBox.y - localBox.y),
    w: Math.abs(prodBox.w - localBox.w),
    h: Math.abs(prodBox.h - localBox.h),
  };
  return Object.entries(delta).some(([key, value]) => value > limits[key]) ? delta : null;
}

function compareStyles(prodStyle, localStyle, props) {
  const delta = {};
  for (const prop of props) {
    const prodValue = prodStyle?.[prop] || '';
    const localValue = localStyle?.[prop] || '';
    if (prodValue !== localValue) {
      delta[prop] = { prod: prodValue, local: localValue };
    }
  }
  return Object.keys(delta).length ? delta : null;
}

function pctDelta(prodValue, localValue) {
  if (!prodValue && !localValue) return 0;
  if (!prodValue) return 100;
  return Math.round((Math.abs(prodValue - localValue) / prodValue) * 1000) / 10;
}

async function gotoProduct(page, base, route) {
  await page.goto(`${base}${route}`, {
    waitUntil: 'domcontentloaded',
    timeout: NAVIGATION_TIMEOUT_MS,
  });
  await page.waitForLoadState('networkidle', { timeout: 15000 }).catch(() => {});
  await page.waitForTimeout(1200);
}

async function extractProductPage(page) {
  return page.evaluate(({ styleProps }) => {
    const clean = (value) => String(value || '').replace(/\s+/g, ' ').trim();
    const boxOf = (el) => {
      if (!el) return null;
      const rect = el.getBoundingClientRect();
      return {
        x: Math.round(rect.x),
        y: Math.round(rect.y),
        w: Math.round(rect.width),
        h: Math.round(rect.height),
        bottom: Math.round(rect.bottom),
        right: Math.round(rect.right),
      };
    };
    const styleOf = (el) => {
      if (!el) return null;
      const computed = getComputedStyle(el);
      return Object.fromEntries(styleProps.map((prop) => [prop, computed[prop]]));
    };
    const visible = (el) => {
      const box = boxOf(el);
      const style = el ? getComputedStyle(el) : null;
      return !!box && box.w > 0 && box.h > 0 && style?.display !== 'none' && style?.visibility !== 'hidden' && style?.opacity !== '0';
    };
    const one = (...selectors) => selectors.map((selector) => document.querySelector(selector)).find(Boolean) || null;
    const snapshot = (label, ...selectors) => {
      const el = one(...selectors);
      if (!el) return null;
      return {
        label,
        selector: selectors.find((selector) => document.querySelector(selector)) || '',
        tag: el.tagName.toLowerCase(),
        cls: String(el.className || ''),
        box: boxOf(el),
        style: styleOf(el),
        text: clean(el.innerText || el.textContent).slice(0, 500),
      };
    };
    const imageIdentity = (img) => {
      if (!img) return '';
      const src = img.currentSrc || img.src || '';
      return src.split('?')[0].split('/').pop() || src;
    };
    const galleryRoot = one('[data-lfk-product-gallery]', '.woocommerce-product-gallery');
    const localGallery = document.querySelector('[data-lfk-product-gallery]');
    let thumbs = [];
    let activeThumb = null;
    let mainImage = null;
    let galleryType = 'none';
    let trackState = null;

    if (localGallery) {
      galleryType = 'lfk';
      thumbs = [...localGallery.querySelectorAll('[data-lfk-product-thumb]')].filter(visible);
      activeThumb = localGallery.querySelector('[data-lfk-product-thumb].is-active');
      const localTrack = localGallery.querySelector('.lfk-product-gallery-track');
      const localSlides = localTrack
        ? [...localTrack.querySelectorAll(':scope > img')]
          .filter((img) => !img.classList.contains('lfk-product-gallery-zoom-clone') && !img.classList.contains('lfk-product-zoom-marker'))
        : [];
      const activeThumbIndex = Math.max(0, thumbs.findIndex((thumb) => thumb === activeThumb || thumb.classList.contains('is-active')));
      mainImage = localGallery.querySelector('[data-lfk-product-main]')
        || localSlides[activeThumbIndex]
        || localSlides.find(visible)
        || null;
      if (localTrack) {
        const trackStyle = getComputedStyle(localTrack);
        const transform = trackStyle.transform || '';
        let translateX = 0;
        if (transform && transform !== 'none') {
          const matrix = new DOMMatrixReadOnly(transform);
          translateX = Math.round(matrix.m41);
        }
        trackState = {
          transform,
          translateX,
          slideWidth: Math.round(localSlides[0]?.getBoundingClientRect().width || localGallery.querySelector('.lfk-product-gallery-main')?.getBoundingClientRect().width || 0),
        };
      }
    } else if (galleryRoot) {
      galleryType = 'woocommerce';
      thumbs = [...document.querySelectorAll('.flex-control-thumbs img, .flex-control-nav img')].filter(visible);
      activeThumb = document.querySelector('.flex-control-thumbs img.flex-active, .flex-control-nav img.flex-active');
      const galleryBox = boxOf(galleryRoot);
      mainImage = document.querySelector('.woocommerce-product-gallery__image.flex-active-slide img')
        || [...galleryRoot.querySelectorAll('.woocommerce-product-gallery__image img, .woocommerce-product-gallery__wrapper img')]
          .filter(visible)
          .find((img) => {
            const box = boxOf(img);
            return galleryBox && box && box.x >= galleryBox.x - 5 && box.x <= galleryBox.x + galleryBox.w + 5;
          })
        || galleryRoot.querySelector('img');
    }

    const thumbBoxes = thumbs.map((thumb, index) => ({
      index,
      box: boxOf(thumb),
      src: imageIdentity(thumb.tagName === 'IMG' ? thumb : thumb.querySelector('img')),
      active: thumb === activeThumb || thumb.classList.contains('is-active') || thumb.classList.contains('flex-active'),
    }));
    const activeIndex = thumbBoxes.findIndex((thumb) => thumb.active);
    const thumbContainer = localGallery?.querySelector('.lfk-product-thumbs')
      || document.querySelector('.flex-control-thumbs, .flex-control-nav')
      || thumbs[0]?.parentElement
      || null;
    const sections = {
      header: snapshot('header', 'header', '.elementor-location-header'),
      gallery: snapshot('gallery', '[data-lfk-product-gallery]', '.woocommerce-product-gallery'),
      galleryMain: snapshot('galleryMain', '.lfk-product-gallery-main', '.woocommerce-product-gallery__wrapper'),
      thumbnails: thumbContainer ? {
        label: 'thumbnails',
        tag: thumbContainer.tagName.toLowerCase(),
        cls: String(thumbContainer.className || ''),
        box: boxOf(thumbContainer),
        style: styleOf(thumbContainer),
        text: '',
      } : null,
      summary: snapshot('summary', '.lfk-single-summary', '.summary.entry-summary', '.summary'),
      title: snapshot('title', '.lfk-single-title', '.product_title', 'h1.product_title'),
      price: snapshot('price', '.lfk-single-price', '.summary .price', 'p.price'),
      stock: snapshot('stock', '.lfk-single-summary > .stock', '.summary .stock', '.stock'),
      cart: snapshot('cart', '.single_add_to_cart_button', 'button[name="add-to-cart"]'),
      description: snapshot('description', '.lfk-product-description', '#tab-description', '.woocommerce-Tabs-panel--description', '.woocommerce-product-details__short-description'),
      related: snapshot('related', '.lfk-related-products', '.related.products'),
      footer: snapshot('footer', 'footer', '.elementor-location-footer'),
    };

    const scripts = [...document.scripts].map((script) => script.src).filter(Boolean);
    const styles = [...document.querySelectorAll('link[rel="stylesheet"]')].map((link) => link.href).filter(Boolean);
    const dependencyPattern = /elementor|woolentor|post-\d+\.css/i;
    const localMain = document.querySelector('.lfk-single-product');

    return {
      url: location.href,
      title: document.title,
      bodyClass: document.body.className,
      viewport: {
        scrollWidth: document.documentElement.scrollWidth,
        clientWidth: document.documentElement.clientWidth,
        scrollHeight: document.documentElement.scrollHeight,
      },
      sections,
      gallery: {
        type: galleryType,
        thumbCount: thumbs.length,
        activeIndex,
        mainSrc: imageIdentity(mainImage),
        mainAlt: mainImage?.alt || '',
        mainBox: boxOf(mainImage),
        thumbBoxes,
        trackState,
      },
      architecture: {
        dependencyStyles: styles.filter((href) => dependencyPattern.test(href)),
        dependencyScripts: scripts.filter((src) => dependencyPattern.test(src)),
        mainDependencyNodes: localMain ? localMain.querySelectorAll('[class*="elementor"], [class*="woolentor"]').length : 0,
      },
    };
  }, { styleProps: STYLE_PROPS });
}

async function interactWithGallery(page) {
  const before = await extractProductPage(page);
  const selector = before.gallery.type === 'lfk'
    ? '[data-lfk-product-thumb]'
    : '.flex-control-thumbs img, .flex-control-nav img';
  const thumbCount = await page.locator(selector).count();
  const steps = [];

  for (let index = 0; index < thumbCount; index += 1) {
    const thumb = page.locator(selector).nth(index);
    if (!(await thumb.isVisible().catch(() => false))) continue;
    const stateBefore = await extractProductPage(page);
    await thumb.click({ timeout: 10000 }).catch((error) => {
      steps.push({ index, status: 'NEEDS FIX', detail: `thumbnail click failed: ${error.message}` });
    });
    await page.waitForTimeout(500);
    const stateAfter = await extractProductPage(page);
    const expectedSelected = stateAfter.gallery.activeIndex === index;
    const trackExpectedX = stateAfter.gallery.trackState ? -index * stateAfter.gallery.trackState.slideWidth : null;
    const trackMovedToThumb = trackExpectedX === null || Math.abs(stateAfter.gallery.trackState.translateX - trackExpectedX) <= 2;
    const changedOrSameThumb = index === stateBefore.gallery.activeIndex
      || stateAfter.gallery.mainSrc !== stateBefore.gallery.mainSrc
      || trackMovedToThumb;
    steps.push({
      index,
      status: expectedSelected && changedOrSameThumb && trackMovedToThumb ? 'PASS' : 'NEEDS FIX',
      before: {
        activeIndex: stateBefore.gallery.activeIndex,
        mainSrc: stateBefore.gallery.mainSrc,
      },
      after: {
        activeIndex: stateAfter.gallery.activeIndex,
        mainSrc: stateAfter.gallery.mainSrc,
      },
      checks: {
        selectedThumbnailChanged: expectedSelected,
        mainImageChangedOrSameThumb: changedOrSameThumb,
        trackMovedToThumb,
      },
    });
  }

  return { before, after: await extractProductPage(page), steps };
}

function classifySelf(capture, jsErrors, baseKey) {
  const issues = [];
  const add = (section, status, detail, evidence = {}) => issues.push({ section, status, detail, evidence });
  const { sections } = capture;
  if (capture.viewport.scrollWidth > capture.viewport.clientWidth + 2) {
    add('layout', 'NEEDS FIX', 'page has horizontal overflow', capture.viewport);
  }
  if (jsErrors.length && baseKey === 'local') {
    add('interaction', 'NEEDS FIX', 'page emitted JS errors during load/interaction', jsErrors);
  }
  if (capture.gallery.thumbCount > 1 && capture.gallery.activeIndex < 0) {
    add('interaction', 'NEEDS FIX', 'gallery has thumbnails but no selected thumbnail state', capture.gallery);
  }
  if (sections.gallery?.box && sections.thumbnails?.box) {
    const gallery = sections.gallery.box;
    const thumbs = sections.thumbnails.box;
    if (thumbs.x < gallery.x - 4 || thumbs.right > gallery.right + 4 || thumbs.bottom > gallery.bottom + 4) {
      add('layout', 'NEEDS FIX', 'thumbnails extend outside gallery area', { gallery, thumbnails: thumbs });
    }
  }
  const gridBottom = Math.max(sections.gallery?.box?.bottom || 0, sections.summary?.box?.bottom || 0);
  if (sections.description?.box && gridBottom && sections.description.box.y < gridBottom - 8) {
    add('layout', 'NEEDS FIX', 'description starts before gallery/summary finish', {
      gridBottom,
      description: sections.description.box,
    });
  }
  if (sections.description?.box && sections.related?.box && sections.related.box.y < sections.description.box.bottom - 8) {
    add('layout', 'NEEDS FIX', 'related products start before description finishes', {
      description: sections.description.box,
      related: sections.related.box,
    });
  }
  if (sections.related?.box && sections.footer?.box && sections.footer.box.y < sections.related.box.bottom - 8) {
    add('layout', 'NEEDS FIX', 'footer starts before related products finish', {
      related: sections.related.box,
      footer: sections.footer.box,
    });
  }
  if (baseKey === 'local' && (capture.architecture.mainDependencyNodes > 0 || capture.architecture.dependencyStyles.length || capture.architecture.dependencyScripts.length)) {
    add('architecture', 'NEEDS FIX', 'local custom theme path depends on Elementor/WooLentor assets or rendered nodes', capture.architecture);
  }
  return issues;
}

function classifyComparison(prod, local) {
  const issues = [];
  const add = (section, status, detail, evidence = {}) => issues.push({ section, status, detail, evidence });
  const comparableSections = {
    mainImage: {
      prod: { box: prod.gallery.mainBox, style: {}, text: prod.gallery.mainSrc },
      local: { box: local.gallery.mainBox, style: {}, text: local.gallery.mainSrc },
      boxLimits: { x: 8, y: 28, w: 8, h: 8 },
    },
    thumbnails: {
      prod: prod.sections.thumbnails,
      local: local.sections.thumbnails,
      boxLimits: { x: 8, y: 28, w: 8, h: 8 },
    },
    title: {
      prod: prod.sections.title,
      local: local.sections.title,
      boxLimits: { x: 8, y: 28, w: 8, h: 8 },
      styleProps: ['fontSize', 'fontWeight', 'lineHeight', 'color'],
      compareText: true,
    },
    price: {
      prod: prod.sections.price,
      local: local.sections.price,
      boxLimits: { x: 8, y: 28, w: 8, h: 8 },
      styleProps: ['fontSize', 'fontWeight', 'lineHeight', 'color'],
      compareText: true,
    },
    stock: {
      prod: prod.sections.stock,
      local: local.sections.stock,
      boxLimits: { x: 8, y: 28, w: 8, h: 8 },
      styleProps: ['fontSize', 'fontWeight', 'lineHeight', 'color'],
    },
    cart: {
      prod: prod.sections.cart,
      local: local.sections.cart,
      boxLimits: { x: 8, y: 28, w: 8, h: 8 },
      styleProps: ['fontSize', 'fontWeight', 'lineHeight', 'color', 'backgroundColor', 'borderRadius'],
    },
    description: {
      prod: prod.sections.description,
      local: local.sections.description,
      boxLimits: { x: 8, y: 56, w: 8, h: 80 },
    },
    related: {
      prod: prod.sections.related,
      local: local.sections.related,
      boxLimits: { x: 8, y: 80, w: 8, h: 80 },
    },
    footer: {
      prod: prod.sections.footer,
      local: local.sections.footer,
      boxLimits: { x: 8, y: 80, w: 8, h: 8 },
    },
  };

  for (const [section, config] of Object.entries(comparableSections)) {
    const prodSection = config.prod;
    const localSection = config.local;
    if (prodSection && !localSection) {
      add(section, 'NEEDS FIX', `missing local ${section}`, { prod: prodSection });
      continue;
    }
    if (!prodSection && localSection) {
      add(section, 'NEEDS FIX', `extra local ${section}`, { local: localSection });
      continue;
    }
    if (!prodSection || !localSection) continue;
    const delta = compareBoxes(prodSection.box, localSection.box, config.boxLimits);
    if (delta) {
      add(section, 'NEEDS FIX', `${section} dimensions/position differ`, {
        delta,
        prod: prodSection.box,
        local: localSection.box,
      });
    }
    if (config.styleProps) {
      const styleDelta = compareStyles(prodSection.style, localSection.style, config.styleProps);
      if (styleDelta) {
        add(section, 'NEEDS FIX', `${section} visible CSS differs`, styleDelta);
      }
    }
    if (config.compareText && cleanText(prodSection.text) !== cleanText(localSection.text)) {
      add(section, 'CONTENT/DATA DIFFERENCE', `${section} text differs`, {
        prod: prodSection.text,
        local: localSection.text,
      });
    }
  }
  const heightDelta = pctDelta(prod.viewport.scrollHeight, local.viewport.scrollHeight);
  if (heightDelta > 12) {
    add('page height', 'NEEDS FIX', `page height differs by ${heightDelta}%`, {
      prod: prod.viewport.scrollHeight,
      local: local.viewport.scrollHeight,
    });
  }
  return issues;
}

async function auditPage(browser, baseKey, product, viewportName, viewport) {
  const context = await browser.newContext({ viewport });
  const page = await context.newPage();
  const jsErrors = [];
  page.on('pageerror', (error) => jsErrors.push({ type: 'pageerror', text: error.message }));
  page.on('console', (message) => {
    if (message.type() === 'error') {
      const text = message.text();
      if (!/favicon|Failed to load resource.*404/i.test(text)) {
        jsErrors.push({ type: 'console.error', text });
      }
    }
  });

  await gotoProduct(page, BASES[baseKey], product.route);
  const interaction = await interactWithGallery(page);
  const screenshotName = `${viewportName}-${baseKey}-${product.key}.png`;
  await page.screenshot({ path: path.join(OUT_DIR, screenshotName), fullPage: true });
  const capture = await extractProductPage(page);
  const selfIssues = classifySelf(capture, jsErrors, baseKey);
  for (const step of interaction.steps) {
    if (step.status !== 'PASS') {
      selfIssues.push({
        section: 'interaction',
        status: 'NEEDS FIX',
        detail: `thumbnail ${step.index + 1} did not update gallery correctly`,
        evidence: step,
      });
    }
  }
  await context.close();
  return {
    baseKey,
    product: product.key,
    route: product.route,
    viewport: viewportName,
    screenshot: screenshotName,
    jsErrors,
    interaction,
    capture,
    selfIssues,
  };
}

async function writeReadme(result) {
  const lines = [
    '# Product Interaction Audit',
    '',
    `Created: ${result.createdAt}`,
    '',
  ];
  for (const viewportName of Object.keys(result.viewports)) {
    lines.push(`## ${viewportName}`);
    for (const product of PRODUCTS) {
      const comparison = result.comparisons[viewportName][product.key];
      const needs = comparison.filter((issue) => issue.status === 'NEEDS FIX').length;
      const content = comparison.filter((issue) => issue.status === 'CONTENT/DATA DIFFERENCE').length;
      const label = needs ? `${needs} NEEDS FIX` : content ? `PASS with ${content} CONTENT/DATA DIFFERENCE` : 'PASS';
      lines.push(`- ${product.key}: ${label}`);
    }
    lines.push('');
  }
  await fs.writeFile(path.join(OUT_DIR, 'README.md'), `${lines.join('\n')}\n`);
}

async function main() {
  await fs.mkdir(OUT_DIR, { recursive: true });
  const browser = await chromium.launch();
  const captures = {};
  const comparisons = {};

  for (const viewportName of Object.keys(VIEWPORTS)) {
    captures[viewportName] = {};
    comparisons[viewportName] = {};
    for (const product of PRODUCTS) {
      captures[viewportName][product.key] = {};
      for (const baseKey of Object.keys(BASES)) {
        console.log(`auditing ${viewportName}/${baseKey}/${product.key}`);
        captures[viewportName][product.key][baseKey] = await auditPage(browser, baseKey, product, viewportName, VIEWPORTS[viewportName]);
      }
      const prod = captures[viewportName][product.key].prod.capture;
      const local = captures[viewportName][product.key].local.capture;
      comparisons[viewportName][product.key] = [
        ...captures[viewportName][product.key].prod.selfIssues.map((issue) => ({ ...issue, base: 'prod' })),
        ...captures[viewportName][product.key].local.selfIssues.map((issue) => ({ ...issue, base: 'local' })),
        ...classifyComparison(prod, local).map((issue) => ({ ...issue, base: 'local-vs-prod' })),
      ];
    }
  }

  await browser.close();
  const result = {
    createdAt: new Date().toISOString(),
    prodBase: BASES.prod,
    localBase: BASES.local,
    outDir: OUT_DIR,
    products: PRODUCTS,
    viewports: VIEWPORTS,
    captures,
    comparisons,
  };
  await fs.writeFile(path.join(OUT_DIR, 'product-interaction-audit.json'), JSON.stringify(result, null, 2));
  await writeReadme(result);
  console.log(JSON.stringify({ outDir: OUT_DIR, products: PRODUCTS.map((product) => product.key) }, null, 2));
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
