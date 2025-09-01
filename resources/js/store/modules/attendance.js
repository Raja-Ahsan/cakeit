import axios from 'axios'

export const attendance = {
    namespaced: true,
    state: {
        lists: [],
        todayAttendance: [],
        currentAttendance: null
    },

    getters: {
        lists: function (state) {
            return state.lists;
        },
        todayAttendance: function (state) {
            return state.todayAttendance;
        },
        currentAttendance: function (state) {
            return state.currentAttendance;
        }
    },

    actions: {
        list: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.get('admin/employee-attendance', { params: payload }).then((res) => {
                    context.commit('lists', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        store: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('admin/employee-attendance', payload).then(res => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        update: function (context, payload) {
            return new Promise((resolve, reject) => {
                const { id, ...data } = payload;
                axios.put(`admin/employee-attendance/${id}`, data).then(res => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        delete: function (context, id) {
            return new Promise((resolve, reject) => {
                axios.delete(`admin/employee-attendance/${id}`).then(res => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        show: function (context, id) {
            return new Promise((resolve, reject) => {
                axios.get(`admin/employee-attendance/show/${id}`).then((res) => {
                    context.commit('currentAttendance', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        todayAttendance: function (context) {
            return new Promise((resolve, reject) => {
                axios.get('admin/employee-attendance/today').then((res) => {
                    context.commit('todayAttendance', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        markCheckIn: function (context, employeeId) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/employee-attendance/check-in/${employeeId}`).then(res => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },

        markCheckOut: function (context, employeeId) {
            return new Promise((resolve, reject) => {
                axios.post(`admin/employee-attendance/check-out/${employeeId}`).then(res => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        }
    },

    mutations: {
        lists: function (state, payload) {
            state.lists = payload
        },
        todayAttendance: function (state, payload) {
            state.todayAttendance = payload
        },
        currentAttendance: function (state, payload) {
            state.currentAttendance = payload
        }
    }
}
