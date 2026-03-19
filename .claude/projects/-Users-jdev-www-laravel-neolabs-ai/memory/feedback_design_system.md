---
name: design-system-warm-amber
description: All UI should use the warm amber accent color system, never purple — editorial SaaS aesthetic
type: feedback
---

Never use purple/violet colors in this project. All accent colors must use the project's warm amber system:
- Primary accent: `bg-amber-accent`, `text-amber-accent`
- Hover states: `hover:bg-amber-accent-dark`
- Light tints: `bg-amber-accent/20`, `border-amber-accent/40`
- Text on accent: `text-white` (for buttons on amber background)
- Selected state text: `text-white/70` (for secondary text on amber background)

**Why:** The user specified a clean, minimal, editorial SaaS design with warm neutral palette (off-white #FAF9F7, soft beige, warm amber/orange #D97706). The existing CSS theme already defines `amber-accent`, `amber-accent-light`, `amber-accent-dark` and a full `warm-*` scale in OKLch. Purple was incorrectly used in the initial image editor build and was corrected.

**How to apply:** When creating any new UI component or page, always use `amber-accent` for interactive highlights (selected items, CTA buttons, active states), `warm-*` scale for surface colors, and `muted-foreground` for secondary text. Never introduce purple, blue, or other off-brand accent colors.
