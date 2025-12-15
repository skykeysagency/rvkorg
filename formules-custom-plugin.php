<?php
/*
Plugin Name: Formules Custom Plugin
Description: Plugin WordPress personnalisé pour gérer les Formules avec une pricing list, une image bannière et des relations avec "Nos plats", "Nos buffets" et "Nos options".  
Version: 1.0  
Author: Votre Nom  
*/

// Sécurité
if (!defined('ABSPATH')) {
    exit;
}

/*--------------------------------------------------------------
  ENREGISTREMENT DES CPT
--------------------------------------------------------------*/
function fcp_register_post_types()
{
    // CPT Formule
    $labels_formule = array(
        'name' => 'Formules',
        'singular_name' => 'Formule',
        'add_new' => 'Ajouter une formule',
        'add_new_item' => 'Ajouter une nouvelle formule',
        'edit_item' => 'Modifier la formule',
        'new_item' => 'Nouvelle formule',
        'view_item' => 'Voir la formule',
        'search_items' => 'Rechercher une formule',
        'not_found' => 'Aucune formule trouvée',
        'not_found_in_trash' => 'Aucune formule dans la corbeille',
        'menu_name' => 'Formules'
    );
    register_post_type('formule', array(
        'labels' => $labels_formule,
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor'),
        'rewrite' => array('slug' => 'formules'),
        'show_in_menu' => 'rivka-traiteur',
    ));

    // CPT Nos plats
    $labels_plats = array(
        'name' => 'Nos plats',
        'singular_name' => 'Plat',
        'add_new' => 'Ajouter un plat',
        'add_new_item' => 'Ajouter un nouveau plat',
        'edit_item' => 'Modifier le plat',
        'new_item' => 'Nouveau plat',
        'view_item' => 'Voir le plat',
        'search_items' => 'Rechercher un plat',
        'not_found' => 'Aucun plat trouvé',
        'not_found_in_trash' => 'Aucun plat dans la corbeille',
        'menu_name' => 'Plats'
    );
    register_post_type('nos_plats', array(
        'labels' => $labels_plats,
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor'),
        'rewrite' => array('slug' => 'nos-plats'),
        'show_in_menu' => 'rivka-traiteur',
    ));

    // CPT Nos buffets
    $labels_buffets = array(
        'name' => 'Nos buffets',
        'singular_name' => 'Buffet',
        'add_new' => 'Ajouter un buffet',
        'add_new_item' => 'Ajouter un nouveau buffet',
        'edit_item' => 'Modifier le buffet',
        'new_item' => 'Nouveau buffet',
        'view_item' => 'Voir le buffet',
        'search_items' => 'Rechercher un buffet',
        'not_found' => 'Aucun buffet trouvé',
        'not_found_in_trash' => 'Aucun buffet dans la corbeille',
        'menu_name' => 'Buffets'
    );
    register_post_type('nos_buffets', array(
        'labels' => $labels_buffets,
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor'),
        'rewrite' => array('slug' => 'nos-buffets'),
        'show_in_menu' => 'rivka-traiteur',
    ));

    // CPT Nos options
    $labels_options = array(
        'name' => 'Nos options',
        'singular_name' => 'Option',
        'add_new' => 'Ajouter une option',
        'add_new_item' => 'Ajouter une nouvelle option',
        'edit_item' => 'Modifier l\'option',
        'new_item' => 'Nouvelle option',
        'view_item' => 'Voir l\'option',
        'search_items' => 'Rechercher une option',
        'not_found' => 'Aucune option trouvée',
        'not_found_in_trash' => 'Aucune option dans la corbeille',
        'menu_name' => 'Options'
    );
    register_post_type('nos_options', array(
        'labels' => $labels_options,
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor'),
        'rewrite' => array('slug' => 'nos-options'),
        'show_in_menu' => 'rivka-traiteur',
    ));
}

// Ajout du menu parent "Rivka Organisation"
function fcp_add_admin_menu()
{
    add_menu_page(
        'Rivka Organisation',
        'Rivka Organisation',
        'manage_options',
        'rivka-traiteur',
        function () {
            echo '<div class="wrap"><h1>Rivka Organisation</h1><p>Bienvenue dans l\'administration de Rivka Organisation.</p></div>';
        },
        'dashicons-store',
        20
    );
}
add_action('admin_menu', 'fcp_add_admin_menu');

add_action('init', 'fcp_register_post_types');

/*--------------------------------------------------------------
  META BOXES POUR LE CPT "FORMULE"
--------------------------------------------------------------*/
function fcp_add_formule_metaboxes()
{
    add_meta_box('fcp_formule_banner', 'Bannière Formule', 'fcp_render_banner_metabox', 'formule', 'normal', 'default');
    add_meta_box('fcp_formule_pricing_list', 'Pricing List', 'fcp_render_pricing_metabox', 'formule', 'normal', 'default');
    add_meta_box('fcp_formule_related', 'Contenu associé', 'fcp_render_related_metabox', 'formule', 'normal', 'default');

}
add_action('add_meta_boxes', 'fcp_add_formule_metaboxes');

function fcp_render_banner_metabox($post)
{
    wp_nonce_field('fcp_save_banner', 'fcp_banner_nonce');
    $banner_id = get_post_meta($post->ID, '_fcp_banner_image', true);
    $banner_url = $banner_id ? wp_get_attachment_url($banner_id) : '';
    ?>
    <div id="fcp-banner-container">
        <img id="fcp-banner-preview" src="<?php echo esc_url($banner_url); ?>"
            style="max-width:100%; <?php echo $banner_url ? '' : 'display:none;'; ?>" />
        <input type="hidden" id="fcp_banner_image" name="fcp_banner_image" value="<?php echo esc_attr($banner_id); ?>">
        <br>
        <button type="button" class="button" id="fcp-banner-upload">Uploader l'image de Bannière</button>
        <button type="button" class="button" id="fcp-banner-remove" <?php echo $banner_url ? '' : 'style="display:none;"'; ?>>Supprimer l'image</button>
    </div>
    <script>
        jQuery(document).ready(function ($) {
            var frame;
            $('#fcp-banner-upload').on('click', function (e) {
                e.preventDefault();
                if (frame) {
                    frame.open();
                    return;
                }
                frame = wp.media({
                    title: 'Choisir une image de bannière',
                    button: { text: 'Utiliser cette image' },
                    multiple: false
                });
                frame.on('select', function () {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#fcp_banner_image').val(attachment.id);
                    $('#fcp-banner-preview').attr('src', attachment.url).show();
                    $('#fcp-banner-remove').show();
                });
                frame.open();
            });
            $('#fcp-banner-remove').on('click', function () {
                $('#fcp_banner_image').val('');
                $('#fcp-banner-preview').attr('src', '').hide();
                $(this).hide();
            });
        });
    </script>
    <?php
}

function fcp_render_pricing_metabox($post)
{
    wp_nonce_field('fcp_save_pricing', 'fcp_pricing_nonce');
    $pricing_list = get_post_meta($post->ID, '_fcp_pricing_list', true);
    if (!is_array($pricing_list)) {
        $pricing_list = array();
    }
    ?>
    <div id="fcp_pricing_list_container">
        <?php if (!empty($pricing_list)): ?>
            <?php foreach ($pricing_list as $index => $item): ?>
                <div class="fcp_pricing_item">
                    <label>Titre :</label>
                    <input type="text" name="fcp_pricing[<?php echo esc_attr($index); ?>][title]"
                        value="<?php echo esc_attr($item['title']); ?>" style="width:100%;">
                    <label>Contenu :</label>
                    <?php
                    $editor_id = 'fcp_pricing_' . $index;
                    $editor_content = isset($item['content']) ? $item['content'] : '';
                    $settings = array(
                        'textarea_name' => 'fcp_pricing[' . esc_attr($index) . '][content]',
                        'textarea_rows' => 10,
                        'media_buttons' => true,
                        'tinymce' => true,
                        'quicktags' => true,
                    );
                    wp_editor($editor_content, $editor_id, $settings);
                    ?>
                    <label style="margin-top: 15px;">Prix (€) :</label>
                    <input type="number" step="0.01" name="fcp_pricing[<?php echo esc_attr($index); ?>][price]"
                        value="<?php echo esc_attr($item['price']); ?>">
                    <button type="button" class="button fcp_remove_pricing_item" style="margin-top: 10px;">Supprimer</button>
                    <hr>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button type="button" id="fcp_add_pricing_item" class="button">Ajouter un élément</button>
    <template id="fcp_pricing_template">
        <div class="fcp_pricing_item">
            <label>Titre :</label>
            <input type="text" name="fcp_pricing[{{index}}][title]" value="" style="width:100%;">
            <label>Contenu :</label>
            <div class="wp-editor-container">
                <textarea name="fcp_pricing[{{index}}][content]" style="width:100%;" class="wp-editor-area"></textarea>
            </div>
            <label style="margin-top: 15px;">Prix (€) :</label>
            <input type="number" step="0.01" name="fcp_pricing[{{index}}][price]" value="">
            <button type="button" class="button fcp_remove_pricing_item" style="margin-top: 10px;">Supprimer</button>
            <hr>
        </div>
    </template>
    <script>
        jQuery(document).ready(function ($) {
            var index = <?php echo count($pricing_list); ?>;
            $('#fcp_add_pricing_item').on('click', function () {
                var template = $('#fcp_pricing_template').html();
                template = template.replace(/{{index}}/g, index);
                $('#fcp_pricing_list_container').append(template);

                // Initialiser l'éditeur pour le nouveau champ
                setTimeout(function () {
                    wp.editor.initialize('fcp_pricing_' + index, {
                        tinymce: true,
                        quicktags: true,
                        mediaButtons: true
                    });
                }, 100);

                index++;
            });
            $('#fcp_pricing_list_container').on('click', '.fcp_remove_pricing_item', function () {
                var item = $(this).closest('.fcp_pricing_item');
                var editorId = item.find('.wp-editor-area').attr('id');
                if (editorId) {
                    wp.editor.remove(editorId);
                }
                item.remove();
            });
        });
    </script>
    <style>
        .fcp_pricing_item {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .fcp_pricing_item label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }

        .fcp_pricing_item .wp-editor-container {
            margin-bottom: 15px;
        }
    </style>
    <?php
}

function fcp_render_related_metabox($post)
{
    wp_nonce_field('fcp_save_related', 'fcp_related_nonce');

    $sections = array(
        'plats' => array('label' => 'Nos plats', 'cpt' => 'nos_plats'),
        'buffets' => array('label' => 'Nos buffets', 'cpt' => 'nos_buffets'),
        'options' => array('label' => 'Nos options', 'cpt' => 'nos_options')
    );

    foreach ($sections as $key => $data) {
        $show = get_post_meta($post->ID, '_fcp_show_' . $key, true);
        $selected = get_post_meta($post->ID, '_fcp_selected_' . $key, true);
        ?>
        <fieldset style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
            <legend><?php echo esc_html($data['label']); ?></legend>
            <label>
                <input type="checkbox" name="fcp_show_<?php echo esc_attr($key); ?>" value="1" <?php checked($show, '1'); ?>>
                Activer l'affichage de cette section
            </label>
            <br><br>
            <label>Sélectionnez un élément :</label>
            <select name="fcp_selected_<?php echo esc_attr($key); ?>">
                <option value="">-- Aucun --</option>
                <?php
                $posts = get_posts(array(
                    'post_type' => $data['cpt'],
                    'posts_per_page' => -1,
                ));
                if (!empty($posts)) {
                    foreach ($posts as $p) {
                        ?>
                        <option value="<?php echo esc_attr($p->ID); ?>" <?php selected($selected, $p->ID); ?>>
                            <?php echo esc_html($p->post_title); ?>
                        </option>
                        <?php
                    }
                }
                ?>
            </select>
            
            <div style="margin-top: 15px; border-top: 1px dashed #ccc; padding-top: 10px;">
                <strong>Bouton Popup Personnalisé</strong><br>
                <?php
                $custom_btn_active = get_post_meta($post->ID, '_fcp_custom_btn_active_' . $key, true);
                $custom_btn_label = get_post_meta($post->ID, '_fcp_custom_btn_label_' . $key, true);
                $custom_btn_content = get_post_meta($post->ID, '_fcp_custom_btn_content_' . $key, true);
                ?>
                <label>
                    <input type="checkbox" name="fcp_custom_btn_active_<?php echo esc_attr($key); ?>" value="1" <?php checked($custom_btn_active, '1'); ?>>
                    Activer le bouton personnalisé
                </label>
                <br><br>
                <label>Titre du bouton :</label>
                <input type="text" name="fcp_custom_btn_label_<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($custom_btn_label); ?>" style="width:100%;">
                <br><br>
                <label>Contenu de la modal :</label>
                <?php
                wp_editor($custom_btn_content, 'fcp_custom_btn_content_' . $key, array(
                    'textarea_name' => 'fcp_custom_btn_content_' . $key,
                    'textarea_rows' => 5,
                    'media_buttons' => true,
                    'tinymce' => true,
                    'quicktags' => true,
                ));
                ?>
            </div>
        </fieldset>
        <?php
    }
}

function fcp_save_formule_metaboxes($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
    if (isset($_POST['post_type']) && 'formule' == $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id))
            return;
    }

    // Bannière
    if (isset($_POST['fcp_banner_nonce']) && wp_verify_nonce($_POST['fcp_banner_nonce'], 'fcp_save_banner')) {
        $banner = isset($_POST['fcp_banner_image']) ? sanitize_text_field($_POST['fcp_banner_image']) : '';
        update_post_meta($post_id, '_fcp_banner_image', $banner);
    }

    // Pricing list
    if (isset($_POST['fcp_pricing_nonce']) && wp_verify_nonce($_POST['fcp_pricing_nonce'], 'fcp_save_pricing')) {
        if (isset($_POST['fcp_pricing']) && is_array($_POST['fcp_pricing'])) {
            $pricing = array();
            foreach ($_POST['fcp_pricing'] as $item) {
                $pricing[] = array(
                    'title' => sanitize_text_field($item['title']),
                    'content' => wp_kses_post($item['content']),
                    'price' => floatval($item['price']),
                );
            }
            update_post_meta($post_id, '_fcp_pricing_list', $pricing);
        } else {
            update_post_meta($post_id, '_fcp_pricing_list', array());
        }
    }

    // Sections liées (Nos plats, buffets, options)
    if (isset($_POST['fcp_related_nonce']) && wp_verify_nonce($_POST['fcp_related_nonce'], 'fcp_save_related')) {
        $sections = array('plats', 'buffets', 'options');
        foreach ($sections as $section) {
            $show = isset($_POST['fcp_show_' . $section]) ? '1' : '0';
            update_post_meta($post_id, '_fcp_show_' . $section, $show);
            $selected = isset($_POST['fcp_selected_' . $section]) ? sanitize_text_field($_POST['fcp_selected_' . $section]) : '';
            update_post_meta($post_id, '_fcp_selected_' . $section, $selected);

            // Sauvegarde du bouton personnalisé
            $custom_btn_active = isset($_POST['fcp_custom_btn_active_' . $section]) ? '1' : '0';
            update_post_meta($post_id, '_fcp_custom_btn_active_' . $section, $custom_btn_active);

            $custom_btn_label = isset($_POST['fcp_custom_btn_label_' . $section]) ? sanitize_text_field($_POST['fcp_custom_btn_label_' . $section]) : '';
            update_post_meta($post_id, '_fcp_custom_btn_label_' . $section, $custom_btn_label);

            $custom_btn_content = isset($_POST['fcp_custom_btn_content_' . $section]) ? wp_kses_post($_POST['fcp_custom_btn_content_' . $section]) : '';
            update_post_meta($post_id, '_fcp_custom_btn_content_' . $section, $custom_btn_content);
        }
    }
}
add_action('save_post_formule', 'fcp_save_formule_metaboxes');

/*--------------------------------------------------------------
  META BOXES POUR LES CPT "NOS PLATS", "NOS BUFFETS" ET "NOS OPTIONS"
--------------------------------------------------------------*/
function fcp_add_related_items_metaboxes()
{
    $post_types = array(
        'nos_plats' => 'Liste des plats',
        'nos_buffets' => 'Liste des buffets',
        'nos_options' => 'Liste des options',
    );
    foreach ($post_types as $post_type => $title) {
        // Metabox pour la bannière
        add_meta_box(
            "fcp_banner_metabox_{$post_type}",
            'Bannière',
            'fcp_render_banner_metabox',
            $post_type,
            'normal',
            'high'
        );

        // Metabox existante pour les items
        add_meta_box(
            "fcp_items_metabox_{$post_type}",
            $title,
            'fcp_render_items_metabox',
            $post_type,
            'normal',
            'default',
            array(
                'meta_key' => '_fcp_items_' . $post_type,
                'item_label' => $title,
            )
        );
    }
}
add_action('add_meta_boxes', 'fcp_add_related_items_metaboxes');

function fcp_render_items_metabox($post, $metabox)
{
    $meta_key = $metabox['args']['meta_key'];
    wp_nonce_field('fcp_save_items', 'fcp_items_nonce');
    $items = get_post_meta($post->ID, $meta_key, true);
    if (!is_array($items)) {
        $items = array();
    }
    $is_buffet = ($post->post_type === 'nos_buffets');

    // Nouvelle structure avec sections
    $sections = get_post_meta($post->ID, $meta_key . '_sections', true);
    if (!is_array($sections)) {
        $sections = array(
            array(
                'title' => '',
                'items' => $items // Migrer les items existants dans la première section
            )
        );
    }
    ?>
    <div id="fcp_sections_container_<?php echo esc_attr($post->post_type); ?>">
        <?php if (!empty($sections)): ?>
            <?php foreach ($sections as $section_index => $section): ?>
                <div class="fcp-section">
                    <h3>Section</h3>
                    <div class="fcp-field">
                        <label>Titre de la section :</label>
                        <input type="text" name="fcp_sections[<?php echo esc_attr($section_index); ?>][title]"
                            value="<?php echo esc_attr($section['title']); ?>" style="width:100%;">
                    </div>

                    <div class="fcp-items-container">
                        <h4>Éléments de cette section</h4>
                        <?php
                        $section_items = isset($section['items']) && is_array($section['items']) ? $section['items'] : array();
                        if (!empty($section_items)):
                            ?>
                            <?php foreach ($section_items as $item_index => $item): ?>
                                <div class="fcp-item">
                                    <div class="fcp-field">
                                        <label>Titre :</label>
                                        <input type="text"
                                            name="fcp_sections[<?php echo esc_attr($section_index); ?>][items][<?php echo esc_attr($item_index); ?>][title]"
                                            value="<?php echo esc_attr($item['title']); ?>" style="width:100%;">
                                    </div>
                                    <?php if ($is_buffet): ?>
                                        <div class="fcp-field">
                                            <label>Image <em>(facultatif)</em> :</label>
                                            <div class="fcp-image-upload">
                                                <input type="hidden"
                                                    name="fcp_sections[<?php echo esc_attr($section_index); ?>][items][<?php echo esc_attr($item_index); ?>][image]"
                                                    value="<?php echo isset($item['image']) ? esc_attr($item['image']) : ''; ?>"
                                                    class="fcp-image-id">
                                                <img src="<?php echo isset($item['image']) ? wp_get_attachment_url($item['image']) : ''; ?>"
                                                    class="fcp-image-preview"
                                                    style="<?php echo isset($item['image']) ? '' : 'display:none;'; ?>">
                                                <button type="button" class="button fcp-upload-image">Choisir une image</button>
                                                <button type="button" class="button fcp-remove-image"
                                                    style="<?php echo isset($item['image']) ? '' : 'display:none;'; ?>">Supprimer
                                                    l'image</button>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="fcp-field">
                                        <label>Prix (€) <em>(facultatif)</em> :</label>
                                        <input type="number" step="0.01"
                                            name="fcp_sections[<?php echo esc_attr($section_index); ?>][items][<?php echo esc_attr($item_index); ?>][price]"
                                            value="<?php echo isset($item['price']) ? esc_attr($item['price']) : ''; ?>">
                                    </div>
                                    <div class="fcp-field">
                                        <label>Informations complémentaires <em>(facultatif)</em> :</label>
                                        <?php
                                        $editor_id = 'fcp_sections_' . $section_index . '_items_info_' . $item_index;
                                        $editor_content = isset($item['info']) ? $item['info'] : '';
                                        $settings = array(
                                            'textarea_name' => 'fcp_sections[' . esc_attr($section_index) . '][items][' . esc_attr($item_index) . '][info]',
                                            'textarea_rows' => 5,
                                            'media_buttons' => true,
                                            'tinymce' => true,
                                            'quicktags' => true,
                                            'editor_class' => 'fcp-item-info-editor'
                                        );
                                        wp_editor($editor_content, $editor_id, $settings);
                                        ?>
                                    </div>
                                    <button type="button" class="button fcp_remove_item">Supprimer</button>
                                    <hr>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="button fcp_add_item" data-section="<?php echo esc_attr($section_index); ?>"
                        data-posttype="<?php echo esc_attr($post->post_type); ?>">Ajouter un élément à cette section</button>
                    <button type="button" class="button fcp_remove_section">Supprimer cette section</button>
                    <hr style="border-top: 2px dashed #ccc; margin: 20px 0;">
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button type="button" class="button fcp_add_section" data-posttype="<?php echo esc_attr($post->post_type); ?>">Ajouter
        une nouvelle section</button>

    <!-- Template pour une nouvelle section -->
    <template id="fcp_section_template_<?php echo esc_attr($post->post_type); ?>">
        <div class="fcp-section">
            <h3>Section</h3>
            <div class="fcp-field">
                <label>Titre de la section :</label>
                <input type="text" name="fcp_sections[{{section_index}}][title]" value="" style="width:100%;">
            </div>

            <div class="fcp-items-container">
                <h4>Éléments de cette section</h4>
            </div>
            <button type="button" class="button fcp_add_item" data-section="{{section_index}}"
                data-posttype="<?php echo esc_attr($post->post_type); ?>">Ajouter un élément à cette section</button>
            <button type="button" class="button fcp_remove_section">Supprimer cette section</button>
            <hr style="border-top: 2px dashed #ccc; margin: 20px 0;">
        </div>
    </template>

    <!-- Template pour un nouvel item -->
    <template id="fcp_item_template_<?php echo esc_attr($post->post_type); ?>">
        <div class="fcp-item">
            <div class="fcp-field">
                <label>Titre :</label>
                <input type="text" name="fcp_sections[{{section_index}}][items][{{item_index}}][title]" value=""
                    style="width:100%;">
            </div>
            <?php if ($is_buffet): ?>
                <div class="fcp-field">
                    <label>Image <em>(facultatif)</em> :</label>
                    <div class="fcp-image-upload">
                        <input type="hidden" name="fcp_sections[{{section_index}}][items][{{item_index}}][image]" value=""
                            class="fcp-image-id">
                        <img src="" class="fcp-image-preview" style="display:none;">
                        <button type="button" class="button fcp-upload-image">Choisir une image</button>
                        <button type="button" class="button fcp-remove-image" style="display:none;">Supprimer l'image</button>
                    </div>
                </div>
            <?php endif; ?>
            <div class="fcp-field">
                <label>Prix (€) <em>(facultatif)</em> :</label>
                <input type="number" step="0.01" name="fcp_sections[{{section_index}}][items][{{item_index}}][price]"
                    value="">
            </div>
            <div class="fcp-field">
                <label>Informations complémentaires <em>(facultatif)</em> :</label>
                <div id="wp-fcp_sections_{{section_index}}_items_info_{{item_index}}-wrap"
                    class="wp-core-ui wp-editor-wrap tmce-active">
                    <div id="wp-fcp_sections_{{section_index}}_items_info_{{item_index}}-editor-tools"
                        class="wp-editor-tools hide-if-no-js">
                        <div class="wp-editor-tabs">
                            <button type="button" id="fcp_sections_{{section_index}}_items_info_{{item_index}}-tmce"
                                class="wp-switch-editor switch-tmce"
                                data-wp-editor-id="fcp_sections_{{section_index}}_items_info_{{item_index}}">Visuel</button>
                            <button type="button" id="fcp_sections_{{section_index}}_items_info_{{item_index}}-html"
                                class="wp-switch-editor switch-html"
                                data-wp-editor-id="fcp_sections_{{section_index}}_items_info_{{item_index}}">Texte</button>
                        </div>
                    </div>
                    <div id="wp-fcp_sections_{{section_index}}_items_info_{{item_index}}-editor-container"
                        class="wp-editor-container">
                        <textarea id="fcp_sections_{{section_index}}_items_info_{{item_index}}"
                            name="fcp_sections[{{section_index}}][items][{{item_index}}][info]"
                            class="wp-editor-area"></textarea>
                    </div>
                </div>
            </div>
            <button type="button" class="button fcp_remove_item">Supprimer</button>
            <hr>
        </div>
    </template>

    <style>
        .fcp-section {
            background: #f9f9f9;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #e5e5e5;
            border-radius: 5px;
        }

        .fcp-items-container {
            margin-left: 15px;
            padding-left: 15px;
            border-left: 3px solid #ddd;
        }

        .fcp-item {
            background: #fff;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #e5e5e5;
            border-radius: 3px;
        }

        .fcp_remove_section {
            margin-left: 10px;
            background: #f5f5f5;
            color: #a00;
            border-color: #ccc;
        }

        .fcp_remove_section:hover {
            background: #f1f1f1;
            color: #dc3232;
            border-color: #999;
        }

        .fcp-field {
            margin-bottom: 15px;
        }

        .fcp-field label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .fcp-image-preview {
            max-width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            margin: 10px 0;
        }

        .fcp-image-upload {
            margin: 10px 0;
        }

        .fcp-remove-image {
            margin-left: 10px !important;
        }
    </style>
    <script>
        jQuery(document).ready(function ($) {
            var sectionsContainer = $('#fcp_sections_container_<?php echo esc_js($post->post_type); ?>');
            var sectionCount = sectionsContainer.find('.fcp-section').length;

            // Fonction pour initialiser TinyMCE
            function initTinyMCE(editorId) {
                // Supprimer l'instance existante si elle existe
                if (tinymce.get(editorId)) {
                    tinymce.remove('#' + editorId);
                }

                // Initialiser le nouvel éditeur
                tinymce.init({
                    selector: '#' + editorId,
                    height: 150,
                    menubar: false,
                    plugins: 'lists link image media wordpress wplink',
                    toolbar: 'formatselect | bold italic | bullist numlist | link image',
                    relative_urls: false,
                    remove_script_host: false,
                    convert_urls: true,
                    browser_spellcheck: true,
                    entity_encoding: 'raw',
                    setup: function (editor) {
                        editor.on('change', function () {
                            editor.save();
                        });
                    }
                });

                // Initialiser Quicktags
                if (typeof QTags !== 'undefined') {
                    QTags({ id: editorId, buttons: 'strong,em,link,block,del,ins,img,ul,ol,li,code,close' });
                }
            }

            // Gestion des images
            sectionsContainer.on('click', '.fcp-upload-image', function (e) {
                e.preventDefault();
                var button = $(this);
                var imageUpload = button.closest('.fcp-image-upload');
                var frame = wp.media({
                    title: 'Sélectionner une image',
                    button: { text: 'Utiliser cette image' },
                    multiple: false
                });

                frame.on('select', function () {
                    var attachment = frame.state().get('selection').first().toJSON();
                    imageUpload.find('.fcp-image-id').val(attachment.id);
                    imageUpload.find('.fcp-image-preview').attr('src', attachment.url).show();
                    imageUpload.find('.fcp-remove-image').show();
                });

                frame.open();
            });

            sectionsContainer.on('click', '.fcp-remove-image', function (e) {
                e.preventDefault();
                var imageUpload = $(this).closest('.fcp-image-upload');
                imageUpload.find('.fcp-image-id').val('');
                imageUpload.find('.fcp-image-preview').attr('src', '').hide();
                $(this).hide();
            });

            // Ajouter une nouvelle section
            $('button.fcp_add_section[data-posttype="<?php echo esc_js($post->post_type); ?>"]').on('click', function () {
                var template = $('#fcp_section_template_<?php echo esc_js($post->post_type); ?>').html();
                template = template.replace(/{{section_index}}/g, sectionCount);
                sectionsContainer.append(template);
                sectionCount++;
            });

            // Supprimer une section
            sectionsContainer.on('click', '.fcp_remove_section', function () {
                var section = $(this).closest('.fcp-section');

                // Supprimer tous les éditeurs TinyMCE dans cette section
                section.find('.wp-editor-area').each(function () {
                    var editorId = $(this).attr('id');
                    if (editorId && tinymce.get(editorId)) {
                        tinymce.remove('#' + editorId);
                    }
                });

                section.remove();
            });

            // Ajouter un nouvel item à une section
            sectionsContainer.on('click', '.fcp_add_item', function () {
                var sectionIndex = $(this).data('section');
                var section = $(this).closest('.fcp-section');
                var itemsContainer = section.find('.fcp-items-container');
                var itemCount = itemsContainer.find('.fcp-item').length;

                var template = $('#fcp_item_template_<?php echo esc_js($post->post_type); ?>').html();
                template = template.replace(/{{section_index}}/g, sectionIndex);
                template = template.replace(/{{item_index}}/g, itemCount);

                itemsContainer.append(template);

                // Initialiser TinyMCE pour le nouvel item
                var editorId = 'fcp_sections_' + sectionIndex + '_items_info_' + itemCount;
                setTimeout(function () {
                    initTinyMCE(editorId);
                }, 100);
            });

            // Supprimer un item
            sectionsContainer.on('click', '.fcp_remove_item', function () {
                var item = $(this).closest('.fcp-item');
                var editorId = item.find('.wp-editor-area').attr('id');
                if (editorId && tinymce.get(editorId)) {
                    tinymce.remove('#' + editorId);
                }
                item.remove();
            });
        });
    </script>
    <?php
}

function fcp_save_items_metaboxes($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    // Sauvegarde de la bannière
    if (isset($_POST['fcp_banner_nonce']) && wp_verify_nonce($_POST['fcp_banner_nonce'], 'fcp_save_banner')) {
        $banner = isset($_POST['fcp_banner_image']) ? sanitize_text_field($_POST['fcp_banner_image']) : '';
        update_post_meta($post_id, '_fcp_banner_image', $banner);
    }

    // Sauvegarde des items
    if (!isset($_POST['fcp_items_nonce']) || !wp_verify_nonce($_POST['fcp_items_nonce'], 'fcp_save_items'))
        return;

    if (isset($_POST['post_type']) && in_array($_POST['post_type'], array('nos_plats', 'nos_buffets', 'nos_options'))) {
        if (!current_user_can('edit_post', $post_id))
            return;
        $post_type = $_POST['post_type'];
        $meta_key = '_fcp_items_' . $post_type;

        // Sauvegarde des sections
        if (isset($_POST['fcp_sections']) && is_array($_POST['fcp_sections'])) {
            $sections = array();
            foreach ($_POST['fcp_sections'] as $section_data) {
                $section = array(
                    'title' => isset($section_data['title']) ? sanitize_text_field($section_data['title']) : '',
                    'items' => array()
                );

                // Traitement des items de la section
                if (isset($section_data['items']) && is_array($section_data['items'])) {
                    foreach ($section_data['items'] as $item) {
                        if (isset($item['title']) && $item['title'] !== '') {
                            $new_item = array(
                                'title' => sanitize_text_field($item['title']),
                                'price' => isset($item['price']) ? floatval($item['price']) : '',
                                'info' => isset($item['info']) ? wp_kses_post($item['info']) : '',
                            );

                            // Ajouter l'image uniquement pour les buffets
                            if ($post_type === 'nos_buffets' && isset($item['image'])) {
                                $new_item['image'] = absint($item['image']);
                            }

                            $section['items'][] = $new_item;
                        }
                    }
                }

                // N'ajouter la section que si elle a un titre ou des items
                if (!empty($section['title']) || !empty($section['items'])) {
                    $sections[] = $section;
                }
            }

            // Sauvegarder les sections
            update_post_meta($post_id, $meta_key . '_sections', $sections);

            // Pour la rétrocompatibilité, sauvegarder également tous les items dans l'ancien format
            $all_items = array();
            foreach ($sections as $section) {
                if (!empty($section['items'])) {
                    foreach ($section['items'] as $item) {
                        $all_items[] = $item;
                    }
                }
            }
            update_post_meta($post_id, $meta_key, $all_items);
        } else {
            // Si pas de sections, vider les métadonnées
            update_post_meta($post_id, $meta_key . '_sections', array());
            update_post_meta($post_id, $meta_key, array());
        }
    }
}
add_action('save_post', 'fcp_save_items_metaboxes');

/*--------------------------------------------------------------
  AFFICHAGE FRONT-END : PRICING LIST ET MODALS
--------------------------------------------------------------*/
function fcp_append_formule_content($content)
{
    // Vérifier si nous sommes sur une page de formule unique et dans la boucle principale
    if (!is_singular('formule') || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    // Éviter la récursion
    remove_filter('the_content', 'fcp_append_formule_content');

    global $post;
    $output = '';

    // Affichage de la bannière
    $banner_id = get_post_meta($post->ID, '_fcp_banner_image', true);
    if ($banner_id) {
        $banner_url = wp_get_attachment_url($banner_id);
        if ($banner_url) {
            $output .= '<div class="fcp-banner-container">';
            $output .= '<div class="fcp-banner" style="background-image: url(\'' . esc_url($banner_url) . '\')">';
            $output .= '<div class="fcp-banner-overlay">';
            $output .= '<h1 class="fcp-banner-title">' . esc_html(get_the_title()) . '</h1>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</div>';
        }
    }

    // Ajout du contenu original après la bannière
    $output .= '<div class="fcp-content">' . $content . '</div>';

    // Affichage de la pricing list
    $pricing_list = get_post_meta($post->ID, '_fcp_pricing_list', true);
    if (is_array($pricing_list) && !empty($pricing_list)) {
        $count = count($pricing_list);
        if ($count > 3) {
            $output .= '<div class="swiper-container fcp-pricing-slider">';
            $output .= '<div class="swiper-wrapper">';
            foreach ($pricing_list as $item) {
                $output .= '<div class="swiper-slide fcp-pricing_item">';
                $output .= '<h3>' . esc_html($item['title']) . '</h3>';
                $output .= '<div class="pricing-content">' . wpautop($item['content']) . '</div>';
                $output .= '<p class="fcp-price">' . esc_html($item['price']) . ' €</p>';
                $output .= '<button type="button" class="fcp-choose-button choisir-formule-btn" 
                    data-formule-slug="' . esc_attr(sanitize_title($item['title'])) . '" 
                    data-formule-title="' . esc_attr($item['title']) . '" 
                    data-formule-price="' . esc_attr($item['price'] . ' €') . '" 
                    data-formule-id="' . esc_attr($post->ID) . '"
                    data-post-title="' . esc_attr(get_the_title($post->ID)) . '">Choisir cette formule</button>';
                $output .= '</div>';
            }
            $output .= '</div>';
            $output .= '<div class="swiper-pagination"></div>';
            $output .= '<div class="swiper-button-next"></div>';
            $output .= '<div class="swiper-button-prev"></div>';
            $output .= '</div>';
        } else {
            $output .= '<div class="fcp-pricing-grid">';
            foreach ($pricing_list as $item) {
                $output .= '<div class="fcp-pricing_item">';
                $output .= '<h3>' . esc_html($item['title']) . '</h3>';
                $output .= '<div class="pricing-content">' . wpautop($item['content']) . '</div>';
                $output .= '<p class="fcp-price">' . esc_html($item['price']) . ' €</p>';
                $output .= '<button type="button" class="fcp-choose-button choisir-formule-btn" 
                    data-formule-slug="' . esc_attr(sanitize_title($item['title'])) . '" 
                    data-formule-title="' . esc_attr($item['title']) . '" 
                    data-formule-price="' . esc_attr($item['price'] . ' €') . '" 
                    data-formule-id="' . esc_attr($post->ID) . '"
                    data-post-title="' . esc_attr(get_the_title($post->ID)) . '">Choisir cette formule</button>';
                $output .= '</div>';
            }
            $output .= '</div>';
        }
    }

    // Affichage des boutons et modals pour les CPT liés
    $sections = array(
        'plats' => array('label' => 'Nos plats', 'cpt' => 'nos_plats'),
        'buffets' => array('label' => 'Nos buffets', 'cpt' => 'nos_buffets'),
        'options' => array('label' => 'Nos options', 'cpt' => 'nos_options'),
    );

    $buttons = '';
    $modals = '';

    foreach ($sections as $key => $data) {
        $show = get_post_meta($post->ID, '_fcp_show_' . $key, true);
        $selected = get_post_meta($post->ID, '_fcp_selected_' . $key, true);

        if ($show === '1' && !empty($selected)) {
            $assoc_post = get_post($selected);
            if ($assoc_post) {
                // Bouton pour ouvrir la modale standard (Plat/Buffet/Option)
                $buttons .= '<button type="button" class="fcp-modal-button" data-modal="fcp-modal-' . esc_attr($key) . '">' . esc_html($data['label']) . '</button>';
                
                // --- DEBUT MODIFICATION : Bouton Popup Personnalisé ---
                $custom_btn_active = get_post_meta($post->ID, '_fcp_custom_btn_active_' . $key, true);
                $custom_btn_label = get_post_meta($post->ID, '_fcp_custom_btn_label_' . $key, true);
                $custom_btn_content = get_post_meta($post->ID, '_fcp_custom_btn_content_' . $key, true);
                
                if ($custom_btn_active === '1' && !empty($custom_btn_label)) {
                    // 1. Création du bouton
                    $buttons .= '<button type="button" class="fcp-modal-button fcp-custom-popup-button" data-modal="fcp-custom-modal-' . esc_attr($key) . '">' . esc_html($custom_btn_label) . '</button>';

                    // 2. Création de la modale associée (qui manquait)
                    $modals .= '<div id="fcp-custom-modal-' . esc_attr($key) . '" class="fcp-modal">';
                    $modals .= '<div class="fcp-modal-content">';
                    $modals .= '<span class="fcp-modal-close">&times;</span>';
                    
                    // Bannière pour la modale personnalisée (titre sur fond couleur par défaut)
                    $modals .= '<div class="fcp-modal-banner">';
                    $modals .= '<div class="fcp-modal-banner-overlay">';
                    $modals .= '<h2>' . esc_html($custom_btn_label) . '</h2>';
                    $modals .= '</div>';
                    $modals .= '</div>';
                    
                    // Corps de la modale avec le contenu libre
                    $modals .= '<div class="fcp-modal-body">';
                    $modals .= '<div class="fcp-modal-description">';
                    $modals .= wpautop($custom_btn_content);
                    $modals .= '</div>'; // fin description
                    $modals .= '</div>'; // fin body
                    
                    $modals .= '</div>'; // fin content
                    $modals .= '</div>'; // fin modal
                }
                // --- FIN MODIFICATION ---

                // Récupérer l'image de bannière standard
                $banner_id = get_post_meta($selected, '_fcp_banner_image', true);
                $banner_url = $banner_id ? wp_get_attachment_url($banner_id) : '';

                // Récupérer la liste d'items depuis le post associé
                $meta_key = '_fcp_items_' . $data['cpt'];
                $items = get_post_meta($selected, $meta_key, true);

                // Créer la modal standard
                $modals .= '<div id="fcp-modal-' . esc_attr($key) . '" class="fcp-modal">';
                $modals .= '<div class="fcp-modal-content">';
                $modals .= '<span class="fcp-modal-close">&times;</span>';

                // Bannière de la modal
                $modals .= '<div class="fcp-modal-banner" style="background-image: url(\'' . esc_url($banner_url) . '\')">';
                $modals .= '<div class="fcp-modal-banner-overlay">';
                $modals .= '<h2>' . esc_html($assoc_post->post_title) . '</h2>';
                $modals .= '</div>';
                $modals .= '</div>';

                $modals .= '<div class="fcp-modal-body">';

                // Description générale
                if (!empty($assoc_post->post_content)) {
                    $modals .= '<div class="fcp-modal-description">';
                    $modals .= wpautop($assoc_post->post_content);
                    $modals .= '</div>';
                }

                // Liste des items (Logique standard existante)
                if (is_array($items) && !empty($items)) {
                    $items_count = count($items);
                    $column_class = '';
                    if ($items_count > 20) {
                        $column_class = 'three-columns';
                    } elseif ($items_count > 10) {
                        $column_class = 'two-columns';
                    }

                    // Vérifier si nous avons des sections
                    $sections_data = get_post_meta($selected, $meta_key . '_sections', true);

                    if (is_array($sections_data) && !empty($sections_data)) {
                        // Affichage avec sections
                        foreach ($sections_data as $section) {
                            if (!empty($section['title'])) {
                                $modals .= '<h3 class="fcp-section-title">' . esc_html($section['title']) . '</h3>';
                            }

                            if (!empty($section['items'])) {
                                $modals .= '<ul class="fcp-items-list ' . esc_attr($column_class) . '">';
                                foreach ($section['items'] as $item) {
                                    $modals .= '<li>';

                                    // Titre et prix sur la même ligne
                                    $modals .= '<div class="fcp-item-title">';
                                    $modals .= '<span class="fcp-item-name">' . esc_html($item['title']) . '</span>';
                                    if (!empty($item['price']) && $item['price'] > 0) {
                                        $modals .= '<span class="fcp-item-price">' . number_format($item['price'], 2, ',', ' ') . ' €</span>';
                                    }
                                    $modals .= '</div>';

                                    // Image pour les buffets (placée après le titre)
                                    if ($data['cpt'] === 'nos_buffets' && !empty($item['image'])) {
                                        $image_url = wp_get_attachment_image_url($item['image'], 'thumbnail');
                                        if ($image_url) {
                                            $modals .= '<div class="fcp-item-image"><img src="' . esc_url($image_url) . '" alt="' . esc_attr($item['title']) . '"></div>';
                                        }
                                    }

                                    // Informations complémentaires
                                    if (!empty($item['info'])) {
                                        // Nettoyer et formater le contenu pour l'afficher en italique
                                        $info_content = wp_kses_post($item['info']);
                                        // Remplacer les balises <p> par des <p class="fcp-item-info-text">
                                        $info_content = str_replace('<p>', '<p class="fcp-item-info-text">', $info_content);
                                        $modals .= '<div class="fcp-item-info">' . $info_content . '</div>';
                                    }

                                    $modals .= '</li>';
                                }
                                $modals .= '</ul>';
                            }
                        }
                    } else {
                        // Affichage classique sans sections (pour la rétrocompatibilité)
                        $modals .= '<ul class="fcp-items-list ' . esc_attr($column_class) . '">';
                        foreach ($items as $item) {
                            $modals .= '<li>';

                            // Titre et prix sur la même ligne
                            $modals .= '<div class="fcp-item-title">';
                            $modals .= '<span class="fcp-item-name">' . esc_html($item['title']) . '</span>';
                            if (!empty($item['price']) && $item['price'] > 0) {
                                $modals .= '<span class="fcp-item-price">' . number_format($item['price'], 2, ',', ' ') . ' €</span>';
                            }
                            $modals .= '</div>';

                            // Image pour les buffets (placée après le titre)
                            if ($data['cpt'] === 'nos_buffets' && !empty($item['image'])) {
                                $image_url = wp_get_attachment_image_url($item['image'], 'thumbnail');
                                if ($image_url) {
                                    $modals .= '<div class="fcp-item-image"><img src="' . esc_url($image_url) . '" alt="' . esc_attr($item['title']) . '"></div>';
                                }
                            }

                            // Informations complémentaires
                            if (!empty($item['info'])) {
                                // Nettoyer et formater le contenu pour l'afficher en italique
                                $info_content = wp_kses_post($item['info']);
                                // Remplacer les balises <p> par des <p class="fcp-item-info-text">
                                $info_content = str_replace('<p>', '<p class="fcp-item-info-text">', $info_content);
                                $modals .= '<div class="fcp-item-info">' . $info_content . '</div>';
                            }

                            $modals .= '</li>';
                        }
                        $modals .= '</ul>';
                    }
                }

                $modals .= '</div></div></div>';
            }
        }
    }

    if (!empty($buttons)) {
        $output .= '<div class="fcp-related-buttons">' . $buttons . '</div>';
    }
    if (!empty($modals)) {
        $output .= '<div class="fcp-modals">' . $modals . '</div>';
    }

    // Ajouter la modal du formulaire de contact à la fin
    $output .= '<div id="modal-formule-contact" class="fcp-modal">';
    $output .= '<div class="fcp-modal-content">';
    $output .= '<span class="fcp-modal-close">&times;</span>';
    $output .= '<div class="fcp-modal-body">';
    $output .= '<h2>Demande de devis</h2>';
    $output .= '<p>Vous avez choisi : <strong><span id="formule-choisie-titre"></span></strong></p>';
    $output .= '<form id="formule-contact-form" method="post">';
    $output .= '<input type="hidden" id="formule-choisie-input" name="formule_choisie" value="">';
    $output .= '<input type="hidden" id="formule-nom-input" name="formule_nom" value="">';
    $output .= '<input type="hidden" id="formule-prix-input" name="formule_prix" value="">';
    $output .= '<input type="hidden" name="formule_contact_submit" value="1">';

    // Début de la grille pour les champs
    $output .= '<div class="form-fields-grid">';

    // Champs sur deux colonnes
    $output .= '<div class="form-group">';
    $output .= '<label for="nom" class="required">Nom</label>';
    $output .= '<input type="text" id="nom" name="nom" required>';
    $output .= '</div>';

    $output .= '<div class="form-group">';
    $output .= '<label for="email" class="required">Email</label>';
    $output .= '<input type="email" id="email" name="email" required>';
    $output .= '</div>';

    $output .= '<div class="form-group">';
    $output .= '<label for="telephone" class="required">Téléphone</label>';
    $output .= '<input type="tel" id="telephone" name="telephone" required>';
    $output .= '</div>';

    $output .= '<div class="form-group">';
    $output .= '<label for="nombre_personnes" class="required">Nombre de personnes</label>';
    $output .= '<input type="number" id="nombre_personnes" name="nombre_personnes" min="1" required>';
    $output .= '</div>';

    $output .= '<div class="form-group">';
    $output .= '<label for="date_evenement">Date de l\'événement</label>';
    $output .= '<input type="date" id="date_evenement" name="date_evenement">';
    $output .= '</div>';

    $output .= '</div>'; // Fin de form-fields-grid

    // Message en pleine largeur
    $output .= '<div class="form-group full-width">';
    $output .= '<label for="message">Message</label>';
    $output .= '<textarea id="message" name="message" rows="4"></textarea>';
    $output .= '</div>';

    $output .= '<button type="submit" class="submit-button">Envoyer la demande</button>';
    $output .= '</form>';
    $output .= '</div>';
    $output .= '</div>';
    $output .= '</div>';

    // Réajouter le filtre pour les prochains appels
    add_filter('the_content', 'fcp_append_formule_content');

    return $output;
}

/*--------------------------------------------------------------
  ENQUEUE DES SCRIPTS ET STYLES FRONT-END
--------------------------------------------------------------*/
function fcp_enqueue_frontend_scripts()
{
    // Charger les scripts sur toutes les pages, car le shortcode peut être utilisé partout
    // Enqueue Swiper CSS
    wp_enqueue_style('swiper-css', 'https://unpkg.com/swiper/swiper-bundle.min.css', array(), '6.8.4');

    // Enqueue Swiper JS
    wp_enqueue_script('swiper-js', 'https://unpkg.com/swiper/swiper-bundle.min.js', array(), '6.8.4', true);

    // Enqueue custom CSS
    wp_enqueue_style('fcp-styles', plugin_dir_url(__FILE__) . 'css/fcp-frontend.css', array(), '1.0.0');

    // Enqueue custom JS
    wp_enqueue_script('fcp-scripts', plugin_dir_url(__FILE__) . 'js/fcp-frontend.js', array('jquery', 'swiper-js'), '1.0.0', true);

    // Localiser les variables pour le script
    wp_localize_script('fcp-scripts', 'fcp_vars', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('fcp-nonce'),
        'plugin_url' => plugin_dir_url(__FILE__),
        'debug' => WP_DEBUG ? true : false
    ));

    // Inline CSS pour les sections et les options
    $custom_css = "
        /* Styles pour les titres de section */
        .fcp-section-title {
            font-size: 1.3em;
            margin-top: 25px;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 2px solid #ddd;
            padding-bottom: 6px;
            clear: both;
            font-weight: 600;
        }
        
        .fcp-modal-body .fcp-section-title:first-child {
            margin-top: 0;
        }
        
        /* Styles pour les listes d'items */
        .fcp-items-list {
            margin-bottom: 20px;
            padding-left: 0;
            list-style: none;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        
        /* Style compact pour les items */
        .fcp-items-list li {
            background: #f9f9f9;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 0;
            border: 1px solid #eee;
            font-size: 0.9em;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }
        
        .fcp-items-list li:hover {
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            background: #fff;
            border-color: #ddd;
        }
        
        /* Style pour le titre et prix */
        .fcp-item-title {
            font-weight: 600;
            margin-bottom: 2px;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            font-size: 0.95em;
            line-height: 1.3;
            width: 100%;
        }
        
        .fcp-item-name {
            flex: 1 !important;
            padding-right: 8px;
            display: inline-block !important;
        }
        
        .fcp-item-price {
            white-space: nowrap;
            color: #e83e8c;
            font-weight: 700;
            padding-left: 5px;
            border-left: 1px solid #eee;
            display: inline-block !important;
            text-align: right;
        }
        
        /* Style pour les informations complémentaires */
        .fcp-item-info {
            font-size: 0.85em;
            color: #666;
            margin-top: 2px;
            font-style: italic;
            clear: both;
        }
        
        .fcp-item-info p {
            margin: 0 0 3px;
        }
        
        .fcp-item-info-text {
            font-style: italic;
            margin: 0 0 3px;
            color: #666;
        }
        
        /* Style pour les images */
        .fcp-item-image {
            margin-bottom: 6px;
            text-align: center;
        }
        
        .fcp-item-image img {
            max-width: 60px;
            height: auto;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .fcp-items-list {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 576px) {
            .fcp-items-list {
                grid-template-columns: 1fr;
            }
            
            .fcp-section-title {
                font-size: 1.2em;
            }
        }
        
        /* Style pour les colonnes */
        .fcp-items-list.two-columns {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .fcp-items-list.three-columns {
            grid-template-columns: repeat(3, 1fr);
        }
        
        /* Ajustements pour les colonnes sur mobile */
        @media (max-width: 768px) {
            .fcp-items-list.two-columns,
            .fcp-items-list.three-columns {
                grid-template-columns: 1fr;
            }
        }
        
        /* Style pour la modal */
        .fcp-modal {
            z-index: 9999;
        }
        
        .fcp-modal-content {
            max-width: 90%;
            width: 1200px;
            margin: 0 auto;
            height: 90%;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }
        
        .fcp-modal-body {
            padding: 20px;
            overflow-y: auto;
            flex: 1;
        }
        
        .fcp-modal-banner {
            height: 200px;
        }
        
        /* Style pour les boutons */
        .fcp-modal-button {
            margin: 5px;
        }
        
        /* Style pour la description générale */
        .fcp-modal-description {
            margin-bottom: 20px;
        }
        
        /* Ajustements pour les modals sur mobile */
        @media (max-width: 768px) {
            .fcp-modal-content {
                width: 95%;
                max-width: 95%;
                height: 95%;
                max-height: 95vh;
            }
            
            .fcp-modal-banner {
                height: 150px;
            }
        }
    ";
    wp_add_inline_style('fcp-styles', $custom_css);
}
add_action('wp_enqueue_scripts', 'fcp_enqueue_frontend_scripts');

// Inclure le script d'administration pour l'importation des options
if (is_admin()) {
    $admin_import_file = plugin_dir_path(__FILE__) . 'scripts/admin-import.php';
    if (file_exists($admin_import_file)) {
        require_once $admin_import_file;
    }
}

// Inclure le fichier d'importation de portfolio s'il existe
$import_portfolio_file = plugin_dir_path(__FILE__) . 'import-portfolio.php';
if (file_exists($import_portfolio_file)) {
    include_once $import_portfolio_file;
}

/*--------------------------------------------------------------
  TRAITEMENT DU FORMULAIRE DE DEVIS
--------------------------------------------------------------*/
function fcp_process_contact_form()
{
    // Vérifier si le formulaire a été soumis
    if (isset($_POST['formule_contact_submit']) && isset($_POST['formule_choisie'])) {

        // Récupérer les données du formulaire
        $formule_choisie = sanitize_text_field($_POST['formule_choisie']);
        $formule_nom = sanitize_text_field($_POST['formule_nom'] ?? '');
        $formule_prix = sanitize_text_field($_POST['formule_prix'] ?? '');
        $nom = sanitize_text_field($_POST['nom'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $telephone = sanitize_text_field($_POST['telephone'] ?? '');
        $nombre_personnes = intval($_POST['nombre_personnes'] ?? 0);
        $date_evenement = sanitize_text_field($_POST['date_evenement'] ?? '');
        $message = sanitize_textarea_field($_POST['message'] ?? '');

        // Vérifier les champs obligatoires
        if (empty($nom) || empty($email) || empty($telephone) || $nombre_personnes <= 0) {
            // Rediriger avec un message d'erreur
            wp_redirect(add_query_arg('devis_status', 'error', wp_get_referer()));
            exit;
        }

        // Préparer le contenu de l'email
        $email_template = get_option('rvk_email_template', '');
        if (empty($email_template)) {
            // Template par défaut si aucun n'est défini
            $email_template = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <h2 style="color: #333; border-bottom: 2px solid #ddd; padding-bottom: 10px;">Nouvelle demande de devis</h2>
                <p><strong>Formule choisie:</strong> {formule_nom}</p>
                <p><strong>Prix:</strong> {formule_prix}</p>
                <p><strong>Nom:</strong> {nom}</p>
                <p><strong>Email:</strong> {email}</p>
                <p><strong>Téléphone:</strong> {telephone}</p>
                <p><strong>Nombre de personnes:</strong> {nombre_personnes}</p>
                <p><strong>Date de l\'événement:</strong> {date_evenement}</p>
                <p><strong>Message:</strong></p>
                <div style="background-color: #f9f9f9; padding: 10px; border-radius: 5px;">{message}</div>
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #777;">
                    <p>Cet email a été envoyé depuis le formulaire de devis du site RVK.</p>
                </div>
            </div>';
        }

        // Journaliser les données pour le débogage
        $upload_dir = wp_upload_dir();
        $debug_log_file = $upload_dir['basedir'] . '/rvk-form-data-log.txt';
        $debug_message = "=== DONNÉES DU FORMULAIRE (" . date('Y-m-d H:i:s') . ") ===\n";
        $debug_message .= "Formule choisie: " . $formule_choisie . "\n";
        $debug_message .= "Formule nom: " . $formule_nom . "\n";
        $debug_message .= "Formule prix: " . $formule_prix . "\n";
        $debug_message .= "Nom: " . $nom . "\n";
        $debug_message .= "Email: " . $email . "\n";
        $debug_message .= "Téléphone: " . $telephone . "\n";
        $debug_message .= "Nombre de personnes: " . $nombre_personnes . "\n";
        $debug_message .= "Date de l'événement: " . $date_evenement . "\n";
        $debug_message .= "Message: " . $message . "\n";
        $debug_message .= "------------------------------------------------\n";
        file_put_contents($debug_log_file, $debug_message, FILE_APPEND);

        // Remplacer les variables dans le template
        $replacements = array(
            '{formule_nom}' => $formule_nom,
            '{formule_prix}' => $formule_prix,
            '{nom}' => $nom,
            '{email}' => $email,
            '{telephone}' => $telephone,
            '{nombre_personnes}' => $nombre_personnes,
            '{date_evenement}' => $date_evenement,
            '{message}' => nl2br($message)
        );

        $email_content = str_replace(array_keys($replacements), array_values($replacements), $email_template);

        // Configurer l'email
        $to = get_option('rvk_email_recipient', get_option('admin_email'));
        $subject = get_option('rvk_email_subject', 'Nouvelle demande de devis - RVK');

        // En-têtes pour l'email HTML avec From et Reply-To
        $site_name = get_bloginfo('name');
        $admin_email = get_option('admin_email');

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <' . $admin_email . '>',
            'Reply-To: ' . $nom . ' <' . $email . '>'
        );

        // Débogage - Enregistrer les informations dans un fichier de log
        $log_message = "Tentative d'envoi d'email:\n";
        $log_message .= "Destinataire: " . $to . "\n";
        $log_message .= "Sujet: " . $subject . "\n";
        $log_message .= "From: " . $site_name . ' <' . $admin_email . ">\n";
        $log_message .= "Reply-To: " . $nom . ' <' . $email . ">\n";
        $log_message .= "Contenu: " . $email_content . "\n";

        // Créer un fichier de log dans le dossier uploads
        $upload_dir = wp_upload_dir();
        $log_file = $upload_dir['basedir'] . '/rvk-email-log.txt';
        file_put_contents($log_file, $log_message, FILE_APPEND);

        // Envoyer l'email avec la fonction alternative
        $email_result = fcp_send_email($to, $subject, $email_content, $headers);

        // Récupérer la méthode utilisée (stockée dans une variable globale)
        global $fcp_email_method;

        // Enregistrer le résultat dans le fichier de log
        $result_message = "Résultat de l'envoi: " . ($email_result ? "Succès" : "Échec") . "\n";
        $result_message .= "Méthode: " . ($fcp_email_method ?: "inconnue") . "\n";
        $result_message .= "Date: " . date('Y-m-d H:i:s') . "\n";

        // Vérifier si des plugins SMTP sont actifs
        $smtp_plugins = array(
            'wp-mail-smtp/wp_mail_smtp.php' => 'WP Mail SMTP',
            'easy-wp-smtp/easy-wp-smtp.php' => 'Easy WP SMTP',
            'post-smtp/postman-smtp.php' => 'Post SMTP'
        );

        $active_smtp = 'Aucun';

        // Vérifier si la fonction is_plugin_active existe
        if (!function_exists('is_plugin_active')) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        foreach ($smtp_plugins as $plugin_file => $plugin_name) {
            if (function_exists('is_plugin_active') && is_plugin_active($plugin_file)) {
                $active_smtp = $plugin_name;
                break;
            }
        }

        $result_message .= "Plugin SMTP actif: " . $active_smtp . "\n";
        $result_message .= "------------------------------------------------\n";
        file_put_contents($log_file, $result_message, FILE_APPEND);

        // Rediriger avec un message de succès ou d'erreur
        if ($email_result) {
            wp_redirect(add_query_arg('devis_status', 'success', wp_get_referer()));
        } else {
            wp_redirect(add_query_arg('devis_status', 'error', wp_get_referer()));
        }
        exit;
    }
}
add_action('init', 'fcp_process_contact_form');

// Afficher les messages de succès ou d'erreur
function fcp_display_form_messages()
{
    if (isset($_GET['devis_status'])) {
        if ($_GET['devis_status'] === 'success') {
            echo '<div class="fcp-message success">
                <p><strong>Votre demande de devis a été envoyée avec succès.</strong></p>
                <p>Nous vous contacterons prochainement. Si vous ne recevez pas de réponse dans les 48 heures, veuillez vérifier votre dossier de spam ou nous contacter directement.</p>
                </div>';
        } elseif ($_GET['devis_status'] === 'error') {
            echo '<div class="fcp-message error">
                <p><strong>Une erreur s\'est produite lors de l\'envoi de votre demande.</strong></p>
                <p>Veuillez réessayer ou nous contacter directement par téléphone.</p>
                </div>';
        }
    }
}
add_action('wp_footer', 'fcp_display_form_messages');

/*--------------------------------------------------------------
  FONCTION ALTERNATIVE D'ENVOI D'EMAIL
--------------------------------------------------------------*/
function fcp_send_email($to, $subject, $message, $headers = array())
{
    global $fcp_email_method;

    // Créer un fichier de log dans le dossier uploads
    $upload_dir = wp_upload_dir();
    $log_file = $upload_dir['basedir'] . '/rvk-email-debug-log.txt';

    // Journaliser les informations de départ
    $log_message = "=== DÉBUT DE LA TENTATIVE D'ENVOI D'EMAIL (" . date('Y-m-d H:i:s') . ") ===\n";
    $log_message .= "Destinataire: " . $to . "\n";
    $log_message .= "Sujet: " . $subject . "\n";
    $log_message .= "En-têtes: " . print_r($headers, true) . "\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);

    // Essayer d'abord avec wp_mail
    $log_message = "Tentative avec wp_mail...\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);

    $wp_mail_result = wp_mail($to, $subject, $message, $headers);

    if ($wp_mail_result) {
        $fcp_email_method = "wp_mail";
        $log_message = "wp_mail a réussi!\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);
        return true;
    } else {
        $log_message = "wp_mail a échoué. Erreur: " . (error_get_last() ? print_r(error_get_last(), true) : "Inconnue") . "\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);
    }

    // Si wp_mail échoue, essayer avec PHPMailer directement
    try {
        $log_message = "Tentative avec PHPMailer...\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);

        // Créer une instance de PHPMailer
        require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
        require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
        require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        // Activer le débogage
        $mail->SMTPDebug = 3; // Niveau de débogage
        $mail->Debugoutput = function ($str, $level) use ($log_file) {
            file_put_contents($log_file, "PHPMailer Debug: $str\n", FILE_APPEND);
        };

        // Configurer le serveur
        $mail->isMail(); // Utiliser la fonction mail() de PHP

        // Destinataires
        $mail->addAddress($to);

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        // En-têtes
        if (is_array($headers)) {
            foreach ($headers as $header) {
                if (strpos($header, 'From:') === 0) {
                    $from = trim(str_replace('From:', '', $header));
                    // Extraire l'email et le nom
                    if (preg_match('/<(.+?)>/', $from, $matches)) {
                        $email = $matches[1];
                        $name = trim(str_replace('<' . $email . '>', '', $from));
                        $mail->setFrom($email, $name);
                        $log_message = "From configuré: $email, $name\n";
                        file_put_contents($log_file, $log_message, FILE_APPEND);
                    } else {
                        $mail->setFrom($from);
                        $log_message = "From configuré: $from\n";
                        file_put_contents($log_file, $log_message, FILE_APPEND);
                    }
                } elseif (strpos($header, 'Reply-To:') === 0) {
                    $replyTo = trim(str_replace('Reply-To:', '', $header));
                    $mail->addReplyTo($replyTo);
                    $log_message = "Reply-To configuré: $replyTo\n";
                    file_put_contents($log_file, $log_message, FILE_APPEND);
                }
            }
        }

        // Envoyer l'email
        $mail->send();
        $fcp_email_method = "PHPMailer";
        $log_message = "PHPMailer a réussi!\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);
        return true;
    } catch (Exception $e) {
        $log_message = "PHPMailer a échoué. Erreur: " . $e->getMessage() . "\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);

        // Si PHPMailer échoue, essayer avec mail() directement
        $log_message = "Tentative avec mail() natif...\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);

        $header_str = '';
        if (is_array($headers)) {
            foreach ($headers as $header) {
                $header_str .= $header . "\r\n";
            }
        } else {
            $header_str = $headers;
        }

        // Envoyer avec mail()
        $mail_result = mail($to, $subject, $message, $header_str);

        if ($mail_result) {
            $fcp_email_method = "mail()";
            $log_message = "mail() a réussi!\n";
            file_put_contents($log_file, $log_message, FILE_APPEND);
        } else {
            $fcp_email_method = "échec";
            $log_message = "mail() a échoué. Erreur: " . (error_get_last() ? print_r(error_get_last(), true) : "Inconnue") . "\n";
            file_put_contents($log_file, $log_message, FILE_APPEND);
        }

        $log_message = "=== FIN DE LA TENTATIVE D'ENVOI D'EMAIL ===\n\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);

        return $mail_result;
    }
}

// Fin de fichier (pas de fermeture PHP pour éviter un espace inattendu)