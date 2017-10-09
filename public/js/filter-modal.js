var filterTags = $('#filter-tags');
$.each(tags.value, function(idx, obj){
    filterTags.append('<label class="btn btn-info"><input type="checkbox" value="'+obj.id+'"/>'+obj.tag+'</label>');
});

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
    },
    initAccountTypeSelect: function(){
        $.each(accountTypes.value, function(idx, accountTypeObject){
            if(!accountTypeObject.disabled){
                $("#filter-account-type").append('<option value="'+accountTypeObject.id+'">'+accountTypeObject.type_name+'</option>');
            }
        });
    },
    initTagsInput: function(){
        var filterTags = $('#filter-tags');
        var tagValues = tags.value.sort(function(a, b){
            return a.tag.length-b.tag.length
        });

        $.each(tagValues, function(idx, tagObj){
            filterTags.append('<label class="btn btn-info label-tag"><input type="checkbox" name="filter-tag" value="'+tagObj.id+'" autocomplete="off"/>'+tagObj.tag+'</label>');
        });
    },
    reset: function(){
        $('#filter-start-date').val('');
        $('#filter-end-date').val('');
        $('#filter-account-type').val('');
        $('#filter-tags label').removeClass('active');
        $('input[name="filter-expense').prop('checked', false)
            .bootstrapSwitch('state', false);
        $('input[name="filter-attachments"]').prop('checked', false)
            .bootstrapSwitch('state', false);
        $('#filter-unconfirmed').prop('checked', false)
            .bootstrapSwitch('state', false);
        $('.is_filtered').hide();
    },
    submit: function(){
        loading.start();

        var startDate = $('#filter-start-date').val();
        var endDate = $('#filter-end-date').val();
        var accountType = $('#filter-account-type').val();
        var tags = [];
        $.each($('input[name="filter-tag"]:checked'), function(idx, tagInput){
            tags.push($(tagInput).val());
        });
        var expenseInputValue = parseInt($('input[name="filter-expense"]:checked').val());
        var attachmentInputValue = parseInt($('input[name="filter-attachments"]:checked').val());
        var unconfirmed = $('#filter-unconfirmed:checked').val();
        var minValue = $('#filter-min-value').val();
        var maxValue = $('#filter-max-value').val();

        var filterModalFilterParameters = filterParameters;
        if(startDate !== '') {          filterModalFilterParameters.start_date = startDate;               }
        if(endDate !== '') {            filterModalFilterParameters.end_date = endDate;                   }
        if(minValue !== ''){            filterModalFilterParameters.min_value = parseFloat(minValue);     }
        if(maxValue !== ''){            filterModalFilterParameters.max_value = parseFloat(maxValue);     }
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

var filterParameters = {
    start_date: null,
    end_date: null,
    min_value: null,
    max_value: null,
    account: null,
    account_type: null,
    tags: null,
    expense: null,
    attachments: null,
    unconfirmed: null,
};