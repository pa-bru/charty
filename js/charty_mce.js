(function() {
//******* Load plugin specific language pack
//tinymce.PluginManager.requireLangPack('charty');
        tinymce.create('tinymce.plugins.charty', {
            init :  function(ed, url) {
                ed.addButton('charty', {
                    type: 'listbox',
                    text: 'geo & map charts',
                    icon: 'icon charty-icon',
                    onselect: function (e) {
                        confirm("Warning : The plugin enables just one geo chart (or map chart) per page for the moment !\nPlease add just one shortcode.") ? ed.insertContent(this.value()) : null;
                    },
                    values: charty_posts_js
                });
            },
            getInfo : function() {
                return {
                    longname : 'Shortcode selector for the charty plugin',
                    author : 'Paul-Adrien Bru',
                    authorurl : 'http://pa-bru.fr',
                    infourl : 'https://github.com/pa-bru/charty',
                    version : "1.0"
                };
            }
        });
        // Register plugin
        tinymce.PluginManager.add('charty', tinymce.plugins.charty);
})();
