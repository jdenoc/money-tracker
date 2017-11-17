var accountsPane = {
    displayed: false,
    displayAccounts: function(){
        $.each(accounts.value, function(index, accountObj){
            $('#account-display-pane').append(
                '<li id="account-id-'+accountObj.id+'">' +
                '<a href="#account-types-for-'+accountObj.id+'" data-toggle="collapse">'+accountObj.account+'<br/>$'+accountObj.total+'</a>' +
                '<div id="account-types-for-'+accountObj.id+'" class="collapse"></div>' +
                '</li>'
            );
        });
        accountsPane.displayed = true;
    },
    displayAccountType: function(){
        if(accountsPane.displayed) {
            $.each(accountTypes.value, function (idx, accountTypeObject) {
                if (!accountTypeObject.disabled) {
                    $('#account-id-' + accountTypeObject.account_group + ' #account-types-for-' + accountTypeObject.account_group).append(
                        '<div class="account-pane-account-type"><a href="#">' + accountTypeObject.type_name + '</a></div>'
                    );
                }
            });
        } else {
            setTimeout(accountsPane.displayAccountType, 50); // try again in 0.05 seconds
        }
    },
    clear: function(){
        accountsPane.displayed = false;
        $('#account-display-pane li:nth-child(n+3)').remove();
    }
};