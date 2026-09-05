# Design: Redesenhar a Página de Pedido Recebido (estilo "ticket")

**Data:** 2026-08-27
**Objetivo:** Substituir o layout genérico do WooCommerce na página de confirmação de pedido (`/finalizar-compra/order-received/{id}/`) por um layout tipo "recibo/ticket", baseado no mockup fornecido pelo usuário (`pedido-recebido-indicativa.html`), adaptado às fontes e cores que o site já usa — sem introduzir uma identidade visual paralela.

---

## Contexto

- Página gerada pelo WooCommerce via `checkout/thankyou.php` → `checkout/order-received.php` + `<ul class="woocommerce-order-overview">` + `do_action('woocommerce_thankyou_{gateway}')` (usado pelo woo-asaas para status de pagamento) + `order/order-details.php` (tabela de itens).
- O mockup (`pedido-recebido-indicativa.html`) usa fontes (`Archivo` + `Source Serif 4`) e paleta (`--creme`, `--tinta`, `--vinho`, `--salvia`) que **não existem no site** — decisão já tomada: adaptar para as fontes (`Syne` display, `Oswald` texto — já carregadas via `functions.php`) e cores (`$color-primary`, `$color-secondary`, `$color-danger`, `$color-dark`, `$text-body`, `$text-muted` — já em `_colors.scss`) que o site inteiro usa, sem carregar fontes novas.
- O mockup só cobre o cenário de **retirada na loja** (`local_pickup`). O site também tem **entrega** (fora de Caruaru só retirada; dentro de Caruaru o cliente escolhe — spec `2026-08-02-caruaru-only-shipping-design.md`) — decisão já tomada: os dois cenários precisam de tratamento dinâmico real, não só o de retirada.
- Já existe precedente de sobrescrever templates do WooCommerce no tema filho: `wp-content/themes/bootscore-child/woocommerce/cart/cart-empty.php`.
- Pedido de referência usado no mockup: `#990`, retirada (`method_id = local_pickup`), pagamento com cartão de crédito, status confirmado — os dados batem com o pedido real (nomes de produtos idênticos), então o mockup foi montado a partir de dados reais, não fictícios.
- WhatsApp da loja já usado no rodapé: `https://wa.me/5581996366201`.
- Sem suíte de testes no repo — validação manual, como nos specs anteriores. Sem navegador neste ambiente — validação visual fica com o usuário.

---

## Decisões de escopo

1. **Abordagem**: override completo de template (`woocommerce/checkout/thankyou.php` no tema filho), não CSS por cima do HTML padrão — a estrutura do mockup (ticket com bordas tracejadas, badge, card escuro, colapsável) não existe no markup atual do WooCommerce e não dá pra simular só com CSS.
2. **Fontes/cores**: reaproveitar 100% do que já existe (`Syne`/`Oswald`, `$color-primary` etc.) — nada de `--creme`/`--vinho`/`--salvia`/Google Fonts novas.
3. **Retirada vs entrega**: detectado via `$order->get_shipping_methods()` — se algum item tiver `method_id === 'local_pickup'`, mostra o card de retirada (endereço da loja + horário, iguais ao rodapé); senão, mostra um card de entrega com o endereço de destino do pedido (`$order->get_shipping_address_*()` / `get_formatted_shipping_address()`) e sem o botão de WhatsApp "estou a caminho" (não faz sentido pra entrega).
4. **Badge de status**: mapeado de `$order->get_status()` — não hardcoded como "Pagamento confirmado" fixo:
   - `processing`, `completed` → "Pagamento confirmado" (verde, `$color-primary`)
   - `pending`, `on-hold` → "Aguardando pagamento" (`$text-muted` sobre `$section-light-bg` — não introduz uma cor de "alerta" nova que não existe na paleta do site)
   - `failed`, `cancelled` → "Pagamento recusado" / "Pedido cancelado" (`$color-danger`)
   - `refunded` → "Pedido reembolsado" (mesmo tom neutro do `pending`/`on-hold`)
5. **Itens do pedido**: reaproveita o padrão já usado no template core (`order/order-details-item.php`) — `$product->get_permalink($item)`, `$item->get_quantity()`, `$order->get_formatted_line_subtotal($item)` — sem reinventar formatação de preço/link.
6. **Frete**: linha "Entrega" mostra `$order->get_shipping_method()` (título) + `$order->get_shipping_total()` formatado (ou "grátis" se zero) — igual ao mockup, mas dinâmico.
7. **WhatsApp**: só aparece no cenário de retirada. Link `https://wa.me/5581996366201?text=` com mensagem pré-preenchida citando o número do pedido, ex. "Olá! Estou a caminho pra retirar meu pedido #990.".
8. **Endereço de cobrança colapsável**: nome (`get_formatted_billing_full_name()`), endereço (`get_formatted_billing_address()` ou campos individuais), telefone, e-mail — igual ao mockup, usando `<details>`/`<summary>` nativos (sem JS).
9. **"Continuar comprando"**: aponta para a Loja (`wc_get_page_permalink('shop')`), não `#`.
10. **Nota de rodapé** ("Dúvidas sobre o pedido?..."): texto fixo reaproveitando o WhatsApp da loja, sem depender de dados do pedido.
11. **Ícone de check animado**: mantido (decorativo, `prefers-reduced-motion` respeitado, igual ao mockup) — SVG inline, sem dependência de ícone externo.
12. **Fora de escopo**: o cupom/desconto, taxa, múltiplos métodos de frete no mesmo pedido — o mockup não cobre isso e o checkout atual da loja não usa cupom hoje (confirmar se necessário, mas não é um caso comum neste site). Se o pedido tiver essas linhas extras, cai num fallback simples (linha "Total" segue funcionando via `get_formatted_order_total()`, que já soma tudo corretamente mesmo sem exibir o detalhamento).

---

## Componentes

### 1. Template override: `wp-content/themes/bootscore-child/woocommerce/checkout/thankyou.php`

Reescreve a página inteira (substitui a combinação de `order-received.php` + `<ul class="woocommerce-order-overview">` + tabela de itens padrão do WooCommerce), reaproveitando `$order` (já disponível na variável do template, conforme o core). Estrutura (nomes de classe novos, prefixo `headshop-order-` pra não colidir com nada existente):

```
.headshop-order-hero          → ícone de check + "Pedido confirmado" + "Enviamos o comprovante para {email}"
.headshop-order-ticket        → card com:
  .headshop-order-ticket__top   → "Pedido nº {numero}" + badge de status
  .headshop-order-ticket__meta  → Data | Forma de pagamento
  .headshop-order-ticket__items → itens (nome+link, qtd, preço)
  .headshop-order-ticket__totals→ Entrega (método + valor/grátis) + Total (grande, destaque)
.headshop-order-pickup        → SE local_pickup: card escuro (endereço loja + horário + botão WhatsApp)
.headshop-order-delivery      → SE entrega: card com endereço de destino do pedido
.headshop-order-billing       → <details> com dados de cobrança
.headshop-order-back-link     → "← Continuar comprando"
.headshop-order-note          → nota de rodapé com WhatsApp
```

Trata explicitamente o caso `$order->has_status('failed')` (o core já mostra uma mensagem de erro + botão de pagar/ver conta nesse caso) e o caso `!$order` (pedido não encontrado) — reaproveitando a lógica do `thankyou.php` original do core pra esses dois casos, sem reescrever.

### 2. Estilos: novo bloco em `_bootscore-custom.scss`

Reaproveita variáveis existentes: `$color-primary` (verde-oliva, badge de confirmado / total em destaque), `$color-danger` (badge de recusado/cancelado), `$color-dark`/`$text-body`/`$text-muted` (textos), `$section-light-bg` ou similar pra fundo do "meta" da ticket, `border-radius` consistente com o resto do tema. Tipografia: `font-family` herdada do body (Oswald já é a fonte de texto do site) pros elementos normais; títulos/badges/eyebrows usando o mesmo tratamento (uppercase + letter-spacing) já visto em outros lugares do tema (ex. `.headshop-footer__info-title`), sem precisar de `Archivo`.

### 3. Responsividade

Mobile-first, largura máxima ~560-640px centralizada (igual ao ajuste já feito na página antes deste redesign) — o mockup já é mobile-first (só 1 coluna, `max-width: 560px`), então a adaptação principal é garantir que o card de retirada/entrega e a ticket não estourem em telas bem estreitas (< 360px) e que o botão do WhatsApp continue de fácil toque.

---

## Fluxo de dados

```
$order (WC_Order, já disponível no template)
        │
        ├─ !$order  → fallback do core (pedido não encontrado)
        ├─ has_status('failed') → fallback do core (erro + botão pagar)
        └─ pedido válido →
                 │
                 ├─ status → badge (mapa fixo de status → label/cor)
                 ├─ get_items() → lista de itens (nome+link+qtd+preço)
                 ├─ get_shipping_method() + get_shipping_total() → linha "Entrega"
                 ├─ get_formatted_order_total() → "Total"
                 ├─ método de envio == local_pickup?
                 │      ├─ sim → card de retirada (endereço fixo da loja) + WhatsApp
                 │      └─ não → card de entrega (endereço do pedido)
                 └─ dados de billing → <details> colapsável
```

---

## Tratamento de erros

- Pedido inexistente / falho: reaproveita a lógica original do `thankyou.php` do core (não reescrita).
- Produto removido/invisível (sem permalink): reaproveita o padrão do core (`$product->is_visible()` antes de linkar, senão mostra só o nome em texto).
- Pedido sem método de frete registrado (edge case): trata como "sem retirada nem entrega identificada" → mostra só o card de entrega genérico com o endereço do pedido, sem quebrar.
- Nenhum item de linha (raro, pedido vazio): a seção de itens simplesmente fica vazia — sem erro fatal.

---

## Testes (manuais)

1. Pedido `990` (retirada, cartão de crédito, confirmado) → compara visualmente com o mockup: hero, ticket, card de retirada com WhatsApp, billing colapsável, "continuar comprando".
2. Um pedido de **entrega** (dentro de Caruaru) → confirma que aparece o card de entrega com o endereço certo, sem o botão de WhatsApp de retirada.
3. Um pedido com status `pending`/`on-hold` → badge mostra "Aguardando pagamento", não "confirmado".
4. Um pedido com status `failed` → cai no fallback original do WooCommerce (não quebra).
5. Testar em largura estreita (< 360px) e larga (desktop) — nada estoura, botão do WhatsApp continua clicável e visível.
6. Clicar no link de um produto no ticket → vai pra página do produto.
7. Expandir/colapsar o "Endereço de cobrança" — funciona sem JS (é `<details>` nativo).
8. "Continuar comprando" → vai pra `/loja/` (ou a página de loja configurada), não fica em `#`.
