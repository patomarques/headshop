<?php
add_action('init', function() {
    register_post_type('banner_home', [
        'labels' => [
            'name'               => 'Banners',
            'singular_name'      => 'Banner',
            'add_new'            => 'Adicionar novo',
            'add_new_item'       => 'Adicionar novo banner',
            'edit_item'          => 'Editar banner',
            'new_item'           => 'Novo banner',
            'view_item'          => 'Ver banner',
            'search_items'       => 'Buscar banners',
            'not_found'          => 'Nenhum banner encontrado',
            'not_found_in_trash' => 'Nenhum banner na lixeira',
        ],
        'public'       => false,
        'show_ui'      => true,
        'menu_icon'    => 'dashicons-images-alt2',
        'supports'     => ['title', 'thumbnail'],
        'show_in_rest' => false,
    ]);
});

add_action('add_meta_boxes', function() {
    add_meta_box('banner_home_text', 'Texto do Banner (opcional)', function($post) {
        $value = get_post_meta($post->ID, '_banner_text', true);
        echo '<textarea name="banner_text" class="banner-meta-field" rows="2" placeholder="Texto exibido sobre a imagem">' . esc_textarea($value) . '</textarea>';
        echo '<p class="description">Aparece centralizado sobre a imagem. Deixe vazio para não exibir texto.</p>';
    }, 'banner_home', 'normal');

    add_meta_box('banner_home_link', 'Link do Banner (opcional)', function($post) {
        $value = get_post_meta($post->ID, '_banner_link', true);
        echo '<input type="url" name="banner_link" value="' . esc_attr($value) . '" class="banner-meta-field" placeholder="https://">';
        echo '<p class="description">Torna a imagem clicável e adiciona um botão "Ver mais" no texto (quando houver texto).</p>';
    }, 'banner_home', 'normal');

    add_meta_box('banner_home_mobile', 'Imagem Mobile (opcional)', function($post) {
        $att_id  = (int) get_post_meta($post->ID, '_banner_mobile_id', true);
        $preview = $att_id ? wp_get_attachment_image_url($att_id, 'medium') : '';
        ?>
        <p class="description" style="margin-bottom:10px;">Carregada em telas até 768 px. Se omitida, o WordPress usa um recorte automático da imagem principal (768 × 500 px).</p>
        <input type="hidden" name="banner_mobile_id" id="banner_mobile_id" value="<?php echo esc_attr($att_id ?: ''); ?>">
        <div id="banner-mobile-preview" style="<?php echo $preview ? '' : 'display:none;'; ?>margin-bottom:8px;">
            <img src="<?php echo esc_url($preview ?: ''); ?>" style="max-height:80px;display:block;border:1px solid #ddd;border-radius:3px;">
        </div>
        <button type="button" class="button" id="banner-mobile-upload">Selecionar imagem mobile</button>
        <button type="button" class="button" id="banner-mobile-remove" style="<?php echo $att_id ? '' : 'display:none;'; ?>margin-left:6px;">Remover</button>
        <script>
        jQuery(function ($) {
            var frame;
            $('#banner-mobile-upload').on('click', function (e) {
                e.preventDefault();
                if (frame) { frame.open(); return; }
                frame = wp.media({
                    title: 'Selecionar imagem mobile',
                    button: { text: 'Usar esta imagem' },
                    multiple: false
                });
                frame.on('select', function () {
                    var att = frame.state().get('selection').first().toJSON();
                    $('#banner_mobile_id').val(att.id);
                    var src = (att.sizes && att.sizes.medium) ? att.sizes.medium.url : att.url;
                    $('#banner-mobile-preview').show().find('img').attr('src', src);
                    $('#banner-mobile-remove').show();
                });
                frame.open();
            });
            $('#banner-mobile-remove').on('click', function () {
                $('#banner_mobile_id').val('');
                $('#banner-mobile-preview').hide();
                $(this).hide();
            });
        });
        </script>
        <?php
    }, 'banner_home', 'side');
});

add_action('save_post_banner_home', function($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['banner_text'])) {
        update_post_meta($post_id, '_banner_text', sanitize_text_field($_POST['banner_text']));
    }
    if (isset($_POST['banner_link'])) {
        update_post_meta($post_id, '_banner_link', esc_url_raw($_POST['banner_link']));
    }
    if (isset($_POST['banner_mobile_id'])) {
        $mobile_id = absint($_POST['banner_mobile_id']);
        if ($mobile_id) {
            update_post_meta($post_id, '_banner_mobile_id', $mobile_id);
        } else {
            delete_post_meta($post_id, '_banner_mobile_id');
        }
    }
});
