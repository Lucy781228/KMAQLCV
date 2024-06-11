<template>
    <div class="details">
        <div class="combo-action">
            <NcButton type="tertiary" @click="startEditting" v-if="isOwner && !isEdit && status != 2 && status != 3"
                aria-label="Example text">
                <template #icon>
                    <Pencil :size="20" />
                </template>
            </NcButton>
            <NcButton type="tertiary" @click="cancelEditting" v-if="isOwner && isEdit && status != 2 && status != 3"
                aria-label="Example text">
                <template #icon>
                    <Close :size="20" />
                </template>
            </NcButton>
            <NcButton type="primary" @click="updateWork" v-if="isOwner && isEdit && status != 2 && status != 3"
                :disabled="!isFormValid" aria-label="Example text">
                <template #icon>
                    <Check :size="20" />
                </template>
            </NcButton>
        </div>
        <div class="grid-view" v-if="work">
            <div class="grid-item full-width">
                <label>Tên công việc (*)</label>
                <input v-if="isEdit && isOwner" type="text" v-model="work.work_name" />
                <input v-else type="text" v-model="initialWork.work_name" class="input-disabled" :disabled="true" />
                <div class="validation-error-container">
                    <span class="validation-error" v-if="!validation.requiredString(work.work_name)">
                        {{ validationMessages['required'] }}
                    </span>
                </div>
            </div>
            <div class="grid-item">
                <label>Nhãn</label>
                <NcMultiselect v-if="isEdit && isOwner" ref="label" class="nc-select" v-model="selectedLabel"
                    :options="labels" label="text" track-by="text" />
                <input v-else type="text" v-model="initialWork.label" class="input-disabled" :disabled="true" />
                <div class="validation-error-container">
                </div>
            </div>
            <div class="grid-item">
                <label>Người nhận việc (*)</label>
                <NcMultiselect v-if="isEdit && status == 0 && isOwner" class="nc-select" v-model="selectedUser"
                    :options="formatUsers" placeholder="Chọn một tùy chọn" label="userId" track-by="userId"
                    :user-select="true">
                    <template #singleLabel="{ option }">
                        <NcListItemIcon v-bind="option" :title="option.userId" :avatar-size="24" :no-margin="true" />
                    </template>
                </NcMultiselect>
                <input v-else type="text" v-model="getName" class="input-disabled" :disabled="true" />
            </div>
            <div class="grid-item">
                <label>Ngày bắt đầu (*)</label>
                <NcDatetimePicker v-if="isEdit && status == 0 && isOwner" ref="start_date" format="DD/MM/YYYY"
                    class="nc-picker" v-model="startDate" />
                <input v-else type="text" v-model="getStartDate" class="input-disabled" :disabled="true" />
                <div class="validation-error-container">
                    <span class="validation-error" v-if="!validation.requiredObject(startDate)">
                        {{ validationMessages['required'] }}
                    </span>
                </div>
            </div>
            <div class="grid-item">
                <label>Ngày kết thúc (*)</label>
                <NcDatetimePicker v-if="isEdit && isOwner" ref="end_date" format="DD/MM/YYYY" class="nc-picker"
                    v-model="endDate" />
                <input v-else type="text" v-model="getEndDate" class="input-disabled" :disabled="true" />
                <div class="validation-error-container">
                    <span class="validation-error" v-if="!validation.requiredObject(endDate)">
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
            <div class="grid-item full-width">
                <label>Mô tả</label>
                <textarea v-if="isEdit && isOwner" class="description" type="text"
                    v-model="work.description"> </textarea>
                <textarea v-else class="description input-disabled" type="text" v-model="initialWork.description"
                    :disabled="true"> </textarea>
            </div>
        </div>
    </div>
</template>

<script>
import { NcMultiselect, NcDatetimePicker, NcListItemIcon, NcButton, NcTextField } from "@nextcloud/vue";
import { generateUrl } from '@nextcloud/router'
import axios from "@nextcloud/axios";
import { showError, showSuccess } from '@nextcloud/dialogs'
import Close from 'vue-material-design-icons/Close.vue'
import Check from 'vue-material-design-icons/Check.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import validation from '../../validate.js';

export default {
    name: 'Details',
    components: {
        NcDatetimePicker,
        NcMultiselect,
        NcListItemIcon,
        NcButton,
        Close,
        Check,
        Pencil
    },

    props: {
        workId: {
            type: Number,
            required: true
        },

        isOwner: {
            type: Boolean,
            required: true
        },

        status: {
            type: Number,
            required: true
        },
    },
    data() {
        return {
            work: null,
            labels: [
                { text: 'Cao' },
                { text: 'Trung bình' },
                { text: 'Thấp' }
            ],
            isEdit: false,
            initialWork: null,
            full_name: '',
            startDate: null,
            endDate: null,
            selectedLabel: null,
            selectedUser: null,
            users: [],
            validationMessages: {
                'required': 'Không được để trống',
                'start_date': null,
            },
        }
    },

    computed: {
        receivedProjectID() {
            return this.$store.state.sharedProjectID;
        },

        getStartDate() {
            return this.formatDateToDDMMYYYY(this.work.start_date)
        },

        getEndDate() {
            return this.formatDateToDDMMYYYY(this.work.end_date)
        },

        getName() {
            return `${this.initialWork.assigned_to} - ${this.full_name}`
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

        validation() {
            return validation;
        },

        isValidDate() {
            if (this.startDate && this.endDate) {
                return this.startDate < this.endDate;
            }
            return true;
        },

        isValidEndDate() {
            if (this.endDate) {
                return this.endDate > new Date().setHours(0, 0, 0, 0)
            }
            return true;
        },

        isFormValid() {
            return this.isValidDate && this.isValidEndDate &&
                this.validation.requiredObject(this.startDate) &&
                this.validation.requiredObject(this.endDate) &&
                this.validation.requiredString(this.work.work_name)
        },
    },

    mounted() {
        this.getUsers().then(() => {
            this.getWork();
        });
    },

    methods: {
        startEditting() {
            this.isEdit = true
            this.editWork = this.work
            this.selectedLabel = this.labels.find(label => label.text === this.initialWork.label);
        },

        cancelEditting() {
            this.isEdit = false
            this.work = JSON.parse(JSON.stringify(this.initialWork));
        },

        formatDateToDDMMYYYY(inputDate) {
            if (!inputDate) return 'Không';
            const parts = inputDate.split('-');
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        },

        async getWork() {
            try {
                const response = await axios.get(generateUrl(`/apps/qlcv/work_by_id/${this.workId}`));
                this.work = response.data.work
                this.initialWork = JSON.parse(JSON.stringify(this.work));
                this.getFullName()
                this.startDate = new Date(this.work.start_date + '')
                this.endDate = new Date(this.work.end_date + '')
                this.setSelectedUser();
            } catch (e) {
                console.error(e)
            }
        },

        setSelectedUser() {
            if (this.users && this.initialWork) {
                const user = this.formatUsers.find(user => user.userId === this.initialWork.assigned_to);
                if (user) {
                    this.selectedUser = user;
                }
            }
        },

        mysqlDateFormatter(date) {
            if (!date) return '';
            const year = date.getFullYear();
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const day = date.getDate().toString().padStart(2, '0');
            return `${year}-${month}-${day}`;
        },

        async updateWork() {
            try {
                const response = await axios.put('/apps/qlcv/update_work', {
                    work_name: this.initialWork.work_name  === this.work.work_name ? null : this.work.work_name,
                    description: this.initialWork.description  === this.work.description ? null : this.work.description,
                    start_date: this.initialWork.start_date  === this.mysqlDateFormatter(this.startDate) ? null : this.mysqlDateFormatter(this.startDate),
                    end_date: this.initialWork.end_date  === this.mysqlDateFormatter(this.endDate) ? null : this.mysqlDateFormatter(this.endDate),
                    label: this.selectedLabel && (this.initialWork.label !== this.selectedLabel.text) ? this.selectedLabel.text : null,
                    assigned_to: this.selectedUser.userId,
                    status: null,
                    work_id: this.work.work_id,
                    project_id: this.receivedProjectID
                });
                this.isEdit = false
                await this.getWork();
                showSuccess("Cập nhật thành công.")
                this.cancelEditting()
            } catch (error) {
                console.error("Lỗi khi tạo công việc: ", error);
            }
        },

        async getFullName() {
            try {
                const response = await axios.get(generateUrl(`/apps/qlcv/full_name/${this.initialWork.assigned_to}`))
                this.full_name = response.data.full_name.full_name

            } catch (e) {
                console.error(e)
            }
        },

        async getUsers() {
            try {
                const response = await axios.get(generateUrl('/apps/qlcv/users'));
                this.users = response.data.users

            } catch (e) {
                console.error(e)
            }
        },
    }
}
</script>

<style scoped>
.details {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    height: 500px;
}

.combo-action {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    width: 100%;
    max-width: 800px;
    height: 44px
}

.grid-view {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    width: 100%;
    max-width: 800px;
}

.grid-item {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.full-width {
    grid-column: 1 / -1;
}

input {
    width: 100%;
    height: 44px !important;
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

.input-disabled {
    border-color: gray;
    color: gray;
    cursor: not-allowed;
}

.validation-error {
    color: red;
    font-size: 0.8em;
}

.validation-error-container {
    height: 10px;
}
</style>