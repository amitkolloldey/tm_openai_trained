<?php 
add_action('admin_init', 'tm_openai_custom_setting');
function tm_openai_custom_setting()
{
    add_settings_section(
        'tm_openai_plugin_options',
        '',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options'
    );

	// Keys
    add_settings_field(
        'tm_openai_amazon_access_key',
        __('Amazon Access Key', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_amazon_access_key'
        )
    );
    add_settings_field(
        'tm_openai_amazon_secret_key',
        __('Amazon Secret Key', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_amazon_secret_key'
        )
    );
    add_settings_field(
        'tm_openai_amazon_partner_tag',
        __('Amazon Partner Tag', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_amazon_partner_tag'
        )
    );
    add_settings_field(
        'tm_openai_number_of_products',
        __('Default Number Of Products From Amazon (Optional)', 'tmo'),
        'tm_openai_number_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_number_of_products'
        )
    );
    add_settings_field(
        'tm_openai_amazon_host',
        __('Amazon Host (Optional)', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_amazon_host'
        )
    );
    add_settings_field(
        'tm_openai_amazon_region',
        __('Amazon Region (Optional)', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_amazon_region'
        )
    );
    add_settings_field(
        'tm_openai_key',
        __('Open AI Key', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_key'
        )
    );

	
	// Turn Off
    add_settings_field(
        'tm_openai_introduction_turn_off',
        __('Turn Off Introduction', 'tmo'),
        'tm_openai_turn_off_select_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_introduction_turn_off'
        )
    );
    add_settings_field(
        'tm_openai_before_amazon_list_turn_off',
        __('Before Amazon List Turn Off', 'tmo'),
        'tm_openai_turn_off_select_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_before_amazon_list_turn_off'
        )
    );
    add_settings_field(
        'tm_openai_review_turn_off',
        __('Turn Off Review', 'tmo'),
        'tm_openai_turn_off_select_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_review_turn_off'
        )
    ); 
    add_settings_field(
        'tm_openai_feature_turn_off',
        __('Turn Off Product Feature', 'tmo'),
        'tm_openai_turn_off_select_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_feature_turn_off'
        )
    ); 	
    add_settings_field(
        'tm_openai_additional_info_turn_off',
        __('Turn Off Additional Info', 'tmo'),
        'tm_openai_turn_off_select_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_additional_info_turn_off'
        )
    ); 
    add_settings_field(
        'tm_openai_buy_guide_turn_off',
        __('Turn Off Buy Guide', 'tmo'),
        'tm_openai_turn_off_select_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_buy_guide_turn_off'
        )
    );
    add_settings_field(
        'tm_openai_faq_turn_off',
        __('Turn Off FAQs', 'tmo'),
        'tm_openai_turn_off_select_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_faq_turn_off'
        )
    );
    add_settings_field(
        'tm_openai_conclusion_turn_off',
        __('Turn Off Conclusion', 'tmo'),
        'tm_openai_turn_off_select_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_conclusion_turn_off'
        )
    ); 

	
	// Model Select
    add_settings_field(
        'tm_openai_model_for_introduction',
        __('Open AI Fine Tuned Model For Introduction', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_model_for_introduction'
        )
    ); 
    add_settings_field(
        'tm_openai_model_for_before_amazon_list',
        __('Open AI Fine Tuned Model Before Amazon List', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_model_for_before_amazon_list'
        )
    );	
    add_settings_field(
        'tm_openai_model_for_review',
        __('Open AI Fine Tuned Model For Review', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_model_for_review'
        )
    );
    add_settings_field(
        'tm_openai_model_for_generating_question',
        __('Open AI Fine Tuned Model For Generating Questions', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_model_for_generating_question'
        )
    );
    add_settings_field(
        'tm_openai_model_for_get_answer',
        __('Open AI Fine Tuned Model For Generating Answer', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_model_for_get_answer'
        )
    );
    add_settings_field(
        'tm_openai_model_for_buy_guide',
        __('Open AI Fine Tuned Model For Buy Guide', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_model_for_buy_guide'
        )
    );
    add_settings_field(
        'tm_openai_model_for_conclusion',
        __('Open AI Fine Tuned Model For Conclusion', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_model_for_conclusion'
        )
    );



	// Prompt Text
    add_settings_field(
        'tm_openai_prompt_text_for_introduction',
        __('Open AI Prompt Text For Introduction', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_prompt_text_for_introduction'
        )
    );
	 add_settings_field(
        'tm_openai_prompt_text_for_before_amazon_list',
        __('Open AI Prompt Text For Before Amazon List', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_prompt_text_for_before_amazon_list'
        )
    );
    add_settings_field(
        'tm_openai_prompt_text_for_review',
        __('Open AI Prompt Text For Product Review', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_prompt_text_for_review'
        )
    );
	 add_settings_field(
        'tm_openai_prompt_text_for_buy_guide',
        __('Open AI Prompt Text For Buy Guide', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_prompt_text_for_buy_guide'
        )
    );		
    add_settings_field(
        'tm_openai_prompt_text_for_generating_question',
        __('Open AI Prompt Text For Generating Questions', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_prompt_text_for_generating_question'
        )
    );
	 add_settings_field(
        'tm_openai_prompt_text_for_buy_guide',
        __('Open AI Prompt Text For Buy Guide', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_prompt_text_for_buy_guide'
        )
    );	
    add_settings_field(
        'tm_openai_prompt_text_for_conclusion',
        __('Open AI Prompt Text For Conclusion', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_prompt_text_for_conclusion'
        )
    );

 	// Text Length
    add_settings_field(
        'tm_openai_text_length_for_introduction',
        __('Open AI Text Length For Introduction', 'tmo'),
        'tm_openai_number_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_text_length_for_introduction'
        )
    );
    add_settings_field(
        'tm_openai_text_length_for_before_amazon_list',
        __('Open AI Text Length For Before Amazon List', 'tmo'),
        'tm_openai_number_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_text_length_for_before_amazon_list'
        )
    );	
    add_settings_field(
        'tm_openai_text_length_for_review',
        __('Open AI Text Length For Review', 'tmo'),
        'tm_openai_number_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_text_length_for_review'
        )
    );
    add_settings_field(
        'tm_openai_text_length_for_buy_guide',
        __('Open AI Text Length For Buy Guide', 'tmo'),
        'tm_openai_number_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_text_length_for_buy_guide'
        )
    );		
    add_settings_field(
        'tm_openai_text_length_for_generating_question',
        __('Open AI Text Length For Generating Questions', 'tmo'),
        'tm_openai_number_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_text_length_for_generating_question'
        )
    );
    add_settings_field(
        'tm_openai_text_length_for_get_answer',
        __('Open AI Text Length For Generating Answer', 'tmo'),
        'tm_openai_number_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_text_length_for_get_answer'
        )
    );
    add_settings_field(
        'tm_openai_text_length_for_conclusion',
        __('Open AI Text Length For Conclusion', 'tmo'),
        'tm_openai_number_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_text_length_for_conclusion'
        )
    );


	// Temperature
    add_settings_field(
        'tm_openai_temperature_for_introduction',
        __('Open AI Temperature For Introduction', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_temperature_for_introduction'
        )
    );
    add_settings_field(
        'tm_openai_temperature_for_before_amazon_list',
        __('Open AI Temperature For Before Amazon List', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_temperature_for_before_amazon_list'
        )
    );	
    add_settings_field(
        'tm_openai_temperature_for_review',
        __('Open AI Temperature For Review', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_temperature_for_review'
        )
    );
    add_settings_field(
        'tm_openai_temperature_for_buy_guide',
        __('Open AI Temperature For Buy Guide', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_temperature_for_buy_guide'
        )
    );		
    add_settings_field(
        'tm_openai_temperature_for_generating_question',
        __('Open AI Temperature For Generating Questions', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_temperature_for_generating_question'
        )
    );
    add_settings_field(
        'tm_openai_temperature_for_get_answer',
        __('Open AI Temperature For Generating Answer', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_temperature_for_get_answer'
        )
    );
    add_settings_field(
        'tm_openai_temperature_for_conclusion',
        __('Open AI Temperature For Conclusion', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_temperature_for_conclusion'
        )
    );


	// Title 
    add_settings_field(
        'tm_openai_conclusion_section_title',
        __('Conclusion Section Title', 'tmo'),
        'tm_openai_text_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_conclusion_section_title'
        )
    );


    register_setting("tm_openai_plugin_options", "tm_openai_amazon_access_key");
    register_setting("tm_openai_plugin_options", "tm_openai_amazon_secret_key");
    register_setting("tm_openai_plugin_options", "tm_openai_amazon_partner_tag");
    register_setting("tm_openai_plugin_options", "tm_openai_number_of_products");
    register_setting("tm_openai_plugin_options", "tm_openai_amazon_host");
    register_setting("tm_openai_plugin_options", "tm_openai_amazon_region");
    register_setting("tm_openai_plugin_options", "tm_openai_key");

    register_setting("tm_openai_plugin_options", "tm_openai_introduction_turn_off"); 
    register_setting("tm_openai_plugin_options", "tm_openai_before_amazon_list_turn_off"); 
    register_setting("tm_openai_plugin_options", "tm_openai_feature_turn_off");
    register_setting("tm_openai_plugin_options", "tm_openai_additional_info_turn_off");
	register_setting("tm_openai_plugin_options", "tm_openai_review_turn_off");
    register_setting("tm_openai_plugin_options", "tm_openai_buy_guide_turn_off");
    register_setting("tm_openai_plugin_options", "tm_openai_faq_turn_off");
    register_setting("tm_openai_plugin_options", "tm_openai_conclusion_turn_off");

    register_setting("tm_openai_plugin_options", "tm_openai_model_for_introduction");
	register_setting("tm_openai_plugin_options", "tm_openai_model_for_before_amazon_list"); 
    register_setting("tm_openai_plugin_options", "tm_openai_model_for_review");
	register_setting("tm_openai_plugin_options", "tm_openai_model_for_buy_guide"); 
    register_setting("tm_openai_plugin_options", "tm_openai_model_for_generating_question");
    register_setting("tm_openai_plugin_options", "tm_openai_model_for_get_answer");
    register_setting("tm_openai_plugin_options", "tm_openai_model_for_conclusion");

    register_setting("tm_openai_plugin_options", "tm_openai_prompt_text_for_introduction");
	register_setting("tm_openai_plugin_options", "tm_openai_prompt_text_for_before_amazon_list"); 
    register_setting("tm_openai_plugin_options", "tm_openai_prompt_text_for_review");
	register_setting("tm_openai_plugin_options", "tm_openai_prompt_text_for_buy_guide"); 
    register_setting("tm_openai_plugin_options", "tm_openai_prompt_text_for_generating_question");
    register_setting("tm_openai_plugin_options", "tm_openai_prompt_text_for_conclusion");

    register_setting("tm_openai_plugin_options", "tm_openai_text_length_for_introduction");
	register_setting("tm_openai_plugin_options", "tm_openai_text_length_for_before_amazon_list");
    register_setting("tm_openai_plugin_options", "tm_openai_text_length_for_review");
	register_setting("tm_openai_plugin_options", "tm_openai_text_length_for_buy_guide"); 
    register_setting("tm_openai_plugin_options", "tm_openai_text_length_for_generating_question");
    register_setting("tm_openai_plugin_options", "tm_openai_text_length_for_get_answer");
    register_setting("tm_openai_plugin_options", "tm_openai_text_length_for_conclusion");

    register_setting("tm_openai_plugin_options", "tm_openai_temperature_for_introduction");
	register_setting("tm_openai_plugin_options", "tm_openai_temperature_for_before_amazon_list");
    register_setting("tm_openai_plugin_options", "tm_openai_temperature_for_review");
	register_setting("tm_openai_plugin_options", "tm_openai_temperature_for_buy_guide"); 
    register_setting("tm_openai_plugin_options", "tm_openai_temperature_for_generating_question");
    register_setting("tm_openai_plugin_options", "tm_openai_temperature_for_get_answer");
    register_setting("tm_openai_plugin_options", "tm_openai_temperature_for_conclusion");
  
    register_setting("tm_openai_plugin_options", "tm_openai_conclusion_section_title");


    add_settings_field(
        'tm_openai_number_of_fields',
        __('Genarate Number Of Additional Fields', 'tmo'),
        'tm_openai_number_field_callback',
        'tm_openai_plugin_options',
        'tm_openai_plugin_options',
        array(
            'tm_openai_number_of_fields'
        )
    );

    register_setting("tm_openai_plugin_options", "tm_openai_number_of_fields");

    $number_of_fields_count = 1;
    $length = get_option('tm_openai_number_of_fields') ? intval(get_option('tm_openai_number_of_fields')) : 2;

    while ($number_of_fields_count <= $length) {
        add_settings_field(
            'tm_openai_before_title_' . $number_of_fields_count,
            __('Before Title ' . $number_of_fields_count . ' (Optional)', 'tmo'),
            'tm_openai_text_field_callback',
            'tm_openai_plugin_options',
            'tm_openai_plugin_options',
            array(
                'tm_openai_before_title_' . $number_of_fields_count
            )
        );

        add_settings_field(
            'tm_openai_after_title_' . $number_of_fields_count,
            __('After Title ' . $number_of_fields_count . ' (Optional)', 'tmo'),
            'tm_openai_text_field_callback',
            'tm_openai_plugin_options',
            'tm_openai_plugin_options',
            array(
                'tm_openai_after_title_' . $number_of_fields_count
            )
        );
		
		add_settings_field(
			'tm_openai_before_amazon_list_section_title_' . $number_of_fields_count,
			__('Before List Section Title ' . $number_of_fields_count , 'tmo'),
			'tm_openai_text_field_callback',
			'tm_openai_plugin_options',
			'tm_openai_plugin_options',
			array(
				'tm_openai_before_amazon_list_section_title_' . $number_of_fields_count
			)
		); 
		add_settings_field(
			'tm_openai_buy_guide_section_title_' . $number_of_fields_count,
			__('Buy Guide Section Title '. $number_of_fields_count, 'tmo'),
			'tm_openai_text_field_callback',
			'tm_openai_plugin_options',
			'tm_openai_plugin_options',
			array(
				'tm_openai_buy_guide_section_title_' . $number_of_fields_count
			)
		);
		add_settings_field(
			'tm_openai_faq_section_title_'. $number_of_fields_count,
			__('FAQs Section Title '. $number_of_fields_count, 'tmo'),
			'tm_openai_text_field_callback',
			'tm_openai_plugin_options',
			'tm_openai_plugin_options',
			array(
				'tm_openai_faq_section_title_' . $number_of_fields_count
			)
		); 
 
        register_setting("tm_openai_plugin_options", "tm_openai_before_title_" . $number_of_fields_count);
        register_setting("tm_openai_plugin_options", "tm_openai_after_title_" . $number_of_fields_count);
		register_setting('tm_openai_plugin_options', 'tm_openai_before_amazon_list_section_title_' . $number_of_fields_count); 
        register_setting('tm_openai_plugin_options', 'tm_openai_buy_guide_section_title_' . $number_of_fields_count); 
		register_setting('tm_openai_plugin_options', 'tm_openai_faq_section_title_' . $number_of_fields_count); 
        $number_of_fields_count++;
    }
}

function tm_openai_plugin_options()
{
?>
    <div class="tm_openai_buttons">
        <ul>
            <li>
                <a href="<?php echo site_url(); ?>/wp-admin/admin.php?page=tm_openai_plugin_model_options_page">Create New Model</a>
            </li>
            <li>
                <a href="<?php echo site_url(); ?>/wp-admin/edit.php?post_type=tm_openai_models">All Open AI Models</a>
            </li>
        </ul>
    </div>

    <div class="tm_openai_suggestions">
        <strong>Examples:</strong>
        <pre>Amazon Region: us-east-1</pre>
        <pre>Prompt: Write a product introduction for the keyword: [keyword or keyword_stripped]</pre>
        <pre>Text Length: 500</pre>
        <pre>Temperature: .7</pre>
    </div>
<?php
}

function tm_openai_text_field_callback($args)
{
    $option = get_option($args[0]); ?>
    <label for="<?php echo $args[0]; ?>">
        <input type="text" class="form-control regular-text" id="<?php echo $args[0]; ?>" name="<?php echo $args[0]; ?>" value="<?php echo $option; ?>" />
    </label>
<?php
}

function tm_openai_number_field_callback($args)
{
    $option = get_option($args[0]); ?>
    <label for="<?php echo $args[0]; ?>">
        <input type="number" class="form-control regular-text" id="<?php echo $args[0]; ?>" name="<?php echo $args[0]; ?>" value="<?php echo $option; ?>" />
    </label>
<?php
}

function tm_openai_editor_field_callback($args)
{
    $option = get_option($args[0]);
    $editor_id = $args[0];
    wp_editor($option, $editor_id);
    echo "<small>[replace_title], [replace_keyword], [replace_sitelink], [replace_sitename] will be replaced accordingly.</small>";
}


function tm_openai_turn_off_select_field_callback($args)
{
    $option = get_option($args[0]);
?>
    <select class="form-control regular-text" name="<?php echo $args[0]; ?>" id="<?php echo $args[0]; ?>">
        <option value="No" <?php echo $option && $option == 'No' ? 'selected' : ''; ?>>No</option>
        <option value="Yes" <?php echo $option && $option == 'Yes' ? 'selected' : ''; ?>>Yes</option>
    </select>
    <em>Select Yes To Turn Off</em>
<?php
}

function tm_openai_model_select_field_callback($args)
{
    $option = get_option($args[0]);

    // Create our arguments for getting our post
    $post_args = [
        'post_type' => 'tm_openai_models'
    ];

    // we get an array of posts objects
    $posts = get_posts($post_args);

    // start our string
    $str = '<select class="form-control regular-text" name="' . $args[0] . '" id="' . $args[0] . '" >';


    // then we create an option for each post
    foreach ($posts as $key => $post) {
        $selected = !empty($option) && $option === $post->post_title ? 'selected' : '';
        $str .= '<option value="' . $post->post_title . '" ' . $selected . '>' . $post->post_title . '</option>';
    }
    $str .= '</select>';
    echo $str;
?>

    <em>Select Open AI Model</em>
<?php
}


function tm_openai_admin_menu()
{
    $page_title = __('TM Open AI Settings', 'tmo');
    $menu_title = __('TM Open AI Settings', 'tmo');
    $capability = 'edit_posts';
    $menu_slug = 'tm_openai_plugin_options_page';
    $function = 'tm_openai_plugin_settings_page';
    $icon_url = '';
    $position = 2;

    add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);
}

add_action('admin_menu', 'tm_openai_admin_menu');

function tm_openai_plugin_settings_page()
{
?>
    <div class="container ">
        <div class="row">
            <div class="col-md-12">
                <div class="wrap">
                    <h1 class="wp-heading-inline mb-3"><?php _e('TM Open AI Settings', 'tmo'); ?></h1>
                    <hr>
                </div>
                <?php settings_errors(); ?>
                <form method="post" action="options.php" enctype="multipart/form-data">
                    <?php settings_fields("tm_openai_plugin_options"); ?>
                    <?php do_settings_sections('tm_openai_plugin_options') ?>
                    <?php submit_button(); ?>
                </form>
            </div>
        </div>
    </div>
<?php
}