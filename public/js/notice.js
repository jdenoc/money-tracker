var notice = {
    typeInfo: 'info',
    typeWarning: 'warning',
    typeSuccess: 'success',
    typeDanger: 'danger',
    display: function(alertType, alertText){
        var alertToDisplay = '';
        if($.inArray(alertType, [notice.typeInfo, notice.typeDanger, notice.typeSuccess, notice.typeWarning]) > -1){
            alertToDisplay += '<div class="alert alert-'+alertType+' alert-dismissable" role="alert">';
            alertToDisplay += '     <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>';
            alertToDisplay += '     '+alertText;
            alertToDisplay += '</div>';
        }
        $('.row').before(alertToDisplay);
    },
    remove: function(){
        $('.alert').remove();
    }
};