# Design: Exigir Login Antes do Checkout

**Data:** 2026-08-25
**Objetivo:** Hoje qualquer visitante anônimo pode finalizar compra como convidado (guest checkout habilitado). O negócio quer reduzir risco de fraude exigindo que o cliente esteja logado (conta identificada) *antes* de chegar na página de checkout, sem perder a venda de quem ainda não tem conta — permitindo cadastro na hora e retorno automático ao checkout.

---

## Contexto

- Checkout: página "Finalização de compra" (`/finalizar-compra/`, ID 9), shortcode clássico `[woocommerce_checkout]`.
- Minha Conta: página "Minha conta" (`/minha-conta/`, ID 10), shortcode `[woocommerce_my_account]` — já renderiza formulário de login **e** cadastro para visitantes deslogados (`woocommerce_enable_myaccount_registration` = `yes`).
- `woocommerce_enable_guest_checkout` = `yes` (permite finalizar sem conta) — será desativado.
- `woocommerce_enable_signup_and_login_from_checkout` = `no` — não há (e não haverá) formulário de login embutido na própria página de checkout; o fluxo passa a ser sempre via Minha Conta.
- Já existe uma string traduzida em `functions.php` ("You must be logged in to checkout.") — é o fallback nativo do WooCommerce para quando guest checkout está desativado; funciona como rede de segurança caso o redirect abaixo seja contornado.
- Não há suite de testes PHP no repo — validação manual, como nos specs anteriores.

---

## Decisões de escopo

1. **Onde bloquear**: só no carrinho para navegar (sem bloqueio ali — ver item 5) e, principalmente, na própria página de checkout, via redirect antes de renderizar o formulário. Carrinho e catálogo continuam 100% acessíveis para anônimos.
2. **Para onde redireciona**: página Minha Conta, com um parâmetro `redirect_to` apontando de volta para o checkout.
3. **Cadastro permitido**: quem não tem conta pode criar uma na hora, no próprio formulário de cadastro da Minha Conta (já habilitado, nenhuma mudança necessária ali).
4. **Volta automática**: após login ou cadastro bem-sucedido, o cliente é levado direto de volta ao checkout (carrinho intacto — sessão de carrinho não depende de estar logado).
5. **Carrinho não é bloqueado**: o botão "Finalizar compra" no carrinho continua levando direto para `/finalizar-compra/` normalmente; é lá que o guard intercepta se necessário. Não duplicar a checagem no carrinho.
6. **Exceções ao bloqueio**: `order-pay` (retomada de pagamento pendente) e `order-received` (confirmação do pedido) — são endpoints do checkout usados *depois* que o pedido já existe; não devem exigir novo login.
7. **Reforço nativo**: desativar `woocommerce_enable_guest_checkout` nas configurações do WooCommerce. Isso é a trava real contra fraude (todo pedido fica amarrado a uma conta autenticada); o redirect é a camada de usabilidade, não a de segurança.
8. **Segurança do redirect de volta**: o valor de `redirect_to` é validado com `wp_validate_redirect()` contra o próprio domínio antes de ser usado, evitando open-redirect.

---

## Componentes

### 1. Guard de acesso (`functions.php`, novo hook `template_redirect`)

```php
add_action('template_redirect', function () {
    if (!function_exists('is_checkout') || !is_checkout() || is_user_logged_in()) {
        return;
    }
    if (is_wc_endpoint_url('order-pay') || is_wc_endpoint_url('order-received')) {
        return;
    }

    wc_add_notice(
        __('Faça login ou crie sua conta para finalizar a compra com segurança.', 'headshop'),
        'notice'
    );

    $redirect = add_query_arg(
        'redirect_to',
        rawurlencode(wc_get_checkout_url()),
        wc_get_page_permalink('myaccount')
    );

    wp_safe_redirect($redirect);
    exit;
});
```

- `wc_add_notice(..., 'notice')` fica na sessão do WooCommerce e é impresso automaticamente na Minha Conta via hook nativo `woocommerce_before_customer_login_form` (`woocommerce_output_all_notices`) — nenhuma alteração de template necessária.
- `wp_safe_redirect` valida o host de destino automaticamente (é sempre o próprio site aqui, então sempre passa).

### 2. Campo oculto `redirect` no formulário de cadastro (`functions.php`, hook `woocommerce_register_form_start`)

> **Nota pós-implementação:** o WooCommerce Blocks já injeta esse mesmo campo nativamente no formulário de **login** (`Automattic\WooCommerce\Blocks\BlockTypesController::redirect_to_field`, hook `woocommerce_login_form_end`) — descoberto só em runtime, inspecionando os callbacks realmente registrados no hook (não aparece lendo os templates). Por isso o código abaixo cobre apenas o formulário de **cadastro**, que não tem equivalente nativo; duplicar para o login seria redundante.

```php
add_action('woocommerce_register_form_start', function () {
    if (empty($_GET['redirect_to'])) {
        return;
    }
    $target = wp_validate_redirect(
        esc_url_raw(wp_unslash($_GET['redirect_to'])),
        ''
    );
    if (!$target) {
        return;
    }
    printf('<input type="hidden" name="redirect" value="%s" />', esc_attr($target));
});
```

- `WC_Form_Handler::process_login()` e `process_registration()` já leem `$_POST['redirect']` nativamente e redirecionam para lá após sucesso (revalidando com `wp_validate_redirect` de novo no core) — nenhum código adicional de pós-login necessário.
- Se `redirect_to` estiver ausente ou inválido, o formulário de cadastro funciona exatamente como hoje (redireciona para Minha Conta). O formulário de login já tem esse comportamento coberto nativamente pelo WooCommerce Blocks (ver nota acima) — vale registrar que o campo nativo do WC usa apenas `esc_url_raw()` (sem checar o host), mas isso não abre uma falha real de open-redirect porque `process_login()` revalida o valor com `wp_validate_redirect()` antes de redirecionar de verdade; não é algo que devêssemos corrigir no core do WooCommerce.

### 3. Configuração nativa

- WooCommerce → Ajustes → Contas e Privacidade: desmarcar "Allow customers to place orders without an account" (`woocommerce_enable_guest_checkout` → `no`).
- Nenhuma outra opção nativa muda (`enable_signup_and_login_from_checkout` continua `no`; registro na Minha Conta continua `yes`).

---

## Fluxo de dados

```
Visitante anônimo clica "Finalizar compra" no carrinho
        │
        ▼
GET /finalizar-compra/  →  template_redirect guard
        │
        ├─ logado, ou order-pay/order-received → segue normal
        │
        └─ anônimo, checkout "puro" →
                 wc_add_notice() + redirect para
                 /minha-conta/?redirect_to=/finalizar-compra/
                        │
                        ▼
        Minha Conta exibe aviso + formulário de login/cadastro
        (campo oculto "redirect" = /finalizar-compra/)
                        │
        ┌───────────────┴───────────────┐
        ▼                               ▼
   Login com conta existente     Cadastro de conta nova
        │                               │
        └───────────────┬───────────────┘
                         ▼
        WC_Form_Handler redireciona para $_POST['redirect']
                         │
                         ▼
        GET /finalizar-compra/ (agora logado) → guard libera
        → checkout renderiza normalmente, carrinho intacto
```

---

## Tratamento de erros

- `redirect_to` ausente, vazio ou apontando para outro domínio: campo oculto não é impresso; login/cadastro caem no comportamento padrão do WooCommerce (vai para Minha Conta).
- Falha de login/cadastro (senha errada, e-mail já em uso etc.): tratamento nativo do WooCommerce já exibe o erro no mesmo formulário — nada customizado aqui.
- Se por algum motivo o guard for contornado (ex. chamada direta a endpoint de processamento), o próprio WooCommerce recusa finalizar o pedido sem conta, pois `enable_guest_checkout` estará desativado — mensagem nativa já traduzida ("Você precisa estar conectado para finalizar a compra.").

---

## Testes (manuais)

1. Visitante anônimo com itens no carrinho acessa `/finalizar-compra/` diretamente (URL) → é redirecionado para `/minha-conta/` com aviso visível.
2. No formulário de login da Minha Conta, entra com conta existente → volta automaticamente para `/finalizar-compra/`, carrinho intacto, formulário de checkout normal.
3. No formulário de cadastro, cria conta nova → mesma coisa: volta para o checkout já logado.
4. Cliente já logado acessa `/finalizar-compra/` → nenhum redirect, checkout normal.
5. Após concluir um pedido, a página de confirmação (`order-received`) carrega normalmente mesmo se a sessão de login expirar nesse meio-tempo.
6. Link de "pagar pedido pendente" (`order-pay`) recebido por e-mail continua acessível sem forçar novo login.
7. Tentar acessar `/minha-conta/?redirect_to=https://site-malicioso.com` manualmente → campo oculto não é gerado (ou login redireciona para Minha Conta, nunca para o domínio externo).
8. Confirmar em WooCommerce → Ajustes que "Allow customers to place orders without an account" está desmarcado.
