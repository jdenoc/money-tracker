<template>
  <div class="grid grid-cols-2 gap-x-2">
    <label v-bind:for="inputStartDateId" class="font-medium">Start Date:</label>
    <label v-bind:for="inputEndDateId" class="font-medium">End Date:</label>
    <input type="date" name="start-date" v-bind:id="inputStartDateId" v-model="dateRangeStartDate" class="text-gray-700 rounded" />
    <input type="date" name="end-date" v-bind:id="inputEndDateId" v-model="dateRangeEndDate" class="text-gray-700 rounded" />
  </div>
</template>

<script>
const EMIT_DATE_START = 'update:startDate';
const EMIT_DATE_END = 'update:endDate';

export default {
  name: "date-range",
  props: {
    chartName: {type: String, required: true},
    endDate: {type: String},
    startDate: {type: String},
  },
  data: function (){
    return {
      dateRangeStartDate:'',
      dateRangeEndDate:'',
    };
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
    },
    inputStartDateId: function(){
      return this.chartName+'-start-date';
    },
    inputEndDateId: function(){
      return this.chartName+'-end-date';
    },
  },
  methods: {
    isoDateFormat: function(d){
      // YYYY-mm-dd
      return d.getFullYear()+"-"+("0"+(d.getMonth()+1)).slice(-2)+"-"+("0"+d.getDate()).slice(-2);
    },
  },
  watch:{
    startDate: function(newValue){
      // if props.startDate is updated after init
      this.dateRangeStartDate = newValue;
    },
    endDate: function(newValue){
      // if props.endDate is updated after init
      this.dateRangeEndDate = newValue
    },
    dateRangeStartDate: function(newValue){
      this.$emit(EMIT_DATE_START, newValue);
    },
    dateRangeEndDate: function(newValue){
      this.$emit(EMIT_DATE_END, newValue);
    }
  },
  mounted: function(){
    this.dateRangeStartDate = this.currentMonthStartDate;
    this.dateRangeEndDate = this.currentMonthEndDate;
  }
}
</script>

<style scoped>
</style>