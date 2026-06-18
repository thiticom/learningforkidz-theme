# Staging Deployment Handoff

This repo is ready for a WordPress staging pass, but it does not currently contain a staging host, deployment workflow, or staging credentials.

## Current State

- Theme code and public report are committed and pushed to `main`.
- GitHub Pages report is live at `https://thiticom.github.io/learningforkidz-theme/`.
- Local audited WordPress URL is `http://100.109.57.34:8085`.
- Production reference URL is `https://www.learningforkidz.com`.
- The repo contains a local Docker development stack only. It does not contain production database rows, uploads, plugin settings, payment settings, mail settings, analytics keys, or staging credentials.

## Required Staging Inputs

Before a real staging deploy can happen, provide these outside git:

- Staging WordPress URL, for example `https://staging.learningforkidz.com`.
- SSH/SFTP or hosting dashboard access for the staging server.
- Staging database access or a managed staging clone.
- Staging `wp-content/uploads` strategy: copied media, shared media, or CDN-backed media.
- Plugin list and plugin settings for WooCommerce, payment, shipping, forms, wishlist, analytics, and caching.
- Clear rule for payment mode: staging must use sandbox/test payment settings only.

## Deployment Shape

1. Create or refresh the staging WordPress instance from a safe production backup.
2. Rewrite URLs from production to staging.
3. Install/sync plugins and uploads required for parity testing.
4. Deploy `themes/lfk-tailwind` from this repo to `wp-content/themes/lfk-tailwind`.
5. Run `npm run build` before deploying or deploy the committed `assets/dist/theme.css`.
6. Activate `lfk-tailwind` on staging.
7. Disable public indexing on staging.
8. Clear all staging caches.

## Staging Audit Commands

Use the same strict audit loop, with staging supplied as the local target:

```bash
export LFK_AUDIT_PROD_BASE="https://www.learningforkidz.com"
export LFK_AUDIT_LOCAL_BASE="https://staging.learningforkidz.com"
export LFK_AUDIT_RUN_LABEL="2026-06-18-staging"

npm run build
docker compose exec -T wordpress php -l /var/www/html/wp-content/themes/lfk-tailwind/functions.php
docker compose exec -T wordpress php -l /var/www/html/wp-content/themes/lfk-tailwind/single-product.php

node reports/product-interaction-audit.mjs
node reports/theme-parity-audit.mjs
node reports/strict-checkout-audit.mjs
node reports/performance-audit.mjs
```

The scripts will write dated staging folders such as:

- `reports/product-interaction-audit-2026-06-18-staging/`
- `reports/theme-parity-audit-2026-06-18-staging/`
- `reports/strict-checkout-audit-2026-06-18-staging/`
- `reports/performance-audit-2026-06-18-staging/`

## Staging Pass Criteria

- Product gallery interactions pass for all four Kanoodle products on desktop and mobile.
- Full visual audit has `0 NEEDS FIX`.
- Cart and checkout remain strict `PASS`.
- Performance report includes request count, encoded KB, DCL, load, LCP, CLS, TBT proxy, JS KB, CSS KB, and image KB.
- Remaining differences are labeled `CONTENT/DATA DIFFERENCE` only when caused by dynamic content, randomized products, media/content data, or staging data differences.

## Current Blocker

The missing item is not code. The blocker is the absence of a real staging WordPress target and deployment credentials/workflow in this repo.
