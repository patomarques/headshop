# 🎨 Estrutura SASS - Storefront Child Theme

## 📋 Visão Geral

Este tema utiliza uma estrutura SASS moderna e organizada seguindo a metodologia **7-1 Pattern**, que divide o código em 7 pastas principais e 1 arquivo principal.

## 📁 Estrutura de Arquivos

```
src/sass/
├── abstracts/          # Variáveis, funções, mixins e placeholders
│   ├── _abstracts.scss
│   ├── functions/
│   │   └── _functions.scss
│   ├── mixins/
│   │   └── _mixins.scss
│   ├── placeholders/
│   │   └── _placeholders.scss
│   └── variables/
│       └── _variables.scss
├── base/               # Estilos base, reset e tipografia
│   └── _base.scss
├── components/         # Componentes reutilizáveis
│   ├── _components.scss
│   ├── _buttons.scss
│   └── _cards.scss
├── layout/             # Layout do site (header, footer, grid)
│   ├── _layout.scss
│   ├── _header.scss
│   └── _footer.scss
├── pages/              # Estilos específicos de páginas
│   ├── _pages.scss
│   └── _woocommerce.scss
├── themes/             # Temas e variações
│   ├── _themes.scss
│   └── _themes.scss
├── vendors/            # Bibliotecas externas (opcional)
│   └── _vendors.scss
└── main.scss           # Arquivo principal
```

## 🎯 Metodologia 7-1 Pattern

### 1. **Abstracts** (`abstracts/`)
Contém todas as ferramentas e helpers do SASS:
- **Variáveis**: Cores, tipografia, espaçamentos, breakpoints
- **Funções**: Funções customizadas para cálculos
- **Mixins**: Mixins reutilizáveis para responsividade, flexbox, etc.
- **Placeholders**: Classes base para extends

### 2. **Base** (`base/`)
Estilos base que se aplicam globalmente:
- Reset e normalize
- Tipografia base
- Estilos de elementos HTML
- Utilitários globais

### 3. **Layout** (`layout/`)
Componentes de layout do site:
- Header e navegação
- Footer
- Grid system
- Sidebar

### 4. **Components** (`components/`)
Componentes reutilizáveis:
- Botões
- Cards
- Formulários
- Modais
- Navegação

### 5. **Pages** (`pages/`)
Estilos específicos de páginas:
- Página inicial
- Páginas de produtos
- Carrinho e checkout
- WooCommerce

### 6. **Themes** (`themes/`)
Temas e variações de design:
- Tema claro (padrão)
- Tema escuro
- Tema alto contraste
- Temas customizados

### 7. **Vendors** (`vendors/`)
Bibliotecas externas e frameworks:
- Bootstrap (se usado)
- Font Awesome
- Outras bibliotecas

## 🚀 Como Usar

### 1. **Instalação de Dependências**
```bash
npm install
```

### 2. **Compilação para Desenvolvimento**
```bash
npm run sass:dev
```

### 3. **Compilação para Produção**
```bash
npm run sass:build
```

### 4. **Desenvolvimento com Watch**
```bash
npm run dev
```

## 🎨 Variáveis Disponíveis

### **Cores**
```scss
$primary-color: #e74c3c;
$secondary-color: #2c3e50;
$success-color: #27ae60;
$warning-color: #f39c12;
$danger-color: #e74c3c;
$info-color: #3498db;
```

### **Tipografia**
```scss
$font-family-primary: 'Open Sans', sans-serif;
$font-family-secondary: 'Montserrat', sans-serif;
$font-size-base: 1rem;
$font-weight-normal: 400;
$font-weight-semibold: 600;
```

### **Espaçamentos**
```scss
$spacing-xs: 0.25rem;   // 4px
$spacing-sm: 0.5rem;    // 8px
$spacing-md: 1rem;      // 16px
$spacing-lg: 1.5rem;    // 24px
$spacing-xl: 2rem;      // 32px
```

### **Breakpoints**
```scss
$breakpoint-sm: 576px;
$breakpoint-md: 768px;
$breakpoint-lg: 992px;
$breakpoint-xl: 1200px;
```

## 🔧 Mixins Disponíveis

### **Responsividade**
```scss
@include respond-to(md) {
  // Estilos para desktop
}

@include respond-below(md) {
  // Estilos para mobile
}
```

### **Flexbox**
```scss
@include flex-center;
@include flex-between;
@include flex-column;
```

### **Botões**
```scss
@include button-base;
@include button-variant($color);
@include button-outline($color);
```

### **Cards**
```scss
@include card-base;
@include card-hover;
```

## 🎯 Placeholders Disponíveis

### **Layout**
```scss
%container
%flex-center
%flex-between
%absolute-center
```

### **Tipografia**
```scss
%heading-base
%text-truncate
%text-clamp-2
%sr-only
```

### **Botões**
```scss
%button-base
%button-primary
%button-secondary
%button-outline
```

### **Cards**
```scss
%card-base
%card-hover
%product-card
```

## 🌐 Temas Disponíveis

### **Tema Claro (Padrão)**
```scss
:root {
  --primary-color: #e74c3c;
  --secondary-color: #2c3e50;
  --bg-color: #ffffff;
}
```

### **Tema Escuro**
```scss
[data-theme="dark"] {
  --primary-color: #e74c3c;
  --bg-color: #212529;
  --text-color: #f8f9fa;
}
```

### **Tema Alto Contraste**
```scss
[data-theme="high-contrast"] {
  --primary-color: #000000;
  --bg-color: #ffffff;
  --text-color: #000000;
}
```

## 📱 Responsividade

### **Breakpoints**
- **XS**: 0px - 575px (Mobile pequeno)
- **SM**: 576px - 767px (Mobile)
- **MD**: 768px - 991px (Tablet)
- **LG**: 992px - 1199px (Desktop pequeno)
- **XL**: 1200px+ (Desktop)

### **Uso**
```scss
@include respond-to(md) {
  // Estilos para tablet e desktop
}

@include respond-below(md) {
  // Estilos para mobile
}
```

## 🎨 Componentes

### **Botões**
```scss
.btn {
  @extend %button-base;
  
  &.btn-primary {
    @extend %button-primary;
  }
}
```

### **Cards**
```scss
.product-card {
  @extend %product-card;
}
```

### **Formulários**
```scss
.form-control {
  @extend %form-control;
}
```

## 🔍 Linting e Formatação

### **Stylelint**
```bash
npm run lint:css
```

### **Prettier**
```bash
npm run format
```

## 📊 Performance

### **Otimizações**
- Compilação minificada para produção
- Source maps para desenvolvimento
- Variáveis CSS para temas dinâmicos
- Placeholders para reduzir CSS duplicado

### **Tamanho do CSS**
- **Desenvolvimento**: ~50KB (não minificado)
- **Produção**: ~15KB (minificado e comprimido)

## 🛠️ Customização

### **Adicionar Nova Variável**
```scss
// src/sass/abstracts/variables/_variables.scss
$custom-color: #ff6b6b;
```

### **Adicionar Novo Mixin**
```scss
// src/sass/abstracts/mixins/_mixins.scss
@mixin custom-mixin {
  // Seu código aqui
}
```

### **Adicionar Novo Componente**
```scss
// src/sass/components/_custom-component.scss
.custom-component {
  // Seus estilos aqui
}
```

## 📚 Recursos Úteis

### **Documentação SASS**
- [Sass Documentation](https://sass-lang.com/documentation)
- [7-1 Pattern](https://sass-guidelin.es/#architecture)

### **Ferramentas**
- [Sass Playground](https://www.sassmeister.com/)
- [Autoprefixer](https://autoprefixer.github.io/)

## 🎯 Boas Práticas

### **Nomenclatura**
- Use kebab-case para classes CSS
- Use camelCase para variáveis SASS
- Use UPPERCASE para constantes

### **Organização**
- Mantenha a estrutura 7-1
- Use imports para organizar arquivos
- Documente mixins e funções complexas

### **Performance**
- Use placeholders para estilos reutilizáveis
- Evite nesting muito profundo
- Use variáveis para valores repetidos

---

**🎨 A estrutura SASS está pronta para desenvolvimento profissional!**

*Para dúvidas ou suporte, consulte a documentação principal do tema.*

