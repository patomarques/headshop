# Design: Restringir Entrega a Caruaru-PE, Baseado em CEP

**Data:** 2026-08-02
**Objetivo:** A loja atualmente cobra frete por distância para qualquer endereço no Brasil (grátis/taxa fixa perto da loja, preço por km além do raio configurado). O negócio quer restringir a opção de **entrega** (frete) apenas a endereços em Caruaru-PE; endereços fora de Caruaru não podem escolher entrega — mas ainda podem finalizar a compra via "Retirada no local" (pickup), que já existe e é independente de zona.

---

## Contexto

- Método de frete atual: `Headshop_Shipping_Distance` ([inc/class-headshop-shipping-distance.php](../../../wp-content/themes/bootscore-child/inc/class-headshop-shipping-distance.php)) — calcula distância (haversine) via geocodificação Nominatim e cobra grátis/taxa fixa/por km.
- `local_pickup` ("Retirada no local") já está instalado em todas as zonas, gratuito, como alternativa universal à entrega (ver `functions.php` linha ~1660).
- O site já depende do ViaCEP (`https://viacep.com.br/ws/{cep}/json/`) no client-side (`custom.js`) para autocompletar endereço a partir do CEP, tanto no checkout quanto na calculadora de frete do carrinho.
- Checkout é o shortcode clássico `[woocommerce_checkout]` (não o bloco), então `local_pickup` funciona normalmente aqui.

---

## Decisões de escopo

1. **Critério de "é Caruaru?"**: consulta ao ViaCEP pelo CEP, comparando `localidade === "Caruaru"` e `uf === "PE"` (case-insensitive). Não usa faixa numérica de CEP — mais preciso, usa a fonte oficial (Correios via ViaCEP) que o site já depende.
2. **Regra de frete dentro de Caruaru**: inalterada. Continua grátis/taxa fixa perto da loja, ou por km além do raio de 5km configurado — desde que o destino esteja em Caruaru.
3. **Fora de Caruaru**: nenhuma taxa de entrega é oferecida (método "Entrega" desaparece das opções). "Retirada no local" continua disponível, então o cliente ainda pode finalizar a compra.
4. **Fail-closed**: se a consulta ao ViaCEP falhar (timeout, fora do ar) ou o CEP for inválido, trata como "fora de Caruaru" (sem entrega disponível) — não cai em taxa fixa como fallback.
5. **Bloqueio no carrinho**: apenas aviso informativo (não desabilita "Finalizar compra") — o campo de CEP na calculadora do carrinho é opcional e serve só para estimar frete de entrega; quem só quer retirar na loja não deveria ser barrado por causa dele.
6. **Bloqueio real**: acontece no checkout, de forma nativa — ao remover a opção "Entrega" das taxas disponíveis, o WooCommerce recalcula automaticamente via `update_checkout` sempre que o endereço muda, então nenhuma entrega para fora de Caruaru pode ser selecionada.

---

## Componentes

### 1. `Headshop_Shipping_Distance::calculate_shipping()` (PHP, server-side)

Novo passo **antes** da geocodificação Nominatim existente:

- Extrai o CEP de `$destination['postcode']`.
- Consulta `is_caruaru_postcode($postcode)` (novo método privado):
  - Normaliza o CEP (só dígitos, precisa ter 8).
  - Busca em transient cache (`headshop_viacep_city_{cep}`, 30 dias em caso de sucesso, 1 hora em caso de falha — mesmo padrão de cache já usado para geocodificação).
  - Se não estiver em cache, faz `wp_remote_get` para `https://viacep.com.br/ws/{cep}/json/` (timeout 5s).
  - Retorna `true` apenas se a resposta tiver `localidade` igual a "Caruaru" (case-insensitive) e `uf` igual a "PE".
  - Qualquer falha (erro HTTP, timeout, `erro: true` do ViaCEP, CEP mal formado) retorna `false`.
- Se `is_caruaru_postcode()` retornar `false`: método `calculate_shipping()` retorna imediatamente, sem chamar `add_rate()` — nenhuma opção de entrega aparece. Nominatim/haversine nem são chamados (evita chamada desnecessária).
- Se retornar `true`: segue exatamente a lógica atual (geocodificação Nominatim, haversine, grátis/flat/por-km).

`local_pickup` não é afetado — continua sendo adicionado a todas as zonas via `functions.php`, sem depender deste método.

### 2. Aviso no carrinho (`custom.js`, bloco "CART — shipping calculator CEP autocomplete")

Dentro do `fetch(...).then(function (data) { ... })` existente (que já preenche estado/cidade via ViaCEP e submete o formulário):

- Após `fillCalculator(data)`, comparar `data.localidade`/`data.uf` contra Caruaru/PE.
- Se não bater: mostrar mensagem inline (elemento novo, ex. `.headshop-cep-warning`, abaixo do campo CEP) com o texto **"Não entregamos neste CEP. Você pode retirar seu pedido na loja."**
- Se bater (ou campo for limpo/editado de novo): esconder a mensagem.
- **Não** mexe no botão "Finalizar compra" — puramente informativo.

### 3. Aviso no checkout (`custom.js`, bloco "CHECKOUT — CEP autocomplete")

Mesma lógica de comparação, reaproveitada dentro do `lookupCep()` existente (usado pelos campos `#billing_postcode` e `#shipping_postcode`):

- Após `fillAddress(prefix, data)`, mesma checagem Caruaru/PE.
- Se não bater: mostra a mesma mensagem inline perto do campo de CEP correspondente (billing ou shipping, conforme o prefixo).
- A remoção real da opção "Entrega" acontece via `update_checkout` nativo do WooCommerce (que já é disparado quando os campos de endereço mudam) — nenhum JS adicional necessário para isso.

### 4. Estilos (`_bootscore-custom.scss`)

Nova classe `.headshop-cep-warning` (texto pequeno, cor de alerta consistente com o design system existente — reaproveitar `$color` de aviso já usado no tema, ex. mesma cor usada em mensagens de erro do WooCommerce) para os avisos do carrinho e checkout.

---

## Fluxo de dados

```
Cliente digita CEP (carrinho ou checkout)
        │
        ▼
custom.js: fetch ViaCEP (client-side, já existente)
        │
        ├─ localidade/uf ≠ Caruaru/PE → mostra aviso inline (não bloqueia)
        └─ localidade/uf = Caruaru/PE → esconde aviso
        │
        ▼
WooCommerce update_checkout (AJAX nativo, já existente)
        │
        ▼
Headshop_Shipping_Distance::calculate_shipping() (server-side)
        │
        ├─ is_caruaru_postcode() = false → nenhuma rate "Entrega" adicionada
        │        └─ Cliente só vê "Retirada no local" → pode finalizar pedido
        │
        └─ is_caruaru_postcode() = true → lógica de distância existente
                 └─ grátis / taxa fixa / por km, conforme já implementado
```

---

## Tratamento de erros

- ViaCEP fora do ar/timeout (server-side, dentro de `calculate_shipping`): tratado como "fora de Caruaru" — cache de falha por 1 hora (não trava novas tentativas por muito tempo).
- CEP inválido/incompleto: mesmo tratamento — sem rate de entrega.
- ViaCEP fora do ar/timeout (client-side, no aviso): falha silenciosa, mesmo padrão já usado em `custom.js` — simplesmente não mostra aviso, sem quebrar a UI.
- Endereços em Caruaru continuam passando pela lógica existente (Nominatim, fallback de taxa fixa se Nominatim falhar) — nada muda aqui.

---

## Testes (manuais — não há suite PHP no repo)

1. CEP de Caruaru (ex. próximo à loja) no carrinho → sem aviso; ao ir para checkout, "Entrega" aparece com preço normal (grátis/taxa fixa conforme distância).
2. CEP de outra cidade (ex. Recife) no carrinho → aviso aparece; "Finalizar compra" continua clicável.
3. Mesmo CEP de fora no checkout → aviso aparece perto do campo; opções de frete mostram apenas "Retirada no local"; pedido pode ser finalizado normalmente com retirada.
4. CEP inválido/incompleto → nenhuma rate de entrega, sem erro fatal, sem aviso quebrado.
5. Confirmar que pedidos com CEP de Caruaru continuam com o mesmo comportamento de preço de antes da mudança (regressão).
