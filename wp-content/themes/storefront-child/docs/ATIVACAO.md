# 🚀 Guia de Ativação do Tema Storefront Child

## 📋 Pré-requisitos

Antes de ativar o tema filho, certifique-se de que:

- ✅ **WordPress** está instalado (versão 5.0 ou superior)
- ✅ **WooCommerce** está instalado e ativo
- ✅ **Tema Storefront** está instalado (tema pai)
- ✅ **PHP** versão 7.4 ou superior

## 🔧 Passo a Passo para Ativação

### 1. Verificar Tema Pai
1. Acesse **Aparência > Temas** no admin do WordPress
2. Confirme que o tema **Storefront** está instalado
3. Se não estiver, instale o Storefront primeiro

### 2. Ativar Tema Filho
1. Na lista de temas, localize **"Storefront Child"**
2. Clique em **"Ativar"**
3. O tema será ativado automaticamente

### 3. Configurar Personalizações
1. Acesse **Aparência > Personalizar**
2. Configure as opções disponíveis:
   - **Cores Customizadas**
   - **Logo do Site**
   - **Menus**
   - **Widgets**

## ⚙️ Configurações Recomendadas

### Cores do Tema
- **Cor Primária**: `#e74c3c` (Vermelho)
- **Cor Secundária**: `#2c3e50` (Azul escuro)

### Menus
Configure os seguintes menus:
- **Menu Principal** - Navegação principal
- **Menu do Rodapé** - Links do rodapé
- **Menu Mobile** - Navegação mobile

### Widgets
Ative os widgets customizados:
- **Área Customizada do Rodapé**
- **Sidebar de Produtos**

## 🛒 Configurações do WooCommerce

### Produtos por Página
- **Loja**: 12 produtos
- **Colunas**: 4 colunas (desktop)

### Funcionalidades Ativadas
- ✅ Galeria de produtos com zoom
- ✅ Lightbox para imagens
- ✅ Slider de produtos
- ✅ Breadcrumbs customizados
- ✅ Mensagens personalizadas

## 🌐 Configuração de Idioma

### Ativar Português Brasileiro
1. Acesse **Configurações > Geral**
2. Altere **Idioma do site** para "Português do Brasil"
3. Salve as alterações

### Ou via wp-config.php
```php
define('WPLANG', 'pt_BR');
```

## 📱 Testes Recomendados

### Funcionalidades Básicas
- [ ] Página inicial carrega corretamente
- [ ] Menu de navegação funciona
- [ ] Busca de produtos funciona
- [ ] Páginas de produtos exibem corretamente

### WooCommerce
- [ ] Loja exibe produtos
- [ ] Carrinho funciona
- [ ] Checkout processa pedidos
- [ ] Minha conta funciona
- [ ] Emails são enviados

### Responsividade
- [ ] Site funciona em desktop
- [ ] Site funciona em tablet
- [ ] Site funciona em mobile
- [ ] Menu mobile funciona

## 🔍 Verificação de Funcionamento

### Verificar se o Tema Filho está Ativo
1. Acesse **Aparência > Temas**
2. Confirme que "Storefront Child" está marcado como **Ativo**
3. Verifique se "Storefront" aparece como **Tema Pai**

### Verificar Estilos
1. Acesse o site no frontend
2. Verifique se as cores customizadas estão aplicadas
3. Confirme se os estilos do tema filho estão carregando

### Verificar JavaScript
1. Abra o console do navegador (F12)
2. Verifique se não há erros JavaScript
3. Teste funcionalidades interativas

## 🐛 Solução de Problemas

### Tema não aparece na lista
**Problema**: Storefront Child não aparece em Aparência > Temas
**Solução**: 
- Verifique se a pasta está em `/wp-content/themes/storefront-child/`
- Confirme se o arquivo `style.css` existe
- Verifique as permissões da pasta

### Estilos não carregam
**Problema**: Site não tem as cores/estilos customizados
**Solução**:
- Limpe o cache do site
- Verifique se o Storefront está ativo
- Confirme se não há conflitos com plugins

### WooCommerce não funciona
**Problema**: Páginas de produtos/carrinho não funcionam
**Solução**:
- Verifique se o WooCommerce está ativo
- Confirme se as páginas do WooCommerce existem
- Verifique as configurações do WooCommerce

### JavaScript não funciona
**Problema**: Funcionalidades interativas não funcionam
**Solução**:
- Verifique se o jQuery está carregado
- Confirme se não há conflitos com outros plugins
- Verifique o console do navegador para erros

## 📞 Suporte

### Recursos Úteis
- [Documentação do WordPress](https://wordpress.org/support/)
- [Documentação do WooCommerce](https://docs.woocommerce.com/)
- [Documentação do Storefront](https://woocommerce.com/storefront/)

### Informações do Tema
- **Nome**: Storefront Child
- **Versão**: 1.0.0
- **Tema Pai**: Storefront
- **Desenvolvedor**: Indicativa Headshop
- **Compatibilidade**: WordPress 5.0+, WooCommerce 3.0+

## ✅ Checklist de Ativação

- [ ] Tema Storefront instalado
- [ ] WooCommerce ativo
- [ ] Tema Storefront Child ativado
- [ ] Cores customizadas configuradas
- [ ] Menus configurados
- [ ] Widgets configurados
- [ ] Idioma configurado para pt_BR
- [ ] Testes básicos realizados
- [ ] WooCommerce testado
- [ ] Responsividade verificada

---

**🎉 Parabéns! Seu tema Storefront Child está ativo e funcionando!**

Para dúvidas ou suporte, consulte a documentação completa no arquivo `README.md`.
