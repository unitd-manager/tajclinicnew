$(function(){

});

/* Filtering task history according to status */
$('#wd_hms_supplierOutstanding select[name=due_period]').livequery('change', function(){
    var due_period      = $(this).val();

	var url = 'index.php?widget=hms_supplierOutstanding&_spAction=supplierOutstandingByDue';
    Util.showProgressInd();
    $.get(url,{due_period: due_period}, function(html){
        $('#wd_hms_supplierOutstanding table').html(html);
        Util.hideProgressInd();
    });
});
