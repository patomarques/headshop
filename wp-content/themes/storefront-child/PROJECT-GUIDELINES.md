# Diretrizes do Projeto

## Tema principal

O tema principal é o **Storefront Child** (`wp-content/themes/storefront-child`).
Todas as customizações de frontend devem ser feitas neste tema.

## Padrão de commits

- Todas as mensagens de commit devem ser em inglês.
- Máximo de 12 palavras por mensagem.
- Sempre incluir um prefixo de tarefa.

| Prefixo | Quando usar |
|--------|-------------|
| `feature:` | Nova funcionalidade |
| `fix:` | Correção de bug |
| `chore:` | Manutenção, configurações, scripts |
| `docs:` | Apenas documentação |
| `style:` | Alterações visuais/CSS |
| `refactor:` | Reestruturação de código sem mudança de comportamento |

Exemplos:
- `feature: add product import script`
- `fix: correct .htaccess rewrite rules`
- `chore: update .gitignore rules`

## Documentação de código

- **Não** escreva comentários inline dentro de arquivos de código — nem durante geração automática de código.
- O código deve ser auto explicativo: nomes de variáveis, funções e classes descrevem a intenção.
- Quando documentação for necessária, crie um arquivo dedicado em `docs/`.
- Nomeie o arquivo pelo assunto: `docs/checkout-flow.md`, `docs/hooks.md`, etc.

## WordPress / WooCommerce

- Apenas o tema customizado e arquivos de documentação são versionados.
- Arquivos do core do WordPress e plugins são ignorados pelo `.gitignore`.
- Plugins obrigatórios: WooCommerce, WooCommerce Stripe Gateway, WooCommerce Correios (ver `docs/plugins.md`).
- Para instalar plugins: use o painel WordPress ou WP-CLI.
- Para cadastrar produtos de demonstração: execute `scripts/cadastrar_produtos.sh`.
- Para importar imagens fictícias: execute `wp eval-file wp-content/themes/storefront-child/scripts/importar_imagens_produtos.php` a partir da raiz do WordPress.

## Desenvolvimento

- Use o tema `wp-content/themes/storefront-child` para todas as customizações.
- Para desenvolvimento local, certifique-se de que o `mod_rewrite` do Apache está habilitado e os permalinks estão definidos como "Nome do post".
- O idioma do site é `pt-BR` (definido em `wp-config.php`).
- Compile o SCSS antes de testar: `npm run build` (ou `npm run watch` durante o desenvolvimento).

## Geral

- Não faça commit de arquivos sensíveis (ex.: `wp-config.php` com credenciais reais).
- Revise as alterações antes de fazer push.
- Mantenha este arquivo atualizado com novos padrões à medida que o projeto evolui.
