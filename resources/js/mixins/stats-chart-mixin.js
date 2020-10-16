import {SnotifyStyle} from "vue-snotify";

export const statsChartMixin = {
    data: function(){
        return {
            dataLoaded: false,
            includeTransfers: false,

            chartConfig: {
                titleText: "Generated data"
            },
        }
    },

    computed: {
        currentMonthStartDate: function(){
            let d = new Date();
            return d.getFullYear()+"-"+("0"+(d.getMonth()+1)).slice(-2)+"-01"
        },
        currentMonthEndDate: function(){
            let d1 = new Date();
            let d2 = new Date(d1.getFullYear(), d1.getMonth()+1, 0);
            return this.isoDateFormat(d2);
        }
    },

    methods: {
        tbdFeatureNotification: function(){
            console.debug("Feature not yet enabled");
            this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, {type: SnotifyStyle.info, message: "Feature not yet enabled"});
        },
        isoDateFormat: function(d){
            // YYYY-mm-dd
            return d.getFullYear()+"-"+("0"+(d.getMonth()+1)).slice(-2)+"-"+("0"+d.getDate()).slice(-2);
        },
        randomColor: function(){
            let max=255, min=0;
            let r=Math.floor(Math.random() * (max - min + 1) + min);
            let g=Math.floor(Math.random() * (max - min + 1) + min);
            let b=Math.floor(Math.random() * (max - min + 1) + min);
            return 'rgba('+r+', '+g+', '+b+', 1)';
        },
        filterIncludeTransferEntries: function(entry){
            // TODO: take into account external transfers (e.g.: transfer_entry_id=0)
            return this.includeTransfers
                || (!this.includeTransfers && entry.hasOwnProperty('is_transfer') && !entry.is_transfer);
        }
    },
};