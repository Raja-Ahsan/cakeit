<template>
    <LoadingComponent :props="loading" />

    <div id="attendance" class="db-card db-tab-div active">
        <div class="db-card-header">
            <h3 class="db-card-title">{{ $t("label.attendance_settings") }}</h3>
        </div>
        <div class="db-card-body">
            <!-- Current Attendance Manager -->
            <div class="current-manager mb-4">
                <h4>{{ $t("label.current_attendance_manager") }}</h4>
                <div class="manager-info" v-if="currentAssignee">
                    <div class="manager-details">
                        <div class="manager-name">{{ currentAssignee.employee.name }}</div>
                        <div class="manager-role">{{ currentAssignee.employee.role }}</div>
                        <div class="assigned-date">Assigned: {{ formatDate(currentAssignee.assigned_at) }}</div>
                    </div>
                    <button @click="removeManager" type="button" class="db-btn text-white bg-[#e60000]" :disabled="loading.isActive">
                        <i v-if="loading.isActive" class="lab lab-spinner"></i>
                        <i v-else class="lab lab-times"></i>
                        <span>{{ $t("button.remove_manager") }}</span>
                    </button>
                </div>
                <div class="no-manager" v-else>
                    <p class="text-muted">{{ $t("label.no_attendance_manager") }}</p>
                </div>
            </div>

            

            <!-- Assign New Manager -->
            <div class="assign-manager mb-4">
                <h4>{{ $t("label.assign_attendance_manager") }}</h4>
                <div class="form-row">
                    <div class="form-col-8">
                        <label class="db-field-title required">{{ $t("label.select_employee") }}</label>
                        <vue-select 
                            v-model="selectedEmployee" 
                            :options="employees" 
                            label-by="name" 
                            value-by="id"
                            placeholder="Select Employee"
                            class="db-field-control"
                            :class="errors.employee_id ? 'is-invalid' : ''"
                        />
                        <small class="db-field-alert" v-if="errors.employee_id">{{ errors.employee_id[0] }}</small>
                    </div>
                    <div class="form-col-4">
                        <label class="db-field-title">&nbsp;</label>
                        <button @click="assignManager" type="button" class="db-btn text-white bg-primary w-100" :disabled="loading.isActive">
                            <i v-if="loading.isActive" class="lab lab-spinner"></i>
                            <i v-else class="lab lab-check"></i>
                            <span>{{ $t("button.assign_manager") }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Assignment History -->
            <div class="assignment-history">
                <h4>{{ $t("label.assignment_history") }}</h4>
                <div class="table-responsive">
                    <table class="db-table">
                        <thead class="db-table-header">
                            <tr>
                                <th>{{ $t("label.employee") }}</th>
                                <th>{{ $t("label.assigned_by") }}</th>
                                <th>{{ $t("label.assigned_date") }}</th>
                                <th>{{ $t("label.status") }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="assignment in assignmentHistory" :key="assignment.id">
                                <td>
                                    <div class="employee-info">
                                        <div class="employee-name">{{ assignment.employee.name }}</div>
                                        <div class="employee-role">{{ assignment.employee.role }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="admin-info">
                                        <div class="admin-name">{{ assignment.assigned_by.name }}</div>
                                        <div class="admin-email">{{ assignment.assigned_by.email }}</div>
                                    </div>
                                </td>
                                <td>{{ formatDate(assignment.assigned_at) }}</td>
                                <td>
                                    <span class="badge" :class="assignment.is_active ? 'badge-success' : 'badge-secondary'">
                                        {{ assignment.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import alertService from "../../../services/alertService";
import axios from "axios";

export default {
    name: "AttendanceComponent",
    components: { LoadingComponent },
    data() {
        return {
            loading: {
                isActive: false,
            },
            currentAssignee: null,
            employees: [
                // Fallback data for testing
                { id: 1, name: 'Test Employee 1', role: 'Staff' },
                { id: 2, name: 'Test Employee 2', role: 'Manager' }
            ],
            assignmentHistory: [],
            selectedEmployee: null,
            errors: {}
        };
    },
    mounted() {
        this.loadData();
    },
    methods: {
        async loadData() {
            try {
                this.loading.isActive = true;
                // Load data sequentially to handle errors better
                await this.loadCurrentAssignee();
                await this.loadEmployees();
                await this.loadAssignmentHistory();
            } catch (error) {
                console.error('Failed to load attendance data:', error);
            } finally {
                this.loading.isActive = false;
            }
        },

        async loadCurrentAssignee() {
            try {
                const response = await this.$store.dispatch('attendanceAssignee/index');
                console.log('Full API response:', response);
                console.log('Response data:', response.data);
                console.log('Response data.data:', response.data.data);
                console.log('Current assignee:', response.data.data?.current_assignee);
                
                // Handle different possible response structures
                let currentAssignee = null;
                if (response.data && response.data.data) {
                    currentAssignee = response.data.data.current_assignee;
                } else if (response.data && response.data.current_assignee) {
                    currentAssignee = response.data.current_assignee;
                }
                
                this.currentAssignee = currentAssignee;
                
                if (this.currentAssignee) {
                    console.log('Successfully loaded current assignee:', this.currentAssignee);
                } else {
                    console.log('No current assignee found');
                    // For testing purposes, let's see if we can create a test assignee
                    console.log('Creating test assignee for debugging...');
                    this.currentAssignee = {
                        id: 1,
                        employee_id: 1,
                        employee: {
                            id: 1,
                            name: 'Test Employee',
                            role: 'Manager'
                        },
                        assigned_at: new Date().toISOString(),
                        is_active: true
                    };
                }
            } catch (error) {
                console.error('Failed to load current assignee:', error);
                console.error('Error details:', error.response?.data);
                // Set to null if no assignee exists
                this.currentAssignee = null;
            }
        },

        async loadEmployees() {
            try {
                const response = await this.$store.dispatch('employee/lists', {});
                this.employees = response.data.data;
            } catch (error) {
                console.error('Failed to load employees:', error);
                console.error('Error details:', error.response?.data);
                // Keep existing employees if any, otherwise use fallback
                if (!this.employees || this.employees.length === 0) {
                    this.employees = [
                        { id: 1, name: 'Test Employee 1', role: 'Staff' },
                        { id: 2, name: 'Test Employee 2', role: 'Manager' }
                    ];
                }
            }
        },

        async loadAssignmentHistory() {
            try {
                const response = await this.$store.dispatch('attendanceAssignee/index');
                this.assignmentHistory = response.data.data.assignee_history || [];
            } catch (error) {
                console.error('Failed to load assignment history:', error);
                console.error('Error details:', error.response?.data);
                // Set empty array as fallback
                this.assignmentHistory = [];
            }
        },

        async assignManager() {
            if (!this.selectedEmployee) {
                alertService.error('Please select an employee');
                return;
            }

            // Check if the selected employee is already the current manager
            if (this.currentAssignee && this.currentAssignee.employee_id === this.selectedEmployee) {
                alertService.warning('This employee is already the current attendance manager');
                return;
            }

            try {
                this.loading.isActive = true;
                this.errors = {};
                
                const assignResponse = await this.$store.dispatch('attendanceAssignee/store', {
                    employee_id: this.selectedEmployee
                });
                
                console.log('Assignment response:', assignResponse);
                
                alertService.success('Attendance manager assigned successfully');
                this.selectedEmployee = null;
                
                // Reload data to get updated current assignee
                console.log('Reloading data after assignment...');
                await this.loadData();
            } catch (error) {
                console.error('Assignment error:', error);
                
                if (error.response?.data?.errors) {
                    this.errors = error.response.data.errors;
                    // Show specific field errors
                    if (error.response.data.errors.employee_id) {
                        alertService.error(error.response.data.errors.employee_id[0]);
                    }
                } else if (error.response?.data?.message) {
                    alertService.error(error.response.data.message);
                } else if (error.message) {
                    alertService.error(error.message);
                } else {
                    alertService.error('Failed to assign attendance manager. Please try again.');
                }
            } finally {
                this.loading.isActive = false;
            }
        },

        async removeManager() {
            if (!this.currentAssignee) {
                alertService.warning('No attendance manager is currently assigned');
                return;
            }

            if (!confirm('Are you sure you want to remove the current attendance manager?')) {
                return;
            }

            try {
                this.loading.isActive = true;
                await this.$store.dispatch('attendanceAssignee/destroy');
                
                alertService.success('Attendance manager removed successfully');
                await this.loadData();
            } catch (error) {
                console.error('Remove manager error:', error);
                
                if (error.response?.data?.message) {
                    alertService.error(error.response.data.message);
                } else if (error.message) {
                    alertService.error(error.message);
                } else {
                    alertService.error('Failed to remove attendance manager. Please try again.');
                }
            } finally {
                this.loading.isActive = false;
            }
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString();
        },

        async testAssignment() {
            console.log('Testing assignment process...');
            console.log('Current assignee before test:', this.currentAssignee);
            console.log('Employees available:', this.employees);
            
            // Test with first available employee
            if (this.employees.length > 0) {
                this.selectedEmployee = this.employees[0].id;
                console.log('Selected employee for test:', this.selectedEmployee);
                await this.assignManager();
            } else {
                alertService.error('No employees available for testing');
            }
        },

        async debugAPI() {
            try {
                console.log('Testing debug API...');
                const response = await axios.get('/api/admin/attendance-assignee/debug');
                console.log('Debug API response:', response.data);
                alertService.success('Debug data logged to console');
            } catch (error) {
                console.error('Debug API error:', error);
                alertService.error('Debug API failed: ' + error.message);
            }
        },

        async testAPI() {
            try {
                console.log('Testing basic API...');
                const response = await axios.get('/api/admin/attendance-assignee/test');
                console.log('Test API response:', response.data);
                alertService.success('Test API working: ' + response.data.message);
            } catch (error) {
                console.error('Test API error:', error);
                alertService.error('Test API failed: ' + error.message);
            }
        }
    }
};
</script>

<style scoped>
.current-manager, .assign-manager, .assignment-history {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.manager-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 15px;
    border-radius: 6px;
    border: 1px solid #dee2e6;
}

.manager-details {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.manager-name {
    font-weight: 600;
    font-size: 16px;
}

.manager-role {
    color: #6c757d;
    font-size: 14px;
}

.assigned-date {
    color: #6c757d;
    font-size: 12px;
}

.no-manager {
    background: white;
    padding: 20px;
    border-radius: 6px;
    border: 1px solid #dee2e6;
    text-align: center;
}

.employee-info, .admin-info {
    display: flex;
    flex-direction: column;
}

.employee-name, .admin-name {
    font-weight: 600;
}

.employee-role, .admin-email {
    font-size: 12px;
    color: #6c757d;
}

.form-row {
    display: flex;
    gap: 20px;
    align-items: end;
}

.form-col-4 {
    flex: 0 0 calc(33.333% - 13.333px);
}

.form-col-8 {
    flex: 0 0 calc(66.667% - 13.333px);
}

/* Disabled button styles */
.db-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.db-btn:disabled:hover {
    transform: none;
}

/* Loading spinner animation */
.lab-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
    }
    
    .form-col-4, .form-col-8 {
        flex: 0 0 100%;
    }
    
    .manager-info {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
}
</style>
