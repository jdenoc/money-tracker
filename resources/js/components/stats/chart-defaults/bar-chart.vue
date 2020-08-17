<script>
    import { Bar } from 'vue-chartjs';

    export default {
        name: "bar-chart",
        extends: Bar,
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
            this.renderChart(this.chartData, this.options)
        }
    }
</script>