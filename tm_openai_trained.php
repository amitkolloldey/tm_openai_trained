<?php
/*
Plugin Name: TM Open AI Trained
Description: Custom Plugin To Generate Bulk Posts with Amazon Products From Given Keywords by importing CSV file and generating content using Open AI.
Author: 3 Marketers 
Text Domain: TM
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
Version: 1.0.0
*/

use Amazon\ProductAdvertisingAPI\v1\ApiException;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\api\DefaultApi;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\ProductAdvertisingAPIClientException;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsRequest;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsResource;
use Amazon\ProductAdvertisingAPI\v1\Configuration;

require_once(__DIR__ . '/vendor/autoload.php'); // change path as needed
require_once dirname(__FILE__) . '/openai_post_settings.php';
require_once dirname(__FILE__) . '/openai_create_model.php';
require_once dirname(__FILE__) . '/inc/prevent_lazy_load.php';
require_once dirname(__FILE__) . '/open_ai_functions.php';
require_once dirname(__FILE__) . '/helpers.php';
add_action('wp_enqueue_scripts', 'tm_openai_callback_for_setting_up_scripts');
function tm_openai_callback_for_setting_up_scripts()
{
    wp_register_style('tm_openai_main_css', plugins_url('css/main.css', __FILE__));
    wp_enqueue_style('tm_openai_main_css');
}


add_action('admin_enqueue_scripts', 'tm_openai_add_admin_scripts');
function tm_openai_add_admin_scripts($hook)
{
    if ($hook !== 'post-new.php' && $hook !== 'post.php') {
        return;
    }
    wp_enqueue_script('media-upload');
    wp_enqueue_script('thickbox');
    wp_enqueue_script('tm_openai_admin_scripts', plugins_url('/tm_openai_trained/js/main.js'), array('jquery', 'media-upload', 'thickbox'), 1.1, true);
    wp_enqueue_style('thickbox');
}

function tm_openai_map_unrestricted_upload_filter($caps, $cap)
{
    if ($cap == 'unfiltered_upload') {
        $caps = array();
        $caps[] = $cap;
    }

    return $caps;
}

add_filter('map_meta_cap', 'tm_openai_map_unrestricted_upload_filter', 0, 2);

function tm_openai_enqueue_styles()
{
?>
    <style type="text/css">
        .tm_openai_buttons ul li {
            display: inline-block;
            margin: 0;
        }

        .tm_openai_buttons ul li a {
            padding: 10px;
            background: #2271b1;
            color: #fff;
            text-decoration: none;
            margin: 10px;
            display: inline-block;
        }

        .tm_openai_buttons ul {
            border-bottom: 1px solid #ccc;
        }
    </style>
<?php
}

add_action('admin_head', 'tm_openai_enqueue_styles');

function tm_openai_enqueue_scripts()
{
?>
    <style>
        .amazon_content h1,
        .amazon_content h2,
        .amazon_content h3 {
            text-transform: capitalize !important;
        }

        .wp-post-image {
            width: auto !important;
            height: auto !important;
        }
    </style>
    <script type="application/javascript">
        jQuery(document).ready(function() {
            jQuery('.amazon_wrapper').find('a.aawp-product__title').replaceWith(function() {
                return '<h3>' + jQuery(this).text() + '</h3>';
            });
        });
    </script>
<?php
}
add_action('wp_head', 'tm_openai_enqueue_scripts');


if (!defined('WP_LOAD_IMPORTERS'))
    return;

// Load Importer API
require_once ABSPATH . 'wp-admin/includes/import.php';

if (!class_exists('WP_Importer')) {
    $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
    if (file_exists($class_wp_importer))
        require_once $class_wp_importer;
}

/**
 * CSV Importer
 *
 * @package WordPress
 * @subpackage Importer
 */
if (class_exists('WP_Importer')) {
    class tm_openai_CSV_Importer extends WP_Importer
    {

        function header()
        {
            echo '<div class="wrap">';
            echo '<h2>' . __('Import CSV', 'tmo') . '</h2>';
        }

        function footer()
        {
            echo '</div>';
        }

        function greet()
        {
            echo '<p>' . __('Choose a CSV (.csv) file to upload, then click Upload file and import.', 'tmo') . '</p>';
            echo '<p>' . __('Excel-style CSV file is unconventional and not recommended. LibreOffice has enough export options and recommended for most users.', 'tmo') . '</p>';
            echo '<p>' . __('Requirements:', 'tmo') . '</p>';
            echo '<ol>';
            echo '<li>' . __('Select UTF-8 as charset.', 'tmo') . '</li>';
            echo '<li>' . sprintf(__('You must use field delimiter as "%s"', 'tmo'), ',') . '</li>';
            echo '<li>' . __('You must quote all text cells.', 'tmo') . '</li>';
            echo '</ol>';
            echo '<p>' . __('Download example CSV files:', 'tmo');
            echo ' <a href="' . plugin_dir_url(__FILE__) . 'sample/sample.csv">' . __('csv', 'tmo') . '</a>,';
            echo ' <a href="' . plugin_dir_url(__FILE__) . 'sample/sample.ods">' . __('ods', 'tmo') . '</a>';
            echo ' ' . __('(OpenDocument Spreadsheet file format for LibreOffice. Please export as csv before import)', 'tmo');
            echo '</p>';
            wp_import_upload_form(add_query_arg('step', 1));
        }

        // Step 2
        function tm_openai_import()
        {
            $file = wp_import_handle_upload();

            if (isset($file['error'])) {
                echo '<p><strong>' . __('Sorry, there has been an error.', 'tmo') . '</strong><br />';
                echo esc_html($file['error']) . '</p>';
                return false;
            }

            $this->id = (int)$file['id'];
            $this->file = get_attached_file($this->id);
            $result = $this->tm_openai_process_posts();
            if (is_wp_error($result))
                return $result;
        }

        function tm_openai_process_posts()
        {

            $config = new Configuration();

            # Please add your access key here
            $access_key = get_option('tm_openai_amazon_access_key');

            # Please add your secret key here
            $secret_key = get_option('tm_openai_amazon_secret_key');

            # Please add your partner tag (store/tracking id) here
            $partner_tag = get_option('tm_openai_amazon_partner_tag');

            $host = get_option('tm_openai_amazon_host');

            $region = get_option('tm_openai_amazon_region');

            $tm_openai_number_of_fields = get_option('tm_openai_number_of_fields');
            $tm_openai_number_of_products = get_option('tm_openai_number_of_products');

            if (empty($tm_openai_number_of_fields)) {
                echo '<p><strong>' . __('Error: Number Of Fields Needs to be filled from the Settings.', 'tmo') . '</strong></p>';
                wp_import_cleanup($this->id);
                return false;
            }

            if (empty($tm_openai_number_of_products)) {
                echo '<p><strong>' . __('Error: Number Of Products Needs to be filled from the Settings.', 'tmo') . '</strong></p>';
                wp_import_cleanup($this->id);
                return false;
            }

            if (!empty($access_key) && !empty($secret_key) && !empty($partner_tag)) {

                $config->setAccessKey($access_key);
                $config->setSecretKey($secret_key);

                $config->setHost($host ? $host : 'webservices.amazon.com');
                $config->setRegion($region ? $region : 'us-east-1');

                $handle = fopen($this->file, 'r');
                if ($handle == false) {
                    echo '<p><strong>' . __('Failed to open file.', 'tmo') . '</strong></p>';
                    wp_import_cleanup($this->id);
                    return false;
                }

                $flag = true;
                echo '<ol>';

                while (($line = fgetcsv($handle)) !== FALSE) { 

                    if ($flag) {
                        $flag = false;
                        continue;
                    } else {
						$keyword = $line && $line[0] ? $line[0] : null; // Keyword 
						$new_keyword = strtolower(trim($keyword));
						$first_word_check = strtok($new_keyword, " ");
						$keyword_stripped = $first_word_check === 'best' ? str_replace($first_word_check, '', $new_keyword) : $new_keyword; 
						$introduction_prompt = tm_open_ai_replace_with_keyword_and_keyword_stripped($keyword, get_option('tm_openai_prompt_text_for_introduction')); 
						$before_amazon_list_prompt = tm_open_ai_replace_with_keyword_and_keyword_stripped($keyword, get_option('tm_openai_prompt_text_for_before_amazon_list')); 
						$buy_guide_prompt = tm_open_ai_replace_with_keyword_and_keyword_stripped($keyword, get_option('tm_openai_prompt_text_for_buy_guide')); 
						$conclusion_prompt = tm_open_ai_replace_with_keyword_and_keyword_stripped($keyword, get_option('tm_openai_prompt_text_for_conclusion'));
						
                        $number = $line && $line[1] ? $line[1] : $tm_openai_number_of_products; // Number 
                        $introduction = get_option('tm_openai_introduction_turn_off') && get_option('tm_openai_introduction_turn_off') == 'No' ? tm_open_ai_generate_introduction($introduction_prompt) : null;
                        $before_amazon_list_content = get_option('tm_openai_before_amazon_list_turn_off') && get_option('tm_openai_before_amazon_list_turn_off') == 'No' ? tm_open_ai_generate_before_amazon_list($before_amazon_list_prompt) : null; 
					    $buy_guide = get_option('tm_openai_buy_guide_turn_off') && get_option('tm_openai_buy_guide_turn_off') == 'No' ? tm_open_ai_generate_buy_guide($buy_guide_prompt) : null;
                        $conclusion = get_option('tm_openai_conclusion_turn_off') && get_option('tm_openai_conclusion_turn_off') == 'No' ? tm_open_ai_generate_conclusion($conclusion_prompt) : null;
                        $qnas = get_option('tm_openai_faq_turn_off') && get_option('tm_openai_faq_turn_off') == 'No' ? tm_open_ai_generate_qna($keyword) : null;

                        if ($keyword && !tm_openai_get_post_id_by_slug($keyword)) {

                            $apiInstance = new DefaultApi(
                                new GuzzleHttp\Client(),
                                $config
                            );

                            $searchIndex = "All";

                            $itemCount = intval($number);

                            $resources = [
                                SearchItemsResource::ITEM_INFOTITLE,
                                SearchItemsResource::ITEM_INFOFEATURES,
                                SearchItemsResource::ITEM_INFOPRODUCT_INFO,
                                SearchItemsResource::ITEM_INFOTECHNICAL_INFO,
                                SearchItemsResource::IMAGESPRIMARYLARGE
                            ];

                            # Forming the request
                            $searchItemsRequest = new SearchItemsRequest();

                            $searchItemsRequest->setKeywords($keyword);
                            $searchItemsRequest->setItemCount($itemCount);
                            $searchItemsRequest->setPartnerTag($partner_tag);
                            $searchItemsRequest->setPartnerType(PartnerType::ASSOCIATES);
                            $searchItemsRequest->setResources($resources);

                            $searchItemsResponse = $apiInstance->searchItems($searchItemsRequest);

                            $items = !empty($searchItemsResponse->getSearchResult()) && !empty($searchItemsResponse->getSearchResult()->getItems()) ? $searchItemsResponse->getSearchResult()->getItems() : null;
                            $rand = rand(1, $tm_openai_number_of_fields);
                            if (!empty($items)) {
                                $featured_image_url = $items[0]->getImages()->getPrimary()->getLarge()->getURL();
                                $before_title_text = trim(get_option('tm_openai_before_title_' . $rand));
                                $after_title_text = trim(get_option('tm_openai_after_title_' . $rand));

                                $predefined_title = ($after_title_text && $before_title_text) ? $before_title_text . ' ' . ucwords($keyword) . ' ' . $after_title_text : $number . ' ' . ucwords($keyword) . ' of ' . date("Y"); 
								
                                $title = $line && $line[2] ? $line[2] : $predefined_title; // title 

                                $category = $line && $line[3] ? $line[3] : 'Uncategorized'; // Category

                                $fount_post = post_exists($title);

                                $get_category = get_term_by('name', $category, 'category');
                                if ($get_category == false) {
                                    $cat = wp_insert_term($category, 'category');
                                    $cat = get_term_by('name', $category, 'category');
                                } else {
                                    $cat = get_term_by('name', $category, 'category');
                                }

                                $body = tm_openai_generate_body($items, $keyword,  $title, $introduction, $before_amazon_list_content,  $buy_guide, $conclusion, $qnas, $rand);

                                if (empty($fount_post)) {
                                    $my_post = array(
                                        'post_title' => ucwords($title),
                                        'post_content' => $body,
                                        'post_name' => sanitize_title($keyword),
                                        'post_status' => 'publish',
                                        'post_author' => 1,
                                        'post_category' => array($cat->term_id)
                                    );

                                    $id = wp_insert_post($my_post);

                                    if ($id) {
                                        TMO_Generate_Featured_Image($featured_image_url, $id, ucwords($title));
                                    }
                                }
                            } else {
                                echo '<h3>' . $keyword . 'has problem </h3>';
                                continue;
                            }
                        }
                    }
                }

                echo '</ol>';

                fclose($handle);

                wp_import_cleanup($this->id);

                echo '<h3>' . __('All Done.', 'tmo') . '</h3>';

                return true;
            } else {
                echo '<h3>' . __('Error: Please Fill Up The Plugin Settings.', 'tmo') . '</h3>';
            }
        }

        // dispatcher
        function dispatch()
        {
            $this->header();

            if (empty($_GET['step']))
                $step = 0;
            else
                $step = (int)$_GET['step'];

            switch ($step) {
                case 0:
                    $this->greet();
                    break;
                case 1:
                    check_admin_referer('import-upload');
                    set_time_limit(0);
                    $result = $this->tm_openai_import();
                    break;
            }

            $this->footer();
        }
    }

    function TMO_Generate_Featured_Image($image_url, $post_id, $title)
    {
        $upload_dir = wp_upload_dir();

        $image_data = file_get_contents($image_url);
        $filename = basename($image_url);
        if (wp_mkdir_p($upload_dir['path']))
            $file = $upload_dir['path'] . '/' . $filename;
        else
            $file = $upload_dir['basedir'] . '/' . $filename;

        file_put_contents($file, $image_data);

        $wp_filetype = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => $title,
            'post_content' => $title,
            'post_status' => 'inherit'
        );
        $attach_id = wp_insert_attachment($attachment, $file, $post_id);
        // update alt text for post
        update_post_meta($attach_id, '_wp_attachment_image_alt', $title);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $file);
        wp_update_attachment_metadata($attach_id, $attach_data);
        set_post_thumbnail($post_id, $attach_id);
    }

    function tm_openai_generate_body($items, $keyword,  $title, $introduction, $before_amazon_list_content,  $buy_guide, $conclusion, $qnas, $rand)
    {
		 
        $body = '<div class="tm_openai_amazon_content">';

        if (!empty($introduction)) {
            $body .= '<div class="tm_openai_header_content">';
            $body .= html_entity_decode($introduction);
            $body .= '</div>';
        }
 
		
        $ratings = ['7.90', '8.00', '8.15', '8.25', '8.30', '8.45', '8.50', '8.60', '8.75', '8.80', '8.90', '9.05', '9.10', '9.15', '9.20', '9.25', '9.35',  '9.40', '9.45', '9.50', '9.55', '9.60', '9.65', '9.70', '9.75', '9.00', '9.85', '9.90'];

        if (!empty($items)) {
            $body .= '<div class="tm_openai_product_table">
            <table class="TM-table table">
              <thead>
                <tr>
                  <th class="TM-table__th-position">#</th>
                  <th class="TM-table__th-thumb">Preview</th>
                  <th class="TM-table__th-title">Product</th>
                  <th class="TM-table__th-title">Score</th>
                  <th class="TM-table__th-links"></th>
                </tr>
              </thead>
              <tbody>';

            $count = 1;

            foreach ($items as $item) {

                $body .= '<tr class="TM-product ">

                    <td class="TM-table__td-position">' . $count . '</td> 
            
                    <td class="TM-table__td-thumb">
                      <a href="' . $item->getDetailPageURL() . '" target="_blank">
                        <img alt="' . $title . '" class="TM-product__img  " src="' . $item->getImages()->getPrimary()->getLarge()->getURL() . '" title="' . $item->getItemInfo()->getTitle()->getDisplayValue() . '"/>
                      </a>
                    </td>
            
            
                    <td class="TM-table__td-title">
                        <a class="TM-product__title" href="' . $item->getDetailPageURL() . '" target="_blank">' . wp_trim_words($item->getItemInfo()->getTitle()->getDisplayValue(), 5, '...') . '</a>
                    </td>

                    <td class="TM-table__td-score">
                        <p><strong>' . $ratings[array_rand($ratings, 1)] . '</strong></p>
                    </td>
            
                    <td class="TM-table__td-links"  >
                        <a class="TM-button  " href="' . $item->getDetailPageURL() . '" target="_blank">Buy on Amazon</a>
                    </td>
             
                  </tr>';
                $count++;
            }

            $body .= '</tbody>
            </table>
          </div>';
        }
  
		if (!empty($before_amazon_list_content)) {
			$body .= '<div class="tm_openai_beforeList">';
			$before_amazon_list_section_title = tm_open_ai_replace_with_keyword_and_keyword_stripped($keyword, get_option('tm_openai_before_amazon_list_section_title_'.$rand));
			$body .= '<h2 class="tm_before_amazon_list_title">' . $before_amazon_list_section_title . '</h2>';
			$body .= html_entity_decode($before_amazon_list_content);
			$body .= '</div>';
		}

        if (!empty($items)) {
            $count2 = 1;

            foreach ($items as $item) {

                $description = null;

                $rating = null;

                $product = [];

                $body .= '<div class="tm_openai_product">
          
                <div class="tm_openai_productname">
                    <h3><strong>' . $count2 . '.</strong>  ' . $item->getItemInfo()->getTitle()->getDisplayValue() . '</h3>
                </div>
          
                <div class="tm_openai_productimage">
                    <a class="amazonbuybutton" href="' . $item->getDetailPageURL() . '" target="_blank"  > 
                        <img alt="' . $title . '" src="' . $item->getImages()->getPrimary()->getLarge()->getURL() . '"  > 
                    </a>
                </div>';
				 
                if (!empty($item->getItemInfo()->getFeatures()) && get_option('tm_openai_feature_turn_off') && get_option('tm_openai_feature_turn_off') == 'No') {
                    $body .= '<div class="tm_openai_productfeatures">
                    <p><strong>Features :</strong></p> 
                    <ul>';

                    foreach ($item->getItemInfo()->getFeatures()->getDisplayValues() as $feature) {
                        $body .= '<li>' . $feature . '</li>';
                    }

                    $body .= '</ul>
                    </div>';
                }

                if (!empty($item->getItemInfo()->getProductInfo()) && get_option('tm_openai_additional_info_turn_off') && get_option('tm_openai_additional_info_turn_off') == 'No') {
                    $body .= '<div class="tm_openai_additional">
                        <p><strong>Additional Info :</strong></p>
                        <table class="tm_openai_technicaltable">
                            <tbody>';

                    if ($item->getItemInfo()->getProductInfo()->getColor() !== 'No Color Availavle') {
                        $body .= '<tr>
                                <td>Color</td>
                                <td>' . $item->getItemInfo()->getProductInfo()->getColor()->getDisplayValue() . '</td>
                            </tr>';
                    }


                    if ($item->getItemInfo()->getProductInfo()->getItemDimensions() !== 'No Dimension Availavle') {
                        $body .= '<tr>
                                    <td>Item Dimensions</td> 
                                </tr>';
                    }


                    if ($item->getItemInfo()->getProductInfo()->getItemDimensions() !== 'No Dimension Availavle') {
                        if (!empty($item->getItemInfo()->getProductInfo()->getItemDimensions()->getHeight())) {
                            $body .= '<tr>
                                    <td>Height</td>
                                    <td>' . $item->getItemInfo()->getProductInfo()->getItemDimensions()->getHeight()->getDisplayValue() . '</td>
                                </tr>';
                        }

                        if (!empty($item->getItemInfo()->getProductInfo()->getItemDimensions()->getWidth())) {
                            $body .= '<tr>
                                    <td>Width</td>
                                    <td>' . $item->getItemInfo()->getProductInfo()->getItemDimensions()->getWidth()->getDisplayValue() . '</td>
                                </tr>';
                        }

                        if (!empty($item->getItemInfo()->getProductInfo()->getItemDimensions()->getLength())) {
                            $body .= '<tr>
                                    <td>Length</td>
                                    <td>' . $item->getItemInfo()->getProductInfo()->getItemDimensions()->getLength()->getDisplayValue() . '</td>
                                </tr>';
                        }

                        echo "<pre>";


                        if (!empty($item->getItemInfo()->getProductInfo()->getItemDimensions()->getWeight())) {
                            $body .= '<tr>
                                    <td>Weight</td>
                                    <td>' . $item->getItemInfo()->getProductInfo()->getItemDimensions()->getWeight()->getDisplayValue() . '</td>
                                </tr>';
                        }
                    }

                    if ($item->getItemInfo()->getProductInfo()->getReleaseDate() !== 'No Release Date Availavle') {
                        $body .= '<tr>
                                <td>Release Date</td>
                                <td>' . $item->getItemInfo()->getProductInfo()->getReleaseDate()->getDisplayValue() . '</td>
                            </tr>';
                    }

                    $body .= '</tbody>
                        </table>
                    </div>';
                }


                if (!empty($product)) {
                    $product_description = $product['description'];
                    $rating = $product['rating'];
                }

                if (!empty($product_description)) {
                    $body .= '<div class="tm_openai_product_description">
                                        <p><strong>Description :</strong></p> 
                                        <p>' . $product_description . '</p>
                                </div>';
                }

                if (!empty($rating)) {
                    $body .= '<div class="tm_openai_product_ratings">
                                        <p><strong>Amazon Rating :</strong></p> 
                                        ' . $rating . '
                                </div>';
                }

                $review_prompt = tm_open_ai_replace_with_keyword_and_keyword_stripped($item->getItemInfo()->getTitle()->getDisplayValue(), get_option('tm_openai_prompt_text_for_review'));

                $reviews = get_option('tm_openai_review_turn_off') && get_option('tm_openai_review_turn_off') == 'No' ? tm_open_ai_generate_review($review_prompt) : null;

                if (!empty($reviews)) {
                    $body .= '<div class="tm_openai_reviews">' . $reviews . '</div>';
                }

                $body .= '<div class="tm_openai_center"><a class="TM-button " href="' . $item->getDetailPageURL() . '" target="_blank">Buy on Amazon</a></div>
                </div>';

                $count2++;
            }
        }
 
       if (!empty($buy_guide)) {
            $body .= '<div class="tm_openai_buy_guide">';
            $buy_guide_section_title = tm_open_ai_replace_with_keyword_and_keyword_stripped($keyword, get_option('tm_openai_buy_guide_section_title_'.$rand));
            $body .= '<h2 class="tm_buy_guide">' . $buy_guide_section_title . '</h2>';
            $body .= html_entity_decode($buy_guide);
            $body .= '</div>';
        }
		
        if (!empty($qnas)) {
            $body .= '<div class="tm_openai_faqs">';
            $faq_section_title = tm_open_ai_replace_with_keyword_and_keyword_stripped($keyword, get_option('tm_openai_faq_section_title_'.$rand));
            $body .= '<h2 class="tm_faq">' . $faq_section_title . '</h2>';
            $body .= '<ul>';

            foreach ($qnas as $qna) {
                $body .= '<li><h3>' . $qna["question"] . '</h3><p>' . $qna["answer"] . '</p>';
            }

            $body .= '</ul>';
            $body .= '</div>';
        }

        if (!empty($conclusion)) {
            $body .= '<div class="tm_openai_conclusion">';
            $conclusion_section_title = tm_open_ai_replace_with_keyword_and_keyword_stripped($keyword, get_option('tm_openai_conclusion_section_title'));
            $body .= '<h2 class="tm_conclusion">' . $conclusion_section_title . '</h2>';
            $body .= html_entity_decode($conclusion);
            $body .= '</div>';
        }

        $body .= '</div>';

        $new_keyword = strtolower(trim($keyword));
        $first_word_check = strtok($new_keyword, " ");
        $keyword_final = $first_word_check === 'best' ? str_replace($first_word_check, '', $new_keyword) : $new_keyword;
        $string = ['[replace_title]', '[replace_keyword]', '[replace_sitelink]', '[replace_sitename]'];
        $replace = [ucwords($title), $keyword_final, network_site_url('/'), get_bloginfo('name')];
        return str_replace($string, $replace, $body);
    }

    // Initialize
    function tm_openai_importer()
    {
        load_plugin_textdomain('tmo', false, dirname(plugin_basename(__FILE__)) . '/languages');

        $tm_openai_CSV_Importer = new tm_openai_CSV_Importer();
        register_importer('csv', __('TM Open AI CSV Importer', 'tmo'), __('Generate Bulk Posts From Given Keywords by importing CSV file and generating content using Open AI Fine Tuned Model' , 'tmo'), array($tm_openai_CSV_Importer, 'dispatch'));
    }

    add_action('plugins_loaded', 'tm_openai_importer');
}


function tm_openai_get_post_id_by_slug($slug)
{
    $page = get_page_by_path(sanitize_title($slug), 'OBJECT', 'post');
    if ($page) {
        return true;
    } else {
        return false;
    }
}


function tm_openai_curl_process_post_request($url, $get = false, $data = null)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if ($get) {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    } else {
        curl_setopt($ch, CURLOPT_POST, 1);
    }
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
