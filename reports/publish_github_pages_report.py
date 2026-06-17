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

    (DOCS_DIR / "README-pages.md").write_text(
        "# GitHub Pages Report\n\n"
        "Serve this folder with GitHub Pages using the repository `main` branch and `/docs` folder.\n\n"
        "- `index.html`: Thai team communication brief\n"
        "- `team-brief-th.pdf`: downloadable PDF\n"
        "- `lfk-section-audit-2026-06-11/`: full visual audit\n",
        encoding="utf-8",
    )

    print(DOCS_DIR)


if __name__ == "__main__":
    main()
