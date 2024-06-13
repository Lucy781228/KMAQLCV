<template>
    <div v-if="showEmpty">
        <NcLoadingIcon />
    </div>
    <div v-else-if="!works.length && isChildRoute">
        <NcEmptyContent>
            <template #title>
                <h1 class="empty-content__title">
                    Không có công việc
                </h1>
            </template>
            <template #action>
                <NcButton ariaLabel="A" :to="{ name: 'new-work', params: { sharedProjectID: receivedProjectID } }"
                    type="primary">
                    Thêm công việc
                </NcButton>
            </template>
        </NcEmptyContent>
    </div>
    <div class="work-list" v-else-if="isChildRoute">
        <div class="header">
            <div class="first-header">
                <h2>{{ receivedTitle }}</h2>
                <div class="search-and-add">
                    <NcTextField :value.sync="searchQuery" label="Nhập tên công việc" trailing-button-icon="close"
                        :show-trailing-button="searchQuery !== ''" @trailing-button-click="clearText">
                        <Magnify :size="16" />
                    </NcTextField>
                    <!-- <NcButton type="tertiary"
                        :to="{ name: 'project-gantt', params: { sharedProjectID: receivedProjectID } }"
                        aria-label="Example text" v-if="isProjectOwner">
                        <template #icon>
                            <ChartGantt :size="20" />
                        </template>
                    </NcButton> -->
                    <NcButton type="tertiary" :to="{ name: 'new-work', params: { sharedProjectID: receivedProjectID } }"
                        aria-label="Example text" v-if="isProjectOwner && sharedProjectStatus != 2">
                        <template #icon>
                            <Plus :size="20" />
                        </template>
                    </NcButton>
                </div>
            </div>
            <div class="second-header" v-if="sharedProjectStatus == 1">
                <div class="grid-column-header" v-for="status in [0, 1, 2, 3]" :key="status">
                    <h4>{{ columnHeaders[status] }}</h4>
                </div>
            </div>
        </div>
        <div class="grid-row" v-if="sharedProjectStatus == 1">
            <div class="grid-column" v-for="status in [0, 1, 2, 3]" :key="status">
                <router-link
                    :to="{ name: 'work', params: { sharedProjectID: receivedProjectID, workId: work.work_id } }"
                    class="work-item" v-for="work in filteredWorksByStatus(status)" :key="work.work_id">
                    <Work :work-name="work.work_name" :label="work.label" :assigned-to="work.assigned_to"
                        :status="work.status" :work-id="work.work_id" @delete="showModal" :end-date="work.end_date"
                        :is-project-owner="isProjectOwner" @update="getWorks" />
                </router-link>
            </div>
        </div>
        <div class="grid-row" v-else>
            <router-link v-for="work in works" :key="work.work_id"
                :to="{ name: 'work', params: { sharedProjectID: receivedProjectID, workId: work.work_id } }"
                class="work-item">
                <Work :work-name="work.work_name" :label="work.label" :assigned-to="work.assigned_to"
                    :status="work.status" :work-id="work.work_id" @delete="showModal" :end-date="work.end_date"
                    :is-project-owner="isProjectOwner" @update="getWorks" />
            </router-link>
        </div>
        <router-view @back-to-worklist="getWorks" />

        <NcModal :show="isDelete" :canClose="false" size="small">
            <div class="modal__content">
                <h3>Bạn chắc chắn không?</h3>
                <div class="modal__actions">
                    <NcButton @click="stopModal" type="primary" aria-label="Example text">
                        Hủy
                    </NcButton>
                    <NcButton @click="deleteWork" type="secondary" aria-label="Example text">
                        Xóa
                    </NcButton>
                </div>
            </div>
        </NcModal>
    </div>
    <div class="chart" v-else>
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
import ChartGantt from 'vue-material-design-icons/ChartGantt.vue'
import WorkMenu from "./WorkMenu.vue";
import Test from "./data/Test.vue";


export default {
    name: 'WorkList',
    components: {
        Work,
        NcButton,
        Plus,
        Magnify,
        NcTextField,
        WorkMenu,
        ChartGantt,
        Test,
        NcModal,
        NcEmptyContent
    },
    data() {
        return {
            works: [],
            columnHeaders: ['CẦN LÀM', 'ĐANG THỰC HIỆN', 'CHỜ DUYỆT', 'HOÀN THÀNH'],
            user: getCurrentUser(),
            searchQuery: '',
            filteredWorks: [],
            modal: false,
            work: null,
            showGantt: false,
            isDelete: false,
            workId: 0,
            showEmpty: true,
        };
    },

    computed: {
        sharedProjectStatus() {
            return this.$store.state.sharedProjectStatus;
        },
        receivedProjectID() {
            return this.$store.state.sharedProjectID
        },

        receivedTitle() {
            return this.$store.state.sharedTitle
        },

        receivedUserID() {
            return this.$store.state.sharedProjectOwner
        },
        isChildRoute() {
            return this.$route.name !== 'project-gantt' && this.$route.name !== 'new-work'
        },
        isProjectOwner() {
            return this.user.uid == this.receivedUserID
        },
        completedWorksCount() {
            return this.works.filter(work => work.status === 3).length
        },
    },

    mounted() {

    },

    watch: {
        receivedProjectID: {
            immediate: true,
            handler(newVal) {
                if (newVal && this.receivedProjectID) {
                    this.getWorks();
                }
            }
        },
        searchQuery(newQuery) {
            if (this.searchQuery) {
                this.filteredWorks = this.works.filter(work => {
                    return work.work_name.toLowerCase().includes(newQuery.toLowerCase());
                });
            } else this.filteredWorks = this.works
        }
    },

    methods: {
        showGanttChart() {
            this.showGantt = true
            this.$router.push({ path: `/project/${this.receivedProjectID}/gantt` });
        },

        filteredWorksByStatus(status) {
            return this.filteredWorks.filter(work => work.status === status);
        },

        clearText() {
            this.searchQuery = ''
        },

        async getWorks() {
            try {
                const response = await axios.get(generateUrl('/apps/qlcv/works'), {
                    params: {
                        project_id: this.receivedProjectID,
                        user_id: this.receivedUserID,
                        assigned_to: this.user.uid
                    }
                });
                this.works = response.data.works;
                this.filteredWorks = JSON.parse(JSON.stringify(this.works))
                this.showEmpty = false
                this.$emit('close')
            } catch (e) {
                console.error(e)
            }
        },

        async getWork(id) {
            try {
                const response = await axios.get(generateUrl(`/apps/qlcv/work_by_id/${id}`));
                this.work = response.data.work

            } catch (e) {
                console.error(e)
            }
        },

        async deleteWork() {
            try {
                const response = await axios.delete(generateUrl('apps/qlcv/delete_work/' + this.workId))
                showSuccess(t('qlcv', 'Xóa thành công'));
                this.stopModal()
                this.getWorks()
            } catch (e) {
                console.error(e)
            }
        },

        showModal(workId) {
            this.isDelete = true
            this.workId = workId
        },

        stopModal() {
            this.isDelete = false
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

.chart {
    height: 100%;
    width: 100%
}
</style>