<template>
    <NcModal v-if="modal" :canClose="full_name" @close="closeModal" size="small">
        <div v-if="full_name" class="modal__content">
            <div class="modal-title">{{ projectName }}</div>
            <div class="grid-view">
                <div class="grid-item">
                    <label>Người giao việc</label>
                    <input type="text" v-model="full_name" class="input-disabled" :disabled="true" />
                </div>
                <div class="grid-item">
                    <label>Mô tả</label>
                    <textarea class="description" type="text" v-model="description" :disabled="true"> </textarea>
                </div>
            </div>

        </div>
        <div v-else class="modal__content">
            <div class="modal-title">{{ getTitle }}</div>
            <div class="grid-view">
                <div class="grid-item">
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
                <div class="grid-item">
                    <label>Mô tả</label>
                    <textarea class="description" type="text" v-model="description"> </textarea>
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
        projectName: {
            type: String,
            default: ""
        },
        isEdit: {
            type: Boolean,
            default: false
        },
        status: {
            type: Number,
            default: 0
        },
        description: {
            type: String,
            default: ""
        },
    },
    data() {
        return {
            touchedFields: {
                project_name: false,
            },
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
                }
            },
            immediate: true
        },
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
                this.validation.requiredString(this.projectName)
        },
    },

    methods: {
        closeModal() {
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
                const currentDate = new Date();
                const response = await axios.post('/apps/qlcv/create_project', {
                    project_name: this.projectName,
                    status: this.startDate <= currentDate ? 1 : 0,
                    description: this.description
                });
                showSuccess(t('qlcv', 'Thêm thành công'));
                this.closeModal()
            } catch (e) {
                console.error(e)
            }
        },

        async updateProject() {
            try {
                const response = await axios.put('/apps/qlcv/update_project', {
                    project_name: this.projectName,
                    project_id: this.projectId,
                    status: null,
                    description: this.description
                });
                showSuccess(t('qlcv', 'Cập nhật thành công'));
                this.closeModal()
            } catch (e) {
                console.error(e)
            }
        },

        async getFullName() {
            if (this.receivedUserID) {
                try {
                    const response = await axios.get(generateUrl(`/apps/qlcv/full_name/${this.receivedUserID}`))
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

.description {
    height: 176px !important;
    width: 100%;
    resize: none;
}

.modal__content {
    margin: 30px;
}

.modal-title {
    padding-bottom: 40px;
    text-align: center;
    font-weight: bold;
    font-size: 24px;
}

.grid-view {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
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


input {
    height: 44px !important
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

.input-disabled {
    border-color: gray;
    color: gray;
    cursor: not-allowed
}
</style>