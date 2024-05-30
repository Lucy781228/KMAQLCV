import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

function loadState() {
    const storedData = localStorage.getItem('store');
    if (storedData) {
        return JSON.parse(storedData);
    }
    return {
        sharedTitle: 'Tác vụ sắp tới',
        sharedProjectOwner: '',
        sharedProjectID: 0,
    };
}

export default new Vuex.Store({
    state: loadState(),
    mutations: {
        updateTitle(state, value) {
            state.sharedTitle = value;
            localStorage.setItem('store', JSON.stringify(state));
        },
        updateProjectOwner(state, value) {
            state.sharedProjectOwner = value;
            localStorage.setItem('store', JSON.stringify(state));
        },
        updateProject(state, value) {
            state.sharedProjectID = value;
            localStorage.setItem('store', JSON.stringify(state));
        }
    }
});