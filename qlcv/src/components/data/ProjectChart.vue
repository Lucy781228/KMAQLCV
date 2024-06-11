<template>
    <div class="main-container" v-if="loaded">
        <div class=line-item1>
            <div class="icon-container">
                <div class="text-container">
                    <span>Tiến độ từng dự án</span>
                </div>
                <div class="icons">
                    <NcButton aria-label="Example text" type="tertiary">
                        <template #icon>
                            <ArrowCollapseDown :size="14" />
                        </template>
                    </NcButton>
                </div>
            </div>
            <div class="container">
                <div class="chart-container">
                    <BarChart :chart-data="projectStatus.chartData" :options="projectStatus.chartOptions"
                        ref="projectStatus" :width="2000" :height="570" />
                </div>
            </div>
        </div>
        <div class=line-item2>
            <div class="icon-container">
                <div class="text-container">
                    <span>Phân loại công việc theo độ ưu tiên</span>
                </div>
                <div class="icons">
                    <NcButton aria-label="Example text" type="tertiary">
                        <template #icon>
                            <ArrowCollapseDown :size="14" />
                        </template>
                    </NcButton>
                </div>
            </div>
            <div class="chart-container">
                <BarChart :chart-data="projectPriority.chartData" :options="projectPriority.chartOptions"
                    ref="projectPriority" :width="200" :height="570" />
            </div>
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
    props: {
        startDate: {
            type: String,
            required: true
        },

        endDate: {
            type: String,
            default: true
        }
    },
    data() {
        return {
            user: getCurrentUser(),
            loaded: false,
            projectStatus: {
                chartData: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Tổng công việc',
                            data: [],
                            borderColor: '#006AA3',
                            borderWidth: 1,
                            data: [],
                            lineTension: 0,
                            type: 'line',
                            fill: false,
                            backgroundColor: 'transparent'
                        },
                        {
                            label: 'Cần làm',
                            backgroundColor: '#f87979',
                            data: [],
                            // maxBarThickness: 8,
                            barPercentage: 0.8,
                            barThickness: 50,
                            categoryPercentage: 0.5,
                        },
                        {
                            label: 'Đang tiến hành',
                            backgroundColor: '#4b77a9',
                            data: [],
                            // maxBarThickness: 8,
                            barPercentage: 0.8,
                            barThickness: 50,
                            categoryPercentage: 0.5,
                        },
                        {
                            label: 'Chờ duyệt',
                            backgroundColor: '#f87979',
                            data: [],
                            // maxBarThickness: 8,
                            barPercentage: 0.8,
                            barThickness: 50,
                            categoryPercentage: 0.5,
                        },
                        {
                            label: 'Hoàn thành',
                            backgroundColor: '#4b77a9',
                            data: [],
                            // maxBarThickness: 8,
                            barPercentage: 0.8,
                            barThickness: 50,
                            categoryPercentage: 0.5,
                        },
                    ]
                },
                chartOptions: {
                    maintainAspectRatio: false,
                    responsive: true,
                    scales: {
                        xAxes: [{
                            stacked: true,
                            gridLines: {
                                display: false
                            }
                        }],
                        yAxes: [{
                            stacked: true,
                            ticks: {
                                beginAtZero: true,
                                callback: function (value) {
                                    if (Number.isInteger(value)) {
                                        return value;
                                    }
                                }
                            }
                        }],
                    },
                    legend: {
                        position: 'top',
                        labels: {
                            filter: function (legendItem) {
                                if (legendItem.datasetIndex === 0) {
                                    return false;
                                }
                                return true;
                            }
                        }
                    },
                    tooltips: {
                        enabled: true,
                    }
                },
                lineChartData: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Số lượng người dùng',
                            backgroundColor: 'rgba(0, 106, 163, 0.2)', // Màu nền nhạt hơn của #006AA3
                            borderColor: '#006AA3', // Màu viền là #006AA3
                            borderWidth: 1,
                            data: [],
                            lineTension: 0
                        }
                    ]
                },
                lineChartOptions: {
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
                                beginAtZero: true,
                                callback: function (value) {
                                    if (Number.isInteger(value)) {
                                        return value;
                                    }
                                }
                            }
                        }]
                    },
                    legend: {
                        display: false,
                    },
                    tooltips: {
                        enabled: true,
                    }
                }
            },

            projectPriority: {
                chartData: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Cao',
                            backgroundColor: '#f87979',
                            data: []
                        },
                        {
                            label: 'Trung bình',
                            backgroundColor: '#4b77a9',
                            data: []
                        },
                        {
                            label: 'Thấp',
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
                            ticks: {
                                beginAtZero: true,
                                callback: function (value) {
                                    if (Number.isInteger(value)) {
                                        return value;
                                    }
                                }
                            }
                        }],
                        yAxes: [{
                            gridLines: {
                                display: false
                            }
                        }],
                    },
                    legend: {
                        position: 'top',
                    },
                    tooltips: {
                        enabled: true,
                    }
                }
            },
        }
    },

    async mounted() {
        await this.fetchData()
    },

    watch: {
        startDate: 'fetchData',
        endDate: 'fetchData'
    },

    methods: {
        async fetchData() {
            this.loaded = false;
            try {
                const params = {
                    startDate: this.startDate,
                    endDate: this.endDate
                };
                const response = await axios.get(generateUrl(`/apps/qlcv/data/count_works/${this.user.uid}`, { params }));

                const workCounts = response.data.data;
                const filteredWorkCounts = workCounts.filter(item => item.all_works > 0);

                const labels = filteredWorkCounts.map(item => item.project_name);
                const all_works = filteredWorkCounts.map(item => item.all_works);
                const todo_work = filteredWorkCounts.map(item => item.todo_work);
                const doing_work = filteredWorkCounts.map(item => item.doing_work);
                const pending_work = filteredWorkCounts.map(item => item.pending_work);
                const done_work = filteredWorkCounts.map(item => item.done_work);

                const high = filteredWorkCounts.map(item => item.high);
                const normal = filteredWorkCounts.map(item => item.normal);
                const low = filteredWorkCounts.map(item => item.low);

                this.projectStatus.chartData.labels = labels;
                this.projectStatus.chartData.datasets[0].data = all_works;
                this.projectStatus.chartData.datasets[1].data = todo_work;
                this.projectStatus.chartData.datasets[2].data = doing_work;
                this.projectStatus.chartData.datasets[3].data = pending_work;
                this.projectStatus.chartData.datasets[4].data = done_work;

                this.projectPriority.chartData.labels = labels;
                this.projectPriority.chartData.datasets[0].data = high;
                this.projectPriority.chartData.datasets[1].data = normal;
                this.projectPriority.chartData.datasets[2].data = low;

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
        }
    }
}
</script>

<style scoped>
.container {
    overflow-x: auto;
    max-width: 528px;
    width: 528px;
}

.main-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-gap: 20px;
    padding-top: 20px;
    padding-bottom: 20px;
    width: 100%;
    height: 100%
}

.line-item1,
.line-item2 {
    display: flex;
    flex-direction: column;
    border: 1px solid #ccc;
    border-radius: 10px;
    padding: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.3s ease;
    height: 100%;
}

.icon-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}

.text-container {
    margin-left: 200px;
    font-size: 16px;
    font-weight: bold;
}

.icons {
    display: flex;
    align-items: center;
    visibility: hidden;
}

.line-item2:hover,
.line-item1:hover {
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.line-item2:hover .icons,
.line-item1:hover .icons {
    visibility: visible;
}
</style>