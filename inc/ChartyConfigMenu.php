<?php
class ChartyConfigMenu {
    protected $plugin_l10n;
    protected $notice_message;
    protected $notice_type;

    public function __construct($plugin_l10n){
        if(is_admin()){
            $this->plugin_l10n = $plugin_l10n;
            add_action( 'admin_menu', array($this, 'register_config_menu'));
            add_action( 'admin_init', array($this, 'charty_export_csv'));
            add_action( 'admin_init', array($this, 'charty_save_options'));
        }
    }

    /**
     * Display a custom menu page
     */
    public function register_config_menu(){
        add_submenu_page('edit.php?post_type=charty', __('Charty Settings', $this->plugin_l10n), __('Settings', $this->plugin_l10n), 'manage_options', 'charty-config-menu', array($this, 'charty_config_menu'));
    }

    public function charty_config_menu(){
        ?>
        <div id="">
            <div class="charty-main-box">
                <h1 class="charty-title"><?php _e('Charty Settings :', $this->plugin_l10n); ?></h1>
                <div class="charty-space-20"></div>
                <div class="charty-alert charty-alert-info">
                    <?php _e('Here you can configure all the settings of the Charty plugin', $this->plugin_l10n); ?>
                </div>

                <div class="charty-panel charty-panel-default">
                    <div class="charty-panel-heading">
                        <h3 class="charty-panel-title"><?php _e('Export list of charts :', $this->plugin_l10n); ?></h3>
                    </div>
                    <div class="charty-panel-body">
                        <?php _e('Click on the button just below to download a file containing information on the charts you have created whith Charty', $this->plugin_l10n); ?>
                        <div class="charty-space-20"></div>
                        <form method="post" name="export_csv" action="">
                            <?php wp_nonce_field( 'save_export_csv', 'charty_export_csv_nonce' ); ?>
                            <input class="charty-button" type="submit" name="export_csv_submit" value="<?php _e('Export', $this->plugin_l10n); ?>">
                        </form>
                    </div>
                </div>

                <div class="charty-panel charty-panel-default">
                    <div class="charty-panel-heading">
                        <h3 class="charty-panel-title"><?php _e('Default Google Maps API Key', $this->plugin_l10n); ?></h3>
                    </div>
                    <div class="charty-panel-body">
                        <?php _e('Put your google Maps API Key here. you can override it when you create a new chart.', $this->plugin_l10n); ?>
                        <div class="charty-space-20"></div>
                        <form method="post" name="export_csv" action="">
                            <input type="text" name="default_google_maps_api_key" value="<?php echo get_option('default_google_maps_api_key'); ?>">
                            <input class="charty-button" type="submit" name="default_google_maps_api_key_submit" value="<?php _e('Add', $this->plugin_l10n); ?>">
                            <?php wp_nonce_field( 'save_default_google_maps_api_key', 'default_google_maps_api_key_nonce' ); ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php
    }

    public function charty_save_options(){
        if(!wp_verify_nonce($_POST['default_google_maps_api_key_nonce'], 'save_default_google_maps_api_key' )){
            return;
        }
        if(isset($_POST['default_google_maps_api_key'])) {
            if($_POST['default_google_maps_api_key'] == ""){
                delete_option('default_google_maps_api_key');
                $this->message = "Your default Google Maps API Key is successfully deleted";
                add_action( 'admin_notices', array($this, 'charty_display_notice'));
            } else{
                update_option('default_google_maps_api_key', $_POST['default_google_maps_api_key']);
                $this->message = "A new default Google Maps API Key is successfully added";
                add_action( 'admin_notices', array($this, 'charty_display_notice'));
            }
        }
    }

    public function charty_display_notice() {
        ?>
        <div class="notice notice-success is-dismissible" style="margin-top: 20px;">
            <p><?php _e($this->message, $this->plugin_l10n); ?></p>
        </div>
        <?php
    }

    public function charty_export_csv(){
        if(!wp_verify_nonce($_POST['charty_export_csv_nonce'], 'save_export_csv' )){
            return;
        }

        if (isset($_POST['export_csv_submit'])) {
            $args = array(
                'orderby' => 'date',
                'order' => 'DESC',
                'post_type' => 'charty',
                'post_status' => 'publish'
            );
            $charty_posts = get_posts( $args );

            header("Content-Type: text/csv");
            header("Content-Disposition: csv" . date("Y-m-d") . ".csv");
            header("Content-Disposition: attachment; filename=" . $this->plugin_l10n . "_".date("Y-m-d") . ".csv");
            header("Pragma: no-cache");
            header("Expires: 0");
            ob_end_clean();
                $out = fopen('php://output', 'w');
                fputcsv($out, array(
                    "ID",
                    "Chart's title",
                    "Chart's type",
                    "Labels",
                    "Data",
                    "shortcode",
                    "Date"
                ));

                foreach(  $charty_posts  as  $charty_post){
                    $charty_labels = get_post_meta($charty_post->ID,'_charty_labels',true);
                    $charty_type = get_post_meta($charty_post->ID,'_charty_type',true);
                    $charty_data = get_post_meta($charty_post->ID,'_charty_data',true);
                    fputcsv($out, array(
                        $charty_post->ID,
                        get_the_title($charty_post->ID),
                        $charty_type,
                        $charty_labels,
                        $charty_data,
                        '[charty_shortcode id='. $charty_post->ID . ']',
                        get_the_date( "Y-m-d H:i:s", $charty_post->ID ),
                    ));
                }

                fclose($out);
            //IMPORTANT (to avoid displaying rest of page in csv file)
            exit();
        }
    }
}