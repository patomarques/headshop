# CLAUDE.md — Storefront Child Eletronicos

Instruções para o Claude Code neste projeto. Estas regras têm prioridade sobre o comportamento padrão.

---

## Projeto

WordPress + WooCommerce. Tema filho do Storefront. Toda customização de frontend vive em `wp-content/themes/storefront-child/`.

**Stack:** PHP 8.2 · WordPress 6.x · WooCommerce 9.x · Bootstrap 5.3 · Dart Sass · Vanilla JS + jQuery

---

## Regras obrigatórias

### Commits
- Idioma: **inglês**
- Formato: `prefix: mensagem` — **sem scope**
- Máximo: **12 palavras**
- Prefixos válidos: `feat` `fix` `chore` `docs` `style` `refactor` `perf` `test`
- **Sem trailers** (`Co-Authored-By`, assinaturas, etc.)

```
# ✅ correto
feat: add CEP autocomplete to checkout
fix: remove duplicate lost password link

# ❌ errado
feat(checkout): add CEP autocomplete    ← tem scope
fix: remove the duplicate lost password link that was showing twice  ← muito longo
```

> Referência completa: [`docs/commit-conventions.md`](docs/commit-conventions.md)

### Comentários no código
Não escreva comentários inline. O código deve ser auto-explicativo por meio de nomes descritivos. Quando documentação for necessária, crie um arquivo em `docs/`.

### SCSS
- Sempre use `@use` (nunca `@import`)
- Manipulação de cor: `sass:color` + `color.adjust()` — nunca `darken()`/`lighten()`
- Compile antes de testar: `npm run build`
- Nunca edite `assets/css/main.css` diretamente (arquivo gerado, gitignored)

> Referência: [`docs/scss-architecture.md`](docs/scss-architecture.md)

---

## Estrutura do tema

```
storefront-child/
├── assets/
│   ├── scss/         ← fonte; edite aqui
│   │   ├── abstracts/   variables, mixins
│   │   ├── base/        reset, typography
│   │   ├── layout/      header, footer
│   │   ├── components/  peças reutilizáveis
│   │   └── pages/       overrides por página
│   ├── css/          ← gerado (gitignored)
│   └── js/
├── woocommerce/      ← overrides de templates WC
├── docs/             ← documentação técnica
├── inc-*.php         ← módulos PHP (google-auth, cpts, etc.)
└── functions.php     ← entry point PHP
```

> Estrutura HTML do Storefront e armadilhas comuns: [`docs/storefront-structure.md`](docs/storefront-structure.md)

---

## WooCommerce — regras ativas

- **Guest checkout desabilitado** — toda compra exige login (filtros em `functions.php`)
- **Gate de identificação** no checkout — template override em `woocommerce/checkout/form-checkout.php`
- **Gateway de pagamento:** Asaas (sandbox ativo)
- **URLs em português** — slugs traduzidos via opções do WC (ex.: `/minha-conta/`, `/carrinho/`)
- **Campos brasileiros** — plugin *WooCommerce Extra Checkout Fields for Brazil* (`billing_cpf`, `billing_persontype`, `billing_birthdate`)
- **CEP autocomplete** — `assets/js/checkout.js` consulta ViaCEP e preenche endereço

> Plugins obrigatórios: [`docs/plugins.md`](docs/plugins.md)

---

## WP-CLI

O binário está na raiz do WordPress:

```bash
php /var/www/html/eletronicos/wp-cli.phar <comando> --allow-root
```

---

## Superpowers — instalação

Superpowers é um plugin do Claude Code que adiciona skills de desenvolvimento estruturado (brainstorming, planejamento, subagent-driven development, etc.).

```bash
claude plugins install superpowers-dev
```

Após instalar, reinicie o Claude Code. As skills ficam disponíveis automaticamente via ferramenta `Skill`.

### Workflow recomendado para novas features

1. **`/brainstorming`** — define o problema, alinha design, gera spec em `docs/superpowers/specs/`
2. **`/writing-plans`** — converte a spec em plano de tarefas em `docs/superpowers/plans/`
3. **`/subagent-driven-development`** — executa o plano task-a-task com review automático
4. **`/finishing-a-development-branch`** — merge ou PR ao concluir

> Specs e planos anteriores em [`docs/superpowers/`](docs/superpowers/) servem de referência de padrão.

---

## Fluxo de desenvolvimento

```bash
# Compilar SCSS (uma vez)
npm run build

# Modo watch durante desenvolvimento
npm run watch

# Verificar logs do PHP/Apache
tail -f /var/log/apache2/error.log
```

**Branches:** crie uma branch por feature (`feature/nome`). Nunca commite direto em `main`.

---

## O que nunca fazer

- Editar `assets/css/main.css` (gerado)
- Commitar `wp-config.php` ou arquivos com credenciais
- Adicionar comentários inline ao código
- Usar `darken()`/`lighten()` no SCSS
- Usar `@import` no SCSS
- Commitar no `main` sem PR ou merge local revisado
- Escrever commits em português ou com scope `fix(scope):`
