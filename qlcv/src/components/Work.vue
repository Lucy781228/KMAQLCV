<template>
    <div class="work-container" @click="handleClick">
        <div class="work-header">
            <div class="work-name">{{ workName }}</div>
            <div class="work-end-date">
                <CalendarBlankOutline v-if="endDate" :size="16" />{{ endDate }}
            </div>
        </div>
        <div class="work_body">
            <div v-if="label == null" class="work-label" :style="textStyle('a')">a</div>
            <div v-else class="work-label" :style="textStyle(label)">{{ label }}</div>
        </div>
        <div class="work-footer">
            <div class="footer-left" v-if="isDue">Quá hạn</div>
            <div class="footer-right">
                <NcAvatar :display-name="assignedTo" />
                <div @click.stop.prevent>
                    <NcActions v-if="isProjectOwner">
                        <NcActionButton type="tertiary" @click.stop.prevent="deleteWork" :closeAfterClick="true">
                            <template #icon>
                                <Delete :size="16" />Xóa
                            </template>
                        </NcActionButton>
                        <NcActionButton type="tertiary" @click.stop.prevent="updateStatus" v-if="status==2" :closeAfterClick="true">
                            <template #icon>
                                <Check :size="16" />Phê duyệt
                            </template>
                        </NcActionButton>
                        <NcActionButton type="tertiary" @click.stop.prevent="updateWork" v-if="status==2" :closeAfterClick="true">
                            <template #icon>
                                <Close :size="16" />Từ chối
                            </template>
                        </NcActionButton>
                    </NcActions>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Delete from 'vue-material-design-icons/Delete.vue'
import Check from 'vue-material-design-icons/Check.vue'
import Close from 'vue-material-design-icons/Close.vue'
import CalendarBlankOutline from 'vue-material-design-icons/CalendarBlankOutline.vue'
import { NcActions, NcActionButton, NcAvatar } from "@nextcloud/vue";
import { getCurrentUser } from '@nextcloud/auth'
import axios from "@nextcloud/axios";
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'

export default {
    name: 'Work',
    components: {
        CalendarBlankOutline,
        NcActionButton,
        Delete,
        NcAvatar,
        NcActions,
        Check,
        Close
    },

    data() {
        return {
            user: getCurrentUser(),
            newStatus: 1,
        };
    },

    props: {
        workId: {
            type: Number,
            required: true
        },
        workName: {
            type: String,
            required: true
        },
        endDate: {
            type: String,
            required: true
        },
        assignedTo: {
            type: String,
            required: true
        },
        label: {
            type: String,
            default: ""
        },
        isDue: {
            type: Boolean,
            default: false
        },
        isProjectOwner: {
            type: Boolean,
            required: true
        },
        status: {
            type: Number,
            required: true
        }
    },

    methods: {
        textStyle(text) {
            const styles = {
                'Gấp': {
                    backgroundColor: '#FF7A66',
                    color: 'black',
                    borderRadius: '10px',
                    padding: '2px 5px',
                },
                'Bình thường': {
                    backgroundColor: '#30CC7B',
                    color: 'white',
                    borderRadius: '10px',
                    padding: '2px 5px',
                },
                'Quan trọng': { 
                    backgroundColor: '#F1DB50',
                    color: 'black',
                    borderRadius: '10px',
                    padding: '2px 5px',
                },
                'a': {
                    visibility: 'hidden'
                }
            };
            return styles[text] || {};
        },

        handleClick() {
            this.$emit('click');
        },

        deleteWork(event) {
            event.stopPropagation();
            this.$emit('delete', this.workId)
        },

        updateStatus() {
            this.newStatus = 3
            this.updateWork()
        },

        async updateWork() {
            try {
                const response = await axios.put('/apps/qlcv/update_work', {
                    work_name: null,
                    description: null,
                    start_date: null,
                    end_date: null,
                    label: null,
                    assigned_to: null,
                    status: this.newStatus,
                    work_id: this.workId
                });
            this.$emit('update')
                showSuccess("Cập nhật thành công.")
            } catch (error) {
                console.error("Lỗi khi tạo công việc: ", error);
            }
        },
    },
};
</script>

<style scoped>
.work-container {
    border: 1px solid #ccc;
    border-radius: 10px;
    padding: 10px;
    height: 120px;
    width: 100%;
    transition: box-shadow 0.3s ease;
    cursor: pointer;
}

.work-container:hover {
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.work-header,
.work_body,
.work-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.work-name,
.work-end-date,
.work-label {
    font-size: 14px;
}

.work-end-date {
    display: flex;
    align-items: center;
    white-space: nowrap;
}

.footer-right {
    display: flex;
    align-items: center;
    margin-left: auto;
}

.footer-left {
    color: red;
    font-weight: bold;
    margin-right: auto;
}
</style>