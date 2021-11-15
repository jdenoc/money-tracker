// utilities
import {Chart, registerables} from 'chart.js';
Chart.register(...registerables);

export const chartMixin = {
    props: {
        chartData: {
            type: Object,
            required:true
        },
        chartOptions: {
            type: Object,
            required: false,
            default:{}
        }
    },
    computed: {
        chartProps(){
            return {
                chartData: this.chartData,
                options: this.chartOptions,
                styles: {
                    marginTop: '2rem',
                    maxHeight: '30rem'
                }
            }
        }
    }
}
