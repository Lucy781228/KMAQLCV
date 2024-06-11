<template>
  <div class="tasks" v-if="status == 2 && isOwner">
    <div class="header">
      <h3>Chọn các tác vụ đạt</h3>
    </div>
    <div class="scrollable-list-b">
      <div class="task-list">
        <div v-for="task in tasks" :key="task.task_id" class="task-item">
          <input type="checkbox" :id="`task-${task.task_id}`" v-model="checkedTasks[task.task_id]" />
          <div class="task-content" :class="task.is_done ? 'task-done' : 'task-not-done'">
            <label :for="`task-${task.task_id}`">{{ task.content }}</label>
          </div>
        </div>
      </div>
    </div>
    <div class="combo-button">
      <NcButton @click="unselectAllTasks" type="secondary">
        Khôi phục
      </NcButton>
      <NcButton @click="selectAllTasks" type="secondary">
        Chọn tất cả
      </NcButton>
      <NcButton @click="approveSelectedTasks" type="primary">
        Phê duyệt
      </NcButton>
    </div>
  </div>
  <div class="tasks" v-else>
    <div class="header">
      <h3 v-if="status != 0">Tiến độ: {{ completedTasksCount }}/{{ tasks.length }}</h3>
      <NcButton :wide="true" aria-label="Example text" type="tertiary" @click="showNewTask" v-if="status == 0">
        <template #icon>
          <Plus :size="20" />
        </template>
      </NcButton>
    </div>
    <div class="scrollable-list">
      <div class="task-list">
        <div v-for="task in tasks" :key="task.task_id" class="task-item">
          <div v-if="editingTaskId !== task.task_id" style="width: 100%;" class="task-item-new">
            <input type="checkbox" :id="`task-${task.task_id}`" :checked="task.is_done" v-if="isAssigned && status == 1"
              @change="updateTask($event, task.task_id)" />
            <div class="task-content" :class="task.is_done ? 'task-done' : 'task-not-done'">
              <label :for="`task-${task.task_id}`">{{ task.content }}</label>
            </div>
            <NcActions class="task-actions" v-if="status == 0">
              <template #icon>
                <DotsHorizontal :size="16" />
              </template>
              <NcActionButton type="tertiary" @click="editTask(task.task_id, task.content)">
                <template #icon>
                  <Pencil :size="16" />
                </template>
                Chỉnh sửa
              </NcActionButton>
              <NcActionButton type="tertiary" @click="showModal(task.task_id)">
                <template #icon>
                  <Delete :size="16" />
                </template>
                Xóa
              </NcActionButton>
            </NcActions>
          </div>
          <div v-else class="task-item-new" style="width: 100%;">
            <input type="text" v-model="editContent">
            <NcButton type="tertiary" @click="cancelEditting" ariaLabel="A" class="button">
              <template #icon>
                <Close :size="20" />
              </template>
            </NcButton>
            <NcButton type="primary" :disabled="editContent.trim() == ''" @click="updateTaskContent(task.task_id)"
              ariaLabel="A">
              <template #icon>
                <ArrowRight :size="20" />
              </template>
            </NcButton>
          </div>
        </div>

        <div class="task-item-new" v-if="status == 0 && showAdd">
          <input type="text" v-model="content" placeholder="Thêm tác vụ" />
          <NcButton type="tertiary" @click="hideNewTask" ariaLabel="A" class="button">
            <template #icon>
              <Close :size="20" />
            </template>
          </NcButton>
          <NcButton type="primary" :disabled="content.trim() == ''" @click="createTask" ariaLabel="A">
            <template #icon>
              <ArrowRight :size="20" />
            </template>
          </NcButton>
        </div>
      </div>
    </div>
    <NcModal :show="isCompleted" :canClose="false" size="small" style="z-index: 11000;">
      <div class="modal__content">
        <div>Bạn đã hoàn thành tất cả tác vụ.
          Công việc hiện trong trạng thái chờ duyệt.</div>
        <div class="modal__actions">
          <NcButton @click="closeModal" type="primary">
            Thoát
          </NcButton>
        </div>
      </div>
    </NcModal>

    <NcModal :show="isDelete" :canClose="false" size="small" style="z-index: 11000;">
      <div class="modal__content">
        <h3>Bạn chắc chắn không?</h3>
        <div class="modal__actions">
          <NcButton @click="stopModal" type="primary" aria-label="Example text">
            Hủy
          </NcButton>
          <NcButton @click="deleteTask" type="secondary" aria-label="Example text">
            Xóa
          </NcButton>
        </div>
      </div>
    </NcModal>
  </div>
</template>

<script>
import { generateUrl } from '@nextcloud/router';
import axios from "@nextcloud/axios";
import Pencil from 'vue-material-design-icons/Pencil.vue';
import Delete from 'vue-material-design-icons/Delete.vue';
import Plus from 'vue-material-design-icons/Plus.vue';
import Alert from 'vue-material-design-icons/Alert.vue';
import Close from 'vue-material-design-icons/Close.vue';
import CardsVariant from 'vue-material-design-icons/CardsVariant.vue';
import ArrowRight from 'vue-material-design-icons/ArrowRight.vue';
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue';

import { NcButton, NcActionButton, NcActions, NcModal, NcEmptyContent } from "@nextcloud/vue";
import { showError, showSuccess } from '@nextcloud/dialogs';

export default {
  name: 'Task',
  components: {
    NcActionButton,
    NcActions,
    NcButton,
    Pencil,
    Delete,
    Plus,
    Close,
    ArrowRight,
    NcModal,
    NcEmptyContent,
    CardsVariant,
    Alert,
    DotsHorizontal
  },
  props: {
    workId: {
      type: Number,
      required: true
    },
    isAssigned: {
      type: Boolean,
      required: true
    },
    status: {
      type: Number,
      required: true
    },
    isOwner: {
      type: Boolean,
      required: true
    }
  },
  data() {
    return {
      tasks: [],
      content: '',
      editContent: '',
      showAdd: false,
      isCompleted: false,
      checkedTasks: {},
      editingTaskId: null,
      isDelete: false,
      deleteTaskId: null
    };
  },
  computed: {
    completedTasksCount() {
      return this.tasks.filter(task => task.is_done === 1).length
    },

    receivedProjectID() {
      return this.$store.state.sharedProjectID;
    },
  },
  mounted() {
    this.getTasks();
  },
  methods: {
    editTask(taskId, content) {
      this.editContent = content
      this.editingTaskId = taskId
    },

    cancelEditting() {
      this.editingTaskId = null
      this.editContent = ''
    },

    selectAllTasks() {
      this.tasks.forEach(task => {
        this.$set(this.checkedTasks, task.task_id, true);
      });
    },

    unselectAllTasks() {
      this.tasks.forEach(task => {
        this.$set(this.checkedTasks, task.task_id, false);
      });
    },

    approveSelectedTasks() {
      const selectedTaskIds = Object.keys(this.checkedTasks).filter(taskId => !this.checkedTasks[taskId]);
      if (selectedTaskIds.length > 0) {
        selectedTaskIds.forEach(taskId => {
          this.changeTaskStatus(taskId);
        });
        this.updateWork(1)
        showSuccess(t('qlcv', 'Phê duyệt thành công'))
      } else {
        this.updateWork(3)
        showSuccess(t('qlcv', 'Phê duyệt thành công'))
      }
    },
    showNewTask() {
      this.showAdd = true;
      this.scrollToBottom();
    },
    hideNewTask() {
      this.showAdd = false;
      this.content = '';
    },

    async getTasks() {
      try {
        const response = await axios.get(generateUrl(`/apps/qlcv/tasks/${this.workId}`));
        this.tasks = response.data.tasks;
        this.unselectAllTasks()
      } catch (e) {
        console.error(e);
      }
    },

    async createTask() {
      try {
        const response = await axios.post('/apps/qlcv/create_task', {
          work_id: this.workId,
          content: this.content,
          is_done: 0,
        });
        showSuccess(t('qlcv', 'Thêm thành công'));
        await this.getTasks();
        this.hideNewTask();
      } catch (e) {
        console.error(e);
      }
    },

    scrollToBottom() {
      this.$nextTick(() => {
        const list = this.$el.querySelector('.scrollable-list');
        if (list) {
          list.scrollTop = list.scrollHeight;
        }
      });
    },

    async changeTaskStatus(task_id) {
      try {
        const response = await axios.put('/apps/qlcv/update_task', {
          task_id: task_id,
          content: null,
          is_done: 0,
        });
        await this.getTasks()
      } catch (e) {
        console.error(e);
      }
    },

    async updateTask(event, task_id) {
      const isChecked = event.target.checked
      try {
        const response = await axios.put('/apps/qlcv/update_task', {
          task_id: task_id,
          content: null,
          is_done: isChecked ? 1 : 0,
        });
        showSuccess(t('qlcv', 'Cập nhật thành công'))
        await this.getTasks()
        if (this.completedTasksCount == this.tasks.length) {
          this.isCompleted = true
          await this.updateWork(2)
        }
      } catch (e) {
        console.error(e);
      }
    },

    async updateTaskContent(task_id) {
      try {
        const response = await axios.put('/apps/qlcv/update_task', {
          task_id: task_id,
          content: this.editContent,
          is_done: null,
        });
        showSuccess(t('qlcv', 'Cập nhật thành công'))
        this.cancelEditting()
        await this.getTasks()
      } catch (e) {
        console.error(e);
      }
    },

    showModal(task_id) {
      this.isDelete = true
      this.deleteTaskId = task_id
    },

    stopModal() {
      this.isDelete = false
    },

    async deleteTask() {
      try {
        const response = await axios.delete(generateUrl('apps/qlcv/delete_task/' + this.deleteTaskId))
        showSuccess(t('qlcv', 'Xóa thành công'));
        this.getTasks()
        this.stopModal()
      } catch (e) {
        console.error(e)
      }
    },

    async updateWork(status) {
      try {
        const response = await axios.put('/apps/qlcv/update_work', {
          work_name: null,
          description: null,
          start_date: null,
          end_date: null,
          label: null,
          assigned_to: null,
          status: status,
          work_id: this.workId,
          project_id: this.receivedProjectID
        });
        this.status = status
        this.$store.commit('updateWorkStatus', status)
        if (response.data.isProjectDone) this.updateProject()
      } catch (error) {
        console.error("Lỗi khi tạo công việc: ", error);
      }
    },

    async updateProject() {
      try {
        const response = await axios.put('/apps/qlcv/update_project', {
          project_name: null,
          description: null,
          user_id: null,
          project_id: this.receivedProjectID,
          status: 2
        });
        this.$store.commit('updateProjectStatus', 2)
      } catch (e) {
        console.error(e)
      }
    },

    closeModal() {
      this.isCompleted = false
      this.disabled = 3
    }
  }
};
</script>

<style scoped>
.scrollable-list {
  overflow-y: auto;
  max-height: 400px;
  height: 400px;
  margin-bottom: 20px;
  border: 1px solid #ccc;
  padding: 20px;
  word-wrap: break-word;
}

.scrollable-list-b {
  overflow-y: auto;
  max-height: 350px;
  height: 350px;
  margin-bottom: 20px;
  border: 1px solid #ccc;
  padding: 20px;
  word-wrap: break-word;
}

.combo-button {
  display: flex;
  justify-content: flex-end;
  gap: 20px;
}

.tasks {
  height: 500px;
  padding: 20px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.task-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.task-item {
  display: flex;
  align-items: center;
}

.task-item input {
  margin-right: 10px;
}

.task-content {
  width: 100%;
  overflow-wrap: break-word;
  word-break: normal;
  white-space: normal;
  border-radius: 8px;
  padding: 8px;
  margin-bottom: 8px;
  box-sizing: border-box;
  display: inline-flex;
  align-items: center;
}

.task-done {
  background-color: #7fc0e4;
}

.task-not-done {
  background-color: #cce1ec;
}

h3 {
  font-size: 24px;
  font-weight: bold;
  color: #333;
}

.task-actions {
  margin-left: auto;
}

.task-item-new {
  display: flex;
  align-items: center;
  margin-bottom: 8px;
}

.task-item-new input {
  flex-grow: 1;
}

.button {
  margin-right: 10px;
}

input {
  height: 44px !important;
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

.modal {
  z-index: 1000;
}
</style>