Util.createCPObject('cpm.hms.prescription');

cpm.hms.prescription = {
    init: function(){

        /* Add Prescribe Medicine */
        $('#AddPrescribeMedicine').live('click', function (e){
                var title = "Add Prescribe Medicine";
                e.preventDefault();
                var prescription_id = $(this).attr('prescription_id');

                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Prescribe Added Successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            cpm.hms.prescription.reloadprescription(prescription_id);
                            //window.location.reload(true);
                        });
                    }
                }
                Util.openFormInDialog.call(this, 'perscriptionMedicinePortalForm', title, 600, 400, expObj);
        });

            /* Edit Prescribe Medicine */
        $('.EditPrescribeMedicine').live('click', function (e){
            var title = "Edit Prescribe Medicine";
            var prescription_id = $(this).attr('prescription_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();
                    Util.alert('Prescribe Updated Successfully');
                    cpm.hms.prescription.reloadprescription(prescription_id);
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 600, 500, expObj);
        });


        /* Delete Prescribe Medicine */
        $('.deletePrescribeMedicine').live('click', function (e){
            msg = "Do you like to delete the Prescribe Medicine?";
            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var prescribe_medicine_id = $(this).attr('prescribe_medicine_id');
                var prescription_id = $(this).attr('prescription_id');

                var url = 'index.php?module=hms_prescription&_spAction=DeletePrescribeMedicine&showHTML=0&prescribe_medicine_id=' + prescribe_medicine_id;
                $.get(url, {prescribe_medicine_id: prescribe_medicine_id}, function(html){
                    Util.hideProgressInd();
                    cpm.hms.prescription.reloadprescription(prescription_id);
                });
            }
        });

        $("input[name='medicine_name']")
        .livequery(cpm.hms.prescription.searchProductTitle);

    },

    reloadprescription: function(prescription_id){
        var url = 'index.php?module=hms_prescription&_spAction=AddPrescribeMedicine&showHTML=0';
        $.get(url,{prescription_id:prescription_id}, function(html){
            $('#PrescribeMedicineLinkPortal').html(html);
            //Util.hideProgressInd();
        });
    },

    searchProductTitle: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?module=hms_prescription&_spAction=searchProductTitle&showHTML=0'
            ,minLength : 3
            ,select: function(event, ui) {
                var selectedObj = ui.item;
                var product_id = selectedObj.id
                $('input[name=product_id]').val(product_id);
            }
        });
    },

}

