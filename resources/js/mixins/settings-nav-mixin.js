export const settingsNavMixin = {
    data: function(){
        return {
            isVisibleSettings: {
                institutions: false,
                // accounts: false,
                accounts: true,     // TODO: used just for testing
                accountTypes: false,
                tags: false,
            }
        }
    },

    computed: {
        settingsNameInstitutions: function(){ return 'institutions' },
        settingsNameAccounts: function(){ return 'accounts' },
        settingsNameAccountTypes: function(){ return 'accountTypes' },
        settingsNameTags: function(){ return 'tags' },
    },

    methods: {
        makeSettingsVisible: function(settingsToMakeVisible){
            Object.keys(this.isVisibleSettings).forEach(function(settingsName){
                this.isVisibleSettings[settingsName] = false;
            }.bind(this));
            if(this.isVisibleSettings.hasOwnProperty(settingsToMakeVisible)){
                this.isVisibleSettings[settingsToMakeVisible] = true;
            } else {
                console.error("FEATURE IS NOT AVAILABLE");
            }
        },
    },
};