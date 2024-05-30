<template>
<div class="details">
    <div class="combo-action" v-show="isProjectOwner">
        <NcButton type="tertiary" @click="startEditting" v-if="!isEdit" aria-label="Example text">
            <template #icon>
                <Pencil :size="20" />
            </template>
        </NcButton>
        <NcButton type="tertiary" @click="cancelEditting" v-if="isEdit" aria-label="Example text">
            <template #icon>
                <Close :size="20" />
            </template>
        </NcButton>
        <NcButton type="primary" @click="updateWork" v-if="isEdit" aria-label="Example text">
            <template #icon>
                <Check :size="20" />
            </template>
        </NcButton>
    </div>
    <div class="grid-view" v-if="work">
        <div class="grid-item full-width">
            <label>Tên công việc (*)</label>
            <input v-if="isEdit" type="text" v-model="work.work_name" />
            <input v-else type="text" v-model="initialWork.work_name" class="input-disabled" :disabled="true"/>
        </div>
        <div class="grid-item">
            <label>Nhãn</label>
            <NcMultiselect v-if="isEdit" ref="label" class="nc-select" v-model="selectedLabel" :options="labels" label="text"
                track-by="text" />
                <input v-else type="text" v-model="initialWork.label" class="input-disabled" :disabled="true"/>
        </div>
        <div class="grid-item">
            <label>Người nhận việc (*)</label>
            <NcMultiselect v-if="isEdit" ref="assigned_to" class="nc-select" v-model="selectedUser" :options="formatUsers"
                label="userId" track-by="userId" :user-select="true">
                <template #singleLabel="{ option }">
                    <NcListItemIcon v-bind="option" :title="option.userId" :avatar-size="24" :no-margin="true" />
                </template>
            </NcMultiselect>
            <input v-else type="text" v-model="getName" class="input-disabled" :disabled="true"/>
        </div>
        <div class="grid-item">
            <label>Ngày bắt đầu (*)</label>
            <NcDatetimePicker v-if="isEdit" ref="start_date" format="DD/MM/YYYY" class="nc-picker" v-model="startDate" />
            <input v-else type="text" v-model="getStartDate" class="input-disabled" :disabled="true"/>
        </div>
        <div class="grid-item">
            <label>Ngày kết thúc (*)</label>
            <NcDatetimePicker v-if="isEdit" ref="end_date" format="DD/MM/YYYY" class="nc-picker" v-model="endDate" />
            <input v-else type="text" v-model="getEndDate" class="input-disabled" :disabled="true"/>
        </div>
        <div class="grid-item full-width">
            <label>Mô tả</label>
            <textarea v-if="isEdit" class="description" type="text" v-model="work.description"> </textarea>
            <textarea v-else class="description input-disabled" type="text" v-model="initialWork.description" :disabled="true" > </textarea>
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

        isProjectOwner: {
            type: Boolean,
            required: true
        }
    },
    data() {
        return {
            work: null,
            users: [],
            labels: [
                { text: 'Gấp' },
                { text: 'Quan trọng' },
                { text: 'Bình thường' }
            ],
            isEdit: false,
            initialWork: null,
            full_name: '',
            startDate: null,
            endDate: null,
            selectedUser: null,
            selectedLabel: null
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

        getStartDate() {
            return this.formatDateToDDMMYYYY(this.work.start_date)
        },

        getEndDate() {
            return this.formatDateToDDMMYYYY(this.work.end_date)
        },

        getName() {
            return `${this.initialWork.assigned_to} - ${this.full_name}`
        }
    },

    mounted() {
        this.getUsers()
        this.getWork()
        // this.getFullName()
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

        async getUsers() {
            try {
                const response = await axios.get(generateUrl('/apps/kmaqlcv/users'));
                this.users = response.data.users

            } catch (e) {
                console.error(e)
            }
        },

        async getWork() {
            try {
                const response = await axios.get(generateUrl(`/apps/kmaqlcv/work_by_id/${this.workId}`));
                this.work = response.data.work
                this.initialWork = JSON.parse(JSON.stringify(this.work));
                this.getFullName()
                this.startDate = new Date(this.work.start_date + '')
                this.endDate = new Date(this.work.end_date + '')
                this.selectedUser =
                {
                    userId: this.work.assigned_to,
                    subtitle: 'full_name',
                    icon: 'icon-user'
                }
            } catch (e) {
                console.error(e)
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
                const response = await axios.put('/apps/kmaqlcv/update_work', {
                    work_name: this.work.work_name,
                    description: this.work.description,
                    start_date: this.mysqlDateFormatter(this.startDate),
                    end_date: this.mysqlDateFormatter(this.endDate),
                    label: this.selectedLabel.text,
                    assigned_to: this.selectedUser.userId,
                    work_id: this.work.work_id
                });
                this.isEdit = false
                await this.getWork();
                showSuccess("Cập nhật thành công.")
                this.cancel()
            } catch (error) {
                console.error("Lỗi khi tạo công việc: ", error);
            }
        },

        async getFullName() {
                try {
                    const response = await axios.get(generateUrl(`/apps/kmaqlcv/full_name/${this.initialWork.assigned_to}`))
                    this.full_name = response.data.full_name.full_name

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
    max-width: 800px; /* Đặt kích thước tối đa cho combo-action */
}

.grid-view {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    width: 100%;
    max-width: 800px; /* Đặt kích thước tối đa cho grid-view */
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
</style>