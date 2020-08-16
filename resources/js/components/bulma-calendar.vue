<template>
    <input type="date" class="input" ref="calendarTrigger" />
</template>

<script>
    import bulmaCalendar from 'bulma-calendar/dist/js/bulma-calendar.min';

    export default {
        name: "bulma-calendar",
        props: {
            dateRangeUpdateCallback: { type: Function }
        },
        computed: {
            calendarRefs: function(){
                return this.$refs.calendarTrigger;
            },
            getBulmaCalendar: function(){
                return this.calendarRefs.bulmaCalendar;
            },
            millisecondsPerMinute: function(){
                return 60000;
            },
            timezoneOffset: function(){
                return new Date().getTimezoneOffset()*this.millisecondsPerMinute;
            },
        },
        methods: {
            formatDate: function(d){
                return d.getFullYear() + "-" + ("0" + (d.getMonth() + 1)).slice(-2) + "-" + ("0" + d.getDate()).slice(-2);
            },

            calendarStartDate: function(){
                let d = this.getBulmaCalendar.date.start || new Date();
                return this.formatDate(d);
            },

            calendarEndDate: function(){
                let d = this.getBulmaCalendar.date.end || new Date();
                return this.formatDate(d);
            },

            setBulmaCalendarDateRange: function(startDate, endDate){
                let startDateObject = new Date(Date.parse(startDate) + this.timezoneOffset);
                let endDateObject = new Date(Date.parse(endDate) + this.timezoneOffset);
                this.getBulmaCalendar.date.start = startDateObject;
                this.getBulmaCalendar.date.end = endDateObject;
                this.getBulmaCalendar.save();
                this.getBulmaCalendar.refresh();
            },
        },
        mounted: function(){
            const calendar = bulmaCalendar.attach(this.calendarRefs, {
                color: 'info',
                isRange: "true",
                allowSameDayRange: false,
                dateFormat: "YYYY-MM-DD",
                showTodayButton: false,
            })[0];
            if(this.dateRangeUpdateCallback !== undefined){
                calendar.on('select', this.dateRangeUpdateCallback);
            }
        },

    }
</script>

<style scoped>

</style>



