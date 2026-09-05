# Redesenhar a Página de Pedido Recebido — Plano de Implementação

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Substituir o layout padrão do WooCommerce na página de pedido recebido por um layout tipo "ticket/recibo", com badge de status dinâmico, itens do pedido, card de retirada ou entrega (dinâmico) e endereço de cobrança colapsável — usando as fontes/cores que o site já usa.

**Architecture:** Um único template override PHP (`woocommerce/checkout/thankyou.php` no tema filho) substitui a combinação de `order-received.php` + lista de overview do WooCommerce, preservando os fallbacks originais do core (pedido inexistente, pedido com falha) e os hooks de gateway de pagamento (essenciais — o woo-asaas usa `woocommerce_thankyou_{gateway}` pra mostrar QR code do Pix / link de boleto, não é decorativo). Um bloco CSS novo estiliza o resultado reaproveitando variáveis já existentes.

**Tech Stack:** PHP (template override do WooCommerce), SCSS (Dart Sass) — mesmo stack usado no resto do tema.

---

## Spec de referência

`docs/superpowers/specs/2026-08-27-order-received-redesign-design.md`

---

## Descoberta importante (não estava na spec original)

Ao investigar os hooks disparados pelo `thankyou.php` original, confirmei que `do_action('woocommerce_thankyou_' . $order->get_payment_method(), ...)` é usado pelo plugin **woo-asaas** para renderizar conteúdo funcional, não decorativo:
- Pix (`pix-thankyou.php`): mostra o **QR code** e o código copia-e-cola — sem isso o cliente não sabe como pagar.
- Boleto (`ticket-thankyou.php`): mostra o **link do boleto** (`bankSlipUrl`) — mesma coisa.
- Cartão de crédito (`credit-card-thankyou.php`): só mostra um status em texto (redundante com o badge novo, mas inofensivo).

Por isso o novo template **precisa continuar disparando esse hook** exatamente como o core faz — o conteúdo dele aparece em uma seção própria (`.woocommerce-order-details`, que **já tem estilo** do trabalho anterior nesta mesma página — título menor, borda superior) logo depois do card de retirada/entrega. Não dá pra reescrever esse conteúdo (é gerado pelo plugin), só acomodar visualmente.

---

## Estrutura de arquivos

| Arquivo | Ação | Responsabilidade |
|---------|------|-------------------|
| `wp-content/themes/bootscore-child/woocommerce/checkout/thankyou.php` | Criar | Template override — layout "ticket" completo |
| `wp-content/themes/bootscore-child/assets/scss/_bootscore-custom.scss` | Modificar (append) | Estilos do novo layout |
| `wp-content/themes/bootscore-child/assets/css/main.css` e `main.min.css` | Recompilar | Saída servida pelo site |

**Pedidos reais pra testar** (confirmados no banco, já com chave de acesso válida):
- `#990` — retirada (`local_pickup`), pagamento cartão, status **não-cancelado/falho** → `http://headshop.local/finalizar-compra/order-received/990/?key=wc_order_NKOpcsYnHWjK0`
- `#617` — entrega (`infixs-correios-automatico`), status **cancelled** → `http://headshop.local/finalizar-compra/order-received/617/?key=wc_order_pX8rLQFSx918a`

Esses dois pedidos juntos cobrem: cenário de retirada, cenário de entrega, badge "confirmado" (990) e badge "cancelado" (617) — sem precisar criar pedido de teste novo.

**Padrão visual "eyebrow" já usado no rodapé** (reaproveitar, não reinventar): `font-size: 0.8rem; font-weight: 900; letter-spacing: 0.15em; text-transform: uppercase;` (`_bootscore-custom.scss`, `.headshop-footer__info-title`). Fonte de destaque grande: `'Syne', sans-serif; font-weight: 800;` (mesmo arquivo, `.headshop-footer__brand-name`).

---

## Task 1: Criar o template override `checkout/thankyou.php`

**Files:**
- Create: `wp-content/themes/bootscore-child/woocommerce/checkout/thankyou.php`

- [x] **Step 1: Confirmar o comportamento atual (baseline, antes do override)**

```bash
curl -s -H "Host: headshop.local" "http://localhost/finalizar-compra/order-received/990/?key=wc_order_NKOpcsYnHWjK0" | grep -c "woocommerce-order-overview"
```

Esperado: `1` (markup padrão do WooCommerce ainda ativo — o override ainda não existe).

- [x] **Step 2: Criar o diretório e o arquivo**

> **Correções pós-implementação (achados reais da revisão de qualidade, já aplicadas no arquivo):**
> 1. O e-mail do cliente aparecia sem checar se quem está vendo a página é o dono logado do pedido — o core do WooCommerce esconde isso por privacidade (qualquer um com a URL+key do pedido não deveria ver o e-mail sem estar logado como o dono). Corrigido: `$show_billing_email = is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email();` controla a linha "Enviamos o comprovante para...".
> 2. Faltava mostrar meta de item (ex: variação/tamanho, em produtos variáveis) — adicionado `wc_display_item_meta($item)` e o filtro `woocommerce_order_item_name` (mesmo padrão do `order/order-details-item.php` do core). Para produtos simples (como os pedidos de teste) isso não muda nada visualmente — só produtos variáveis ganham a linha extra.
>
> O código abaixo já reflete as duas correções.

```bash
mkdir -p wp-content/themes/bootscore-child/woocommerce/checkout
```

Crie `wp-content/themes/bootscore-child/woocommerce/checkout/thankyou.php` com exatamente este conteúdo:

```php
<?php
/**
 * Thankyou page — Headshop "ticket" layout.
 *
 * Overrides WooCommerce's default checkout/thankyou.php. The "no order"
 * and "order failed" branches below are copied unchanged from WooCommerce
 * core (checkout/thankyou.php, template version 8.1.0) — only the happy
 * path (valid, non-failed order) is redesigned. woocommerce_before_thankyou,
 * woocommerce_thankyou_{payment_method} and woocommerce_thankyou still fire
 * in the same order core fires them: woo-asaas hooks the payment-method
 * action to render the Pix QR code / boleto link there — that's required
 * to complete payment, not decorative, so it must never be skipped.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 *
 * @var WC_Order $order
 */

defined('ABSPATH') || exit;
?>

<div class="woocommerce-order headshop-order">

<?php if ($order) :

    do_action('woocommerce_before_thankyou', $order->get_id());

    if ($order->has_status('failed')) :
        ?>
        <p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php esc_html_e('Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce'); ?></p>

        <p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
            <a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" class="button pay"><?php esc_html_e('Pay', 'woocommerce'); ?></a>
            <?php if (is_user_logged_in()) : ?>
                <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="button pay"><?php esc_html_e('My account', 'woocommerce'); ?></a>
            <?php endif; ?>
        </p>
        <?php
    else :
        $is_pickup = false;
        foreach ($order->get_shipping_methods() as $shipping_method) {
            if ('local_pickup' === $shipping_method->get_method_id()) {
                $is_pickup = true;
                break;
            }
        }

        $status_badges = [
            'processing' => ['label' => 'Pagamento confirmado', 'class' => 'is-confirmed'],
            'completed'  => ['label' => 'Pagamento confirmado', 'class' => 'is-confirmed'],
            'pending'    => ['label' => 'Aguardando pagamento', 'class' => 'is-pending'],
            'on-hold'    => ['label' => 'Aguardando pagamento', 'class' => 'is-pending'],
            'cancelled'  => ['label' => 'Pedido cancelado', 'class' => 'is-declined'],
            'refunded'   => ['label' => 'Pedido reembolsado', 'class' => 'is-pending'],
        ];
        $status = $order->get_status();
        $badge  = $status_badges[$status] ?? ['label' => ucfirst($status), 'class' => 'is-pending'];
        ?>

        <section class="headshop-order-hero">
            <div class="headshop-order-hero__check" aria-hidden="true">
                <svg viewBox="0 0 24 24"><polyline points="4 12.5 10 18.5 20 6.5" /></svg>
            </div>
            <h1>Pedido confirmado</h1>
            <?php
            // Só mostra o e-mail pra quem está logado como dono do pedido —
            // mesma checagem que o core faz na order-overview list (thankyou.php)
            // antes de expor o e-mail a quem só tem a URL+key do pedido.
            $show_billing_email = is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email();
            ?>
            <?php if ($show_billing_email) : ?>
                <p>Obrigado! Enviamos o comprovante para <strong><?= esc_html($order->get_billing_email()); ?></strong>.</p>
            <?php else : ?>
                <p>Obrigado! Seu pedido foi recebido.</p>
            <?php endif; ?>
        </section>

        <section class="headshop-order-ticket" aria-label="Resumo do pedido">
            <div class="headshop-order-ticket__top">
                <div class="headshop-order-ticket__num">
                    <span class="headshop-order-eyebrow">Pedido nº</span>
                    <strong><?= esc_html($order->get_order_number()); ?></strong>
                </div>
                <span class="headshop-order-badge <?= esc_attr($badge['class']); ?>"><?= esc_html($badge['label']); ?></span>
            </div>

            <div class="headshop-order-ticket__meta">
                <div>
                    <span class="headshop-order-eyebrow">Data</span>
                    <div class="headshop-order-ticket__val"><?= esc_html(wc_format_datetime($order->get_date_created())); ?></div>
                </div>
                <?php if ($order->get_payment_method_title()) : ?>
                    <div>
                        <span class="headshop-order-eyebrow">Pagamento</span>
                        <div class="headshop-order-ticket__val"><?= wp_kses_post($order->get_payment_method_title()); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="headshop-order-ticket__items">
                <?php foreach ($order->get_items() as $item_id => $item) :
                    $product           = $item->get_product();
                    $is_visible        = $product && $product->is_visible();
                    $product_permalink = $is_visible ? $product->get_permalink($item) : '';
                    ?>
                    <div class="headshop-order-ticket__item">
                        <span>
                            <?php
                            $item_name = $product_permalink
                                ? sprintf('<a href="%s">%s</a>', esc_url($product_permalink), esc_html($item->get_name()))
                                : esc_html($item->get_name());
                            echo wp_kses_post(apply_filters('woocommerce_order_item_name', $item_name, $item, $is_visible));
                            ?>
                            <span class="headshop-order-ticket__qty">&times; <?= esc_html($item->get_quantity()); ?></span>
                            <?php wc_display_item_meta($item); ?>
                        </span>
                        <span class="headshop-order-ticket__price"><?= wp_kses_post($order->get_formatted_line_subtotal($item)); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="headshop-order-ticket__totals">
                <?php if ($order->get_shipping_method()) : ?>
                    <div class="headshop-order-ticket__row">
                        <span>Entrega</span>
                        <span class="headshop-order-ticket__muted">
                            <?= esc_html($order->get_shipping_method()); ?>
                            &middot;
                            <?= $order->get_shipping_total() > 0 ? wp_kses_post(wc_price($order->get_shipping_total())) : 'grátis'; ?>
                        </span>
                    </div>
                <?php endif; ?>
                <div class="headshop-order-ticket__row headshop-order-ticket__row--grand">
                    <span class="headshop-order-ticket__label">Total</span>
                    <span class="headshop-order-ticket__amount"><?= wp_kses_post($order->get_formatted_order_total()); ?></span>
                </div>
            </div>
        </section>

        <?php if ($is_pickup) : ?>
            <section class="headshop-order-pickup" aria-label="Informações de retirada">
                <span class="headshop-order-eyebrow">Retirada no local</span>
                <h2>Te esperamos na loja</h2>
                <address>
                    Rua Tupy, 147 &mdash; Salgado<br>
                    Caruaru &mdash; PE &middot; 55016-080
                </address>
                <p class="headshop-order-pickup__hours">
                    <b>Segunda a sexta</b> &mdash; 9h30 às 19h<br>
                    <b>Sábado</b> &mdash; 9h30 às 16h
                </p>
                <a class="headshop-order-btn-whats" href="<?= esc_url('https://wa.me/5581996366201?text=' . rawurlencode('Olá! Estou a caminho para retirar meu pedido #' . $order->get_order_number() . '.')); ?>" target="_blank" rel="noopener noreferrer">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2c-1.5 0-3-.4-4.3-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.6-6.1c-.3-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1-.2.3-.7.8-.8 1-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4 0-.5.1-.7l.4-.5c.1-.2.1-.3 0-.5l-.8-1.9c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.9.9-1.1 2.2-.2 3.7 1.2 2 2.9 3.5 5 4.4 1.9.8 2.7.6 3.4.3.6-.2 1.2-.8 1.4-1.4.2-.6.2-1.1.1-1.2-.1-.1-.3-.2-.7-.4z" /></svg>
                    Avisar que estou a caminho
                </a>
            </section>
        <?php else : ?>
            <section class="headshop-order-delivery" aria-label="Endereço de entrega">
                <span class="headshop-order-eyebrow">Entrega</span>
                <h2>Enviaremos para</h2>
                <address><?= wp_kses_post($order->get_formatted_shipping_address(esc_html__('N/A', 'woocommerce'))); ?></address>
            </section>
        <?php endif; ?>

        <?php
    endif;

    do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id());
    do_action('woocommerce_thankyou', $order->get_id());

    if (!$order->has_status('failed')) :
        ?>
        <details class="headshop-order-billing">
            <summary>
                <span class="headshop-order-eyebrow">Endereço de cobrança</span>
                <span class="headshop-order-billing__chev" aria-hidden="true">&#9662;</span>
            </summary>
            <div class="headshop-order-billing__body">
                <?= wp_kses_post($order->get_formatted_billing_address(esc_html__('N/A', 'woocommerce'))); ?>
                <?php if ($order->get_billing_phone()) : ?>
                    <br><?= esc_html($order->get_billing_phone()); ?>
                <?php endif; ?>
                <?php if ($order->get_billing_email()) : ?>
                    &middot; <?= esc_html($order->get_billing_email()); ?>
                <?php endif; ?>
            </div>
        </details>

        <a class="headshop-order-back-link" href="<?= esc_url(wc_get_page_permalink('shop')); ?>">&larr; Continuar comprando</a>

        <p class="headshop-order-note">Dúvidas sobre o pedido? Fale com a gente no <a href="https://wa.me/5581996366201" target="_blank" rel="noopener noreferrer">WhatsApp</a> &mdash; Indicativa Headshop &middot; Caruaru &middot; <?= (int) (gmdate('Y') - 2019); ?> anos.</p>
        <?php
    endif;

else :

    wc_get_template('checkout/order-received.php', ['order' => false]);

endif; ?>

</div>
```

> **Correção pós-implementação (descoberta na revisão):** os pedidos `#990`/`#617` pertencem a uma conta de cliente registrada — o WooCommerce exige **login de verdade** pra ver esses pedidos (mostra um formulário de login, não o ticket), mesmo com a `key` certa na URL. Isso é comportamento nativo do WooCommerce (proteção contra adivinhar a URL do pedido), não um bug do override, e não tem workaround razoável via `curl` sem credenciais reais — não faz sentido tentar burlar login de uma conta real. Achamos só **um** pedido convidado no banco (`#982`, retirada, cancelado — `customer_id=0`), que usa verificação por e-mail (não login) e É automatizável via `curl` com um passo extra de POST. Os Steps 3-6 abaixo foram reescritos pra usar esse pedido; a cobertura de "retirada + confirmado" e "entrega + cancelado" com os pedidos reais 990/617 fica pro navegador, na Task 4 (onde o usuário já está logado como dono do pedido).

- [x] **Step 3: Verificar que o override está ativo (pedido convidado #982 — retirada, cancelado)**

O pedido convidado exige um passo de verificação por e-mail antes de mostrar o conteúdo (proteção padrão do WooCommerce) — precisa de cookie jar e um POST com o e-mail do pedido:

```bash
CJ=$(mktemp)
URL="http://localhost/finalizar-compra/order-received/982/?key=wc_order_GJQYWRCUtk9pF"
curl -s -c "$CJ" -b "$CJ" -H "Host: headshop.local" "$URL" -o /tmp/verify982.html
NONCE=$(grep -o 'name="check_submission" value="[^"]*"' /tmp/verify982.html | grep -o 'value="[^"]*"' | sed 's/value="//;s/"//')
curl -s -c "$CJ" -b "$CJ" -H "Host: headshop.local" -X POST "$URL" \
  --data-urlencode "check_submission=$NONCE" \
  --data-urlencode "_wp_http_referer=/finalizar-compra/order-received/982/?key=wc_order_GJQYWRCUtk9pF" \
  --data-urlencode "email=teste@example.com" \
  --data-urlencode "verify=1" -o /tmp/order982.html
rm -f "$CJ"
grep -c "headshop-order-ticket\b" /tmp/order982.html
grep -o "headshop-order-badge [a-z-]*" /tmp/order982.html
grep -c "headshop-order-pickup\b" /tmp/order982.html
grep -c "headshop-order-delivery\b" /tmp/order982.html
```

Esperado: `headshop-order-ticket` aparece (contagem ≥ 1); badge mostra `is-declined` (pedido 982 está cancelado); `headshop-order-pickup` aparece (é retirada); `headshop-order-delivery` **não** aparece (`0`).

- [x] **Step 4: Verificar itens e link de produto**

```bash
grep -o 'headshop-order-ticket__item.\{0,200\}' /tmp/order982.html | head -2
```

Esperado: pelo menos um item aparece com link `<a href=...>` e preço formatado (`R$`).

- [x] **Step 5: Verificar que o hook do gateway de pagamento continua disparando**

```bash
grep -c "woocommerce-order-details__title" /tmp/order982.html
```

Esperado: `1` ou mais.

- [x] **Step 6: Verificação adicional dos cenários que o `curl` não alcança (pedidos 990/617, via render server-side)**

Como 990/617 exigem login real, a lógica do template pra esses dois foi conferida renderizando `wc_get_template('checkout/thankyou.php', ...)` diretamente via PHP CLI com os objetos de pedido reais (mesma chamada que o WordPress faz, só pulando a camada HTTP/autenticação) — confirmado: pedido 990 (retirada) → `headshop-order-ticket=1`, badge `is-confirmed`, `headshop-order-pickup=1`, `headshop-order-delivery=0`, `headshop-order-btn-whats=1`, `woocommerce-order-details__title=2`; pedido 617 (entrega) → badge `is-declined`, `headshop-order-delivery=1`, `headshop-order-pickup=0`, `headshop-order-btn-whats=0`. A confirmação real via navegador (usuário logado) acontece na Task 4.

- [x] **Step 7: Commit**

Não commitar ainda — commit final só depois da Task 4 (validação manual).

---

## Task 2: Estilos do novo layout

**Files:**
- Modify: `wp-content/themes/bootscore-child/assets/scss/_bootscore-custom.scss` (append no final do arquivo)

> Nota: a seção `ORDER RECEIVED (thank you page)` já existente no arquivo (do trabalho anterior nesta página) continua valendo — ela estiliza `.woocommerce-order`, `.woocommerce-order-details__title`, `.woocommerce-order-overview`, `.woocommerce-table--order-details`, que ainda são usados (pelo hook do gateway de pagamento e, no fallback de pedido com falha, pelo notice do WooCommerce). Não remover nem duplicar — só adicionar as classes novas (`.headshop-order-*`).

- [x] **Step 1: Adicionar o bloco de estilos**

> **Correções pós-implementação (achados reais da revisão de qualidade, já aplicadas):**
> 1. `.headshop-order-back-link` usava `$color-danger` (vermelho) pra um link neutro de navegação ("Continuar comprando") — em todo o resto do arquivo `$color-danger` só é usado pra ações destrutivas/erro (remover item, badge de pedido cancelado). Trocado pra `$color-primary`.
> 2. Faltava `:focus-visible` em três elementos interativos (link "Continuar comprando", `<summary>` do endereço de cobrança, botão do WhatsApp) — o resto do arquivo sempre pareia `&:hover, &:focus`/`:focus-visible`. Adicionado.
> 3. O badge `.is-pending` (`background: $text-muted`, texto branco) ficava com contraste 4.44:1 — abaixo do mínimo AA (4.5:1) pra texto normal. Trocado pra `mix(#000, $text-muted, 10%)` (mesmo padrão de escurecimento já usado em `$btn-primary-hover`).
>
> O bloco abaixo já reflete as três correções.

Adicione ao final de `wp-content/themes/bootscore-child/assets/scss/_bootscore-custom.scss`:

```scss

/* =====================================================================
   ORDER RECEIVED — "ticket" redesign (headshop-order-*)
   Reaproveita o padrão "eyebrow" (uppercase, letter-spacing) já usado
   no rodapé (.headshop-footer__info-title) e a fonte de destaque Syne
   (.headshop-footer__brand-name) — sem carregar fonte nova.
   ===================================================================== */
.headshop-order {
  .headshop-order-eyebrow {
    display: block;
    font-size: 0.8rem;
    font-weight: 900;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: $text-muted;
  }
}

.headshop-order-hero {
  text-align: center;
  padding: 8px 0 28px;

  &__check {
    width: 52px;
    height: 52px;
    margin: 0 auto 14px;
    border-radius: 50%;
    background: $color-primary;
    display: grid;
    place-items: center;
    animation: headshop-order-pop 0.45s cubic-bezier(0.2, 1.4, 0.4, 1) both;

    svg {
      width: 26px;
      height: 26px;
      stroke: #fff;
      stroke-width: 3;
      fill: none;
    }
  }

  h1 {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    font-size: 1.75rem;
    line-height: 1.1;
  }

  p {
    color: $text-muted;
    margin-top: 8px;
    font-size: 1.05rem;

    strong {
      color: $text-body;
    }
  }
}

@keyframes headshop-order-pop {
  from {
    transform: scale(0.4);
    opacity: 0;
  }
}
@media (prefers-reduced-motion: reduce) {
  .headshop-order-hero__check {
    animation: none;
  }
}

.headshop-order-ticket {
  background: #fff;
  border: 1px solid rgba($color-dark, 0.12);
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba($color-dark, 0.05);

  &__top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 18px 20px;
    background: $section-light-bg;
  }

  &__num strong {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 1.6rem;
    display: block;
  }

  &__meta {
    display: flex;
    border-bottom: 1px dashed rgba($color-dark, 0.15);

    > div {
      flex: 1;
      padding: 14px 20px;

      & + div {
        border-left: 1px dashed rgba($color-dark, 0.15);
      }
    }
  }

  &__val {
    font-weight: 600;
    margin-top: 2px;
  }

  &__items {
    padding: 6px 20px;
  }

  &__item {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 16px;
    padding: 14px 0;

    & + & {
      border-top: 1px solid rgba($color-dark, 0.1);
    }

    a {
      color: $text-body;
      text-decoration-color: rgba($color-dark, 0.3);
      text-underline-offset: 3px;
    }
  }

  &__qty {
    color: $text-muted;
    font-size: 0.9rem;
    white-space: nowrap;
  }

  // wc_display_item_meta() output (variação/tamanho em produtos
  // variáveis) — só aparece quando o item tem meta, produtos simples
  // não geram essa lista.
  .wc-item-meta {
    list-style: none;
    margin: 4px 0 0;
    padding: 0;
    font-size: 0.85rem;
    color: $text-muted;
  }

  &__price {
    font-weight: 600;
    white-space: nowrap;
  }

  &__totals {
    border-top: 1px dashed rgba($color-dark, 0.15);
    padding: 14px 20px 18px;
  }

  &__row {
    display: flex;
    justify-content: space-between;
    padding: 4px 0;
    color: $text-muted;
    font-size: 0.95rem;

    &--grand {
      margin-top: 8px;
      padding-top: 12px;
      border-top: 1px solid rgba($color-dark, 0.15);
      color: $text-body;
      align-items: baseline;
    }
  }

  &__label {
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    font-size: 0.85rem;
  }

  &__amount {
    font-size: 1.4rem;
    font-weight: 600;
  }

  &__muted {
    font-size: 0.85rem;
  }
}

.headshop-order-badge {
  font-family: 'Syne', sans-serif;
  font-weight: 700;
  font-size: 0.75rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #fff;
  padding: 7px 12px;
  border-radius: 999px;
  white-space: nowrap;

  &.is-confirmed {
    background: $color-primary;
  }

  &.is-pending {
    background: mix(#000, $text-muted, 10%);
  }

  &.is-declined {
    background: $color-danger;
  }
}

.headshop-order-pickup,
.headshop-order-delivery {
  margin-top: 20px;
  border-radius: 10px;
  padding: 22px 20px;

  h2 {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    text-transform: uppercase;
    font-size: 1.2rem;
    letter-spacing: 0.02em;
    margin: 6px 0 12px;
  }

  address {
    font-style: normal;
    line-height: 1.55;
  }
}

.headshop-order-pickup {
  background: $color-dark;
  color: $section-light-bg;

  .headshop-order-eyebrow {
    color: rgba($section-light-bg, 0.65);
  }

  &__hours {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid rgba($section-light-bg, 0.18);
    font-size: 0.95rem;
    line-height: 1.6;
    color: rgba($section-light-bg, 0.85);

    b {
      color: $section-light-bg;
      font-weight: 600;
    }
  }
}

.headshop-order-delivery {
  background: $section-light-bg;
  color: $text-body;
}

.headshop-order-btn-whats {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  margin-top: 18px;
  background: $color-primary;
  color: #fff;
  font-family: 'Syne', sans-serif;
  font-weight: 700;
  font-size: 0.85rem;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  text-decoration: none;
  padding: 15px 18px;
  border-radius: 8px;

  svg {
    width: 18px;
    height: 18px;
    fill: currentColor;
  }

  &:hover,
  &:focus-visible {
    background: $btn-primary-hover;
    color: #fff;
  }
}

.headshop-order-billing {
  margin-top: 20px;
  background: #fff;
  border: 1px solid rgba($color-dark, 0.12);
  border-radius: 10px;

  summary {
    list-style: none;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;

    &::-webkit-details-marker {
      display: none;
    }

    &:focus-visible {
      outline: 2px solid $color-primary;
      outline-offset: 2px;
    }
  }

  &__chev {
    transition: transform 0.2s;
    color: $text-muted;
  }

  &[open] &__chev {
    transform: rotate(180deg);
  }

  &__body {
    padding: 0 20px 18px;
    color: $text-muted;
    line-height: 1.6;
  }
}

.headshop-order-back-link {
  display: block;
  text-align: center;
  margin-top: 24px;
  font-family: 'Syne', sans-serif;
  font-weight: 700;
  font-size: 0.8rem;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: $color-primary;
  text-decoration: none;
  padding: 12px;

  &:hover,
  &:focus-visible {
    text-decoration: underline;
    text-underline-offset: 4px;
  }
}

.headshop-order-note {
  margin-top: 28px;
  text-align: center;
  font-size: 0.85rem;
  color: $text-muted;

  a {
    color: $text-muted;
    text-decoration: underline;
  }
}
```

- [ ] **Step 2: Commit**

Não commitar ainda.

---

## Task 3: Recompilar e verificar o CSS

**Files:**
- Modify (gerado): `wp-content/themes/bootscore-child/assets/css/main.css`, `main.min.css`

- [x] **Step 1: Compilar**

```bash
cd wp-content/themes/bootscore-child/assets/scss
sass main.scss ../css/main.css --style expanded --source-map --quiet
sass main.scss ../css/main.min.css --style compressed --source-map --quiet
cd /var/www/html/headshop
```

Esperado: nenhum erro (avisos de depreciação do Dart Sass são esperados).

- [x] **Step 2: Verificar que as classes novas saíram no CSS compilado**

```bash
grep -c "headshop-order-ticket\|headshop-order-badge\|headshop-order-pickup\|headshop-order-btn-whats" wp-content/themes/bootscore-child/assets/css/main.css
```

Esperado: número maior que `0`.

- [x] **Step 3: Recarregar a página de teste e conferir visualmente via HTML (cores aplicadas)**

Use o pedido convidado `#982` (o único acessível via `curl` sem login real — ver nota na Task 1, Step 3, pelo mesmo fluxo de verificação por e-mail):

```bash
CJ=$(mktemp)
URL="http://localhost/finalizar-compra/order-received/982/?key=wc_order_GJQYWRCUtk9pF"
curl -s -c "$CJ" -b "$CJ" -H "Host: headshop.local" "$URL" -o /tmp/verify982b.html
NONCE=$(grep -o 'name="check_submission" value="[^"]*"' /tmp/verify982b.html | grep -o 'value="[^"]*"' | sed 's/value="//;s/"//')
curl -s -c "$CJ" -b "$CJ" -H "Host: headshop.local" -X POST "$URL" \
  --data-urlencode "check_submission=$NONCE" \
  --data-urlencode "_wp_http_referer=/finalizar-compra/order-received/982/?key=wc_order_GJQYWRCUtk9pF" \
  --data-urlencode "email=teste@example.com" \
  --data-urlencode "verify=1" | grep -o "headshop-order-badge is-declined"
rm -f "$CJ"
```

Esperado: aparece (confirma que o pedido 982 renderiza com o badge de status correto depois do recompile — o valor final da cor em si só dá pra conferir de verdade no navegador, na Task 4).

- [ ] **Step 4: Commit**

Não commitar ainda — commit final na Task 4.

---

## Task 4: Validação manual (navegador) e commit final

Sem navegador disponível neste ambiente — os passos abaixo precisam ser feitos manualmente pelo usuário. **Os pedidos 990/617 pertencem a uma conta registrada** — o WooCommerce vai pedir login antes de mostrar a página (não é bug); acesse **logado como o dono desses pedidos** (ou substitua por qualquer pedido seu recente de retirada/entrega).

- [ ] **Passo 1:** Logado, abrir `http://headshop.local/finalizar-compra/order-received/990/?key=wc_order_NKOpcsYnHWjK0` → comparar com o mockup (`pedido-recebido-indicativa.html`): hero com check, ticket com número/badge/itens/total, card escuro de retirada com endereço/horário/botão do WhatsApp, seção de pagamento do woo-asaas logo abaixo (título menor, sem quebrar o layout), endereço de cobrança colapsável, "Continuar comprando".
- [ ] **Passo 2:** Logado, abrir `http://headshop.local/finalizar-compra/order-received/617/?key=wc_order_pX8rLQFSx918a` → confirmar que aparece o card de **entrega** (não o de retirada/WhatsApp) e o badge mostra "Pedido cancelado" em vermelho/danger.
- [ ] **Passo 3:** Clicar no link de um produto no ticket → vai pra página do produto.
- [ ] **Passo 4:** Expandir/colapsar "Endereço de cobrança" → funciona sem erro.
- [ ] **Passo 5:** Clicar em "Continuar comprando" → vai pra página da loja.
- [ ] **Passo 6:** Clicar no botão do WhatsApp (pedido 990) → abre o WhatsApp com a mensagem pré-preenchida citando o número do pedido.
- [ ] **Passo 7:** Testar em largura estreita (< 360px) e em desktop — nada estoura, texto legível, botão do WhatsApp com bom alvo de toque.
- [ ] **Passo 8 (se possível):** Um pedido real com Pix ou boleto pendente → confirmar que o QR code / link do boleto do woo-asaas ainda aparece normalmente (é o hook crítico preservado na Task 1).

- [ ] **Step Final: Commit (só depois que todos os passos acima passarem)**

```bash
cd /var/www/html/headshop
git add wp-content/themes/bootscore-child/woocommerce/checkout/thankyou.php \
        wp-content/themes/bootscore-child/assets/scss/_bootscore-custom.scss \
        wp-content/themes/bootscore-child/assets/css/main.min.css
git commit -m "$(cat <<'EOF'
feature: redesign order-received page as a receipt-style ticket

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
EOF
)"
```

Nota: `_bootscore-custom.scss` e `main.min.css` também têm outras mudanças pendentes de sessões/tarefas anteriores (mobile-cart, cor do Bootstrap) — usar `git add -p` se precisar isolar só as mudanças desta feature, ou confirmar com o usuário se pode ir tudo junto num commit maior.
