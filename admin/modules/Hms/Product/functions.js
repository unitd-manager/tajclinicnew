Util.createCPObject('cpm.hms.product');

cpm.hms.product = {
    init: function(){
        $('#frmEdit select#fld_category_id').livequery('change', function(){
           Util.loadSubCategoryDropdown.call(this);
        });

        $('#frmEdit select#fld_product_group_id').livequery('change', function(){
           cpm.tradingsg.product.loadCategoryDropdown.call(this);
        });

        $("#bulkAddVouchers").livequery('click', function (e){
            var title = "Bulk Generate Voucher Codes";

            e.preventDefault();
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Generated successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        Links.reloadPortalRecords('tradingsg_product#ecommerce_productVoucherLink', 'tradingsg_product');
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 300, 150, expObj);
        });

        /* Add Dosage AgeWise */
        $('#AddDosageAgeWise').live('click', function (e){
                var title = "Add Dosage AgeWise";
                e.preventDefault();
                var product_id = $(this).attr('product_id');

                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Dosage AgeWise Added Successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            cpm.hms.product.reloadproductAge(product_id);
                            //window.location.reload(true);
                        });
                    }
                }
                Util.openFormInDialog.call(this, 'dosageAgeWisePortalForm', title, 600, 400, expObj);
        });

            /* Edit Dosage AgeWise */
        $('.EditDosageAgeWise').live('click', function (e){
            var title = "Edit Dosage AgeWise";
            var product_id = $(this).attr('product_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();
                    Util.alert('Dosage AgeWise Updated Successfully');
                    cpm.hms.product.reloadproductAge(product_id);
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 600, 500, expObj);
        });


        /* Delete Dosage AgeWise */
        $('.deleteDosageAgeWise').live('click', function (e){
            msg = "Do you like to delete the Dosage AgeWise?";
            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var dosage_agewise_id = $(this).attr('dosage_agewise_id');
                var product_id = $(this).attr('product_id');

                var url = 'index.php?module=hms_product&_spAction=DeleteDosageAgeWise&showHTML=0&dosage_agewise_id=' + dosage_agewise_id;
                $.get(url, {dosage_agewise_id: dosage_agewise_id}, function(html){
                    Util.hideProgressInd();
                    cpm.hms.product.reloadproductAge(product_id);
                });
            }
        });

        /* Add Dosage WeightWise */
        $('#AddDosageWeightWise').live('click', function (e){
                var title = "Add Dosage WeightWise";
                e.preventDefault();
                var product_id = $(this).attr('product_id');

                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Dosage WeightWise Added Successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            cpm.hms.product.reloadproductWeight(product_id);
                            //window.location.reload(true);
                        });
                    }
                }
                Util.openFormInDialog.call(this, 'dosageWeightWisePortalForm', title, 600, 400, expObj);
        });

            /* Edit Dosage WeightWise */
        $('.EditDosageWeightWise').live('click', function (e){
            var title = "Edit Dosage WeightWise";
            var product_id = $(this).attr('product_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();
                    Util.alert('Dosage WeightWise Updated Successfully');
                    cpm.hms.product.reloadproductWeight(product_id);
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 600, 500, expObj);
        });


        /* Delete Dosage WeightWise */
        $('.deleteDosageWeightWise').live('click', function (e){
            msg = "Do you like to delete the Dosage WeightWise?";
            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var dosage_wtwise_id = $(this).attr('dosage_wtwise_id');
                var product_id = $(this).attr('product_id');

                var url = 'index.php?module=hms_product&_spAction=DeleteDosageWeightWise&showHTML=0&dosage_wtwise_id=' + dosage_wtwise_id;
                $.get(url, {dosage_wtwise_id: dosage_wtwise_id}, function(html){
                    Util.hideProgressInd();
                    cpm.hms.product.reloadproductWeight(product_id);
                });
            }
        });

        /* Add Product Price*/
        $('#AddProductPrice').live('click', function (e){
                var title = "Add Product Price";
                var product_id = $(this).attr('product_id');
                e.preventDefault();
                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Price Added Successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            cpm.hms.product.reloadProductPriceLink(product_id);
                        });
                    }
                }
            Util.openFormInDialog.call(this, 'AddProductPriceForm', title, 462, 302, expObj);
        });

        /* Edit Product Price*/
        $('.EditProductPrice').live('click', function (e){
            var title = "Edit Product Price";
            e.preventDefault();
            var product_id = $(this).attr('product_id');
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Price Updated Successfully';
                    Util.alert(msg, function(){ 
                        Util.closeAllDialogs();
                        cpm.hms.product.reloadProductPriceLink(product_id);
                        cpm.hms.product.reloadProductPriceHistoryLink(product_id);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'EditProductPriceForm', title, 462, 302, expObj);
        });

        /* Add Branch Medicine */
        $('#AddBranchMedicine').live('click', function (e){
                var title = "Add Branch Medicine";
                e.preventDefault();
                var product_id = $(this).attr('product_id');

                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Branch Medicine Added Successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            cpm.hms.product.reloadbranchMedicine(product_id);
                            //window.location.reload(true);
                        });
                    }
                }
                Util.openFormInDialog.call(this, 'AddBranchMedicineForm', title, 600, 400, expObj);
        });

            /* Edit Branch Medicine */
        $('.EditBranchMedicine').live('click', function (e){
            var title = "Edit Branch Medicine";
            var product_id = $(this).attr('product_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();
                    Util.alert('Branch Medicine Updated Successfully');
                    cpm.hms.product.reloadbranchMedicine(product_id);
                }
            }
            Util.openFormInDialog.call(this, 'EditBranchMedicineForm', title, 600, 500, expObj);
        });


        /* Delete Branch Medicine */
        $('.deleteBranchMedicine').live('click', function (e){
            msg = "Do you like to delete the Branch Medicine?";
            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var medicine_site_id = $(this).attr('medicine_site_id');
                var product_id = $(this).attr('product_id');

                var url = 'index.php?module=hms_product&_spAction=DeleteBranchMedicine&showHTML=0&medicine_site_id=' + medicine_site_id;
                $.get(url, {medicine_site_id: medicine_site_id}, function(html){
                    Util.hideProgressInd();
                    cpm.hms.product.reloadbranchMedicine(product_id);
                });
            }
        });

        /*$(".ui-corner-all").livequery('click', function (e){
            Links.reloadPortalRecords('tradingsg_product#tradingsg_companyLink', 'tradingsg_product');
        });*/
    },

    loadCategoryDropdown: function(){
        $(this).each(function(){
            ProductGroupId = $(this).val();
            var url = $('#scopeRootAlias').val() + 'index.php?module=tradingsg_product&_spAction=categoryJsonByProductGroupId&showHTML=0'

            $.getJSON(url, {product_group_id: ProductGroupId}, function(data) {
                $('#frmEdit select#fld_category_id').cp_loadSelect(data);
            });
        });
    },

    reloadproductAge: function(product_id){
        var url = 'index.php?module=hms_product&_spAction=AddDosageAgeWise&showHTML=0';
        $.get(url,{product_id:product_id}, function(html){
            $('#DosageAgeWiseLinkPortal').html(html);
            //Util.hideProgressInd();
        });
    },

    reloadproductWeight: function(product_id){
        var url = 'index.php?module=hms_product&_spAction=AddDosageWeightWise&showHTML=0';
        $.get(url,{product_id:product_id}, function(html){
            $('#DosageWeightWiseLinkPortal').html(html);
            //Util.hideProgressInd();
        });
    },

    reloadProductPriceLink: function(product_id){
        var url = 'index.php?module=hms_product&_spAction=ProductPriceDetail&showHTML=0';
        $.get(url, {product_id: product_id}, function(html){
            $('#productPriceLinkPortal').html(html);
        });
    },

    reloadProductPriceHistoryLink: function(product_id){
        var url = 'index.php?module=hms_product&_spAction=ProductPriceHistory&showHTML=0';
        $.get(url, {product_id: product_id}, function(html){
            $('#productPriceHistoryLinkPortal').html(html);
        });
    },

    reloadbranchMedicine: function(product_id){
        var url = 'index.php?module=hms_product&_spAction=AddBranchMedicine&showHTML=0';
        $.get(url,{product_id:product_id}, function(html){
            $('#BranchMedicineLinkPortal').html(html);
            //Util.hideProgressInd();
        });
    },

    publishQuoteRecordFromList: function(room, rowID, currentValue, reUploadRecord){

        if(reUploadRecord){
            reUpload = 1;
        } else {
            reUpload = 0;
        }

        var url = $('#scopeRootAlias').val() + "index.php?_spAction=publishQuoteRecordByID&showHTML=0";

        var cell = "#txt__general_quote__" + rowID

        $(cell).html('processing');
        var data = {
             record_id: rowID
            ,room: room
            ,currentValue: currentValue
            ,reUpload: reUpload
        };
        $.post(url, data, function (data) {
            $(cell).html(data);
        });

    }
}


