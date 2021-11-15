<template>
  <BaseLineChart v-bind="lineChartProps"></BaseLineChart>
</template>

<script>
import BaseChart from './base-chart';
import {LineChart} from 'vue-chart-3';

export default {
  name: 'line-chart',
  extends: BaseChart,
  components: {
    BaseLineChart: LineChart
  },
  computed: {
    lineChartProps(){
      this.chartProps.cssClasses = 'line-chart';
      return this.chartProps;
    }
  }
}
</script>

<style scoped>

</style>