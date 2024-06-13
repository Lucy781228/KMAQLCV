<template>
  <div class="files">
    <div class="grid-container" :style="{ 'grid-template-columns': gridContainerStyle }">
      <div class="grid-item upload" v-if="sharedWorkStatus == 1 || isOwner">
        <TrayArrowUp :size="70" />
        <NcButton :wide="true" type="primary" @click="triggerFileInput">Tải lên</NcButton>
        <input type="file" ref="fileInput" @change="handleFileChange" style="display: none;" />
      </div>
      <div class="grid-item file-list">
        <div class="scrollable-list">
          <ul>
            <li v-for="file in files" :key="file.file_id">
              <NcListItem :title="file.file_name" :bold="false" :details="formatDateTime(file.mtime)">
                <template #subtitle>
                  {{ formatFileSize(file.size) + ' ' + showName(file.uploaded_by) }}
                </template>
                <template #actions>
                  <NcActionButton @click="downloadFile(file.file_id)">
                    <template #icon>
                      <ArrowCollapseDown :size="20" />
                    </template>
                    Tải xuống
                  </NcActionButton>
                  <NcActionButton @click="deleteFile(file.file_id)" v-if="user.uid == owner">
                    <template #icon>
                      <Delete :size="20" />
                    </template>
                    Xóa
                  </NcActionButton>
                </template>
              </NcListItem>
            </li>
          </ul>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import axios from "@nextcloud/axios";
import { getCurrentUser } from '@nextcloud/auth'
import { NcActionButton, NcListItem, NcButton } from "@nextcloud/vue";
import ArrowCollapseDown from 'vue-material-design-icons/ArrowCollapseDown.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import TrayArrowUp from 'vue-material-design-icons/TrayArrowUp.vue'

export default {
  name: 'File',
  components: {
    NcListItem,
    NcActionButton,
    NcButton,
    ArrowCollapseDown,
    Delete,
    TrayArrowUp
  },
  props: {
    workId: {
      type: Number,
      required: true
    },
    owner: {
      type: String,
      required: true
    },
    assignedTo: {
      type: String,
      required: true
    },
    isOwner: {
      type: Boolean,
      required: true
    },
  },
  data() {
    return {
      file: null,
      files: [],
      user: getCurrentUser(),
      file_id: null,
      assignedName: '',
      ownerName: ''
    };
  },

  async mounted() {
    if (this.sharedWorkStatus == 1 && this.owner != this.assignedTo) {
      await this.shareFolder()
      await this.addCreatePermission()
    }
    this.getFiles();
    this.assignedName = await this.getFullName(this.assignedTo);
    this.ownerName = await this.getFullName(this.owner);

  },

  computed: {
    gridContainerStyle() {
      if (this.sharedWorkStatus == 1 || this.isOwner) {
        return '2fr 4fr'
      }
      return '1fr'
    },

    sharedWorkStatus() {
      return this.$store.state.sharedWorkStatus;
    }
  },

  watch: {
    'sharedWorkStatus'(newStatus) {
      if (this.owner != this.assignedTo && newStatus != 0) {
        if (newStatus == 1) {
          this.addCreatePermission()
        }
        else if (newStatus != 0) {
          this.setReadPermission()
        }
      }
      console.log('sharedWorkStatus changed to: ', newStatus);
    }
  },

  methods: {
    triggerFileInput() {
      this.$refs.fileInput.click();
    },
    async handleFileChange(event) {
      this.file = event.target.files[0];
      await this.uploadFile()
      await this.createFile()
      this.getFiles();
    },

    async shareFolder() {
      try {
        const response = await axios.get('/apps/qlcv/share_folder', {
          params: {
            work_id: this.workId,
            owner: this.owner,
            assigned_to: this.assignedTo
          }
        });
      } catch (error) {
        console.error('Error fetching files', error);
      }
    },

    async setReadPermission() {
      try {
        const response = await axios.get('/apps/qlcv/set_read_permission', {
          params: {
            work_id: this.workId,
            owner: this.owner,
            assigned_to: this.assignedTo
          }
        });
      } catch (error) {
        console.error('Error fetching files', error);
      }
    },

    async addCreatePermission() {
      try {
        const response = await axios.get('/apps/qlcv/add_create_permission', {
          params: {
            work_id: this.workId,
            owner: this.owner,
            assigned_to: this.assignedTo
          }
        });
      } catch (error) {
        console.error('Error fetching files', error);
      }
    },

    async getFiles() {
      try {
        const response = await axios.get('/apps/qlcv/get_files', {
          params: {
            work_id: this.workId,
            owner: this.owner
          }
        });
        this.files = response.data.files;
      } catch (error) {
        console.error('Error fetching files', error);
      }
    },

    async uploadFile() {
      let formData = new FormData();
      formData.append('file', this.file);
      formData.append('owner', this.owner);
      formData.append('work_id', this.workId);

      try {
        let response = await axios.post('/apps/qlcv/upload_file', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        this.file_id = response.data.fileId
      } catch (error) {
        console.error('Error uploading file', error);
      }
    },


    async downloadFile(fileId) {
      try {
        const response = await axios.get(`/apps/qlcv/download_file`, {
          params: {
            fileId: fileId,
            owner: this.owner,
            work_id: this.workId
          },
          responseType: 'blob',
        });
        const contentDisposition = response.headers['content-disposition'];
        let fileName = 'downloaded-file';
        if (contentDisposition) {
          const fileNameMatch = contentDisposition.match(/filename="?([^"]+)"?/);
          if (fileNameMatch.length === 2)
            fileName = fileNameMatch[1];
        }
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', fileName);
        document.body.appendChild(link);
        link.click();
      } catch (error) {
        console.error('Error downloading file', error);
      }
    },

    async createFile() {
      try {
        const response = await axios.post('/apps/qlcv/create_file', {
          work_id: this.workId,
          file_id: this.file_id
        });
      } catch (error) {
        console.error('Error fetching files', error);
      }
    },

    async deleteFile(fileId) {
      try {
        await axios.delete(`/apps/qlcv/delete_file/${fileId}/${this.owner}`);
        console.log('File deleted successfully');
        this.getFiles();
      } catch (error) {
        console.error('Error deleting file', error);
      }
    },

    formatFileSize(size) {
      const units = ['B', 'KB', 'MB', 'GB', 'TB'];
      let unitIndex = 0;
      let fileSize = size;
      while (fileSize >= 1024 && unitIndex < units.length - 1) {
        fileSize /= 1024;
        unitIndex++;
      }

      return `${fileSize.toFixed(1)} ${units[unitIndex]}`;
    },

    formatDateTime(dateTime) {
      const date = new Date(dateTime * 1000);
      return date.toLocaleString();
    },

    showName(name) {
      return this.owner == name ? this.ownerName : this.assignedName
    },

    async getFullName(user_id) {
      try {
        const response = await axios.get(`/apps/qlcv/full_name/${user_id}`)
        return response.data.full_name.full_name
      } catch (e) {
        console.error(e)
      }
    },
  }
};
</script>

<style scoped>
.files {
  height: 500px;
  padding: 20px;
}

.grid-container {
  display: grid;
  grid-template-columns: 2fr 4fr;
  gap: 20px;
  height: 100%;
}

.grid-item.upload {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  margin: 40px
}

ul {
  list-style-type: none;
  margin: 10px;
}

li {
  margin-bottom: 10px;
  width: 97%
}

.scrollable-list {
  overflow-y: auto;
  max-height: 480px;
  height: 480px;
  border: 1px solid #ccc;
  word-wrap: break-word;
}
</style>