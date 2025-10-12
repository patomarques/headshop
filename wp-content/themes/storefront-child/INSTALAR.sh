#!/bin/bash

# =============================================================================
# Script de Instalação do Tema Storefront Child
# =============================================================================
# Este script automatiza a instalação e configuração do tema filho
# 
# Uso: ./INSTALAR.sh
# 
# Autor: Indicativa Headshop
# Versão: 1.0.0
# Data: 10 de outubro de 2024
# =============================================================================

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para exibir mensagens coloridas
print_message() {
    local color=$1
    local message=$2
    echo -e "${color}${message}${NC}"
}

# Função para exibir cabeçalho
print_header() {
    echo "============================================================================="
    echo "🚀 INSTALAÇÃO DO TEMA STOREFRONT CHILD"
    echo "============================================================================="
    echo "📅 Data: $(date)"
    echo "👤 Usuário: $(whoami)"
    echo "📁 Diretório: $(pwd)"
    echo "============================================================================="
    echo ""
}

# Função para verificar dependências
check_dependencies() {
    print_message $BLUE "🔍 Verificando dependências..."
    
    # Verificar se o WordPress está instalado
    if [ ! -f "wp-config.php" ]; then
        print_message $RED "❌ WordPress não encontrado. Execute este script no diretório raiz do WordPress."
        exit 1
    fi
    
    # Verificar se o tema Storefront está instalado
    if [ ! -d "wp-content/themes/storefront" ]; then
        print_message $RED "❌ Tema Storefront não encontrado. Instale o tema Storefront primeiro."
        exit 1
    fi
    
    # Verificar se o WooCommerce está ativo
    if [ ! -d "wp-content/plugins/woocommerce" ]; then
        print_message $YELLOW "⚠️  WooCommerce não encontrado. Instale o plugin WooCommerce para funcionalidade completa."
    fi
    
    print_message $GREEN "✅ Dependências verificadas com sucesso!"
    echo ""
}

# Função para verificar permissões
check_permissions() {
    print_message $BLUE "🔐 Verificando permissões..."
    
    # Verificar permissões do diretório de temas
    if [ ! -w "wp-content/themes" ]; then
        print_message $RED "❌ Sem permissão de escrita no diretório wp-content/themes"
        exit 1
    fi
    
    print_message $GREEN "✅ Permissões verificadas com sucesso!"
    echo ""
}

# Função para fazer backup
create_backup() {
    print_message $BLUE "💾 Criando backup..."
    
    local backup_dir="backups/$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$backup_dir"
    
    # Backup do tema atual (se existir)
    if [ -d "wp-content/themes/storefront-child" ]; then
        cp -r "wp-content/themes/storefront-child" "$backup_dir/"
        print_message $GREEN "✅ Backup criado em: $backup_dir"
    else
        print_message $YELLOW "ℹ️  Nenhum backup necessário (tema não existe)"
    fi
    
    echo ""
}

# Função para instalar o tema
install_theme() {
    print_message $BLUE "📦 Instalando tema..."
    
    # Verificar se o tema já existe
    if [ -d "wp-content/themes/storefront-child" ]; then
        print_message $YELLOW "⚠️  Tema já existe. Atualizando..."
        rm -rf "wp-content/themes/storefront-child"
    fi
    
    # Criar diretório do tema
    mkdir -p "wp-content/themes/storefront-child"
    
    print_message $GREEN "✅ Tema instalado com sucesso!"
    echo ""
}

# Função para configurar permissões
set_permissions() {
    print_message $BLUE "🔐 Configurando permissões..."
    
    # Definir permissões corretas
    chmod -R 755 "wp-content/themes/storefront-child"
    chmod 644 "wp-content/themes/storefront-child"/*.php
    chmod 644 "wp-content/themes/storefront-child"/*.css
    chmod 644 "wp-content/themes/storefront-child"/*.js
    chmod 644 "wp-content/themes/storefront-child"/*.po
    chmod 644 "wp-content/themes/storefront-child"/*.md
    
    print_message $GREEN "✅ Permissões configuradas com sucesso!"
    echo ""
}

# Função para verificar instalação
verify_installation() {
    print_message $BLUE "🔍 Verificando instalação..."
    
    local theme_dir="wp-content/themes/storefront-child"
    local required_files=(
        "style.css"
        "functions.php"
        "woocommerce.php"
        "VERIFICACAO.php"
        "assets/css/woocommerce.css"
        "assets/js/child-theme.js"
        "languages/storefront-child-pt_BR.po"
    )
    
    local missing_files=()
    
    for file in "${required_files[@]}"; do
        if [ ! -f "$theme_dir/$file" ]; then
            missing_files+=("$file")
        fi
    done
    
    if [ ${#missing_files[@]} -eq 0 ]; then
        print_message $GREEN "✅ Todos os arquivos estão presentes!"
    else
        print_message $RED "❌ Arquivos faltando:"
        for file in "${missing_files[@]}"; do
            echo "   - $file"
        done
        exit 1
    fi
    
    echo ""
}

# Função para exibir informações do tema
show_theme_info() {
    print_message $BLUE "📋 Informações do tema:"
    
    local theme_dir="wp-content/themes/storefront-child"
    
    if [ -f "$theme_dir/style.css" ]; then
        echo "   Nome: $(grep 'Theme Name:' "$theme_dir/style.css" | cut -d: -f2 | xargs)"
        echo "   Versão: $(grep 'Version:' "$theme_dir/style.css" | cut -d: -f2 | xargs)"
        echo "   Autor: $(grep 'Author:' "$theme_dir/style.css" | cut -d: -f2 | xargs)"
        echo "   Template: $(grep 'Template:' "$theme_dir/style.css" | cut -d: -f2 | xargs)"
    fi
    
    echo ""
}

# Função para exibir próximos passos
show_next_steps() {
    print_message $GREEN "🎉 Instalação concluída com sucesso!"
    echo ""
    print_message $BLUE "📋 Próximos passos:"
    echo "   1. Acesse o admin do WordPress"
    echo "   2. Vá em Aparência > Temas"
    echo "   3. Ative o tema 'Storefront Child'"
    echo "   4. Configure as personalizações em Aparência > Personalizar"
    echo "   5. Configure o WooCommerce se necessário"
    echo ""
    print_message $YELLOW "📚 Documentação disponível:"
    echo "   - README.md: Documentação principal"
    echo "   - ATIVACAO.md: Guia de ativação"
    echo "   - CONFIGURACAO.md: Configurações avançadas"
    echo "   - VERIFICACAO.php: Sistema de verificação"
    echo ""
}

# Função principal
main() {
    print_header
    check_dependencies
    check_permissions
    create_backup
    install_theme
    set_permissions
    verify_installation
    show_theme_info
    show_next_steps
    
    print_message $GREEN "🚀 Tema Storefront Child instalado com sucesso!"
    print_message $BLUE "📞 Para suporte, consulte a documentação incluída."
}

# Executar função principal
main "$@"
