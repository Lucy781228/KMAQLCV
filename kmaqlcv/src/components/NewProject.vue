<template>
    <NcModal v-if="modal" :canClose="full_name">
        <div v-if="full_name" class="modal__content">
            <div class="grid-item full-width">
                <h2 class="modal-title">{{ projectName }}</h2>
            </div>

            <div class="grid-item project-name">
                <label>Người giao việc</label>
                <input type="text" v-model="full_name" class="input-disabled" :disabled="true" />
            </div>

            <div class="grid-item">
                <label>Ngày bắt đầu</label>
                <input type="text" v-model="getStartDate" class="input-disabled" :disabled="true" />
            </div>

            <div class="grid-item">
                <label>Ngày kết thúc</label>
                <input type="text" v-model="getEndDate" class="input-disabled" :disabled="true" />
            </div>
        </div>
        <div v-else class="modal__content">
            <div class="grid-item full-width">
                <h2 class="modal-title">{{ getTitle }}</h2>
            </div>
            <div class="grid-item">
                <label>Ngày bắt đầu (*)</label>
                <NcDatetimePicker v-if="isEdit" ref="start_date" format="DD/MM/YYYY" class="nc-picker"
                    v-model="formatStartDate" />
                <NcDatetimePicker v-else ref="start_date" format="DD/MM/YYYY" class="nc-picker" v-model="startDate" />
                <div class="validation-error-container">
                    <span class="validation-error"
                        v-if="!isEdit && touchedFields.start_date && !validation.requiredObject(startDate)">
                        Không được để trống
                    </span>
                    <span class="validation-error" v-if="isEdit && !validation.requiredObject(formatStartDate)">
                        Không được để trống
                    </span>
                </div>
            </div>

            <div class="grid-item">
                <label>Ngày kết thúc (*)</label>
                <NcDatetimePicker v-if="isEdit" ref="end_date" format="DD/MM/YYYY" class="nc-picker"
                    v-model="formatEndDate" />
                <NcDatetimePicker v-else ref="end_date" format="DD/MM/YYYY" class="nc-picker" v-model="endDate"/>
                <div class="validation-error-container">
                    <span class="validation-error"
                        v-if="!isEdit && touchedFields.end_date && !validation.requiredObject(endDate)">
                        Không được để trống
                    </span>
                    <span class="validation-error" v-if="isEdit && !validation.requiredObject(formatEndDate)">
                        Không được để trống
                    </span>
                    <span class="validation-error" v-if="!isValidDate">
                        Ngày kết thúc phải sau ngày bắt đầu
                    </span>
                </div>
            </div>

            <div class="grid-item project-name">
                <label>Tên dự án (*)</label>
                <input type="text" v-model="projectName" @blur="handleFieldFocus('project_name')" />
                <div class="validation-error-container">
                    <span class="validation-error"
                        v-if="!isEdit && touchedFields.project_name && !validation.requiredString(projectName)">
                        Không được để trống
                    </span>
                    <span class="validation-error" v-if="isEdit && !validation.requiredString(projectName)">
                        Không được để trống
                    </span>
                </div>
            </div>

            <div class="combo-actions">
                <NcButton type="secondary" @click="closeModal">
                    Hủy
                </NcButton>
                <NcButton v-if="isEdit" @click="updateProject" type="primary" :disabled="!isFormValid">
                    Cập nhật
                </NcButton>
                <NcButton v-else @click="createProject" type="primary" :disabled="!isFormValid">
                    Thêm
                </NcButton>
            </div>
        </div>
    </NcModal>
</template>

<script>
import axios from "@nextcloud/axios";
import { generateUrl } from '@nextcloud/router'
import { NcButton, NcModal, NcDatetimePicker } from "@nextcloud/vue";
import { showError, showSuccess } from '@nextcloud/dialogs'
import validation from '../validate.js';
import { getCurrentUser } from '@nextcloud/auth'

export default {
    name: 'NewProject',
    components: {
        NcButton,
        NcModal,
        NcDatetimePicker
    },
    props: {
        modal: {
            type: Boolean,
            required: true
        },
        projectId: {
            type: String,
            default: ""
        },
        startDate: {
            type: String,
            default: null
        },
        endDate: {
            type: String,
            default: null
        },
        projectName: {
            type: String,
            default: ""
        },
        isEdit: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            touchedFields: {
                start_date: false,
                end_date: false,
                project_name: false,
            },
            formatStartDate: null,
            formatEndDate: null,
            isValidDate: true,
            user: getCurrentUser(),
            full_name: null,
            isView: false
        };
    },

    watch: {
        modal: {
            handler(newVal, oldVal) {
                if (newVal != oldVal) {
                    if (this.receivedUserID != this.user.uid) {
                        this.getFullName()
                    }
                    if (newVal) {
                        this.$nextTick(() => {
                            this.attachBlurListener(this.$refs.start_date, 'start_date');
                            this.attachBlurListener(this.$refs.end_date, 'end_date');
                        });
                        this.formatEndDate = this.endDate ? new Date(this.endDate + '') : null
                        this.formatStartDate = this.startDate ? new Date(this.startDate + '') : null
                        console.log(this.formatStartDate)
                        console.log(this.formatEndDate)
                    }
                }
            },
            immediate: true
        },

        endDate(newVal) {
            if (newVal) {
                if (this.startDate != null) {
                    this.isValidDate = this.endDate > this.startDate
                }
            }
        },

        formatEndDate(newVal) {
            if (newVal) {
                if (this.formatStartDate != null) {
                    this.isValidDate = this.formatEndDate > this.formatStartDate
                }
            }
        },

        startDate(newVal) {
            if (newVal) {
                if (this.endDate != null) {
                    this.isValidDate = this.endDate > this.startDate
                }
            }
        },

        formatStartDate(newVal) {
            if (newVal) {
                if (this.formatEndDate != null) {
                    this.isValidDate = this.formatEndDate > this.formatStartDate
                }
            }
        }
    },

    mounted() {
    },

    computed: {
        receivedUserID() {
            return this.$store.state.sharedProjectOwner;
        },

        getTitle() {
            return this.isEdit ? 'CẬP NHẬT DỰ ÁN' : 'THÊM DỰ ÁN'
        },
        validation() {
            return validation;
        },

        isFormValid() {
            return this.isValidDate &&
                this.validation.requiredObject(this.isEdit ? this.formatStartDate : this.startDate) &&
                this.validation.requiredObject(this.isEdit ? this.formatEndDate : this.endDate) &&
                this.validation.requiredString(this.projectName)
        },

        getStartDate() {
            return this.formatDateToDDMMYYYY(this.startDate)
        },

        getEndDate() {
            return this.formatDateToDDMMYYYY(this.endDate)
        },
    },

    methods: {
        closeModal() {
            this.touchedFields.end_date = false
            this.touchedFields.start_date = false
            this.touchedFields.project_name = false
            this.isValidDate = true
            this.$emit('close');
        },

        handleFieldFocus(fieldName) {
            this.touchedFields[fieldName] = true;
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

        mysqlDateFormatter(date) {
            if (!date) return '';
            const year = date.getFullYear();
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const day = date.getDate().toString().padStart(2, '0');
            return `${year}-${month}-${day}`;
        },

        async createProject() {
            try {
                const response = await axios.post('/apps/kmaqlcv/create_project', {
                    start_date: this.mysqlDateFormatter(this.startDate),
                    end_date: this.mysqlDateFormatter(this.endDate),
                    project_name: this.projectName,
                    user_id: this.user.uid,
                });
                showSuccess(t('kmaqlcv', 'Thêm thành công'));
                this.closeModal()
            } catch (e) {
                console.error(e)
            }
        },

        async updateProject() {
            try {
                const response = await axios.put('/apps/kmaqlcv/update_project', {
                    start_date: this.mysqlDateFormatter(this.formatStartDate),
                    end_date: this.mysqlDateFormatter(this.formatEndDate),
                    project_name: this.projectName,
                    user_id: this.user.uid,
                    project_id: this.projectId
                });
                showSuccess(t('kmaqlcv', 'Cập nhật thành công'));
                this.closeModal()
            } catch (e) {
                console.error(e)
            }
        },

        async getFullName() {
            if (this.receivedUserID) {
                try {
                    const response = await axios.get(generateUrl(`/apps/kmaqlcv/full_name/${this.receivedUserID}`))
                    this.full_name = response.data.full_name.full_name
                    console.log(this.full_name)

                } catch (e) {
                    console.error(e)
                }
            }
        },

        formatDateToDDMMYYYY(inputDate) {
            if (!inputDate) return 'Không';
            const parts = inputDate.split('-');
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        },
    }
};
</script>

<style scoped>
.table-actions {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 10px;
}

.modal__content {
    margin: 30px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    height: 400px
}

.validation-error-container {
    height: 10px;
}

.validation-error {
    color: red;
    font-size: 0.8em;
}

.grid-item input,
.grid-item .nc-datetime-picker,
.grid-item .nc-multiselect {
    width: 100%;
}

::v-deep .nc-picker {
    width: 100% !important;
}

input {
    height: 44px !important
}

::v-deep .mx-input {
    height: 44px !important;
}

.full-width {
    grid-column: 1 / -1;
    text-align: center;
}

.combo-actions {
    grid-column: 1 / -1;
    display: flex;
    justify-content: flex-end;
    gap: 20px;
    margin-top: 20px;
}

.project-name {
    grid-column: 1 / -1;
}

.input-disabled {
    border-color: gray;
    color: gray;
    cursor: not-allowed
}
</style>