<?php

function tm_openai_models_post_type()
{
    register_post_type(
        'tm_openai_models',
        array(
            'labels'      => array(
                'name'          => __('TM OpenAI Models', 'tmo'),
                'singular_name' => __('TM OpenAI Model', 'tmo'),
            ),
            'public'      => true,
            'has_archive' => false,
            'supports'            => array('title', 'editor', 'excerpt'),
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'menu_position'       => 4,
        )
    );
}
add_action('init', 'tm_openai_models_post_type');

/**
 *   metabox markup for tm_openai_models 
 */
function tm_openai_models_meta_box_markup($post)
{
    wp_nonce_field(basename(__FILE__), "checkbox_nonce");
    $checkbox_stored_meta = get_post_meta($post->ID);
?>

    <label for="tm_openai_model_sync">
        <input type="checkbox" name="tm_openai_model_sync" id="tm_openai_model_sync" value="yes" <?php if (isset($checkbox_stored_meta['tm_openai_model_sync'])) checked($checkbox_stored_meta['tm_openai_model_sync'][0], 'yes'); ?> />
        <?php _e('Sync Model', 'tmo') ?>
    </label>

<?php 
}

/**
 *  Save metabox markup per tm_openai_models 
 */
function tm_openai_models_save_custom_meta_box($post_id)
{
    // Checks save status
    $is_autosave = wp_is_post_autosave($post_id);
    $is_revision = wp_is_post_revision($post_id);
    $is_valid_nonce = (isset($_POST['checkbox_nonce']) && wp_verify_nonce($_POST['checkbox_nonce'], basename(__FILE__))) ? 'true' : 'false';

    // Exits script depending on save status
    if ($is_autosave || $is_revision || !$is_valid_nonce) {
        return;
    } 

    // Checks for input and saves
    if (isset($_POST['tm_openai_model_sync'])) {
        update_post_meta($post_id, 'tm_openai_model_sync', 'yes');
    } else {
        update_post_meta($post_id, 'tm_openai_model_sync', '');
    }
}
add_action('save_post', 'tm_openai_models_save_custom_meta_box', 10, 2);


/**
 *  Add Metabox per post/page and any registered tm_openai_models 
 */
function tm_openai_models_add_custom_meta_box()
{
    $post_types = ['tm_openai_models'];
    foreach ($post_types as $post_type) {
        add_meta_box('tm_openai_model_sync', __('Sync Created Model', 'tmo'), 'tm_openai_models_meta_box_markup', $post_types, 'side', 'default', null);
    }
}
add_action('add_meta_boxes', 'tm_openai_models_add_custom_meta_box');


function tm_openai_models_disable_new_posts()
{
    // Hide sidebar link
    global $submenu;
    unset($submenu['edit.php?post_type=tm_openai_models'][10]);
}
add_action('admin_menu', 'tm_openai_models_disable_new_posts');

 
function tm_create_model()
{
    $title = get_option('tm_openai_model_name');
    $found_title = get_page_by_title($title, 'OBJECT', 'tm_openai_models');
    if ( get_option('tm_openai_model_file_link') && !$found_title) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/files');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
 
        $uploads_dir = wp_upload_dir();
        $file_path = str_replace($uploads_dir['baseurl'], $uploads_dir['basedir'], get_option('tm_openai_model_file_link'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'purpose' => 'fine-tune',
            'file' => new CURLFile($file_path),
        ]);

        $headers = array();
        $headers[] = 'Content-Type: multipart/form-data';
        $headers[] = 'Authorization: Bearer '. get_option('tm_openai_key');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        
        $new_model = array(
            'post_title'    =>  $title,
            'post_content'  =>  $response,
            'post_status'   => 'pending',
            'post_type'     => 'tm_openai_models'
        );
        // Insert the post into the database
        $model_id = wp_insert_post($new_model);


        if (!empty($model_id) ) {
            $model_response = get_the_content(null,  false, $model_id);
            if (isset($model_response)) {
                $training_file_id = json_decode($model_response)->id;
                if (isset($training_file_id)) {
                    $ch_create_model = curl_init();

                    curl_setopt($ch_create_model, CURLOPT_URL, 'https://api.openai.com/v1/fine-tunes');
                    curl_setopt($ch_create_model, CURLOPT_RETURNTRANSFER, 1); 
                    curl_setopt($ch_create_model, CURLOPT_POSTFIELDS, "{\n  \"model\": \"davinci\",\n  \"training_file\": \"$training_file_id\"\n}");
//                     curl_setopt($ch, CURLOPT_POSTFIELDS, '{ "model": "text-davinci-002","training_file": "'.$training_file_id.'"}');
                    curl_setopt($ch_create_model, CURLOPT_POST, 1);

                    $headers = array();
                    $headers[] = 'Content-Type: application/json';
                    $headers[] = 'Authorization: Bearer '.get_option('tm_openai_key');
                    curl_setopt($ch_create_model, CURLOPT_HTTPHEADER, $headers);

                    $new_response = curl_exec($ch_create_model);

                    if (curl_errno($ch_create_model)) {
                        echo 'Error:' . curl_error($ch_create_model);
                    }
                    curl_close($ch_create_model);
                    wp_update_post([
                        'ID'           => $model_id, 
                        'post_content' => $new_response,
                        'post_status'   => 'publish'
                    ]);
                } 
                update_option('tm_openai_model_name', "");
                update_option('tm_openai_model_file_link', "");
            }
//             wp_safe_redirect(site_url() . '/wp-admin/post.php?post=' . $model_id . '&action=edit');
//             exit;
        }
    }
}
add_action('added_option', 'tm_create_model', 10, 2);

function tm_openai_publish_openai_model($new_status, $old_status, $post)
{
    $tm_openai_model_sync = get_post_meta($post->ID, 'tm_openai_model_sync', true);

    if (('publish' === $new_status && 'publish' === $old_status) && 'tm_openai_models' === $post->post_type && $tm_openai_model_sync === 'yes') {
        $old_response = $post->post_content;
        $fine_tune_model_id = json_decode($old_response)->id;
        if (isset($fine_tune_model_id)) {
            $ch_update_model = curl_init();
            curl_setopt($ch_update_model, CURLOPT_URL, 'https://api.openai.com/v1/fine-tunes/' . $fine_tune_model_id);
            curl_setopt($ch_update_model, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch_update_model, CURLOPT_CUSTOMREQUEST, 'GET');

            $headers = array();
            $headers[] = 'Authorization: Bearer '.get_option('tm_openai_key');
            curl_setopt($ch_update_model, CURLOPT_HTTPHEADER, $headers);

            $new_response = curl_exec($ch_update_model);
            if (curl_errno($ch_update_model)) {
                echo 'Error:' . curl_error($ch_update_model);
            }

            curl_close($ch_update_model);
            $fine_tuned_model = json_decode($old_response)->fine_tuned_model;
            // We unhook this action to prevent an infinite loop
            remove_action('transition_post_status', 'tm_openai_publish_openai_model');

            wp_update_post([
                'ID'           => $post->ID,
                'post_excerpt'   => isset($fine_tuned_model) ? $fine_tuned_model : "",
                'post_content' => $new_response
            ]);
 
        }
    }
    return;
}
add_action('transition_post_status', 'tm_openai_publish_openai_model', 10, 3);
 


function tm_openai_model_custom_setting()
{
    add_settings_section(
        'tm_openai_plugin_model_options',
        '',
        'tm_openai_plugin_model_options',
        'tm_openai_plugin_model_options'
    );

    add_settings_field(
        'tm_openai_model_name',
        __('Open AI Model Name', 'tmo'),
        'tm_openai_model_name_callback',
        'tm_openai_plugin_model_options',
        'tm_openai_plugin_model_options',
        array(
            'tm_openai_model_name'
        )
    ); 

    add_settings_field(
        'tm_openai_model_file_link',
        __('Open AI Model File Link', 'tmo'),
        'tm_openai_model_file_link_callback',
        'tm_openai_plugin_model_options',
        'tm_openai_plugin_model_options',
        array(
            'tm_openai_model_file_link'
        )
    ); 
 
    register_setting("tm_openai_plugin_model_options", "tm_openai_model_file_link");
    register_setting("tm_openai_plugin_model_options", "tm_openai_model_name");
}
add_action('admin_init', 'tm_openai_model_custom_setting');

function tm_openai_plugin_model_options()
{
?>
    <div class="tm_openai_buttons">
        <ul>
            <li>
                <a href="<?php echo site_url(); ?>/wp-admin/admin.php?page=tm_openai_plugin_options_page">Open AI Settings</a>
            </li>
            <li>
                <a href="<?php echo site_url(); ?>/wp-admin/edit.php?post_type=tm_openai_models">All Open AI Models</a>
            </li>
        </ul>
    </div>
<?php
} 

function tm_openai_model_name_callback($args)
{
    $option = get_option($args[0]); ?>
    <label for="<?php echo $args[0]; ?>">
        <input type="text" class="form-control regular-text" id="<?php echo $args[0]; ?>" name="<?php echo $args[0]; ?>" value="<?php echo $option; ?>" />
        <em>Name of the model for your reference.</em>
    </label>
<?php
}

function tm_openai_model_file_link_callback($args)
{
    $option = get_option($args[0]); ?>
    <label for="<?php echo $args[0]; ?>">
        <input type="text" class="form-control regular-text" id="<?php echo $args[0]; ?>" name="<?php echo $args[0]; ?>" value="<?php echo $option; ?>" />
        <em>Upload a jsonl file from WordPress media uploader and place the link here.</em>
    </label>
<?php
}

function tm_openai_model_admin_menu()
{
    $page_title = __('TM Open AI Model Create', 'tmo');
    $menu_title = __('TM Open AI Model Create', 'tmo');
    $capability = 'edit_posts';
    $menu_slug = 'tm_openai_plugin_model_options_page';
    $function = 'tm_openai_plugin_model_options_page';
    $icon_url = '';
    $position = 3;

    add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);
}

add_action('admin_menu', 'tm_openai_model_admin_menu');

function tm_openai_plugin_model_options_page()
{
?>
    <div class="container ">
        <div class="row">
            <div class="col-md-12">
                <div class="wrap">
                    <h1 class="wp-heading-inline mb-3"><?php _e('TM Open AI Model Create', 'tmo'); ?></h1>
                    <hr>
                </div>

                <?php settings_errors(); ?>

                <form method="post" action="options.php" enctype="multipart/form-data">
                    <?php settings_fields("tm_openai_plugin_model_options"); ?>
                    <?php do_settings_sections('tm_openai_plugin_model_options') ?>
                    <?php submit_button('Create Model', 'secondary'); ?>
                </form>
            </div>
        </div>
    </div>

<?php
}
 