define(
    [
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Ui/js/modal/alert'
    ],
    function (urlBuilder, storage, alert) {
        'use strict';

        var showError = function(message, e)
        {
            alert( { content: message });

            if (typeof e != "undefined")
                console.error(e);
        };

        return function (selectedPaymentMethodId, callback)
        {
            var serviceUrl = urlBuilder.createUrl('/devlogicsol-tappay/payments/get_token', {});

            if (selectedPaymentMethodId)
            {
                return storage.post(
                    serviceUrl,
                    JSON.stringify({ paymentMethodId: selectedPaymentMethodId })
                ).always(callback);
                // return storage.post(
                //     serviceUrl,
                //     JSON.stringify({ paymentMethodId: selectedPaymentMethodId })
                // ).then(function(result, b, c)
                // {
                //     return result;
                // })
                // .fail(function(result)
                // {
                //     return showError("Sorry, a server side error has occurred.", result);
                // })
                // .always(callback);
            }
            else
            {
                return storage.post(serviceUrl).always(callback);
            }
        };
    }
);
