<?php

/**
 * Created by PhpStorm.
 * User: pabru
 * Date: 19/10/2016
 * Time: 18:11
 */
class ChartyMce {
    public function __construct(){
        add_action( 'init', array($this,'charty_add_button'));
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
        $plugin_array['charty'] =  plugins_url() . '/charty/inc/charty_mce.js.php' ;
        return $plugin_array;
    }

    public function get_all_published_charty(){
        $query = new WP_Query(array(
            'post_type' => 'charty',
            'post_status' => 'publish'
        ));
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            echo $post_id;
            echo "<br>";
        }
    }
}
