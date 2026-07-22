#!/usr/bin/env bash
set -euo pipefail

environment=${1:-}
original_command=${SSH_ORIGINAL_COMMAND:-}

case "$environment" in
  staging)
    wp_root=/home/thaiada/domains/staging.learningforkidz.com/public_html
    ;;
  production)
    wp_root=/home/thaiada/domains/learningforkidz.com/public_html
    ;;
  *)
    exit 64
    ;;
esac

if [[ ! "$original_command" =~ ^deploy\ ([0-9a-f]{40})$ ]]; then
  exit 64
fi

commit=${BASH_REMATCH[1]}
repository=thiticom/learningforkidz-theme
theme_name=lfk-tailwind
release_root=/home/thaiada/theme-releases/${environment}/${theme_name}
releases=${release_root}/releases
release=${releases}/${commit}
current=${release_root}/current
theme_path=${wp_root}/wp-content/themes/${theme_name}

mkdir -p "$releases"

if [[ ! -d "$release" ]]; then
  workdir=$(mktemp -d "${release_root}/.deploy.XXXXXX")
  trap 'rm -rf "$workdir"' EXIT

  curl --fail --silent --show-error --location --proto '=https' \
    "https://github.com/${repository}/archive/${commit}.tar.gz" \
    | tar -xz -C "$workdir"

  source_theme=${workdir}/learningforkidz-theme-${commit}/themes/${theme_name}
  test -f "${source_theme}/style.css"
  test -f "${source_theme}/functions.php"
  test -f "${source_theme}/assets/dist/theme.css"
  php -l "${source_theme}/functions.php" >/dev/null
  php -l "${source_theme}/single-product.php" >/dev/null

  mv "$source_theme" "$release"
fi

next_link=${release_root}/.current-${commit}
ln -s "$release" "$next_link"
mv -Tf "$next_link" "$current"

if [[ ! -L "$theme_path" ]]; then
  backup=${theme_path}.pre-git-$(date -u +%Y%m%d-%H%M%S)
  next_theme=${theme_path}.next
  ln -s "$current" "$next_theme"
  mv "$theme_path" "$backup"
  mv "$next_theme" "$theme_path"
fi

wp --path="$wp_root" litespeed-purge all >/dev/null
printf '%s %s\n' "$environment" "$commit"
