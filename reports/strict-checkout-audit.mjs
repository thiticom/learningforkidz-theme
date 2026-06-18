import fs from 'node:fs/promises';
import path from 'node:path';
import { chromium } from 'playwright';

const ROOT = process.cwd();
const DATE = new Date().toISOString().slice(0, 10);
const RUN_LABEL = process.env.LFK_AUDIT_RUN_LABEL || DATE;
const OUT_DIR = path.join(ROOT, 'reports', `strict-checkout-audit-${RUN_LABEL}`);
const PRODUCT_ID = process.env.LFK_AUDIT_PRODUCT_ID || '32848';
const NAVIGATION_TIMEOUT_MS = Number(process.env.LFK_AUDIT_NAV_TIMEOUT_MS || 120000);

const ENVS = {
  prod: process.env.LFK_AUDIT_PROD_BASE || 'https://www.learningforkidz.com',
  local: process.env.LFK_AUDIT_LOCAL_BASE || 'http://100.109.57.34:8085',
};

const VIEWPORTS = {
  desktop: { width: 1365, height: 1400, isMobile: false, deviceScaleFactor: 1 },
  mobile: { width: 390, height: 1200, isMobile: true, deviceScaleFactor: 2 },
};

const SECTION_SELECTORS = {
  notices: '.woocommerce-notices-wrapper:not(:empty), .woocommerce-error, .woocommerce-message',
  couponToggle: '.woocommerce-form-coupon-toggle, .e-coupon-box, .coupon',
  couponForm: 'form.checkout_coupon, .woocommerce-form-coupon',
  checkoutForm: 'form.checkout, form[name="checkout"]',
  customerDetails: '#customer_details',
  billing: '.woocommerce-billing-fields',
  shipping: '.woocommerce-shipping-fields',
  additional: '.woocommerce-additional-fields',
  orderHeading: '#order_review_heading',
  orderReview: '#order_review, .woocommerce-checkout-review-order',
  orderTable: '.woocommerce-checkout-review-order-table, table.shop_table',
  payment: '#payment, .woocommerce-checkout-payment',
  privacy: '.woocommerce-privacy-policy-text',
  terms: '.woocommerce-terms-and-conditions-wrapper',
  placeOrder: '#place_order',
};

const STYLE_PROPS = [
  'display',
  'gridTemplateColumns',
  'flexDirection',
  'gap',
  'fontFamily',
  'fontSize',
  'fontWeight',
  'lineHeight',
  'color',
  'backgroundColor',
  'borderTopColor',
  'borderRightColor',
  'borderBottomColor',
  'borderLeftColor',
  'borderTopWidth',
  'borderRightWidth',
  'borderBottomWidth',
  'borderLeftWidth',
  'borderRadius',
  'borderCollapse',
  'borderSpacing',
  'boxShadow',
  'paddingTop',
  'paddingRight',
  'paddingBottom',
  'paddingLeft',
  'marginTop',
  'marginRight',
  'marginBottom',
  'marginLeft',
];

function cleanText(value) {
  return String(value || '').replace(/\s+/g, ' ').trim();
}

function roundedBox(box) {
  if (!box) return null;
  return {
    x: Math.round(box.x),
    y: Math.round(box.y),
    w: Math.round(box.width),
    h: Math.round(box.height),
    bottom: Math.round(box.y + box.height),
  };
}

async function seedCart(page, base) {
  const addUrl = `${base}/cart/?add-to-cart=${encodeURIComponent(PRODUCT_ID)}`;
  await page.goto(addUrl, { waitUntil: 'domcontentloaded', timeout: NAVIGATION_TIMEOUT_MS });
  await page.waitForTimeout(2500);
  await page.goto(`${base}/checkout/`, { waitUntil: 'domcontentloaded', timeout: NAVIGATION_TIMEOUT_MS });
  await page.waitForSelector('form.checkout, form[name="checkout"], .woocommerce, main', { timeout: 15000 }).catch(() => {});
  await page.waitForTimeout(3000);
}

async function extractPage(page) {
  return await page.evaluate(({ sectionSelectors, styleProps }) => {
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
    const isVisible = (el) => {
      if (!el) return false;
      const box = boxOf(el);
      const style = getComputedStyle(el);
      return !!box && box.w > 0 && box.h > 0 && style.display !== 'none' && style.visibility !== 'hidden';
    };
    const one = (selector) => [...document.querySelectorAll(selector)].find(isVisible) || null;
    const sectionSnapshot = (selector) => {
      const el = one(selector);
      if (!el) return null;
      return {
        selector,
        tag: el.tagName.toLowerCase(),
        id: el.id || '',
        cls: el.className || '',
        box: boxOf(el),
        style: styleOf(el),
        text: clean(el.innerText).slice(0, 900),
      };
    };
    const labelFor = (field) => {
      const id = field.id;
      const aria = field.getAttribute('aria-label');
      const wrapped = field.closest('label');
      const explicit = id ? document.querySelector(`label[for="${CSS.escape(id)}"]`) : null;
      return clean(aria || explicit?.innerText || wrapped?.innerText || field.closest('p, .form-row, .woocommerce-input-wrapper')?.innerText || '');
    };
    const dependencyPattern = /elementor|woolentor|post-\d+\.css/i;
    const mainEl = document.querySelector('main, #primary, .site-main, form.checkout, [data-elementor-type="wp-page"], .elementor');
    const fields = [...document.querySelectorAll('form.checkout input, form.checkout select, form.checkout textarea, form[name="checkout"] input, form[name="checkout"] select, form[name="checkout"] textarea')]
      .filter((field) => field.type !== 'hidden' && isVisible(field))
      .map((field, index) => ({
        index,
        tag: field.tagName.toLowerCase(),
        type: field.getAttribute('type') || field.tagName.toLowerCase(),
        id: field.id || '',
        name: field.getAttribute('name') || '',
        label: labelFor(field),
        placeholder: field.getAttribute('placeholder') || '',
        required: field.required || field.getAttribute('aria-required') === 'true' || field.closest('.validate-required') !== null,
        autocomplete: field.getAttribute('autocomplete') || '',
        checked: field.checked || false,
        box: boxOf(field),
        style: styleOf(field),
      }));
    const buttons = [...document.querySelectorAll('button, input[type="submit"], a.button, .button')]
      .filter((el) => isVisible(el) && clean(el.innerText || el.value))
      .map((el, index) => ({
        index,
        tag: el.tagName.toLowerCase(),
        id: el.id || '',
        cls: el.className || '',
        text: clean(el.innerText || el.value),
        box: boxOf(el),
        style: styleOf(el),
      }));
    const shippingMethod = document.querySelector('#shipping_method');
    const shippingMethodAfter = shippingMethod ? getComputedStyle(shippingMethod, '::after') : null;
    const rows = [...document.querySelectorAll('.woocommerce-checkout-review-order-table tr, table.shop_table tr')]
      .map((row, index) => ({
        index,
        cls: row.className || '',
        cells: [...row.children].map((cell) => clean(cell.innerText)),
        box: boxOf(row),
        style: styleOf(row),
      }));
    const duplicateText = {};
    for (const chunk of clean(document.body.innerText).split(/(?<=[.!?])\s+|\n+/)) {
      const text = clean(chunk);
      if (text.length >= 8 && text.length <= 120) {
        duplicateText[text] = (duplicateText[text] || 0) + 1;
      }
    }
    return {
      url: location.href,
      title: document.title,
      bodyClass: document.body.className,
      viewport: {
        scrollWidth: document.documentElement.scrollWidth,
        clientWidth: document.documentElement.clientWidth,
        scrollHeight: document.documentElement.scrollHeight,
      },
      sections: Object.fromEntries(Object.entries(sectionSelectors).map(([name, selector]) => [name, sectionSnapshot(selector)])),
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
      headings: [...document.querySelectorAll('h1,h2,h3,h4')].map((el, index) => ({
        index,
        tag: el.tagName.toLowerCase(),
        text: clean(el.innerText),
        box: boxOf(el),
        style: styleOf(el),
      })),
      fields,
      buttons,
      rows,
      shippingMethod: shippingMethod ? {
        box: boxOf(shippingMethod),
        text: clean(shippingMethod.innerText || shippingMethod.textContent),
        after: {
          content: shippingMethodAfter?.content || '',
          display: shippingMethodAfter?.display || '',
          color: shippingMethodAfter?.color || '',
          fontSize: shippingMethodAfter?.fontSize || '',
          lineHeight: shippingMethodAfter?.lineHeight || '',
          marginTop: shippingMethodAfter?.marginTop || '',
          marginBottom: shippingMethodAfter?.marginBottom || '',
        },
      } : null,
      notices: [...document.querySelectorAll('.woocommerce-error, .woocommerce-info, .woocommerce-message, .woocommerce-notice, [role="alert"]')].map((el) => clean(el.innerText)),
      duplicateText: Object.fromEntries(Object.entries(duplicateText).filter(([, count]) => count > 1)),
    };
  }, { sectionSelectors: SECTION_SELECTORS, styleProps: STYLE_PROPS });
}

function classify(prod, local, viewport) {
  const issues = [];
  const add = (label, status, detail, evidence = {}) => issues.push({ label, status, detail, evidence });

  if (local.architecture.dependencyStyles.length || local.architecture.dependencyScripts.length || local.architecture.mainDependencyNodes > 0) {
    add('architecture', 'NEEDS FIX', 'Local checkout depends on Elementor/WooLentor assets or rendered nodes', {
      styles: local.architecture.dependencyStyles,
      scripts: local.architecture.dependencyScripts,
      mainDependencyNodes: local.architecture.mainDependencyNodes,
    });
  }

  for (const name of Object.keys(SECTION_SELECTORS)) {
    const prodSection = prod.sections[name];
    const localSection = local.sections[name];
    if (prodSection && !localSection) {
      add(name, 'NEEDS FIX', `Missing checkout section in local: ${name}`, { prodSelector: prodSection.selector });
    } else if (!prodSection && localSection) {
      add(name, 'NEEDS FIX', `Extra checkout section in local: ${name}`, { localSelector: localSection.selector });
    } else if (prodSection && localSection) {
      const hDelta = Math.abs((prodSection.box?.h || 0) - (localSection.box?.h || 0));
      const wDelta = Math.abs((prodSection.box?.w || 0) - (localSection.box?.w || 0));
      const bothZeroHeight = (prodSection.box?.h || 0) === 0 && (localSection.box?.h || 0) === 0;
      if (!bothZeroHeight && (hDelta > 24 || wDelta > 24)) {
        add(name, 'NEEDS FIX', `Section dimensions differ: ${name}`, {
          prodBox: prodSection.box,
          localBox: localSection.box,
        });
      }
      const sectionStyleProps = ['display', 'gridTemplateColumns', 'flexDirection', 'fontSize', 'backgroundColor', 'borderRadius', 'borderCollapse', 'borderSpacing', 'boxShadow']
        .filter((prop) => !(name === 'checkoutForm' && ['display', 'gridTemplateColumns', 'flexDirection'].includes(prop)));
      for (const prop of sectionStyleProps) {
        if ((prodSection.style?.[prop] || '') !== (localSection.style?.[prop] || '')) {
          add(name, 'NEEDS FIX', `Section CSS differs for ${name}.${prop}`, {
            prod: prodSection.style?.[prop] || '',
            local: localSection.style?.[prop] || '',
          });
        }
      }
    }
  }

  const prodFields = prod.fields.map((field) => field.name || field.id || field.label);
  const localFields = local.fields.map((field) => field.name || field.id || field.label);
  if (prodFields.join('|') !== localFields.join('|')) {
    add('billing/shipping field order', 'NEEDS FIX', 'Checkout field order or field set differs', {
      prodFields,
      localFields,
    });
  }

  const prodRows = prod.rows.map((row) => row.cells.join(' | '));
  const localRows = local.rows.map((row) => row.cells.join(' | '));
  if (prodRows.length !== localRows.length) {
    add('order summary rows', 'CONTENT/DATA DIFFERENCE', 'Order summary row count differs; verify product/cart state before visual sign-off', {
      prodRows,
      localRows,
    });
  }

  const visibleHeadings = (capture) => capture.headings
    .filter((heading) => heading.text && (heading.box?.w || 0) > 0 && (heading.box?.h || 0) > 0)
    .map((heading) => ({
      tag: heading.tag,
      text: heading.text,
      box: heading.box,
      style: {
        fontSize: heading.style.fontSize,
        lineHeight: heading.style.lineHeight,
        fontWeight: heading.style.fontWeight,
        color: heading.style.color,
      },
    }));
  const prodHeadings = visibleHeadings(prod);
  const localHeadings = visibleHeadings(local);
  if (prodHeadings.map((heading) => `${heading.tag}:${heading.text}`).join('|') !== localHeadings.map((heading) => `${heading.tag}:${heading.text}`).join('|')) {
    add('checkout headings', 'NEEDS FIX', 'Visible checkout heading sequence differs', {
      prodHeadings,
      localHeadings,
    });
  } else {
    prodHeadings.forEach((prodHeading, index) => {
      const localHeading = localHeadings[index];
      for (const prop of ['fontSize', 'lineHeight', 'fontWeight', 'color']) {
        if ((prodHeading.style?.[prop] || '') !== (localHeading.style?.[prop] || '')) {
          add('checkout headings', 'NEEDS FIX', `Visible heading CSS differs for "${prodHeading.text}": ${prop}`, {
            prod: prodHeading,
            local: localHeading,
          });
        }
      }
      const prodBox = prodHeading.box || {};
      const localBox = localHeading.box || {};
      if (
        Math.abs((prodBox.x || 0) - (localBox.x || 0)) > 8 ||
        Math.abs((prodBox.y || 0) - (localBox.y || 0)) > 8 ||
        Math.abs((prodBox.w || 0) - (localBox.w || 0)) > 8 ||
        Math.abs((prodBox.h || 0) - (localBox.h || 0)) > 4
      ) {
        add('checkout headings', 'NEEDS FIX', `Visible heading dimensions differ for "${prodHeading.text}"`, {
          prod: prodHeading,
          local: localHeading,
        });
      }
    });
  }

  const prodShippingNote = prod.shippingMethod?.after?.content || '';
  const localShippingNote = local.shippingMethod?.after?.content || '';
  if (prodShippingNote !== localShippingNote) {
    add('shipping method note', 'NEEDS FIX', 'Shipping method explanatory note differs', {
      prod: prod.shippingMethod?.after || null,
      local: local.shippingMethod?.after || null,
    });
  }

  const placeOrderProd = prod.buttons.find((button) => button.id === 'place_order' || /สั่งซื้อ|place order/i.test(button.text));
  const placeOrderLocal = local.buttons.find((button) => button.id === 'place_order' || /สั่งซื้อ|place order/i.test(button.text));
  if (placeOrderProd && !placeOrderLocal) {
    add('place order button', 'NEEDS FIX', 'Place order button missing locally', { prod: placeOrderProd });
  } else if (placeOrderProd && placeOrderLocal) {
    for (const prop of ['fontSize', 'backgroundColor', 'color', 'borderRadius', 'paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft']) {
      if ((placeOrderProd.style?.[prop] || '') !== (placeOrderLocal.style?.[prop] || '')) {
        add('place order button', 'NEEDS FIX', `Place order button CSS differs: ${prop}`, {
          prod: placeOrderProd.style?.[prop] || '',
          local: placeOrderLocal.style?.[prop] || '',
          prodBox: placeOrderProd.box,
          localBox: placeOrderLocal.box,
        });
      }
    }
  }

  if (local.viewport.scrollWidth > local.viewport.clientWidth + 2) {
    add('horizontal overflow', 'NEEDS FIX', `Local ${viewport} checkout has horizontal overflow`, {
      scrollWidth: local.viewport.scrollWidth,
      clientWidth: local.viewport.clientWidth,
    });
  }

  for (const [text, count] of Object.entries(local.duplicateText)) {
    if (/stock|สินค้า|คงเหลือ|remaining/i.test(text) && count > (prod.duplicateText[text] || 0)) {
      add('duplicate text', 'NEEDS FIX', `Local duplicate checkout/product text appears more often than prod: "${text}"`, {
        localCount: count,
        prodCount: prod.duplicateText[text] || 0,
      });
    }
  }

  if (!issues.length) {
    add('checkout parity', 'PASS', `No strict checkout differences detected for ${viewport}`);
  }
  return issues;
}

async function run() {
  await fs.mkdir(OUT_DIR, { recursive: true });
  const browser = await chromium.launch({ headless: true });
  const result = {
    createdAt: new Date().toISOString(),
    productId: PRODUCT_ID,
    outDir: OUT_DIR,
    captures: {},
    comparisons: {},
  };

  for (const [viewportName, viewport] of Object.entries(VIEWPORTS)) {
    result.captures[viewportName] = {};
    for (const [envName, base] of Object.entries(ENVS)) {
      console.error(`capturing ${viewportName}/${envName}`);
      const context = await browser.newContext({
        viewport: { width: viewport.width, height: viewport.height },
        isMobile: viewport.isMobile,
        deviceScaleFactor: viewport.deviceScaleFactor,
        ignoreHTTPSErrors: true,
      });
      const page = await context.newPage();
      await seedCart(page, base);
      const screenshotPath = path.join(OUT_DIR, `${viewportName}-${envName}-checkout.png`);
      await page.screenshot({ path: screenshotPath, fullPage: true });
      const capture = await extractPage(page);
      capture.screenshot = screenshotPath;
      result.captures[viewportName][envName] = capture;
      await context.close();
    }
    result.comparisons[viewportName] = classify(
      result.captures[viewportName].prod,
      result.captures[viewportName].local,
      viewportName,
    );
  }

  await browser.close();
  await fs.writeFile(path.join(OUT_DIR, 'checkout-audit.json'), JSON.stringify(result, null, 2));

  const lines = [];
  lines.push(`# Strict Checkout Audit`);
  lines.push('');
  lines.push(`Created: ${result.createdAt}`);
  lines.push(`Product ID: ${PRODUCT_ID}`);
  lines.push('');
  for (const viewportName of Object.keys(VIEWPORTS)) {
    lines.push(`## ${viewportName}`);
    for (const issue of result.comparisons[viewportName]) {
      lines.push(`- **${issue.status}** ${issue.label}: ${issue.detail}`);
    }
    lines.push('');
  }
  await fs.writeFile(path.join(OUT_DIR, 'README.md'), lines.join('\n'));
  console.log(JSON.stringify({ outDir: OUT_DIR, summary: result.comparisons }, null, 2));
}

run().catch((error) => {
  console.error(error);
  process.exit(1);
});
