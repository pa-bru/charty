<?php
    require_once('../../../../wp-load.php');
    require_once('../../../../wp-admin/includes/admin.php');
    do_action('admin_init');

    if ( ! is_user_logged_in() )
        die('You must be logged in to access this script.');
    $args = array(
        'orderby' => 'date',
        'order' => 'DESC',
        'post_type' => 'charty',
        'post_status' => 'publish'
    );
    $charty_posts = get_posts( $args );
?>
(function() {
//******* Load plugin specific language pack
//tinymce.PluginManager.requireLangPack('charty');

        tinymce.create('tinymce.plugins.charty', {
            init :  function(ed, url) {
                ed.addButton('charty', {
                    type: 'listbox',
                    text: 'geo & map charts',
                    icon: 'icon dashicons dashicons-admin-site',
                    onselect: function (e) {
                        confirm("Warning : The plugin enables just one geo chart per page for the moment ! please add just one shortcode.") ? ed.insertContent(this.value()) : null;
                    },
                    values: [
                        <?php foreach($charty_posts as $charty_post):?>
                            { text: '<?php echo get_the_title($charty_post->ID); ?>', value: '[charty_shortcode id=<?php echo $charty_post->ID;?>]' },
                        <?php endforeach;?>
                    ]
                });
            },
            /**
             * Returns information about the plugin as a name/value array.
             * The current keys are longname, author, authorurl, infourl and version.
             *
             * @return {Object} Name/value array containing information about the plugin.
             */
            getInfo : function() {
                return {
                    longname : 'Shortcode selector for the charty plugin',
                    author : 'P-A Bru',
                    authorurl : 'http://pa-bru.fr',
                    infourl : 'https://github.com/pa-bru/charty',
                    version : "0.1"
                };
            }
        });
// Register plugin
        tinymce.PluginManager.add('charty', tinymce.plugins.charty);
})();
