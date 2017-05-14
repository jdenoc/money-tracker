$(document).ready(function(){
    loading.img = 'imgs/loader.gif';

    entryModal.init();
    filterModal.init();

    tags.load();
    accounts.load();
    accountTypes.load();
    entries.load();
});

function responseToData(response, responseType){
    var responseData = [];
    var responseCount = 0;

    $.each(response, function(key, object){
        if(key !== 'count'){
            responseData.push(object);
        } else {
            responseCount = object;
        }
    });

    if(typeof responseType !== 'undefined' && responseCount !== responseData.length){
        notice.display(notice.typeWarning, "Not all "+responseType+" were downloaded");
    }
    return responseData;
}

var tags = {
    uri: "/api/tags",
    value: [],
    load: function(){
        $.ajax({
            url: tags.uri,
            dataType: "json",
            beforeSend: loading.start,
            statusCode: {
                200: function(response){
                    tags.value = [];
                    tags.value = responseToData(response, 'tags');
                },
                404: function(){
                    tags.value = [];
                },
                500: function(){
                    notice.display(notice.typeDanger, "Error occurred when attempting to retrieve tags");
                }
            },
            complete: function () {
                tags.display();
            }
        });
    },
    display: function () {
        entryModal.initTagsInput();
        filterModal.initTagsInput();
    },
    getAllNames: function(){
        return $.map(tags.value, function(element, index){
            return element.tag;
        });
    },
    getNamesById: function(tagIds){
        var tagNames = [];
        for(i=0; i<tags.value.length; i++){
            if($.inArray(tags.value[i]['id'], tagIds) !== -1){
                tagNames.push(tags.value[i]['tag']);
            }
        }
        return tagNames;
    },
    getIdByName: function(tagName){
        var tagObjects = $.grep(tags.value, function(element){
            return element.tag === tagName;
        });
        if(tagObjects.length > 0){
            return tagObjects[0].id;
        } else {
            return -1;
        }
    }
};

var accounts = {
    uri: "/api/accounts",
    value: [],
    load: function(){
        $.ajax({
            url: accounts.uri,
            dataType: "json",
            beforeSend: loading.start,
            statusCode: {
                200: function(response){
                    accounts.value = [];
                    accounts.value = responseToData(response, 'accounts');
                },
                404: function(){
                    accounts.value = [];
                    notice.display(notice.typeInfo, "No accounts currently available");
                },
                500: function(){
                    notice.display(notice.typeDanger, "Error occurred when attempting to retrieve accounts");
                }
            },
            complete: function(){
                accounts.display();
            }
        });
    },
    display: function(){
        accountsPane.clear();
        accountsPane.displayAccounts();
    }
};

var accountTypes = {
    uri: "/api/account-types",
    value: [],
    load: function(){
        $.ajax({
            url: accountTypes.uri,
            dataType: "json",
            beforeSend: loading.start,
            statusCode: {
                200: function(response){
                    accountTypes.value = responseToData(response, 'account types');
                },
                404: function(){
                    accountTypes.value = [];
                    notice.display(notice.typeInfo, "No account types available");
                },
                500: function(){
                    accountTypes.value = [];
                    notice.display(notice.typeDanger, 'Error occurred while attempting to retrieve account types');
                }
            },
            complete: function(){
                accountTypes.display();
            }
        });
    },
    display: function(){
        entryModal.initAccountTypeSelect();
        filterModal.initAccountTypeSelect();
        accountsPane.displayAccountType();
    },
    getNameById: function (accountTypeId) {
        var accountTypeObjects = $.grep(accountTypes.value, function(element){
            return element.id === accountTypeId;
        });

        if(accountTypeObjects.length > 0){
            return accountTypeObjects[0].type_name;
        } else {
            return '';
        }
    }
};

var entries = {
    uri: "/api/entries",
    value: [],
    load: function(){
        $.ajax({
            url: entries.uri,
            dataType: "json",
            beforeSend: loading.start,
            statusCode: {
                200: function(responseData){
                    entries.value = responseToData(responseData, 'entries');
                },
                404: function(){
                    entries.value = [];
                    notice.display(notice.typeInfo, "No entries available");
                },
                500: function(){
                    entries.value = [];
                    notice.display(notice.typeDanger, 'Error occurred while attempting to retrieve entries');
                }
            },
            complete: function(){
                filterModal.active = false;
                entries.display();
                loading.end();
            }
        });
    },
    filter: function(filterParameters){
        $.ajax({
            url: entries.uri,
            type: 'POST',
            beforeSend: loading.start,
            data: JSON.stringify(filterParameters),
            dataType: 'json',
            statusCode: {
                200: function(responseData){
                    entries.value = responseToData(responseData, 'entries');
                },
                404: function(){
                    entries.value = [];
                    notice.display(notice.typeInfo, "No entries were found");
                },
                500: function(){
                    entries.value = [];
                    notice.display(notice.typeDanger, "An error occurred while attempting to retrieve filtered entries");
                }
            },
            complete: function(){
                filterModal.active = true;
                entries.display();
                loading.end();
            }
        });
    },
    display: function(){
        entries.clearDisplay();
        $.each(entries.value, function(index, entryObject){
            var displayTags = '';
            $.each(tags.getNamesById(entryObject.tags), function(id, tagName){
                displayTags += '<span class="label label-default entry-tag">'+tagName+'</span>';
            });
            $('#entries-display-pane tbody').append(
                '<tr class="'+(!entryObject.confirm ? 'warning' : (entryObject.expense ? '' : 'success'))+'">' +
                '<td class="check-col" data-toggle="modal" data-target="#entry-modal" onclick="entry.load('+entryObject.id+');">' +
                "\t"+'<span class="glyphicon glyphicon-pencil"></span>' +
                '</td>' +
                '<td>'+entryObject.entry_date+'</td>' +
                '<td>'+entryObject.memo+'</td>' +
                '<td class="value-col">'+(entryObject.expense ? '' : '$'+entryObject.entry_value)+'</td>' +
                '<td class="value-col">'+(entryObject.expense ? '$'+entryObject.entry_value : '')+'</td>' +
                '<td>'+accountTypes.getNameById(entryObject.account_type)+'</td>' +
                '<td><span class="glyphicon glyphicon-'+(entryObject.has_attachments ? 'check' : 'unchecked')+'" aria-hidden="true"></span></td>' +
                '<td>'+displayTags+'</td>' +
                '</tr>'

            );
        });
    },
    clearDisplay: function(){
        $("#entries-display-pane tbody tr").remove();
    }
};

var entry = {
    uri: "/api/entry",
    value: [],
    load: function(entryId){
        $.ajax({
            url: entry.uri+'/'+entryId,
            dataType: "json",
            beforeSend: loading.start,
            statusCode: {
                200: function(response){
                    entry.value = response;
                },
                404: function(){
                    entry.value = [];
                    notice.display(notice.typeWarning, "Entry does not exist");
                },
                500: function(){
                    entry.value = [];
                    notice.display(notice.typeDanger, 'Error occurred while attempting to retrieve entries');
                }
            },
            complete: function(){
                entryModal.fillFields();
                loading.end();
            }
        });
    },
    save: function(entryData){
        var entryId = parseInt(entryData.id);
        delete entryData.id;
        if($.isNumeric(entryId)){
            // update entry
            $.ajax({
                url: entry.uri+'/'+entryId,
                method: 'PUT',
                beforeSend: loading.start,
                data: JSON.stringify(entryData),
                dataType: 'json',
                statusCode: {
                    200: function(){
                        notice.display(notice.typeSuccess, "Entry updated");
                    },
                    400: function(responseObject){
                        notice.display(notice.typeWarning, responseObject.responseJSON.error);
                    },
                    404: function(responseObject){
                        notice.display(notice.typeWarning, responseObject.responseJSON.error);
                    },
                    500: function(){
                        notice.display(notice.typeError, "An error occurred while attempting to update entry ["+entryId+"]");
                    }
                },
                complete: completeEntryUpdate
            });
        } else {
            // new entry
            $.ajax({
                url: entry.uri,
                method: 'POST',
                beforeSend: loading.start,
                data: JSON.stringify(entryData),
                dataType: 'json',
                statusCode: {
                    201: function(){
                        notice.display(notice.typeSuccess, "New entry created");
                    },
                    400: function(responseObject){
                        notice.display(notice.typeWarning, responseObject.responseJSON.error);
                    },
                    500: function(){
                        notice.display(notice.typeError, "An error occurred while attempting to create an entry");
                    }
                },
                complete: completeEntryUpdate
            });
        }
    },
    delete: function(entryId){
        $.ajax({
            url: entry.uri+'/'+entryId,
            method: 'delete',
            beforeSend: loading.start,
            dataType: 'json',
            statusCode: {
                204: function(){
                    notice.display(notice.typeSuccess, "Entry was deleted");
                },
                404: function(){
                    notice.display(notice.typeWarning, "Entry ["+entryId+"] does not exist and cannot be deleted");
                },
                500: function(){
                    notice.display(notice.typeError, "An error occurred while attempting to delete entry ["+entryId+"]");
                }
            },
            complete: completeEntryUpdate
        });
    }
};

var attachment = {
    open: function(uuid){
        var url = '/attachment/'+uuid;
        var win=window.open(url, '_blank');
        win.focus();
    },
    remove: function(uuid){
        var entryId = $('#entry_id').val();
        if(confirm('Are you sure you want to delete attachment: '+attachmentName)){
            $.ajax({
                type: 'DELETE',
                url: url+nocache(),
                data: {
                    type: 'delete_attachment',
                    entry_id : entryId,
                    id: attachmentId
                },
                beforeSend:function(){
                    notice.remove();
                },
                success:function(data){
                    $('#attachment_'+attachmentId).remove();
                    $('#entry_has_attachment').val( parseInt(data) );
                },
                error:function(){
                    notice.display(notice.typeDanger, 'Could not delete attachment');
                }
            });
        }
    },
    removeUpload: function(filename, temp){
        // TODO: delete recently uploaded file
    }
};

function completeEntryUpdate(){
    if(filterModal.active){
        filterModal.submit();
    } else {
        entries.load();
    }
    accountsPane.displayed = false;
    accounts.load();
    accountsPane.displayAccountType();
}