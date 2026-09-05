# Design: Padronizar Cores do Site (remover azul do Bootstrap)

**Data:** 2026-08-26
**Objetivo:** Botões, links, campos em foco e checkboxes/radios ainda aparecem no azul padrão do Bootstrap (`#0d6efd`) em várias partes do site — mais visível no checkout (`/finalizar-compra/`), mas não exclusivo dele. O negócio quer que toda a UI siga consistentemente a paleta já definida (`_colors.scss`), sem depender de remendos pontuais como o que já existe hoje só para o botão "Finalizar compra" do carrinho.

---

## Contexto

- Paleta e tokens semânticos já existem em `wp-content/themes/bootscore-child/assets/scss/_colors.scss`: `$color-primary` (verde-oliva `$dark-olive`), `$color-secondary` (cyan-escuro `$dark-cyan`), `$btn-primary-hover`, `$btn-secondary-hover`, `$link-color`, `$link-hover-color`.
- O tema filho (`bootscore-child`) **não** importa o SCSS do Bootstrap — ele só importa suas próprias variáveis/customizações (`main.scss`: `colors`, `bootscore-variables`, `bootscore-maps`, `bootscore-utilities`, `bootscore-custom`). O Bootstrap já vem **pré-compilado** em `wp-content/themes/bootscore/assets/css/main.css` (tema pai), carregado como stylesheet separado.
- **Ordem de carregamento real na página** (confirmada via HTML renderizado): `woocommerce-general.css` → `woo-asaas-store.css` → `parent-style` (`bootscore/style.css`) → `headshop-main-css` (nosso `_bootscore-custom.scss` compilado) → `main-css` do **tema pai** (Bootstrap compilado, o arquivo com `--bs-primary: #0d6efd`) → `bootscore-style-css`. Ou seja, **o CSS do Bootstrap do tema pai carrega DEPOIS do nosso CSS do tema filho** — qualquer override nosso sem `!important` seria vencido de volta pelo azul do Bootstrap no cascade. É por isso que o remendo existente do `.checkout-button` já usa `!important`; todo override novo precisa do mesmo.
- **Achado importante**: nem todo "azul do Bootstrap" vem de uma variável CSS (`var(--bs-primary)`) reaproveitável. Inspecionando o CSS compilado do tema pai diretamente:
  - `.btn-primary`, `.btn-secondary`, `.btn-outline-primary`, `.btn-outline-secondary`, `.form-control:focus`, `.form-select:focus`, `.form-check-input:checked`, `.form-check-input:focus` têm as cores **gravadas como valor literal** (ex: `--bs-btn-bg: #0d6efd`, `box-shadow: ... rgba(13,110,253,.25)`) — **não** referenciam `var(--bs-primary)`. Sobrescrever a variável de root não alcança essas classes; precisam de override direto, classe por classe.
  - Só `.text-primary`, `.bg-primary` (utilitários) e a cor de link/anel de foco padrão (`--bs-link-color`, `--bs-link-hover-color`, `--bs-focus-ring-color` — quando não usados dentro de `.form-control`/`.form-check-input`, que têm seu próprio valor literal) realmente consomem a variável de root.
- `.btn.btn-primary` / `.btn.btn-secondary` / `.btn.btn-outline-*` já são as classes que o tema pai usa para renderizar botões do WooCommerce (adicionar ao carrinho, ver carrinho, finalizar compra, busca, paginação, formulário de senha etc. — ver `bootscore/woocommerce/inc/wc-cart.php`, `wc-loop.php`, `wc-skip-cart.php`), então corrigir essas classes cobre o site inteiro, não só o checkout.
- No checkout especificamente, `.form-check-input:checked`/`:focus` cobre a caixa "Aceito os termos" e os radios de forma de pagamento — provavelmente o azul mais visível ali.
- Variáveis de cor próprias do WooCommerce (`--woocommerce`, `--wc-primary`, `--wc-blue`, definidas em `woocommerce.css`) foram checadas e **não são consumidas em nenhuma regra** desse mesmo arquivo — são tokens não usados, fora de escopo.
- CSS do gateway de pagamento (`woo-asaas-store.css`) foi checado e não tem nenhuma cor azul — não é fonte do problema.
- Sem suíte de testes automatizados no repo — validação manual, como nos specs anteriores. Sem acesso a navegador/screenshot neste ambiente — validação visual fica a cargo do usuário após a implementação.

---

## Decisões de escopo

1. **Onde o override vive**: em `_bootscore-custom.scss`, não em `_colors.scss`. `_colors.scss` é hoje um arquivo puro de variáveis (nenhuma regra de seletor) — o override precisa gerar CSS real (`:root { ... }`, `.btn-primary { ... }`), então pertence junto dos outros overrides de componente que já existem em `_bootscore-custom.scss` (ex: o antigo `.checkout-button`, que este trabalho substitui). `_colors.scss` continua sendo só a fonte dos valores (`$color-primary` etc.), que `_bootscore-custom.scss` já consome livremente (mesmo escopo global via `@import`).
2. **Duas camadas de override, ambas necessárias**:
   - **Tokens de root** (`--bs-primary`, `--bs-primary-rgb`, `--bs-secondary`, `--bs-secondary-rgb`, `--bs-link-color`, `--bs-link-hover-color`, `--bs-focus-ring-color`) — cobre utilitários (`.text-primary`, `.bg-primary`) e qualquer componente Bootstrap que genuinamente use `var()`.
   - **Classes específicas** (`.btn-primary`, `.btn-secondary`, `.btn-outline-primary`, `.btn-outline-secondary`, `.form-control:focus`, `.form-select:focus`, `.form-check-input:checked`, `.form-check-input:focus`) — cobre os casos onde o Bootstrap gravou a cor como valor fixo.
3. **`!important` obrigatório em ambas as camadas**, pela ordem de carregamento já explicada — não é sobre especificidade de seletor, é sobre origem/ordem no cascade.
4. **Consolidar, não duplicar**: o override genérico de `.btn-primary` cobre o botão "Finalizar compra" do carrinho (`.checkout-button` também carrega `btn btn-primary`) — o remendo específico existente (`_bootscore-custom.scss`, bloco `.checkout-button`) fica redundante e será **removido** como parte desta mudança.
5. **Fora de escopo**: variantes `-subtle`/`-emphasis`/`-bg-subtle`/`-border-subtle` do Bootstrap (usadas em `.alert-primary`, badges, etc.) — sem visualização disponível neste ambiente para calibrar tons intermediários com confiança, e uso real dessas variantes no site não foi confirmado como visível/relevante. Se aparecer algum badge/alert azul depois, tratamos como item separado.
6. **Fora de escopo**: bolha de descrição de campo do WooCommerce (`#1e85be`, hardcoded em `woocommerce.css`, usada em dicas tipo força de senha) — baixa visibilidade/probabilidade de aparecer no checkout normal, não vale o risco de mexer no CSS do plugin.

---

## Componentes

### 1. Tokens de root do Bootstrap (`_bootscore-custom.scss`, novo bloco logo antes do `.checkout-button` existente)

```scss
// =====================================================================
// Bootstrap color tokens — override do azul padrão pela paleta da loja
// =====================================================================
//
// O Bootstrap deste tema vem pré-compilado no tema pai e carrega DEPOIS
// do nosso CSS no cascade — !important é necessário em tudo abaixo,
// não é estilo, é a única forma de vencer a ordem de carregamento.
:root {
  --bs-primary: #{$color-primary} !important;
  --bs-primary-rgb: #{red($color-primary)}, #{green($color-primary)}, #{blue($color-primary)} !important;
  --bs-secondary: #{$color-secondary} !important;
  --bs-secondary-rgb: #{red($color-secondary)}, #{green($color-secondary)}, #{blue($color-secondary)} !important;
  --bs-link-color: #{$link-color} !important;
  --bs-link-hover-color: #{$link-hover-color} !important;
  --bs-focus-ring-color: rgba(#{red($color-primary)}, #{green($color-primary)}, #{blue($color-primary)}, 0.25) !important;
}
```

### 2. Botões (`_bootscore-custom.scss`, mesmo bloco)

```scss
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
```

### 3. Campos de formulário e checkboxes/radios (`_bootscore-custom.scss`, mesmo bloco)

```scss
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

> **Nota pós-implementação (correção de bug encontrada na revisão de qualidade):** a versão original usava `rgba(#{red($color-primary)}, #{green($color-primary)}, #{blue($color-primary)}, .25)` também aqui — funciona dentro de `--custom-property` (parsing tolerante), mas **quebra a compilação inteira do tema** dentro de uma propriedade CSS normal como `box-shadow`, porque `rgba()` é resolvida como função real do Sass e a interpolação vira string, não número (`Error: $red: 59 is not a number.`). A forma de 2 argumentos `rgba($color, $alpha)` do próprio Sass resolve isso e compila para o mesmo resultado (`rgba(59, 74, 45, 0.25)`).

### 4. Remoção do remendo redundante (`_bootscore-custom.scss`)

Remover o bloco `.checkout-button { ... }` (e o comentário associado) em `wp-content/themes/bootscore-child/assets/scss/_bootscore-custom.scss` — o novo `.btn-primary` genérico, adicionado logo acima dele, já cobre esse botão.

---

## Riscos e mitigação

- **`!important` em cascata**: usar `!important` extensivamente é normalmente um cheiro de código ruim, mas aqui é necessário pela ordem de carregamento herdada do tema pai (não é algo que devemos "consertar" reestruturando o enqueue — risco alto de quebrar outras coisas do bootscore, fora do escopo pedido). Mitigação: manter todos os overrides de cor concentrados num único bloco/arquivo (`_colors.scss`), documentado, em vez de espalhados — se precisar ajustar tom no futuro, é um lugar só.
- **Sem validação visual automatizada**: não há navegador disponível neste ambiente. A validação real (visual, checkout completo, formulário de senha, radios de pagamento) fica para o usuário depois da implementação.
- **Efeito colateral em áreas não visadas**: como `.btn-primary`/`.btn-secondary` são genéricos, a mudança afeta o site inteiro (header, busca, paginação, formulário de senha, blocos do Gutenberg), não só o checkout — isso é o objetivo (padronização), mas vale o usuário revisar essas áreas também, não só o checkout.

---

## Testes (manuais)

1. Compilar o SCSS (`sass main.scss ...`) e conferir, via `grep`, que `--bs-primary`, `.btn-primary`, `.form-check-input:checked` etc. aparecem no `main.css`/`main.min.css` compilado com as cores da paleta (não `#0d6efd`).
2. No navegador: `/finalizar-compra/` logado com item no carrinho — botão "Finalizar pedido", radios de forma de pagamento (selecionado), checkbox de termos, foco nos campos de endereço/CEP — nenhum deve aparecer azul.
3. `/carrinho/` — botão "Finalizar compra" continua verde-oliva (mesma cor de antes, agora vindo do override genérico em vez do remendo específico).
4. Header/busca — botões e outline-buttons (ex. ícone de busca, ações do header) sem azul.
5. Página 404 e formulário de senha protegida (usam `btn-outline-primary`/`btn-outline-secondary`) — conferir visualmente.
6. Conferir que nenhum outro elemento ficou sem cor (herdando `currentColor`/transparente) por engano — comparar visualmente antes/depois de cada área tocada.
