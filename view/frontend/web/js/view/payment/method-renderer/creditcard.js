/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern Europe GmbH
 * (abbreviated to Wirecard CEE) and are explicitly not part of the Wirecard CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 2 (GPLv2) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard CEE does not guarantee their full
 * functionality neither does Wirecard CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

define(
    [
        'jquery',
        'Wirecard_ElasticEngine/js/view/payment/method-renderer/default',
        'Magento_Ui/js/model/messageList'
    ],
    function ($, Component, globalMessageList) {
        'use strict';
        return Component.extend({
            tokenId: null,
            defaults: {
                template: 'Wirecard_ElasticEngine/payment/method-creditcard',
                redirectAfterPlaceOrder: false
            },
            seamlessFormInit: function () {
                WirecardPaymentPage.seamlessRenderForm({
                    requestData: this.config.seamless_request_data,
                    wrappingDivId: 'seamless-creditcard-form',
                    onSuccess: this.seamlessFormDummyHandler,
                    onError: this.seamlessFormErrorHandler
                });
            },
            seamlessFormSubmit: function() {
                WirecardPaymentPage.seamlessSubmitForm({
                    onSuccess: this.seamlessFormSubmitSuccessHandler,
                    onError: this.seamlessFormErrorHandler
                });
            },
            seamlessFormSubmitSuccessHandler: function (response) {
                this.tokenId = response.token_id;

                //ToDo - Fix placeOrder triggering
                this.placeOrder();
            },
            seamlessFormErrorHandler: function (response) {
                if (response.loader_error) {
                    globalMessageList.addErrorMessage({message: response.loader_error});
                }

                if (response.status_description_1) {
                    globalMessageList.addErrorMessage({message: response.status_description_1});
                }
            },
            seamlessFormDummyHandler: function (response) {
                console.log(response);
            },
            /**
             * Get payment method data
             */
            getData: function () {
                return {
                    'method': this.getCode(),
                    'po_number': null,
                    'additional_data': {
                        'token_id': this.tokenId
                    }
                };
            },
            placeOrder: function (data, event) {
                if (event) {
                    event.preventDefault();
                }

                if(!this.tokenId) {
                    this.seamlessFormSubmit();

                    return false;
                }

                return this._super();
            }
        });
    }
);
