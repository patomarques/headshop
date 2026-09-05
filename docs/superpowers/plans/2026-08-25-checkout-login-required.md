# Exigir Login Antes do Checkout — Plano de Implementação

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Bloquear acesso anônimo à página de checkout, redirecionando para login/cadastro na Minha Conta e retornando automaticamente ao checkout após autenticação, para reduzir risco de fraude.

**Architecture:** Guard em `template_redirect` no `functions.php` do tema filho, mais um campo oculto injetado nos formulários nativos de login/cadastro do WooCommerce para permitir volta automática ao checkout. Reforçado pela desativação da opção nativa `woocommerce_enable_guest_checkout`. Sem build step (PHP puro, sem SCSS/JS envolvidos).

**Tech Stack:** PHP 8.x, WordPress hooks, WooCommerce core (`is_checkout()`, `is_wc_endpoint_url()`, `wc_add_notice()`, `WC_Form_Handler`).

---

## Spec de referência

`docs/superpowers/specs/2026-08-25-checkout-login-required-design.md`

---

## Estrutura de arquivos

| Arquivo | Ação | Responsabilidade |
|---------|------|-------------------|
| `wp-content/themes/bootscore-child/functions.php` | Modificar (append, nova seção 16 no final do arquivo, após linha 1752) | Guard de redirect + injeção do campo oculto `redirect` |
| `wp_options` (tabela do banco, via `mysql`) | Modificar 1 linha | `woocommerce_enable_guest_checkout` → `no` |

Ambiente de teste: site local em `http://headshop.local` (acessível via `curl -H "Host: headshop.local" http://localhost/...`). Sem WP-CLI disponível; PHP CLI local não tem `mysqli` habilitado (apenas o PHP do servidor web tem) — mudanças em `wp_options` são feitas via `mysql` direto (credenciais em `wp-config.php`: `wdevp_headshop` / `root`/`root` / `localhost`, prefixo `wdevp_`).

---

## Task 1: Guard — bloquear checkout anônimo com redirect

**Files:**
- Modify: `wp-content/themes/bootscore-child/functions.php` (append após linha 1752, fim do arquivo)

- [x] **Step 1: Confirmar o comportamento atual (sem guard) — baseline**

Rode:

```bash
curl -s -o /dev/null -w "%{http_code}\n" -H "Host: headshop.local" http://localhost/finalizar-compra/
```

Esperado: `200` (checkout carrega normalmente para anônimo, sem bloqueio — ainda não implementamos o guard).

- [x] **Step 2: Adicionar o guard em `functions.php`**

Abra `wp-content/themes/bootscore-child/functions.php`, vá até o final do arquivo (depois do fechamento da seção 15, linha 1752) e adicione:

```php


/* =====================================================================
   16. CHECKOUT — REQUIRE LOGIN
   Reduz risco de fraude: visitante anônimo que tentar acessar o
   checkout é redirecionado para Minha Conta (login/cadastro) antes de
   ver o formulário. Volta automática ao checkout é feita pelo campo
   oculto "redirect" (ver função headshop_checkout_redirect_field
   abaixo), lido nativamente por WC_Form_Handler::process_login() e
   process_registration(). order-pay/order-received ficam de fora pois
   já assumem que o pedido existe — não devem exigir novo login.
   ===================================================================== */

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

- [x] **Step 3: Verificar o redirect para anônimo**

Rode:

```bash
curl -s -D - -o /dev/null -H "Host: headshop.local" http://localhost/finalizar-compra/ | grep -i "^HTTP\|^Location"
```

Esperado: `HTTP/1.1 302 Found` e `Location: http://headshop.local/minha-conta/?redirect_to=...finalizar-compra%2F...` (a URL do checkout, url-encoded, como valor do parâmetro).

- [x] **Step 4: Verificar que o aviso aparece na página de destino**

A notice do WooCommerce vive na sessão (cookie), então esse passo precisa reusar o cookie da requisição anterior — não dá pra checar em uma chamada `curl` isolada. Use um cookie jar e siga o redirect na mesma "sessão":

```bash
CJ=$(mktemp)
curl -s -c "$CJ" -b "$CJ" -L -H "Host: headshop.local" http://localhost/finalizar-compra/ | grep -i "Faça login ou crie sua conta"
rm -f "$CJ"
```

Esperado: a linha do aviso aparece no HTML da página final (Minha Conta), impressa pelo hook nativo `woocommerce_before_customer_login_form`.

- [x] **Step 5: Commit**

Não commitar ainda — este plano roda em várias tasks; o commit final acontece depois da validação manual completa (Task 4).

---

## Task 2: Campo oculto `redirect` no formulário de cadastro

> **Descoberta durante a implementação:** o WooCommerce Blocks já registra nativamente um callback (`Automattic\WooCommerce\Blocks\BlockTypesController::redirect_to_field`) no hook `woocommerce_login_form_end` que faz exatamente isso para o formulário de **login** — só não existe equivalente para o de **cadastro**. Isso só aparece inspecionando os callbacks reais do hook (`$wp_filter['woocommerce_login_form_end']`), não lendo os templates. Por isso o hook em `woocommerce_login_form_start` foi removido do código abaixo (era redundante) e mantido apenas `woocommerce_register_form_start`.

**Files:**
- Modify: `wp-content/themes/bootscore-child/functions.php` (logo após o bloco da Task 1)

- [x] **Step 1: Adicionar a função e o hook**

Adicione, logo após o `add_action('template_redirect', ...)` da Task 1 (ainda dentro da seção 16):

```php

// WooCommerce já injeta esse campo nativamente no formulário de login
// (Automattic\WooCommerce\Blocks\BlockTypesController::redirect_to_field,
// hook woocommerce_login_form_end) — só falta no de cadastro, cobrimos
// apenas esse aqui para não duplicar o que o core já faz.
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

- [x] **Step 2: Verificar o campo oculto aparece com `redirect_to` válido**

Rode:

```bash
curl -s -H "Host: headshop.local" "http://localhost/minha-conta/?redirect_to=http%3A%2F%2Fheadshop.local%2Ffinalizar-compra%2F" | grep -o '<input type="hidden" name="redirect"[^>]*>'
```

Esperado: duas ocorrências, ambas com `value="http://headshop.local/finalizar-compra/"` — uma é o campo **nativo do WooCommerce** no formulário de login (não é código nosso), a outra é o campo do formulário de cadastro adicionado neste passo.

- [x] **Step 3: Verificar que o campo NÃO aparece sem `redirect_to`**

Rode:

```bash
curl -s -H "Host: headshop.local" "http://localhost/minha-conta/" | grep -c 'name="redirect"'
```

Esperado: `0`.

- [x] **Step 4: Verificar proteção contra open-redirect (domínio externo) no nosso campo**

Rode:

```bash
curl -s -H "Host: headshop.local" "http://localhost/minha-conta/?redirect_to=https%3A%2F%2Fexemplo-malicioso.com%2F" | grep -n 'name="redirect"'
```

Esperado: **uma** ocorrência (não zero) — é o campo **nativo do WooCommerce** no form de login, que usa `esc_url_raw()` sem checar host (comportamento pré-existente do core, fora do nosso escopo — mas inofensivo, pois `WC_Form_Handler::process_login()` revalida com `wp_validate_redirect()` antes de redirecionar de verdade). O importante é que o valor `https://exemplo-malicioso.com/` **não apareça duas vezes** — confirmando que o nosso campo (formulário de cadastro) corretamente não o imprimiu.

- [ ] **Step 5: Commit**

Ainda não — mesma observação da Task 1, commit único ao final (Task 4).

---

## Task 3: Desativar guest checkout nativo (reforço anti-fraude)

**Files:**
- Modify: `wp_options` (banco de dados, 1 linha)

- [x] **Step 1: Confirmar valor atual**

Rode:

```bash
mysql -uroot -proot -h localhost wdevp_headshop -N -e "SELECT option_value FROM wdevp_options WHERE option_name = 'woocommerce_enable_guest_checkout';"
```

Esperado: `yes`.

- [x] **Step 2: Desativar**

Rode:

```bash
mysql -uroot -proot -h localhost wdevp_headshop -e "UPDATE wdevp_options SET option_value = 'no' WHERE option_name = 'woocommerce_enable_guest_checkout';"
```

- [x] **Step 3: Verificar**

Rode:

```bash
mysql -uroot -proot -h localhost wdevp_headshop -N -e "SELECT option_value FROM wdevp_options WHERE option_name = 'woocommerce_enable_guest_checkout';"
```

Esperado: `no`.

Nota: essa é a mesma alteração que "WooCommerce → Ajustes → Contas e Privacidade → desmarcar 'Allow customers to place orders without an account'" faria via wp-admin — só estamos aplicando direto no banco por não haver WP-CLI disponível neste ambiente.

- [ ] **Step 4: Commit**

Não aplicável (mudança de dados, não de arquivo versionado).

---

## Task 4: Validação manual end-to-end (navegador)

Sem suite de testes automatizados no repo para o fluxo completo de login/sessão — os passos abaixo replicam a seção "Testes" da spec e precisam ser feitos manualmente pelo usuário no navegador, pois envolvem uma conta real e cookies de sessão que não devem ser simulados/criados automaticamente.

- [ ] **Passo 1:** Com o carrinho tendo pelo menos 1 item, deslogado, acessar `http://headshop.local/finalizar-compra/` diretamente pela URL → deve redirecionar para `/minha-conta/` com o aviso "Faça login ou crie sua conta para finalizar a compra com segurança." visível.
- [ ] **Passo 2:** Logar com uma conta existente no formulário de login → deve voltar automaticamente para `/finalizar-compra/`, carrinho intacto, formulário de checkout normal.
- [ ] **Passo 3:** Repetir o Passo 1 deslogado e, dessa vez, criar uma conta nova pelo formulário de cadastro → deve voltar automaticamente para `/finalizar-compra/`, já logado.
- [ ] **Passo 4:** Já logado, acessar `/finalizar-compra/` → carrega direto, sem nenhum redirect.
- [ ] **Passo 5:** Finalizar um pedido de teste e conferir que a página de confirmação (`order-received`) carrega normalmente.
- [ ] **Passo 6:** Se houver um pedido pendente de pagamento, conferir que o link de "pagar pedido" (`order-pay`) recebido por e-mail/painel continua acessível sem forçar novo login.
- [ ] **Passo 7:** Em WooCommerce → Ajustes → Contas e Privacidade, confirmar visualmente que "Allow customers to place orders without an account" está desmarcado (reflete a mudança da Task 3).

- [ ] **Step Final: Commit (só depois que todos os passos acima passarem)**

```bash
cd /var/www/html/headshop
git add wp-content/themes/bootscore-child/functions.php
git commit -m "$(cat <<'EOF'
feature: require login before checkout to reduce fraud risk

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
EOF
)"
```

Nota: a mudança da Task 3 (`woocommerce_enable_guest_checkout`) vive no banco de dados, não em arquivo versionado — não faz parte deste commit.
