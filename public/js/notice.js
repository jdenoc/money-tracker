toastr.options.closeButton = true;
toastr.options.progressBar = true;
toastr.options.positionClass = 'toast-bottom-right';

var notice = {
    typeInfo: 'info',
    typeWarning: 'warning',
    typeSuccess: 'success',
    typeDanger: 'danger',
    typeError: 'error',
    display: function(alertType, alertText){
        switch(alertType){
            case notice.typeWarning:
                toastr.warning(alertText);
                break;
            case notice.typeDanger:
            case notice.typeError:
                toastr.error(alertText);
                break;
            case notice.typeSuccess:
                toastr.success(alertText);
                break;
            case notice.typeInfo:
            default:
                toastr.info(alertText);
                break;
        }
    },
    remove: function(){
        toastr.clear();
    }
};