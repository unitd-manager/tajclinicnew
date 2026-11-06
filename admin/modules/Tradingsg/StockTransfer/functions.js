Util.createCPObject('cpm.tradingsg.stockTransfer');

cpm.tradingsg.stockTransfer = {
    init: function(){
        $(".addProduct input[name='product_title']")
        .livequery(cpm.tradingsg.stockTransfer.posProductTitle);
        
        $("#orderItems input[name='qty']").live('change', function(){
            var qty = $(this).val();
            var stock_transfer_id = $(this).attr('stock_transfer_id');
            var stock_transfer_history_id = $(this).attr('stock_transfer_history_id');
            var request_qty = $(this).parents('tr').find("input[name='request_qty']");
            var request_qty = parseInt(request_qty.val(), 10);
            var stock = parseInt($(this).attr('stock'), 10);
            
            if(stock < qty){
                Util.alert('The qty should be less than the stock qty');
                cpm.tradingsg.stockTransfer.reloadOrderItems(stock_transfer_id);
            } else {
                if(request_qty < qty){
                    Util.alert('The qty should be less than the request qty');
                    cpm.tradingsg.stockTransfer.reloadOrderItems(stock_transfer_id);
                }
                else{
                    var url = 'index.php?module=tradingsg_stockTransfer&_spAction=updateQtyOrderItem&showHTML=0';
                    $.get(url, {qty: qty, stock_transfer_history_id: stock_transfer_history_id, stock_transfer_id: stock_transfer_id}, function(html){
                      cpm.tradingsg.stockTransfer.reloadOrderItems(stock_transfer_id); 
                    });
                }
            }
        });

        $("#orderItems input[name='request_qty']").live('change', function(){
            var request_qty = $(this).val();
            var stock_transfer_id = $(this).attr('stock_transfer_id');
            var stock_transfer_history_id = $(this).attr('stock_transfer_history_id');
            var stock =parseInt($(this).attr('stock'), 10);
            if(stock < request_qty){
                Util.alert('The qty should be less than the stock qty');
                cpm.tradingsg.stockTransfer.reloadOrderItems(stock_transfer_id);
            } else {
                var url = 'index.php?module=tradingsg_stockTransfer&_spAction=updateRequestQtyOrderItem&showHTML=0';
                $.get(url, {request_qty: request_qty, stock_transfer_history_id: stock_transfer_history_id, stock_transfer_id: stock_transfer_id}, function(html){
                  cpm.tradingsg.stockTransfer.reloadOrderItems(stock_transfer_id); 
                });
            }
        });

        $('.deleteItem').livequery('click', function (){
            var url = 'index.php?module=tradingsg_stockTransfer&_spAction=deleteItem&showHTML=0';
            var stock_transfer_id = $(this).attr('stock_transfer_id');
            var stock_transfer_history_id = $(this).attr('stock_transfer_history_id');

            var msg = "Are you sure to delete this item?";
            if (confirm(msg)){
                Util.showProgressInd();
                $.get(url,  {stock_transfer_history_id:stock_transfer_history_id, stock_transfer_id: stock_transfer_id}, function(html){
                    cpm.tradingsg.stockTransfer.reloadOrderItems(stock_transfer_id);
                });
            }
        });

        $('.completeTransaction').livequery('click', function (){
            var stock_transfer_id     = $(this).attr('stock_transfer_id');
            var site_id               = $(this).attr('site_id');
            var stockTransfer_product = $('.stockTransfer_product_count').val();

            if(stockTransfer_product == 0 || stockTransfer_product == undefined){
                alert('Please add some products!');
                $('#fld_product_title').focus();

            }else{

                var msg = "Do you like to complete the transaction?";
                if (confirm(msg)){
                    Util.showProgressInd();
                    var url = 'index.php?module=tradingsg_stockTransfer&_spAction=updateCompleteTransactionProduct&showHTML=0';
                    $.get(url, {stock_transfer_id: stock_transfer_id}, function(html){
                      cpm.tradingsg.stockTransfer.reloadEditDisplay(stock_transfer_id, site_id); 
                    });
                }
            }
        });

        $('.rollbackChanges').livequery('click', function (){
            var stock_transfer_id      = $(this).attr('stock_transfer_id');
            var site_id                = $(this).attr('site_id');

            var msg = "Do you like to rollback the transaction?";
            if (confirm(msg)){
                Util.showProgressInd();
                var url = 'index.php?module=tradingsg_stockTransfer&_spAction=rollbackCompleteTransactionProduct&showHTML=0';
                $.get(url, {stock_transfer_id: stock_transfer_id}, function(html){
                  cpm.tradingsg.stockTransfer.reloadEditDisplay(stock_transfer_id, site_id); 
                });
            }
        });

        $('.deductFromStock').livequery('click', function (){
            var stock_transfer_id = $(this).attr('stock_transfer_id');
            var site_id = $(this).attr('site_id');

            var msg = "This action will deduct item(s) from the stock \n\n Would you like to continue?";
            if (confirm(msg)){
                Util.showProgressInd();
                var url = 'index.php?module=tradingsg_stockTransfer&_spAction=updateDeductStockProduct&showHTML=0';
                $.get(url, {stock_transfer_id: stock_transfer_id}, function(html){
                  cpm.tradingsg.stockTransfer.reloadEditDisplay(stock_transfer_id, site_id);
                });
            }
        });

        $("select[name='status']").live('change', function(){
            var product_status    = $(this).val();
            var stock_transfer_id = $('#record_id').val();
            var site_id           = $("input[name='site_id']").val();
            
            Util.showProgressInd();
            var url = 'index.php?module=tradingsg_stockTransfer&_spAction=updateStatusStockTransfer&showHTML=0';
            $.get(url, {product_status: product_status, stock_transfer_id: stock_transfer_id, site_id: site_id}, function(html){
                cpm.tradingsg.stockTransfer.reloadEditDisplay(stock_transfer_id, site_id); 
            });
            
        });

        /* Add the batch popup product to pos */
        $('.batchProductAdd').livequery('click', function(){
            var product_id        = $(this).attr('product_id');
            var batch_no          = $(this).attr('batch_no');
            var stock_transfer_id = $(this).attr('stock_transfer_id');
            var po_product_id     = $(this).attr('po_product_id');

            var urlBatchProduct = 'index.php?module=tradingsg_stockTransfer&_spAction=addBatchProductForStockTransfer&showHTML=0';
            Util.showProgressInd();
            
            $.get(urlBatchProduct, {stock_transfer_id: stock_transfer_id, product_id: product_id, batch_no: batch_no, po_product_id: po_product_id}, function(html){
                if(html.msg == "Please note the product is already added"){
                    alert(html.msg);
                    Util.hideProgressInd();
                }
                else{
                    $(".addProduct input[name='product_title']").val('');
                    cpm.tradingsg.stockTransfer.reloadOrderItems(stock_transfer_id);
                }
            });
            
        });

        $("select[name='from_location']").live('change', function(){
            var from_location = $(this).val();
            var url = 'index.php?module=tradingsg_stockTransfer&_spAction=toLocationJson&showHTML=0';
            $.get(url, {from_location: from_location}, function(data){
                $("select[name='to_location']").cp_loadSelect(data);
            },'json');
            
        });
    },

    posProductTitle: function() {
        var titleObj = this;
        var from_location = $("input[name=from_location]").val();
        $(titleObj).autocomplete({
             source : 'index.php?module=tradingsg_stockTransfer&_spAction=searchProductTitle&from_location='+from_location+'&showHTML=0'
            ,minLength : 1
            ,selectFirst: true
            ,autoFocus: true
            ,focus: function(event, ui) {
                var len = $('.ui-autocomplete > li').length;
                if(len === 1){
                    var selectedObj = ui.item;
        			var product_id  = selectedObj.id
                    var stock       = selectedObj.stock
                    var stock_transfer_id = $(this).attr('stock_transfer_id');
                        
                    var site_id = $("input[name=from_location]").val();
                    var urlBatchProduct = 'index.php?module=tradingsg_stockTransfer&_spAction=batchProductCountCheck&showHTML=0';
                    $.get(urlBatchProduct, {site_id: site_id, product_id: product_id, from_location: from_location}, function(html){
                        if(html > 1) {
                            Util.showProgressInd();
                            var url = "index.php?_topRm=inventory&module=tradingsg_stockTransfer&_spAction=BatchProductSelectStock&product_id="+product_id+"&stock_transfer_id="+stock_transfer_id+"&from_location="+site_id+"&stock="+stock+"&showHTML=0";
                            var exp = {
                                url: url
                            };

                            Util.openDialogForLink('Batch Product(s)',  850, 350, 0, exp); 
                        }
                        else{
                			$(this).after("<input type='hidden' name='product_id' value=" + product_id + ">");

                            //--------------------------------------------
                            Util.showProgressInd();
            	           	var url = 'index.php?module=tradingsg_stockTransfer&_spAction=updateOrderLineItems&showHTML=0';
                            $.get(url, {product_id: product_id, stock_transfer_id: stock_transfer_id, from_location: from_location}, function(json){
            	                cpm.tradingsg.stockTransfer.reloadOrderItems(stock_transfer_id);
            	                $(".addProduct input[name='product_title']").val('');
                                Util.hideProgressInd();
                            });
                            $(titleObj).autocomplete( "close" );
                        }
                    });
                }

                barcodeinput = 1;
            }
            ,select: function(event, ui) {
                barcodeinput = 1;
                var selectedObj = ui.item;
                var product_id  = selectedObj.id
                var stock       = selectedObj.stock
                var stock_transfer_id = $(this).attr('stock_transfer_id');
                var site_id = $("input[name=from_location]").val();

                var urlBatchProduct = 'index.php?module=tradingsg_stockTransfer&_spAction=batchProductCountCheck&showHTML=0';
                $.get(urlBatchProduct, {site_id: site_id, product_id: product_id, from_location: from_location}, function(html){
                    if(html > 1) {
                        Util.showProgressInd();
                        var url = "index.php?_topRm=inventory&module=tradingsg_stockTransfer&_spAction=BatchProductSelectStock&product_id="+product_id+"&stock_transfer_id="+stock_transfer_id+"&from_location="+site_id+"&stock="+stock+"&showHTML=0";
                        var exp = {
                            url: url
                        };

                        Util.openDialogForLink('Batch Product(s)',  850, 350, 0, exp);                         
                    }
                    else{
                        $(this).after("<input type='hidden' name='product_id' value=" + product_id + ">");

                        //--------------------------------------------
                        Util.showProgressInd();
                        var url = 'index.php?module=tradingsg_stockTransfer&_spAction=updateOrderLineItems&showHTML=0';
                        $.get(url, {product_id: product_id, stock_transfer_id: stock_transfer_id, from_location: from_location}, function(json){
                            cpm.tradingsg.stockTransfer.reloadOrderItems(stock_transfer_id);
                            $(".addProduct input[name='product_title']").val('');
                            Util.hideProgressInd();
                        });
                        $(titleObj).autocomplete( "close" );
                    }
                });
    		}
    	});
    },

    reloadOrderItems: function(stock_transfer_id){
        var url = 'index.php?module=tradingsg_stockTransfer&_spAction=orderItems&showHTML=0';
        $.get(url, {stock_transfer_id: stock_transfer_id}, function(html){
            $('#orderItems').html(html);
            Util.hideProgressInd();
        });
    },

    reloadEditDisplay: function(stock_transfer_id, site_id){
        var url = 'index.php?module=tradingsg_stockTransfer&_spAction=editDisplay&showHTML=0';
        $.get(url, {stock_transfer_id: stock_transfer_id, site_id:site_id}, function(html){
            $('#editDisplayLoad').html(html);
            Util.hideProgressInd();
        });
    }

}
cpm.tradingsg.stockTransfer.afterNewLocation = function(){
    Util.closeAllDialogs();
    Util.alert('New location successfully created.', function(){
        //cpm.tradingsg.purchaseOrder.loadSupplier();
        window.location.reload(true);
    });
}