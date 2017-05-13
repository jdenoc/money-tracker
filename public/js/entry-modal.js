$('#entry-confirm').bootstrapSwitch({
    size: 'mini',
    labelText: "Confirmed",
    onColor: "success",
    onText: "Yes",
    offText: "No"
});

$("input[name='expense-switch']").bootstrapSwitch({
    size: "large",
    onColor: "warning",
    offColor: "info",
    onText: "Expense",
    offText: "Income",
    handleWidth: 123
});

$('#entry-tags-info').tooltip({
    placement: 'right',
    trigger: 'click',
    title: function(){
        return '['+tags.getAllNames().join("]\n[")+']';
    }
});

$('#attachment-uploader').uploadFile({
    url:"/attachment/upload",
    returnType: 'json',
    formData: {_token: uploadToken},
    multiple:true,
    dragDrop:true,
    showProgress: true,
    showDelete: true,
    onSuccess: function(files, data, xhr){
        $.each(files, function(idx, filename){
            notice.display(notice.typeInfo, "uploaded: "+filename);
        });
    },
    onError: function(files, status, errorMsg){
        $.each(files, function(idx, filename){
            notice.display(notice.typeWarning, "file upload failure: "+errorMsg);
        });
    },
    deleteCallback: function(attachment){
        $.ajax({
            url: '/attachment/upload',
            method: 'delete',
            data: {
                _token: uploadToken,
                filename: attachment.tmp_filename
            },
            dataType: "json",
            statusCode: {
                204: function(){
                    notice.display(notice.typeInfo, "Attachment deleted");
                },
                400: function(){
                    notice.display(notice.typeWarning, "Error occurred while attempting to delete attachment");
                    return false;
                },
                404: function(){
                    notice.display(notice.typeInfo, "Attachment not found");
                },
                500: function(){
                    notice.display(notice.typeWarning, "Error occurred while attempting to delete attachment");
                }
            }
        });
    },
    showFileSize: true,
    showFileCounter: false,
    dragdropWidth: 350,
    fileName: 'attachment'
});

var entryModal = {
    init: function(){
        $('#entry-modal').on('hidden.bs.modal', function (e) {
            entryModal.clearFields();
        });

        $('#entry-unlock').click(entryModal.unlockFields);
        $('#entry-lock').click(entryModal.fillFields);      // reset all fields and then "locks" them // FIXME: every time this is run, the attachments are duplicated
        $('#entry-save').click(entryModal.submit);
        $('#entry-delete').click(entryModal.delete);
        entryModal.initEntryDate();
    },
    initEntryDate: function(){
        var today = new Date();
        $("#entry-date").val(
            today.getFullYear()+'-'
            +(today.getMonth()<9?'0':'')+(today.getMonth()+1)+'-'	// months in JavaScript start from 0=January
            +(today.getDate()<10?'0':'')+today.getDate()
        );
    },
    initAccountTypeSelect: function(){
        $.each(accountTypes.value, function(idx, accountTypeObject){
            if(!accountTypeObject.disabled){
                $("#entry-account-type").append('<option value="'+accountTypeObject.id+'">'+accountTypeObject.type_name+'</option>');
            }
        });
    },
    initTagsInput: function(){
        $('#entry-tags').tagsinput({
            itemValue: 'id',
            itemText: 'tag',
            typeahead: {
                source: tags.value,
                afterSelect: function(val){
                    // this clears the input field after a "tag" has been "selected"
                    this.$element.val("");
                }
            },
            tagClass: 'label label-tag',
            freeInput: false
        });
    },
    fillFields: function(){
        $("#entry-confirm")
            .prop('checked', entry.value.confirm)
            .bootstrapSwitch('state', entry.value.confirm);
        $("#entry-id").val(entry.value.id);
        $("#entry-id-display").html(entry.value.id);
        $("#entry-date").val(entry.value.entry_date);
        $("#entry-value").val(entry.value.entry_value);
        $("#entry-memo").val(entry.value.memo);
        $("#entry-account-type").val(entry.value.account_type);
        $("input[name='expense-switch']")
            .prop('checked', entry.value.expense)
            .bootstrapSwitch('state', entry.value.expense);

        $.each(entry.value.tags, function(idx, tagObject){
            $('#entry-tags').tagsinput('add', tagObject);
        });

        $.each(entry.value.attachments, function(idx, attachmentObject){
            $('.ajax-file-upload-container').append(
                '<div class="ajax-file-upload-statusbar">' +
                '<div class="ajax-file-upload-filename">'+attachmentObject.attachment+'</div>' +
                // TODO: delete attachment button
                '<button type="button" class="btn btn-danger glyphicon glyphicon-trash pull-right" onclick="attachment.remove(\''+attachmentObject.uuid+'\');"></button>' +
                // TODO: open attachment button
                '<button type="button" class="btn btn-default glyphicon glyphicon-search pull-right" onclick="attachment.open(\''+attachmentObject.uuid+'\');"></button>' +
                '<input type="hidden" name="entry-attachments[]" value="'+JSON.stringify(attachmentObject)+'" />'+
                '</div>'
            );
        });

        if(entry.value.confirm){
            entryModal.lockFields();
        }
    },
    clearFields: function(){
        entryModal.unlockFields();
        $('#entry-lock').toggle(false); // do this because unlockFields() displays the element

        $("#entry-confirm").prop('checked', false)
            .bootstrapSwitch('state', false);
        $("#entry-id").val('');
        $("#entry-id-display").html('new');
        entryModal.initEntryDate();
        $("#entry-value").val('');
        $("#entry-memo").val('');
        $("#entry-account-type").val('');
        $("input[name='expense-switch']").prop('checked', true)
            .bootstrapSwitch('state', true);
        $('#entry-tags').tagsinput('removeAll');

        $('.ajax-file-upload-statusbar').remove(); // clear attachments from entry-modal
    },
    lockFields: function(){
        entryModal.setLockStatus(true);
    },
    unlockFields: function(){
        entryModal.setLockStatus(false);
    },
    setLockStatus: function(lockStatus){
        $("#entry-confirm").bootstrapSwitch('readonly', lockStatus);
        $("#entry-date").prop('readonly', lockStatus);
        $("#entry-value").prop('readonly', lockStatus);
        $("#entry-account-type").prop('disabled', lockStatus);  // select elements do not have a readonly attribute
        $("#entry-memo").prop('readonly', lockStatus);
        $('input[name="expense-switch"]').bootstrapSwitch('readonly', lockStatus);

        $('#entry-tags').prop('readonly', lockStatus);
        $('.bootstrap-tagsinput input').prop('readonly', lockStatus);
        $('.bootstrap-tagsinput span[data-role="remove"]').toggle(!lockStatus);

        $('#entry-lock').toggle(!lockStatus);
        $('#entry-unlock').toggle(lockStatus);
    },
    submit: function(){
        // TODO: submit entry data to API
    }
};