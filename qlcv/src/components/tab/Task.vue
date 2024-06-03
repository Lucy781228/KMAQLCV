<template>
      <div v-if="!isTodoWork" class="tasks" >
        <NcEmptyContent description="Tác vụ sẽ hiển thị khi đến ngày bắt đầu công việc.">
          <template #icon>
			<CardsVariant />
		</template>
            <template #title>
                <h1 class="empty-content__title">
                  Số tác vụ: {{ tasks.length }}
                </h1>
            </template>
        </NcEmptyContent>
    </div>
  <div class="tasks" v-else>
    <div class="header">
      <h3>Tiến độ: {{ completedTasksCount }}/{{ tasks.length }}</h3>
      <NcButton :wide="true" aria-label="Example text" type="tertiary" @click="showNewTask" v-if="disableAdd">
        <template #icon>
          <Plus :size="20" />
        </template>
      </NcButton>
    </div>
    <div class="scrollable-list">
      <div class="task-list">
        <div v-for="(task, index) in tasks" :key="index" class="task-item">
          <input type="checkbox" :id="`task-${index}`" :checked="task.is_done" :disabled="disableCheck" @change="updateTask($event, index)" />
          <div class="task-content">
            <label :for="`task-${index}`">{{ task.content }}</label>
            <NcActions class="task-actions" v-if="disableEdit">
              <NcActionButton class="edit-button" type="tertiary" @click="cancelEditting" aria-label="Example text">
                <template #icon>
                  <Pencil :size="16" />Chỉnh sửa
                </template>
              </NcActionButton>
              <NcActionButton class="delete-button" type="tertiary" @click="cancelEditting" aria-label="Example text">
                <template #icon>
                  <Delete :size="16" />Xóa
                </template>
              </NcActionButton>
            </NcActions>
          </div>
        </div>
        <div class="task-item-new" v-if="showAdd">
          <input type="text" v-model="content" placeholder="Thêm tác vụ" />
          <NcButton type="tertiary" @click="hideNewTask" ariaLabel="A" class="button">
            <template #icon>
              <Close :size="20" />
            </template>
          </NcButton>
          <NcButton type="primary" :disabled="!content" @click="createTask" ariaLabel="A">
            <template #icon>
              <ArrowRight :size="20" />
            </template>
          </NcButton>
        </div>
      </div>
    </div>
    <NcModal :show="isCompleted" :canClose="false" size="small">
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
    Alert
  },
  props: {
    workId: {
      type: Number,
      required: true
    },
    isProjectOwner: {
      type: Boolean,
      required: true
    },
    disabled: {
      type: Number,
      required: true
    },
    isTodoWork: {
      type: Boolean,
      required: true
    }
  },
  data() {
    return {
      tasks: [],
      content: '',
      showAdd: false,
      isCompleted: false
    };
  },
  computed: {
    completedTasksCount() {
      return this.tasks.filter(task => task.is_done === 1).length
    },

    disableAdd() {
      if(this.disabled == 3) return false
      else return this.isProjectOwner && !this.showAdd
    },

    disableCheck() {
      if(this.disabled == 3) return true
      else if(this.disabled == 2) return !this.isProjectOwner
      else return this.isProjectOwner
    },

    disableEdit() {
      if(this.disabled == 3) return false
      else return this.isProjectOwner
    },
  },
  mounted() {
    this.getTasks();
  },
  methods: {
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

    async updateTask(event, index) {
      const isChecked = event.target.checked
      try {
        const response = await axios.put('/apps/qlcv/update_task', {
          task_id: this.tasks[index].task_id,
          content: null,
          is_done: isChecked? 1 : 0,
        });
        showSuccess(t('qlcv', 'Cập nhật thành công'))
        await this.getTasks()
        if(this.completedTasksCount == this.tasks.length) {
          this.isCompleted = true
          await this.updateWork()
        }
      } catch (e) {
        console.error(e);
      }
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
                    status: 2,
                    work_id: this.workId
                });
            } catch (error) {
                console.error("Lỗi khi tạo công việc: ", error);
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
  background-color: #7fc0e4;
  border-radius: 8px;
  padding: 8px;
  margin-bottom: 8px;
  box-sizing: border-box;
  display: inline-flex;
  align-items: center;
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
  margin-right: 10px;
  margin-left: 20px;
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
</style>