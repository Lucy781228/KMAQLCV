<template>
    <div class="new-work">
        <div class="title">
            <h2>THÊM CÔNG VIỆC</h2>
            <div class="error">Lưu ý: Chỉ có thể thay đổi danh sách tác vụ với công việc cần làm.</div>
        </div>
        <div class="grid-view-a">
            <div class="grid-item grid-view-b">
                <div class="grid-item-b full-width">
                    <label>Tên công việc (*)</label>
                    <input type="text" v-model="work.work_name" @blur="handleFieldFocus('work_name')" />
                    <div class="validation-error-container">
                        <span class="validation-error"
                            v-if="touchedFields.work_name && !validation.requiredString(work.work_name)">
                            {{ validationMessages['required'] }}
                        </span>
                    </div>
                </div>
                <div class="grid-item-b">
                    <label>Nhãn</label>
                    <NcMultiselect ref="label" class="nc-select" v-model="work.label" :options="labels" label="text"
                        placeholder="Chọn một tùy chọn" track-by="text" />
                </div>
                <div class="grid-item-b">
                    <label>Người nhận việc (*)</label>
                    <NcMultiselect ref="assigned_to" class="nc-select" v-model="work.assigned_to" :options="formatUsers"
                        placeholder="Chọn một tùy chọn" label="userId" track-by="userId" :user-select="true">
                        <template #singleLabel="{ option }">
                            <NcListItemIcon v-bind="option" :title="option.userId" :avatar-size="24"
                                :no-margin="true" />
                        </template>
                    </NcMultiselect>
                    <div class="validation-error-container">
                        <span class="validation-error"
                            v-if="touchedFields.assigned_to && !validation.requiredObject(work.assigned_to)">
                            {{ validationMessages['required'] }}
                        </span>
                    </div>
                </div>
                <div class="grid-item-b">
                    <label>Ngày bắt đầu (*)</label>
                    <NcDatetimePicker id="start_date" ref="start_date" format="DD/MM/YYYY" class="nc-picker"
                        placeholder="Chọn một ngày" :clearable="true" v-model="work.start_date" />
                    <div class="validation-error-container">
                        <span class="validation-error"
                            v-if="touchedFields.start_date && !validation.requiredObject(work.start_date)">
                            {{ validationMessages['required'] }}
                        </span>
                    </div>
                </div>
                <div class="grid-item-b">
                    <label>Ngày kết thúc (*)</label>
                    <NcDatetimePicker id="end_date" ref="end_date" format="DD/MM/YYYY" class="nc-picker"
                        placeholder="Chọn một ngày" v-model="work.end_date" :clearable="true" />
                    <div class="validation-error-container">
                        <span class="validation-error"
                            v-if="touchedFields.end_date && !validation.requiredObject(work.end_date)">
                            {{ validationMessages['required'] }}
                        </span>
                        <span class="validation-error" v-if="!isValidEndDate">
                            Ngày kết thúc phải sau ngày hiện tại
                        </span>
                        <span class="validation-error" v-else-if="!isValidDate">
                            Ngày kết thúc phải sau ngày bắt đầu
                        </span>
                    </div>
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
                    <NcButton type="primary" :disabled="taskContent.trim() === ''" @click="addToTaskField"
                        ariaLabel="A">
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
            <NcButton @click="createWork" type="primary" ariaLabel="A" :disabled="!isFormValid">
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
import validation from '../validate.js';
import { getCurrentUser } from '@nextcloud/auth'

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
            isValidDate: true,
            isValidEndDate: true,
            project: null,
            touchedFields: {
                work_name: false,
                start_date: false,
                end_date: false,
                assigned_to: false
            },
            work: {
                work_name: '',
                description: '',
                start_date: null,
                end_date: null,
                assigned_to: null,
                label: { text: 'Trung bình' }
            },
            users: [],
            labels: [
                { text: 'Cao' },
                { text: 'Trung bình' },
                { text: 'Thấp' }
            ],
            taskContent: '',
            tasks: [],
            validationMessages: {
                'required': 'Không được để trống',
            },
            user: getCurrentUser(),
        }
    },

    watch: {
        work: {
            handler: function (newVal, oldVal) {
                this.isValidDate = this.validateDates()
                this.isValidEndDate = this.validateEndDate()
            },
            deep: true
        }
    },

    computed: {
        sharedProjectStatus() {
            return this.$store.state.sharedProjectStatus;
        },

        isFormValid() {
            return this.isValidDate && this.isValidEndDate &&
                this.validation.requiredObject(this.work.start_date) &&
                this.validation.requiredObject(this.work.end_date) &&
                this.validation.requiredObject(this.work.assigned_to) &&
                this.validation.requiredString(this.work.work_name) && this.tasks.length
        },

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

        validation() {
            return validation;
        },
    },

    async mounted() {
        this.getUsers()
        this.attachBlurListener(this.$refs.assigned_to, 'assigned_to');
        this.attachBlurListener(this.$refs.start_date, 'start_date');
        this.attachBlurListener(this.$refs.end_date, 'end_date');
    },

    methods: {
        validateDates() {
            if (this.work.start_date && this.work.end_date) {
                return this.work.start_date < this.work.end_date;
            }
            return true;
        },

        validateEndDate() {
            if (this.work.end_date) {
                return this.work.end_date > new Date().setHours(0, 0, 0, 0)
            }
            return true;
        },

        formatDateToDDMMYYYY(inputDate) {
            if (inputDate) {
                const parts = inputDate.split('-');
                return `${parts[2]}/${parts[1]}/${parts[0]}`;
            }
        },

        attachBlurListener(componentRef, fieldName) {
            if (componentRef && componentRef.$el) {
                const input = componentRef.$el.querySelector('input');
                if (input) {
                    input.addEventListener('blur', () => {
                        this.handleFieldFocus(fieldName);
                    });
                }
            }
        },

        handleFieldFocus(fieldName) {
            this.touchedFields[fieldName] = true;
        },
        async getUsers() {
            try {
                const response = await axios.get(generateUrl('/apps/qlcv/users'));
                this.users = response.data.users

            } catch (e) {
                console.error(e)
            }
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
                const status = this.work.start_date <= new Date().setHours(0, 0, 0, 0) ? 1 : 0
                const response = await axios.post(generateUrl('/apps/qlcv/create_work'), {
                    project_id: this.receivedProjectID,
                    work_name: this.work.work_name,
                    description: this.work.description,
                    start_date: this.mysqlDateFormatter(this.work.start_date),
                    end_date: this.mysqlDateFormatter(this.work.end_date),
                    label: this.work.label.text,
                    assigned_to: this.work.assigned_to.userId,
                    contents: this.tasks,
                    owner: this.user.uid,
                    status: status
                });

                if (status == 1 && this.sharedProjectStatus == 0) this.updateProject()

                showSuccess("Tạo thành công.")
                this.cancel()
            } catch (error) {
                console.error("Lỗi khi tạo công việc: ", error);
                showError("Có lỗi xảy ra khi tạo công việc.");
            }
        },

        async updateProject() {
            try {
                const response = await axios.put('/apps/qlcv/update_project', {
                    project_name: null,
                    description: null,
                    user_id: null,
                    project_id: this.receivedProjectID,
                    status: 1
                });
                this.$store.commit('updateProjectStatus', 1)
            } catch (e) {
                console.error(e)
            }
        },

        cancel() {
            this.$emit('back-to-worklist');
            console.log('from workmenu')
            this.$router.push({ name: 'project', params: { receivedProjectID: this.$route.params.sharedProjectID } });
        },
    }
}
</script>

<style scoped>
.new-work .grid-view-a {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    height: 80%;
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
    grid-row-gap: 10px;
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
    height: 75%;
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

.validation-error {
    color: red;
    font-size: 0.8em;
}

.validation-error-container {
    height: 10px;
}

.error {
    color: red;
    font-size: 1.2em;
}
</style>