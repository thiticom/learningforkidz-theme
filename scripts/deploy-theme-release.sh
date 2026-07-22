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
release_root=${wp_root}/wp-content/themes/.lfk-releases/${theme_name}
releases=${release_root}/releases
release=${releases}/${commit}
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

next_theme=${theme_path}.next-${commit}
previous_commit=pre-git
if [[ -f "${release_root}/deployed-commit" ]]; then
  previous_commit=$(cat "${release_root}/deployed-commit")
fi
backup=${release_root}/previous/${previous_commit}-$(date -u +%Y%m%d-%H%M%S)
mkdir -p "${release_root}/previous"
cp -a "$release" "$next_theme"
mv "$theme_path" "$backup"
mv "$next_theme" "$theme_path"
printf '%s\n' "$commit" > "${release_root}/deployed-commit"

wp --path="$wp_root" litespeed-purge all >/dev/null
printf '%s %s\n' "$environment" "$commit"
