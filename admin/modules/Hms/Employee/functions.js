Util.createCPObject('cpm.hms.employee');

cpm.hms.employee.init = function(){
    $('.m-hms_employee select[name=employee_work_type]').livequery('change', function (e){
        var employee_work_type = $(this).val();

        if(employee_work_type == 'Part time'){
            $('.m-hms_employee .addHourlyRate').show();
        } else {
            $('.m-hms_employee .addHourlyRate').hide();
        }
    });

    $('.m-hms_employee select[name=employee_work_type]').livequery('change', function (e){
        var employee_work_type = $(this).val();

        if(employee_work_type == 'Full Time'){
            $('.m-hms_employee .salaryForFullTime').show();
        } else {
            $('.m-hms_employee .salaryForFullTime').hide();
        }
    });

    $('.addNewValue').livequery('click', function (e){
        var title = "Add New Value";
        e.preventDefault();

        var valuelist_name = $(this).attr('valuelist_name');
        
        var expObj = {
            validate: true
           ,callbackOnSuccess: function(data){
                Util.closeAllDialogs();
                var newValue = data.returnUrl;
                var url = 'index.php?module=hms_employee&_spAction=valueByValuelistJSON&showHTML=0';
                $.get(url, {valuelist_name: valuelist_name}, function (data) {
                    if(valuelist_name == 'positionType'){
                        $('#fld_position').cp_loadSelect(data);
                        $('#fld_position').val(newValue);
                    } 

                    if(valuelist_name == 'employeeCategory'){
                        $('#fld_category').cp_loadSelect(data);
                        $('#fld_category').val(newValue);
                    } 
                }, 'json');
            }
        }

        Util.openFormInDialog.call(this, 'portalForm', title, 400, 300, expObj);
    });



    $('.TimeinButton').livequery('click', function (e){
        var employee_id = $(this).attr('employee_id');
        var parent = $(this).closest('tr');
        //alert(employee_id);
        msg = "Would You Update Time In?";
        if (confirm (msg)){
            Util.showProgressInd();
            var url = 'index.php?module=hms_employee&_spAction=employeeTimeInUpdate';

            $.get(url, {employee_id: employee_id}, function(){
              $('.TimeinButton', parent).hide();
              Util.hideProgressInd();
              alert('Time In Updated!');
            });
        }

    });


    $('.TimeoutButton').livequery('click', function (e){
        var employee_id = $(this).attr('employee_id');
        var parent = $(this).closest('tr');
        msg = "Would You Update Time Out?";
        if (confirm (msg)){
            Util.showProgressInd();
            var url = 'index.php?module=hms_employee&_spAction=employeeTimeOutUpdate';

            $.get(url, {employee_id: employee_id}, function(){
              $('.TimeoutButton', parent).hide();
              Util.hideProgressInd();
              alert('Time Out Updated!');
            });
        }
    });

    /* Add Employee Performance*/
    $('#AddEmployeePerformance').live('click', function (e){
        var title = "Add Employee Performance";
        var employee_id = $(this).attr('employee_id');
        e.preventDefault();
        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                var msg = 'Performance Added Successfully';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    //cpm.hms.employee.reloadEmployeePerformanceLink(employee_id);
                    window.location.reload(true);
                });
            }
        }

        Util.openFormInDialog.call(this, 'AddEmployeePerformanceForm', title, 550, 382, expObj);
    });

    /* Edit Employee Performance*/
    $('.EditEmployeePerformance').live('click', function (e){
        var title = "Edit Employee Performance";
        var employee_id = $(this).attr('employee_id');
        e.preventDefault();
        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                var msg = 'Performance Update Successfully';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    window.location.reload(true);
                });
            }
        }

        Util.openFormInDialog.call(this, 'EditEmployeePerformanceForm', title, 550, 382, expObj);
    });

    /* Delete Employee Performance */
    $('.deleteEmployeePerformance').live('click', function (e){
        var emp_performance_id = $(this).attr('emp_performance_id');
        var employee_id        = $(this).attr('employee_id');

        var msg = "Are you sure to delete?";
        if (confirm(msg)){
            Util.showProgressInd();
            var url = 'index.php?module=hms_employee&_spAction=deleteEmployeePerformance'
                    + '&showHTML=0';
            $.get(url, {emp_performance_id:emp_performance_id, employee_id:employee_id} ,function(html){
                Util.hideProgressInd();
                alert('Deleted Successfully!');
                window.location.reload(true);
            });
        }
    });

    $("select[name=employee_type]").live('change', function (){
        var employee_type = $(this).val();

        if(employee_type == "Double Shift") {
            $('.doubleShiftTimings').removeClass('displayNone');
            $('.normalTimings').addClass('displayNone');
        } else {
            $('.normalTimings').removeClass('displayNone');
            $('.doubleShiftTimings').addClass('displayNone');
        }
    });
}

cpm.hms.employee.reloadEmployeePerformanceLink = function(employee_id){
    var url = 'index.php?module=hms_employee&_spAction=EmployeePerformance&showHTML=0';
    $.get(url, {employee_id: employee_id}, function(html){
        $('#employeePerformanceLinkPortal').html(html);
    });
}