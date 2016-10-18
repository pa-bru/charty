<?php
/*
Plugin name: Charty - a google chart plugin
Description: This plugin enables you to create and manage cgoogle harts. You can also customize your charts (title, content, context...).
Version: 1.0
Author: P-A BRU
Author URI: http://www.pa-bru.com/
*/

	
//blocking direct access to the plugin PHP files	
	defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

//apply translation of the plugin :
	add_action( 'plugins_loaded', 'charty_load_textdomain' );
	function charty_load_textdomain() {
		load_plugin_textdomain('charty', false, plugin_basename(dirname(__FILE__)) . '/languages');
	}

//globals :
	$description_maxlength = 100;


//requires :
	require( plugin_dir_path( __FILE__ ) . 'inc/cpt.inc.php');
	require( plugin_dir_path( __FILE__ ) . 'inc/places-code.php');

//Enqueued script with localized data.
//add the scripts :
    add_action( 'wp_enqueue_scripts', 'add_plugin_scripts' );
    function add_plugin_scripts(){
        wp_enqueue_script('charty_load_chart', plugins_url( '/js/charty_load_chart.js' , __FILE__ ), array("google_charts_api"), false, true);
        wp_enqueue_script('google_charts_api','https://www.gstatic.com/charts/loader.js' , array(), false, true);
    }


//add meta boxes :
	add_action('add_meta_boxes', 'charty_meta_boxes', 10, 2);
	function charty_meta_boxes($post_type, $post){
		if('charty' == $post_type){
			add_meta_box('charty_meta_box', __( 'Information of the chart', 'charty' ), 'charty_meta_box', $post_type, 'normal', 'high');
		}
	}
//write meta box :
	function charty_meta_box($post){
		$charty_display_mode =  get_post_meta($post->ID,'_charty_display_mode',true);
		$charty_region =  get_post_meta($post->ID,'_charty_region',true);
		$charty_color_axis =  get_post_meta($post->ID,'_charty_color_axis',true);
		$charty_bg_color =  get_post_meta($post->ID,'_charty_bg_color',true);
		$charty_tooltip_trigger =  get_post_meta($post->ID,'_charty_tooltip_trigger',true);
		$charty_dataless_region_color =  get_post_meta($post->ID,'_charty_dataless_region_color',true);
		$charty_default_color =  get_post_meta($post->ID,'_charty_default_color',true);
        $charty_description =  get_post_meta($post->ID,'_charty_description',true);
		$charty_data =  get_post_meta($post->ID,'_charty_data',true);
		$charty_labels =  get_post_meta($post->ID,'_charty_labels',true);
        $charty_maps_api_key =  get_post_meta($post->ID,'_charty_maps_api_key',true);

        global $description_maxlength;
        global $countries;
        global $continents_and_subs;
		?>

		<!-- START CHARTY SHORTCODE -->
			<div class="meta-box-item-title">
				<h4><?php _e('Shortcode to paste in the post you want', 'charty'); ?></h4>
			</div>

			<div class="meta-box-item-content">
				<input style="width:100%" type="text" disabled="disabled" name="charty_shortcode" id="charty_shortcode" value="<?php echo '[charty_shortcode id='. $post->ID . ']'; ?>"/>
			</div>
		<!-- END CHARTY SHORTCODE -->


        <!-- START CHARTY COLOR AXIS ARRAY -->
        <div class="meta-box-item-title">
            <h4>
                <?php
                _e('Put your Google Maps API KEY here', 'charty');
                ?>
            </h4>
        </div>

        <div class="meta-box-item-content">
            <input style="width:100%" type="text" name="charty_maps_api_key" id="charty_maps_api_key" value="<?php echo $charty_maps_api_key;?>"/>
        </div>
        <!-- END CHARTY COLOR AXIS ARRAY -->

		<!-- START CHARTY DISPLAY MODE -->
			<div class="meta-box-item-title">
				<h4><?php _e('Display mode you want for your chart', 'charty'); ?></h4>
			</div>

			<div class="meta-box-item-content">
				<select name="charty_display_mode" id="charty_display_mode">
					<option <?php selected( 'auto', $charty_display_mode, false ) ;?>value="auto">auto</option>
					<option <?php selected( 'regions', $charty_display_mode, false );?> value="regions">regions</option>
					<option <?php selected( 'markers', $charty_display_mode, false );?> value="markers">markers</option>
					<option <?php selected( 'text', $charty_display_mode, false );?> value="text">text</option>
				</select>
			</div>
		<!-- END CHARTY DISPLAY MODE -->

        <!-- START CHARTY REGION -->
            <div class="meta-box-item-title">
                <h4><?php _e('Region you want to display on your chart', 'charty'); ?></h4>
            </div>

            <div class="meta-box-item-content">
                <select name="charty_region" id="charty_region">
                    <option <?php selected( 'world', $charty_region, false ) ;?>value="world">world</option>
                    <?php foreach($continents_and_subs as $key => $val){?>
                        <option <?php selected( $key, $charty_region, false ) ;?>value="<?php echo $key; ?>"><?php echo $val; ?></option>
                    <?php } ?>
                    <?php foreach($countries as $key => $val){?>
                        <option <?php selected( $key, $charty_region, false ) ;?>value="<?php echo $key; ?>"><?php echo $val; ?></option>
                    <?php } ?>
                </select>
            </div>
        <!-- END CHARTY REGION -->

        <!-- START CHARTY TOOLTIP TRIGGER -->
            <div class="meta-box-item-title">
                <h4><?php _e('Region you want to display on your chart', 'charty'); ?></h4>
            </div>

            <div class="meta-box-item-content">
                <select name="charty_tooltip_trigger" id="charty_tooltip_trigger">
                    <option <?php selected( 'focus', $charty_tooltip_trigger, false ) ;?>value="focus">focus</option>
                    <option <?php selected( 'selection', $charty_tooltip_trigger, false );?> value="selection">selection</option>
                    <option <?php selected( 'none', $charty_tooltip_trigger, false );?> value="none">none</option>
                </select>
            </div>
        <!-- END CHARTY TOOLTIP TRIGGER -->


        <!-- START CHARTY COLOR AXIS ARRAY -->
            <div class="meta-box-item-title">
                <h4>
                    <?php
                    _e('Colors to assign to values in the visualization. It creates a gradient with specified colors. Separate each label by a semi column. You must add at least 2 colors (by name or hexadecimal value)', 'charty');
                    ?>
                </h4>
            </div>

            <div class="meta-box-item-content">
                <input maxlength="200" style="width:100%" type="text" name="charty_color_axis" id="charty_color_axis" value="<?php echo $charty_color_axis;?>"/>
            </div>
        <!-- END CHARTY COLOR AXIS ARRAY -->


        <!-- START CHARTY BG COLOR -->
            <div class="meta-box-item-title">
                <h4>
                    <?php
                    _e('The background color for the main area of the chart. (by color name or hexadecimal value)', 'charty');
                    ?>
                </h4>
            </div>

            <div class="meta-box-item-content">
                <input maxlength="15" style="width:100%" type="text" name="charty_bg_color" id="charty_bg_color" value="<?php echo $charty_bg_color;?>"/>
            </div>
        <!-- END CHARTY BG COLOR -->

        <!-- START CHARTY DATALESS REGION COLOR -->
            <div class="meta-box-item-title">
                <h4>
                    <?php
                    _e('Color to assign to regions with no associated data.(by name or hexadecimal value)', 'charty');
                    ?>
                </h4>
            </div>

            <div class="meta-box-item-content">
                <input maxlength="15" style="width:100%" type="text" name="charty_dataless_region_color" id="charty_dataless_region_color" value="<?php echo $charty_dataless_region_color;?>"/>
            </div>
        <!-- END CHARTY DATALESS REGION COLOR -->

        <!-- START CHARTY DEFAULT COLOR -->
            <div class="meta-box-item-title">
                <h4>
                    <?php
                    _e('The color to use when for data points in a geochart when the location is present but the value is either null or unspecified.(by name or hexadecimal value)', 'charty');
                    ?>
                </h4>
            </div>

            <div class="meta-box-item-content">
                <input maxlength="15" style="width:100%" type="text" name="charty_default_color" id="charty_default_color" value="<?php echo $charty_default_color;?>"/>
            </div>
        <!-- END CHARTY DEFAULT COLOR -->

		<!-- START CHARTY DESCRIPTION -->
			<div class="meta-box-item-title">
				<h4>
					<?php 
						printf(esc_html__( 'The description you want : (%d characters max)', 'charty' ), $description_maxlength);
					?>
				</h4>
			</div>
			<div class="meta-box-item-content">
				<input maxlength="<?php echo $description_maxlength;?>" style="width:100%" type="text" name="charty_description" id="charty_description"
					   value="<?php echo $charty_description;?>"/>
			</div>
		<!-- END CHARTY DESCRIPTION -->

		<!-- START CHARTY LABELS ARRAY -->
			<div class="meta-box-item-title">
				<h4>
					<?php
					 _e('Labels of the chart (same number of column as data column). Separate each label by a semi column', 'charty');
					?>
				</h4>
			</div>

			<div class="meta-box-item-content">
				<input maxlength="200" style="width:100%" type="text" name="charty_labels" id="charty_labels" value="<?php echo $charty_labels;?>"/>
			</div>
        <!-- END CHARTY LABELS ARRAY -->


        <!-- START CHARTY DATA ARRAY -->
			<div class="meta-box-item-title">
				<h4>
					<?php
                    _e('Every data of the chart (same number of column as labels). Separate each value by a semi column and each entity of the chart by a new line', 'charty');
					?>
				</h4>
			</div>

			<div class="meta-box-item-content">
				<textarea maxlength="500" style="width:100%" name="charty_data" id="charty_data"><?php echo $charty_data; ?></textarea>
			</div>
        <!-- END CHARTY DATA ARRAY -->

		<?php
		// Add a nonce field :
		wp_nonce_field( 'save_metabox_data', 'charty_meta_box_nonce' );
	}

//save charty meta box with update :
	add_action('save_post','save_charty_metabox_data');
	function save_charty_metabox_data($post_ID){
		//verify if nonce is valid  and if the request referred from an administration screen :
		if(!wp_verify_nonce($_POST['charty_meta_box_nonce'], 'save_metabox_data' )){
			return $post_ID;
		}
		//type, labels and data are necessary to create the chart :
		if(!isset($_POST['charty_display_mode']) || empty($_POST['charty_labels']) || empty($_POST['charty_data'])){
			return $post_ID;
		}

		update_post_meta($post_ID,'_charty_display_mode', sanitize_text_field($_POST['charty_display_mode']));
		update_post_meta($post_ID,'_charty_description', sanitize_text_field($_POST['charty_description']));
		update_post_meta($post_ID,'_charty_labels', sanitize_text_field($_POST['charty_labels']));
		update_post_meta($post_ID,'_charty_data', esc_textarea($_POST['charty_data']));
        update_post_meta($post_ID,'_charty_region', sanitize_text_field($_POST['charty_region']));
        update_post_meta($post_ID,'_charty_color_axis', sanitize_text_field($_POST['charty_color_axis']));
        update_post_meta($post_ID,'_charty_bg_color', sanitize_text_field($_POST['charty_bg_color']));
        update_post_meta($post_ID,'_charty_tooltip_trigger', sanitize_text_field($_POST['charty_tooltip_trigger']));
        update_post_meta($post_ID,'_charty_dataless_region_color', sanitize_text_field($_POST['charty_dataless_region_color']));
        update_post_meta($post_ID,'_charty_default_color', sanitize_text_field($_POST['charty_default_color']));
        update_post_meta($post_ID,'_charty_maps_api_key', sanitize_text_field($_POST['charty_maps_api_key']));
	}


// String to Array function :
    function strToArray($str, $separation){
        $tab = explode($separation, $str);
        return $tab;
    }

// create shortcode :
	add_shortcode('charty_shortcode', 'charty_shortcode');
	function charty_shortcode($atts){

		//globals :
			global $description_maxlength;
		
		//verifying if id parameter in shortcode is an int :
			$atts['id'] = intval($atts['id']);
			if ( !$atts['id'] ){
				return __('Chart cannot be displayed because of a false shortcode', 'charty');
			}

		//verifying if post is a charty and if it exists :
			$charty_post = get_post($atts['id']);
			if(!$charty_post->post_type == 'charty' || $charty_post === null ){
				return false;
			}

        //TODO : extract default settings !


		//values of the charty post :
            //API Key :
                $charty_maps_api_key = get_post_meta($atts['id'],'_charty_maps_api_key',true);
            //Display Mode :
                $charty_display_mode = get_post_meta($atts['id'],'_charty_display_mode',true);

            //Region :
                $charty_region = get_post_meta($atts['id'],'_charty_region',true);

            //Bg color :
                $charty_bg_color = get_post_meta($atts['id'],'_charty_bg_color',true);

            //tooltip trigger :
                $charty_tooltip_trigger = get_post_meta($atts['id'],'_charty_tooltip_trigger',true);

            //Dataless region color :
                $charty_dataless_region_color = get_post_meta($atts['id'],'_charty_dataless_region_color',true);

            //Default color :
                $charty_default_color = get_post_meta($atts['id'],'_charty_default_color',true);

            //title :
                $charty_title = $charty_post->post_title;

            //Color axis :
                $charty_color_axis = get_post_meta($atts['id'],'_charty_color_axis',true);
                $charty_color_axis = strToArray($charty_color_axis, ";");
                $charty_color_axis = array_map('trim',$charty_color_axis);

            //labels :
                $charty_labels = get_post_meta($atts['id'],'_charty_labels',true);
                $charty_labels = strToArray($charty_labels, ";");
                $charty_labels = array_map('trim',$charty_labels);

            //data :
                $charty_data = get_post_meta($atts['id'],'_charty_data',true);
                $charty_data = trim($charty_data);

                // build array with each line of textarea :
                $array_of_lines = strToArray($charty_data, "\n");
                $array_of_lines = array_filter($array_of_lines, 'trim'); // remove any extra \r characters left behind

                //bluid array for each element of the current line :
                $array_data = [];
                foreach ($array_of_lines as $line) {
                    $line_to_array = strToArray($line, ";");
                    $array_data[] = array_map('trim',$line_to_array);
                }

            //description
                $charty_description = get_post_meta($atts['id'],'_charty_description',true);
                    if ( strlen( $charty_description ) > $description_maxlength ){
                        $charty_description = substr( $charty_description, 0, $description_maxlength );
                    }


		//Localize the script with new data (use php variables in js) :
			$variables_array = array(
				'charty_id' => $atts['id'],
				'charty_title' => $charty_title,
                'charty_display_mode' => $charty_display_mode,
                'charty_region' => $charty_region,
                'charty_color_axis' => $charty_color_axis,
                'charty_bg_color' => $charty_bg_color,
                'charty_tooltip_trigger' => $charty_tooltip_trigger,
                'charty_dataless_region_color' => $charty_dataless_region_color,
                'charty_default_color' => $charty_default_color,
				'charty_labels' => $charty_labels,
				'charty_data' => $array_data,
				'charty_maps_api_key' => $charty_maps_api_key,
            );
			wp_localize_script( 'charty_load_chart', 'charty', $variables_array );
		
		//Display the charty post :
			$display_charty = '<h2>'.$charty_title.'</h2>'
								.'<div id="charty_'.$atts['id'].'" style="height: 400px;"></div>'
								.'<p style="text-align:center;font-style:italic;">'.$charty_description.'</p>';

			return $display_charty;
	}
