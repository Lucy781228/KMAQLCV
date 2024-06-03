<template>
    <NcModal v-if="true" size="large" :canClose="false">
        <div class="modal__content">
            <NcAppSidebar @close="closeMenu" class="sidebar">
                <NcAppSidebarTab name="Chi tiết" id="details" order="1">
                    <template #icon>
                        <Home :size="20" />
                    </template>
                    <Details v-if="work" :work-id="workId" :is-project-owner="isProjectOwner" :disabled="work.status"/>
                </NcAppSidebarTab>
                <NcAppSidebarTab name="Tác vụ" id="tasks" order="2">
                    <template #icon>
                        <CardsVariant :size="20" />
                    </template>
                    <Task v-if="work" :work-id="workId" :is-project-owner="isProjectOwner" :disabled="work.status" :is-todo-work="work.status"/>
                </NcAppSidebarTab>
                <NcAppSidebarTab name="Đính kèm" id="attachment" order="3">
                    <template #icon>
                        <Paperclip :size="20" />
                    </template>
                    <File v-if="work" :work-id="workId" :assigned-to="work.assigned_to" :owner="work.owner"
                        :shareUser="shareUser" />
                </NcAppSidebarTab>
                <NcAppSidebarTab name="Bình luận" id="comment" order="4">
                    <template #icon>
                        <Comment :size="20" />
                    </template>
                    <WorkComment v-if="work" :work-id="workId" />
                </NcAppSidebarTab>
            </NcAppSidebar>
        </div>
    </NcModal>
</template>
<script>
import Home from 'vue-material-design-icons/Home'
import Paperclip from 'vue-material-design-icons/Paperclip'
import Comment from 'vue-material-design-icons/Comment'
import CardsVariant from 'vue-material-design-icons/CardsVariant'
import axios from "@nextcloud/axios";
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { NcModal, NcAppSidebar, NcAppSidebarTab, NcButton } from "@nextcloud/vue";
import File from './tab/File.vue';
import Details from './tab/Details.vue';
import WorkComment from './tab/WorkComment.vue';
import Task from './tab/Task.vue';
import { getCurrentUser } from '@nextcloud/auth'

export default {
    name: 'WorkMenu',
    components: {
        Home,
        Paperclip,
        Comment,
        NcAppSidebar,
        NcAppSidebarTab,
        NcModal,
        NcButton,
        CardsVariant,
        File,
        Details,
        WorkComment,
        Task
    },

    props: {
        workId: {
            type: String,
            required: true
        }
    },

    data() {
        return {
            work: null,
            user: getCurrentUser(),
            shareUser: '',
        }
    },

    mounted() {
        this.getWork()
    },

    computed: {
        receivedProjectID() {
            return this.$store.state.sharedProjectID;
        },
        receivedUserID() {
            return this.$store.state.sharedProjectOwner;
        },
        isProjectOwner() {
            return this.user.uid == this.receivedUserID;
        }
    },

    methods: {
        closeMenu() {
            this.$emit('back-to-worklist');
            console.log('from workmenu')
            this.$router.push({ name: 'project', params: { receivedProjectID: this.$route.params.sharedProjectID } });
        },

        async getWork() {
            try {
                const response = await axios.get(generateUrl(`/apps/qlcv/work_by_id/${this.workId}`));
                this.work = response.data.work
                this.shareUser = this.user.uid == this.work.assigned_to ? this.work.owner : this.work.assigned_to

            } catch (e) {
                console.error(e)
            }
        }
    }
}
</script>

<style scoped>
::v-deep .sidebar {
    max-width: none !important;
    width: 100% !important;
    /* height: 650px !important; */
}

::v-deep .app-sidebar-header {
    height: 40px !important
}
</style>