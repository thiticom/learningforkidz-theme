# Application Context

## Scope

This project contains the custom Tailwind-based WordPress theme for
`learningforkidz.com`. It is not a full production WordPress export.

## Main Paths

- `themes/lfk-tailwind/functions.php`: theme setup, asset loading, search behavior, local-site dequeue rules, product card helpers, and SVG icons.
- `themes/lfk-tailwind/*.php`: page, archive, taxonomy, cart, checkout, account, wishlist, product, post, and search templates.
- `themes/lfk-tailwind/template-parts/`: reusable homepage, product archive, age navigation, and carousel sections.
- `themes/lfk-tailwind/assets/src/theme.css`: Tailwind source CSS.
- `themes/lfk-tailwind/assets/dist/theme.css`: built CSS loaded by WordPress.
- `themes/lfk-tailwind/assets/js/theme.js`: frontend interactions.

## Runtime Assumptions

- WordPress runs the theme from `wp-content/themes/lfk-tailwind`.
- WooCommerce is expected for product, cart, checkout, account, and shop routes.
- The theme references `product_brand` and `age` taxonomies.
- The wishlist page expects a `[wishlist_page]` shortcode provider.
- Contact page styling assumes the production contact form plugin may be present.
- Some homepage assets intentionally resolve through production upload URLs.

## Development Rule

Keep local changes focused on the theme source unless the user explicitly asks
for production content, plugin settings, or database migration work.
