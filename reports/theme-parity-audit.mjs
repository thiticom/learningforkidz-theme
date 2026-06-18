import fs from 'node:fs/promises';
import path from 'node:path';
import { chromium } from 'playwright';

const ROOT = process.cwd();
const DATE = new Date().toISOString().slice(0, 10);
const RUN_LABEL = process.env.LFK_AUDIT_RUN_LABEL || DATE;
const OUT_DIR = path.join(ROOT, 'reports', `theme-parity-audit-${RUN_LABEL}`);
const PRODUCT_ID = process.env.LFK_AUDIT_PRODUCT_ID || '32848';
const NAVIGATION_TIMEOUT_MS = Number(process.env.LFK_AUDIT_NAV_TIMEOUT_MS || 120000);

const BASES = {
  prod: process.env.LFK_AUDIT_PROD_BASE || 'https://www.learningforkidz.com',
  local: process.env.LFK_AUDIT_LOCAL_BASE || 'http://100.109.57.34:8085',
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

const STYLE_PROPS = [
  'display',
  'gridTemplateColumns',
  'flexDirection',
  'gap',
  'fontSize',
  'fontWeight',
  'lineHeight',
  'color',
  'backgroundColor',
  'borderTopWidth',
  'borderRightWidth',
  'borderBottomWidth',
  'borderLeftWidth',
  'borderTopColor',
  'borderRightColor',
  'borderBottomColor',
  'borderLeftColor',
  'borderRadius',
  'boxShadow',
  'paddingTop',
  'paddingRight',
  'paddingBottom',
  'paddingLeft',
  'marginTop',
  'marginRight',
  'marginBottom',
  'marginLeft',
  'objectFit',
];

function cleanText(value) {
  return String(value || '').replace(/\s+/g, ' ').trim();
}

function slug(value) {
  return String(value).replace(/[^a-z0-9_-]+/gi, '-').replace(/^-+|-+$/g, '').toLowerCase();
}

function pctDelta(prodValue, localValue) {
  if (!prodValue && !localValue) return 0;
  if (!prodValue) return 100;
  return Math.round((Math.abs(prodValue - localValue) / prodValue) * 1000) / 10;
}

async function gotoRoute(page, base, routeKey) {
  if (routeKey === 'cart' || routeKey === 'checkout') {
    await page.goto(`${base}/cart/?add-to-cart=${encodeURIComponent(PRODUCT_ID)}`, {
      waitUntil: 'domcontentloaded',
      timeout: NAVIGATION_TIMEOUT_MS,
    });
    await page.waitForTimeout(2500);
  }

  await page.goto(`${base}${ROUTES[routeKey]}`, {
    waitUntil: 'domcontentloaded',
    timeout: NAVIGATION_TIMEOUT_MS,
  });
  await page.waitForLoadState('networkidle', { timeout: 15000 }).catch(() => {});
  await page.waitForTimeout(1500);
}

async function extractPage(page) {
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
      return !!box && box.w > 0 && box.h > 0 && style?.display !== 'none' && style?.visibility !== 'hidden';
    };
    const one = (selector) => document.querySelector(selector);
    const snapshotElement = (el, selector) => {
      if (!el) return null;
      return {
        selector,
        tag: el.tagName.toLowerCase(),
        id: el.id || '',
        cls: String(el.className || ''),
        box: boxOf(el),
        style: styleOf(el),
        text: clean(el.innerText || el.textContent).slice(0, 1000),
      };
    };
    const snapshot = (selector) => snapshotElement(one(selector), selector);
    const outsideHeaderFooter = (el) => !el.matches('.elementor-location-header, .elementor-location-footer, header, footer')
      && !el.closest('.elementor-location-header, .elementor-location-footer, header, footer');
    const pickMain = () => {
      const mainSelectors = [
        'main',
        '#primary',
        '.site-main',
        '.elementor-location-single',
        '[data-elementor-type="wp-page"]',
        '[data-elementor-type="single-page"]',
        'section',
        'article',
        '.elementor-section',
        '.elementor-element.e-con',
        '.e-con-boxed',
        '.lfk-woocommerce-page',
        '.lfk-single-product',
        '.lfk-product-archive',
        '.lfk-post-archive',
        '.lfk-page',
      ].join(',');
      const firstContentHeading = [...document.querySelectorAll('h1,h2,h3,h4')]
        .filter(visible)
        .filter(outsideHeaderFooter)
        .find((heading) => clean(heading.innerText || heading.textContent));
      if (firstContentHeading) {
        const headingBox = boxOf(firstContentHeading);
        const ancestors = [];
        for (let el = firstContentHeading.parentElement; el && !['BODY', 'HTML'].includes(el.tagName); el = el.parentElement) {
          if (el.matches(mainSelectors) && visible(el) && outsideHeaderFooter(el)) {
            const box = boxOf(el);
            if (box && box.h >= Math.max(headingBox.h + 80, 112) && box.w >= Math.min(260, document.documentElement.clientWidth - 20)) {
              ancestors.push(el);
            }
          }
        }
        if (ancestors.length) {
          return ancestors.sort((a, b) => {
            const aBox = boxOf(a);
            const bBox = boxOf(b);
            const aIsMain = a.matches('main, #primary, .site-main, .lfk-woocommerce-page');
            const bIsMain = b.matches('main, #primary, .site-main, .lfk-woocommerce-page');
            if (aIsMain !== bIsMain) return aIsMain ? -1 : 1;
            return aBox.h - bBox.h;
          })[0];
        }
      }

      const preferred = [...document.querySelectorAll('main, #primary, .site-main, .elementor-location-single')]
        .filter(visible)
        .filter(outsideHeaderFooter)
        .filter((el) => !['BODY', 'HTML'].includes(el.tagName));
      if (preferred.length) {
        return preferred[0];
      }

      return [...document.querySelectorAll('.elementor-page, .elementor')]
        .filter(visible)
        .filter(outsideHeaderFooter)
        .filter((el) => !['BODY', 'HTML'].includes(el.tagName))
        .sort((a, b) => {
          const aBox = boxOf(a);
          const bBox = boxOf(b);
          const aScore = clean(a.innerText || a.textContent).length + (aBox?.h || 0);
          const bScore = clean(b.innerText || b.textContent).length + (bBox?.h || 0);
          return bScore - aScore;
        })[0] || null;
    };
    const dependencyPattern = /elementor|woolentor|post-\d+\.css/i;
    const mainEl = pickMain();
    const visibleText = clean(document.body.innerText || '');
    const duplicateText = {};
    for (const chunk of visibleText.split(/(?<=[.!?])\s+|\n+/)) {
      const text = clean(chunk);
      if (text.length >= 8 && text.length <= 140) {
        duplicateText[text] = (duplicateText[text] || 0) + 1;
      }
    }
    const fieldLabel = (field) => {
      const id = field.id;
      const explicit = id ? document.querySelector(`label[for="${CSS.escape(id)}"]`) : null;
      return clean(field.getAttribute('aria-label') || explicit?.innerText || field.closest('label,p,.form-row')?.innerText || '');
    };
    const sectionCandidates = [
      ...document.querySelectorAll('main, section, article, .elementor-section, .elementor-element.e-con, .lfk-hero, .lfk-products-section, .lfk-intro-section, .lfk-product-archive, .lfk-post-archive, .lfk-woocommerce-page, .summary, .product, li.product'),
    ]
      .filter(visible)
      .filter((el) => {
        const box = boxOf(el);
        return box.h >= 40 && box.w >= Math.min(260, document.documentElement.clientWidth - 20);
      })
      .slice(0, 80)
      .map((el, index) => ({
        index,
        tag: el.tagName.toLowerCase(),
        id: el.id || '',
        cls: String(el.className || '').slice(0, 180),
        key: el.id || String(el.className || '').split(/\s+/).filter(Boolean).slice(0, 4).join('.') || el.tagName.toLowerCase(),
        box: boxOf(el),
        style: styleOf(el),
        text: clean(el.innerText || el.textContent).slice(0, 260),
      }));

    return {
      url: location.href,
      title: document.title,
      bodyClass: document.body.className,
      viewport: {
        scrollWidth: document.documentElement.scrollWidth,
        clientWidth: document.documentElement.clientWidth,
        scrollHeight: document.documentElement.scrollHeight,
      },
      landmarks: {
        header: snapshot('.elementor-location-header, header, .lfk-header'),
        main: snapshotElement(mainEl, 'main, #primary, .site-main, .elementor-location-single, .elementor-page, .elementor'),
        footer: snapshot('footer, .lfk-site-footer, .elementor-location-footer'),
      },
      architecture: {
        dependencyStyles: [...document.querySelectorAll('link[rel="stylesheet"]')]
          .map((link) => link.href)
          .filter((href) => dependencyPattern.test(href)),
        dependencyScripts: [...document.scripts]
          .map((script) => script.src)
          .filter(Boolean)
          .filter((src) => dependencyPattern.test(src)),
        mainDependencyNodes: mainEl
          ? mainEl.querySelectorAll('[class*="elementor"], [data-elementor-type], [class*="woolentor"]').length
          : 0,
      },
      headings: [...document.querySelectorAll('h1,h2,h3,h4')]
        .filter(visible)
        .map((el, index) => ({
          index,
          tag: el.tagName.toLowerCase(),
          text: clean(el.innerText),
          box: boxOf(el),
          style: styleOf(el),
        })),
      buttons: [...document.querySelectorAll('button, input[type="submit"], a.button, .button, .add_to_cart_button, .single_add_to_cart_button')]
        .filter((el) => visible(el) && clean(el.innerText || el.value || el.getAttribute('aria-label')))
        .filter((el) => !el.closest('.elementor-location-header, .elementor-location-footer, header, footer, .lfk-header, .lfk-site-footer, .lfk-back-to-top, .grecaptcha-badge'))
        .map((el, index) => ({
          index,
          tag: el.tagName.toLowerCase(),
          id: el.id || '',
          cls: String(el.className || ''),
          text: clean(el.innerText || el.value || el.getAttribute('aria-label')),
          box: boxOf(el),
          style: styleOf(el),
        })),
      fields: [...document.querySelectorAll('input, select, textarea')]
        .filter((el) => visible(el) && el.type !== 'hidden')
        .map((el, index) => ({
          index,
          tag: el.tagName.toLowerCase(),
          type: el.getAttribute('type') || el.tagName.toLowerCase(),
          id: el.id || '',
          name: el.getAttribute('name') || '',
          label: fieldLabel(el),
          placeholder: el.getAttribute('placeholder') || '',
          box: boxOf(el),
          style: styleOf(el),
        })),
      productCards: [...document.querySelectorAll('li.product, .product, .lfk-product-card, .ht-product, .woolentor-product')]
        .filter(visible)
        .map((el, index) => ({
          index,
          tag: el.tagName.toLowerCase(),
          cls: String(el.className || '').slice(0, 180),
          text: clean(el.innerText || el.textContent).slice(0, 220),
          box: boxOf(el),
          style: styleOf(el),
        }))
        .slice(0, 80),
      images: [...document.images]
        .filter(visible)
        .filter((img) => !img.closest('.elementor-location-header, .elementor-location-footer, header, footer, .lfk-header, .lfk-site-footer, .lfk-back-to-top, .grecaptcha-badge'))
        .map((img, index) => ({
          index,
          src: img.currentSrc || img.src,
          file: (img.currentSrc || img.src).split('/').pop()?.split('?')[0] || '',
          alt: img.alt || '',
          naturalWidth: img.naturalWidth,
          naturalHeight: img.naturalHeight,
          box: boxOf(img),
          style: styleOf(img),
        }))
        .slice(0, 80),
      notices: [...document.querySelectorAll('.woocommerce-error, .woocommerce-info, .woocommerce-message, .woocommerce-notice, [role="alert"]')]
        .filter(visible)
        .map((el) => clean(el.innerText)),
      sections: sectionCandidates,
      duplicateText: Object.fromEntries(Object.entries(duplicateText).filter(([, count]) => count > 1)),
      textLength: visibleText.length,
    };
  }, { styleProps: STYLE_PROPS });
}

function compareBoxes(prodBox, localBox, strict = false) {
  if (!prodBox || !localBox) return null;
  const limits = typeof strict === 'object'
    ? strict
    : strict ? { x: 8, y: 8, w: 8, h: 8 } : { x: 16, y: 24, w: 24, h: 48 };
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

function relatedHeadingStart(headings) {
  return headings.findIndex((heading) => heading.text === 'สินค้าที่เกี่ยวข้อง');
}

function headingTextForComparison(routeKey, headings, index) {
  const relatedStart = routeKey === 'single-product' ? relatedHeadingStart(headings) : -1;
  if (relatedStart >= 0 && index > relatedStart) {
    return `related-product-${index - relatedStart}`;
  }
  return headings[index]?.text || '';
}

function headingSignature(routeKey, headings) {
  return headings.map((heading, index) => `${heading.tag}:${headingTextForComparison(routeKey, headings, index)}`).join('|');
}

function relatedHeadingEvidence(headings) {
  const relatedStart = relatedHeadingStart(headings);
  return relatedStart >= 0
    ? headings.slice(relatedStart + 1).map((heading) => ({ tag: heading.tag, text: heading.text, box: heading.box }))
    : [];
}

function classify(routeKey, viewportName, prod, local) {
  const issues = [];
  const add = (section, status, detail, evidence = {}) => issues.push({ section, status, detail, evidence });
  const strictCommerce = ['cart', 'checkout', 'my-account'].includes(routeKey);
  const relaxedY = viewportName === 'mobile' ? 120 : 64;
  const landmarkLimits = (name) => {
    if (strictCommerce && name === 'main') return { x: 8, y: 8, w: 8, h: 48 };
    if (strictCommerce || name === 'header') return { x: 8, y: 8, w: 8, h: 8 };
    if (name === 'footer') return { x: 8, y: relaxedY, w: 8, h: 12 };
    return { x: 16, y: relaxedY, w: 24, h: viewportName === 'mobile' ? 140 : 80 };
  };
  const contentBoxLimits = {
    x: 16,
    y: relaxedY,
    w: 24,
    h: viewportName === 'mobile' ? 140 : 80,
  };
  const compactBoxLimits = strictCommerce
    ? { x: 8, y: 8, w: 8, h: 8 }
    : { x: 16, y: relaxedY, w: 16, h: 40 };

  if (local.architecture.dependencyStyles.length || local.architecture.dependencyScripts.length || local.architecture.mainDependencyNodes > 0) {
    add('architecture', 'NEEDS FIX', 'Local custom theme path depends on Elementor/WooLentor assets or rendered nodes', {
      styles: local.architecture.dependencyStyles,
      scripts: local.architecture.dependencyScripts,
      mainDependencyNodes: local.architecture.mainDependencyNodes,
    });
  }

  if (local.viewport.scrollWidth > local.viewport.clientWidth + 2) {
    add('page', 'NEEDS FIX', 'Local page has horizontal overflow', {
      scrollWidth: local.viewport.scrollWidth,
      clientWidth: local.viewport.clientWidth,
    });
  }

  const scrollDeltaPct = pctDelta(prod.viewport.scrollHeight, local.viewport.scrollHeight);
  if (scrollDeltaPct > 12) {
    add('page height', 'NEEDS FIX', `Page height differs by ${scrollDeltaPct}%`, {
      prod: prod.viewport.scrollHeight,
      local: local.viewport.scrollHeight,
    });
  }

  for (const name of ['header', 'main', 'footer']) {
    const prodLandmark = prod.landmarks[name];
    const localLandmark = local.landmarks[name];
    if (prodLandmark && !localLandmark) {
      add(name, 'NEEDS FIX', `Missing ${name} landmark locally`, { prod: prodLandmark });
    } else if (!prodLandmark && localLandmark) {
      add(name, 'NEEDS FIX', `Extra ${name} landmark locally`, { local: localLandmark });
    } else if (prodLandmark && localLandmark) {
      const delta = compareBoxes(prodLandmark.box, localLandmark.box, landmarkLimits(name));
      if (delta) {
        add(name, 'NEEDS FIX', `${name} dimensions/position differ`, {
          delta,
          prod: prodLandmark.box,
          local: localLandmark.box,
        });
      }
      for (const prop of ['fontSize', 'lineHeight', 'backgroundColor', 'display', 'gridTemplateColumns']) {
        if (name === 'main' && prop === 'display') {
          continue;
        }
        if ((prodLandmark.style?.[prop] || '') !== (localLandmark.style?.[prop] || '')) {
          if (prop === 'backgroundColor' && [prodLandmark.style?.[prop], localLandmark.style?.[prop]].includes('rgba(0, 0, 0, 0)')) {
            continue;
          }
          add(name, 'NEEDS FIX', `${name} CSS differs: ${prop}`, {
            prod: prodLandmark.style?.[prop] || '',
            local: localLandmark.style?.[prop] || '',
          });
        }
      }
    }
  }

  const prodRawHeadingSig = prod.headings.map((heading) => `${heading.tag}:${heading.text}`).join('|');
  const localRawHeadingSig = local.headings.map((heading) => `${heading.tag}:${heading.text}`).join('|');
  const prodHeadingSig = headingSignature(routeKey, prod.headings);
  const localHeadingSig = headingSignature(routeKey, local.headings);
  if (prodHeadingSig !== localHeadingSig) {
    add('headings', 'NEEDS FIX', 'Visible heading sequence/text differs', {
      prod: prod.headings.map((heading) => ({ tag: heading.tag, text: heading.text, box: heading.box })),
      local: local.headings.map((heading) => ({ tag: heading.tag, text: heading.text, box: heading.box })),
    });
  } else {
    if (routeKey === 'single-product' && prodRawHeadingSig !== localRawHeadingSig) {
      add('related products', 'CONTENT/DATA DIFFERENCE', 'Production randomizes related product selections; compare card layout/boxes instead of exact product-title text', {
        prod: relatedHeadingEvidence(prod.headings),
        local: relatedHeadingEvidence(local.headings),
      });
    }
    prod.headings.forEach((prodHeading, index) => {
      const localHeading = local.headings[index];
      for (const prop of ['fontSize', 'lineHeight', 'fontWeight', 'color']) {
        if ((prodHeading.style?.[prop] || '') !== (localHeading.style?.[prop] || '')) {
          add('headings', 'NEEDS FIX', `Heading CSS differs for "${prodHeading.text}": ${prop}`, {
            prod: prodHeading.style?.[prop] || '',
            local: localHeading.style?.[prop] || '',
          });
        }
      }
      const delta = compareBoxes(prodHeading.box, localHeading.box, strictCommerce ? true : contentBoxLimits);
      if (delta) {
        add('headings', 'NEEDS FIX', `Heading position/dimensions differ for "${prodHeading.text}"`, {
          delta,
          prod: prodHeading.box,
          local: localHeading.box,
        });
      }
    });
  }

  if (Math.abs(prod.productCards.length - local.productCards.length) > 1) {
    add('product cards', 'CONTENT/DATA DIFFERENCE', 'Product/card count differs; verify data before visual sign-off', {
      prod: prod.productCards.length,
      local: local.productCards.length,
    });
  }

  const prodFields = prod.fields.map((field) => field.name || field.id || field.label || field.placeholder);
  const localFields = local.fields.map((field) => field.name || field.id || field.label || field.placeholder);
  if (prodFields.join('|') !== localFields.join('|')) {
    add('forms', routeKey === 'my-account' ? 'CONTENT/DATA DIFFERENCE' : 'NEEDS FIX', 'Visible form fields differ', {
      prod: prodFields,
      local: localFields,
    });
  }

  const buttonTexts = (capture) => capture.buttons.map((button) => button.text).filter(Boolean);
  if (buttonTexts(prod).join('|') !== buttonTexts(local).join('|')) {
    add('buttons', 'NEEDS FIX', 'Visible button text/order differs', {
      prod: buttonTexts(prod),
      local: buttonTexts(local),
    });
  } else {
    prod.buttons.forEach((prodButton, index) => {
      const localButton = local.buttons[index];
      const delta = compareBoxes(prodButton.box, localButton.box, compactBoxLimits);
      if (delta) {
        add('buttons', 'NEEDS FIX', `Button position/dimensions differ for "${prodButton.text}"`, {
          delta,
          prod: prodButton.box,
          local: localButton.box,
        });
      }
      const styleDelta = compareStyles(prodButton.style, localButton.style, [
        'fontSize',
        'fontWeight',
        'lineHeight',
        'color',
        'backgroundColor',
        'borderTopWidth',
        'borderTopColor',
        'borderRadius',
      ]);
      if (styleDelta) {
        add('buttons', 'NEEDS FIX', `Button CSS differs for "${prodButton.text}"`, styleDelta);
      }
    });
  }

  if (prodFields.join('|') === localFields.join('|')) {
    prod.fields.forEach((prodField, index) => {
      const localField = local.fields[index];
      const delta = compareBoxes(prodField.box, localField.box, compactBoxLimits);
      if (delta) {
        add('forms', 'NEEDS FIX', `Field position/dimensions differ for "${prodField.label || prodField.name || prodField.id}"`, {
          delta,
          prod: prodField.box,
          local: localField.box,
        });
      }
      const styleDelta = compareStyles(prodField.style, localField.style, [
        'fontSize',
        'lineHeight',
        'color',
        'backgroundColor',
        'borderTopWidth',
        'borderTopColor',
        'borderRadius',
      ]);
      if (styleDelta) {
        add('forms', 'NEEDS FIX', `Field CSS differs for "${prodField.label || prodField.name || prodField.id}"`, styleDelta);
      }
    });
  }

  if (Math.abs(prod.images.length - local.images.length) > 8) {
    add('images', 'NEEDS FIX', 'Visible image count differs substantially', {
      prod: prod.images.length,
      local: local.images.length,
    });
  }

  for (let index = 0; index < Math.min(prod.images.length, local.images.length, 20); index += 1) {
    const prodImage = prod.images[index];
    const localImage = local.images[index];
    const delta = compareBoxes(prodImage.box, localImage.box, strictCommerce ? true : contentBoxLimits);
    if (delta) {
      add('images', strictCommerce ? 'NEEDS FIX' : 'CONTENT/DATA DIFFERENCE', `Image ${index + 1} dimensions/position differ`, {
        delta,
        prod: { file: prodImage.file, alt: prodImage.alt, box: prodImage.box },
        local: { file: localImage.file, alt: localImage.alt, box: localImage.box },
      });
      break;
    }
  }

  for (const [text, count] of Object.entries(local.duplicateText)) {
    if (/stock|สินค้า|คงเหลือ|remaining|มีสินค้า/i.test(text) && count > (prod.duplicateText[text] || 0)) {
      add('duplicate text', 'NEEDS FIX', `Local duplicate text appears more often than prod: "${text}"`, {
        prodCount: prod.duplicateText[text] || 0,
        localCount: count,
      });
    }
  }

  if (!issues.length) {
    add('page', 'PASS', `${routeKey} ${viewportName} has no detected parity issues`);
  }
  return issues;
}

async function run() {
  await fs.mkdir(OUT_DIR, { recursive: true });
  const browser = await chromium.launch({ headless: true });
  const result = {
    createdAt: new Date().toISOString(),
    prodBase: BASES.prod,
    localBase: BASES.local,
    outDir: OUT_DIR,
    routeKeys: ROUTE_KEYS,
    captures: {},
    comparisons: {},
  };

  for (const [viewportName, viewport] of Object.entries(VIEWPORTS)) {
    result.captures[viewportName] = {};
    result.comparisons[viewportName] = {};
    for (const routeKey of ROUTE_KEYS) {
      result.captures[viewportName][routeKey] = {};
      for (const [envName, base] of Object.entries(BASES)) {
        console.error(`capturing ${viewportName}/${routeKey}/${envName}`);
        const context = await browser.newContext({
          viewport: { width: viewport.width, height: viewport.height },
          isMobile: viewport.isMobile,
          deviceScaleFactor: viewport.deviceScaleFactor,
          ignoreHTTPSErrors: true,
        });
        const page = await context.newPage();
        await gotoRoute(page, base, routeKey);
        const screenshot = path.join(OUT_DIR, `${viewportName}-${envName}-${slug(routeKey)}.png`);
        await page.screenshot({ path: screenshot, fullPage: true });
        const capture = await extractPage(page);
        capture.screenshot = screenshot;
        result.captures[viewportName][routeKey][envName] = capture;
        await context.close();
      }
      result.comparisons[viewportName][routeKey] = classify(
        routeKey,
        viewportName,
        result.captures[viewportName][routeKey].prod,
        result.captures[viewportName][routeKey].local,
      );
    }
  }

  await browser.close();
  await fs.writeFile(path.join(OUT_DIR, 'theme-parity-audit.json'), JSON.stringify(result, null, 2));

  const lines = ['# Theme Parity Audit', '', `Created: ${result.createdAt}`, ''];
  for (const viewportName of Object.keys(VIEWPORTS)) {
    lines.push(`## ${viewportName}`);
    for (const routeKey of ROUTE_KEYS) {
      const issues = result.comparisons[viewportName][routeKey];
      const needsFix = issues.filter((issue) => issue.status === 'NEEDS FIX').length;
      const content = issues.filter((issue) => issue.status === 'CONTENT/DATA DIFFERENCE').length;
      const pass = issues.every((issue) => issue.status === 'PASS');
      lines.push(`- ${routeKey}: ${pass ? 'PASS' : `${needsFix} NEEDS FIX, ${content} CONTENT/DATA DIFFERENCE`}`);
    }
    lines.push('');
  }
  await fs.writeFile(path.join(OUT_DIR, 'README.md'), lines.join('\n'));
  console.log(JSON.stringify({ outDir: OUT_DIR, routeKeys: ROUTE_KEYS }, null, 2));
}

run().catch((error) => {
  console.error(error);
  process.exit(1);
});
