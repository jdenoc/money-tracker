<script>
    import { Line, mixins } from 'vue-chartjs'

    export default {
        name: "line-chart",
        extends: Line,
        mixins: [mixins.reactiveProp],
        props: {
            options: {
                type: Object,
                default: null
            }
        },
        mounted () {
            // Overwriting base render method with actual data.
            this.renderChart(this.chartData, this.options);
        }
    }
</script>