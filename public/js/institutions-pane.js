var institutionsPane = {
    institutionsDisplayed: false,
    accountsDisplayed: false,
    closedAccountsDisplayed: false,
    displayInstitutions: function(){
        // display institutions in institutions pane
        $.each(institutions.value, function(index, institutionObj){
            if(institutionObj.active){
                $('#institution-display-pane').append(
                    '<li id="institution-id-'+institutionObj.id+'" class="institutions-pane-institution panel panel-default">' +
                    '<a data-toggle="collapse" data-target="#accounts-for-institution'+institutionObj.id+'" data-parent="#institution-display-pane" class="panel-heading"><span class="panel-title">'+institutionObj.name+'</span></a>' +
                    '<div id="accounts-for-institution'+institutionObj.id+'" class="collapse panel-body institutions-pane-collapse"></div>' +
                    '</li>'
                );
            }
        });
        institutionsPane.institutionsDisplayed = true;
    },
    displayAccounts: function(){
        // display accounts under institutions in an accordion fashion
        if(institutionsPane.institutionsDisplayed) {
            $.each(accounts.value, function(index, accountObject){
                var accountDiv = '<div id="account-id-'+accountObject.id+'" class="institutions-pane-account">'
                    + '<a data-toggle="tooltip" title="">'
                    + accountObject.name
                    + '</a></div>';
                if(accountObject.disabled){
                    // add account to closed accounts section
                    $("#closed-accounts").append(accountDiv);
                    institutionsPane.closedAccountsDisplayed = true;
                } else {
                    // add account to institution collapse
                    $('#institution-id-' + accountObject.institution_id + ' #accounts-for-institution' + accountObject.institution_id).append(accountDiv);
                    $('#account-id-'+accountObject.id+' a').append('<br/><span class="institutions-pane-currency '+accountObject.currency+'"></span>'+accountObject.total)
                }

                // display account related entries on click
                $('#account-id-'+accountObject.id).click(function(){
                    paginate.current = 0;   // reset current "page number"
                    var accountFilterParameters = $.extend(true, {}, defaultFilterParameters);
                    accountFilterParameters.account = accountObject.id;
                    entries.filter(accountFilterParameters);
                    // assign active class after click event occurs
                    institutionsPane.clearActiveState();
                    institutionsPane.setActiveState(accountFilterParameters.account);
                });
            });
            institutionsPane.accountsDisplayed = true;
            $('#closed-accounts-parent').toggle(institutionsPane.closedAccountsDisplayed);  // show/hide closed accounts section
        } else {
            setTimeout(institutionsPane.displayAccounts, 50); // try again in 0.05 seconds
        }
    },
    displayAccountTypes: function(){
        // display account-types associated with accounts in a tooltip fashion
        if(institutionsPane.accountsDisplayed) {
            $.each(accountTypes.value, function (index, accountTypeObject) {
                var accountElement = $('#account-id-' + accountTypeObject.account_id+' a');
                if (!accountTypeObject.disabled){
                    var tooltipVal = accountElement.attr('title');
                    if(tooltipVal === undefined){
                        tooltipVal = '';
                    }
                    tooltipVal += "&bull; " + accountTypeObject.name + " (" + accountTypeObject.last_digits + ")<br/>\n";
                    accountElement.attr('title', tooltipVal);
                }
            });
            $('.institutions-pane-account a').each(function(idx, accountElement){
                var accountTooltip = $(accountElement).attr('title');
                var lastNewlineIndex = accountTooltip.lastIndexOf("\n");
                $(accountElement)
                    .attr('title', accountTooltip.substring(0, lastNewlineIndex))
                    .tooltip({placement: "right", html: true, container: 'body'});
            });
        } else {
            setTimeout(institutionsPane.displayAccountTypes, 100); // try again in 0.1 seconds
        }
    },
    clear: function(){
        institutionsPane.institutionsDisplayed = false;
        institutionsPane.accountsDisplayed = false;
        institutionsPane.closedAccountsDisplayed = false;
        $('.institutions-pane-account').remove();
        $('.institutions-pane-institution').remove();
    },
    clearActiveState: function(){
        $('#entry-overview').removeClass('active');
        $('.institutions-pane-account').removeClass('active');
    },
    setActiveState: function(accountId){
        if(!$.isNumeric(accountId)){
            $('#entry-overview').addClass('active');
        } else {
            $('#account-id-'+accountId)
                .addClass('active')
                .parents('.institutions-pane-collapse')
                .collapse('show');
        }
    }
};

$(document).ready(function(){
    $("#closed-accounts-parent")
        .on('show.bs.collapse', function(){
            $('#closed-accounts-parent .panel-title span')
                .removeClass('glyphicon-plus-sign')
                .addClass('glyphicon-minus-sign')
        })
        .on('hide.bs.collapse', function(){
            $('#closed-accounts-parent .panel-title span')
                .removeClass('glyphicon-minus-sign')
                .addClass('glyphicon-plus-sign');
        });

    $("#entry-overview").click(function(){
        paginate.current = 0;
        entries.load();
        institutionsPane.clearActiveState();
        institutionsPane.setActiveState();
        $('.institutions-pane-collapse.in').collapse('hide');
    });
});