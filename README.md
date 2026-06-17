# Learning for Kidz Theme

Custom WordPress/WooCommerce theme source for `learningforkidz.com`.

## What It Contains

- `themes/lfk-tailwind`: custom WordPress theme templates and assets.
- `package.json`: Tailwind CSS build scripts for the theme CSS bundle.
- `docker-compose.yml`: local WordPress and MariaDB development stack.
- `AGENTS.md`: coding-agent operating rules for this workspace.

This checkout is a source/theme clone. Production database rows, uploads, plugin
settings, and private credentials are not included.

## Developer Docs

- `AGENTS.md` contains agent operating rules.
- `docs/application-context.md` explains the theme, runtime assumptions, and external dependencies.
- `docs/remote-development.md` covers the Hetzner Codex development folder and Docker workflow.
- `docs/verification.md` lists build and runtime checks.

## Local Theme Build

```bash
npm install
npm run build
```

For ongoing CSS work:

```bash
npm run watch
```

## Docker Development

```bash
cp .env.example .env
docker compose up -d db wordpress
docker compose run --rm cli wp core install \
  --url=http://100.109.57.34:8085 \
  --title="Learning for Kidz Local" \
  --admin_user=admin \
  --admin_password=admin \
  --admin_email=admin@example.test \
  --skip-email
docker compose run --rm cli wp plugin install woocommerce --activate
docker compose run --rm cli wp theme activate lfk-tailwind
```

Default local URL:

```text
http://100.109.57.34:8085
```
