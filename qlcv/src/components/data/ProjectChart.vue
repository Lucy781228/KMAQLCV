<template>
    <div class="main-container" v-if="loaded">
        <div class="icon-container">
            <div class="icons">
                <NcButton aria-label="Example text" type="tertiary">
                    <template #icon>
                        <FilterVariant :size="20" />
                    </template>
                </NcButton>
                <NcButton aria-label="Example text" type="tertiary" @click="downloadBarChart">
                    <template #icon>
                        <ArrowCollapseDown :size="20" />
                    </template>
                </NcButton>
            </div>
        </div>
        <div class="chart-container">
            <BarChart :chart-data="bar.chartData" :options="bar.chartOptions" ref="barChart" :width="300"
                :height="320" />
        </div>
        <div class="icon-container">
            <div class="icons">
                <NcButton aria-label="Example text" type="tertiary">
                    <template #icon>
                        <FilterVariant :size="20" />
                    </template>
                </NcButton>
                <NcButton aria-label="Example text" type="tertiary" @click="downloadBarChart2">
                    <template #icon>
                        <ArrowCollapseDown :size="20" />
                    </template>
                </NcButton>
            </div>
        </div>
        <div class="chart-container">
            <LineChart :chart-data="bar2.chartData" :options="bar2.chartOptions" ref="barChart" :width="300"
                :height="320" />
        </div>
    </div>
</template>

<script>
import LineChart from './LineChart.vue'
import BarChart from './BarChart.vue'
import GanttChart from './GanttChart.vue'
import { getCurrentUser } from '@nextcloud/auth'
import axios from "@nextcloud/axios";
import { generateUrl } from '@nextcloud/router'
import FilterVariant from 'vue-material-design-icons/FilterVariant'
import ArrowCollapseDown from 'vue-material-design-icons/ArrowCollapseDown'
import { NcModal, NcButton } from "@nextcloud/vue";

export default {
    name: 'ProjectChart',
    components: { BarChart, GanttChart, NcButton, FilterVariant, ArrowCollapseDown, LineChart },
    data() {
        return {
            user: getCurrentUser(),
            loaded: false,
            bar: {
                chartData: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Tổng công việc',
                            backgroundColor: '#f87979',
                            data: []
                        },
                        {
                            label: 'Công việc hoàn thành',
                            backgroundColor: '#4b77a9',
                            data: []
                        }
                    ]
                },

                chartOptions: {
                    maintainAspectRatio: false,
                    responsive: true,
                    scales: {
                        xAxes: [{
                            gridLines: {
                                display: false
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    },
                    legend: {
                        display: true,
                        position: 'bottom',
                    },
                    tooltips: {
                        enabled: true,
                    },
                    title: {
                        display: true,
                        text: 'TỔNG CÔNG VIỆC VÀ SỐ CÔNG VIỆC HOÀN THÀNH Ở MỖI DỰ ÁN',
                        fontSize: 16,
                        fontColor: '#333'
                    }
                }
            },
            bar2: {
                chartData: {
                    labels: ['A','B','C'],
                    datasets: [
                        {
                            label: 'Gấp',
                            backgroundColor: '#f87979',
                            data: [1,2,3]
                        },
                        {
                            label: 'Quan trọng',
                            backgroundColor: '#4b77a9',
                            data: [2,4,5]
                        },
                        {
                            label: 'Bình thường',
                            backgroundColor: '#4b77a9',
                            data: [2,1,4]
                        }
                    ]
                },

                chartOptions: {
                    maintainAspectRatio: false,
                    responsive: true,
                    scales: {
                        xAxes: [{
                            gridLines: {
                                display: false
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    },
                    legend: {
                        display: true,
                        position: 'bottom',
                    },
                    tooltips: {
                        enabled: true,
                    },
                    title: {
                        display: true,
                        text: 'SỐ CÔNG VIỆC THEO TỪNG PHÂN LOẠI',
                        fontSize: 16,
                        fontColor: '#333'
                    }
                }
            },
        }
    },

    async mounted() {
        await this.fetchData()
    },

    methods: {
        async fetchData() {
            try {
                const workCountResponse = await axios.get(generateUrl(`/apps/qlcv/data/count_works/${this.user.uid}`));
                const doneWorkResponse = await axios.get(generateUrl(`/apps/qlcv/data/done_works/${this.user.uid}`));

                const workCounts = workCountResponse.data.data;
                const doneWorks = doneWorkResponse.data.data;

                const labels = workCounts.map(item => item.project_name);
                const totalWorks = workCounts.map(item => item.work_count);
                const completedWorks = doneWorks.map(item => item.work_count);

                this.bar.chartData = {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Tổng công việc',
                            backgroundColor: '#f87979',
                            data: totalWorks
                        },
                        {
                            label: 'Công việc hoàn thành',
                            backgroundColor: '#4b77a9',
                            data: completedWorks
                        }
                    ]
                };

                this.loaded = true;

            } catch (e) {
                console.error('Error fetching data:', e);
            }
        },
        downloadBarChart() {
            const chart = this.$refs.barChart.$data._chart;
            if (chart) {
                const url = chart.toBase64Image();
                const link = document.createElement('a');
                link.href = url;
                link.download = 'chart.png';
                link.click();
            } else {
                console.error('Chart instance not found');
            }
        },
        downloadBarChart() {
            const chart = this.$refs.barChart2.$data._chart;
            if (chart) {
                const url = chart.toBase64Image();
                const link = document.createElement('a');
                link.href = url;
                link.download = 'chart.png';
                link.click();
            } else {
                console.error('Chart instance not found');
            }
        }
    }
}
</script>

<style scoped>
.main-container {
    display: flex;
    flex-direction: column;
    height: 300px;
    padding: 50px
}

.icon-container {
    display: flex;
    justify-content: flex-end;
}

.icons {
    display: flex;
    align-items: center;
}
</style>