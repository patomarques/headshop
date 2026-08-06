#!/bin/bash
# Script para atualizar categorias dos produtos WooCommerce (exemplo: pai > filho)

WP_ROOT="$(cd "$(dirname "$0")/../../../.." && pwd)"
WP="php $WP_ROOT/wp-cli.phar --path=$WP_ROOT"

ELETRONICOS_ID=$($WP term create product_cat "Eletrônicos" --porcelain)

RESISTORES_ID=$($WP term create product_cat "Resistores" --parent=$ELETRONICOS_ID --porcelain)
CAPACITORES_ID=$($WP term create product_cat "Capacitores" --parent=$ELETRONICOS_ID --porcelain)
TRANSISTORES_ID=$($WP term create product_cat "Transistores" --parent=$ELETRONICOS_ID --porcelain)
FERRAMENTAS_ID=$($WP term create product_cat "Ferramentas" --parent=$ELETRONICOS_ID --porcelain)

if [ -z "$RESISTORES_ID" ]; then RESISTORES_ID=$($WP term list product_cat --name="Resistores" --field=term_id); fi
if [ -z "$CAPACITORES_ID" ]; then CAPACITORES_ID=$($WP term list product_cat --name="Capacitores" --field=term_id); fi
if [ -z "$TRANSISTORES_ID" ]; then TRANSISTORES_ID=$($WP term list product_cat --name="Transistores" --field=term_id); fi
if [ -z "$FERRAMENTAS_ID" ]; then FERRAMENTAS_ID=$($WP term list product_cat --name="Ferramentas" --field=term_id); fi

IDS=($($WP post list --post_type=product --format=ids))
for i in "${!IDS[@]}"; do
  ID=${IDS[$i]}
  case $((i % 4)) in
    0) CAT=$RESISTORES_ID;;
    1) CAT=$CAPACITORES_ID;;
    2) CAT=$TRANSISTORES_ID;;
    3) CAT=$FERRAMENTAS_ID;;
  esac
  $WP post term set $ID product_cat $CAT
  echo "Produto $ID atualizado para categoria $CAT"
done
