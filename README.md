# Kodezen Ecosystem Bar

A lightweight WordPress bar that links to the Kodezen ecosystem products. Available as a **shortcode** and a **Gutenberg block**, with visual style controls. Built inline-first: **zero enqueued frontend assets, no external requests, and no JavaScript on the frontend** (the dropdown is a native `<details>` element).

## Features

- **Real brand logos** inlined as SVG (no HTTP requests, no logo files loaded at runtime).
- **First-view + More dropdown** — feature the important products inline, fold the rest into a polished "More" popover.
- **Gutenberg block** with a live editor preview and visual controls (no build step — uses the `wp.*` globals).
- **Shortcode** with the same options for classic/page-builder use.
- **Active state** — the product matching the current site is auto-highlighted (e.g. Kodezen on `kodezen.com`), by host + longest-path match.
- **Responsive visibility** — show on all devices, hide on mobile, or desktop only.
- **Inline styling** — appearance is applied per element, so it survives contexts that strip `<style>` blocks.

## Usage

### Shortcode

```
[kodezen_ecosystem_bar]
```

| Attribute | Default | Description |
|---|---|---|
| `inline` | `yes` | `yes` renders in place; `no` pins a fixed top bar. |
| `bg` | `#ffffff` | Bar background color. |
| `width` | `full` | `full`, `content` (theme content size), `wide` (theme wide size), or a length like `1140px`. |
| `align` | `justify` | `justify` (spread evenly), `left`, `center`, `right`. |
| `height` | `56` | Bar height in px. |
| `new_tab` | `no` | `yes` opens links in a new tab. |
| `hover_bg` | `#f0f1f3` | Hover background for logo links. |
| `active_highlight` | `yes` | Highlight the product matching the current site. |
| `active_bg` | `#eef2ff` | Active item background. |
| `active_indicator` | `#006bff` | Active item underline color. |
| `border_color` | `#e5e7eb` | Bar bottom border color. |
| `border_width` | `1` | Bar bottom border width in px (`0` = none). |
| `visibility` | `no-mobile` | `all`, `no-mobile` (hide < 782px), `desktop-only` (hide < 1024px). |
| `more_label` | `More` | More button label. |
| `more_icon` | `yes` | Show the apps icon on the More button. |
| `more_text` | `#141a24` | More button text color. |
| `more_bg` | `#ffffff` | More button background color. |
| `more_border` | `#d9dce2` | More button border color. |
| `more_border_width` | `1` | More button border width in px (`0` = none). |
| `more_icon_color` | `#006bff` | More button icon color. |

Auto-render on every page (instead of the shortcode) can be enabled with:

```php
add_filter( 'keb_auto_render', '__return_true' );
```

### Gutenberg block

Insert **"Kodezen Ecosystem Bar"** (Widgets category). All the options above are exposed as sidebar controls (Layout, Bar color, More button, Hover & active), with a live server-rendered preview.

## Configuration

Edit the product list — names, URLs, order, and which are inline vs. in the dropdown — in [`includes/products.php`](includes/products.php). Add a product by dropping its SVG into [`assets/logos/`](assets/logos/) and adding a row.

## Structure

```
kodezen-ecosystem-bar.php        Bootstrap (shortcode + block registration)
includes/
  render.php                     Core renderer (keb_build_bar) + shortcode
  block.php                      Dynamic block + preview REST route
  products.php                   Product list (config)
blocks/ecosystem-bar/
  block.json                     Block metadata + attributes
  edit.js                        No-build editor UI
assets/logos/                    Inline brand SVGs
```

## Requirements

- WordPress 5.9+
- PHP 7.4+

## License

GPL-2.0-or-later
