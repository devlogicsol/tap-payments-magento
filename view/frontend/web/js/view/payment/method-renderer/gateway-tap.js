define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'devlogicsol_TapPay/js/action/get-token',
        'mage/url',
        'mage/translate'
    ],
    function(
        ko,
        $,
        Component,
        placeOrderAction,
        selectPaymentMethodAction,
        customer,
        checkoutData,
        additionalValidators,
        customerData,
        quote,
        totals,
        getTokenAction,
        urlBuilder,
        $t
    ) {
        'use strict';

        console.log(window.checkoutConfig);
        var active_pk = window.checkoutConfig.payment.tap.active_pk;
        var token_returned = document.getElementById('token');
        var post_url = window.checkoutConfig.payment.tap.post_url;
        var ui_mode = window.checkoutConfig.payment.tap.uimode;
        console.log(ui_mode);
        if (ui_mode == 'token') {
            var template_path = 'devlogicsol_TapPay/payment/gateway';
        } else {
            template_path = 'devlogicsol_TapPay/payment/gateway2';
        }
        var response_url = window.checkoutConfig.payment.tap.responseUrl;
        var config_trans_mode = window.checkoutConfig.payment.tap.transaction_mode;
        var knet = window.checkoutConfig.payment.tap.knet;
        var guest_customerdata = customerData.get('checkout-data')();

        var response_url = '';
        var post_url = '';
        var firstname = '';
        var lastname = '';
        var email = '';
        var phone = '';
        var currency_code = '';
        var amount = '';
        //var config_trans_mode = '';
        // if (!customer.isLoggedIn()) {
        // 	var email =  guest_customerdata.inputFieldEmailValue;
        // 	var firstname = guest_customerdata.shippingAddressFromData.firstname;
        // 	var lastname = guest_customerdata.shippingAddressFromData.lastname;
        // 	var phone = guest_customerdata.shippingAddressFromData.telephone;
        // }
        // else {
        // 	var email = window.checkoutConfig.customerData.email;
        // 	var firstname = window.checkoutConfig.customerData.firstname;
        // 	var lastname = window.checkoutConfig.customerData.lastname;
        // 	var phone = '';
        // }
        var middlename = '';
        var country_code = '';
        var cart_items = window.checkoutConfig.quoteItemData;
        var tap_args = [];
        cart_items.forEach(function(sl_item) {
            tap_args.push({
                id: sl_item.item_id,
                name: sl_item.name,
                description: sl_item.description,
                quantity: sl_item.qty,
                amount_per_unit: sl_item.base_price,
                discount: {
                    type: 'P',
                    value: '10%'
                },
                total_amount: sl_item.base_price
            })

        });
        //var config_trans_mode = 'capture';
        var total = quote.getTotals()();
        console.log()
        var total = quote.getTotals()();
        console.log(total);
        var qoute_total_amount = total.grand_total;
        var orderId = window.checkoutConfig.payment.tap.orderId;
        // console.log(total.total_segments);
        // var total_segments = total.total_segments;
        // var obj = {};
        // for (var i = 0; i < total_segments.length; ++i){
        // 	obj[total_segments[i].code] = total_segments[i];
        // }
        // console.log(obj.partial_pay_now.value);


        //var partial_total = window.checkoutConfig.quoteData.partial_pay_now;
        //partial_total = obj.partial_pay_now.value;
        var amount = window.checkoutConfig.totalsData.base_grand_total;

        //console.log(amount);
        //console.log(window.checkoutConfig.totalsData.base_grand_total);
        //console.log(partial_total);
        // if (partial_total ==="") {
        // 	var total_amount = amount;
        // }
        // else {
        // 	var total_amount = partial_total;
        // }

        //if (typeof(partial_total) != "undefined" || partial_total.length > 0) {
        //var total_amount = partial_total
        //} else {
        //var total_amount = amount;
        // if (partial_total == 0.000) {
        // 	var total_amount = amount;
        // }
        // else {
        // 	var total_amount = obj.partial_pay_now.value;
        // }


        // console.log(partial_total);

        var currency_code = window.checkoutConfig.quoteData.quote_currency_code;
        var total_amount = qoute_total_amount;
        //console.log(amount);
        // 		if (ui_mode == 'popup' || ui_mode == 'redirect') {



        // 			if (config_trans_mode == 'authorize') {
        // 					var object_trans = {
        // 						mode :'authorize',
        // 						authorize:{
        // 		            			auto:{
        // 		              				type:'VOID', 
        // 		              				time: 100
        // 		            			},
        // 		            	saveCard: false,
        // 		            	threeDSecure: true,
        // 		            	description: "description",
        // 		            	statement_descriptor:"statement_descriptor",
        // 		            	reference:{
        // 		              							transaction: "txn_0001",
        // 		              							order: orderId
        // 		            						},
        // 		            					metadata:{},
        // 		            					receipt:{
        // 		              					email: false,
        // 		              					sms: true
        // 		            					},
        // 		            				redirect: response_url,
        // 		            				post: post_url

        // 										}
        // 								}
        // 			}

        // 		}

        return Component.extend({
            showTapCCBlock: ko.observable(true),
            selectPaymentType: function(type) {
                console.log(type);
                this.showTapCCBlock((type == 'CC'));
            },
            initObservable: function() {
                this._super()
                    .observe([
                        // 'paymentElement',
                        'isPaymentFormComplete',
                        'isPaymentFormVisible',
                        // 'isLoading',
                        // 'permanentError',
                        'isOrderPlaced',

                        // Saved payment methods dropdown
                        'dropdownOptions',
                        'selection',
                        'isDropdownOpen'
                    ]);

                var self = this;

                this.isPaymentFormVisible(false);
                // this.isOrderPlaced(false);

                // var currentTotals = quote.totals();

                return this;
            },
            newPaymentMethod: function() {
                this.selection({
                    // type: 'new',
                    value: 'new',
                    icon: false,
                    label: $t('New card')
                });
                this.isDropdownOpen(false);
                this.isPaymentFormVisible(true);
            },
            initSavedPaymentMethods: function() {
                var cards = window.checkoutConfig.payment.tap.saved_cards;
                var options = [];

                for (const i in cards)
                    options.push(cards[i]);

                if (options.length > 0) {
                    this.isPaymentFormVisible(false);
                    this.selection(options[0]);
                } else {
                    this.isPaymentFormVisible(true);
                    this.selection(false);
                }

                this.dropdownOptions(options);
            },
            toggleDropdown: function() {
                this.isDropdownOpen(!this.isDropdownOpen());
            },
            getPaymentMethodId: function()
            {
                var selection = this.selection();

                if (selection && typeof selection.value != "undefined" && selection.value != "new")
                    return selection.value;

                return null;
            },
            reloadPayment: function() {
                var self = this;

                fullScreenLoader.startLoader();
                jQuery.ajax({
                    url: URL,
                    type: "POST",
                    data: {
                        checked: self.creditValue(),
                        quote_id: quoteId
                    },
                    success: function(response) {
                        if (response) {
                            var deferred = jQuery.Deferred();
                            getTotalsAction([], deferred);
                            // var deferred = $.Deferred();
                            fullScreenLoader.stopLoader();
                            getPaymentInformationAction(deferred);
                            jQuery.when(deferred).done(function() {
                                isApplied(false);
                                totals.isLoading(false);
                            });
                            messageContainer.addSuccessMessage({
                                'message': message
                            });
                            totals.isLoading(true);


                        }
                    }
                });
            },
            initialize: function() {
                this._super();
                this.initSavedPaymentMethods();
                return this;
            },

            defaults: {
                template: template_path
            },

            navigate: function() {
                var self = this;
                getPaymentInformation().done(function() {
                    self.isVisible(true);
                });
            },

            placeOrder: function(data, event) {
                console.log(data);
                if (event) {
                    event.preventDefault();
                }

                var self = this,
                    // placeOrder,
                    emailValidationResult = customer.isLoggedIn(),
                    loginFormSelector = 'form[data-role=email-with-possible-login]';

                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }

                if (!emailValidationResult || !this.validate() || !additionalValidators.validate())
                    return;

                this.isPlaceOrderActionAllowed(false);
                var placeNewOrder = this.placeNewOrder.bind(this);

                console.log(this.getPaymentMethodId());
                if(this.getPaymentMethodId()) {
                    
                    // if(window.checkoutConfig.amastyRecurringConfig && window.checkoutConfig.amastyRecurringConfig.isRecurringProducts) {
                    //     // console.log(this.getPaymentMethodId());
                    //     // token = this.getPaymentMethodId();
                    //     var tokenElement = document.getElementById('token');
                    //     tokenElement.textContent = this.getPaymentMethodId();
                    //     placeNewOrder();
                    // } else {
                        $.when(getTokenAction(this.getPaymentMethodId(), function(result, outcome, response){
                            console.log(result, outcome, response);
                            // var tokenElement = document.getElementById('token');
                            // tokenElement.textContent = result;
                            placeNewOrder();
                        })).done(this.onGetToken.bind(this));
                    // }
                } else {
                    $('#tap-btn').click();
                    placeNewOrder();
                }
                
                // placeOrder = placeOrderAction(this.getData(), false, this.messageContainer);

                // $.when(placeOrder).fail(function() {
                //     self.isPlaceOrderActionAllowed(true);
                // }).done(this.afterPlaceOrder.bind(this));
                // return true;
                return false;
            },

            onGetToken: function(result, outcome, response)
            {
                console.log(result);
                var tokenElement = document.getElementById('token');
                tokenElement.textContent = result;
            },

            placeNewOrder: function()
            {
                var self = this;
                this.getPlaceOrderDeferredObject()
                    .fail(function() {
                        self.isPlaceOrderActionAllowed(true);
                    })
                    .done(this.afterPlaceOrder.bind(this));
            },

            getPlaceOrderDeferredObject: function () {
                return $.when(
                    placeOrderAction(this.getData(), false, this.messageContainer)
                );
            },

            getMailingAddress: function() {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },

            getfinalAmount: function() {
                return total_amount;
            },

            getPaymentAcceptanceMarkSrc: function() {
                return window.checkoutConfig.payment.tap.paymentAcceptanceMarkSrc;
            },

            getKnetAtCheckout: function() {
                if (window.checkoutConfig.payment.tap.knet == 0) {
                    return false;
                } else {
                    return true;
                }
            },
            getBenefitAtCheckout: function() {
                if (window.checkoutConfig.payment.tap.benefit == 0) {
                    return false;
                } else {
                    return true;
                }
            },

            getKnet: function() {
                this.selectPaymentType('CC')
                return 'KNET';
            },

            getbenefit: function() {
                return 'BENEFIT';
            },

            getbutton: function() {
                if (ui_mode == 'token') {
                    return true;
                } else {
                    return false;
                }
            },

            getNaps: function() {
                return 'NAPs';
            },

            getcss: function() {

                if (ui_mode == 'token') {
                    require(["goSell"],
                        function(goSell) {
                            var tap = Tapjsli(active_pk);
                            var elements = tap.elements({});
                            var style = {
                                base: {
                                    color: '#535353',
                                    lineHeight: '18px',
                                    fontFamily: 'sans-serif',
                                    fontSmoothing: 'antialiased',
                                    fontSize: '16px',
                                    '::placeholder': {
                                        color: 'rgba(0, 0, 0, 0.26)',
                                        fontSize: '15px'
                                    }
                                },
                                invalid: {
                                    color: 'red'
                                }
                            };

                            var labels = {
                                cardNumber: "Card Number",
                                expirationDate: "MM/YY",
                                cvv: "CVV",
                                cardHolder: "Card Holder Name"
                            };
                            //payment options
                            var paymentOptions = {
                                currencyCode: "all",
                                labels: labels,
                                TextDirection: 'ltr'
                            }
                            //create element, pass style and payment options
                            var card = elements.create('card', {
                                backgroundImg: {
                                    url: '../../../../images/gray_bg.png',
                                    opacity: '0.5'
                                },
                                style: style
                            }, paymentOptions);
                            //mount element
                            card.mount('#element-container');
                            //card change event listener
                            card.addEventListener('change', function(event) {
                                if (event.BIN) {
                                    console.log(event.BIN)
                                }
                                if (event.loaded) {
                                    console.log("UI loaded :" + event.loaded);
                                    console.log("current currency is :" + card.getCurrency())
                                }
                                var displayError = document.getElementById('error-handler');
                                if (event.error) {
                                    displayError.textContent = event.error.message;
                                } else {
                                    displayError.textContent = '';
                                }
                            });

                            // Handle form submission
                            var form = document.getElementById('form-container');
                            form.addEventListener('submit', function(event) {
                                event.preventDefault();

                                tap.createToken(card).then(function(result) {
                                    console.log(result);
                                    if (result.error) {
                                        // Inform the user if there was an error
                                        var errorElement = document.getElementById('error-handler');
                                        errorElement.textContent = result.error.message;
                                    } else {
                                        // Send the token to your server
                                        var errorElement = document.getElementById('success');
                                        errorElement.style.display = "block";
                                        var tokenElement = document.getElementById('token');
                                        tokenElement.textContent = result.id;
                                        console.log(result.id);
                                        //return result.id;
                                    }
                                });
                            });
                        }
                    )
                } else {
                    return false;
                }
            },

            afterPlaceOrder: function() {
                if (ui_mode == 'popup') {
                    var self = this;
                    var AjaxDataResponse;
                    $.ajax({
                        type: 'POST',
                        // url: urlBuilder.build("Standard/Success"),
                        url: window.checkoutConfig.payment.tap.redirectUrl,
                        async: false,
                        data: {
                            email: 'test@gmail.com',
                        },

                        /**
                         * Success callback
                         * @param {Object} response
                         */
                        success: function(response) {


                            //var response = JSON.parse(response);
                            // console.log(response);
                            AjaxDataResponse = response;
                            // console.log(response.customer.first_name);
                            // orderId = response.reference.order;
                            // response_url = response.redirect;
                            // post_url = response.post;
                            // firstname22 = response.customer.first_name;
                            // lastname = response.customer.last_name;
                            // email = response.customer.email;
                            // phone = response.customer.phone.number;
                            // currency_code = response.currency;
                            // amount = response.amount;
                            // console.log(response)
                            // myObj.cars[0];
                            //console.log(response->id);


                            // if (response.customer.first_name) {
                            //     self.renderCheckout(response);

                            // } 
                        }
                    });

                    console.log("----our variables----");
                    console.log(AjaxDataResponse);
                    //console.log(AjaxDataResponse.redirect);
                    var country_code = '965';
                    response_url = AjaxDataResponse.redirect.url;
                    post_url = AjaxDataResponse.post.url;
                    firstname = AjaxDataResponse.customer.first_name;
                    lastname = AjaxDataResponse.customer.last_name;
                    email = AjaxDataResponse.customer.email;
                    phone = AjaxDataResponse.customer.phone.number;
                    currency_code = AjaxDataResponse.currency;
                    amount = AjaxDataResponse.amount;
                    orderId = AjaxDataResponse.reference.order;
                    console.log(country_code);
                    console.log(post_url);
                    console.log(firstname);
                    console.log(phone);
                    console.log(email);
                    console.log(response_url);
                    console.log(currency_code);
                    console.log(amount);
                    console.log(orderId);
                    console.log(config_trans_mode);
                    if (config_trans_mode == 'capture') {
                        var object_trans = {
                            mode: 'charge',
                            charge: {
                                saveCard: false,
                                threeDSecure: true,
                                description: orderId,
                                statement_descriptor: "Sample",
                                reference: {
                                    transaction: "txn_0001",
                                    order: orderId
                                },
                                metadata: {},
                                receipt: {
                                    email: false,
                                    sms: true
                                },
                                redirect: response_url,
                                post: post_url
                            }
                        }
                    }

                    if (config_trans_mode == 'authorize') {
                        var object_trans = {
                            mode: 'authorize',
                            authorize: {
                                auto: {
                                    type: 'VOID',
                                    time: 100
                                },
                                saveCard: false,
                                threeDSecure: true,
                                description: "description",
                                statement_descriptor: "statement_descriptor",
                                reference: {
                                    transaction: "txn_0001",
                                    order: orderId
                                },
                                metadata: {},
                                receipt: {
                                    email: false,
                                    sms: true
                                },
                                redirect: response_url,
                                post: post_url
                            }
                        }
                    }

                }

                if (ui_mode == 'popup' || ui_mode == 'redirect') {
                    require(["goSellJs"],
                        function(goSell) {
                            goSell.config({
                                gateway: {
                                    publicKey: active_pk,
                                    language: "en",
                                    contactInfo: true,
                                    supportedCurrencies: "all",
                                    supportedPaymentMethods: "all",
                                    saveCardOption: true,
                                    customerCards: true,
                                    notifications: 'standard',
                                    callback: (response) => {
                                        console.log("response", response);
                                    },
                                    labels: {
                                        cardNumber: "Card Number",
                                        expirationDate: "MM/YY",
                                        cvv: "CVV",
                                        cardHolder: "Name on Card",
                                        actionButton: "Pay"
                                    },
                                    style: {
                                        base: {
                                            color: '#535353',
                                            lineHeight: '18px',
                                            fontFamily: 'sans-serif',
                                            fontSmoothing: 'antialiased',
                                            fontSize: '16px',
                                            '::placeholder': {
                                                color: 'rgba(0, 0, 0, 0.26)',
                                                fontSize: '15px'
                                            }
                                        },
                                        invalid: {
                                            color: 'red',
                                            iconColor: '#fa755a '
                                        }
                                    }
                                },
                                customer: {
                                    id: "",
                                    first_name: firstname,
                                    middle_name: middlename,
                                    last_name: lastname,
                                    email: email,
                                    phone: {
                                        country_code: country_code,
                                        number: phone
                                    }
                                },
                                order: {
                                    amount: total_amount,
                                    currency: currency_code,
                                    items: tap_args,
                                    shipping: null,
                                    taxes: null
                                },

                                transaction: object_trans
                            });

                        }
                    )
                }

                require(["goSellJs"],
                    function(goSell) {
                        console.log(object_trans);

                        var payment_type_mode = jQuery("input[name='payment_type']:checked").val();
                        console.log(ui_mode);
                        if (ui_mode == 'redirect' && payment_type_mode == 'CC') {
                            $.mage.redirect(window.checkoutConfig.payment.tap.redirectUrl + '?' + 'redirect=redirect');
                            //console.log('inredirect');
                            //goSell.openPaymentPage();	
                        } else if (ui_mode == 'popup' && payment_type_mode == 'CC') {
                            console.log('opppppp');
                            goSell.openLightBox();
                        }
                        if (ui_mode == 'token') {
                            var config_trans_mode = '';
                            console.log(ui_mode);
                            var token = document.getElementById("token").innerHTML;
                            console.log(token);
                        }

                        console.log(payment_type_mode);
                        if (payment_type_mode == 'charge_knet') {
                            $.mage.redirect(window.checkoutConfig.payment.tap.redirectUrl + '?' + 'knet=knet');
                        }

                        if (payment_type_mode == 'benefit') {
                            $.mage.redirect(window.checkoutConfig.payment.tap.redirectUrl + '?' + 'benefit=benefit');
                        }


                        if (ui_mode == 'token' && payment_type_mode == 'CC') {
                            $.mage.redirect(window.checkoutConfig.payment.tap.redirectUrl + '?' + 'token=' + token);
                        }
                    }
                )

            }
        })
    })