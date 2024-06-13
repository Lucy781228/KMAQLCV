<template>
	<NcContent id="content" app-name="qlcv">
		<NcAppNavigation>
			<template #list>
				<NcAppNavigationNew text="Thêm dự án" @click="addNewProject" />
				<NcAppNavigationItem :exact="true" name="Công việc sắp tới" @click="updateTitle('Công việc sắp tới')"
					to="/">
					<template #icon>
						<CalendarAlertOutline :size="20" />
					</template>
				</NcAppNavigationItem>
				<NcAppNavigationItem v-if="todoProjects.length" :exact="true" :title="t('qlcv', 'Dự án cần làm')"
					:allowCollapse="true">
					<template #icon>
						<Briefcase :size="20" />
					</template>
					<template #counter>
						<NcCounterBubble>
							{{ todoProjects.length }}
						</NcCounterBubble>
					</template>
					<template>
						<NcAppNavigationItem v-for="(item, index) in todoProjects" :key="`project-${index}`"
							:title="t('qlcv', item.project_name)"
							@click="updateStore(item.project_id, item.project_name, item.user_id, item.status)"
							:to="{ name: 'project', params: { sharedProjectID: item.project_id } }">
							<template #icon>
								<NcAppNavigationIconBullet color="0082c9" />
							</template>
							<template v-if="item.user_id == user.uid" #actions>
								<NcActionButton
									@click="editProject(item.project_id, item.project_name, item.user_id, item.description, item.status)">
									<template #icon>
										<Pencil :size="20" />
									</template>
									Sửa
								</NcActionButton>
								<NcActionButton @click="showDeleteModal(item.project_id)">
									<template #icon>
										<Delete :size="20" />
									</template>
									Xóa
								</NcActionButton>
							</template>
							<template v-else #actions>
								<NcActionButton
									@click="editProject(item.project_id, item.project_name, item.user_id, item.description, item.status)">
									<template #icon>
										<Eye :size="20" />
									</template>
									Chi tiết
								</NcActionButton>
							</template>
						</NcAppNavigationItem>
					</template>
				</NcAppNavigationItem>
				<NcAppNavigationItem v-if="doingProjects.length" :exact="true"
					:title="t('qlcv', 'Dự án đang tiến hành')" :allowCollapse="true" :open="true">
					<template #icon>
						<Briefcase :size="20" />
					</template>
					<template #counter>
						<NcCounterBubble>
							{{ doingProjects.length }}
						</NcCounterBubble>
					</template>
					<template>
						<NcAppNavigationItem v-for="(item, index) in doingProjects" :key="`project-${index}`"
							:title="t('qlcv', item.project_name)"
							@click="updateStore(item.project_id, item.project_name, item.user_id, item.status)"
							:to="{ name: 'project', params: { sharedProjectID: item.project_id } }">
							<template #icon>
								<NcAppNavigationIconBullet color="ddcb55" />
							</template>
							<template v-if="item.user_id == user.uid" #actions>
								<NcActionButton
									@click="editProject(item.project_id, item.project_name, item.user_id, item.description, item.status)">
									<template #icon>
										<Pencil :size="20" />
									</template>
									Sửa
								</NcActionButton>
								<NcActionButton @click="showDeleteModal(item.project_id)">
									<template #icon>
										<Delete :size="20" />
									</template>
									Xóa
								</NcActionButton>
							</template>
							<template v-else #actions>
								<NcActionButton
									@click="editProject(item.project_id, item.project_name, item.user_id, item.description, item.status)">
									<template #icon>
										<Eye :size="20" />
									</template>
									Chi tiết
								</NcActionButton>
							</template>
						</NcAppNavigationItem>
					</template>
				</NcAppNavigationItem>
				<NcAppNavigationItem v-if="doneProjects.length" :exact="true" :title="t('qlcv', 'Dự án hoàn thành')"
					:allowCollapse="true">
					<template #icon>
						<Briefcase :size="20" />
					</template>
					<template #counter>
						<NcCounterBubble>
							{{ doneProjects.length }}
						</NcCounterBubble>
					</template>
					<template>
						<NcAppNavigationItem v-for="(item, index) in doneProjects" :key="`project-${index}`"
							:title="t('qlcv', item.project_name)"
							@click="updateStore(item.project_id, item.project_name, item.user_id, item.status)"
							:to="{ name: 'project', params: { sharedProjectID: item.project_id } }">
							<template #icon>
								<NcAppNavigationIconBullet color="4ce046" />
							</template>
							<template v-if="item.user_id == user.uid" #actions>
								<NcActionButton @click="showDeleteModal(item.project_id)">
									<template #icon>
										<Delete :size="20" />
									</template>
									Xóa
								</NcActionButton>
							</template>
							<template v-else #actions>
								<NcActionButton
									@click="editProject(item.project_id, item.project_name, item.user_id, item.description, item.status)">
									<template #icon>
										<Eye :size="20" />
									</template>
									Chi tiết
								</NcActionButton>
							</template>
						</NcAppNavigationItem>
					</template>
				</NcAppNavigationItem>
			</template>
			<!-- <template #footer>
				<NcAppNavigationItem :exact="true" name="Thống kê và báo cáo" @click="" :to="'/analyst'">
					<template #icon>
						<Poll :size="20" />
					</template>
				</NcAppNavigationItem>
			</template> -->
		</NcAppNavigation>

		<NcAppContent class="parent">
			<router-view @close="stopModal" />
		</NcAppContent>
		<NcModal :show="isDelete" :canClose="false" size="small">
			<div class="modal__content">
				<h3>Bạn chắc chắn không?</h3>
				<div class="modal__actions">
					<NcButton @click="stopModal" type="primary">
						Hủy
					</NcButton>
					<NcButton @click="deleteProject" type="secondary">
						Xóa
					</NcButton>
				</div>
			</div>
		</NcModal>

		<NewProject :modal="isAdd" @close="stopModal" :project-id="projectId"
			:project-name="projectName" :description="description" :status="status" />
	</NcContent>
</template>

<script>
import { NcAppContent, NcActions, NcActionButton, NcButton, NcModal } from "@nextcloud/vue";
import { NcAppNavigation, NcCounterBubble } from "@nextcloud/vue";
import { NcAppNavigationItem, NcAppNavigationIconBullet, NcAppNavigationNew, NcAppNavigationNewItem } from "@nextcloud/vue";
import { NcContent } from "@nextcloud/vue";
import CalendarAlertOutline from 'vue-material-design-icons/CalendarAlertOutline'
import Magnify from 'vue-material-design-icons/Magnify'
import Plus from 'vue-material-design-icons/Plus'
import Delete from 'vue-material-design-icons/Delete'
import Briefcase from 'vue-material-design-icons/Briefcase'
import Pencil from 'vue-material-design-icons/Pencil'
import Eye from 'vue-material-design-icons/Eye'
import Poll from 'vue-material-design-icons/Poll'
import axios from "@nextcloud/axios";
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { getCurrentUser } from '@nextcloud/auth'
import NewProject from "./components/NewProject.vue";

export default {
	name: "App",
	components: {
		NcAppContent,
		NcAppNavigation,
		NcAppNavigationItem,
		NcContent,
		CalendarAlertOutline,
		Magnify,
		NcActionButton,
		NcActions,
		Pencil,
		Delete,
		Plus,
		Eye,
		NcAppNavigationIconBullet,
		NcAppNavigationNew,
		NewProject,
		NcModal,
		NcButton,
		NcCounterBubble,
		Poll,
		Briefcase,
		NcAppNavigationNewItem
	},
	data() {
		return {
			todoProjects: [],
			doingProjects: [],
			doneProjects: [],
			user: getCurrentUser(),
			isAdd: false,
			isDelete: false,
			projectId: "",
			description: "",
			projectName: "",
			status: 0
		};
	},

	computed: {
		receivedProjectID() {
			return this.$store.state.sharedProjectID
		},
	},

	mounted() {
		this.getProjects()
	},

	methods: {
		addNewProject() {
			this.isAdd = true
		},

		showDeleteModal(id) {
			this.projectId = id
			this.isDelete = true
		},

		editProject(id, name, user_id, description, status) {
			this.$store.commit('updateProject', id)
			this.$store.commit('updateTitle', name)
			this.$store.commit('updateProjectOwner', user_id)
			this.isAdd = true
			this.projectId = id
			this.projectName = name
			this.description = description
			this.status = status
		},

		stopModal() {
			this.isAdd = false
			this.isDelete = false
			this.projectId = ""
			this.description = ""
			this.projectName = ""
			this.status = 0
			this.getProjects()
		},

		updateTitle(itemName) {
			this.$store.commit('updateTitle', itemName);
		},

		updateStore(id, name, user_id, status) {
			this.$store.commit('updateProject', id)
			this.$store.commit('updateTitle', name)
			this.$store.commit('updateProjectOwner', user_id)
			this.$store.commit('updateProjectStatus', status)
		},

		async getProjects() {
			try {
				const response = await axios.get(generateUrl('/apps/qlcv/projects'))
				const projects = response.data.projects;
				this.todoProjects = projects.filter(project => project.status === 0);
				this.doingProjects = projects.filter(project => project.status === 1);
				this.doneProjects = projects.filter(project => project.status === 2);

			} catch (e) {
				console.error(e)
			}
		},

		async deleteProject() {
			try {
				const response = await axios.delete(generateUrl('apps/qlcv/delete_project/' + this.projectId));
				const deletedProjectId = this.projectId
				showSuccess(t('qlcv', 'Xóa thành công'));
				this.stopModal();
				this.getProjects();
				if (deletedProjectId == this.receivedProjectID) {
				console.log(this.receivedProjectID)
					this.$router.push({ name: 'upcoming' })
				}

			} catch (e) {
				console.error(e);
			}
		}

	}
};

</script>

<style scoped>
.nav {
	margin-left: 10px;
	margin-top: 10px;
	margin-bottom: 10px;
	overflow-y: auto;
	height: 100% !important
}

.parent {
	position: relative;
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