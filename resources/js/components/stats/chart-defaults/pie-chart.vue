<script>
import { Pie, mixins } from 'vue-chartjs';

export default {
  name: "pie-chart",
  extends: Pie,
  mixins: [mixins.reactiveProp],
  props: {
    options: {
      type: Object,
      default: null
    }
  },
  mounted () {
    this.renderChart(this.chartData, this.options)
  }
}
</script>