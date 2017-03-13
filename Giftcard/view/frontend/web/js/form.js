/* Magedelight
 * Copyright (C) 2016 Magedelight <info@magedelight.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category Magedelight
 * @package Magedelight_Giftcard
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

define(
    [
        'jquery',
        'Magento_Ui/js/modal/modal',
        'mage/url'        
    ],
    function ($,modal,urlBuilder){
        "use strict";
        //creating jquery widget
        $.widget('Magedelight.modalForm', {
            options: {
                modalForm: '#modal-form',
                modalButton: '.open-modal-form'
            },
            _create: function () {
                this.options.modalOption = this._getModalOptions();
                this._bind();
            },
            _getModalOptions: function () {
                var options = {
                    type: 'popup',
                    responsive: true,
                    buttons: []
                };

                return options;
            },
            _bind: function () {
                var modalOption = this.options.modalOption;
                var modalForm = this.options.modalForm;

                $(document).on('click', this.options.modalButton, function () {
                    var url = urlBuilder.build('giftcard/product/preview');
                    
                    var optionData = $('#product_addtocart_form').serializeArray();
                    var imageUrl = $('#imageurl').val();
                    var expireTime = $('label.validity_form').attr('data-value');
                    optionData.push({name: 'code', value: 'XXXXXX'});
                    optionData.push({name: 'imageurl', value: imageUrl});
                    optionData.push({name: 'expiration_time', value: expireTime});
                    
                    $.ajax({
                        showLoader: true,
                        url: url,
                        data: optionData,
                        type: "POST"
                    }).done(function(data){
                        $(modalForm).html(data);
                        //Initialize modal
                        $(modalForm).modal(modalOption);
                        //open modal
                        $(modalForm).trigger('openModal');
                    });
                });
            }
        });
        return $.Magedelight.modalForm;
    }
);
