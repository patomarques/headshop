#!/bin/bash
# Script para atualizar todos os preços dos produtos para valores em Real (R$)

WP_ROOT="$(cd "$(dirname "$0")/../../../.." && pwd)"
WP="php $WP_ROOT/wp-cli.phar --path=$WP_ROOT"

IDS=$($WP post list --post_type=product --format=ids)

for ID in $IDS
  do
    PRECO=$(shuf -i 5-100 -n 1)
    $WP post meta update $ID _price "$PRECO"
    $WP post meta update $ID _regular_price "$PRECO"
  done

echo "Todos os preços dos produtos foram atualizados para valores em Real (R$)."
