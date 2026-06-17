#!/usr/bin/env python3
from __future__ import annotations

import html
import json
import os
import shutil
from pathlib import Path

os.environ.setdefault("MPLCONFIGDIR", "/tmp/matplotlib")

import matplotlib

matplotlib.use("Agg")

import matplotlib.pyplot as plt
import matplotlib.ticker as mticker
import pandas as pd
import seaborn as sns


ROOT = Path(__file__).resolve().parents[1]
AUDIT_DIR = ROOT / "reports" / "lfk-section-audit-2026-06-11"
OUT_DIR = ROOT / "reports" / "ai-marketing-foundation-team-brief-2026-06-12"
ASSET_DIR = OUT_DIR / "assets"

LOCAL_SITE_URL = "http://100.109.57.34:8085"
LOCAL_REPORT_PATH = "/ai-marketing-foundation-team-brief-2026-06-12/"
FULL_AUDIT_PATH = "/lfk-section-audit-2026-06-11/"

FONT_FAMILY = ["DejaVu Sans", "Inter", "Segoe UI", "Arial"]
MONO_FONT_FAMILY = ["SF Mono", "Menlo", "Consolas", "DejaVu Sans Mono", "monospace"]

TOKENS = {
    "surface": "#FCFCFD",
    "panel": "#FFFFFF",
    "ink": "#1F2430",
    "muted": "#6F768A",
    "grid": "#E6E8F0",
    "axis": "#D7DBE7",
}

COLOR_FAMILIES = {
    "blue": {
        "open": TOKENS["panel"],
        "xlight": "#EAF1FE",
        "light": "#CEDFFE",
        "base": "#A3BEFA",
        "mid": "#5477C4",
        "dark": "#2E4780",
    },
    "orange": {
        "open": TOKENS["panel"],
        "xlight": "#FFEDDE",
        "light": "#FFBDA1",
        "base": "#F0986E",
        "mid": "#CC6F47",
        "dark": "#804126",
    },
    "olive": {
        "open": TOKENS["panel"],
        "xlight": "#D8ECBD",
        "light": "#BEEB96",
        "base": "#A3D576",
        "mid": "#71B436",
        "dark": "#386411",
    },
    "gold": {
        "open": TOKENS["panel"],
        "xlight": "#FFF4C2",
        "light": "#FFEA8F",
        "base": "#FFE15B",
        "mid": "#B8A037",
        "dark": "#736422",
    },
}


def use_chart_theme() -> None:
    sns.set_theme(
        style="whitegrid",
        rc={
            "figure.facecolor": TOKENS["surface"],
            "figure.edgecolor": "none",
            "savefig.facecolor": "none",
            "savefig.edgecolor": "none",
            "axes.facecolor": TOKENS["panel"],
            "axes.edgecolor": TOKENS["axis"],
            "axes.labelcolor": TOKENS["ink"],
            "axes.grid": True,
            "axes.spines.top": False,
            "axes.spines.right": False,
            "grid.color": TOKENS["grid"],
            "grid.linewidth": 0.8,
            "font.family": "sans-serif",
            "font.sans-serif": FONT_FAMILY,
            "font.monospace": MONO_FONT_FAMILY,
            "patch.linewidth": 1.0,
        },
    )


def add_chart_header(fig, ax, title: str, subtitle: str) -> None:
    if not title or not subtitle:
        raise ValueError("Every shipped chart needs a non-empty title and subtitle.")
    ax.set_title("")
    fig.subplots_adjust(top=0.82, left=0.24, right=0.96, bottom=0.16)
    left = ax.get_position().x0
    fig.text(left, 0.985, title, ha="left", va="top", fontsize=13, fontweight="bold", color=TOKENS["ink"])
    fig.text(left, 0.93, subtitle, ha="left", va="top", fontsize=9, color=TOKENS["muted"])
    sns.despine(ax=ax)


def write_bar_chart(df: pd.DataFrame, path_base: Path, *, title: str, subtitle: str, family_name: str) -> None:
    family = COLOR_FAMILIES[family_name]
    plot_df = df.sort_values("value", ascending=True)
    fig, ax = plt.subplots(figsize=(9.5, 5.4))
    palette = {row["label"]: family["base"] for _, row in plot_df.iterrows()}
    sns.barplot(
        data=plot_df,
        x="value",
        y="label",
        hue="label",
        palette=palette,
        legend=False,
        dodge=False,
        ax=ax,
        edgecolor=family["dark"],
        linewidth=1.0,
    )
    ax.set_xlim(0, max(100, float(plot_df["value"].max()) + 5))
    ax.xaxis.set_major_formatter(mticker.PercentFormatter(xmax=100))
    ax.set_xlabel("Percent reduced from production")
    ax.set_ylabel("")
    ax.tick_params(axis="y", labelsize=9, colors=TOKENS["ink"])
    ax.tick_params(axis="x", labelsize=8, colors=TOKENS["muted"])
    for patch, value in zip(ax.patches, plot_df["value"]):
        ax.text(
            value + 1.3,
            patch.get_y() + patch.get_height() / 2,
            f"{value:.1f}%",
            va="center",
            ha="left",
            fontsize=8,
            color=TOKENS["ink"],
        )
    add_chart_header(fig, ax, title, subtitle)
    fig.savefig(path_base.with_suffix(".png"), dpi=160)
    fig.savefig(path_base.with_suffix(".svg"))
    plt.close(fig)


def copy_asset(src_name: str, dest_name: str | None = None) -> str:
    dest_name = dest_name or src_name
    src = AUDIT_DIR / src_name
    dest = ASSET_DIR / dest_name
    if not src.exists():
        raise FileNotFoundError(src)
    shutil.copy2(src, dest)
    return f"assets/{dest_name}"


def metric_card(label: str, value: str, note: str, tone: str) -> str:
    return f"""
      <article class="metric metric-{tone}">
        <div class="metric-value">{html.escape(value)}</div>
        <div class="metric-label">{html.escape(label)}</div>
        <p>{html.escape(note)}</p>
      </article>
    """


def screenshot_figure(src: str, caption: str) -> str:
    return f"""
      <figure class="shot">
        <a href="{html.escape(src)}"><img src="{html.escape(src)}" alt="{html.escape(caption)}"></a>
        <figcaption>{html.escape(caption)}</figcaption>
      </figure>
    """


def comparison_block(title: str, prod_src: str, local_src: str, note: str) -> str:
    return f"""
      <article class="comparison">
        <h3>{html.escape(title)}</h3>
        <p>{html.escape(note)}</p>
        <div class="screen-pair">
          <figure>
            <a href="{html.escape(prod_src)}"><img src="{html.escape(prod_src)}" alt="{html.escape(title)} production screenshot"></a>
            <figcaption>Production Elementor</figcaption>
          </figure>
          <figure>
            <a href="{html.escape(local_src)}"><img src="{html.escape(local_src)}" alt="{html.escape(title)} local custom theme screenshot"></a>
            <figcaption>Local Custom Theme</figcaption>
          </figure>
        </div>
      </article>
    """


def status_row(area: str, status: str, status_class: str, feedback: str, next_step: str) -> str:
    return f"""
      <tr>
        <th scope="row">{html.escape(area)}</th>
        <td><span class="status {html.escape(status_class)}">{html.escape(status)}</span></td>
        <td>{html.escape(feedback)}</td>
        <td>{html.escape(next_step)}</td>
      </tr>
    """


def page_summary_rows(rows: list[dict]) -> str:
    chosen = [
        "home",
        "shop",
        "product-category",
        "single-product",
        "cart",
        "checkout",
        "promotion",
        "ages-page",
        "brands-page",
        "about-page",
    ]
    by_label = {row["label"]: row for row in rows}
    out = []
    for label in chosen:
        row = by_label[label]
        out.append(
            f"""
            <tr>
              <th scope="row">{html.escape(label)}</th>
              <td>{row['prodReq']} -> {row['localReq']}</td>
              <td>{row['reqReductionPct']:.1f}%</td>
              <td>{row['prodLoad']} -> {row['localLoad']} ms</td>
              <td>{row['loadChangePct']:.1f}% ลดลง</td>
            </tr>
            """
        )
    return "\n".join(out)


def build_html(perf: dict, screenshot_paths: dict[str, str], rows: list[dict]) -> str:
    aggregate = perf["aggregate"]
    metric_cards = "\n".join(
        [
            metric_card("จำนวน request ลดลง", f"{aggregate['requestReductionMedianPct']:.1f}%", "ค่ากลางของ 21 หน้า: request ลดลงจาก production Elementor", "blue"),
            metric_card("น้ำหนักหน้าเว็บลดลง", f"{aggregate['kbReductionMedianPct']:.1f}%", "ค่ากลางของ decoded payload ที่ browser ต้องโหลด ลดลงจาก production", "olive"),
            metric_card("เวลา load ลดลง", f"{aggregate['loadChangeMedianPct']:.1f}%", "เวลา load event ลดลงจาก production เป็นตัวชี้วัดทิศทาง", "orange"),
            metric_card("หน้า audit แล้ว", "21", "รวม desktop และ mobile สำหรับหน้าหลักของ WooCommerce และ custom pages", "gold"),
        ]
    )

    status_rows = "\n".join(
        [
            status_row("Header, menu, language, search, footer", "พร้อมเป็นฐาน", "ready", "โครงสร้างหลักใกล้เคียงเว็บจริงและเร็วขึ้นมาก", "ใช้เป็นระบบกลางของทุก marketing page"),
            status_row("Shop, category, brand, age archive", "พร้อมเป็นฐาน", "ready", "มี hero, filter, product grid และ product card ที่ทีมใช้งานต่อได้", "ตรวจ product count และ refinement ของ card ในรอบถัดไป"),
            status_row("Single product", "ใกล้เคียงเว็บจริง", "good", "หน้าสินค้าใช้งานได้และเบากว่า Elementor ชัดเจน", "ปรับ related products และ trust content ให้เหมือน production มากขึ้น"),
            status_row("Cart, account, wishlist", "ใกล้เคียงเว็บจริง", "good", "flow หลักทำงานและ style รวมเข้ากับ theme แล้ว", "ทดสอบ coupon, shipping, login และ edge cases ก่อนขึ้นจริง"),
            status_row("Checkout", "ต้องตัดสินใจ", "decision", "เร็วขึ้นมาก แต่ layout ยัง utilitarian กว่า production", "ต้องเลือกว่าจะ match Elementor สองคอลัมน์ หรือใช้ checkout ที่เรียบและเร็วกว่า"),
            status_row("Home, promotion, ages, brands", "ต้องปรับเชิงภาพ", "decision", "first viewport และฐาน theme ดี แต่บาง section ยังไม่ rich เท่า Elementor", "ใช้ custom components ทำ hero/cards/campaign modules ที่ AI reuse ได้"),
            status_row("Blog, search, contact, policy pages", "ใช้ต่อได้", "good", "functional และเบากว่าเดิม โดยหน้ากฎหมาย/เนื้อหายังไม่จำเป็นต้องย้ายทั้งหมดทันที", "ค่อยย้ายหน้า content ที่มีผลต่อ SEO และ campaign ก่อน"),
        ]
    )

    compare_blocks = "\n".join(
        [
            comparison_block(
                "Home / หน้าแรก",
                screenshot_paths["desktop-prod-home.png"],
                screenshot_paths["desktop-local-home.png"],
                "Header และ first viewport เข้าใกล้ production แล้ว ส่วน lower sections ควรทำเป็น reusable marketing modules ในเฟสต่อไป",
            ),
            comparison_block(
                "Shop archive / หน้ารวมสินค้า",
                screenshot_paths["desktop-prod-shop.png"],
                screenshot_paths["desktop-local-shop.png"],
                "Custom theme มี filter และ grid พร้อมเป็นฐานสำหรับ product discovery และ campaign landing pages",
            ),
            comparison_block(
                "Single product / หน้าสินค้า",
                screenshot_paths["desktop-prod-single-product.png"],
                screenshot_paths["desktop-local-single-product.png"],
                "หน้าสินค้าเบาขึ้นมากและพร้อมต่อยอดเป็น AI product page assistant",
            ),
            comparison_block(
                "Cart / ตะกร้า",
                screenshot_paths["desktop-prod-cart.png"],
                screenshot_paths["desktop-local-cart.png"],
                "Cart ใกล้ production และ CTA สีหลักเข้ากับ brand แล้ว",
            ),
            comparison_block(
                "Checkout / ชำระเงิน",
                screenshot_paths["desktop-prod-checkout.png"],
                screenshot_paths["desktop-local-checkout.png"],
                "นี่คือหน้าที่ควรตัดสินใจเชิงธุรกิจ เพราะ speed ดีขึ้น แต่ visual match ยังต่างที่สุด",
            ),
        ]
    )

    overview_gallery = "\n".join(
        [
            screenshot_figure(screenshot_paths["desktop-commerce.png"], "Desktop commerce overview: product archive, product detail, cart, checkout"),
            screenshot_figure(screenshot_paths["mobile-commerce.png"], "Mobile commerce overview: key WooCommerce pages"),
            screenshot_figure(screenshot_paths["desktop-content.png"], "Desktop content overview: blog, search, contact, custom pages"),
            screenshot_figure(screenshot_paths["mobile-content.png"], "Mobile content overview: content and marketing pages"),
        ]
    )

    return f"""<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Learning For Kidz AI Marketing Foundation Brief</title>
  <style>
    :root {{
      --ink: #18202f;
      --muted: #5d6678;
      --line: #dfe4ec;
      --panel: #ffffff;
      --surface: #f7f9fb;
      --blue: #2f6f9f;
      --cyan: #42bedd;
      --orange: #cc6f47;
      --olive: #6d9444;
      --gold: #a8871f;
      --ready: #e6f6ed;
      --ready-ink: #1e6a3d;
      --good: #e9f3ff;
      --good-ink: #245e8e;
      --decision: #fff2dc;
      --decision-ink: #87521c;
    }}
    * {{ box-sizing: border-box; }}
    html {{ scroll-behavior: smooth; }}
    body {{
      margin: 0;
      color: var(--ink);
      background: var(--surface);
      font-family: "Noto Sans Thai", "Tahoma", "Segoe UI", Arial, sans-serif;
      line-height: 1.65;
    }}
    a {{ color: var(--blue); }}
    img {{ max-width: 100%; display: block; }}
    .page {{
      width: min(1120px, calc(100% - 32px));
      margin: 0 auto;
      padding: 28px 0 64px;
    }}
    header.report-header {{
      padding: 28px 0 18px;
      border-bottom: 1px solid var(--line);
    }}
    .eyebrow {{
      margin: 0 0 10px;
      color: var(--orange);
      font-size: 0.92rem;
      font-weight: 700;
    }}
    h1 {{
      margin: 0;
      max-width: 980px;
      font-size: 3.7rem;
      line-height: 1.08;
      letter-spacing: 0;
    }}
    .lead {{
      margin: 18px 0 0;
      max-width: 860px;
      color: var(--muted);
      font-size: 1.12rem;
    }}
    .actions {{
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 22px;
    }}
    .button {{
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
    }}
    .button.primary {{
      border-color: var(--cyan);
      background: var(--cyan);
      color: #082330;
    }}
    section {{
      margin-top: 34px;
      padding-top: 6px;
    }}
    h2 {{
      margin: 0 0 12px;
      font-size: 2rem;
      line-height: 1.25;
      letter-spacing: 0;
    }}
    h3 {{
      margin: 0 0 8px;
      font-size: 1.08rem;
      line-height: 1.35;
      letter-spacing: 0;
    }}
    p {{ margin: 0 0 12px; }}
    .summary {{
      background: var(--panel);
      border: 1px solid var(--line);
      border-radius: 8px;
      padding: 18px;
    }}
    .summary ul {{
      margin: 0;
      padding-left: 22px;
    }}
    .summary li + li {{ margin-top: 10px; }}
    .metrics {{
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 12px;
      margin-top: 16px;
    }}
    .metric {{
      border: 1px solid var(--line);
      border-radius: 8px;
      background: var(--panel);
      padding: 16px;
      min-height: 150px;
    }}
    .metric-value {{
      font-size: 3.2rem;
      line-height: 1;
      font-weight: 800;
    }}
    .metric-label {{
      margin-top: 8px;
      font-weight: 800;
    }}
    .metric p {{
      color: var(--muted);
      font-size: 0.94rem;
    }}
    .metric-blue .metric-value {{ color: var(--blue); }}
    .metric-olive .metric-value {{ color: var(--olive); }}
    .metric-orange .metric-value {{ color: var(--orange); }}
    .metric-gold .metric-value {{ color: var(--gold); }}
    .chart-grid {{
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 16px;
      margin-top: 16px;
    }}
    .visual {{
      background: var(--panel);
      border: 1px solid var(--line);
      border-radius: 8px;
      padding: 12px;
    }}
    .visual p {{
      color: var(--muted);
      font-size: 0.94rem;
    }}
    .table-wrap {{
      overflow-x: auto;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: var(--panel);
    }}
    table {{
      width: 100%;
      border-collapse: collapse;
      min-width: 760px;
    }}
    th, td {{
      padding: 12px 14px;
      text-align: left;
      border-bottom: 1px solid var(--line);
      vertical-align: top;
    }}
    th {{
      font-weight: 800;
      color: var(--ink);
    }}
    tr:last-child th, tr:last-child td {{ border-bottom: 0; }}
    .status {{
      display: inline-flex;
      align-items: center;
      min-height: 30px;
      white-space: nowrap;
      border-radius: 999px;
      padding: 4px 10px;
      font-weight: 800;
      font-size: 0.86rem;
    }}
    .status.ready {{ background: var(--ready); color: var(--ready-ink); }}
    .status.good {{ background: var(--good); color: var(--good-ink); }}
    .status.decision {{ background: var(--decision); color: var(--decision-ink); }}
    .gallery {{
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 16px;
    }}
    .shot {{
      margin: 0;
      background: var(--panel);
      border: 1px solid var(--line);
      border-radius: 8px;
      overflow: hidden;
    }}
    .shot img {{
      width: 100%;
      aspect-ratio: 16 / 10;
      object-fit: cover;
      object-position: top;
      border-bottom: 1px solid var(--line);
    }}
    figcaption {{
      padding: 10px 12px;
      color: var(--muted);
      font-size: 0.9rem;
    }}
    .comparison {{
      margin-top: 16px;
      background: var(--panel);
      border: 1px solid var(--line);
      border-radius: 8px;
      padding: 16px;
    }}
    .comparison > p {{ color: var(--muted); }}
    .screen-pair {{
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 12px;
      margin-top: 10px;
    }}
    .screen-pair figure {{
      margin: 0;
      border: 1px solid var(--line);
      border-radius: 6px;
      overflow: hidden;
      background: #fff;
    }}
    .screen-pair img {{
      width: 100%;
      height: 360px;
      object-fit: cover;
      object-position: top;
      border-bottom: 1px solid var(--line);
    }}
    .process {{
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 10px;
      margin-top: 16px;
    }}
    .step {{
      background: var(--panel);
      border: 1px solid var(--line);
      border-top: 4px solid var(--cyan);
      border-radius: 8px;
      padding: 13px;
      min-height: 160px;
    }}
    .step strong {{
      display: block;
      margin-bottom: 6px;
    }}
    .step p {{
      color: var(--muted);
      font-size: 0.92rem;
    }}
    .apps {{
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 12px;
      margin-top: 16px;
    }}
    .app {{
      background: var(--panel);
      border: 1px solid var(--line);
      border-radius: 8px;
      padding: 14px;
    }}
    .app p {{
      color: var(--muted);
      font-size: 0.94rem;
    }}
    .roadmap {{
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 14px;
      margin-top: 16px;
    }}
    .phase {{
      background: var(--panel);
      border: 1px solid var(--line);
      border-radius: 8px;
      padding: 16px;
    }}
    .phase ul {{
      margin: 8px 0 0;
      padding-left: 20px;
    }}
    .message {{
      background: #1f2430;
      color: #f7f9fb;
      border-radius: 8px;
      padding: 18px;
      white-space: pre-wrap;
      font-size: 0.98rem;
    }}
    .message strong {{ color: #ffffff; }}
    .note {{
      color: var(--muted);
      font-size: 0.95rem;
    }}
    footer {{
      margin-top: 36px;
      padding-top: 20px;
      border-top: 1px solid var(--line);
      color: var(--muted);
      font-size: 0.92rem;
    }}
    @media (max-width: 920px) {{
      h1 {{ font-size: 2.6rem; }}
      h2 {{ font-size: 1.7rem; }}
      .metric-value {{ font-size: 2.7rem; }}
      .metrics, .chart-grid, .gallery, .screen-pair, .process, .apps, .roadmap {{
        grid-template-columns: 1fr;
      }}
      .screen-pair img {{ height: 300px; }}
      table {{ min-width: 680px; }}
    }}
    @media (max-width: 520px) {{
      h1 {{ font-size: 2.35rem; }}
      h2 {{ font-size: 1.55rem; }}
      .lead {{ font-size: 1rem; }}
      .metric-value {{ font-size: 2.45rem; }}
      .button {{ width: 100%; justify-content: center; }}
    }}
    @media print {{
      body {{ background: #fff; }}
      .button, .actions {{ display: none; }}
      .page {{ width: auto; margin: 0; padding: 0; }}
      section {{ break-inside: avoid; }}
      .screen-pair img {{ height: 260px; }}
    }}
  </style>
</head>
<body>
  <main class="page">
    <header class="report-header">
      <p class="eyebrow">Team Brief - June 12, 2026</p>
      <h1>Learning For Kidz: เปลี่ยนเว็บไซต์ให้เป็นฐานของ AI Marketing Engine</h1>
      <p class="lead">Custom theme ไม่ใช่แค่เปลี่ยนหน้าตาเว็บ แต่เป็นก้าวแรกในการทำให้ทีมสร้าง marketing pages, promotion tools, business apps และ automation ได้เร็วขึ้น พร้อมหลักฐานก่อนและหลังทุกครั้ง</p>
      <div class="actions">
        <a class="button primary" href="{FULL_AUDIT_PATH}">เปิด Full Section Audit</a>
        <a class="button" href="{LOCAL_SITE_URL}">เปิด Local Test Site</a>
        <a class="button" href="team-brief-th.pdf">Download PDF</a>
        <a class="button" href="team-message-th.md">เปิดข้อความสำหรับส่งทีม</a>
      </div>
    </header>

    <section>
      <h2>Executive Summary / สรุปสำหรับทีม</h2>
      <div class="summary">
        <ul>
          <li><strong>ใช่, หน้าเว็บโหลดดีขึ้นอย่างมีนัยสำคัญ.</strong> จาก audit 21 หน้า จำนวน request ลดลงค่ากลาง {aggregate['requestReductionMedianPct']:.1f}% และเวลา load event ลดลงค่ากลาง {aggregate['loadChangeMedianPct']:.1f}% เมื่อเทียบ custom theme local กับ production Elementor.</li>
          <li><strong>Custom theme คือ foundation สำหรับ AI-ready marketing.</strong> เมื่อส่วนสำคัญกลายเป็น template และ component ที่ชัดเจน AI จะสร้างหน้า campaign, product page, report และ automation ได้เร็วกว่าแก้ Elementor ทีละหน้า.</li>
          <li><strong>Commerce core พร้อมเป็นฐานแล้ว แต่ checkout ยังต้องตัดสินใจ.</strong> Shop/archive/product/cart ใกล้เคียง production และเร็วขึ้นมาก ส่วน checkout ต้องเลือกระหว่าง match Elementor ให้มากขึ้น หรือยอมรับ layout ที่เรียบกว่าเพื่อ speed และ maintainability.</li>
          <li><strong>วิธีคุยกับทีมควรเป็น business roadmap.</strong> สื่อสารว่าเรากำลังสร้างระบบให้ทีมขอ solution/app ได้เร็วขึ้น ไม่ใช่แค่ทำเว็บใหม่.</li>
        </ul>
      </div>
      <div class="metrics">{metric_cards}</div>
    </section>

    <section>
      <h2>หลักฐานเรื่องความเร็ว</h2>
      <p>คำตอบสั้นคือดีขึ้นชัดเจน. Percent ใน report นี้หมายถึง “ลดลงจาก production” โดยคำนวณแบบ (production - local) / production. ตัวอย่าง: request ลดลง 79.8% แปลว่า local เหลือ request ประมาณ 20.2% ของ production.</p>
      <div class="chart-grid">
        <article class="visual">
          <img src="assets/performance_reduction.png" alt="Median performance reduction chart">
          <p>ค่ากลางของ 21 route: request, payload, DOMContentLoaded และ load event ลดลงทั้งหมดเมื่อเทียบกับ production.</p>
        </article>
        <article class="visual">
          <img src="assets/page_group_speed.png" alt="Page group request reduction chart">
          <p>Commerce และ marketing pages ได้ประโยชน์มาก เพราะลด Elementor/WooCommerce wrapper และ asset ที่ไม่จำเป็น.</p>
        </article>
      </div>
    </section>

    <section>
      <h2>สถานะตามส่วนของเว็บไซต์</h2>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ส่วนของเว็บ</th>
              <th>สถานะ</th>
              <th>Feedback สำหรับทีม</th>
              <th>งานต่อไป</th>
            </tr>
          </thead>
          <tbody>{status_rows}</tbody>
        </table>
      </div>
    </section>

    <section>
      <h2>ตัวเลขหน้าเว็บหลัก</h2>
      <p class="note">ตารางนี้เอาไว้ใช้ตอบคำถามว่า “เร็วขึ้นแค่ไหน” แบบไม่ต้องเปิด technical log.</p>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Page</th>
              <th>Requests: Prod -> Local</th>
              <th>Request ลดลง</th>
              <th>Load: Prod -> Local</th>
              <th>Load time ลดลง</th>
            </tr>
          </thead>
          <tbody>{page_summary_rows(rows)}</tbody>
        </table>
      </div>
    </section>

    <section>
      <h2>ภาพรวม Screenshot Evidence</h2>
      <p>ภาพเหล่านี้คือ contact sheet จาก audit ที่จับทั้ง production Elementor และ local custom theme. คลิกที่รูปเพื่อดูขนาดเต็ม.</p>
      <div class="gallery">{overview_gallery}</div>
    </section>

    <section>
      <h2>ภาพเทียบหน้าสำคัญ</h2>
      <p>ส่วนนี้ช่วยให้ทีมเห็นว่าอะไรเข้าใกล้ production แล้ว และอะไรต้องตัดสินใจต่อ.</p>
      {compare_blocks}
    </section>

    <section>
      <h2>Process: จาก Website Theme ไปสู่ AI Marketing App Factory</h2>
      <p>วิธีคิดคือทำให้ทุกงาน marketing กลายเป็น workflow ที่ AI ช่วยสร้าง ทดสอบ และส่งหลักฐานให้ทีมตัดสินใจได้.</p>
      <div class="process">
        <article class="step"><strong>1. Business Need</strong><p>ทีมบอกโจทย์ เช่น โปรโมชัน, launch, landing page, report หรือ automation.</p></article>
        <article class="step"><strong>2. AI Draft</strong><p>AI สร้างหน้า/app รุ่นแรกจาก theme components และ business rules.</p></article>
        <article class="step"><strong>3. Auto Evidence</strong><p>ระบบจับ screenshot, page speed, section checklist และ before/after.</p></article>
        <article class="step"><strong>4. Team Review</strong><p>ทีม non-technical ดูรายงานภาษาไทยและตัดสินใจด้วยภาพกับตัวเลข.</p></article>
        <article class="step"><strong>5. Deploy</strong><p>สิ่งที่ผ่าน review ถูกนำขึ้น production แบบควบคุมความเสี่ยง.</p></article>
        <article class="step"><strong>6. Reuse</strong><p>งานที่ดีถูกเก็บเป็น template เพื่อสร้างรอบต่อไปได้เร็วขึ้น.</p></article>
      </div>
    </section>

    <section>
      <h2>Marketing Apps ที่ควรสร้างต่อจาก Foundation นี้</h2>
      <div class="apps">
        <article class="app"><h3>Campaign Landing Page Builder</h3><p>สร้างหน้าโปรโมชันตามเทศกาล พร้อม product grid, hero, coupon และ report.</p></article>
        <article class="app"><h3>Product Page Assistant</h3><p>ช่วยปรับ headline, benefits, FAQ, SEO และ trust blocks สำหรับสินค้า.</p></article>
        <article class="app"><h3>Bundle & Promotion Tool</h3><p>ทำชุดสินค้า age/brand/category พร้อม logic ส่วนลดและหน้า landing.</p></article>
        <article class="app"><h3>Thai/English Content Assistant</h3><p>ช่วยแปล, สรุป, ทำ SEO article, product copy และ ad copy ให้สม่ำเสมอ.</p></article>
        <article class="app"><h3>Screenshot Audit Generator</h3><p>ทุกครั้งที่แก้เว็บ จะได้ report before/after ให้ทีมดูอัตโนมัติ.</p></article>
        <article class="app"><h3>Email/SMS Campaign Assistant</h3><p>ต่อยอดจาก promotion page ไปเป็นข้อความ follow-up และ abandoned cart.</p></article>
        <article class="app"><h3>Review & Testimonial Collector</h3><p>เก็บรีวิว แยกตามสินค้า/อายุเด็ก แล้วนำกลับมาใช้บน landing/product pages.</p></article>
        <article class="app"><h3>Business KPI Brief</h3><p>สรุปยอดขาย, page speed, conversion, campaign impact เป็นภาษาไทยสำหรับทีม.</p></article>
      </div>
    </section>

    <section>
      <h2>Roadmap 30/60/90 วัน</h2>
      <div class="roadmap">
        <article class="phase">
          <h3>30 วัน: Stabilize Foundation</h3>
          <ul>
            <li>Finalize checkout decision</li>
            <li>QA product/archive/cart flows</li>
            <li>ทำ report template ภาษาไทยให้ใช้ซ้ำ</li>
            <li>กำหนด component library สำหรับ campaign pages</li>
          </ul>
        </article>
        <article class="phase">
          <h3>60 วัน: First AI Marketing Tools</h3>
          <ul>
            <li>Campaign landing page generator</li>
            <li>Product page assistant</li>
            <li>Promotion/bundle builder</li>
            <li>Auto screenshot and speed audit</li>
          </ul>
        </article>
        <article class="phase">
          <h3>90 วัน: Automation Layer</h3>
          <ul>
            <li>Email/SMS workflow จาก campaign</li>
            <li>SEO and content workflow</li>
            <li>Business KPI weekly brief</li>
            <li>Reusable playbook สำหรับทุกโปรเจกต์ใหม่</li>
          </ul>
        </article>
      </div>
    </section>

    <section>
      <h2>ข้อความสำหรับส่งทีม</h2>
      <div class="message"><strong>สรุป:</strong> เราไม่ได้ทำแค่เปลี่ยน theme เว็บ แต่กำลังสร้าง foundation ให้บริษัททำ marketing, business apps และ automation ด้วย AI ได้เร็วขึ้น

ตอนนี้เรา audit custom theme เทียบกับเว็บ production แล้ว 21 หน้า ผลคือจำนวน request ลดลงค่ากลาง {aggregate['requestReductionMedianPct']:.1f}% และเวลา load event ลดลงค่ากลาง {aggregate['loadChangeMedianPct']:.1f}%

สิ่งที่พร้อมเป็นฐานแล้ว: header/footer, shop/archive, product page, cart, wishlist, account และหลายหน้า content

สิ่งที่ต้องตัดสินใจต่อ: checkout จะให้เหมือน Elementor production มากขึ้น หรือใช้ layout ที่เรียบกว่าและเร็วกว่า

เป้าหมายถัดไปคือทำให้ทีมสามารถขอหน้าโปรโมชัน, product campaign, report หรือ automation แล้วให้ AI สร้างเวอร์ชันแรกพร้อม screenshot และตัวเลขให้ review ได้ทันที</div>
    </section>

    <section>
      <h2>ข้อควรเข้าใจ</h2>
      <p class="note">ตัวเลข timing เป็น directional เพราะ production กับ local อยู่คนละระบบ hosting/CDN แต่จำนวน request และ payload reduction เป็นหลักฐานที่แข็งแรงว่า custom theme เบากว่า. ก่อนขึ้น production ต้องทดสอบ payment, shipping, coupon, login, mobile checkout และ tracking/analytics อีกครั้ง.</p>
    </section>

    <footer>
      Source: Learning For Kidz section audit created 2026-06-11, Thai team brief generated 2026-06-12. Full audit: <a href="{FULL_AUDIT_PATH}">{FULL_AUDIT_PATH}</a>
    </footer>
  </main>
</body>
</html>
"""


def build_team_message(perf: dict) -> str:
    aggregate = perf["aggregate"]
    return f"""# ข้อความสำหรับส่งทีม Learning For Kidz

สรุป: เราไม่ได้ทำแค่เปลี่ยน theme เว็บ แต่กำลังสร้าง foundation ให้บริษัททำ marketing, business apps และ automation ด้วย AI ได้เร็วขึ้น

ตอนนี้เรา audit custom theme เทียบกับเว็บ production แล้ว 21 หน้า ผลคือ:

- จำนวน request ลดลงค่ากลาง {aggregate['requestReductionMedianPct']:.1f}%
- น้ำหนักหน้าเว็บลดลงค่ากลาง {aggregate['kbReductionMedianPct']:.1f}%
- เวลา load event ลดลงค่ากลาง {aggregate['loadChangeMedianPct']:.1f}%

สิ่งที่พร้อมเป็นฐานแล้ว:

- header/footer
- shop/archive/category/brand/age pages
- product page
- cart, wishlist, account
- หลายหน้า content และ custom pages

สิ่งที่ต้องตัดสินใจต่อ:

- checkout จะให้เหมือน Elementor production มากขึ้น หรือใช้ layout ที่เรียบกว่าและเร็วกว่า
- home/lower marketing sections จะทำให้เหมือนเดิมแค่ไหน หรือเปลี่ยนเป็น reusable campaign modules

เป้าหมายถัดไป:

1. ทำ custom theme ให้ stable เป็น foundation
2. สร้าง AI marketing tools เช่น campaign landing page builder, product page assistant, promotion builder
3. ทุกครั้งที่ AI สร้างหรือแก้หน้าเว็บ ต้องมี screenshot, speed result และ checklist ให้ทีมดู
4. ทำให้ทีมขอ solution/app ได้เร็วขึ้น โดยไม่ต้องรอ development cycle ยาว

ลิงก์สำหรับดู report:

- Team brief: {LOCAL_SITE_URL}{LOCAL_REPORT_PATH}
- Full section audit: {LOCAL_SITE_URL}{FULL_AUDIT_PATH}
"""


def build_readme() -> str:
    return f"""# Learning For Kidz AI Marketing Foundation Team Brief

Generated: 2026-06-12

This folder contains a Thai-first communication report for non-technical stakeholders.

- `index.html` and `report.html`: browser report
- `team-brief-th.pdf`: printable/shareable PDF export of the same report
- `assets/`: chart PNG/SVG files and selected screenshot evidence
- `team-message-th.md`: paste-ready Thai team message
- `source-notes.json`: source metadata and chart map

Local URL after publishing into WordPress:

{LOCAL_SITE_URL}{LOCAL_REPORT_PATH}
"""


def main() -> None:
    if not AUDIT_DIR.exists():
        raise FileNotFoundError(AUDIT_DIR)

    OUT_DIR.mkdir(parents=True, exist_ok=True)
    ASSET_DIR.mkdir(parents=True, exist_ok=True)

    perf = json.loads((AUDIT_DIR / "performance.json").read_text())
    rows = perf["summaryRows"]
    df = pd.DataFrame(rows)

    use_chart_theme()

    metric_df = pd.DataFrame(
        [
            {"label": "Request count", "value": perf["aggregate"]["requestReductionMedianPct"]},
            {"label": "Decoded payload", "value": perf["aggregate"]["kbReductionMedianPct"]},
            {"label": "DOMContentLoaded", "value": perf["aggregate"]["dclChangeMedianPct"]},
            {"label": "Load event time", "value": perf["aggregate"]["loadChangeMedianPct"]},
        ]
    )
    write_bar_chart(
        metric_df,
        ASSET_DIR / "performance_reduction",
        title="Median reduction from production",
        subtitle="Percent reduced = (production value - local value) / production value, across 21 audited routes",
        family_name="blue",
    )

    group_map = {
        "Commerce core": ["shop", "product-category", "product-brand", "age-taxonomy", "single-product", "cart", "checkout", "my-account", "wishlist"],
        "Marketing pages": ["home", "contact", "promotion", "ages-page", "brands-page"],
        "Content and SEO": ["article-archive", "single-post", "search"],
        "Policy and support": ["about-page", "refund-page", "how-to-orders", "privacy-page"],
    }
    group_rows = []
    for group, labels in group_map.items():
        part = df[df["label"].isin(labels)]
        group_rows.append({"label": group, "value": float(part["reqReductionPct"].mean())})
    write_bar_chart(
        pd.DataFrame(group_rows),
        ASSET_DIR / "page_group_speed",
        title="Average request reduction by page group",
        subtitle="Lower request count is the strongest evidence that the custom theme is lighter",
        family_name="orange",
    )

    screenshot_names = [
        "desktop-commerce.png",
        "mobile-commerce.png",
        "desktop-content.png",
        "mobile-content.png",
        "desktop-prod-home.png",
        "desktop-local-home.png",
        "desktop-prod-shop.png",
        "desktop-local-shop.png",
        "desktop-prod-single-product.png",
        "desktop-local-single-product.png",
        "desktop-prod-cart.png",
        "desktop-local-cart.png",
        "desktop-prod-checkout.png",
        "desktop-local-checkout.png",
    ]
    screenshot_paths = {name: copy_asset(name) for name in screenshot_names}

    report_html = build_html(perf, screenshot_paths, rows)
    (OUT_DIR / "report.html").write_text(report_html, encoding="utf-8")
    (OUT_DIR / "index.html").write_text(report_html, encoding="utf-8")
    (OUT_DIR / "team-message-th.md").write_text(build_team_message(perf), encoding="utf-8")
    (OUT_DIR / "README.md").write_text(build_readme(), encoding="utf-8")

    source_notes = {
        "generatedAt": "2026-06-12",
        "deliveryMode": "html",
        "audience": "product stakeholders",
        "sourceAuditDir": str(AUDIT_DIR),
        "inputs": [
            "performance.json",
            "report.json",
            "selected screenshot PNG files",
        ],
        "chartMap": [
            {
                "section": "หลักฐานเรื่องความเร็ว",
                "question": "Did page load improve significantly?",
                "chartType": "ranked horizontal bar",
                "fields": ["metric", "median reduction percent"],
                "artifact": "assets/performance_reduction.png",
            },
            {
                "section": "หลักฐานเรื่องความเร็ว",
                "question": "Which page groups show the strongest request reduction?",
                "chartType": "ranked horizontal bar",
                "fields": ["page group", "average request reduction percent"],
                "artifact": "assets/page_group_speed.png",
            },
        ],
        "caveats": [
            "Raw timings are directional because production and local use different host/CDN paths.",
            "Request count and decoded payload reductions are stronger evidence of page-weight improvement.",
            "Checkout needs business decision before final parity sign-off.",
        ],
    }
    (OUT_DIR / "source-notes.json").write_text(json.dumps(source_notes, indent=2), encoding="utf-8")

    print(OUT_DIR)


if __name__ == "__main__":
    main()
