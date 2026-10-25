---
name: Civic Glass
colors:
  surface: '#f8f9ff'
  surface-dim: '#cbdbf5'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff4ff'
  surface-container: '#e5eeff'
  surface-container-high: '#dce9ff'
  surface-container-highest: '#d3e4fe'
  on-surface: '#0b1c30'
  on-surface-variant: '#45464d'
  inverse-surface: '#213145'
  inverse-on-surface: '#eaf1ff'
  outline: '#76777d'
  outline-variant: '#c6c6cd'
  surface-tint: '#565e74'
  primary: '#000000'
  on-primary: '#ffffff'
  primary-container: '#131b2e'
  on-primary-container: '#7c839b'
  inverse-primary: '#bec6e0'
  secondary: '#006398'
  on-secondary: '#ffffff'
  secondary-container: '#5bb8fe'
  on-secondary-container: '#00476e'
  tertiary: '#000000'
  on-tertiary: '#ffffff'
  tertiary-container: '#001e2c'
  on-tertiary-container: '#008ebf'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dae2fd'
  primary-fixed-dim: '#bec6e0'
  on-primary-fixed: '#131b2e'
  on-primary-fixed-variant: '#3f465c'
  secondary-fixed: '#cce5ff'
  secondary-fixed-dim: '#93ccff'
  on-secondary-fixed: '#001d31'
  on-secondary-fixed-variant: '#004b73'
  tertiary-fixed: '#c4e7ff'
  tertiary-fixed-dim: '#7bd0ff'
  on-tertiary-fixed: '#001e2c'
  on-tertiary-fixed-variant: '#004c69'
  background: '#f8f9ff'
  on-background: '#0b1c30'
  surface-variant: '#d3e4fe'
typography:
  display:
    fontFamily: Plus Jakarta Sans
    fontSize: 3.5rem
    fontWeight: '800'
    lineHeight: 4.25rem
    letterSpacing: -0.03em
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 2.25rem
    fontWeight: '700'
    lineHeight: 2.75rem
    letterSpacing: -0.02em
  headline-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 1.75rem
    fontWeight: '700'
    lineHeight: 2.25rem
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 1.5rem
    fontWeight: '600'
    lineHeight: 2rem
    letterSpacing: -0.015em
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 1.25rem
    fontWeight: '600'
    lineHeight: 1.75rem
    letterSpacing: -0.01em
  body-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 1.125rem
    fontWeight: '400'
    lineHeight: 1.75rem
    letterSpacing: '0'
  body-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 1rem
    fontWeight: '400'
    lineHeight: 1.5rem
    letterSpacing: '0'
  body-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 0.875rem
    fontWeight: '400'
    lineHeight: 1.25rem
    letterSpacing: 0.005em
  label-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 0.875rem
    fontWeight: '600'
    lineHeight: 1.25rem
    letterSpacing: 0.01em
  label-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 0.75rem
    fontWeight: '600'
    lineHeight: 1rem
    letterSpacing: 0.02em
  label-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 0.6875rem
    fontWeight: '700'
    lineHeight: 0.875rem
    letterSpacing: 0.04em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1.5rem
  gutter-sm: 1rem
  gutter-lg: 2rem
  margin: 1rem
  margin-md: 2rem
  margin-lg: 4rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2rem
---

## Brand & Style

This design system crafts an intentional, civic-minded space that feels simultaneously grounded and luminous. Built for a hyperlocal community network, the identity avoids the noisy, chaotic nature of legacy forums in favor of high-clarity SaaS precision, modern architectural clarity, and welcoming neighborhood warmth. 

The aesthetic is anchored in Modern Corporate SaaS infused with selective Glassmorphism. Translucent elements, frosted backdrops, and refined surface layers evoke transparency and physical place without impairing utility. Crisp borders, disciplined layout metrics, and deliberate typography project legitimacy and civic trust, encouraging constructive neighborhood discourse, local commerce, and civic coordination.

## Colors

The palette relies on high-contrast foundation values, anchored by deep slates and luminous whites, accented by atmospheric cyans and functional semaphores:

- **Primary Canvas & Surfaces**: Base canvas defaults to `#F8FAFC`, paired with crisp `#FFFFFF` card layers and frosted overlays (`rgba(255, 255, 255, 0.82)`).
- **Core Brand & Structure**: `#0F172A` delivers structural weight for headers, dominant icons, and high-emphasis controls, while `#1E293B` serves secondary structural containment and active navigation states.
- **Vibrant Accent & Locality**: `#0284C7` and `#38BDF8` represent neighborhood vitality, live activity, links, and focal badges.
- **Functional Semaphores**:
  - **Success**: `#10B981` (Verified neighbor statuses, resolved items, community recommendations).
  - **Warning**: `#F59E0B` (Urgent updates, neighborhood watches, scheduled maintenance).
  - **Destructive / Alert**: `#EF4444` (Violations, immediate hazards, reports, cancellation).
- **Text & Neutral Hierarchy**: Primary body text is set to `#0F172A`, secondary commentary and UI metadata to `#64748B`, and subtle boundaries to `#E2E8F0`.

## Typography

The typography leverages Plus Jakarta Sans across all roles to achieve a balance between civic authority and neighborhood warmth. Geometric underpinnings maintain strict tabular alignment for dashboard statistics and map overlays, while humanist terminal curves prevent the platform from feeling detached or bureaucratic.

- **Weight Discipline**: Restrict implementation to 400 (Body), 600 (Labels, Subtitles), 700 (Headlines), and 800 (Display/Promotional).
- **Scale Usage**:
  - `display`: Landing heroes and neighborhood milestone metrics.
  - `headline-lg` / `headline-lg-mobile`: Top-level neighborhood names and view titles.
  - `headline-md`: Feed groupings, local business names, and modal heads.
  - `headline-sm`: Card titles, post subjects, and drawer headers.
  - `body-lg` / `body-md`: Primary feed reading, community discussions, and announcements.
  - `body-sm`: Metadata lines, timestamps, and secondary commentary.
  - `label-lg` / `label-md` / `label-sm`: Locality badges, pin callouts, status chips, and button text.

## Layout & Spacing

A disciplined 8px spatial grid dictates all layouts, padding rhythms, and alignment margins:

- **Layout Structure**:
  - **Desktop (>=1200px)**: 12-column grid with a maximum content constraint of `1280px`. Left rail for neighborhood scopes (280px fixed or 3 columns), center feed stream (6 columns), and contextual right rail (3 columns) for events and pinned alerts. Gutter is set to `gutter-lg` (`2rem`).
  - **Tablet (768px - 1199px)**: 8-column layout. Sidebar compresses into an off-canvas drawer or collapsible icon rail; margins scale to `margin-md` (`2rem`).
  - **Mobile (<768px)**: 4-column layout. Single column stream with sticky top locality headers and bottom glass-dock navigation. Outer margin scales to `margin` (`1rem`) and gutters reduce to `gutter-sm` (`1rem`).
- **Internal Component Rhythm**: All spacing strictly follows 4px and 8px progressions (`0.25rem`, `0.5rem`, `1rem`, `1.5rem`, `2rem`). Micro paddings within badges and locality chips use `space-xs` and `space-sm`. Standard content containers utilize `space-md` to `space-lg`.

## Elevation & Depth

Visual hierarchy combines low-opacity ambient drop shadows, hairline border definitions, and glassmorphic surfaces:

- **Surface Tiers**:
  - **Level 0 (Canvas)**: Background base `#F8FAFC`.
  - **Level 1 (Cards & Flat Panels)**: Solid `#FFFFFF` or translucent `rgba(255, 255, 255, 0.88)` with `backdrop-filter: blur(12px)`. Border: `1px solid rgba(226, 232, 240, 0.8)`. Shadow: `0 1px 3px 0 rgba(15, 23, 42, 0.04), 0 1px 2px -1px rgba(15, 23, 42, 0.04)`.
  - **Level 2 (Interactive Cards, Dropdowns & Popovers)**: `#FFFFFF` or `rgba(255, 255, 255, 0.94)` with `backdrop-filter: blur(16px)`. Border: `1px solid rgba(203, 213, 225, 0.6)`. Shadow: `0 4px 6px -1px rgba(15, 23, 42, 0.05), 0 2px 4px -2px rgba(15, 23, 42, 0.05)`.
  - **Level 3 (Modals, Pinned Overlays & Floating Navigation)**: `rgba(255, 255, 255, 0.95)` with `backdrop-filter: blur(20px)`. Border: `1px solid rgba(255, 255, 255, 0.4)`. Shadow: `0 20px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04)`.
- **Shadow Quality**: Shadows must remain cool-tinted, utilizing `#0F172A` at minimal opacity tiers. Harsh black casts are prohibited.

## Shapes

The design system incorporates geometric hierarchy across its corner radii to communicate interactive roles and nesting structures:

- **8px (`rounded-sm` / base radius)**: Used for granular controls, input fields, dropdown trigger buttons, check indicators, and locality micro-tags.
- **12px (`rounded-md`)**: Standard for interactive buttons, image thumbnails, media attachments, tooltips, and flyout menus.
- **16px (`rounded-lg`)**: Primary cards, post modules, map preview boxes, and contextual notifications.
- **20px (`rounded-xl`)**: Top-level modal overlays, neighborhood spotlight banners, bottom glass sheets on mobile, and major layout containers.
- **Pill (`rounded-full`)**: Strictly reserved for status dots, user avatars, pill badges, and search bars.

## Components

- **Buttons**:
  - *Primary*: Background `#0F172A`, text `#FFFFFF`, border-radius 12px, hover state `#1E293B`, active scaling 0.98. Height 40px (desktop), 44px (mobile touch targets).
  - *Secondary / Glass*: Background `rgba(255, 255, 255, 0.8)`, border `1px solid #E2E8F0`, text `#0F172A`, backdrop-filter blur(8px). Hover state background `#FFFFFF` with shadow Level 1.
  - *Accent*: Background `#0284C7`, text `#FFFFFF`. Hover state `#0369A1`.
  - *Destructive*: Background `rgba(239, 68, 68, 0.08)`, text `#EF4444`, border `1px solid rgba(239, 68, 68, 0.2)`. Hover background `#EF4444`, text `#FFFFFF`.

- **Locality Tags & Chips**:
  - Compact badges featuring an outline Map Pin icon (14px).
  - Background `rgba(2, 132, 199, 0.08)`, text `#0284C7`, border `1px solid rgba(2, 132, 199, 0.2)`. Radius 8px or pill.
  - Sub-neighborhood tags use neutral `#F1F5F9` background with `#64748B` text.

- **Cards & Feed Posts**:
  - Level 1 elevation, `#FFFFFF` background, 16px corner radius, hairline `1px solid #E2E8F0` border.
  - Internal padding of 24px (`space-lg`). Header area contains author avatar (40px circular), name, verified badge (`#10B981`), locality tag with pin, and timestamp (`#64748B`).
  - Interactive hover state: lifts smoothly with a `-2px` Y-axis translate and transition to Level 2 shadow.

- **Input Fields**:
  - Height 44px, radius 8px, background `#FFFFFF`, border `1px solid #CBD5E1`, text `#0F172A`, placeholder `#94A3B8`.
  - Focus state: border `#0284C7`, ring `3px solid rgba(2, 132, 199, 0.15)`, zero outline.

- **Checkboxes & Radios**:
  - Size 18px by 18px. Radii: 4px for checkboxes, 50% for radios.
  - Unchecked: `#FFFFFF` fill, `1.5px solid #CBD5E1` border.
  - Checked: `#0284C7` background, white check icon or center pip, subtle glow `0 0 0 2px rgba(2, 132, 199, 0.2)`.

- **Lists & Navigation Items**:
  - Horizontal padding 12px, vertical padding 8px, border-radius 8px.
  - Active selection: `#F0F9FF` background with `#0284C7` typography and an inset 3px left indicator bar.

- **Iconography**:
  - Lucide-style minimalist line icons with consistent 1.75px stroke width, rounded caps and joins, normalized to a 20x20px or 16x16px bounding box. Color matches adjacent text token.