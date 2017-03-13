/*global $
require(['jquery',
        'jquery/ui',
        'mage/mage',
        'mage/translate'
        ], function ($) {
        jQuery = $;

        var dataForm = jQuery('#megamenu_form');
        dataForm.mage('validation');
        jQuery('.megamenu-save-continue').click(function () {
            jQuery('#menu_back').val('1');
            saveForm(jQuery);
        });
        jQuery('.megamenu-save').click(function () {
            jQuery('#menu_back').val('');
            saveForm(jQuery);
        });
        function saveForm(jQuery) {
            if (dataForm.valid()) {
                dataForm.submit();
            } else {
                jQuery('.mainroot').find('input.mage-error').each(function () {
                    var errorPlacement = jQuery(this);
                    var toggleObject = errorPlacement.parent().closest('li.dd-item').find('.expand.linktoggle');
                    toggleObject.hide();
                    toggleObject.siblings('.collapse').show();
                    toggleObject.siblings('.item-information').slideDown();
                    errorPlacement.parent().closest('li.dd-item').effect("shake");
                });
            }
        }
});*/