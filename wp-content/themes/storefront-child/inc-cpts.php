<?php
add_action('init', function () {
    register_post_type('brand', [
        'labels'        => [
            'name'          => 'Marcas',
            'singular_name' => 'Marca',
            'add_new_item'  => 'Adicionar marca',
            'edit_item'     => 'Editar marca',
        ],
        'public'        => false,
        'show_ui'       => true,
        'menu_icon'     => 'dashicons-store',
        'supports'      => ['title', 'thumbnail'],
        'show_in_rest'  => false,
        'menu_position' => 25,
    ]);

    register_post_type('payment_logo', [
        'labels'        => [
            'name'          => 'Formas de Pagamento',
            'singular_name' => 'Forma de Pagamento',
            'add_new_item'  => 'Adicionar forma de pagamento',
            'edit_item'     => 'Editar forma de pagamento',
        ],
        'public'        => false,
        'show_ui'       => true,
        'menu_icon'     => 'dashicons-money-alt',
        'supports'      => ['title', 'thumbnail', 'page-attributes', 'custom-fields'],
        'show_in_rest'  => false,
        'menu_position' => 26,
    ]);
});
