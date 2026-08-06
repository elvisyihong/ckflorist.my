# CK Florist design system

## Direction

The system is warm editorial botanical: ivory paper, evergreen ink, muted rose, oxidized brass, and cocoa accents. Outfit is used for interface text; a high-contrast system serif gives headings a human floral cadence. The source remains usable if web fonts fail.

The generated direction uses an artistic asymmetric hero, an infinite botanical marquee, inline imagery within select editorial text, horizontal accordions on wide screens, card stacking, and restrained scrubbed text reveals. Motion is enhancement only and is disabled by `prefers-reduced-motion`.

## Tokens

| Group | Tokens |
| --- | --- |
| Colour | `ink #17372d`, `forest #214f40`, `moss #70866f`, `ivory #f8f3e8`, `paper #fffdf8`, `rose #c98f8b`, `blush #efd7ce`, `brass #b88a45`, `cocoa #5d4537`, `danger #a33b35` |
| Type | display `clamp(2.75rem, 8vw, 7.6rem)`; h2 `clamp(2rem, 5vw, 4.8rem)`; body `1rem/1.65`; small `0.8125rem/1.4` |
| Spacing | `4, 8, 12, 16, 24, 32, 48, 72, 96, 144px` |
| Radius | controls `12px`; cards `24px`; editorial images `42px`; pills `999px` |
| Shadow | soft `0 18px 50px rgb(23 55 45 / .10)`; lift `0 24px 70px rgb(23 55 45 / .16)` |
| Content | compact `42rem`; reading `68rem`; wide `90rem` |

## Components

- Buttons: primary forest, secondary bordered, quiet text action, and danger. Minimum touch area is 44×44px.
- Inputs: persistent label, helper/error region, 48px minimum height, visible 3px focus outline, never colour-only invalid state.
- Cards: flat paper surfaces with thin botanical borders; hover movement never exceeds 4px.
- Chips: multi-select controls expose `aria-pressed`; selected state uses fill plus checkmark.
- Badges: short status only, with text and shape differentiation.
- Modal: centered desktop dialog with focus trap; converts to a bottom sheet below 640px.
- Bottom sheet: drag-handle decoration, labelled close control, scroll-safe body, sticky actions.
- Toast: polite live region for success, assertive only for blocking failures; auto-dismiss pauses on hover/focus.
- Empty states: concise explanation plus one relevant action, not decorative filler.
- Skeleton: uses matching content geometry and no animation under reduced-motion.

## Layout and motion

Mobile is a single editorial column with a fixed bottom navigation and safe-area padding. Desktop uses a 12-column grid. The principal home bento fills a 12×2 grid exactly: 7×2 + 5×1 + 5×1. Main sections use 96–144px vertical separation on desktop and 64–96px on mobile.

GSAP is pinned to one version and loaded only on the home page. It stacks three service cards and reveals one story paragraph. If unavailable, all content remains visible and in document order.

## Content rules

Sample photography is always labelled as inspiration. Price ranges use “estimated” and fulfilment actions use “Send enquiry,” never “Buy now.” Confirmation language is reserved for staff status changes. No decorative uppercase section numbering is used.

