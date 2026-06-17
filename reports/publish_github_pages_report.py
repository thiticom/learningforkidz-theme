#!/usr/bin/env python3
from __future__ import annotations

import shutil
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
DOCS_DIR = ROOT / "docs"
TEAM_REPORT_DIR = ROOT / "reports" / "ai-marketing-foundation-team-brief-2026-06-12"
FULL_AUDIT_DIR = ROOT / "reports" / "lfk-section-audit-2026-06-11"


GENERATED_FILES = [
    "index.html",
    "report.html",
    "README-pages.md",
    "source-notes.json",
    "team-brief-th.pdf",
    "team-message-th.md",
    "version-log.html",
]


def remove_generated_docs() -> None:
    for name in GENERATED_FILES:
        path = DOCS_DIR / name
        if path.exists():
            path.unlink()
    for name in ["assets", "lfk-section-audit-2026-06-11"]:
        path = DOCS_DIR / name
        if path.exists():
            shutil.rmtree(path)


def copy_tree(src: Path, dest: Path) -> None:
    if dest.exists():
        shutil.rmtree(dest)
    shutil.copytree(src, dest)


def public_html(source: str) -> str:
    source = source.replace(
        '        <a class="button" href="http://100.109.57.34:8085">เปิด Local Test Site</a>\n',
        "",
    )
    source = source.replace('href="/lfk-section-audit-2026-06-11/"', 'href="lfk-section-audit-2026-06-11/"')
    source = source.replace(
        'href="lfk-section-audit-2026-06-11/">/lfk-section-audit-2026-06-11/</a>',
        'href="lfk-section-audit-2026-06-11/">Full audit</a>',
    )
    return source


def public_audit_html(source: str) -> str:
    return source.replace(
        'Local test URL: <a href="http://100.109.57.34:8085">http://100.109.57.34:8085</a>. ',
        "Local test URL was Tailscale-only during QA. ",
    )


def public_team_message(source: str) -> str:
    return source.replace(
        "- Team brief: http://100.109.57.34:8085/ai-marketing-foundation-team-brief-2026-06-12/\n"
        "- Full section audit: http://100.109.57.34:8085/lfk-section-audit-2026-06-11/\n",
        "- Team brief: GitHub Pages homepage for this repo\n"
        "- Full section audit: `lfk-section-audit-2026-06-11/`\n",
    )


def version_log_html() -> str:
    return """<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Learning For Kidz Version Log</title>
  <style>
    :root {
      --ink: #18202f;
      --muted: #5d6678;
      --line: #dfe4ec;
      --panel: #ffffff;
      --surface: #f7f9fb;
      --cyan: #42bedd;
      --blue: #2f6f9f;
      --orange: #cc6f47;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      color: var(--ink);
      background: var(--surface);
      font-family: "Noto Sans Thai", "Tahoma", "Segoe UI", Arial, sans-serif;
      line-height: 1.65;
    }
    a { color: var(--blue); }
    .page {
      width: min(980px, calc(100% - 32px));
      margin: 0 auto;
      padding: 36px 0 64px;
    }
    .eyebrow {
      margin: 0 0 8px;
      color: var(--orange);
      font-size: 0.92rem;
      font-weight: 700;
    }
    h1 {
      margin: 0;
      font-size: 3rem;
      line-height: 1.08;
      letter-spacing: 0;
    }
    h2 {
      margin: 0 0 10px;
      font-size: 1.7rem;
      line-height: 1.25;
      letter-spacing: 0;
    }
    p { margin: 0 0 12px; }
    .lead {
      margin-top: 16px;
      color: var(--muted);
      font-size: 1.05rem;
      max-width: 760px;
    }
    .actions {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 22px;
      padding-bottom: 22px;
      border-bottom: 1px solid var(--line);
    }
    .button {
      display: inline-flex;
      align-items: center;
      min-height: 42px;
      padding: 9px 14px;
      border: 1px solid var(--line);
      border-radius: 6px;
      background: var(--panel);
      color: var(--ink);
      text-decoration: none;
      font-weight: 700;
    }
    .button.primary {
      border-color: var(--cyan);
      background: var(--cyan);
      color: #082330;
    }
    section { margin-top: 30px; }
    .entry, .process {
      background: var(--panel);
      border: 1px solid var(--line);
      border-radius: 8px;
      padding: 18px;
    }
    .entry + .entry { margin-top: 14px; }
    .meta {
      color: var(--muted);
      font-size: 0.94rem;
      margin-bottom: 10px;
    }
    ul { margin: 8px 0 0; padding-left: 22px; }
    li + li { margin-top: 6px; }
    .status {
      display: inline-flex;
      border-radius: 999px;
      padding: 4px 10px;
      background: #e6f6ed;
      color: #1e6a3d;
      font-weight: 800;
      font-size: 0.86rem;
    }
    @media (max-width: 520px) {
      h1 { font-size: 2.2rem; }
      h2 { font-size: 1.45rem; }
      .button { width: 100%; justify-content: center; }
    }
  </style>
</head>
<body>
  <main class="page">
    <p class="eyebrow">Public Report Version Log</p>
    <h1>Learning For Kidz: Version / Update Log</h1>
    <p class="lead">หน้านี้เป็น log สำหรับทีม ไม่ใช่ technical Git log. ใช้ดูว่า report เวอร์ชันล่าสุดเปลี่ยนอะไร และควรแชร์ลิงก์ไหน.</p>
    <div class="actions">
      <a class="button primary" href="./">เปิด Report ล่าสุด</a>
      <a class="button" href="strict-checkout-audit-2026-06-17-pure/">Pure Checkout PASS Evidence</a>
      <a class="button" href="theme-parity-audit-2026-06-17-cart-pure/">Pure Cart PASS Evidence</a>
      <a class="button" href="team-brief-th.pdf">Download PDF ล่าสุด</a>
      <a class="button" href="https://github.com/thiticom/learningforkidz-theme/commits/main">Technical Commit Log</a>
    </div>

    <section>
      <h2>Current Version</h2>
      <article class="entry">
        <div class="meta"><span class="status">Current</span> Version v2026.06.17.6 - Pure custom checkout parity checkpoint</div>
        <p><strong>What changed:</strong> Rebuilt checkout as a pure custom WooCommerce/Tailwind template while using production Elementor only as the visual reference.</p>
        <ul>
          <li>Checkout now passes strict parity on desktop and mobile.</li>
          <li>Matched billing/shipping fields, coupon box, order summary rows, shipping note, payment methods, terms/privacy area, place order button, mobile stacking, and footer position.</li>
          <li>Broader checkout route audit also passes with zero Elementor/WooLentor dependency styles, scripts, or rendered main-content nodes.</li>
          <li>Published the valid checkpoint with screenshots: <a href="strict-checkout-audit-2026-06-17-pure/">open pure custom checkout PASS evidence</a>.</li>
        </ul>
        <p><strong>Verification:</strong> CSS build passed, strict checkout audit reports PASS for desktop and mobile, broader checkout route audit reports PASS for desktop and mobile, and architecture evidence shows no Elementor/WooLentor runtime dependency in the custom checkout path.</p>
      </article>

      <article class="entry">
        <div class="meta">Version v2026.06.17.5 - Pure custom cart parity checkpoint</div>
        <p><strong>What changed:</strong> Rebuilt cart parity using the pure custom Tailwind/WooCommerce path. Production Elementor remains the visual blueprint only.</p>
        <ul>
          <li>Cart page now passes the strict parity audit on desktop and mobile.</li>
          <li>Matched product table/card layout, coupon area, cart totals, shipping label/radios, checkout button, page height, and footer position.</li>
          <li>Hardened the parity audit to compare the real content section, buttons, and form field geometry/CSS.</li>
          <li>Published the valid checkpoint with screenshots: <a href="theme-parity-audit-2026-06-17-cart-pure/">open pure custom cart PASS evidence</a>.</li>
        </ul>
        <p><strong>Verification:</strong> CSS build passed, PHP lint passed, strict cart audit reports PASS for desktop and mobile, and local architecture evidence shows no Elementor/WooLentor styles, scripts, or rendered main-content nodes.</p>
      </article>

      <article class="entry">
        <div class="meta">Version v2026.06.17.4 - Pure custom theme architecture correction</div>
        <p><strong>What changed:</strong> Updated the implementation direction so production Elementor is used only as the visual reference. Local/staging cart and checkout now use custom WooCommerce/Tailwind templates instead of rendering stored Elementor page content.</p>
        <ul>
          <li>Removed the cart/checkout path that rendered Elementor page content locally.</li>
          <li>Removed cloned Elementor upload CSS rewriting and trimmed Elementor/WooLentor assets from cart/checkout while preserving WooCommerce checkout/payment scripts.</li>
          <li>Updated the parity audit harness with an architecture gate: local pages fail if they require Elementor/WooLentor assets or rendered main-content nodes.</li>
        </ul>
        <p><strong>Verification:</strong> CSS build passed, PHP lint passed for functions/cart/checkout/single product, and local cart/checkout architecture checks found no Elementor/WooLentor styles, scripts, or main-content nodes.</p>
      </article>

      <article class="entry">
        <div class="meta">Version v2026.06.17.2 - Superseded checkout and footer parity checkpoint</div>
        <p><strong>What changed:</strong> Tightened the custom theme checkout page against production Elementor/WooLentor output and added a repeatable strict checkout audit script.</p>
        <ul>
          <li>Checkout now renders the stored Elementor checkout content instead of the generic shortcode shell.</li>
          <li>Matched checkout title, order summary, shipping note, payment/terms spacing, button color, and desktop/mobile page height.</li>
          <li>Matched the custom footer desktop/mobile height and added the compact mobile footer/copyright behavior for team-visible pages.</li>
          <li>Added `reports/strict-checkout-audit.mjs` so future checkout checks compare prod/local screenshots, DOM sections, fields, rows, headings, CSS, and dimensions.</li>
        </ul>
        <p><strong>Verification:</strong> PHP lint and CSS build passed. Strict checkout audit passed on the last successful live-prod run; final footer dimensions were rechecked locally against cached prod metrics because production timed out during the final rerun.</p>
      </article>

      <article class="entry">
        <div class="meta">Version v2026.06.17.1 - Public team sharing release</div>
        <p><strong>What changed:</strong> Published the Thai AI Marketing Foundation report to public GitHub Pages, with PDF download and full section audit.</p>
        <ul>
          <li>Added public report URL for non-Tailscale sharing.</li>
          <li>Added full screenshot audit and PDF download.</li>
          <li>Clarified that performance percentages mean “percent reduced from production.”</li>
          <li>Committed the custom WordPress theme source and GitHub Pages report package.</li>
        </ul>
        <p><strong>Verification:</strong> Public report, PDF, and full audit returned 200; browser check found no broken images and no horizontal overflow.</p>
      </article>
    </section>

    <section>
      <h2>How Future Versions Should Work</h2>
      <article class="process">
        <ul>
          <li>Create or update the theme/report locally.</li>
          <li>Run the audit and regenerate the public report assets.</li>
          <li>Add a new entry to this version log with date, purpose, main changes, and verification result.</li>
          <li>Commit and push to GitHub. GitHub Pages will automatically publish the latest `docs/` version.</li>
          <li>For important milestones, keep the old PDF or make a dated folder if the team needs to compare versions.</li>
        </ul>
      </article>
    </section>
  </main>
</body>
</html>
"""


def main() -> None:
    if not TEAM_REPORT_DIR.exists():
        raise FileNotFoundError(TEAM_REPORT_DIR)
    if not FULL_AUDIT_DIR.exists():
        raise FileNotFoundError(FULL_AUDIT_DIR)

    DOCS_DIR.mkdir(exist_ok=True)
    remove_generated_docs()

    copy_tree(TEAM_REPORT_DIR / "assets", DOCS_DIR / "assets")
    copy_tree(FULL_AUDIT_DIR, DOCS_DIR / "lfk-section-audit-2026-06-11")
    audit_index = DOCS_DIR / "lfk-section-audit-2026-06-11" / "index.html"
    audit_index.write_text(public_audit_html(audit_index.read_text(encoding="utf-8")), encoding="utf-8")

    html = public_html((TEAM_REPORT_DIR / "index.html").read_text(encoding="utf-8"))
    (DOCS_DIR / "index.html").write_text(html, encoding="utf-8")
    (DOCS_DIR / "report.html").write_text(html, encoding="utf-8")

    for name in ["source-notes.json", "team-brief-th.pdf"]:
        shutil.copy2(TEAM_REPORT_DIR / name, DOCS_DIR / name)

    team_message = public_team_message((TEAM_REPORT_DIR / "team-message-th.md").read_text(encoding="utf-8"))
    (DOCS_DIR / "team-message-th.md").write_text(team_message, encoding="utf-8")
    (DOCS_DIR / "version-log.html").write_text(version_log_html(), encoding="utf-8")

    (DOCS_DIR / "README-pages.md").write_text(
        "# GitHub Pages Report\n\n"
        "Serve this folder with GitHub Pages using the repository `main` branch and `/docs` folder.\n\n"
        "- `index.html`: Thai team communication brief\n"
        "- `team-brief-th.pdf`: downloadable PDF\n"
        "- `version-log.html`: reader-facing version and update log\n"
        "- `lfk-section-audit-2026-06-11/`: full visual audit\n"
        "- `strict-checkout-audit-2026-06-17-pure/`: pure custom checkout PASS evidence\n"
        "- `theme-parity-audit-2026-06-17-cart-pure/`: pure custom cart PASS evidence\n",
        encoding="utf-8",
    )

    print(DOCS_DIR)


if __name__ == "__main__":
    main()
