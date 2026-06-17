# Verification

## Source Checks

```bash
npm install
npm run build
git status --short
```

Expected build output:

```text
themes/lfk-tailwind/assets/dist/theme.css
```

## Remote Stack Checks

```bash
ssh thaiada@2nd-hetz-server
cd /home/thaiada/codex/learningforkidz.com
docker compose ps
curl -fsSI http://100.109.57.34:8085 | head
docker compose run --rm cli wp theme status lfk-tailwind
docker compose run --rm cli wp plugin status woocommerce
```

## Browser Checks

After opening `http://100.109.57.34:8085` from a machine on the same Tailscale
network, check:

- Home page renders with the `lfk-tailwind` theme active.
- Shop/product routes render after WooCommerce has sample or imported products.
- Cart, checkout, account, contact, wishlist, age, and brand pages do not fatally error.
- Built CSS changes appear after `npm run build`.

## Known Limits

Without a production database/import, WooCommerce product grids, brand and age
taxonomy pages, wishlist output, contact form output, menus, and media-heavy
homepage sections may be empty or simplified.
