<template>
  <div class="tasks">
    <div>
      <h3>Tiến độ: </h3>
    </div>
    <div class="scrollable-list">
      <div class="task-list">
        <div v-for="(task, index) in tasks" :key="index" class="task-item">
          <input type="checkbox" :id="`task-${index}`" :checked="task.is_done" />
          <div class="task-content">
            <label :for="`task-${index}`">{{ task.content }}</label>
            <NcActions class="task-actions">
              <NcActionButton class="edit-button" type="tertiary" @click="cancelEditting" aria-label="Example text">
                <template #icon>
                  <Pencil :size="20" />Chỉnh sửa
                </template>
              </NcActionButton>
              <NcActionButton class="delete-button" type="tertiary" @click="cancelEditting" aria-label="Example text">
                <template #icon>
                  <Delete :size="20" />Xóa
                </template>
              </NcActionButton>
            </NcActions>
          </div>
        </div>
        <NcButton :wide="true" aria-label="Example text" type="secondary">
          <template #icon>
            <Plus :size="20" />
          </template>Thêm tác vụ
        </NcButton>
      </div>
    </div>
  </div>
</template>

<script>
import { generateUrl } from '@nextcloud/router'
import axios from "@nextcloud/axios";
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import { NcButton, NcActionButton, NcActions } from "@nextcloud/vue";

export default {
  name: 'Task',
  components: {
    NcActionButton,
    NcActions,
    NcButton,
    Pencil,
    Delete,
    Plus
  },
  props: {
    workId: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      tasks: []
    };
  },
  mounted() {
    this.getTasks()
  },
  methods: {
    async getTasks() {
      try {
        const response = await axios.get(generateUrl(`/apps/kmaqlcv/tasks/${this.workId}`));
        this.tasks = response.data.tasks;

      } catch (e) {
        console.error(e)
      }
    },
  }
}
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
  margin-bottom: 20px;
}

.task-actions {
  margin-left: auto;
}
</style>