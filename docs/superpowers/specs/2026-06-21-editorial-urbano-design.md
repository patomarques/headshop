# Design Spec — Editorial Urbano + Footer Horizontal
**Data:** 2026-06-21  
**Escopo:** Tipografia de seções da homepage + redesign do footer  

---

## Visão geral

Modernizar o layout da homepage e do footer da Headshop (WooCommerce / Bootscore child theme) com direção visual **Editorial Urbano**: tipografia display Syne 800, eyebrows de seção, régua horizontal, alternância de fundos claro/escuro entre seções, e footer horizontal minimalista em fundo claro.

---

## 1. Tipografia

### Fonte display: Syne 800
- Carregada via `wp_enqueue_style` apontando para Google Fonts (preconnect + stylesheet).
- Usada em: títulos de seção (`h2`), nome da marca no footer.
- Mantém `Space Grotesk` como fonte de corpo (já usada no mockup — pode ser adicionada junto ao Syne).

---

## 2. Títulos de Seção (Editorial Urbano)

### Markup por seção

```html
<div class="headshop-section-title">
  <span class="headshop-section-title__eyebrow">— Destaques da semana</span>
  <div class="headshop-section-title__row">
    <h2 class="headshop-section-title__text">EM OFERTA</h2>
    <div class="headshop-section-title__rule"></div>
  </div>
</div>
```

### Estilos

| Token | Valor |
|---|---|
| Eyebrow font | Syne 700, 0.65rem, letter-spacing .25em, uppercase |
| Título font | Syne 800, ~2.4rem desktop / ~1.6rem mobile, uppercase |
| Régua | `flex: 1; height: 1.5px; background: currentColor; opacity: .35` |
| Gap row | `align-items: flex-end; gap: 16px` |

### Seção "Produtos em Oferta" (fundo claro)
- Background: `#fafaf8` (off-white quente — substitui `#fff`)
- Eyebrow color: `#b0413e` (dusty-mauve)
- Título color: `#473335` (deep-mocha)
- Régua color: `#473335`

### Seção "Produtos Novos" (fundo escuro)
- Background: `#473335` (deep-mocha)
- Eyebrow color: `rgba(255, 255, 255, 0.45)`
- Título color: `#fcaa67` (sandy-brown)
- Régua color: `rgba(255, 255, 198, 0.3)` (cream)
- Cards: ajuste de cor de texto e background para contraste no fundo escuro

---

## 3. Cards de Produto em Seção Escura

Os cards `.headshop-new-products__card` dentro da seção dark ganham:
- Background: `rgba(255, 255, 255, 0.05)`
- Border: `1px solid rgba(255, 255, 255, 0.08)`
- Nome: `rgba(255, 255, 255, 0.8)`
- Preço: `#fcaa67`
- Hover shadow: `rgba(252, 170, 103, 0.15)`

---

## 4. Footer — Horizontal Minimalista (Opção C)

### Estrutura HTML

```
<footer>
  <div class="headshop-footer__top-rule"></div>       ← 3px deep-mocha
  <div class="headshop-footer__main">                 ← grid: auto 1fr auto
    <div class="headshop-footer__brand-col">
      INDICATIVA HEADSHOP (Syne 800, 2rem)
      Caruaru · 7 anos (eyebrow pequeno)
    </div>
    <div class="headshop-footer__info-cols">           ← flex, gap: 48px
      [Horário] [Endereço] [Links]
    </div>
    <div class="headshop-footer__social-col">
      Botões "chip": WhatsApp (filled) / Instagram (outlined)
    </div>
  </div>
  <div class="headshop-footer__bar">                  ← deep-mocha bg
    © 2024 INDICATIVA HEADSHOP      Política · Entrega
  </div>
</footer>
```

### Tokens

| Token | Valor |
|---|---|
| Footer background | `#fafaf8` |
| Top rule | `3px solid #473335` |
| Brand name | Syne 800, `2rem`, color `#473335` |
| Brand sub | `0.6rem`, letter-spacing `.2em`, color `#fcaa67` |
| Info group title | `0.55rem`, uppercase, letter-spacing `.2em`, color `#473335`, opacity `.5` |
| Info group text | `0.7rem`, color `#8c7173` |
| Social chip (WhatsApp) | filled: bg `#473335`, text `#fff` |
| Social chip (Instagram) | outline: border `1.5px #473335`, text `#473335` |
| Bottom bar bg | `#473335` |
| Bottom bar text | `rgba(255,255,255,.35)`, `0.6rem` |

---

## 5. Arquivos a modificar

| Arquivo | O que muda |
|---|---|
| `functions.php` | `headshop_enqueue_assets()` — adicionar Google Fonts; `headshop_sale_products()` e `headshop_new_products()` — novo markup de título com eyebrow + rule |
| `footer.php` | Estrutura completa do footer substituída |
| `assets/scss/_bootscore-custom.scss` | Estilos das seções e do footer |
| Compilar com sass | `sass assets/scss/main.scss assets/css/main.css --style expanded` + versão min |

---

## 6. O que NÃO muda

- Lógica PHP de busca de produtos
- Sistema de carousel (JS + botões prev/next)
- Cards dentro do carousel (exceto ajuste de cor na seção escura)
- Header, banner, categorias
- Cores da paleta — apenas reaproveitadas em novos contextos

---

## Critérios de sucesso

- [ ] Fonte Syne carregada sem FOUT (preconnect declarado)
- [ ] Títulos de seção com eyebrow + rule visíveis em desktop e mobile
- [ ] Seção "Novos" com fundo deep-mocha, cards legíveis
- [ ] Footer horizontal com 3 colunas colapsando corretamente em mobile
- [ ] Bottom bar com copyright e links
- [ ] Nenhuma regressão nas seções de banner/categorias
