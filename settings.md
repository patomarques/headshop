# Projeto Headshop – Settings

## Boas práticas de desenvolvimento

1. Evite comentários no código. O código deve ser autoexplicativo por si só, utilizando nomes claros para variáveis, funções e estruturas.

2. Commits devem ter no máximo 12 palavras, sempre com prefixo (feature, bug, chore, refactor, etc). Separe commits por funcionalidade ou correção implementada, evitando mudanças grandes e genéricas.

## css / scss

1. Aplique BEM no CSS, siga padrões de escrita de CSS, evite abreviações e escreva as classes sempre em inglês.
2. Nunca use CSS inline no atributo `style` dos componentes; evite ao máximo.
3. Priorize usar classes já existentes do Bootstrap, em vez de escrever mais código CSS.
4. Sempre que implementar elementos novos, cuide também da parte responsiva.

- Tema WordPress em uso (produção/dev atual): **bootscore-child**
- Tema pai: **bootscore**
- Tema legado de referência visual/comportamental: **wp-headshop**

Notas:
- Estilos principais do tema bootscore-child: `wp-content/themes/bootscore-child/assets/css/main.css`.
- SCSS do tema bootscore-child: `wp-content/themes/bootscore-child/assets/scss/`.
- O compilador SCSS em PHP do Bootscore está desativado; a compilação é feita via Sass CLI.
- Comportamento do header (`#masthead`) no bootscore-child foi ajustado para imitar o tema `wp-headshop`.
