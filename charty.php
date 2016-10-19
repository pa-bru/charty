<?php
/*
Plugin name: Charty - a google geochart plugin 
Description: This plugin enables you to create and manage google geographic charts. It's a useful tool to display demographic data on geographic charts but also on google maps (there is a Map display mode).
You can also customize your geographic charts (title, content, context, background, color gradient...).
Version: 1.0
Author: P-A BRU
Author URI: https://www.pa-bru.fr/
*/


//blocking direct access to the plugin PHP files	
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Charty {
	private $plugin_path;
	private $plugin_url;
	private $plugin_version;
	private $plugin_l10n;
	private $api_base = 'https://www.gstatic.com/charts/loader.js';
	private $app_name = 'Charty';
	private $cpt_name = 'charty';

	protected static $charty;
    protected $mce;

	protected $countries;
	protected $continents_and_subs;

	const DESCRIPTION_MAX_LENGTH = 100;

	public static function get_instance(){
        if(!(static::$charty instanceof static)){
            static::$charty = new static;
        }
        return static::$charty;
    }

	public function __construct() {
		$this->setProperties();

        require($this->plugin_path . 'inc/ChartyMce.php');

        $this->mce = new ChartyMce();
	
		//apply translation of the plugin :
		add_action( 'plugins_loaded', array( $this, 'charty_load_textdomain'));
		//register charty custom post type :
		add_action( 'init', array($this,'charty'), 0 );
		//add the scripts :
	    add_action( 'wp_enqueue_scripts', array($this, 'add_plugin_scripts'));
		//add the admin scripts :     
		add_action( 'admin_enqueue_scripts', array($this, 'add_admin_scripts'), 10, 2 );
		//add the admin styles :
		add_action( 'admin_print_styles', array($this, 'charty_admin_styles'), 10, 2 );
		//add meta boxes :
		add_action('add_meta_boxes', array($this, 'charty_meta_boxes'), 10, 2);//and exec the method charty_meta_box to write the meta boxes
		//save charty meta box with update :
		add_action('save_post',array($this, 'save_charty_metabox_data'));
		// create shortcode :
		add_shortcode('charty_shortcode', array($this,'charty_shortcode'));
	}

	public function setProperties(){
		$this->plugin_path    			= plugin_dir_path( __FILE__ );
		$this->plugin_url     			= plugin_dir_url( __FILE__ );
		$this->plugin_version 			= '1.0';
		$this->plugin_l10n    			= 'charty';
		$this->countries      			= require_once( $this->plugin_path . 'inc/countries.php');
		$this->continents_and_subs      = require_once( $this->plugin_path . 'inc/continents_and_subs.php');
	}

	public function charty_load_textdomain() {
		load_plugin_textdomain( $this->plugin_l10n, false, plugin_basename(dirname(__FILE__)) . '/languages');
	}

	// Register Custom Post Type
	public function charty() {
		$labels = array(
			'name'                  => _x( 'charty', 'Post Type General Name', 'charty' ),
			'singular_name'         => _x( 'charty', 'Post Type Singular Name', 'charty' ),
			'menu_name'             => __( 'charty', 'charty' ),
			'name_admin_bar'        => __( 'charty', 'charty' ),
			'parent_item_colon'     => __( '', 'charty' ),
			'all_items'             => __( 'All charts', 'charty' ),
			'add_new_item'          => __( 'Add a new chart', 'charty' ),
			'add_new'               => __( 'Add new', 'charty' ),
			'new_item'              => __( 'New chart', 'charty' ),
			'edit_item'             => __( 'Edit chart', 'charty' ),
			'update_item'           => __( 'Update chart', 'charty' ),
			'view_item'             => __( 'View chart', 'charty' ),
			'search_items'          => __( 'Search chart', 'charty' ),
			'not_found'             => __( 'Not found', 'charty' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'charty' ),
			'items_list'            => __( 'chart list', 'charty' ),
			'items_list_navigation' => __( 'chart list navigation', 'charty' ),
			'filter_items_list'     => __( 'Filter chart list', 'charty' ),
		);
		$args = array(
			'label'                 => __( 'charty', 'charty' ),
			'description'           => __( 'Used to manage your charts.', 'charty' ),
			'labels'                => $labels,
			'supports'              => array( 'title', ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 80,
			'menu_icon'             => 'dashicons-chart-pie',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,		
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
		);
		register_post_type( 'charty', $args );
	}

	public function add_plugin_scripts(){
        wp_enqueue_script('charty_load_chart', plugins_url( '/js/charty_load_chart.js' , __FILE__ ), array("google_charts_api"), $this->plugin_version, true);
        wp_enqueue_script('google_charts_api', $this->api_base , array(), false, true);
    }

    public function add_admin_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('charty_panel', plugins_url( '/js/charty-panel.js', __FILE__ ), array('jquery'), $this->plugin_version, true);
	}

	public function charty_admin_styles(){
        wp_enqueue_style( 'charty_styles', plugin_dir_url( __FILE__ ) . '/css/charty-styles.css' );
	}

	public function charty_meta_boxes($post_type, $post){
		if($this->cpt_name == $post_type){
			add_meta_box('charty_meta_box', __( 'Information of the chart', $this->plugin_l10n ), array($this, 'charty_meta_box'), $post_type, 'normal', 'high');
		}
	}

	// String to Array function :
    public function strToArray($str, $separation){
        $tab = explode($separation, $str);
        return $tab;
    }

	public function mytrim( &$item1, $key, &$separation ) {
	    $item1 = trim($item1, $separation);
	}

	//write meta box :
	public function charty_meta_box($post){
        //geo chart type :
		$charty_display_mode =  get_post_meta($post->ID,'_charty_display_mode',true);
		$charty_region =  get_post_meta($post->ID,'_charty_region',true);
		$charty_color_axis =  get_post_meta($post->ID,'_charty_color_axis',true);
		$charty_bg_color =  get_post_meta($post->ID,'_charty_bg_color',true);
		$charty_tooltip_trigger =  get_post_meta($post->ID,'_charty_tooltip_trigger',true);
		$charty_dataless_region_color =  get_post_meta($post->ID,'_charty_dataless_region_color',true);
		$charty_default_color =  get_post_meta($post->ID,'_charty_default_color',true);

        //global data :
        $charty_description =  get_post_meta($post->ID,'_charty_description',true);
		$charty_data =  get_post_meta($post->ID,'_charty_data',true);
		$charty_labels =  get_post_meta($post->ID,'_charty_labels',true);
        $charty_maps_api_key =  get_post_meta($post->ID,'_charty_maps_api_key',true);
        $charty_type =  get_post_meta($post->ID,'_charty_type',true);

        //map type :
        $charty_map_zoom_level =  get_post_meta($post->ID,'_charty_map_zoom_level',true);
        $charty_map_style =  get_post_meta($post->ID,'_charty_map_style',true);
        $charty_map_type_control = get_post_meta($post->ID,'_charty_map_type_control',true);
		?>

		<!-- START CHARTY SHORTCODE -->

			<div class="charty-alert charty-alert-warning">
    	       <p><?php _e('Warning : for the two modes (map and geochart) you must add exactly 2 columns (ex: City; Population) !', $this->plugin_l10n); ?></p>
               <ul>
                   <li><?php _e('geochart syntax : country or city; Number', $this->plugin_l10n); ?></li>
                   <li><?php _e('Map syntax : place; text or number', $this->plugin_l10n); ?></li>
               </ul>
    	    </div>
			<div class="meta-box-item-title">
				<h4><?php _e('Shortcode to paste in the post you want', $this->plugin_l10n); ?></h4>
			</div>

			<div class="meta-box-item-content">
				<input style="width:100%" type="text" disabled="disabled" name="charty_shortcode" id="charty_shortcode" value="<?php echo '[charty_shortcode id='. $post->ID . ']'; ?>"/>
			</div>
		<!-- END CHARTY SHORTCODE -->

        <!-- START CHARTY GOOGLE MAPS API KEY -->
            <div class="meta-box-item-title">
                <h4>
                    <?php
                    _e('Put your Google Maps API KEY here', $this->plugin_l10n);
                    ?>
                </h4>
            </div>

            <div class="meta-box-item-content">
                <input style="width:100%" type="text" name="charty_maps_api_key" id="charty_maps_api_key" value="<?php echo $charty_maps_api_key;?>"/>
            </div>
        <!-- END CHARTY GOOGLE MAPS API KEY -->

        <!-- START CHARTY DESCRIPTION -->
            <div class="meta-box-item-title">
                <h4>
                    <?php
                    printf(esc_html__( 'The description you want : (%d characters max)', $this->plugin_l10n ), self::DESCRIPTION_MAX_LENGTH);
                    ?>
                </h4>
            </div>
            <div class="meta-box-item-content">
                <input maxlength="<?php echo self::DESCRIPTION_MAX_LENGTH;?>" style="width:100%" type="text" name="charty_description" id="charty_description"
                       value="<?php echo $charty_description;?>"/>
            </div>
        <!-- END CHARTY DESCRIPTION -->

        <!-- START CHARTY LABELS ARRAY -->
            <div class="meta-box-item-title">
                <h4>
                    <?php
                    _e('Labels of the chart (same number of column as data column) :', $this->plugin_l10n);
                    ?>
                </h4>
            </div>

            <div class="meta-box-item-content charty-alert charty-alert-info">
                <p><?php _e('Put 2 labels. Separate each label by a semi column', $this->plugin_l10n); ?></p>
                <input maxlength="200" style="width:100%" type="text" name="charty_labels" id="charty_labels" value="<?php echo $charty_labels;?>"/>
            </div>
        <!-- END CHARTY LABELS ARRAY -->

        <!-- START CHARTY DATA ARRAY -->
            <div class="meta-box-item-title">
                <h4>
                    <?php
                    _e('DATA List : Every data of the chart (same number of column as labels). Separate each value by a semi column and each entity of the chart by a new line', $this->plugin_l10n);
                    ?>
                </h4>
            </div>
            <div class="meta-box-item-content charty-alert charty-alert-info">
                <p><?php _e('Separate each value by a semi column and each entity of the chart by a new line', $this->plugin_l10n); ?></p>
                <p><?php _e('The first data must be a Country or City (and must belong to the region you have chosen to display the geochart).The second data must be a number but can be a string if you chose the Map Type!', $this->plugin_l10n); ?></p>
                <p><?php _e('Exemple : Paris;3456.98 ', $this->plugin_l10n); ?></p>
                <textarea rows="10" style="width:100%" name="charty_data" id="charty_data"><?php echo $charty_data; ?></textarea>
            </div>
        <!-- END CHARTY DATA ARRAY -->

		<!-- START CHARTY TYPE -->
			<div class="meta-box-item-title">
				<h4><?php _e('Choose the type of Map (map or geographic chart)', $this->plugin_l10n); ?></h4>
			</div>

            <input type="radio" name="charty_type" class="charty_type" id="charty_type_geo_chart" value="geo_chart"  <?php checked( "geo_chart", $charty_type); ?>/>
            <label for="charty_type_geo_chart"><?php _e('Geo chart', $this->plugin_l10n); ?></label>

            <input type="radio" name="charty_type" class="charty_type" id="charty_type_map" value="map" <?php checked( "map", $charty_type); ?>/>
            <label for="charty_type_map"><?php _e('Map', $this->plugin_l10n); ?></label>
		<!-- END CHARTY TYPE -->

        <!-- START GEOCHART TYPE  -->
            <div data-geochart>
                <br/>
                <hr/>
                <h3>
                    <?php
                    _e('Geographic Chart Type :', $this->plugin_l10n);
                    ?>
                </h3>
                <!-- START CHARTY DISPLAY MODE -->
                    <div class="meta-box-item-title">
                        <h4><?php _e('Display mode you want for your chart', $this->plugin_l10n); ?></h4>
                    </div>

                    <div class="meta-box-item-content">
                        <select name="charty_display_mode" id="charty_display_mode">
                            <option <?php selected( 'markers', $charty_display_mode );?> value="markers"><?php _e('Markers', $this->plugin_l10n); ?></option>
                            <option <?php selected( 'regions', $charty_display_mode );?> value="regions"><?php _e('Regions', $this->plugin_l10n); ?></option>
                            <option <?php selected( 'text', $charty_display_mode );?> value="text"><?php _e('Text', $this->plugin_l10n); ?></option>
                            <option <?php selected( 'auto', $charty_display_mode ) ;?>value="auto"><?php _e('Auto', $this->plugin_l10n); ?></option>
                        </select>
                    </div>
                <!-- END CHARTY DISPLAY MODE -->

                <!-- START CHARTY REGION -->
                    <div class="meta-box-item-title">
                        <h4><?php _e('Region you want to display on your chart', $this->plugin_l10n); ?></h4>
                    </div>

                    <div class="meta-box-item-content">
                        <select name="charty_region" id="charty_region">
                            <option <?php selected( 'world', $charty_region ) ;?>value="world">world</option>
                            <?php foreach($this->continents_and_subs as $key => $val){?>
                                <option <?php selected( $key, $charty_region ) ;?>value="<?php echo $key; ?>"><?php echo $val; ?></option>
                            <?php } ?>
                            <?php foreach($this->countries as $key => $val){?>
                                <option <?php selected( $key, $charty_region ) ;?>value="<?php echo $key; ?>"><?php echo $val; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                <!-- END CHARTY REGION -->

                <!-- START CHARTY TOOLTIP TRIGGER -->
                    <div class="meta-box-item-title">
                        <h4><?php _e('Region you want to display on your chart', $this->plugin_l10n); ?></h4>
                    </div>

                    <div class="meta-box-item-content">
                        <select name="charty_tooltip_trigger" id="charty_tooltip_trigger">
                            <option <?php selected( 'focus', $charty_tooltip_trigger ) ;?>value="focus">focus</option>
                            <option <?php selected( 'selection', $charty_tooltip_trigger );?> value="selection">selection</option>
                            <option <?php selected( 'none', $charty_tooltip_trigger );?> value="none">none</option>
                        </select>
                    </div>
                <!-- END CHARTY TOOLTIP TRIGGER -->

                <!-- START CHARTY COLOR AXIS ARRAY -->
                    <div class="meta-box-item-title">
                        <h4>
                            <?php
                            _e('Color Axis : Colors to assign to values in the visualization. It creates a gradient with specified colors. Separate each label by a semi column. You must add at least 2 colors (by name or hexadecimal value)', $this->plugin_l10n);
                            ?>
                        </h4>
                    </div>

                    <div class="meta-box-item-content">
                        <input maxlength="200" style="" type="text" name="charty_color_axis" id="charty_color_axis" value="<?php echo $charty_color_axis;?>"/>
                    </div>
                <!-- END CHARTY COLOR AXIS ARRAY -->

                <!-- START CHARTY BG COLOR -->
                    <div class="meta-box-item-title">
                        <h4>
                            <?php
                            _e('Background : The background color for the main area of the chart. (by color name or hexadecimal value)', $this->plugin_l10n);
                            ?>
                        </h4>
                    </div>

                    <div class="meta-box-item-content">
                        <input maxlength="15" style="" type="text" name="charty_bg_color" id="charty_bg_color" value="<?php echo $charty_bg_color;?>"/>
                    </div>
                <!-- END CHARTY BG COLOR -->

                <!-- START CHARTY DATALESS REGION COLOR -->
                    <div class="meta-box-item-title">
                        <h4>
                            <?php
                            _e('Dataless Region color :Color to assign to regions with no associated data.(by name or hexadecimal value)', $this->plugin_l10n);
                            ?>
                        </h4>
                    </div>

                    <div class="meta-box-item-content">
                        <input maxlength="15" style="" type="text" name="charty_dataless_region_color" id="charty_dataless_region_color" value="<?php echo $charty_dataless_region_color;?>"/>
                    </div>
                <!-- END CHARTY DATALESS REGION COLOR -->

                <!-- START CHARTY DEFAULT COLOR -->
                    <div class="meta-box-item-title">
                        <h4>
                            <?php
                            _e('The color to use when for data points in a geochart when the location is present but the value is either null or unspecified.(by name or hexadecimal value)', $this->plugin_l10n);
                            ?>
                        </h4>
                    </div>

                    <div class="meta-box-item-content">
                        <input maxlength="15" style="" type="text" name="charty_default_color" id="charty_default_color" value="<?php echo $charty_default_color;?>"/>
                    </div>
                <!-- END CHARTY DEFAULT COLOR -->
            </div>
        <!-- END GEOCHART TYPE  -->

        <!-- START MAP TYPE  -->
            <div data-map>
                <br/>
                <hr/>
                <h3>
                    <?php
                    _e('MAP Type :', $this->plugin_l10n);
                    ?>
                </h3>
                <!-- START CHARTY MAP ZOOM LEVEL -->
                    <div class="meta-box-item-title">
                        <h4>
                            <?php
                            _e('Zoom Level of the map. (between 0 and 19', $this->plugin_l10n);
                            ?>
                        </h4>
                    </div>

                    <div class="meta-box-item-content">
                        <p><?php _e('Put a number between 0 and 19. 0 is the world and 19 is the maximum zoom.', $this->plugin_l10n); ?></p>
                        <input type="number" style="" min="0" max="19" name="charty_map_zoom_level" id="charty_map_zoom_level" value="<?php echo $charty_map_zoom_level ;?>" placeholder="ex : 4"/>
                    </div>
                <!-- END CHARTY MAP ZOOM LEVEL -->


                <!-- START CHARTY STYLE MAP -->
                    <div class="meta-box-item-title">
                        <h4><?php _e('The design of the map :', $this->plugin_l10n); ?></h4>
                    </div>

                    <div class="meta-box-item-content">
                        <select name="charty_map_style" id="charty_map_style">
                            <option <?php selected( 'none', $charty_map_style );?> value="none"><?php _e('None', $this->plugin_l10n); ?></option>
                            <option <?php selected( 'green', $charty_map_style );?> value="green"><?php _e('Green', $this->plugin_l10n); ?></option>
                            <option <?php selected( 'red', $charty_map_style );?> value="red"><?php _e('Red', $this->plugin_l10n); ?></option>
                            <option <?php selected( 'blue', $charty_map_style ) ;?>value="blue"><?php _e('Blue', $this->plugin_l10n); ?></option>
                        </select>
                    </div>
                <!-- END CHARTY STYLE MAP -->

                <!-- START CHARTY MAP TYPE CONTROL -->
                    <div class="meta-box-item-title">
                        <h4><?php _e('Map Type Control : Authorize the viewer to switch between [map, satellite, hybrid, terrain]', $this->plugin_l10n); ?></h4>
                    </div>

                    <input type="radio" name="charty_map_type_control" id="charty_map_type_control_false" value="false"  <?php checked( "false", $charty_map_type_control); ?>/>
                    <label for="charty_map_type_control_false"><?php _e('No', $this->plugin_l10n); ?></label>

                    <input type="radio" name="charty_map_type_control" id="charty_map_type_control_true" value="true" <?php checked( "true", $charty_map_type_control); ?>/>
                    <label for="charty_map_type_control_true"><?php _e('Yes', $this->plugin_l10n); ?></label>
                <!-- END CHARTY MAP TYPE CONTROL -->
                <br/>
                <hr/>
            </div>
        <!-- END MAP TYPE  -->
		<?php
		// Add a nonce field :
		wp_nonce_field( 'save_metabox_data', 'charty_meta_box_nonce' );
	}

	public function save_charty_metabox_data($post_ID){
		//verify if nonce is valid  and if the request referred from an administration screen :
		if(!wp_verify_nonce($_POST['charty_meta_box_nonce'], 'save_metabox_data' )){
			return $post_ID;
		}
		//type, labels and data are necessary to create the chart :
		if(!isset($_POST['charty_type']) || empty($_POST['charty_labels']) || empty($_POST['charty_data'])){
			return $post_ID;
		}

        //global data :
        update_post_meta($post_ID,'_charty_type', sanitize_text_field($_POST['charty_type']));
        update_post_meta($post_ID,'_charty_description', sanitize_text_field($_POST['charty_description']));
        update_post_meta($post_ID,'_charty_labels', sanitize_text_field($_POST['charty_labels']));
        update_post_meta($post_ID,'_charty_data', esc_textarea($_POST['charty_data']));
        update_post_meta($post_ID,'_charty_maps_api_key', sanitize_text_field($_POST['charty_maps_api_key']));

        if($_POST['charty_type'] == "geo_chart"){
            // geochart type :
            update_post_meta($post_ID,'_charty_display_mode', sanitize_text_field($_POST['charty_display_mode']));
            update_post_meta($post_ID,'_charty_region', sanitize_text_field($_POST['charty_region']));
            update_post_meta($post_ID,'_charty_color_axis', sanitize_text_field($_POST['charty_color_axis']));
            update_post_meta($post_ID,'_charty_bg_color', sanitize_text_field($_POST['charty_bg_color']));
            update_post_meta($post_ID,'_charty_tooltip_trigger', sanitize_text_field($_POST['charty_tooltip_trigger']));
            update_post_meta($post_ID,'_charty_dataless_region_color', sanitize_text_field($_POST['charty_dataless_region_color']));
            update_post_meta($post_ID,'_charty_default_color', sanitize_text_field($_POST['charty_default_color']));
        } elseif($_POST['charty_type'] == "map"){
            // Map type :
            update_post_meta($post_ID,'_charty_map_zoom_level', intval($_POST['charty_map_zoom_level']));
            update_post_meta($post_ID,'_charty_map_style', sanitize_text_field($_POST['charty_map_style']));
            update_post_meta($post_ID,'_charty_map_type_control', $_POST['charty_map_type_control']);
        }
    }

    public function charty_shortcode($atts){
		//verifying if id parameter in shortcode is an int :
			$atts['id'] = intval($atts['id']);
			if ( !$atts['id'] ){
				return __('Chart cannot be displayed because of a false shortcode', $this->plugin_l10n);
			}

		//verifying if post is a charty and if it exists :
			$charty_post = get_post($atts['id']);
			if(!$charty_post->post_type == $this->cpt_name || $charty_post === null ){
				return false;
			}

        //TODO : extract default settings !

        /*
         * Global Data :
         */
        //API Key :
            $charty_maps_api_key = get_post_meta($atts['id'],'_charty_maps_api_key',true);

        //Charty type :
            $charty_type = get_post_meta($atts['id'],'_charty_type',true);

        //title :
            $charty_title = $charty_post->post_title;

        //labels :
            $charty_labels = get_post_meta($atts['id'],'_charty_labels',true);
            //remove whitespaces on the line and semi-colon.
            $charty_labels = trim($charty_labels);
            $charty_labels = trim($charty_labels, ";");
            $charty_labels = $this->strToArray($charty_labels, ";");
            $charty_labels = array_map('trim',$charty_labels);

        //data :
            $charty_data = get_post_meta($atts['id'],'_charty_data',true);
            $charty_data = trim($charty_data);

            // build array with each line of textarea :
            $array_of_lines = $this->strToArray($charty_data, "\n");
            $array_of_lines = array_map('trim',$array_of_lines);
            array_walk($array_of_lines, array($this, 'mytrim'), ";" );

            //bluid array for each element of the current line :
            $array_data = [];
            foreach ($array_of_lines as $line) {
                $line_to_array = $this->strToArray($line, ";");
                $line_to_array = array_map('trim',$line_to_array);

                if($charty_type == "geo_chart"){
                    $line_to_array[1] = (int)$line_to_array[1];
                }
                $array_data[] = $line_to_array;
            }
        //description
            $charty_description = get_post_meta($atts['id'],'_charty_description',true);
            if ( strlen( $charty_description ) > self::DESCRIPTION_MAX_LENGTH ){
                $charty_description = substr( $charty_description, 0, self::DESCRIPTION_MAX_LENGTH );
            }

        switch($charty_type){
            case "geo_chart":
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

                //Color axis :
                $charty_color_axis = get_post_meta($atts['id'],'_charty_color_axis',true);
                $charty_color_axis = trim($charty_color_axis);
                $charty_color_axis = trim($charty_color_axis, ";");
                $charty_color_axis = $this->strToArray($charty_color_axis, ";");
                $charty_color_axis = array_map('trim',$charty_color_axis);

                $spe_vars = array(
                    'charty_display_mode' => $charty_display_mode,
                    'charty_region' => $charty_region,
                    'charty_color_axis' => $charty_color_axis,
                    'charty_bg_color' => $charty_bg_color,
                    'charty_tooltip_trigger' => $charty_tooltip_trigger,
                    'charty_dataless_region_color' => $charty_dataless_region_color,
                    'charty_default_color' => $charty_default_color,
                );
                break;
            case "map":
                $charty_map_zoom_level =  get_post_meta($atts['id'],'_charty_map_zoom_level',true);
                $charty_map_style =  get_post_meta($atts['id'],'_charty_map_style',true);
                $charty_map_type_control = get_post_meta($atts['id'],'_charty_map_type_control',true);

                $spe_vars = array(
                    'charty_map_zoom_level' => $charty_map_zoom_level,
                    'charty_map_style' => $charty_map_style,
                    'charty_map_type_control' => $charty_map_type_control
                );
                break;
            default:
                return false;
        }
        /*
         * Send Data To the JavaScript :
         */
        $variables_array = array(
            'charty_id' => $atts['id'],
            'charty_title' => $charty_title,
            'charty_labels' => $charty_labels,
            'charty_data' => $array_data,
            'charty_maps_api_key' => $charty_maps_api_key,
            'charty_type' => $charty_type
        );
        $variables_array = array_merge($variables_array, $spe_vars);
        wp_localize_script( 'charty_load_chart', 'charty', $variables_array );
		
		/*
		 * Return the content generated and replace the shortcode by that :
		 */
			$display_charty = '<h2>'.$charty_title.'</h2>'
								.'<div id="charty_'.$atts['id'].'" style="height: 600px;"></div>'
								.'<p style="text-align:center;font-style:italic;">'.$charty_description.'</p>';

			return $display_charty;
	}
}

Charty::get_instance();