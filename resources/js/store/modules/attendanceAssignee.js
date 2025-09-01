import axios from 'axios'

export const attendanceAssignee = {
    namespaced: true,
    state: {
        currentAssignee: null,
        employees: [],
        assignmentHistory: []
    },

    getters: {
        currentAssignee: function (state) {
            return state.currentAssignee;
        },
        employees: function (state) {
            return state.employees;
        },
        assignmentHistory: function (state) {
            return state.assignmentHistory;
        }
    },

    actions: {
        index: function (context) {
            return new Promise((resolve, reject) => {
                axios.get('admin/attendance-assignee').then((res) => {
                    context.commit('setData', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        store: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('admin/attendance-assignee', payload).then(res => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        destroy: function (context) {
            return new Promise((resolve, reject) => {
                axios.delete('admin/attendance-assignee').then(res => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        }
    },

    mutations: {
        setData: function (state, data) {
            state.currentAssignee = data.current_assignee;
            state.employees = data.employees;
            state.assignmentHistory = data.assignee_history;
        }
    }
}
