<template>
    <div class="new-work">
        <div class="title">
            <h2>THÊM CÔNG VIỆC</h2>
        </div>
        <div class="grid-view-a">
            <div class="grid-item grid-view-b">
                <div class="grid-item-b full-width">
                    <label>Tên công việc (*)</label>
                    <input type="text" v-model="work.work_name" />
                </div>
                <div class="grid-item-b">
                    <label>Nhãn</label>
                    <NcMultiselect ref="label" class="nc-select" v-model="work.label" :options="labels" label="text"
                        track-by="text" />
                </div>
                <div class="grid-item-b">
                    <label>Người nhận việc (*)</label>
                    <NcMultiselect ref="assigned_to" class="nc-select" v-model="work.assigned_to" :options="formatUsers"
                        label="userId" track-by="userId" :user-select="true">
                        <template #singleLabel="{ option }">
                            <NcListItemIcon v-bind="option" :title="option.userId" :avatar-size="24"
                                :no-margin="true" />
                        </template>
                    </NcMultiselect>
                </div>
                <div class="grid-item-b">
                    <label>Ngày bắt đầu (*)</label>
                    <NcDatetimePicker ref="start_date" format="DD/MM/YYYY" class="nc-picker"
                        v-model="work.start_date" />
                </div>
                <div class="grid-item-b">
                    <label>Ngày kết thúc (*)</label>
                    <NcDatetimePicker ref="end_date" format="DD/MM/YYYY" class="nc-picker" v-model="work.end_date" />
                </div>
                <div class="grid-item-b full-width">
                    <label>Mô tả</label>
                    <textarea class="description" type="text" v-model="work.description"> </textarea>
                </div>
            </div>
            <div class="grid-item">
                <h3 class="task-title">TÁC VỤ</h3>
                <div class="scrollable-list">
                    <div class="list-item" v-for="(task, index) in tasks" :key="index">
                        <div class="inline-item">
                            <div class="task-number">{{ index + 1 }}</div>
                            <div @click="startEditting(index)" class="task-content">{{ task }}</div>
                            <Close class="close-icon" :size="16" @click="deleteTask(index)" />
                        </div>
                    </div>
                </div>
                <div class="task-field">
                    <input type="text" v-model="taskContent" placeholder="Thêm tác vụ" />
                    <NcButton type="tertiary" @click="resetTaskField" ariaLabel="A">
                        <template #icon>
                            <Close :size="20" />
                        </template>
                    </NcButton>
                    <NcButton type="primary" :disabled="!taskContent" @click="addToTaskField" ariaLabel="A">
                        <template #icon>
                            <ArrowRight :size="20" />
                        </template>
                    </NcButton>
                </div>
            </div>
        </div>
        <div class="combo-actions">
            <NcButton type="secondary" @click="cancel" ariaLabel="A">
                Hủy
            </NcButton>
            <NcButton @click="createWork" type="primary" ariaLabel="A">
                Thêm
            </NcButton>
        </div>
    </div>
</template>

<script>
import { NcMultiselect, NcDatetimePicker, NcListItemIcon, NcButton, NcTextField } from "@nextcloud/vue";
import { generateUrl } from '@nextcloud/router'
import axios from "@nextcloud/axios";
import { showError, showSuccess } from '@nextcloud/dialogs'
import Close from 'vue-material-design-icons/Close.vue'
import ArrowRight from 'vue-material-design-icons/ArrowRight.vue'
import Delete from 'vue-material-design-icons/Delete.vue'

export default {
    name: 'NewWork',
    components: {
        NcDatetimePicker,
        NcMultiselect,
        NcListItemIcon,
        NcButton,
        Close,
        ArrowRight,
        Delete
    },
    data() {
        return {
            work: {
                work_name: '',
                description: '',
                start_date: null,
                end_date: null,
                assigned_to: null,
                label: ''
            },
            users: [],
            labels: [
                { text: 'Gấp' },
                { text: 'Quan trọng' },
                { text: 'Bình thường' }
            ],
            taskContent: '',
            tasks: [],
        }
    },

    computed: {
        formatUsers() {
            const usersArray = Object.values(this.users);
            return usersArray.map(user => {
                return {
                    userId: user.qlcb_uid,
                    subtitle: user.full_name,
                    icon: 'icon-user'
                };
            });
        },

        receivedProjectID() {
            return this.$store.state.sharedProjectID;
        },
    },

    mounted() {
        this.getUsers()
        this.scrollToBottom();
        // this.attachBlurListener(this.$refs.qlcb_uid, 'qlcb_uid');
        // this.attachBlurListener(this.$refs.gender, 'gender');
        // this.attachBlurListener(this.$refs.date_of_birth, 'date_of_birth');
    },

    methods: {
        async getUsers() {
            try {
                const response = await axios.get(generateUrl('/apps/kmaqlcv/users'));
                this.users = response.data.users

            } catch (e) {
                console.error(e)
            }
        },

        resetTaskField() {
            this.taskContent = ''
        },

        addToTaskField() {
            this.tasks.push(this.taskContent)
            this.taskContent = ''
            this.scrollToBottom();
        },

        deleteTask(index) {
            this.tasks.splice(index, 1)
            this.scrollToBottom();
        },
        scrollToBottom() {
            this.$nextTick(() => {
                const list = this.$el.querySelector('.scrollable-list');
                if (list) {
                    list.scrollTop = list.scrollHeight;
                }
            });
        },

        mysqlDateFormatter(date) {
            if (!date) return '';
            const year = date.getFullYear();
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const day = date.getDate().toString().padStart(2, '0');
            return `${year}-${month}-${day}`;
        },

        async createWork() {
            try {
                const response = await axios.post(generateUrl('/apps/kmaqlcv/create_work'), {
                    project_id: this.receivedProjectID,
                    work_name: this.work.work_name,
                    description: this.work.description,
                    start_date: this.mysqlDateFormatter(this.work.start_date),
                    end_date: this.mysqlDateFormatter(this.work.end_date),
                    label: this.work.label.text,
                    assigned_to: this.work.assigned_to.userId,
                    contents: this.tasks
                });

                showSuccess("Tạo thành công.")
                this.cancel()
            } catch (error) {
                console.error("Lỗi khi tạo công việc: ", error);
                showError("Có lỗi xảy ra khi tạo công việc.");
            }
        },

        cancel() {
         this.$router.push(`/project/${this.receivedProjectID}`);
     },
    }
}
</script>

<style scoped>
.new-work .grid-view-a {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    height: 90%;
}

.new-work .grid-item {
    padding: 20px;
    border: 1px solid gray;
    border-radius: 20px;
    justify-content: space-between;
}

.new-work .grid-view-b {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-row-gap: 20px;
    grid-column-gap: 20px;
}

.grid-item-b {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.title {
    text-align: center;
    padding-bottom: 20px;
}

.task-title {
    text-align: center;
}

.full-width {
    grid-column: 1 / -1;
}

.new-work {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90%;
    height: 70%;
}

input {
    width: 100%
}

input {
    height: 44px !important
}

.description {
    height: 88px !important;
    width: 100%;
    resize: none;
}

::v-deep .mx-input {
    height: 44px !important;
}

::v-deep .nc-picker {
    width: 100% !important;
}

::v-deep .multiselect__tags {
    border: 2px solid #949494 !important;
}

::v-deep .multiselect__tags:hover {
    border-color: #3287b5 !important;
}

::v-deep .mx-input {
    height: 44px !important;
}

::v-deep .multiselect {
    min-width: auto !important;
    width: 100% !important;
}

.combo-actions {
    width: 100%;
    display: flex;
    justify-content: flex-end;
    gap: 20px;
    margin-top: 20px;
}

.scrollable-list {
    overflow-y: auto;
    max-height: 295px;
    height: 295px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    padding: 10px;
    word-wrap: break-word;
}

.task-field {
    display: flex;
    align-items: center;
    gap: 10px;
}

.inline-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.task-content {
    width: 100%;
    overflow-wrap: break-word;
    word-break: normal;
    white-space: normal;
    background-color: #7fc0e4;
    border-radius: 8px;
    padding: 8px;
    margin-bottom: 8px;

    box-sizing: border-box;
}

.task-number {
    width: 30px;
    height: 30px;
    display: flex;
    justify-content: center;
    box-sizing: border-box;
}

.list-item .close-icon:hover {
    background-color: #e6e6e6;
    border-radius: 50%;
    transition: background-color 0.3s ease;
}

.list-item .close-icon {
    padding: 10px;
    box-sizing: border-box;
}
</style>