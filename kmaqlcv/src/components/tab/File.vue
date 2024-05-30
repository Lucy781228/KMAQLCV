<template>
  <div class="files">
    <div class="grid-container">
      <div class="grid-item upload">
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
                  {{ formatFileSize(file.size) }}
                </template>
                <template #actions>
                  <NcActions>
                    <NcActionButton @click="downloadFile(file.file_id)">
                      <template #icon>
                        <ArrowCollapseDown :size="20" />
                      </template>
                      Tải xuống
                    </NcActionButton>
                    <NcActionButton @click="deleteFile(file.file_id)" v-if="user.uid == file.owner">
                      <template #icon>
                        <Delete :size="20" />
                      </template>
                      Xóa
                    </NcActionButton>
                  </NcActions>
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
import { NcActionButton, NcListItem, NcButton, NcActions } from "@nextcloud/vue";
import ArrowCollapseDown from 'vue-material-design-icons/ArrowCollapseDown.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import TrayArrowUp from 'vue-material-design-icons/TrayArrowUp.vue'

export default {
  name: 'File',
  components: {
    NcListItem,
    NcActionButton,
    NcButton,
    NcActions,
    ArrowCollapseDown,
    Delete,
    TrayArrowUp
  },
  props: {
    workId: {
      type: Number,
      required: true
    },
    assignedTo: {
      type: String,
      required: true
    },
    owner: {
      type: String,
      required: true
    },
    shareUser: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      file: null,
      files: [],
      user: getCurrentUser(),
      // shareUser: ''
    };
  },
  mounted() {
    this.getFiles();
  },

  computed: {
    shareUser() {
      return this.user.uid == this.assignedTo ? this.owner : this.assignedTo
    }
  },

  methods: {
    triggerFileInput() {
      this.$refs.fileInput.click();
    },
    handleFileChange(event) {
      this.file = event.target.files[0];
      this.uploadFile();
    },
    async getFiles() {
      try {
        const response = await axios.get('/apps/kmaqlcv/get_files', {
          params: {
            work_id: this.workId,
            share_by: this.shareUser
          }
        });
        this.files = response.data.files;

        console.log(this.files)
      } catch (error) {
        console.error('Error fetching files', error);
      }
    },
    async uploadFile() {
      if (!this.file) {
        alert('Please select a file.');
        return;
      }

      let formData = new FormData();
      formData.append('file', this.file);
      formData.append('share_with', this.shareUser);
      formData.append('work_id', this.workId);

      try {
        let response = await axios.post('/apps/kmaqlcv/upload_file', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        console.log('File uploaded successfully', response);
        this.getFiles(); // Refresh the file list
      } catch (error) {
        console.error('Error uploading file', error);
        console.log(this.shareUser);
      }
    },
    async downloadFile(fileId) {
      try {
        const response = await axios.get(`/apps/kmaqlcv/download_file/${fileId}/${this.shareUser}`, {
          responseType: 'blob', // Important for handling the binary data correctly
        });
        const contentDisposition = response.headers['content-disposition'];
        let fileName = 'downloaded-file';
        if (contentDisposition) {
          const fileNameMatch = contentDisposition.match(/filename="?([^"]+)"?/);
          if (fileNameMatch.length === 2)
            fileName = fileNameMatch[1];
        }
        console.log(fileName)
        // Create a URL for the blob object and trigger the download
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
    async deleteFile(fileId) {
      try {
        await axios.delete(`/apps/kmaqlcv/delete_file/${fileId}`);
        console.log('File deleted successfully');
        this.getFiles(); // Refresh the file list
      } catch (error) {
        console.error('Error deleting file', error);
      }
    },
    formatFileSize(size) {
      const units = ['B', 'KB', 'MB', 'GB', 'TB'];
      let unitIndex = 0;
      let fileSize = size;

      console.log(size)

      while (fileSize >= 1024 && unitIndex < units.length - 1) {
        fileSize /= 1024;
        unitIndex++;
      }

      return `${fileSize.toFixed(1)} ${units[unitIndex]}`;
    },
    formatDateTime(dateTime) {
      const date = new Date(dateTime);
      return date.toLocaleString();
    }
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
  margin: 0;
}

li {
  margin-bottom: 10px;
  width: 90%
}

.scrollable-list {
  overflow-y: auto;
  max-height: 480px;
  height: 480px;
  padding: 10px;
  border: 1px solid #ccc;
  word-wrap: break-word;
}
</style>