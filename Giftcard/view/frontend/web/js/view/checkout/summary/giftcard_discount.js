define(
   [
       'Magento_Checkout/js/view/summary/abstract-total',
       'Magento_Checkout/js/model/quote',
       'Magento_Catalog/js/price-utils',
       'Magento_Checkout/js/model/totals'
   ],
   function (Component, quote, priceUtils, totals) {
       "use strict";
       return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Magedelight_Giftcard/checkout/summary/giftcard_discount'
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,
            isDisplayed: function() {
                console.log(this.totals());
                console.log(this.getBaseValue());
                return this.isFullMode() && this.getBaseValue() != 0;
            },
            getValue: function(){
                var price = 0;
                if (this.totals()){
                    price = parseFloat(totals.getSegment('giftcard_discount').value);
                }
                return this.getFormattedPrice(price);
            },
            getBaseValue: function(){
                var price = 0;
                if (this.totals()){
                    price = parseFloat(totals.getSegment('giftcard_discount').value);
                }
                return price;
            }
       });
   }
);