<template>
  <BaseBarChart v-bind="barChartProps"></BaseBarChart>
</template>

<script>
import BaseChart from './base-chart';
import {BarChart} from 'vue-chart-3';

export default {
  name: 'bar-chart',
  extends: BaseChart,
  components: {
    BaseBarChart: BarChart
  },
  computed:{
    barChartProps(){
      this.chartProps.cssClasses = 'bar-chart';
      return this.chartProps;
    }
  }
}
</script>

<style scoped>

</style>