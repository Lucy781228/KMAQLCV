<template>
    <div class="wrapper">
        <div class="menu">
            <NcMultiselect class="nc-select" v-model="selectedOption" :options="options" label="text" id="option"
                ref="option" track-by="value" />
            Từ
            <NcDatetimePicker format="DD/MM/YYYY" class="nc-picker" :clearable="true" placeholder="Chọn một ngày" :disabled="!selectedOption"
                v-model="startDate" />
            đến
            <NcDatetimePicker format="DD/MM/YYYY" class="nc-picker" :clearable="true" placeholder="Chọn một ngày" :disabled="!selectedOption"
                v-model="endDate" />
            <NcButton aria-label="Example text" type="primary" @click="showChart" :disabled="!selectedOption">
                Áp dụng
            </NcButton>
        </div>
        <div class="content">
            <ProjectChart v-if="selectedOption.value == 0" :start-date="mysqlDateFormatter(start)" :end-date="mysqlDateFormatter(end)" />
        </div>
    </div>
</template>

<script>
import axios from "@nextcloud/axios";
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { NcButton, NcDatetimePicker, NcMultiselect } from "@nextcloud/vue";
import ProjectChart from './ProjectChart.vue'
// import { options } from "linkifyjs";

export default {
    name: 'DataMenu',
    components: {
        NcButton,
        NcDatetimePicker,
        NcMultiselect,
        ProjectChart
    },
    data() {
        return {
            start: null,
            end: null,
            selectedOption: { text: 'Dự án', value: 0 },
            startDate: null,
            endDate: null,
            options: [
                { text: 'Dự án', value: 0 },
                { text: 'Công việc', value: 1 },
            ],
        }
    },
    methods: {
        showChart() {
            this.start = this.startDate
            this.end = this.endDate
        },

        mysqlDateFormatter(date) {
            if (!date) return '';
            const year = date.getFullYear();
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const day = date.getDate().toString().padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
    },
}
</script>

<style scoped>
.wrapper {
    display: flex;
    flex-direction: column; /* Sắp xếp nội dung theo hướng dọc */
    padding: 20px 50px;
    width: 100%;
    height: 100%;
}

.menu {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 10px;
    margin-bottom: 10px;
}

.content {
    flex: 1; /* Chiếm toàn bộ không gian còn lại */
}

.menu NcButton {
    display: inline-block;
}

input {
    height: 44px !important
}

::v-deep .mx-input {
    height: 44px !important;
}

::v-deep .multiselect {
    min-width: auto !important;
    width: 150px !important;
}

::v-deep .multiselect__tags {
    border: 2px solid #949494 !important;
}

::v-deep .multiselect__tags:hover {
    border-color: #3287b5 !important;
}

::v-deep .nc-picker {
    width: 150px !important;
}
</style>