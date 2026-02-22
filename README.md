# Headshop (Indicativa)

Loja virtual da Indicativa Headshop (Caruaru/PE), construída em WordPress + WooCommerce com tema filho baseado em Bootscore.

## Requisitos do Site

- CMS: WordPress
- E-commerce: WooCommerce
- Tema principal: `wp-content/themes/bootscore-child`
- Tema pai obrigatório: `wp-content/themes/bootscore`
- Tema legado de referência visual/comportamental: `wp-content/themes/wp-headshop`

## Requisitos Funcionais Implementados

- Header customizado (desktop e mobile/offcanvas).
- Busca com overlay em todas as páginas.
- Carrinho no header com dropdown e remoção de itens via AJAX.
- Seção de banners na home via CPT `banner`.
- Seção de categorias da home com ordenação configurável no admin.
- Seção de produtos recentes na home.
- Footer customizado com links institucionais e redes sociais.

## Desenvolvimento

- O compilador SCSS interno do Bootscore está desativado.
- A compilação deve ser feita via Sass CLI para gerar `assets/css/main.css`.

Exemplo de compilação manual (dentro de `wp-content/themes/bootscore-child`):

```bash
sass --no-source-map --style=expanded assets/scss/main.scss assets/css/main.css
```

Exemplo de watch:

```bash
sass --watch assets/scss/main.scss:assets/css/main.css
```

## Padrões de Engenharia

- Aplicar boas práticas de desenvolvimento e engenharia de software.
- Priorizar DRY, reuso, consistência e manutenção simples.
- Escrever código claro, legível e testável.

## Documento de Referência Interna

- Diretrizes de desenvolvimento: `docs/settings.md`
