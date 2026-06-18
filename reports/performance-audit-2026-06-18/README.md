# Performance Audit

Created: 2026-06-18T04:03:25.231Z

Percentages mean percent reduced from production: `(prod - local) / prod * 100`.

## Aggregate

- Median request reduction: 82%
- Median encoded KB reduction: 71.35%
- Median DOMContentLoaded reduction: 86.1%
- Median load event reduction: 86.95%
- Median LCP reduction: 85.7%
- Median TBT proxy reduction: 85.55%
- Median JS KB reduction: 95.9%
- Median CSS KB reduction: 84.2%
- Median image KB reduction: 0.3%

## Routes

- desktop/home: requests 85.9%, KB 56.9%, DCL -0.5%, load 11%, LCP 11.9%, TBT proxy 61.1%
- desktop/shop: requests 79.3%, KB 68.3%, DCL 83.6%, load 84.5%, LCP 81.9%, TBT proxy 34.5%
- desktop/product-category: requests 79.9%, KB 81.9%, DCL 83.6%, load 84.8%, LCP 82.8%, TBT proxy 70.5%
- desktop/product-brand: requests 80.5%, KB 59.7%, DCL 76%, load 77.9%, LCP 75.3%, TBT proxy 70.7%
- desktop/age-taxonomy: requests 81%, KB 84%, DCL 84.5%, load 85.6%, LCP 84%, TBT proxy 57.3%
- desktop/single-product: requests 77.9%, KB 63%, DCL 83%, load 84.3%, LCP 78.5%, TBT proxy 81.8%
- desktop/article-archive: requests 82.9%, KB 78.9%, DCL 90.7%, load 91.2%, LCP 89.4%, TBT proxy 74.6%
- desktop/single-post: requests 81.7%, KB 70.1%, DCL 88.1%, load 88.7%, LCP 87.4%, TBT proxy 42.5%
- desktop/search: requests 78.1%, KB 69.1%, DCL 90.1%, load 89.4%, LCP 89.7%, TBT proxy 83.9%
- desktop/cart: requests 55.6%, KB 50.3%, DCL 79.7%, load 80.4%, LCP 80.4%, TBT proxy 92.9%
- desktop/checkout: requests 51.1%, KB 48.6%, DCL 81%, load 81.4%, LCP 65%, TBT proxy 85.4%
- desktop/my-account: requests 70.7%, KB 64.5%, DCL 86.1%, load 86.4%, LCP 83.5%, TBT proxy 64.9%
- desktop/wishlist: requests 78.1%, KB 77.1%, DCL 88.8%, load 89%, LCP 88.5%, TBT proxy 100%
- desktop/contact: requests 51.7%, KB 30.1%, DCL 81.9%, load 73.9%, LCP 80.8%, TBT proxy 49.4%
- desktop/promotion: requests 81.9%, KB 14.3%, DCL 93.4%, load 93.6%, LCP 92.2%, TBT proxy 97.1%
- desktop/ages-page: requests 83.7%, KB 73.1%, DCL 85.9%, load 86.8%, LCP 84.4%, TBT proxy 66.7%
- desktop/brands-page: requests 82.6%, KB 84.4%, DCL 89%, load 89.7%, LCP 88.2%, TBT proxy 100%
- desktop/about-page: requests 85.4%, KB 72.6%, DCL 93.3%, load 93.5%, LCP 91.8%, TBT proxy 100%
- desktop/refund-page: requests 86.1%, KB 78.8%, DCL 78.4%, load 79.5%, LCP 77.8%, TBT proxy 85.7%
- desktop/how-to-orders: requests 86.7%, KB 90.6%, DCL 92.3%, load 92.6%, LCP 91.5%, TBT proxy 75.9%
- desktop/privacy-page: requests 86.1%, KB 78.8%, DCL 89%, load 89.4%, LCP 88%, TBT proxy 66.7%
- mobile/home: requests 86.5%, KB 52%, DCL 83.8%, load 85.2%, LCP 85.5%, TBT proxy 72.8%
- mobile/shop: requests 81.5%, KB 47.1%, DCL 86.7%, load 87.7%, LCP 85.7%, TBT proxy 100%
- mobile/product-category: requests 82.1%, KB 73.6%, DCL 80.7%, load 82.4%, LCP 80.4%, TBT proxy 88.9%
- mobile/product-brand: requests 82.4%, KB 61.4%, DCL 77.6%, load 79.2%, LCP 76.8%, TBT proxy 65.5%
- mobile/age-taxonomy: requests 81.5%, KB 76.1%, DCL 86.1%, load 87.1%, LCP 85.7%, TBT proxy 100%
- mobile/single-product: requests 78.4%, KB 59.9%, DCL 85.4%, load 86.5%, LCP 84.5%, TBT proxy 83.3%
- mobile/article-archive: requests 86.3%, KB 73.6%, DCL 91.6%, load 92%, LCP 90.4%, TBT proxy 100%
- mobile/single-post: requests 84.8%, KB 75.1%, DCL 88.5%, load 89.2%, LCP 87.7%, TBT proxy 66.7%
- mobile/search: requests 82.1%, KB 72.8%, DCL 91.7%, load 92.1%, LCP 91.5%, TBT proxy 100%
- mobile/cart: requests 57.7%, KB 45.2%, DCL 77.3%, load 78.3%, LCP 78%, TBT proxy 98.6%
- mobile/checkout: requests 53%, KB 43.7%, DCL 79.6%, load 79.8%, LCP 84.6%, TBT proxy 85.4%
- mobile/my-account: requests 72.9%, KB 61%, DCL 85.3%, load 86.2%, LCP 83.1%, TBT proxy 91.9%
- mobile/wishlist: requests 80.5%, KB 74.4%, DCL 89.5%, load 89.9%, LCP 88.9%, TBT proxy 100%
- mobile/contact: requests 69.5%, KB 37.8%, DCL 86%, load 74.9%, LCP 86%, TBT proxy -54.5%
- mobile/promotion: requests 82.5%, KB 44.5%, DCL 92.4%, load 92.7%, LCP 91.2%, TBT proxy 100%
- mobile/ages-page: requests 88.6%, KB 88.1%, DCL 55.9%, load 59.5%, LCP 57.5%, TBT proxy 100%
- mobile/brands-page: requests 87.9%, KB 90.1%, DCL 90%, load 90.5%, LCP 89.6%, TBT proxy 100%
- mobile/about-page: requests 88%, KB 69.2%, DCL 92.1%, load 92.4%, LCP 90.9%, TBT proxy 100%
- mobile/refund-page: requests 88.7%, KB 76.3%, DCL 92.4%, load 92.7%, LCP 91.6%, TBT proxy 100%
- mobile/how-to-orders: requests 87.4%, KB 90.6%, DCL 93.1%, load 93.4%, LCP 92.5%, TBT proxy 100%
- mobile/privacy-page: requests 90.4%, KB 92.4%, DCL 89.2%, load 89.8%, LCP 89%, TBT proxy 100%
