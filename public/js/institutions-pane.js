var institutionsPane = {
    institutionsDisplayed: false,
    accountsDisplayed: false,
    displayInstitutions: function(){
        // display institutions in institutions pane
        $.each(institutions.value, function(index, institutionObj){
            if(institutionObj.active){
                $('#institution-display-pane').append(
                    '<li id="institution-id-'+institutionObj.id+'" class="panel panel-default">' +
                    '<a data-toggle="collapse" data-target="#accounts-for-institution'+institutionObj.id+'" data-parent="#institution-display-pane" class="panel-heading"><span class="panel-title">'+institutionObj.name+'</span></a>' +
                    '<div id="accounts-for-institution'+institutionObj.id+'" class="collapse panel-body"></div>' +
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
                if(!accountObject.disabled){
                    $('#institution-id-' + accountObject.institution_id + ' #accounts-for-institution' + accountObject.institution_id).append(
                        '<div id="account-id-'+accountObject.id+'" class="institutions-pane-account"><a href="#" data-toggle="tooltip" data-placement="bottom">'+accountObject.name+'<br/>$'+accountObject.total+'</a></div>'
                    );
                    // display account related entries on click
                    $('#account-id-'+accountObject.id).click(function(){
                        var accountFilterParameters = filterParameters;
                        accountFilterParameters.account = accountObject.id;
                        entries.filter(accountFilterParameters);
                        // assign active class after click
                        institutionsPane.clearActiveState();
                        $(this).addClass('active');
                    });
                }
            });
            institutionsPane.accountsDisplayed = true;
        } else {
            setTimeout(institutionsPane.displayAccounts, 50); // try again in 0.05 seconds
        }
    },
    displayAccountTypes: function(){
        // display account-types associated with accounts in a tooltip fashion
        if(institutionsPane.accountsDisplayed) {
            $.each(accountTypes.value, function (index, accountTypeObject) {
                if (!accountTypeObject.disabled) {
                    var accountElement = $('#account-id-' + accountTypeObject.account_id+' a');
                    var tooltipVal = accountElement.attr('title');
                    if(tooltipVal === undefined){
                        tooltipVal = '';
                    }
                    tooltipVal += "" + accountTypeObject.type_name + " (" + accountTypeObject.last_digits + ")\n";
                    accountElement.attr('title', tooltipVal);
                }
            });
            $('.institutions-pane-account a').tooltip({placement: "right"});
        } else {
            setTimeout(institutionsPane.displayAccountTypes, 100); // try again in 0.1 seconds
        }
    },
    clear: function(){
        institutionsPane.institutionsDisplayed = false;
        institutionsPane.accountsDisplayed = false;
        $('#institution-display-pane li:nth-child(n+3)').remove();
    },
    clearActiveState: function(){
        $('#entry-overview').removeClass('active');
        $('.institutions-pane-account').removeClass('active');
    }
};