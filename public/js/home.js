$(document).ready(function(){
    loading.img = 'imgs/loader.gif';

    version.load();

    entryModal.init();
    filterModal.init();

    tags.load();
    institutions.load();
    accounts.load();
    accountTypes.load();
    entries.load();
    paginate.init();
});

function responseToData(response, responseType){
    var responseCount = parseInt(response.count);
    delete response.count;

    response = $.map(response, function(el) { return el });
    if(typeof responseType !== 'undefined'){
        if(responseType !== 'entries' && responseCount !== response.length) {
            // entries come in batches
            notice.display(notice.typeWarning, "Not all " + responseType + " were downloaded");
        }
    }
    return response;
}

var tags = {
    uri: "/api/tags",
    value: [],
    find: function(id){
        id = parseInt(id);
        var foundTags = $.grep(tags.value, function(tag){ return tag.id === id });
        if(foundTags.length > 0){
            return foundTags[0];
        } else {
            return {};  // could not find a tag associated with the provided ID
        }
    },
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
            return element.name;
        });
    },
    getNameById: function(id){
        var tag = tags.find(id);
        return (tag.hasOwnProperty('name')) ? tag.name : '';
    },
    getIdByName: function(tagName){
        var tagObjects = $.grep(tags.value, function(element){
            return element.name === tagName;
        });
        if(tagObjects.length > 0){
            return tagObjects[0].id;
        } else {
            return -1;
        }
    }
};

var version = {
    uri: "/api/version",
    value: "",
    load: function(){
        $.ajax({
            url: version.uri,
            statusCode: {
                200: function(response){
                    version.value = response;
                },
                404: function(){
                    version.value = "N/A";
                },
                500: function(){
                    version.value = "N/A";
                }
            },
            complete: function(){
                version.display();
            }
        });
    },
    display: function(){
        $('#app-version').text(version.value);
    }
};

var institutions = {
    uri: "/api/institutions",
    value: [],
    load: function(){
        $.ajax({
            url: institutions.uri,
            dataType: "json",
            beforeSend: loading.start,
            statusCode: {
                200: function(response){
                    institutions.value = [];
                    institutions.value = responseToData(response, 'institutions');
                },
                404: function(){
                    institutions.value = [];
                    notice.display(notice.typeInfo, "No institutions currently available");
                },
                500: function(){
                    notice.display(notice.typeDanger, "Error occurred when attempting to retrieve institutions");
                }
            },
            complete: function(){
                institutions.display();
            }
        });
    },
    display: function(){
        institutionsPane.clear();
        institutionsPane.displayInstitutions();
    }
};

var accounts = {
    uri: "/api/accounts",
    value: [],
    find: function(id){
        id = parseInt(id);
        var foundAccounts = $.grep(accounts.value, function(account){ return account.id === id });
        if(foundAccounts.length > 0){
            return foundAccounts[0];
        } else {
            return {};  // couldn't find the account associated with the provided ID
        }
    },
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
        institutionsPane.displayAccounts();
        filterModal.initAccountSelect();
    },
    valuesSortedBy: function(property){
        return accounts.value.slice().sort(function(a, b){
            if(a.hasOwnProperty(property) && b.hasOwnProperty(property)){
                if(a[property] < b[property]) return -1;
                if(a[property] > b[property]) return 1;
                return 0;
            } else {
                return 0;
            }
        });
    }
};

var accountTypes = {
    uri: "/api/account-types",
    value: [],
    find: function(id){
        id = parseInt(id);
        var foundAccountTypes = $.grep(accountTypes.value, function(accountType){ return accountType.id === id });
        if(foundAccountTypes.length > 0){
            return foundAccountTypes[0];
        } else {
            return {};
        }
    },
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
        institutionsPane.displayAccountTypes();
    },
    getNameById: function (accountTypeId) {
        var accountType = accountTypes.find(accountTypeId);
        if(accountType.hasOwnProperty('name')){
            return accountType.name;
        } else {
            return '';
        }
    },
    getAccount: function(accountTypeId){
        var accountType = accountTypes.find(accountTypeId);
        if(accountType.hasOwnProperty('account_id')){
            return accounts.find(accountType.account_id);
        } else {
            return {};  // couldn't find the account_type associated with the provided ID
        }
    },
    valuesSortedBy: function(property){
        return accountTypes.value.slice().sort(function(a, b){
            if(a.hasOwnProperty(property) && b.hasOwnProperty(property)){
                if(a[property] < b[property]) return -1;
                if(a[property] > b[property]) return 1;
                return 0;
            } else {
                return 0;
            }
        });
    }
};

var entries = {
    uri: "/api/entries",
    value: [],
    total: 0,
    sort: {
        parameter: 'entry_date',
        direction: 'desc'
    },
    load: function(pageNumber){
        entries.ajaxRequest(pageNumber, {});
    },
    filter: function(filterParameters, pageNumber){
        entries.ajaxRequest(pageNumber, filterParameters);
    },
    display: function(){
        entries.clearDisplay();
        var isEntryInFuture = function(entryDate){
            var millisecondsPerMinute = 60000;
            var timezoneOffset = new Date().getTimezoneOffset()*millisecondsPerMinute;
            return Date.parse(entryDate)+timezoneOffset > Date.now();
        };
        $.each(entries.value, function(index, entryObject){
            entryObject.isEntryInFuture = isEntryInFuture(entryObject.entry_date);
            var displayTags = '';
            $.each(entryObject.tags, function(id, tagId){
                displayTags += '<span class="label label-default entry-tag">'+tags.getNameById(tagId)+'</span>';
            });
            $('#entries-display-pane tbody').append(
                '<tr class="'+(!entryObject.confirm ? 'warning' : (entryObject.expense ? '' : 'success'))+(entryObject.isEntryInFuture ? ' text-muted':'')+'">' +
                '<td class="check-col" data-toggle="modal" data-target="#entry-modal" onclick="entry.load('+entryObject.id+');">' +
                "\t"+'<span class="glyphicon glyphicon-pencil"></span>' +
                '</td>' +
                '<td>'+entryObject.entry_date+'</td>' +
                '<td>'+entryObject.memo+'</td>' +
                '<td class="value-col">'+(entryObject.expense ? '' : '$'+entryObject.entry_value)+'</td>' +
                '<td class="value-col">'+(entryObject.expense ? '$'+entryObject.entry_value : '')+'</td>' +
                '<td>'+accountTypes.getNameById(entryObject.account_type_id)+'</td>' +
                '<td><span class="glyphicon glyphicon-'+(entryObject.has_attachments ? 'check' : 'unchecked')+'" aria-hidden="true"></span></td>' +
                '<td>'+displayTags+'</td>' +
                '</tr>'

            );
        });
    },
    clearDisplay: function(){
        $("#entries-display-pane tbody tr").remove();
    },
    ajaxStatusCodeProcessing: {
        200: function(responseData){
            entries.total = responseData.count;
            entries.value = responseToData(responseData, 'entries');
        },
        404: function(){
            entries.value = [];
            notice.display(notice.typeInfo, "No entries were found");
        },
        500: function(){
            entries.value = [];
            notice.display(notice.typeDanger, "An error occurred while attempting to retrieve "+(filterModal.active?"filtered":"")+" entries");
        }
    },
    ajaxRequest: function(pageNumber, filterParameters){
        pageNumber = paginate.processPageNumber(pageNumber);

        $.each(filterParameters, function(parameter, value){
            if(value === null){
                delete filterParameters[parameter];
            }
        });
        var requestParameters = $.extend({}, filterParameters);
        requestParameters.sort = entries.sort;


        $.ajax({
            url: entries.uri+'/'+pageNumber,
            type: 'POST',
            beforeSend: function(){
                loading.start();
                filterModal.active = !$.isEmptyObject(filterParameters);
                paginate.filterState = filterParameters;
            },
            data: JSON.stringify(requestParameters),
            dataType: 'json',
            statusCode: entries.ajaxStatusCodeProcessing,
            complete: entries.ajaxCompleteProcessing
        });
    },
    ajaxCompleteProcessing: function(){
        $('.is-filtered').toggle(filterModal.active);
        entries.display();
        paginate.display.previous(paginate.current !== 0);
        paginate.display.next(paginate.current < Math.ceil(entries.total/50)-1);
        loading.end();
    },
    reload: function(pageNumber, filterParameters){
        pageNumber = paginate.processPageNumber(pageNumber);
        if(filterParameters){
            entries.filter(filterParameters, pageNumber);
        } else {
            entries.load(pageNumber);
        }
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
        var ajaxEntryData = JSON.stringify(entryData);
        if($.isNumeric(entryId)){
            // update entry
            $.ajax({
                url: entry.uri+'/'+entryId,
                method: 'PUT',
                beforeSend: loading.start,
                data: ajaxEntryData,
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
                complete: entry.completeEntryUpdate
            });
        } else {
            // new entry
            $.ajax({
                url: entry.uri,
                method: 'POST',
                beforeSend: loading.start,
                data: ajaxEntryData,
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
                complete: entry.completeEntryUpdate
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
            complete: entry.completeEntryUpdate
        });
    },
    completeEntryUpdate: function(){
        // re-display entries
        entries.reload(paginate.current, paginate.filterState);
        // re-display institutes-pane contents
        institutionsPane.clear();
        institutionsPane.displayInstitutions();
        accounts.load();
        institutionsPane.displayAccountTypes();

        var intervalSetAccountActive = setInterval(function(){
            // need to wait for the accounts to be re-displayed
            if(institutionsPane.accountsDisplayed){
                var accountId = ($.isEmptyObject(paginate.filterState)) ? '' : paginate.filterState.account;
                institutionsPane.setActiveState(accountId);
                clearInterval(intervalSetAccountActive);    // stops interval from running again
            }
        }, 50);
    }
};

var attachment = {
    uri: '/api/attachment/',
    open: function(uuid){
        var url = '/attachment/'+uuid;
        var win=window.open(url, '_blank');
        win.focus();
    },
    remove: function(attachmentUuid, attachmentName){
        if(confirm('Are you sure you want to delete attachment: '+attachmentName)){
            $.ajax({
                type: 'DELETE',
                url: attachment.uri+attachmentUuid,
                beforeSend:function(){
                    notice.remove();
                },
                success:function(data){
                    $.each(entry.value.attachments, function(idx, entryAttachment){
                        if(entryAttachment.uuid === attachmentUuid){
                            delete entry.value.attachments[idx];
                        }
                    });
                    $('#attachment_'+attachmentUuid).remove();
                    notice.display(notice.typeInfo, "Attachment has been deleted");
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