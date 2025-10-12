# Storefront Child Theme

Um tema filho personalizado para o Storefront, desenvolvido para a Indicativa Headshop.

## 📋 Descrição

Este tema filho estende o tema Storefront com personalizações customizadas, melhorias de performance e funcionalidades adicionais para e-commerce.

## ✨ Funcionalidades

### 🎨 Personalizações Visuais
- **Cores customizadas** - Sistema de cores personalizável via Customizer
- **Estilos melhorados** - Design moderno com transições suaves
- **Responsividade** - Otimizado para todos os dispositivos
- **Efeitos visuais** - Hover effects e animações

### 🛒 WooCommerce
- **Produtos destacados** - Melhor apresentação dos produtos
- **Carrinho otimizado** - Interface melhorada para o carrinho
- **Checkout personalizado** - Formulários de checkout aprimorados
- **Breadcrumbs** - Navegação melhorada

### ⚡ Performance
- **Lazy loading** - Carregamento otimizado de imagens
- **Minificação** - Código otimizado
- **Cache friendly** - Compatível com plugins de cache
- **SEO otimizado** - Meta tags e estrutura melhoradas

### 🔧 Funcionalidades Técnicas
- **Widgets customizados** - Áreas de widget adicionais
- **Menu responsivo** - Navegação mobile otimizada
- **AJAX** - Funcionalidades dinâmicas
- **Acessibilidade** - Suporte a screen readers

## 📁 Estrutura de Arquivos

```
storefront-child/
├── style.css                 # Estilos do tema filho
├── functions.php             # Funções PHP customizadas
├── README.md                 # Documentação
├── assets/
│   └── js/
│       └── child-theme.js    # JavaScript customizado
└── languages/
    └── storefront-child-pt_BR.po  # Tradução em português
```

## 🚀 Instalação

### 1. Upload do Tema
1. Faça upload da pasta `storefront-child` para `/wp-content/themes/`
2. Ou use o instalador de temas do WordPress

### 2. Ativação
1. Acesse **Aparência > Temas**
2. Ative o tema "Storefront Child"
3. Certifique-se de que o tema Storefront está instalado

### 3. Configuração
1. Acesse **Aparência > Personalizar**
2. Configure as cores customizadas
3. Personalize os widgets e menus

## ⚙️ Configurações

### Cores Customizadas
- **Cor Primária**: Cor principal do site (botões, links)
- **Cor Secundária**: Cor de destaque (cabeçalho, títulos)

### Widgets Disponíveis
- **Área Customizada do Rodapé**: Widgets para o rodapé
- **Sidebar de Produtos**: Widgets específicos para páginas de produtos

### Menus
- **Menu Principal**: Navegação principal do site
- **Menu Mobile**: Navegação otimizada para dispositivos móveis

## 🎯 Personalizações Incluídas

### Estilos CSS
```css
/* Cores personalizadas */
:root {
    --primary-color: #e74c3c;
    --secondary-color: #2c3e50;
}

/* Botões com efeitos */
.woocommerce a.button {
    border-radius: 25px;
    transition: all 0.3s ease;
}

/* Produtos com hover */
.woocommerce ul.products li.product:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
}
```

### JavaScript
- Scroll suave para âncoras
- Efeitos de hover nos produtos
- Atualizações AJAX do carrinho
- Menu mobile responsivo
- Lazy loading de imagens

## 🔧 Desenvolvimento

### Estrutura do Functions.php
```php
// Configuração do tema
function storefront_child_setup() {
    load_child_theme_textdomain('storefront-child', get_stylesheet_directory() . '/languages');
    add_theme_support('woocommerce');
}

// Enfileirar scripts e estilos
function storefront_child_scripts() {
    wp_enqueue_style('storefront-child-style', get_stylesheet_directory_uri() . '/style.css');
    wp_enqueue_script('storefront-child-script', get_stylesheet_directory_uri() . '/assets/js/child-theme.js');
}
```

### Hooks e Filtros
- `storefront_child_setup` - Configuração inicial
- `storefront_child_scripts` - Enfileiramento de assets
- `storefront_child_body_classes` - Classes do body
- `storefront_child_customize_register` - Customizer

## 🌐 Tradução

O tema inclui suporte completo ao português brasileiro:

- **Text Domain**: `storefront-child`
- **Arquivo de tradução**: `languages/storefront-child-pt_BR.po`
- **Strings traduzidas**: Todas as strings customizadas

### Ativar Tradução
1. Configure o WordPress para português brasileiro
2. As traduções serão carregadas automaticamente

## 📱 Responsividade

### Breakpoints
- **Mobile**: até 768px
- **Tablet**: 769px - 1024px
- **Desktop**: 1025px+

### Otimizações Mobile
- Menu hambúrguer responsivo
- Imagens otimizadas
- Touch-friendly buttons
- Swipe gestures

## 🔍 SEO

### Otimizações Incluídas
- Meta descriptions automáticas
- Structured data
- Open Graph tags
- Schema.org markup
- Sitemap friendly

## 🛡️ Segurança

### Medidas Implementadas
- Sanitização de inputs
- Escape de outputs
- Nonce verification
- Capability checks
- XSS protection

## 📊 Performance

### Métricas Otimizadas
- **Lazy loading** de imagens
- **Minificação** de CSS/JS
- **Compressão** de assets
- **Cache headers**
- **CDN ready**

## 🐛 Troubleshooting

### Problemas Comuns

#### Tema não aparece
- Verifique se o Storefront está instalado
- Confirme que a pasta está em `/wp-content/themes/`

#### Estilos não carregam
- Limpe o cache do site
- Verifique se o Storefront está ativo

#### JavaScript não funciona
- Verifique se o jQuery está carregado
- Confirme que não há conflitos com outros plugins

## 📞 Suporte

### Documentação
- [WordPress Codex](https://codex.wordpress.org/)
- [WooCommerce Docs](https://docs.woocommerce.com/)
- [Storefront Theme](https://woocommerce.com/storefront/)

### Contato
- **Desenvolvedor**: Indicativa Headshop
- **Versão**: 1.0.0
- **Compatibilidade**: WordPress 5.0+, WooCommerce 3.0+

## 📄 Licença

Este tema é licenciado sob GPL v2 ou posterior.

## 🔄 Changelog

### Versão 1.0.0
- Lançamento inicial
- Personalizações básicas do Storefront
- Suporte ao WooCommerce
- Tradução para português brasileiro
- Otimizações de performance
- Funcionalidades JavaScript customizadas

---

**Desenvolvido com ❤️ para a Indicativa Headshop**
