# Storefront Child Eletronicos

Tema filho do [Storefront](https://woocommerce.com/storefront/) customizado para uma loja de componentes eletrônicos.

- **Autor:** Dayvson Marques
- **Versão:** 1.0.0
- **Tema pai:** Storefront
- **Stack:** WordPress + WooCommerce, Bootstrap 5.3, Dart Sass, Vanilla JS

---

## Requisitos

- WordPress 6.x
- WooCommerce 8.x
- Tema pai Storefront instalado e ativo
- Node.js (para compilação do SCSS)

---

## Instalação e configuração

Instale as dependências e compile o SCSS:

```bash
cd wp-content/themes/storefront-child
npm install
npm run build
```

Para recompilar automaticamente durante o desenvolvimento:

```bash
npm run watch
```

> `assets/css/main.css` é o arquivo compilado — nunca edite diretamente. Ele é ignorado pelo `.gitignore`.

---

## Estrutura de arquivos

```
storefront-child/
├── assets/
│   ├── css/              # Saída compilada (ignorada pelo git)
│   ├── img/              # Imagens do tema
│   ├── js/
│   │   └── main.js       # Header sticky + slider de promoções
│   └── scss/
│       ├── main.scss              # Ponto de entrada
│       ├── abstracts/             # Variáveis, mixins
│       ├── base/                  # Reset, tipografia, botões
│       ├── layout/                # Header, footer
│       ├── components/            # Cards, botões
│       └── pages/                 # _home.scss
├── docs/
│   ├── storefront-structure.md    # Estrutura HTML e regras de enqueue
│   ├── scss-architecture.md       # Convenções SCSS e build
│   └── plugins.md                 # Plugins obrigatórios
├── scripts/
│   ├── cadastrar_produtos.sh          # Cadastra 50 produtos de exemplo
│   ├── atualizar_precos.sh            # Atualiza preços aleatoriamente
│   ├── atualizar_categorias_produtos.sh # Reorganiza categorias
│   └── importar_imagens_produtos.php  # Importa imagens fictícias via WP-CLI
├── functions.php
├── header.php
├── footer.php
├── homepage.php              # Template: Home Custom Eletronicos
├── front-page.php
├── inc-banner-cpt.php
├── package.json
├── PROJECT-GUIDELINES.md
└── style.css                 # Declaração do tema
```

---

## Template da homepage

O template `homepage.php` (`Home Custom Eletronicos`) renderiza três seções:

| Seção | Fonte de dados |
|---|---|
| Categorias | Top 6 categorias WooCommerce por contagem de produtos |
| Promoções | Até 10 produtos com preço promocional ativo, slider infinito e arrastável |
| Produtos em destaque | 8 produtos mais recentes em grid Bootstrap |

---

## JavaScript

`assets/js/main.js` possui dois recursos, ambos em Vanilla JS (sem jQuery):

- **Header sticky** — usa `IntersectionObserver` no elemento `#after-banner-sentinel` (injetado após o banner em `header.php`) para alternar a classe `.is-sticky` no `#masthead`
- **Slider infinito de promoções** — clona os filhos de `#promo-track`, faz loop via `requestAnimationFrame`, suporta arrastar com mouse e toque

---

## Variáveis SCSS principais

| Token | Valor |
|---|---|
| `$color-primary` | `#0d6efd` |
| `$color-danger` | `#dc3545` |
| `$font-family-base` | Segoe UI / Verdana / sans-serif |
| `$bp-md` | `768px` |
| `$bp-lg` | `992px` |

Lista completa em [assets/scss/abstracts/_variables.scss](assets/scss/abstracts/_variables.scss).

---

## Scripts utilitários

Todos os scripts ficam em `scripts/` e devem ser executados a partir da raiz do WordPress ou diretamente (o caminho do WP-CLI é detectado automaticamente).

```bash
# Cadastrar produtos de demonstração
bash wp-content/themes/storefront-child/scripts/cadastrar_produtos.sh

# Atualizar preços aleatoriamente
bash wp-content/themes/storefront-child/scripts/atualizar_precos.sh

# Importar imagens fictícias (via WP-CLI eval-file)
php wp-cli.phar eval-file wp-content/themes/storefront-child/scripts/importar_imagens_produtos.php
```

---

## Documentação

- [Estrutura do Storefront e regras de enqueue](docs/storefront-structure.md)
- [Arquitetura SCSS e configuração de build](docs/scss-architecture.md)
- [Plugins obrigatórios](docs/plugins.md)
- [Diretrizes do projeto](PROJECT-GUIDELINES.md)
