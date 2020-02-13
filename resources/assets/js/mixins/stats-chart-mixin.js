import {Entries} from '../entries';
import {Tags} from "../tags"
import {SnotifyStyle} from "vue-snotify";

export const statsChartMixin = {
    data: function(){
        return {
            dataLoaded: false,
            entriesObject: new Entries(),
            tagsObject: new Tags(),

            buttons: {
                isActive: {
                    current: {
                        year: false,
                        quarter: false,
                        month: false,
                        week: false,
                        day: false
                    },
                    previous: {
                        year: false,
                        quarter: false,
                        month: false,
                        week: false,
                        day: false
                    }
                },
                isCustomDateRangeVisible: false
            },
        }
    },

    computed: {
        quarterMonth: function(){
            let d = new Date();
            return Math.floor(d.getMonth()/3)
        },

        currentYearStartDate: function(){
            return new Date().getFullYear()+"-01-01";
        },
        currentYearEndDate: function(){
            return new Date().getFullYear()+"-12-31";
        },
        currentQuarterStartDate: function(){
            let d1 = new Date();
            let d2 = new Date(d1.getFullYear(), this.quarterMonth * 3, 1);
            return this.isoDateFormat(d2);
        },
        currentQuarterEndDate: function(){
            let d1 = new Date();
            let d2 = new Date(d1.getFullYear(), this.quarterMonth * 3+3, 0);
            return this.isoDateFormat(d2);
        },
        currentMonthStartDate: function(){
            let d = new Date();
            return d.getFullYear()+"-"+("0"+(d.getMonth()+1)).slice(-2)+"-01"
        },
        currentMonthEndDate: function(){
            let d1 = new Date();
            let d2 = new Date(d1.getFullYear(), d1.getMonth()+1, 0);
            return this.isoDateFormat(d2);
        },
        currentWeekStartDate: function(){
            let d = new Date();
            d = new Date(d.setDate(d.getDate() - d.getDay()));
            return this.isoDateFormat(d);
        },
        currentWeekEndDate: function(){
            let d = new Date();
            d = new Date(d.setDate(d.getDate() - d.getDay() + 6));  // 6 is Saturday
            return this.isoDateFormat(d);
        },
        today: function(){
            return this.isoDateFormat(new Date());
        },

        previousYearStartDate: function(){
            return (new Date().getFullYear()-1)+"-01-01";
        },
        previousYearEndDate: function(){
            return (new Date().getFullYear()-1)+"-12-31";
        },
        previousQuarterStartDate: function(){
            let d1 = new Date();
            let d2 = new Date(d1.getFullYear(), this.quarterMonth * 3 - 3, 1);
            return this.isoDateFormat(d2);
        },
        previousQuarterEndDate: function(){
            let d1 = new Date();
            let d2 = new Date(d1.getFullYear(),  this.quarterMonth * 3, 0);
            return this.isoDateFormat(d2);
        },
        previousMonthStartDate: function(){
            let d1 = new Date();
            let d2 = new Date(d1.getFullYear(), d1.getMonth()-1, 1);
            return this.isoDateFormat(d2);
        },
        previousMonthEndDate: function(){
            let d1 = new Date();
            let d2 = new Date(d1.getFullYear(), d1.getMonth(), 0);
            return this.isoDateFormat(d2);
        },
        previousWeekStartDate: function(){
            let d = new Date();
            d = new Date(d.setDate(d.getDate() - (d.getDay()+7)));
            return this.isoDateFormat(d);
        },
        previousWeekEndDate: function(){
            let d = new Date();
            d = new Date(d.setDate(d.getDate() - (d.getDay()+7) + 6));  // 6 is Saturday
            return this.isoDateFormat(d);
        },
        yesterday: function(){
            let d = new Date();
            d.setDate(d.getDate() - 1);
            return this.isoDateFormat(d);
        },

        rawEntryData: function(){
            return this.entriesObject.retrieve;
        },
        rawTagsData: function(){
            return this.tagsObject.retrieve;
        },
    },

    methods: {
        tbdFeatureNotification: function(){
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
        toggleCustomDateRangeVisibility: function(){
            this.buttons.isCustomDateRangeVisible = !this.buttons.isCustomDateRangeVisible;
        },

        toggleActiveButton: function(newActiveTimePeriod = ''){
            newActiveTimePeriod = (newActiveTimePeriod === '') ? [] : newActiveTimePeriod.split('.');
            Object.keys(this.buttons.isActive.current).forEach(function(timePeriod){
                this.buttons.isActive.current[timePeriod] = false;
            }.bind(this));
            Object.keys(this.buttons.isActive.previous).forEach(function(timePeriod){
                this.buttons.isActive.previous[timePeriod] = false;
            }.bind(this));
            if(this.buttons.isActive.hasOwnProperty(newActiveTimePeriod[0])){
                if(this.buttons.isActive[newActiveTimePeriod[0]].hasOwnProperty(newActiveTimePeriod[1])){
                    this.buttons.isActive[newActiveTimePeriod[0]][newActiveTimePeriod[1]] = true;
                }
            }
        },
    },
};