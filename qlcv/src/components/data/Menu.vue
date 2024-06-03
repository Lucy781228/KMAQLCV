<template>
    <div class="wrapper">
        <div class="menu">
            <NcButton
                aria-label="Example text"
                :type="isSelected === 0 ? 'primary' : 'secondary'"
                @mouseover="hoverButton(0, true)"
                @mouseout="hoverButton(0, false)"
                @click="selectButton(0)">
                <template>Dự án</template>
            </NcButton>
            <NcButton
                aria-label="Example text"
                :type="isSelected === 1 ? 'primary' : 'secondary'"
                @mouseover="hoverButton(1, true)"
                @mouseout="hoverButton(1, false)"
                @click="selectButton(1)">
                <template>Công việc</template>
            </NcButton>
        </div>
        <div class="content">
            <ProjectChart v-if="showProject"/>
        </div>
    </div>
</template>

<script>
import axios from "@nextcloud/axios";
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { NcButton } from "@nextcloud/vue";
import ProjectChart from "./ProjectChart.vue";

export default {
    name: 'Menu',
    components: {
        NcButton,
        ProjectChart,
    },
    data() {
        return {
            isHovered: [false, false],
            isSelected: 0,
            showProject: true
        }
    },
    methods: {
        hoverButton(index, state) {
            this.isHovered.splice(index, 1, state);
        },
        selectButton(index) {
            this.isSelected = index;
            this.showProject = this.isSelected ? false : true;
        }
    }
}
</script>

<style scoped>
.wrapper {
    padding-left: 50px;
    padding-top: 10px;
    padding-bottom: 10px;
    padding-right: 50px
}

.menu {
    display: flex;
    gap: 10px; /* Khoảng cách giữa các nút */
}

.menu NcButton {
    display: inline-block;
}

.menu NcButton:hover {
    cursor: pointer;
}
</style>