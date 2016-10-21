<?php
if (! defined( 'ABSPATH' )) exit;
class ChartyMce {
    public function __construct(){
        add_action( 'init', array($this,'charty_add_button'));
        foreach ( array('post.php','post-new.php') as $hook ) {
            add_action( "admin_head-$hook", array($this, 'my_admin_head'));
        }
    }

    public function my_admin_head(){
        $args = array(
                'orderby' => 'date',
                'order' => 'DESC',
                'post_type' => 'charty',
                'post_status' => 'publish'
            );
        $charty_posts = get_posts( $args );

        $result = array();

        foreach($charty_posts as $charty_post){
            $result[] = array(
                    "text"  => get_the_title($charty_post->ID) ."-" .  get_the_date( 'F j, Y g:i a', $charty_post->ID ),
                    "value" => '[charty_shortcode id=' .$charty_post->ID . ']'
                );
        }
        ?>
        <script type="text/javascript">
            var charty_posts_js = <?php echo json_encode($result); ?>;
        </script>
        <?php
    }

    public function charty_add_button() {
        if ( current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) ) {
            add_filter( 'mce_buttons', array($this, 'charty_register_button' ));
            add_filter( 'mce_external_plugins', array($this,'charty_add_plugin' ));
        }
    }

    public function charty_register_button( $buttons ) {
        array_push( $buttons, "|", "charty" );
        return $buttons;
    }

    public function charty_add_plugin( $plugin_array ) {
        $plugin_array['charty'] =  plugins_url() . '/charty/js/charty_mce.js' ;
        return $plugin_array;
    }
}