# Learning for Kidz Theme Section Audit

Local: http://100.109.57.34:8085
Prod: https://www.learningforkidz.com

## Summary

Yes, measured page load improves significantly on the local custom theme. Median request reduction: 79.8%. Median decoded payload reduction: 64.1%.

Timing is directional only because prod and local are different hosts/CDN paths. Request count and decoded payload are the strongest evidence.

## Performance Table

| Page | Requests prod -> local | Payload prod -> local | Load prod -> local |
|---|---:|---:|---:|
| home | 355 -> 83 | 18174 -> 10478 KB | 5089 -> 704 ms |
| shop | 347 -> 66 | 18269 -> 6351 KB | 5331 -> 511 ms |
| product-category | 346 -> 65 | 17455 -> 5947 KB | 8443 -> 665 ms |
| product-brand | 346 -> 66 | 17696 -> 6483 KB | 5158 -> 740 ms |
| age-taxonomy | 344 -> 63 | 17430 -> 5898 KB | 4836 -> 625 ms |
| single-product | 363 -> 72 | 18561 -> 6030 KB | 5553 -> 632 ms |
| article-archive | 351 -> 68 | 20028 -> 7087 KB | 8086 -> 574 ms |
| single-post | 364 -> 69 | 18412 -> 6149 KB | 7935 -> 532 ms |
| search | 353 -> 71 | 18360 -> 6294 KB | 7076 -> 597 ms |
| cart | 378 -> 117 | 18598 -> 8366 KB | 7897 -> 1935 ms |
| checkout | 400 -> 137 | 18889 -> 8616 KB | 9377 -> 1711 ms |
| my-account | 357 -> 85 | 18282 -> 6968 KB | 7096 -> 634 ms |
| wishlist | 337 -> 68 | 17330 -> 6003 KB | 18482 -> 481 ms |
| contact | 352 -> 91 | 18768 -> 9460 KB | 8183 -> 1146 ms |
| promotion | 339 -> 67 | 17557 -> 6308 KB | 13712 -> 516 ms |
| ages-page | 344 -> 57 | 17223 -> 5834 KB | 8522 -> 546 ms |
| brands-page | 350 -> 71 | 17226 -> 6044 KB | 8273 -> 577 ms |
| about-page | 340 -> 150 | 17358 -> 9482 KB | 17972 -> 4037 ms |
| refund-page | 339 -> 149 | 17629 -> 9340 KB | 7866 -> 3937 ms |
| how-to-orders | 338 -> 155 | 17071 -> 9400 KB | 12009 -> 4048 ms |
| privacy-page | 338 -> 149 | 17327 -> 9341 KB | 11475 -> 3966 ms |

## Screenshot Report

Open ./index.html for per-section desktop/mobile prod-vs-local screenshots.
