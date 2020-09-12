<script>
    import { Bar, mixins } from 'vue-chartjs';

    export default {
        name: "bar-chart",
        extends: Bar,
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