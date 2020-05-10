<script>
    import { Line } from 'vue-chartjs';

    export default {
        name: "line-chart",
        extends: Line,
        props: {
            chartData: {
                type: Object,
                default: null
            },
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