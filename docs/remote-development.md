# Remote Development

Use this when working on the Hetzner development copy.

## Host And Folder

```bash
ssh thaiada@2nd-hetz-server
cd /home/thaiada/codex/learningforkidz.com
```

The remote copy is intended for source/theme development. Do not place
production database dumps, uploads, or secrets in git.

## First Setup

```bash
cp .env.example .env
npm install
npm run build
docker compose up -d db wordpress
```

Install WordPress and activate the theme:

```bash
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

## Access From Your Machine

The compose stack binds to the Hetzner host's Tailscale IPv4 address:

```text
http://100.109.57.34:8085
```

This requires your machine to be on the same Tailscale network. No SSH tunnel is
needed for normal browser access.

## Common Commands

```bash
npm run build
docker compose ps
docker compose logs --tail=80 wordpress
docker compose run --rm cli wp theme list
docker compose run --rm cli wp plugin list
```

## Updating The Remote Copy

From the local machine:

```bash
rsync -az --delete \
  --exclude .git \
  --exclude node_modules \
  --exclude .env \
  --exclude .DS_Store \
  ./ thaiada@2nd-hetz-server:/home/thaiada/codex/learningforkidz.com/
```

After syncing:

```bash
cd /home/thaiada/codex/learningforkidz.com
npm install
npm run build
docker compose up -d db wordpress
```

## Production Clone Notes

A complete production clone needs separate, deliberate handling of:

- WordPress database dump with URLs rewritten for the dev URL.
- `wp-content/uploads`.
- Plugin list and plugin settings.
- Private API keys, payment settings, mail credentials, and analytics tags.

Do not copy those into this source repo without an explicit task and a verified
source backup.
