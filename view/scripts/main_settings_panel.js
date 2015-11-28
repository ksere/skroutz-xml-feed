/*!
 * Copyright: 2014 Panagiotis Vagenas
 *
 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
 * @since 150120
 */


(function ($) {
    var extension = $xd_v141226_dev.$.extension_class('skroutz', '$ajax');

    $skroutz.$ajax = new extension();


    /**
     * Performs the request to generate the skroutz.xml file
     * @param $modalContainer
     */
    $skroutz.$ajax.generateXMLNow = function ($modalContainer) {
        var $genNowBtn = jQuery('.generate-now');

        $skroutz.$ajax.get(
            '©ajax.®ajaxGenerateSkroutzXML',
            $skroutz.$ajax.___private_type,
            [],
            {
                complete: function (response) {
                    var json = response.responseJSON;
                    if (json == undefined || json.success != true || json.data.productsUpdated == undefined) {
                        var message = json == undefined ? 'Something went awfully wrong!' : json.data;
                        $modalContainer.find('.modal-title').html('<p class="bg-danger">' + message + '</p>');
                    } else {
                        $modalContainer.find('.modal-title').html('<p class="bg-success">Generation Complete! ' + json.data.productsUpdated + ' products included in XML</p>');
                    }
                    $genNowBtn.removeClass('disabled').removeClass('loading');
                },
                beforeSend: function () {
                    $modalContainer.find('.modal-title').html('');
                    $genNowBtn.addClass('disabled').addClass('loading');
                },
                error: function(response, status, error){
                    $modalContainer.find('.modal-title').html('<p class="bg-danger">' + error + '</p>');
                }
            }
        );
    };

    /**
     *
     * @param text
     */
    $skroutz.$ajax.copyToClipboard = function (text) {
        window.prompt("Copy to clipboard: Ctrl+C, Enter", text);
    };

    $('.generate-now').click(function () {
        $skroutz.$ajax.generateXMLNow($('#generateNowModal'));
    });

    $('#show-advanced').change(function (e) {
        e.preventDefault();
        if ($(this).is(':checked')) {
            $('.main-settings-form-wrapper').find('.advanced').slideDown('fast');
        } else {
            $('.main-settings-form-wrapper').find('.advanced').slideUp('fast');
        }
    });

    $('#map-category').change(function(){
        var $maybeHide = $('.hide-if-not-product-tax');
        if($.isNumeric($(this).val())){
            $maybeHide.hide();
        } else {
            $maybeHide.show();
        }
    });

})(jQuery);