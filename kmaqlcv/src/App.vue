<template>
	<NcEmptyContent v-if="!isUserInUsers">
		<template #title>
			<h1 class="empty-content__title">
				Bạn không có quyền truy cập. Liên hệ quản trị viên để được hỗ trợ.
			</h1>
		</template>
	</NcEmptyContent>
	<NcContent id="content" app-name="kmaqlcv" v-else>
		<NcAppNavigation>
			<div class="nav">
				<NcAppNavigationNew text="Thêm dự án" @click="addNewProject" />
				<NcAppNavigationItem :exact="true" name="Công việc sắp tới" @click="updateValue('Công việc sắp tới')"
					to="/">
					<template #icon>
						<CalendarAlertOutline :size="20" />
					</template>
				</NcAppNavigationItem>
				<NcAppNavigationItem v-if="todoProjects.length" :exact="true" :title="t('kmaqlcv', 'Dự án cần làm')"
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
							:title="t('kmaqlcv', item.project_name)"
							@click="updateStore(item.project_id, item.project_name, item.user_id)"
							:to="{ name: 'project', params: { sharedProjectID: item.project_id } }">
							<template #icon>
								<NcAppNavigationIconBullet color="0082c9" />
							</template>
							<template v-if="item.user_id == user.uid" #actions>
								<NcActionButton
									@click="editProject(item.project_id, item.project_name, item.user_id, item.start_date, item.end_date)">
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
									@click="editProject(item.project_id, item.project_name, item.user_id, item.start_date, item.end_date)">
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
					:title="t('kmaqlcv', 'Dự án đang tiến hành')" :allowCollapse="true" :open="true">
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
							:title="t('kmaqlcv', item.project_name)"
							@click="updateStore(item.project_id, item.project_name, item.user_id)"
							:to="{ name: 'project', params: { sharedProjectID: item.project_id } }">
							<template #icon>
								<NcAppNavigationIconBullet color="ddcb55" />
							</template>
							<template v-if="item.user_id == user.uid" #actions>
								<NcActionButton
									@click="editProject(item.project_id, item.project_name, item.user_id, item.start_date, item.end_date)">
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
									@click="editProject(item.project_id, item.project_name, item.user_id, item.start_date, item.end_date)">
									<template #icon>
										<Eye :size="20" />
									</template>
									Chi tiết
								</NcActionButton>
							</template>
						</NcAppNavigationItem>
					</template>
				</NcAppNavigationItem>
				<NcAppNavigationItem v-if="doneProjects.length" :exact="true" :title="t('kmaqlcv', 'Dự án hoàn thành')"
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
							:title="t('kmaqlcv', item.project_name)"
							@click="updateStore(item.project_id, item.project_name, item.user_id)"
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
									@click="editProject(item.project_id, item.project_name, item.user_id, item.start_date, item.end_date)">
									<template #icon>
										<Eye :size="20" />
									</template>
									Chi tiết
								</NcActionButton>
							</template>
						</NcAppNavigationItem>
					</template>
				</NcAppNavigationItem>
			</div>
		</NcAppNavigation>

		<NcAppContent class="parent">
			<router-view />
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

		<NewProject :modal="isAdd" @close="stopModal" :is-edit="isEdit" :project-id="projectId"
			:project-name="projectName" :start-date="startDate" :end-date="endDate" />
	</NcContent>
</template>

<script>
import { NcAppContent, NcActions, NcActionButton, NcButton, NcModal } from "@nextcloud/vue";
import { NcAppNavigation, NcCounterBubble, NcEmptyContent } from "@nextcloud/vue";
import { NcAppNavigationItem, NcAppNavigationIconBullet, NcAppNavigationNew } from "@nextcloud/vue";
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
		NcEmptyContent
	},
	data() {
		return {
			todoProjects: [],
			doingProjects: [],
			doneProjects: [],
			user: getCurrentUser(),
			isAdd: false,
			isEdit: false,
			isDelete: false,
			projectId: "",
			startDate: null,
			endDate: null,
			projectName: "",
			users: []
		};
	},

	computed: {
		isUserInUsers() {
			return this.users.some(user => user.qlcb_uid === this.user.uid);
		}
	},

	async mounted() {
		await this.getUsers()
		console.log(this.users)
		console.log(this.isUserInUsers)
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

		editProject(id, name, user_id, start_date, end_date) {
			this.$store.commit('updateProject', id)
			this.$store.commit('updateTitle', name)
			this.$store.commit('updateProjectOwner', user_id)
			this.isAdd = true
			this.isEdit = true
			this.projectId = id
			this.projectName = name
			this.startDate = start_date
			this.endDate = end_date
		},

		stopModal() {
			this.isAdd = false
			this.isEdit = false
			this.isDelete = false
			this.projectId = ""
			this.startDate = null
			this.endDate = null
			this.projectName = ""
			this.getProjects()
		},

		updateValue(itemName) {
			this.$store.commit('updateValue', itemName);
		},

		updateStore(id, name, user_id) {
			this.$store.commit('updateProject', id)
			this.$store.commit('updateTitle', name)
			this.$store.commit('updateProjectOwner', user_id)
		},

		async getProjects() {
			try {
				const response = await axios.get(generateUrl(`/apps/kmaqlcv/projects/${this.user.uid}`))
				const projects = response.data.projects;

				// Lọc các dự án theo status
				this.todoProjects = projects.filter(project => project.status === 0);
				this.doingProjects = projects.filter(project => project.status === 1);
				this.doneProjects = projects.filter(project => project.status === 2);

			} catch (e) {
				console.error(e)
			}
		},

		async deleteProject() {
			try {
				const response = await axios.delete(generateUrl('apps/kmaqlcv/delete_project/' + this.projectId))
				showSuccess(t('kmaqlcv', 'Xóa thành công'));
				this.stopModal()
				this.getProjects()
			} catch (e) {
				console.error(e)
			}
		},

		async getUsers() {
			try {
				const response = await axios.get(generateUrl('/apps/kmaqlcv/users'));
				this.users = response.data.users

			} catch (e) {
				console.error(e)
			}
		},

	}
};

</script>

<style scoped>
.nav {
	margin: 10px;
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