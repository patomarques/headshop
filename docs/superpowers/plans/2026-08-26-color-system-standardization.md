# Padronizar Cores do Site (remover azul do Bootstrap) — Plano de Implementação

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Eliminar o azul padrão do Bootstrap (`#0d6efd`) de botões, links, campos em foco e checkboxes/radios em todo o site, substituindo pela paleta já definida em `_colors.scss` (verde-oliva/cyan-escuro), de forma centralizada e não pontual.

**Architecture:** Um único bloco novo em `_bootscore-custom.scss` (reaproveitando os tokens de `_colors.scss`) com dois tipos de override CSS — tokens de `:root` (para o que o Bootstrap consome via `var()`) e classes específicas com `!important` (para o que o Bootstrap grava como cor fixa, como `.btn-primary`, `.form-check-input:checked`). Remove o remendo pontual redundante que já existia só para o botão do carrinho. Sem build step além do `sass` já usado nas mudanças anteriores desta sessão (PHP/JS não são tocados).

**Tech Stack:** SCSS (Dart Sass), variáveis já existentes em `_colors.scss`.

---

## Spec de referência

`docs/superpowers/specs/2026-08-26-color-system-standardization-design.md`

---

## Estrutura de arquivos

| Arquivo | Ação | Responsabilidade |
|---------|------|-------------------|
| `wp-content/themes/bootscore-child/assets/scss/_bootscore-custom.scss` | Modificar (novo bloco + remover bloco antigo) | Novo bloco de override das cores do Bootstrap; remove o remendo redundante `.checkout-button` |
| `wp-content/themes/bootscore-child/assets/css/main.css` e `main.min.css` | Recompilar | Saída compilada servida pelo site (ver Task 3) |

Valores derivados exatos, já confirmados compilando isoladamente as variáveis existentes (não são chute):

| Variável SCSS | Valor compilado |
|---|---|
| `red($color-primary), green($color-primary), blue($color-primary)` | `59, 74, 45` |
| `red($color-secondary), green($color-secondary), blue($color-secondary)` | `84, 134, 135` |
| `$color-primary` | `#3B4A2D` |
| `$color-secondary` | `#548687` |
| `$btn-primary-hover` | `rgb(53.1, 66.6, 40.5)` |
| `$btn-secondary-hover` | `rgb(75.6, 120.6, 121.5)` |
| `$link-color` | `#3B4A2D` |
| `$link-hover-color` | `rgb(51.92, 65.12, 39.6)` |

---

## Task 1: Adicionar o bloco de override de cores em `_bootscore-custom.scss`

> **Correção pós-implementação (bug de compilação encontrado na revisão de qualidade):** a versão original deste bloco vivia em `_colors.scss` e usava `rgba(#{red($color-primary)}, #{green($color-primary)}, #{blue($color-primary)}, .25)` dentro de `box-shadow`. Isso quebra a compilação inteira do tema (`Error: $red: 59 is not a number.`) porque, fora de uma `--custom-property`, `rgba()` é resolvida como função real do Sass e a interpolação vira string. A versão abaixo já está corrigida (usa `rgba($color-primary, .25)`, forma de 2 argumentos) e no lugar certo — `_colors.scss` é hoje um arquivo só de variáveis, sem nenhuma regra de seletor; este bloco gera CSS real, então pertence a `_bootscore-custom.scss`, junto dos outros overrides de componente (como o `.checkout-button` que a Task 2 remove a seguir).

**Files:**
- Modify: `wp-content/themes/bootscore-child/assets/scss/_bootscore-custom.scss` (logo antes do bloco `.checkout-button` existente, por volta da linha 3132)

Nota: o comentário de cabeçalho usa o estilo `/* ... */` (igual ao resto de `_bootscore-custom.scss`, ex. o próprio bloco `.checkout-button` logo abaixo), não o estilo `//` usado em `_colors.scss` — é o arquivo de destino que dita a convenção aqui.

- [x] **Step 1: Adicionar o bloco**

Abra `wp-content/themes/bootscore-child/assets/scss/_bootscore-custom.scss`, localize o comentário `CART — "Finalizar compra" button...` (`grep -n "checkout-button" wp-content/themes/bootscore-child/assets/scss/_bootscore-custom.scss` para achar a linha exata) e insira o bloco abaixo **imediatamente antes dele**:

```scss

/* =====================================================================
   Bootstrap color tokens — override do azul padrão pela paleta da loja
   O Bootstrap deste tema vem pré-compilado no tema pai e carrega DEPOIS
   do nosso CSS no cascade — !important é necessário em tudo abaixo,
   não é estilo, é a única forma de vencer a ordem de carregamento.
   Duas camadas: tokens de :root (o que o Bootstrap consome via var())
   e classes específicas (o que o Bootstrap grava como cor fixa).
   ===================================================================== */
:root {
  --bs-primary: #{$color-primary} !important;
  --bs-primary-rgb: #{red($color-primary)}, #{green($color-primary)}, #{blue($color-primary)} !important;
  --bs-secondary: #{$color-secondary} !important;
  --bs-secondary-rgb: #{red($color-secondary)}, #{green($color-secondary)}, #{blue($color-secondary)} !important;
  --bs-link-color: #{$link-color} !important;
  --bs-link-hover-color: #{$link-hover-color} !important;
  --bs-focus-ring-color: rgba(#{red($color-primary)}, #{green($color-primary)}, #{blue($color-primary)}, 0.25) !important;
}

.btn-primary,
.btn-primary:disabled {
  background-color: $color-primary !important;
  border-color: $color-primary !important;
}
.btn-primary:hover,
.btn-primary:focus,
.btn-primary:active {
  background-color: $btn-primary-hover !important;
  border-color: $btn-primary-hover !important;
}

.btn-secondary,
.btn-secondary:disabled {
  background-color: $color-secondary !important;
  border-color: $color-secondary !important;
}
.btn-secondary:hover,
.btn-secondary:focus,
.btn-secondary:active {
  background-color: $btn-secondary-hover !important;
  border-color: $btn-secondary-hover !important;
}

.btn-outline-primary {
  color: $color-primary !important;
  border-color: $color-primary !important;
}
.btn-outline-primary:hover,
.btn-outline-primary:focus,
.btn-outline-primary:active {
  background-color: $color-primary !important;
  border-color: $color-primary !important;
  color: #fff !important;
}

.btn-outline-secondary {
  color: $color-secondary !important;
  border-color: $color-secondary !important;
}
.btn-outline-secondary:hover,
.btn-outline-secondary:focus,
.btn-outline-secondary:active {
  background-color: $color-secondary !important;
  border-color: $color-secondary !important;
  color: #fff !important;
}

.form-control:focus,
.form-select:focus {
  border-color: $color-primary !important;
  box-shadow: 0 0 0 .25rem rgba($color-primary, .25) !important;
}

.form-check-input:checked {
  background-color: $color-primary !important;
  border-color: $color-primary !important;
}
.form-check-input:focus {
  border-color: $color-primary !important;
  box-shadow: 0 0 0 .25rem rgba($color-primary, .25) !important;
}
```

- [ ] **Step 2: Commit**

Não commitar ainda — commit final só depois da Task 4 (validação manual).

---

## Task 2: Remover o remendo redundante `.checkout-button`

> **Correção pós-implementação (regressão real encontrada na revisão de qualidade):** remover `.checkout-button` sem mais nada quebraria o `:hover` do botão "Finalizar compra" — ele não usa as classes `btn btn-primary` do Bootstrap (confirmado no template `cart/proceed-to-checkout-button.php` do WooCommerce: a classe real é `checkout-button button alt wc-forward`), então o override genérico da Task 1 nunca o alcança. O estado normal (não-hover) já fica correto de graça, porque o CSS pré-compilado do tema pai usa `background-color: var(--bs-primary)` ali — mas o `:hover` do tema pai grava a cor como RGB literal (`rgb(11.7, 99, 227.7)`, calculado em tempo de build), não como variável. Esse mesmo problema afeta toda a família de botões nativos do WooCommerce que compartilham essa regra (`.woocommerce div.product form.cart .button` — "Adicionar ao carrinho" na página de produto —, `.woocommerce button.button.alt`, `.woocommerce a.button`, `.woocommerce a.button-alt`), não só o botão do carrinho. Por isso, o Step 2 abaixo não é uma remoção pura — inclui também um override de `:hover` novo, mais abrangente que o remendo antigo, cobrindo toda essa família de uma vez.

**Files:**
- Modify: `wp-content/themes/bootscore-child/assets/scss/_bootscore-custom.scss` (linhas 3132-3145, remover)

- [x] **Step 1: Confirmar o bloco a remover**

O bloco atual (confirme que bate exatamente antes de remover):

```scss
/* =====================================================================
   CART — "Finalizar compra" button in the design system's green,
   not Bootstrap's default blue (.checkout-button uses var(--bs-primary)
   unmodified in the parent theme).
   ===================================================================== */
.checkout-button {
  background-color: $btn-primary-bg !important;
  border-color: $btn-primary-bg !important;

  &:hover {
    background-color: $btn-primary-hover !important;
    border-color: $btn-primary-hover !important;
  }
}
```

Rode para confirmar a localização exata antes de editar:

```bash
grep -n "checkout-button" wp-content/themes/bootscore-child/assets/scss/_bootscore-custom.scss
```

- [x] **Step 2: Remover o bloco e adicionar o override de `:hover` da família de botões nativos do WooCommerce**

Delete o bloco de comentário + `.checkout-button { ... }` mostrado no Step 1. O estado normal (não-hover) já fica coberto pelo token `--bs-primary` da Task 1 (o CSS do tema pai usa `background-color: var(--bs-primary)` nesses botões). O `:hover`, porém, precisa de um override próprio — adicione, no mesmo lugar onde `.checkout-button` estava:

```scss
/* =====================================================================
   WooCommerce's own .button/.button.alt/.checkout-button/a.button-alt
   family (not Bootstrap's .btn classes — used by "Add to cart" on the
   product page, "Proceed to checkout" on the cart, etc.) reads the
   resting-state color from var(--bs-primary), already fixed above, but
   the parent theme bakes the :hover color as a literal compiled RGB
   value (shade-color($primary, 10%) at build time) instead of a CSS
   variable — needs its own override.
   ===================================================================== */
.woocommerce div.product form.cart .button:hover,
.woocommerce-cart .wc-proceed-to-checkout a.checkout-button:hover,
.woocommerce button.button.alt:hover,
.woocommerce a.button:hover,
.woocommerce a.button-alt:hover {
  background-color: $btn-primary-hover !important;
  border-color: $btn-primary-hover !important;
}
```

- [x] **Step 3: Confirmar que sumiu**

```bash
grep -n "checkout-button" wp-content/themes/bootscore-child/assets/scss/_bootscore-custom.scss
```

Esperado: nenhuma saída (bloco removido).

- [ ] **Step 4: Commit**

Não commitar ainda.

---

## Task 3: Recompilar e verificar o CSS gerado

**Files:**
- Modify (gerado): `wp-content/themes/bootscore-child/assets/css/main.css`, `main.min.css`

- [x] **Step 1: Compilar**

```bash
cd wp-content/themes/bootscore-child/assets/scss
sass main.scss ../css/main.css --style expanded --source-map --quiet
sass main.scss ../css/main.min.css --style compressed --source-map --quiet
cd /var/www/html/headshop
```

Esperado: nenhum erro (avisos de depreciação do Dart Sass são esperados e já aparecem hoje neste projeto — não são falha).

- [x] **Step 2: Verificar que os tokens de root saíram corretos**

```bash
grep -o -- "--bs-primary: #3B4A2D[^;]*;\|--bs-primary-rgb: 59, 74, 45[^;]*;\|--bs-secondary: #548687[^;]*;" wp-content/themes/bootscore-child/assets/css/main.css
```

Esperado: as três linhas aparecem (cada uma com `!important`).

- [x] **Step 3: Verificar que `.btn-primary` não usa mais o azul**

```bash
grep -c "#0d6efd" wp-content/themes/bootscore-child/assets/css/main.css
grep -A3 "^\.btn-primary," wp-content/themes/bootscore-child/assets/css/main.css | head -6
```

Esperado: `0` no primeiro comando (nosso arquivo do tema filho nunca teve azul); `background-color: #3B4A2D !important;` no segundo. **Nota**: o azul ainda existe no CSS do tema PAI (`wp-content/themes/bootscore/assets/css/main.css`) — isso é esperado e não é bug; o que resolve o problema é a ordem de carregamento + `!important` do nosso override, não a ausência do azul no arquivo do Bootstrap.

- [x] **Step 4: Verificar `.form-check-input:checked` e `.form-control:focus`**

```bash
grep -A2 "^\.form-check-input:checked" wp-content/themes/bootscore-child/assets/css/main.css
grep -A2 "^\.form-control:focus" wp-content/themes/bootscore-child/assets/css/main.css
```

Esperado: `background-color: #3B4A2D !important;` / `border-color: #3B4A2D !important;` no primeiro; `border-color: #3B4A2D !important; box-shadow: 0 0 0 .25rem rgba(59, 74, 45, .25) !important;` no segundo.

- [ ] **Step 5: Commit**

Não commitar ainda — commit final na Task 4.

---

## Task 4: Validação manual (navegador) e commit final

Sem suite de testes visuais no repo e sem navegador disponível neste ambiente — os passos abaixo (mesmos da seção "Testes" da spec) precisam ser feitos manualmente pelo usuário.

- [ ] **Passo 1:** Logado, com item no carrinho, abrir `/finalizar-compra/` — botão "Finalizar pedido", radio de forma de pagamento selecionado, checkbox de termos e foco nos campos de endereço/CEP não devem aparecer azuis.
- [ ] **Passo 2:** `/carrinho/` — botão "Finalizar compra" continua verde-oliva (mesma cor de antes, agora vindo do override genérico).
- [ ] **Passo 3:** Header/busca do site — botões e outline-buttons sem azul.
- [ ] **Passo 4:** Página 404 e formulário de senha protegida (usam `btn-outline-primary`/`btn-outline-secondary`) — conferir visualmente.
- [ ] **Passo 5:** Passar por outras páginas com botões (loja, página de produto, paginação) confirmando que nada ficou sem cor por engano (herdando transparente/`currentColor`).

- [ ] **Step Final: Commit (só depois que todos os passos acima passarem)**

```bash
cd /var/www/html/headshop
git add wp-content/themes/bootscore-child/assets/scss/_colors.scss \
        wp-content/themes/bootscore-child/assets/scss/_bootscore-custom.scss \
        wp-content/themes/bootscore-child/assets/css/main.min.css
git commit -m "$(cat <<'EOF'
improved: standardize Bootstrap colors to match design system

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
EOF
)"
```

Nota: `main.css` (não-minificado) é ignorado pelo git (`.gitignore` do tema) — só `main.min.css` entra no commit, junto com os dois arquivos `.scss`.
