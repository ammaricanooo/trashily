---
name: Eco-Positive Exchange
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
  on-surface-variant: '#3d4a3d'
  inverse-surface: '#213145'
  inverse-on-surface: '#eaf1ff'
  outline: '#6d7b6c'
  outline-variant: '#bccbb9'
  surface-tint: '#006e2f'
  primary: '#006e2f'
  on-primary: '#ffffff'
  primary-container: '#22c55e'
  on-primary-container: '#004b1e'
  inverse-primary: '#4ae176'
  secondary: '#1f6c3a'
  on-secondary: '#ffffff'
  secondary-container: '#a4f1b2'
  on-secondary-container: '#24703e'
  tertiary: '#55615a'
  on-tertiary: '#ffffff'
  tertiary-container: '#a2afa7'
  on-tertiary-container: '#37433c'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#6bff8f'
  primary-fixed-dim: '#4ae176'
  on-primary-fixed: '#002109'
  on-primary-fixed-variant: '#005321'
  secondary-fixed: '#a6f4b5'
  secondary-fixed-dim: '#8bd79b'
  on-secondary-fixed: '#00210b'
  on-secondary-fixed-variant: '#005226'
  tertiary-fixed: '#d9e6dd'
  tertiary-fixed-dim: '#bdcac1'
  on-tertiary-fixed: '#131e19'
  on-tertiary-fixed-variant: '#3e4943'
  background: '#f8f9ff'
  on-background: '#0b1c30'
  surface-variant: '#d3e4fe'
typography:
  display-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 56px
    fontWeight: '800'
    lineHeight: 64px
    letterSpacing: -0.02em
  display-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 36px
    fontWeight: '800'
    lineHeight: 44px
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
  body-lg:
    fontFamily: Be Vietnam Pro
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Be Vietnam Pro
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  label-md:
    fontFamily: Be Vietnam Pro
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
    letterSpacing: 0.01em
  label-sm:
    fontFamily: Be Vietnam Pro
    fontSize: 12px
    fontWeight: '700'
    lineHeight: 16px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  unit: 8px
  container-max: 1280px
  gutter: 24px
  margin-desktop: 64px
  margin-mobile: 20px
  stack-sm: 16px
  stack-md: 32px
  stack-lg: 64px
---

## Brand & Style

The design system prioritizes environmental optimism and community action. The visual language is built on **Modern Minimalism** with **Tactile** influences, creating an approachable and rewarding atmosphere. The goal is to transform the perception of "waste" into "value."

The UI should feel lightweight and airy, utilizing generous whitespace and soft transitions to reduce cognitive load. A friendly, welcoming emotional response is achieved through organic shapes and a vibrant, life-affirming color palette that emphasizes growth and renewal.

## Colors

The color palette is anchored in a high-energy **Vibrant Green** (#22C55E) that symbolizes vitality and the "green light" to take action. 

- **Primary:** Used for main calls-to-action, progress indicators, and active states.
- **Secondary:** A deep forest green used for high-contrast text and grounding elements to ensure legibility and professional authority.
- **Tertiary:** A very soft mint tint used for large background sections and card surfaces to differentiate content areas without using heavy grays.
- **Neutral:** A balanced slate gray for body text and secondary information.

The background is kept at a crisp **#FFFFFF** to maintain a sense of cleanliness, essential for a waste-management platform.

## Typography

This design system utilizes **Plus Jakarta Sans** for headlines to provide a soft, modern, and slightly geometric feel that remains highly readable. Its friendly curves mirror the rounded UI components.

For body copy and functional labels, **Be Vietnam Pro** is used. It offers a contemporary, clean aesthetic that excels in data-heavy views (like point balances and transaction lists) while maintaining a warm, casual tone. 

- Use **display-lg** for hero sections to make a bold impact.
- Use **label-sm** in uppercase for overlines or category tags.
- Tighten letter spacing on larger headlines to maintain visual density.

## Layout & Spacing

The layout follows a **Fluid Grid** model with a maximum container width to prevent line lengths from becoming unreadable on ultra-wide displays. 

- **Desktop:** 12-column grid with 24px gutters. Use 64px side margins to create a "breathable" frame.
- **Tablet:** 8-column grid with 20px gutters and margins.
- **Mobile:** 4-column grid with 16px gutters and 20px margins.

Spacing is based on an 8px base unit. Vertical rhythm should use larger increments (**stack-lg**) between major sections to emphasize the clean, minimalist aesthetic. Smaller increments (**stack-sm**) should be used for grouping related content like icons and their descriptions.

## Elevation & Depth

Hierarchy is established through **Tonal Layers** and **Ambient Shadows**. 

1. **Base Level:** Pure white (#FFFFFF) for the main background.
2. **Surface Level:** Tertiary Green (#F0FDF4) for section backgrounds and secondary containers.
3. **Elevated Level:** White cards with extremely soft, large-radius shadows (Blur: 40px, Opacity: 4%, Color: Secondary Green). This creates a "lifted" effect that feels light and approachable rather than heavy or industrial.

Avoid harsh borders. Instead, use subtle shifts in background color or the aforementioned soft shadows to define boundaries.

## Shapes

The shape language is consistently **Rounded**, reflecting the "circular" nature of recycling and the friendly brand persona.

- **Standard Elements:** Buttons, inputs, and small cards use a 0.5rem (8px) radius.
- **Large Containers:** Hero images and main feature cards use a 1.5rem (24px) radius.
- **Interactive Tags:** Chips and status indicators should use a full pill-shape (999px) to distinguish them from actionable buttons.

## Components

### Buttons
Primary buttons use the Primary Green background with White text. They feature a subtle scale-up animation (1.02x) on hover. Secondary buttons use a Primary Green outline with a Tertiary Green ghost fill on hover.

### Cards
Cards are the primary vehicle for "Waste Categories" and "Reward Items." They should feature a white background, 24px padding, and the custom ambient shadow. Use a 1px border of Tertiary Green to add definition without weight.

### Progress Bars (Points)
Use a thick, rounded track in Tertiary Green with a Primary Green fill. Include a small leaf icon or spark effect at the end of the progress line to symbolize growth.

### Input Fields
Inputs should have a light gray background (#F1F5F9) that transitions to a White background with a Primary Green border upon focus. This visual feedback reinforces the "clean" aesthetic.

### Chips & Badges
Use for trash categories (e.g., "Plastic," "Paper"). These are pill-shaped with a Secondary Green text color on a Tertiary Green background for high legibility at small sizes.

### Point Counter
A specialized component featuring a large **display-md** font size for the number, paired with a Primary Green "Point" icon. This should be the most visually prominent element in the user dashboard.