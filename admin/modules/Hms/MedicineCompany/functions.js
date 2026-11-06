Util.createCPObject('cpm.hms.medicineCompany');

cpm.hms.medicineCompany = {
    init: function(){
        $('#AddProduct').live('click', function (e){
            e.preventDefault();
            var expObj = {
                beforeCloseFn: function(){
                    Util.closeAllDialogs();
                }
            }
            Util.openDialogForLink.call(this, 'Add Medicines', 400, 300, expObj);
        });

        $("input[name='product_title']").livequery(function(){
            var titleObj = this;
            $(titleObj).autocomplete({
                 source : 'index.php?module=hms_medicineCompany&_spAction=searchProductTitle&showHTML=0'
                ,minLength : 1
                ,selectFirst: true
                ,autoFocus: true
                ,select: function(event, ui) {
                    var selectedObj = ui.item;
                    var product_id = selectedObj.id
                    $("input[name='product_id[]']", parent).val(product_id);                
                    var medicine_company_id   = $(this).attr('medicine_company_id');

                    var url = 'index.php?module=hms_medicineCompany&_spAction=linkProductSubmit&showHTML=0';
                    $.get(url, {product_id: product_id, medicine_company_id:medicine_company_id}, function(html){
                        if(html == ''){
                            cpm.hms.medicineCompany.reloadMedicineTab(medicine_company_id);
                            alert('Medicine Added Successfully');
                            //alert(product_id);                  
                            $(".addExistingMedicine input[name='product_title']").focus();
                        } else{
                            $('.productExist').html(html);                            
                        }
                    });
                }
            });
        });

        $('.deleteProduct').live('click', function (e){
            msg = "Do you like to delete the Medicine?";
            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var medicine_company_id = $(this).attr('medicine_company_id');
                var product_id = $(this).attr('product_id');

                var url = 'index.php?module=hms_medicineCompany&_spAction=deleteLinkedProduct&showHTML=0';
                $.get(url, {product_id: product_id, medicine_company_id:medicine_company_id}, function(html){
                    Util.hideProgressInd();
                    cpm.hms.medicineCompany.reloadMedicineTab(medicine_company_id);
                });
            }
        });

        $("#productDisplayPortal input[name='offer_medicine']").livequery('change', function(){
            var offer_medicine = $(this).val();
            var product_id = $(this).attr('product_id');

            //Util.showProgressInd();
            var url = 'index.php?module=hms_medicineCompany&_spAction=updateOfferMedicine&showHTML=0';
            $.get(url, {offer_medicine: offer_medicine, product_id: product_id}, function(html){
                //Util.hideProgressInd();
            });
        });

        $('.medicineSave').livequery('click', function(){
            alert('Offer medicine saved successfully')
        });
    },
    reloadMedicineTab: function(medicine_company_id){
        var url = 'index.php?module=hms_medicineCompany&_spAction=productDisplay&showHTML=0';
        $.get(url, {medicine_company_id: medicine_company_id}, function(html){
            $('#productDisplayPortal').html(html);
            Util.hideProgressInd();
        });
    },
}