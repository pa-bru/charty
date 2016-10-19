jQuery(document).ready(function($) {
    var $radioGroup = $("input[type=radio][name=charty_type]");
    // Change the form on click radio buttons :
    $radioGroup.on("change", function () {
        if (this.value == 'map') {
            $('div[data-map]').slideDown(500);
            $('div[data-geochart]').slideUp(500);
        }
        else if (this.value == 'geo_chart') {
            $('div[data-geochart]').slideDown(500);
            $('div[data-map]').slideUp(500);
        }
    });
    //on load document if one type is already chosen (when update a chart) : we hide the other one :
    if ($("input[type=radio][name=charty_type]:checked").val() == 'map') {
        $('div[data-geochart]').hide();
        $('div[data-map]').show();
    }
    else if ($radioGroup.val() == 'geo_chart') {
        $('div[data-map]').hide();
        $('div[data-geochart]').show();
    }
    //Hide two charty types on init a new chart : (because no one is selected) :
    if (!$radioGroup.is(":checked")) {
        $('div[data-map]').hide();
        $('div[data-geochart]').hide();
    }
});