/**
 * SPDX-FileCopyrightText: 2018 John Molakvoæ <skjnldsv@protonmail.com>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { generateFilePath } from '@nextcloud/router'
import { generateUrl } from '@nextcloud/router'

import Vue from 'vue'
import App from './App'
import WorkList from './components/WorkList.vue'
import NewWork from './components/NewWork.vue'
import VueRouter from 'vue-router'
import store from './store'
import WorkMenu from './components/WorkMenu.vue'
import UpcomingWorkList from './components/UpcomingWorkList.vue'



Vue.use(VueRouter)
Vue.directive('click-outside', {
	bind(el, binding, vnode) {
	  el.clickOutsideEvent = function (event) {
		if (!(el === event.target || el.contains(event.target)) && event.target.closest('.vue-select') === null) {
		  vnode.context[binding.expression](event);
		}
	  };
	  document.body.addEventListener('click', el.clickOutsideEvent);
	},
	unbind(el) {
	  document.body.removeEventListener('click', el.clickOutsideEvent);
	},
  });
  
const router = new VueRouter({
	// mode: 'history', 
	base: generateUrl('/apps/kmaqlcv'),
	routes: [
		{
			path: '/',
			component: UpcomingWorkList
		},
		{
			path: '/newwork',
			component: NewWork,
		},
		{
			name: 'project',
			path: '/project/:sharedProjectID',
			component: WorkList,
			children: [
				{
					name: 'work',
					path: 'work/:workId',
					component: WorkMenu,
					props: true
				}
			],
		}
	]
})

// router.push('/apps/qlcv/upcoming_tasks')
__webpack_public_path__ = generateFilePath(appName, '', 'js/')

Vue.mixin({ methods: { t, n } })

export default new Vue({
	store,
	router,
	el: '#content',
	render: h => h(App),
})
