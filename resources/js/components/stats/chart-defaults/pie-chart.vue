<template>
  <BasePieChart v-bind="pieChartProps"></BasePieChart>
</template>

<script>
import BaseChart from './base-chart';
import {PieChart} from 'vue-chart-3';

export default {
  name: 'pie-chart',
  extends: BaseChart,
  components: {
    BasePieChart: PieChart
  },
  computed: {
    pieChartProps(){
      this.chartProps.cssClasses = 'pie-chart';
      return this.chartProps;
    }
  }
}
</script>

<style scoped>

</style>