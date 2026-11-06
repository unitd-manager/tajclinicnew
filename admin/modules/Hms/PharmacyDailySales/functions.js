Util.createCPObject('cpm.hms.pharmacyDailySales');

cpm.hms.pharmacyDailySales = {
    init: function(){
    	$('input[name=sales_amount]').livequery('keyup', function(){
            cpm.hms.pharmacyDailySales.reloadTotalAmount();
        });

        $('input[name=excess_amount]').livequery('keyup', function(){
            cpm.hms.pharmacyDailySales.reloadTotalAmount();
        });
    },

    reloadTotalAmount: function(){
        var sales_amount  = $('#fld_sales_amount .value').html();
        var excess_amount = $('input[name=excess_amount]').val();
        var total         = parseFloat(0);

        if(sales_amount == ''){
            sales_amount = 0;
        }

        if(excess_amount == ''){
            excess_amount = 0;
        }

        total = parseFloat(sales_amount) + parseFloat(excess_amount);
        total = total.toFixed(2);

        $('.pharmacyTotalAmount').html(total);
    },
}