<template>
  <div class="container">
    <Gantt class="left-container" :tasks="tasks"></Gantt>
  </div>
</template>

<script>
import Gantt from './GanttChart.vue'

export default {
  name: 'Test',
  components: { Gantt },
  data() {
    return {
      tasks: {
        "data": [
          { "id": "2", "text": "Work 1", "start_date": "13-05-2024", "duration": 10, "color": "gray" },
          { "id": "4", "text": "Work 2", "start_date": "15-05-2024", "duration": 3, "color": "gray" },
          { "id": "1", "text": "Work 3", "start_date": "19-05-2024", "duration": 9, "estimated_duration": 6 },
          { "id": "3", "text": "Work 4", "start_date": "20-05-2024", "duration": 11, "estimated_duration": 9 },
          { "id": "5", "text": "Work 5", "start_date": "24-05-2024", "duration": 10, "estimated_duration": 7 },
          { "id": "6", "text": "Work 6", "start_date": "27-05-2024", "duration": 15, "estimated_duration": 10 }
        ]
      }
    }
  }
}
</script>

<style>
html,
body {
  height: 100%;
  margin: 0;
  padding: 0;
}

.container {
  height: 100%;
  width: 100%;
  padding: 50px;
}

.left-container {
  overflow: hidden;
  position: relative;
  height: 100%;
}
</style>