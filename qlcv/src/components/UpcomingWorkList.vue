<template>
    <div class="work-list" v-if="isDataReady">
        <div class="header">
            <div class="first-header">
                <h2>{{ receivedTitle }}</h2>
            </div>
            <div class="second-header">
                <div class="grid-column-header" v-for="status in [0, 1, 2, 3]" :key="status">
                    <h4>{{ columnHeaders[status] }}</h4>
                </div>
            </div>
        </div>
        <div class="grid-row">
            <div class="grid-column" v-for="(works, index) in filteredWorks" :key="index">
                <div v-for="work in works" :key="work.work_id" class="work-item">
                    <router-link
                        :to="{ name: 'work', params: { sharedProjectID: receivedProjectID, workId: work.work_id } }">
                        <Work :work-name="work.work_name" :label="work.label" :assigned-to="work.assigned_to"
                            :work-id="work.work_id" @delete="showModal" :end-date="work.end_date"
                            :is-project-owner="false" />
                    </router-link>
                </div>
            </div>
        </div>
        <router-view @back-to-worklist="getWorks" />
    </div>
</template>

<script>
import axios from "@nextcloud/axios";
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { NcButton, NcTextField, NcModal, NcEmptyContent } from "@nextcloud/vue";
import { getCurrentUser } from '@nextcloud/auth'
import Work from "./Work.vue";
import Plus from 'vue-material-design-icons/Plus.vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import WorkMenu from "./WorkMenu.vue";
import Test from "./data/Test.vue";


export default {
    name: 'UpcomingWorkList',
    components: {
        Work,
        NcButton,
        Plus,
        Magnify,
        NcTextField,
        WorkMenu,
        Test,
        NcModal,
        NcEmptyContent
    },
    data() {
        return {
            works: [],
            columnHeaders: ['TRƯỚC ĐÓ', 'CÒN 1 NGÀY', 'CÒN 7 NGÀY', 'CÒN 30 NGÀY'],
            user: getCurrentUser(),
            isDataReady: false,
            filteredWorks: [],
        };
    },

    computed: {
        receivedProjectID() {
            return this.$store.state.sharedProjectID;
        },

        receivedTitle() {
            return this.$store.state.sharedTitle;
        },

        receivedUserID() {
            return this.$store.state.sharedProjectOwner;
        },
        isChildRoute() {
            return this.$route.name !== 'project';
        },
        isProjectOwner() {
            return this.user.uid == this.receivedUserID;
        }
    },

    mounted() {
        this.getUpcomingWorks()
    },

    methods: {
        clearText() {
            this.searchQuery = ''
        },

        async getUpcomingWorks() {
            try {
                const response = await axios.get(generateUrl('/apps/qlcv/upcoming_works'));
                console.log(response);
                const { currentTimestamp, oneDayAhead, sevenDaysAhead } = this.calculateTimestamps();

                if (response.data && response.data.works) {
                    this.filteredWorks = [0, 1, 2, 3].map((type) => {
                        switch (type) {
                            case 0: return response.data.works.filter(work => work.end_date <= currentTimestamp);
                            case 1: return response.data.works.filter(work => work.end_date === oneDayAhead);
                            case 2: return response.data.works.filter(work => work.end_date > oneDayAhead && work.end_date < sevenDaysAhead);
                            case 3: return response.data.works.filter(work => work.end_date >= sevenDaysAhead);
                            default: return [];
                        }
                    });
                }
                console.log(this.filteredWorks)
                this.isDataReady = true;
            } catch (e) {
                console.error(e);
            }
        },

        calculateTimestamps() {
            const today = Math.floor(new Date().setHours(0, 0, 0, 0) / 1000);
            const oneDay = 24 * 60 * 60;
            return {
                currentTimestamp: today,
                oneDayAhead: today + oneDay,
                sevenDaysAhead: today + (7 * oneDay),
            };
        },
    }
}
</script>

<style scoped>
.work-list {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: flex-start;
    padding-left: 30px;
    padding-right: 30px;
    padding-bottom: 30px;
}

.chart {
    height: 100%
}

.header {
    width: 100%;
    position: sticky;
    top: 0px;
    background: white;
    z-index: 100;
    padding-top: 30px;
}

.first-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 10px;
}

.second-header {
    /* display: flex;
    justify-content: space-between; */
    padding-bottom: 10px;

    display: grid;
    grid-template-columns: repeat(4, 1fr);
    grid-gap: 50px;
}

.grid-column-header {
    flex: 1;
    text-align: center;
}

.grid-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    grid-gap: 50px;
    width: 100%;
    position: relative;
}

.grid-column {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.work-item {
    margin-bottom: 30px;
    width: 100%;
    cursor: pointer;
}

.search-and-add {
    display: flex;
    align-items: center;
}

.modal__content {
    margin: 20px;
    text-align: center;
}

.modal__actions {
    display: flex;
    justify-content: flex-end;
    gap: 20px;
    margin-top: 20px;
}
</style>