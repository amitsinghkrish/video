require(['jquery'], function($){
    /* for check in this file we only add jquery code that display(in console) class of element which selector used */
    function togglePrice(){
        var priceType = $('[name="product[giftcard_price_type]"]').val();
        if(priceType == 1){
            $('.admin__field[data-index="giftcard_balance"]').show().find('input').prop('disabled', false);
            $('.admin__field[data-index="giftcard_price_min"]').hide().find('input').prop('disabled', true);
            $('.admin__field[data-index="giftcard_price_max"]').hide().find('input').prop('disabled', true);
        }
        else{
            $('.admin__field[data-index="giftcard_balance"]').hide().find('input').prop('disabled', true);
            $('.admin__field[data-index="giftcard_price_min"]').show().find('input').prop('disabled', false);
            $('.admin__field[data-index="giftcard_price_max"]').show().find('input').prop('disabled', false);
        }
    }
    
    setTimeout(function(){
        togglePrice();
    },5000);
    
    $(document).on('change',"[data-index='giftcard_price_type'] select",function(){
        togglePrice();
    });
});