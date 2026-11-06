Util.createCPObject('cpm.tradingin.inventory');

cpm.tradingin.inventory = {
    init: function(){
        $(".poLinked").livequery('click', function (e){
            var product_id = $(this).attr('product_id');
            var site_id = $(this).attr('site_id');
            Util.showProgressInd();

            var url = "index.php?module=tradingin_inventory&_spAction=purchaseOrderDisplay&product_id="+product_id+"&site_id="+site_id+"&showHTML=0";
            var exp = {
                url: url
            };

            Util.openDialogForLink('Purchase Orders Linked', 995, 410, 0, exp);
        });

        $(".billsLinked").livequery('click', function (e){
            var product_id = $(this).attr('product_id');
            var site_id = $(this).attr('site_id');
            Util.showProgressInd();

            var url = "index.php?module=tradingin_inventory&_spAction=orderDisplay&product_id="+product_id+"&site_id="+site_id+"&showHTML=0";
            var exp = {
                url: url
            };

            Util.openDialogForLink('Bills Linked', 700, 410, 0, exp);
        });

        /*$(".batchStock").livequery('click', function (e){
            var product_id = $(this).attr('product_id');
            var internal_location_id = $(this).attr('internal_location_id');
            Util.showProgressInd();

            var url = "index.php?module=tradingin_inventory&_spAction=batchWiseStockDisplay&product_id="+product_id+"&internal_location_id="+internal_location_id+"&showHTML=0";
            var exp = {
                url: url
            };

            Util.openDialogForLink('Batch Wise Stock', 700, 410, 0, exp);
        });*/

        $(".stockTransferredIn").livequery('click', function (e){
            var product_id = $(this).attr('product_id');
            var internal_location_id = $(this).attr('internal_location_id');
            Util.showProgressInd();

            var url = "index.php?module=tradingin_inventory&_spAction=stockTransferDisplay&product_id="+product_id+"&internal_location_id="+internal_location_id+"&type=stockIn&showHTML=0";
            var exp = {
                url: url
            };

            Util.openDialogForLink('Stack Transfer(In) Linked', 700, 410, 0, exp);
        });

        $(".stockTransferredOut").livequery('click', function (e){
            var product_id = $(this).attr('product_id');
            var internal_location_id = $(this).attr('internal_location_id');
            Util.showProgressInd();

            var url = "index.php?module=tradingin_inventory&_spAction=stockTransferDisplay&product_id="+product_id+"&internal_location_id="+internal_location_id+"&type=stockOut&showHTML=0";
            var exp = {
                url: url
            };

            Util.openDialogForLink('Stack Transfer(Out) Linked', 700, 410, 0, exp);
        });

        $(".stockUsedForPatient").livequery('click', function (e){
            var product_id = $(this).attr('product_id');
            var internal_location_id = $(this).attr('internal_location_id');
            Util.showProgressInd();

            var url = "index.php?module=tradingin_inventory&_spAction=stockTransferDisplay&product_id="+product_id+"&internal_location_id="+internal_location_id+"&type=usedPatient&showHTML=0";
            var exp = {
                url: url
            };

            Util.openDialogForLink('Used For Patient Linked', 700, 410, 0, exp);
        });

        $('.viewAllUpdatedAdjustStockHistory').livequery('click', function (){
            Util.showProgressInd();
            var inventory_batchwise_stock_id = $(this).attr('inventory_batchwise_stock_id');

            if(inventory_batchwise_stock_id != "") {
                var url = "index.php?_topRm=inventory&module=tradingin_inventory&_spAction=updatedAdjustStockHistory&inventory_batchwise_stock_id="+inventory_batchwise_stock_id+"&showHTML=0";
                var exp = {
                    url: url
                };

                Util.openDialogForLink('Adjust Stock History',  400, 300, 0, exp);
            } else {
                Util.hideProgressInd();
                Util.alert("There is no history records found!");
            }
        });

        $('.AdjustStockInventoryEditSaveBtn').livequery('click', function(){
            var mgsalert = 'Adjust Stock Updated Successfully!';
            var n = noty({
                text: mgsalert,
                type: 'confirm',
                dismissQueue: true,
                layout: 'topCenter',
                theme: 'defaultTheme',
                timeout: 5000,
            });
        });

        $('input[name=adjust_stock].AdjustStockInventoryEdit').livequery('change', function(){
            var adjust_stock  = $(this).val();
            var batch_no      = $(this).attr('batch_no');
            var product_id    = $(this).attr('product_id');
            var inventory_id  = $(this).attr('inventory_id');
            var po_product_id = $(this).attr('po_product_id');
            var stock         = $(this).attr('stock');
            var parent        = $(this).closest('tr');
//alert(stock);
            /*if(parseInt(adjust_stock) > parseInt(stock)) {
                Util.alert("Please enter adjust stock less than or equal to "+stock);
                $(this).val("0");
            } else {*/
                if(adjust_stock == "") {
                    adjust_stock = 0;
                }

                var url = 'index.php?module=tradingin_inventory&_spAction=createUpdateChangedStockRecord&showHTML=0';
                $.get(url, {batch_no: batch_no, po_product_id: po_product_id, product_id: product_id, inventory_id:inventory_id, adjust_stock:adjust_stock}, function(html){
                    var overallStock = parseInt(0);
                    overallStock = parseInt(stock) + parseInt(adjust_stock);
                    $('.overallStockForLocationWise', parent).html(overallStock);
                    $("span.stockUpdate").html(parseInt(html));
                    $("span.stockUpdateList_"+inventory_id).html(parseInt(html));
                });
            //}
        });

        $('.updatedCurrentStockToAllProduct').livequery('click', function(){
            var msg = "Are you sure to update the stock?";
            if (confirm(msg)){
                Util.showProgressInd("Updating your stock please wait.....");
                var url = "index.php?module=tradingin_inventory&_spAction=updateStockInInventoryAndProduct&showHTML=0";
                $.get(url, function(){
                    Util.hideProgressInd();
                    window.location.reload(true);
                });
            }
        });
    }
}