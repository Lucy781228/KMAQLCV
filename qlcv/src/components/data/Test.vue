<template>
  <div class="container">
    <div class="action-button">
      <NcButton type="tertiary" aria-label="Example text">
        <template #icon>
          <ArrowLeft :size="20" />
        </template>
      </NcButton>
      <NcButton type="secondary" aria-label="Example text">
        <template #icon>
          <ChartLineVariant :size="20" />
        </template>
      </NcButton>
    </div>
    <div class="legend">
      <div class="legend-item">
        <div class="legend-color initial-duration"></div>
        <span>Thời gian hoàn thành ban đầu</span>
      </div>
      <div class="legend-item">
        <div class="legend-color actual-duration"></div>
        <span>Thời gian hoàn thành thực tế</span>
      </div>
      <div class="legend-item">
        <div class="legend-color estimated-duration"></div>
        <span>Thời gian hoàn thành dự kiến</span>
      </div>
    </div>
    <Gantt class="left-container" :tasks="tasks"></Gantt>
  </div>
</template>

<script>
import Gantt from './GanttChart.vue'
import ChartLineVariant from 'vue-material-design-icons/ChartLineVariant.vue'
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'
import { NcButton } from "@nextcloud/vue";


export default {
  name: 'Test',
  components: {
    Gantt,
    ChartLineVariant,
    ArrowLeft,
    NcButton
  },
  data() {
    return {
      tasks: {
        "data": [
          { "id": "2", "text": "Work 1", "start_date": "23-05-2024", "duration": 10, "actual_duration": 8 },
          { "id": "4", "text": "Work 2", "start_date": "25-05-2024", "duration": 3, "actual_duration": 5 },
          { "id": "1", "text": "Work 3", "start_date": "29-05-2024", "duration": 9, "estimated_duration": 6 },
          { "id": "3", "text": "Work 4", "start_date": "30-05-2024", "duration": 11 },
          { "id": "5", "text": "Work 5", "start_date": "03-06-2024", "duration": 10, "estimated_duration": 7 },
          { "id": "6", "text": "Work 6", "start_date": "06-06-2024", "duration": 15, "estimated_duration": 10}
        ]
      }
    }
  }
}
</script>

<style scoped>
@import "~dhtmlx-gantt/codebase/dhtmlxgantt.css";


.container {
  height: 100%;
  width: 100%;
  padding: 40px;
}

.left-container {
  overflow: hidden;
  position: relative;
  height: 90%;
}

.action-button {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-bottom: 5px;
}

.legend {
  display: flex;
  justify-content: center;
  margin-bottom: 10px;
  margin: 0 auto
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  padding-left: 15px;
  padding-right: 15px;
}

.legend-color {
  width: 30px;
  height: 15px;
}

.actual-duration {
  background-color: #3DB9D3;
  background-image: linear-gradient(45deg,
      rgba(255, 255, 255, 0.4) 25%,
      transparent 25%,
      transparent 50%,
      rgba(255, 255, 255, 0.4) 50%,
      rgba(255, 255, 255, 0.4) 75%,
      transparent 75%,
      transparent);
  background-size: 30px 30px;
  color: white;
  border: none;
}

.estimated-duration {
  border: 2px dashed #000;
  border-radius: 3px;
  background-color: white;
}

.initial-duration {
  background-color: #3DB9D3;
}
</style>