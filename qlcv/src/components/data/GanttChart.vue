<template>
  <div ref="gantt" class="gantt-container"></div>
</template>

<script>
import 'dhtmlx-gantt'

export default {
  name: 'GanttChart',
  props: {
    tasks: {
      type: Object,
      default() {
        return { data: [] }
      }
    }
  },

  watch: {
    tasks: {
    deep: true,
    handler(newVal) {
      if (newVal && newVal.data) {
        gantt.clearAll();
        gantt.parse(newVal);
      }
    }
  }
},

  mounted() {
    gantt.plugins({
      marker: true
    });
    gantt.config.readonly = true;
    gantt.config.scale_unit = "day";
    gantt.config.date_scale = "%d %M";
    gantt.config.step = 1;
    gantt.config.subscales = [
      { unit: "month", step: 1, date: "%F %Y" }
    ];
    gantt.config.scale_height = 50;

    gantt.config.columns = [
      { name: "text", label: "Tên công việc", width: 150, tree: true },
    ];

    gantt.templates.task_text = (start, end, task) => {
      let taskText = '';
      let width = 0;
      if (task.actual_duration) {
        width = 70 * task.actual_duration;
        taskText = `<div class="task-text task-texture-diagonal" style="width: ${width}px; height: 29px;">${''}</div>`;
      } else if (task.estimated_duration) {
        width = 70 * task.estimated_duration;
        taskText = `<div class="task-text task-border" style="width: ${width}px; height: 30px;">${''}</div>`;
      }
      return taskText;
    };

    gantt.init(this.$refs.gantt)
    gantt.parse(this.$props.tasks)

    this.$nextTick(() => {
    const tasks = gantt.getTaskByTime();
    if (tasks.length >= 3) {
      const thirdTaskId = tasks[2].id;
      gantt.selectTask(thirdTaskId);
      gantt.showTask(thirdTaskId);
    }
  });

    var currentDate = new Date().setHours(0, 0, 0, 0)

    var todayMarker = gantt.addMarker({
      start_date: currentDate,
      css: "today",
      text: "Today"
    });
  }
}
</script>

<style>
@import "~dhtmlx-gantt/codebase/dhtmlxgantt.css";

.gantt_task_content {
  overflow: visible;
}

.task-text {
  position: relative;
  z-index: 2;
}

.task-texture-diagonal {
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

.task-border {
  border: 2px dashed #000;
  border-radius: 3px;
}

.gantt_tree_icon {
    display: none !important;
}
</style>