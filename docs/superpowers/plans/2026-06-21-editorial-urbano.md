# Editorial Urbano — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Modernizar a homepage com tipografia Editorial Urbano (Syne 800 + eyebrow + rule horizontal) e redesenhar o footer com layout horizontal minimalista em fundo claro.

**Architecture:** Editar SCSS em `_bootscore-custom.scss`, markup PHP em `functions.php` e `footer.php`, enfileirar Google Fonts via `wp_enqueue_style`. Compilar SCSS com `sass` após cada tarefa de estilo.

**Tech Stack:** PHP (WordPress/WooCommerce), SCSS (Dart Sass 1.97), Bootstrap 5

---

## Mapa de arquivos

| Arquivo | O que muda |
|---|---|
| `wp-content/themes/bootscore-child/functions.php` | `headshop_enqueue_assets()` — Google Fonts; `headshop_sale_products()` e `headshop_new_products()` — novo markup de título |
| `wp-content/themes/bootscore-child/footer.php` | Estrutura completa substituída |
| `wp-content/themes/bootscore-child/assets/scss/_bootscore-custom.scss` | Bloco `.headshop-footer` (L1373–1479) substituído; bloco `.headshop-sale-products__title` (L1716–1734) removido; seção `NEW PRODUCTS` (L1873–1874) recebe fundo e card escuros; novos estilos `.headshop-section-title` adicionados |

---

## Task 1: Enfileirar Google Fonts (Syne)

**Files:**
- Modify: `wp-content/themes/bootscore-child/functions.php:27-51`

- [ ] **Step 1:** Abrir `functions.php` e localizar a função `headshop_enqueue_assets()` (linha ~27).

- [ ] **Step 2:** Adicionar o `wp_enqueue_style` para o Google Fonts logo após o enqueue do `parent-style`:

```php
// Google Fonts — Syne display
wp_enqueue_style(
    'headshop-google-fonts',
    'https://fonts.googleapis.com/css2?family=Syne:wght@700;800&display=swap',
    array(),
    null
);
```

- [ ] **Step 3:** Adicionar preconnect hints via `wp_head` (fora da função, no topo do arquivo junto aos outros `add_action`):

```php
add_action('wp_head', function () {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}, 1);
```

- [ ] **Step 4:** Verificar no browser que `Syne` aparece em Network → carregando `fonts.googleapis.com/css2?family=Syne…`. Sem erros de CORS.

- [ ] **Step 5:** Commit:

```bash
git add wp-content/themes/bootscore-child/functions.php
git commit -m "feat: enqueue Google Fonts Syne 700/800"
```

---

## Task 2: SCSS — Estilos do título de seção (`.headshop-section-title`)

**Files:**
- Modify: `wp-content/themes/bootscore-child/assets/scss/_bootscore-custom.scss` (adicionar ao final)

- [ ] **Step 1:** Adicionar ao final de `_bootscore-custom.scss` o bloco de estilos do título compartilhado entre seções:

```scss
/* =====================================================================
   SECTION TITLE — Editorial Urbano
   ===================================================================== */

.headshop-section-title {
  margin-bottom: 28px;

  &__eyebrow {
    display: block;
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 0.65rem;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    margin-bottom: 6px;
  }

  &__row {
    display: flex;
    align-items: flex-end;
    gap: 16px;
  }

  &__text {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 2.4rem;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    line-height: 1;
    margin: 0;
    flex-shrink: 0;
  }

  &__rule {
    flex: 1;
    height: 1.5px;
    background: currentColor;
    opacity: 0.25;
    margin-bottom: 7px;
  }
}

@media (max-width: 768px) {
  .headshop-section-title {
    &__text {
      font-size: 1.6rem;
    }
  }
}
```

- [ ] **Step 2:** Compilar:

```bash
cd /var/www/html/headshop/wp-content/themes/bootscore-child
sass assets/scss/main.scss assets/css/main.css --style expanded --no-source-map
sass assets/scss/main.scss assets/css/main.min.css --style compressed --no-source-map
```

Resultado esperado: sem erros, dois arquivos CSS atualizados.

- [ ] **Step 3:** Commit:

```bash
git add assets/scss/_bootscore-custom.scss assets/css/main.css assets/css/main.min.css
git commit -m "feat: add .headshop-section-title styles (Syne, eyebrow, rule)"
```

---

## Task 3: SCSS — Seção "Produtos em Oferta" (fundo off-white, eyebrow dusky-mauve)

**Files:**
- Modify: `wp-content/themes/bootscore-child/assets/scss/_bootscore-custom.scss:1713-1734`

- [ ] **Step 1:** Localizar o bloco `.headshop-sale-products` (linha ~1713). Substituir apenas o `&__title { … }` (linhas 1716–1734) removendo o estilo antigo do título centralizado, e adicionar as cores de seção abaixo do `background`:

Encontrar e substituir este trecho dentro de `.headshop-sale-products { … }`:

```scss
  // REMOVER — bloco inteiro __title abaixo:
  &__title {
    font-size: 2rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: $color-dark;
    position: relative;
    padding-bottom: 15px;

    &::after {
      content: '';
      display: block;
      width: 60px;
      height: 3px;
      background: $sale-badge-bg;
      margin: 12px auto 0;
      border-radius: 2px;
    }
  }
```

Substituir por nada (deletar o bloco). E mudar `background: $section-light-bg;` para `background: #fafaf8;` (mesma semântica, tom off-white levemente quente).

Adicionar as cores do `.headshop-section-title` para essa seção, também dentro de `.headshop-sale-products { … }`:

```scss
  // Cores do título section-title para esta seção
  .headshop-section-title__eyebrow { color: $sale-badge-bg; }   // #b0413e
  .headshop-section-title__text    { color: $color-dark; }       // #473335
  .headshop-section-title__rule    { background: $color-dark; }
```

- [ ] **Step 2:** Compilar:

```bash
cd /var/www/html/headshop/wp-content/themes/bootscore-child
sass assets/scss/main.scss assets/css/main.css --style expanded --no-source-map
sass assets/scss/main.scss assets/css/main.min.css --style compressed --no-source-map
```

- [ ] **Step 3:** Commit:

```bash
git add assets/scss/_bootscore-custom.scss assets/css/main.css assets/css/main.min.css
git commit -m "feat: sale-products section - off-white bg, section-title colors"
```

---

## Task 4: SCSS — Seção "Produtos Novos" (fundo deep-mocha, cards ajustados)

**Files:**
- Modify: `wp-content/themes/bootscore-child/assets/scss/_bootscore-custom.scss:1873-1874`

- [ ] **Step 1:** Localizar `.headshop-new-products { background: #fff; … }` (linha ~1873). Substituir `background: #fff;` por `background: $color-dark;` e remover o bloco `&__title { … }` (análogo ao que foi feito na Task 3).

- [ ] **Step 2:** Dentro de `.headshop-new-products { … }`, adicionar após o `background`:

```scss
  color: rgba(255, 255, 255, 0.85);

  // Cores do título
  .headshop-section-title__eyebrow { color: rgba(255, 255, 255, 0.45); }
  .headshop-section-title__text    { color: $color-primary; }           // #fcaa67
  .headshop-section-title__rule    { background: rgba(255, 255, 198, 0.3); }

  // Botão "Ver todos"
  &__btn {
    color: $color-primary;
    border-color: $color-primary;

    &:hover {
      background: $color-primary;
      color: $color-dark;
      box-shadow: 0 6px 18px rgba($color-primary, 0.3);
    }
  }

  // Cards adaptados ao fundo escuro
  &__card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: rgba(255, 255, 255, 0.85);
    box-shadow: none;

    &:hover {
      box-shadow: 0 8px 24px rgba($color-primary, 0.15);
      color: rgba(255, 255, 255, 0.85);
    }
  }

  &__image-wrap { background: rgba(255, 255, 255, 0.04); }
  &__name       { color: rgba(255, 255, 255, 0.8); }
  &__price      { color: $color-primary; }

  // Botões do carousel (prev/next) no fundo escuro
  .headshop-products-carousel__btn {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.2);
    color: #fff;

    &:hover {
      background: $color-primary;
      color: $color-dark;
    }
  }
```

- [ ] **Step 3:** Compilar:

```bash
cd /var/www/html/headshop/wp-content/themes/bootscore-child
sass assets/scss/main.scss assets/css/main.css --style expanded --no-source-map
sass assets/scss/main.scss assets/css/main.min.css --style compressed --no-source-map
```

- [ ] **Step 4:** Commit:

```bash
git add assets/scss/_bootscore-custom.scss assets/css/main.css assets/css/main.min.css
git commit -m "feat: new-products section - dark mocha bg, card/button overrides"
```

---

## Task 5: PHP — Novo markup do título em `headshop_sale_products()`

**Files:**
- Modify: `wp-content/themes/bootscore-child/functions.php:720`

- [ ] **Step 1:** Localizar em `headshop_sale_products()` a linha:

```php
<h2 class="headshop-sale-products__title text-center mb-4">PRODUTOS EM OFERTA</h2>
```

Substituir por:

```php
<div class="headshop-section-title">
  <span class="headshop-section-title__eyebrow">— Destaques da semana</span>
  <div class="headshop-section-title__row">
    <h2 class="headshop-section-title__text">EM OFERTA</h2>
    <div class="headshop-section-title__rule"></div>
  </div>
</div>
```

- [ ] **Step 2:** Verificar no browser: seção "EM OFERTA" com eyebrow pequeno cinza-escarlate + h2 Syne bold + linha horizontal à direita. Fundo levemente off-white.

- [ ] **Step 3:** Commit:

```bash
git add wp-content/themes/bootscore-child/functions.php
git commit -m "feat: sale-products section title — Editorial Urbano markup"
```

---

## Task 6: PHP — Novo markup do título em `headshop_new_products()`

**Files:**
- Modify: `wp-content/themes/bootscore-child/functions.php:783`

- [ ] **Step 1:** Localizar em `headshop_new_products()` a linha:

```php
<h2 class="headshop-new-products__title text-center mb-4">PRODUTOS NOVOS</h2>
```

Substituir por:

```php
<div class="headshop-section-title">
  <span class="headshop-section-title__eyebrow">— Acabou de chegar</span>
  <div class="headshop-section-title__row">
    <h2 class="headshop-section-title__text">NOVIDADES</h2>
    <div class="headshop-section-title__rule"></div>
  </div>
</div>
```

- [ ] **Step 2:** Verificar no browser: seção "NOVIDADES" em fundo deep-mocha (#473335), título em sandy-brown, eyebrow semi-transparente, cards com background escuro, carousel buttons em branco.

- [ ] **Step 3:** Commit:

```bash
git add wp-content/themes/bootscore-child/functions.php
git commit -m "feat: new-products section title — Editorial Urbano markup + dark section"
```

---

## Task 7: SCSS — Redesenho do bloco `.headshop-footer`

**Files:**
- Modify: `wp-content/themes/bootscore-child/assets/scss/_bootscore-custom.scss:1373-1479`

- [ ] **Step 1:** Substituir o bloco `.headshop-footer { … }` inteiro (linhas 1373–1479) pelo novo bloco abaixo. O bloco termina em `}` antes de `.footer-dev-link` (linha ~1481 — manter `.footer-dev-link` intacto):

```scss
.headshop-footer {
  background: #fafaf8;
  color: $color-dark;
  border-top: none;

  a {
    color: inherit;
    text-decoration: none;

    &:hover { color: $color-primary; }
  }

  &__top-rule {
    height: 3px;
    background: $color-dark;
  }

  &__main {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 48px;
    align-items: start;
    padding: 36px 0;
  }

  &__brand-col {}

  &__brand-name {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 2rem;
    color: $color-dark;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    line-height: 1;
    margin-bottom: 6px;
  }

  &__brand-sub {
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: $color-primary;
  }

  &__info-cols {
    display: flex;
    gap: 40px;
    padding-top: 4px;
    flex-wrap: wrap;
  }

  &__info-group {}

  &__info-title {
    font-size: 0.55rem;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: $color-dark;
    opacity: 0.4;
    margin-bottom: 8px;
  }

  &__info-line {
    display: block;
    font-size: 0.7rem;
    color: $text-muted;
    margin-bottom: 4px;
    line-height: 1.5;

    a {
      color: inherit;

      &:hover { color: $color-primary; }
    }
  }

  &__social-col {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
    padding-top: 4px;
  }

  &__social-label {
    font-size: 0.55rem;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: $color-dark;
    opacity: 0.35;
  }

  &__social-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    text-decoration: none !important;
    transition: opacity 0.2s;

    img {
      width: 16px;
      height: 16px;
      object-fit: contain;
    }

    &--whatsapp {
      background: $color-dark;
      color: #fff;

      img { filter: brightness(0) invert(1); }

      &:hover { color: #fff; opacity: 0.8; }
    }

    &--instagram {
      background: transparent;
      color: $color-dark;
      border: 1.5px solid $color-dark;

      img { filter: brightness(0); }

      &:hover { color: $color-dark; opacity: 0.7; }
    }
  }

  &__bar {
    background: $color-dark;
    padding: 10px 0;
  }

  &__bar-inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  &__bar-text {
    font-size: 0.6rem;
    color: rgba(255, 255, 255, 0.3);
    letter-spacing: 0.06em;
  }

  &__bar-link {
    font-size: 0.6rem;
    color: rgba(255, 255, 255, 0.25);
    text-decoration: none !important;

    &:hover { color: rgba(255, 255, 255, 0.55); }
  }
}

@media (max-width: 768px) {
  .headshop-footer {
    &__main {
      grid-template-columns: 1fr;
      gap: 24px;
      padding: 28px 0;
    }

    &__social-col {
      align-items: flex-start;
      flex-direction: row;
      flex-wrap: wrap;
    }

    &__info-cols {
      flex-direction: column;
      gap: 20px;
    }
  }
}
```

- [ ] **Step 2:** Compilar:

```bash
cd /var/www/html/headshop/wp-content/themes/bootscore-child
sass assets/scss/main.scss assets/css/main.css --style expanded --no-source-map
sass assets/scss/main.scss assets/css/main.min.css --style compressed --no-source-map
```

- [ ] **Step 3:** Commit:

```bash
git add assets/scss/_bootscore-custom.scss assets/css/main.css assets/css/main.min.css
git commit -m "feat: footer redesign - horizontal minimalist SCSS"
```

---

## Task 8: PHP — Redesenho do `footer.php`

**Files:**
- Modify: `wp-content/themes/bootscore-child/footer.php`

- [ ] **Step 1:** Substituir todo o conteúdo de `footer.php` por:

```php
<?php
/**
 * Custom footer for Headshop — Editorial Urbano redesign
 * Layout: horizontal — Brand | Info groups | Social chips | Bottom bar
 *
 * @package Bootscore Child
 */

defined('ABSPATH') || exit;
?>

<?php do_action('bootscore_before_footer'); ?>

<footer id="footer" class="headshop-footer">
  <div class="headshop-footer__top-rule"></div>

  <div class="container" style="max-width:1400px;">
    <div class="headshop-footer__main">

      <!-- Brand -->
      <div class="headshop-footer__brand-col">
        <div class="headshop-footer__brand-name"><?php bloginfo('name'); ?></div>
        <div class="headshop-footer__brand-sub">Caruaru &middot; 7 anos</div>
      </div>

      <!-- Info groups -->
      <div class="headshop-footer__info-cols">
        <div class="headshop-footer__info-group">
          <div class="headshop-footer__info-title">Horário</div>
          <span class="headshop-footer__info-line">Seg–Sex 9:30–19h</span>
          <span class="headshop-footer__info-line">Sáb 9:30–16h</span>
        </div>
        <div class="headshop-footer__info-group">
          <div class="headshop-footer__info-title">Endereço</div>
          <span class="headshop-footer__info-line">Rua Tupy, 147 — Salgado</span>
          <span class="headshop-footer__info-line">Caruaru — PE &middot; 55016-080</span>
          <span class="headshop-footer__info-line">
            <a href="tel:+5581996366201">(81) 99636-6201</a>
          </span>
          <span class="headshop-footer__info-line">Delivery grátis a partir de R$20 (até 5km)</span>
        </div>
        <div class="headshop-footer__info-group">
          <div class="headshop-footer__info-title">Links</div>
          <span class="headshop-footer__info-line"><a href="<?= esc_url(site_url('/sobre')); ?>">Sobre</a></span>
          <span class="headshop-footer__info-line"><a href="<?= esc_url(get_privacy_policy_url()); ?>">Política de Privacidade</a></span>
          <span class="headshop-footer__info-line"><a href="<?= esc_url(site_url('/entrega-segura')); ?>">Entrega Segura</a></span>
        </div>
      </div>

      <!-- Social chips -->
      <div class="headshop-footer__social-col">
        <span class="headshop-footer__social-label">Redes</span>
        <a href="https://wa.me/5581996366201"
           target="_blank" rel="noopener noreferrer"
           class="headshop-footer__social-chip headshop-footer__social-chip--whatsapp"
           aria-label="WhatsApp">
          <img src="<?= esc_url(get_stylesheet_directory_uri()); ?>/assets/img/whatsapp.png" alt="" width="16" height="16" />
          WhatsApp
        </a>
        <a href="https://instagram.com/indicativaheadshop2"
           target="_blank" rel="noopener noreferrer"
           class="headshop-footer__social-chip headshop-footer__social-chip--instagram"
           aria-label="Instagram">
          <img src="<?= esc_url(get_stylesheet_directory_uri()); ?>/assets/img/instagram.png" alt="" width="16" height="16" />
          Instagram
        </a>
      </div>

    </div><!-- .headshop-footer__main -->
  </div><!-- .container -->

  <!-- Bottom bar -->
  <div class="headshop-footer__bar">
    <div class="container headshop-footer__bar-inner" style="max-width:1400px;">
      <span class="headshop-footer__bar-text">
        <a href="/" class="headshop-footer__bar-link">Indicativa Headshop</a>
        &copy; <?= date('Y'); ?>
      </span>
      <span class="headshop-footer__bar-text">
        <a href="<?= esc_url(get_privacy_policy_url()); ?>" class="headshop-footer__bar-link">Política</a>
        &nbsp;&middot;&nbsp;
        <a href="<?= esc_url(site_url('/entrega-segura')); ?>" class="headshop-footer__bar-link">Entrega</a>
      </span>
    </div>
  </div>

</footer>

<!-- Floating dev signature -->
<a href="https://webdev.recife.br/" class="footer-dev-link" target="_blank" rel="noopener"
   data-bs-toggle="tooltip" data-bs-placement="top" title="Desenvolvido por Web Dev Studio">
  <span aria-label="Desenvolvido por Web Dev Studio">&lt;/&gt;</span>
</a>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
```

- [ ] **Step 2:** Verificar no browser: footer com fundo off-white claro, nome da loja em Syne grande à esquerda, grupos de info no centro, chips WhatsApp/Instagram à direita, barra deep-mocha embaixo.

- [ ] **Step 3:** Verificar em mobile (≤768px): grid colapsa para coluna única, chips de rede social ficam em linha.

- [ ] **Step 4:** Commit:

```bash
git add wp-content/themes/bootscore-child/footer.php
git commit -m "feat: footer redesign - horizontal minimalist layout"
```

---

## Verificação final

- [ ] Homepage carrega sem erros PHP no log (`/var/log/apache2/error.log` ou WP Debug)
- [ ] Fonte Syne carregando nos títulos das seções (inspecionar elemento → font-family)
- [ ] Seção "EM OFERTA": fundo `#fafaf8`, eyebrow em dusky-mauve, título Syne 800
- [ ] Seção "NOVIDADES": fundo `#473335`, título em sandy-brown, cards escuros legíveis
- [ ] Footer claro com grid horizontal (3 áreas), chips sociais, barra escura embaixo
- [ ] Mobile: footer colapsa corretamente, chips ficam em linha
- [ ] `.footer-dev-link` (losango Web Dev Studio) ainda presente e funcional
