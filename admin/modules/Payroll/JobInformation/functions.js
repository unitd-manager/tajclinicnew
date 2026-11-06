Util.createCPObject('cpm.payroll.jobInformation');

cpm.payroll.jobInformation = {
    init: function(){
        $("input[name='employee_name']")
        .livequery(cpm.payroll.jobInformation.employeeName);
    },
    //Auto select patient details
    employeeName: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?module=payroll_jobInformation&_spAction=searchEmployeeDetails&showHTML=0'
            ,minLength : 3
            ,selectFirst: true
            ,autoFocus: true
            ,select: function(event, ui) {
                var selectedObj = ui.item;
                var employee_id = selectedObj.id
                $('input[name=employee_id]').val(employee_id);
            }
        });
    },

    duplicate: function() {
        if (!confirm("Are you sure you want to duplicate the jobInformation?")){
            return;
        }

        var employee_id = $('#record_id').val();
        var url = 'index.php?module=tradingsg_quote&_spAction=duplicateQuote&showHTML=0' +
                  '&employee_id=' + employee_id;

        $.post(url, function (json) {
            if (json.status == 'error') {
                Util.alert(json.errorMsg);
                return;
            }
            document.location = json.returnUrl;
        }, 'json');

    },
}