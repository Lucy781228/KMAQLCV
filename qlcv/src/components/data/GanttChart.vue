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
      default () {
        return {data: []}
      }
    }
  },
    
  mounted () {
    gantt.plugins({
      marker: true
    });

    // Ẩn bảng
    gantt.config.show_grid = false;

    // Ngăn user thay đổi chiều dài của từng thanh
    gantt.config.readonly = true;

    // Cấu hình thang đo thời gian
    gantt.config.scale_unit = "day";
    gantt.config.date_scale = "%d %M";
    gantt.config.step = 1;
    gantt.config.subscales = [
      {unit: "month", step: 1, date: "%F %Y"}
    ];
    gantt.config.scale_height = 50;

    gantt.templates.task_class = function(start, end, task) {
      return task.color ? `task-color-${task.color}` : 'task-color-default';
    };

    // Tùy chỉnh để hiển thị estimated_duration
    gantt.templates.task_text = (start, end, task) => {
      return this.showEstimatedDuration(task) || task.text;
    };

    gantt.init(this.$refs.gantt)
    gantt.parse(this.$props.tasks)

    var currentDate = new Date().setHours(0, 0, 0, 0)

    var todayMarker = gantt.addMarker({ 
      start_date: currentDate,
      css: "today", 
      text: "Today"
    });
  },
  methods: {
    showEstimatedDuration(task) {
      if (task.estimated_duration) {
        const estimatedEndDate = gantt.calculateEndDate({start_date: task.start_date, duration: task.estimated_duration});
        const estimatedWidth = gantt.posFromDate(estimatedEndDate) - gantt.posFromDate(task.start_date);
        const taskWidth = gantt.posFromDate(task.end_date) - gantt.posFromDate(task.start_date);
        const left_position = 0; // Vị trí bắt đầu của thanh estimated_duration
        return `
          <div class="estimated-duration-bar" style="position: absolute; left: ${left_position}px; width: ${estimatedWidth}px;"></div>
          <div class="task-text">${task.text}</div>
        `;
      }
      return task.text;
    }
  }
}
</script>

<style>
@import "~dhtmlx-gantt/codebase/dhtmlxgantt.css";

.gantt_task_content {
  overflow: visible;
}

.estimated-duration-bar {
  height: 100%;
  background-color: rgba(0, 255, 55); /* Màu xanh nhạt với độ trong suốt */
  border-radius: 3px;
  z-index: 1;
}

.task-text {
  position: relative;
  z-index: 2;
}

.gray .gantt_task_content {
  background-color: gray;
}
</style>