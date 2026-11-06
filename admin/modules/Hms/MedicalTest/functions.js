Util.createCPObject('cpm.hms.medicalTest');

cpm.hms.medicalTest = {
    init: function(){
        $('.addNewValue').livequery('click', function (e){
        var title = "Add New Value";
        e.preventDefault();

        var valuelist_name = $(this).attr('valuelist_name');

        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                Util.closeAllDialogs();
                //window.location.reload(true);
                //$(".m-manPower_opportunity select[name='valuelist_value']").val(valuelist_value);

                var url = 'index.php?module=hms_medicalTest&_spAction=valueByValuelistJSON&showHTML=0';
                $.get(url, {valuelist_name: valuelist_name}, function (data) {
                    if(valuelist_name == 'investigationGroup'){
                        $('#fld_group_name').cp_loadSelect(data);
                    } 
                }, 'json');
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 400, 300, expObj);
        });


        /* Add Medical Parameters */
        $('#AddMedicalParameters').live('click', function (e){
                var title = "Add Medical Parameters";
                e.preventDefault();
                var medical_test_id = $(this).attr('medical_test_id');

                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Medical Parameters Added Successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            cpm.hms.medicalTest.reloadParameters(medical_test_id);
                            //window.location.reload(true);
                        });
                    }
                }
                Util.openFormInDialog.call(this, 'medicalParametersPortalForm', title, 600, 400, expObj);
        });

            /* Edit Medical Parameters */
        $('.EditMedicalParameters').live('click', function (e){
            var title = "Edit Medical Parameters";
            var medical_test_id = $(this).attr('medical_test_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();
                    Util.alert('Medical Parameters Updated Successfully');
                    Util.hideProgressInd();
                    cpm.hms.medicalTest.reloadParameters(medical_test_id);
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 600, 500, expObj);
        });


        /* Delete Medical Parameters */
        $('.deleteMedicalParameters').live('click', function (e){
            msg = "Do you like to delete the Medical Parameters?";
            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var medical_test_parameter_id = $(this).attr('medical_test_parameter_id');
                var medical_test_id = $(this).attr('medical_test_id');

                var url = 'index.php?module=hms_medicalTest&_spAction=DeleteMedicalParameters&showHTML=0&medical_test_parameter_id=' + medical_test_parameter_id;
                $.get(url, {medical_test_parameter_id: medical_test_parameter_id}, function(html){
                    Util.hideProgressInd();
                    cpm.hms.medicalTest.reloadParameters(medical_test_id);
                });
            }
        });
    },


    reloadParameters: function(medical_test_id){
        var url = 'index.php?module=hms_medicalTest&_spAction=medicalTestParameter&showHTML=0';
        $.get(url,{medical_test_id:medical_test_id}, function(html){
            $('#medicalTestLinkPortal').html(html);
            //Util.hideProgressInd();
        });
    },
}