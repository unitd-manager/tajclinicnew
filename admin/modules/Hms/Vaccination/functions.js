Util.createCPObject('cpm.hms.vaccination');

cpm.hms.vaccination = {
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

                var url = 'index.php?module=hms_vaccination&_spAction=valueByValuelistJSON&showHTML=0';
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
                            cpm.hms.vaccination.reloadParameters(medical_test_id);
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
                    cpm.hms.vaccination.reloadParameters(medical_test_id);
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

                var url = 'index.php?module=hms_vaccination&_spAction=DeleteMedicalParameters&showHTML=0&medical_test_parameter_id=' + medical_test_parameter_id;
                $.get(url, {medical_test_parameter_id: medical_test_parameter_id}, function(html){
                    Util.hideProgressInd();
                    cpm.hms.vaccination.reloadParameters(medical_test_id);
                });
            }
        });

        /* Add Medical Test Group */
        $('#AddMedicalTestGroup').live('click', function (e){
            var title = "Add Group Name";
            e.preventDefault();
            var medical_test_id = $(this).attr('medical_test_id');

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Group Added Successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.vaccination.reloadMedicalTestGroup(medical_test_id);
                        //window.location.reload(true);
                    });
                }
            }

            Util.openFormInDialog.call(this, 'medicalTestGroupPortalForm', title, 500, 250, expObj);
        });

        
        /* Edit Medical Test Group */
        $('.EditMedicalTestGroup').live('click', function (e){
            var title = "Edit Group";
            var medical_test_id = $(this).attr('medical_test_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();
                    Util.alert('Group Updated Successfully');
                    Util.hideProgressInd();
                    cpm.hms.vaccination.reloadMedicalTestGroup(medical_test_id);
                }
            }
            Util.openFormInDialog.call(this, 'medicalTestGroupEditPortalForm', title, 500, 250, expObj);
        });

        /* Delete Medical Test Group */
        $('.deleteMedicalTestGroup').live('click', function (e){
            msg = "Do you like to delete the Group?";
            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var medical_test_group_id = $(this).attr('medical_test_group_id');
                var medical_test_id = $(this).attr('medical_test_id');

                var url = 'index.php?module=hms_vaccination&_spAction=DeleteMedicalTestGroup&showHTML=0&medical_test_group_id=' + medical_test_group_id;
                $.get(url, {medical_test_group_id: medical_test_group_id}, function(html){
                    Util.hideProgressInd();
                    cpm.hms.vaccination.reloadMedicalTestGroup(medical_test_id);
                    //window.location.reload(true);
                });
            }
        });
        
    },


    reloadParameters: function(medical_test_id){
        var url = 'index.php?module=hms_vaccination&_spAction=medicalTestParameter&showHTML=0';
        $.get(url,{medical_test_id:medical_test_id}, function(html){
            $('#medicalTestLinkPortal').html(html);
            //Util.hideProgressInd();
        });
    },

    reloadMedicalTestGroup: function(medical_test_id){
        var url = 'index.php?module=hms_vaccination&_spAction=medicalTestGroupLink&showHTML=0';
        $.get(url,{medical_test_id:medical_test_id}, function(html){
            $('#medicalTestGroupLinkPortal').html(html);
            //Util.hideProgressInd();
        });
    },
}