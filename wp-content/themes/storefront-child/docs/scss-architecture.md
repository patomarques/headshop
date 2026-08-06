# SCSS Architecture

## Entry point

`assets/scss/main.scss` — imports all partials in this order:

1. `abstracts/` — variables, mixins (no CSS output)
2. `base/` — reset, typography, buttons
3. `layout/` — header, footer
4. `components/` — reusable UI pieces
5. `pages/` — page-specific overrides (e.g. `_home.scss`)

## Build commands

```bash
npm run build   # one-off compressed build, no source map
npm run watch   # watch mode, recompiles on save
```

Output goes to `assets/css/main.css`. This file is gitignored — never edit it directly.

## Module system

Uses Dart Sass `@use` (not `@import`). Each partial that needs variables or mixins must declare:

```scss
@use '../abstracts/variables' as *;
@use '../abstracts/mixins' as *;
```

Color manipulation uses `sass:color` — never the deprecated `darken()`/`lighten()` functions:

```scss
@use 'sass:color';
color.adjust($color-primary, $lightness: -10%)
```

## Breakpoints

Defined in `_variables.scss` and consumed via the `respond-to($bp)` mixin in `_mixins.scss`. Available keys: `sm`, `md`, `lg`, `xl`.
