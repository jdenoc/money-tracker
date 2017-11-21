var bootstrapSwitchObject = {
    size: "small",
    handleWidth: 79,    // forces display width to 200px
    inverse: true,
    wrapperClass: ['pull-right'],
    onText: "Enabled",
    offText: "Disabled"
};
var bootstrapSwitchRadioObject = $.extend({}, bootstrapSwitchObject, {radioAllOff: true});
$("input[name='filter-expense']").bootstrapSwitch(bootstrapSwitchRadioObject);
$("input[name='filter-attachments']").bootstrapSwitch(bootstrapSwitchRadioObject);
$("#filter-unconfirmed").bootstrapSwitch(bootstrapSwitchObject);

var filterModal = {
    active: false,
    init: function(){
        $('#filter-reset').click(filterModal.reset);
        $('#filter-set').click(filterModal.submit);
        filterModal.toggleAccountOrAccountTypeSelect();
    },
    toggleAccountOrAccountTypeSelect: function(){
        $('#filter-account-or-account-type .generated').remove();
        var currentState = $('#filter-toggle-account-or-account-type').bootstrapSwitch('state');
        // on/true = account; off/false = account_type; see bootstrapSwitchAccountOrAccountTypeObject
        if(currentState){
            filterModal.initAccountSelect();
        } else {
            filterModal.initAccountTypeSelect();
        }
    },
    initAccountSelect: function(){
        $("#filter-account-or-account-type").attr('name', 'filter-account');
        filterModal.initAccountOrAccountTypeSelect(accounts.value);
    },
    initAccountTypeSelect: function(){
        $("#filter-account-or-account-type").attr('name', 'filter-account-type');
        filterModal.initAccountOrAccountTypeSelect(accountTypes.value);
    },
    initAccountOrAccountTypeSelect: function(accountOrAccountTypeValues){
        $.each(accountOrAccountTypeValues, function(idx, accountOrAccountTypeObject){
            if(!accountOrAccountTypeObject.disabled){
                if(accountOrAccountTypeObject.hasOwnProperty('type_name')){
                    accountOrAccountTypeObject.name = accountOrAccountTypeObject.type_name;
                }
                $("#filter-account-or-account-type").append('<option value="'+accountOrAccountTypeObject.id+'" class="generated">'+accountOrAccountTypeObject.name+'</option>');
            }
        });
    },
    initTagsInput: function(){
        var filterTags = $('#filter-tags');
        var tagValues = tags.value.sort(function(a, b){
            return a.name.length-b.name.length
        });

        $.each(tagValues, function(idx, tagObj){
            filterTags.append('<label class="btn btn-info label-tag"><input type="checkbox" name="filter-tag" value="'+tagObj.id+'" autocomplete="off"/>'+tagObj.name+'</label>');
        });
    },
    reset: function(){
        // traditional inputs
        $('#filter-start-date').val('');
        $('#filter-end-date').val('');
        $('#filter-min-value').val('');
        $('#filter-max-value').val('');
        $('#filter-account-or-account-type').val('');
        // tags
        $('#filter-tags label').removeClass('active');
        $('input[name="filter-tag"]').prop('checked', false);
        // toggle/switches
        $('input[name="filter-expense').prop('checked', false)
            .bootstrapSwitch('state', false);
        $('input[name="filter-attachments"]').prop('checked', false)
            .bootstrapSwitch('state', false);
        $('#filter-unconfirmed').prop('checked', false)
            .bootstrapSwitch('state', false);
    },
    submit: function(){
        loading.start();
        filterModal.active = true;
        institutionsPane.clearActiveState();
        $('.institutions-pane-collapse.in').collapse('hide');

        var startDate = $('#filter-start-date').val();
        var endDate = $('#filter-end-date').val();
        var account = '';
        var accountType = '';
        if($('#filter-toggle-account-or-account-type').bootstrapSwitch('state')){
            account = $('select[name="filter-account"]').val();
        } else {
            accountType = $('select[name="filter-account-type"]').val();
        }
        var tags = [];
        $.each($('input[name="filter-tag"]:checked'), function(idx, tagInput){
            tags.push($(tagInput).val());
        });
        var expenseInputValue = parseInt($('input[name="filter-expense"]:checked').val());
        var attachmentInputValue = parseInt($('input[name="filter-attachments"]:checked').val());
        var unconfirmed = $('#filter-unconfirmed:checked').val();
        var minValue = $('#filter-min-value').val();
        var maxValue = $('#filter-max-value').val();

        var filterModalFilterParameters = $.extend(true, {}, defaultFilterParameters);
        if(startDate !== '') {          filterModalFilterParameters.start_date = startDate;               }
        if(endDate !== '') {            filterModalFilterParameters.end_date = endDate;                   }
        if(minValue !== ''){            filterModalFilterParameters.min_value = parseFloat(minValue);     }
        if(maxValue !== ''){            filterModalFilterParameters.max_value = parseFloat(maxValue);     }
        if(account !== ''){             filterModalFilterParameters.account = parseInt(account);          }
        if(accountType !== ''){         filterModalFilterParameters.account_type = parseInt(accountType); }
        if(tags.length > 0){            filterModalFilterParameters.tags = tags;                          }
        if(expenseInputValue === 0){    filterModalFilterParameters.expense = false;                      } // income
        if(expenseInputValue === 1){    filterModalFilterParameters.expense = true;                       } // expense
        if(attachmentInputValue === 0){ filterModalFilterParameters.attachments = false;                  } // no attachments
        if(attachmentInputValue === 1){ filterModalFilterParameters.attachments = true;                   } // has attachments
        if(unconfirmed){                filterModalFilterParameters.unconfirmed = true;                   }

        entries.filter(filterModalFilterParameters);
    }
};

var bootstrapSwitchAccountOrAccountTypeObject = $.extend({}, bootstrapSwitchObject, {
    onText: "Account",
    offText: "Account Type",
    onColor: 'default',
    offColor: 'default',
    wrapperClass: ['pull-left'],
    onSwitchChange: filterModal.toggleAccountOrAccountTypeSelect
});
$('#filter-toggle-account-or-account-type').bootstrapSwitch(bootstrapSwitchAccountOrAccountTypeObject);

var defaultFilterParameters = {
    start_date: null,
    end_date: null,
    min_value: null,
    max_value: null,
    account: null,
    account_type: null,
    tags: null,
    expense: null,
    attachments: null,
    unconfirmed: null
};