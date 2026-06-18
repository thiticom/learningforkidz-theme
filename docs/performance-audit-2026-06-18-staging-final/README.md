# Performance Audit

Created: 2026-06-18T07:40:49.776Z

Percentages mean percent reduced from production: `(prod - local) / prod * 100`.

## Aggregate

- Median request reduction: 78.85%
- Median encoded KB reduction: 67.2%
- Median DOMContentLoaded reduction: 18.7%
- Median load event reduction: 25.35%
- Median LCP reduction: 20.45%
- Median TBT proxy reduction: 85.6%
- Median JS KB reduction: 94.5%
- Median CSS KB reduction: 79.8%
- Median image KB reduction: 0.8%

## Routes

- desktop/home: requests 82.7%, KB 50.3%, DCL 1.8%, load 13.7%, LCP 14.2%, TBT proxy 52.2%
- desktop/shop: requests 76%, KB 60.7%, DCL 2.8%, load 12.1%, LCP 11.6%, TBT proxy 61.8%
- desktop/product-category: requests 76.5%, KB 73.1%, DCL -26.2%, load -15.2%, LCP -15.9%, TBT proxy 55.1%
- desktop/product-brand: requests 77.2%, KB 70.2%, DCL 10.5%, load 19.8%, LCP 19.2%, TBT proxy 56.2%
- desktop/age-taxonomy: requests 77.6%, KB 74.9%, DCL 7.2%, load 16.7%, LCP 15%, TBT proxy 86.7%
- desktop/single-product: requests 75%, KB 56.7%, DCL 39.4%, load 44.7%, LCP 28.9%, TBT proxy 62.5%
- desktop/article-archive: requests 79.1%, KB 63%, DCL -14.4%, load -3.3%, LCP -16%, TBT proxy 85%
- desktop/single-post: requests 80.2%, KB 69.6%, DCL 17.2%, load 25%, LCP 22.8%, TBT proxy 77.6%
- desktop/search: requests 76.2%, KB 68.8%, DCL 10.5%, load 18.4%, LCP -6.2%, TBT proxy 70.4%
- desktop/cart: requests 59.4%, KB 58.6%, DCL 21.1%, load 25.8%, LCP 15.5%, TBT proxy 87.5%
- desktop/checkout: requests 54.8%, KB 57.4%, DCL -7.2%, load -1.4%, LCP -95.9%, TBT proxy 59.4%
- desktop/my-account: requests 68.8%, KB 61.7%, DCL 9.3%, load 16.5%, LCP -5.9%, TBT proxy 69.9%
- desktop/wishlist: requests 74.5%, KB 68.4%, DCL 24.5%, load 29.9%, LCP 23%, TBT proxy 77%
- desktop/contact: requests 48.8%, KB 22.7%, DCL 27.9%, load 24.8%, LCP 26.3%, TBT proxy -65.3%
- desktop/promotion: requests 75.5%, KB 6.1%, DCL 16.8%, load 25.4%, LCP 23.2%, TBT proxy 98.1%
- desktop/ages-page: requests 80.3%, KB 65.3%, DCL 14.2%, load 21.2%, LCP 19.1%, TBT proxy 91.2%
- desktop/brands-page: requests 79.2%, KB 75.4%, DCL 19.3%, load 25.7%, LCP 24.5%, TBT proxy 77.8%
- desktop/about-page: requests 81.8%, KB 69.7%, DCL 25.3%, load 30%, LCP 29.5%, TBT proxy 92.4%
- desktop/refund-page: requests 82.5%, KB 70.3%, DCL 20.1%, load 26.6%, LCP 17.8%, TBT proxy 82.3%
- desktop/how-to-orders: requests 78.9%, KB 68.5%, DCL 15%, load 22.8%, LCP 21.7%, TBT proxy 84.9%
- desktop/privacy-page: requests 82.4%, KB 70.3%, DCL 18.6%, load 26%, LCP 15.7%, TBT proxy 71.9%
- mobile/home: requests 83.3%, KB 44.7%, DCL 35.7%, load 40.2%, LCP 43%, TBT proxy 91.2%
- mobile/shop: requests 78.1%, KB 42%, DCL -16.5%, load -5.9%, LCP -7.5%, TBT proxy 87.5%
- mobile/product-category: requests 78.6%, KB 65.8%, DCL 16.2%, load 22.7%, LCP 21.8%, TBT proxy 89.2%
- mobile/product-brand: requests 79.1%, KB 67%, DCL 18.8%, load 26.3%, LCP 25.9%, TBT proxy 98.9%
- mobile/age-taxonomy: requests 79.1%, KB 68.7%, DCL 39.6%, load 43.2%, LCP 42.6%, TBT proxy 100%
- mobile/single-product: requests 75.5%, KB 54.7%, DCL 19.9%, load 27.2%, LCP 25.5%, TBT proxy 100%
- mobile/article-archive: requests 83.6%, KB 68.2%, DCL 25.5%, load 31.2%, LCP 29.5%, TBT proxy 97.8%
- mobile/single-post: requests 81.5%, KB 64.2%, DCL 21.5%, load 29.4%, LCP 28.5%, TBT proxy 74.8%
- mobile/search: requests 79.6%, KB 72.7%, DCL 22.4%, load 26.6%, LCP 10.3%, TBT proxy 86.2%
- mobile/cart: requests 61.6%, KB 54.2%, DCL -21.3%, load -13.3%, LCP -33.7%, TBT proxy 100%
- mobile/checkout: requests 56.7%, KB 53.1%, DCL 29.6%, load 32.3%, LCP 30.3%, TBT proxy 100%
- mobile/my-account: requests 71%, KB 58%, DCL 14.2%, load 20.7%, LCP 1%, TBT proxy 78.6%
- mobile/wishlist: requests 77.4%, KB 67.4%, DCL 18.2%, load 25%, LCP 15.3%, TBT proxy 100%
- mobile/contact: requests 66.2%, KB 31%, DCL 31.4%, load 27.5%, LCP 22.9%, TBT proxy 34.7%
- mobile/promotion: requests 78.8%, KB 39.6%, DCL 24.7%, load 30.8%, LCP 27.9%, TBT proxy 92.8%
- mobile/ages-page: requests 85.7%, KB 81.6%, DCL 23.2%, load 27.6%, LCP 27.2%, TBT proxy 97.4%
- mobile/brands-page: requests 84.9%, KB 83.3%, DCL 11.9%, load 19.8%, LCP 18.9%, TBT proxy 93.3%
- mobile/about-page: requests 85%, KB 68.7%, DCL 0.3%, load 7.4%, LCP 7.1%, TBT proxy 72.1%
- mobile/refund-page: requests 87%, KB 85.5%, DCL 38.2%, load 43%, LCP 30%, TBT proxy 67.8%
- mobile/how-to-orders: requests 84.3%, KB 83.8%, DCL 23.1%, load 28.9%, LCP 27.8%, TBT proxy 90%
- mobile/privacy-page: requests 86.9%, KB 85.5%, DCL 19.2%, load 25.3%, LCP 15.5%, TBT proxy 100%
