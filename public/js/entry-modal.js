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
        notice.display(notice.typeInfo, "uploaded: "+files[0]);
        var newAttachmentsInput = $('#entry-new-attachments');
        var recentlyAddedAttachments = JSON.parse(newAttachmentsInput.val());
        recentlyAddedAttachments.push(data);
        newAttachmentsInput.val(JSON.stringify(recentlyAddedAttachments));
    },
    onError: function(files, status, errorMsg){
        notice.display(notice.typeWarning, "file upload failure: "+errorMsg);
    },
    deleteCallback: function(attachment){
        $.ajax({
            url: '/attachment/upload',
            method: 'delete',
            data: {
                _token: uploadToken,
                uuid: attachment.uuid,
                filename: attachment.name
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
                401: function(){
                    notice.display(notice.typeError, "Failed to delete attachment due to expired session. Refresh page.")
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
        $('#entry-lock').click(function(){
            // reset all fields and then "locks" them
            entryModal.clearAttachmentViews();  // clear attachment fields to prevent duplication when we call "fillFields()"
            entryModal.fillFields();
        });
        $('#entry-save').click(entryModal.submit);
        $('#entry-delete').click(entryModal.delete);
        entryModal.initEntryDate();
        $('#entry-value').change(function(){
            var value = $(this).val().replace(/[^0-9.]/g, '');
            $(this).val( parseFloat(value).toFixed(2) );
        });
        $('#entry-account-type').change(function(){
            var accountTypeId = $(this).val();
            var accountType = accountTypes.find(accountTypeId);
            var account = accountTypes.getAccount(accountTypeId);
            var accountTypeMetaParentElement = $('.account-type-meta');
            if(account.hasOwnProperty('name')){
                accountTypeMetaParentElement.removeClass('hidden');
                $('#entry-account-name span').text(account.name);
                $('#entry-account-type-last-digits span').text(accountType.last_digits);
            } else {
                accountTypeMetaParentElement.addClass('hidden');
                $('#entry-account-name span').text('');
                $('#entry-account-type-last-digits span').text('');
            }

            if(account.hasOwnProperty('disabled') && account.disabled){
                accountTypeMetaParentElement.removeClass('text-info').addClass('text-muted');
            } else {
                accountTypeMetaParentElement.removeClass('text-muted').addClass('text-info');
            }
        });
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
                $("#entry-account-type").append('<option value="'+accountTypeObject.id+'">'+accountTypeObject.name+'</option>');
            }
        });
    },
    initTagsInput: function(){
        $('#entry-tags').tagsinput({
            itemValue: 'id',
            itemText: 'name',
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
        $("#entry-account-type").val(entry.value.account_type_id)
            .trigger('change'); // show/update account name in modal
        $("input[name='expense-switch']")
            .prop('checked', entry.value.expense)
            .bootstrapSwitch('state', entry.value.expense);

        $.each(entry.value.tags, function(idx, tagObject){
            $('#entry-tags').tagsinput('add', tagObject);
        });

        $.each(entry.value.attachments, function(idx, attachmentObject){
            $('.ajax-file-upload-container').append(
                '<div id="attachment_'+attachmentObject.uuid+'" class="ajax-file-upload-statusbar">' +
                '<div class="ajax-file-upload-filename">'+attachmentObject.name+'</div>' +
                '<button type="button" class="btn btn-danger glyphicon glyphicon-trash pull-right" onclick="attachment.remove(\''+attachmentObject.uuid+'\', \''+attachmentObject.name+'\');"></button>' +
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
        $("#entry-account-type").val('')
            .trigger('change'); // hide/clear account name from display
        $("input[name='expense-switch']").prop('checked', true)
            .bootstrapSwitch('state', true);
        // clear tags input
        var entryTagsInput = $('#entry-tags');
        entryTagsInput.tagsinput('removeAll');
        entryTagsInput.parents('label').children('.bootstrap-tagsinput').children('input').val('');

        entryModal.clearAttachmentViews();
        $('#entry-new-attachments').val('[]');
    },
    clearAttachmentViews: function(){
        // clear attachment view elements from the entry modal
        $('.ajax-file-upload-statusbar').remove();
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
        var entryTags = $('#entry-tags').val();
        var entrySaveData = {
            id: $('#entry-id').val(),
            entry_date: $('#entry-date').val(),
            entry_value: $('#entry-value').val(),
            account_type_id: parseInt($('#entry-account-type').val()),
            memo: $('#entry-memo').val(),
            expense: $('input[name="expense-switch"]').prop('checked'),
            confirm: $('#entry-confirm').prop('checked'),
            tags: (entryTags === '') ? [] : $.map(entryTags.split(','), Number),
            attachments: JSON.parse($('#entry-new-attachments').val()),
        };
        entry.save(entrySaveData);
    },
    delete: function(){
        entry.delete( parseInt($('#entry-id').val()) );
    }
};