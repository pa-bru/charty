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
                        ed.insertContent(this.value());
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
                    version : "1.2"
                };
            }
        });
        // Register plugin
        tinymce.PluginManager.add('charty', tinymce.plugins.charty);
})();
