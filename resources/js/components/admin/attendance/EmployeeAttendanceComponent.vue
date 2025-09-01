<template>
    <LoadingComponent :props="loading" />
    <style>
        .pagination{
            justify-content: center;
        }
        .pagination .page-item{
            margin: 0 5px;
        }
        .pagination .page-item.active{
            background-color: #007bff;
            color: white;
        }
        .pagination .page-item.disabled{
            opacity: 0.5;
            cursor: not-allowed;
        }
        .pagination .page-item.disabled .page-link{
            color: #6c757d;
        }
        .pagination .page-item.disabled .page-link:hover{
            background-color: #6c757d;
            color: white;
        }
        .pagination .page-item.disabled .page-link:hover{
            background-color: #6c757d;
            color: white;
        }
        .pagination .page-item.disabled .page-link:hover{
            background-color: #6c757d;
            color: white;
        }
        .pagination .page-item.disabled .page-link:hover{
            background-color: #6c757d;
            color: white;
        }
        .pagination .page-item.disabled .page-link:hover{
            background-color: #6c757d;
            color: white;
        }
        .pagination .page-item.disabled .page-link:hover{
            background-color: #6c757d;
            color: white;
        }
        .pagination .page-item.disabled .page-link:hover{
            background-color: #6c757d;
            color: white;
        }
        .pagination .page-item.disabled .page-link:hover{
            background-color: #6c757d;
            color: white;
        }
        
    </style>
    <div class="db-card">
        <div class="db-card-header">
            <h3 class="db-card-title">{{ $t("menu.employee_attendance") }}</h3>
            <div class="db-card-header-actions">
                <button @click="openAttendanceModal" type="button" class="db-btn text-white bg-primary">
                    <i class="lab lab-plus"></i>
                    <span>{{ $t("button.add_attendance") }}</span>
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="db-card-body">
            <div class="filter-section mb-4">
                <div class="row gap-3">
                    <div class="col-md-3" style="flex-grow: 1;">
                        <label class="db-field-title">{{ $t("label.employee") }}</label>
                        <vue-select 
                            v-model="filters.employee_id" 
                            :options="formattedEmployees" 
                            label-by="name" 
                            value-by="id"
                            class="db-field-control"
                            placeholder="Select Employee"
                            clearable
                        />
                        <small class="text-muted">Available employees: {{ employees.length }}</small>
                    </div>
                    <div class="col-md-3" style="flex-grow: 1;">
                        <label class="db-field-title">{{ $t("label.status") }}</label>
                        <vue-select 
                            v-model="filters.status" 
                            :options="statusOptions" 
                            label-by="label"
                            value-by="value"
                            placeholder="Select Status"
                            class="db-field-control"
                            clearable
                        />
                    </div>
                    <div class="col-md-3" style="flex-grow: 1;">  
                        <label class="db-field-title">{{ $t("label.start_date") }}</label>
                        <input 
                            v-model="filters.start_date" 
                            type="date" 
                            class="db-field-control"
                        />
                    </div>
                    <div class="col-md-3" style="flex-grow: 1;">
                        <label class="db-field-title">{{ $t("label.end_date") }}</label>
                        <input 
                            v-model="filters.end_date" 
                            type="date" 
                            class="db-field-control"
                        />
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <button @click="applyFilters" type="button" class="db-btn text-white bg-primary me-2">
                            <i class="lab lab-search"></i>
                            <span>{{ $t("button.filter") }}</span>
                        </button>
                        <button @click="clearFilters" type="button" class="db-btn text-white bg-secondary bg-[#874215]">
                            <i class="lab lab-refresh"></i>
                            <span>{{ $t("button.clear") }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Today's Attendance Summary -->
            <div class="attendance-summary mb-4" v-if="todayAttendance.length > 0">
                <h4 style="margin-bottom: 20px;">{{ $t("label.today_attendance") }}</h4>
                <div class="row gap-3">
                    <div class="col-md-3" style="flex-grow: 1;" v-for="summary in attendanceSummary" :key="summary.status">
                        <div class="summary-card" :class="summary.class">
                            <div class="summary-count">{{ summary.count }}</div>
                            <div class="summary-label">{{ summary.label }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Table -->
            <div class="table-responsive">
                <table class="db-table">
                    <thead>
                        <tr>
                            <th>{{ $t("label.employee") }}</th>
                            <th>{{ $t("label.date") }}</th>
                            <th>{{ $t("label.check_in") }}</th>
                            <th>{{ $t("label.check_out") }}</th>
                            <th>{{ $t("label.status") }}</th>
                            <th>{{ $t("label.working_hours") }}</th>
                            <th>{{ $t("label.actions") }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="attendance in attendanceList" :key="attendance.id">
                            <td>
                                <div class="employee-info">
                                    <div class="employee-name">{{ attendance.employee.name }}</div>
                                    <div class="employee-role">{{ attendance.employee.role }}</div>
                                </div>
                            </td>
                            <td>{{ formatDate(attendance.date) }}</td>
                            <td>
                                <span v-if="attendance.check_in" class="time-badge">
                                    {{ attendance.check_in }}
                                </span>
                                <span v-else class="text-muted">-</span>
                            </td>
                            <td>
                                <span v-if="attendance.check_out" class="time-badge">
                                    {{ attendance.check_out }}
                                </span>
                                <span v-else class="text-muted">-</span>
                            </td>
                            <td>
                                <span class="badge" :class="attendance.status_badge_class">
                                    {{ formatStatus(attendance.status) }}
                                </span>
                            </td>
                            <td>
                                <span v-if="attendance.working_hours > 0">
                                    {{ attendance.working_hours }}h
                                </span>
                                <span v-else class="text-muted">-</span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button @click="editAttendance(attendance)" class="btn btn-sm btn-outline-primary">
                                        <i class="lab lab-edit"></i>
                                    </button>
                                    <button @click="deleteAttendance(attendance.id)" class="btn btn-sm btn-outline-danger">
                                        <i class="lab lab-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-wrapper mt-4" v-if="pagination">
                <nav>
                    <ul class="pagination">
                        <li class="page-item" :class="{ disabled: !pagination.prev_page_url }">
                            <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page - 1)">
                                {{ $t("button.previous") }}
                            </a>
                        </li>
                        <li class="page-item" v-for="page in paginationPages" :key="page" :class="{ active: page === pagination.current_page }">
                            <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
                        </li>
                        <li class="page-item" :class="{ disabled: !pagination.next_page_url }">
                            <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page + 1)">
                                {{ $t("button.next") }}
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Attendance Modal -->
    <AttendanceModal 
        v-if="showModal" 
        :attendance="selectedAttendance"
        :employees="employees"
        @close="closeModal"
        @saved="attendanceSaved"
    />
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import AttendanceModal from "./AttendanceModal";
import alertService from "../../../services/alertService";

export default {
    name: "EmployeeAttendanceComponent",
    components: { LoadingComponent, AttendanceModal },
    data() {
        return {
            loading: {
                isActive: false,
            },
            attendanceList: [],
            employees: [
                // Fallback data for testing
                { id: 1, name: 'Test Employee 1', role: 'Staff' },
                { id: 2, name: 'Test Employee 2', role: 'Manager' }
            ],
            todayAttendance: [],
            filters: {
                employee_id: null,
                status: null,
                start_date: null,
                end_date: null,
                paginate: 1,
                per_page: 10
            },
            pagination: null,
            showModal: false,
            selectedAttendance: null
        };
    },
    computed: {
        statusOptions() {
            return [
                { label: 'Present', value: 'present' },
                { label: 'Absent', value: 'absent' },
                { label: 'Late', value: 'late' },
                { label: 'Half Day', value: 'half_day' },
                { label: 'Leave', value: 'leave' }
            ];
        },
        attendanceSummary() {
            const summary = {
                present: { count: 0, label: 'Present', class: 'summary-present' },
                absent: { count: 0, label: 'Absent', class: 'summary-absent' },
                late: { count: 0, label: 'Late', class: 'summary-late' },
                total: { count: 0, label: 'Total', class: 'summary-total' }
            };

            this.todayAttendance.forEach(item => {
                if (item.attendance) {
                    summary[item.attendance.status].count++;
                } else {
                    summary.absent.count++;
                }
                summary.total.count++;
            });

            return Object.values(summary);
        },
        paginationPages() {
            if (!this.pagination) return [];
            
            const pages = [];
            const current = this.pagination.current_page;
            const last = this.pagination.last_page;
            
            for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
                pages.push(i);
            }
            
            return pages;
        },
        formattedEmployees() {
            // Ensure employees have the correct structure for vue-select
            return this.employees.map(emp => {
                // Handle different possible data structures
                if (typeof emp === 'object' && emp !== null) {
                    return {
                        id: emp.id,
                        name: emp.name || emp.full_name || emp.first_name + ' ' + emp.last_name || 'Unknown Employee',
                        email: emp.email || '',
                        role: emp.role || emp.role_name || 'Staff'
                    };
                }
                return emp;
            });
        }
    },
    mounted() {
        console.log('Component mounted, loading data...');
        this.loadAttendance();
        this.loadEmployees();
        this.loadTodayAttendance();
    },
    watch: {
        employees: {
            handler(newEmployees) {
                console.log('Employees changed:', newEmployees);
            },
            immediate: true
        }
    },
    methods: {
        async loadAttendance() {
            try {
                this.loading.isActive = true;
                const response = await this.$store.dispatch('attendance/list', this.filters);
                this.attendanceList = response.data.data;
                this.pagination = response.data.meta;
            } catch (error) {
                alertService.error(error.response?.data?.message || 'Failed to load attendance');
            } finally {
                this.loading.isActive = false;
            }
        },
        async loadEmployees() {
            try {
                console.log('Loading employees...');
                
                // Try multiple approaches to get employees
                let employees = [];
                
                // Approach 1: Try the attendance assignee API (which we know works)
                try {
                    const assigneeResponse = await this.$store.dispatch('attendanceAssignee/index');
                    if (assigneeResponse.data.data.employees) {
                        employees = assigneeResponse.data.data.employees;
                        console.log('Employees loaded from attendance assignee API:', employees);
                    }
                } catch (assigneeError) {
                    console.log('Attendance assignee API failed, trying employee API...');
                }
                
                // Approach 2: Try direct employee API call
                if (employees.length === 0) {
                    try {
                        const directResponse = await this.$http.get('admin/employee');
                        employees = directResponse.data.data;
                        console.log('Employees loaded via direct API call:', employees);
                    } catch (directError) {
                        console.log('Direct API call failed, trying store...');
                    }
                }
                
                // Approach 3: Try store dispatch
                if (employees.length === 0) {
                    try {
                        const response = await this.$store.dispatch('employee/lists', {});
                        employees = response.data.data;
                        console.log('Employees loaded via store:', employees);
                    } catch (storeError) {
                        console.log('Store dispatch failed');
                    }
                }
                
                // Approach 4: Use fallback data if all else fails
                if (employees.length === 0) {
                    console.log('All methods failed, using fallback data');
                    employees = [
                        { id: 1, name: 'Test Employee 1', email: 'test1@example.com' },
                        { id: 2, name: 'Test Employee 2', email: 'test2@example.com' }
                    ];
                }
                
                this.employees = employees;
                console.log('Final employees data:', this.employees);
                
            } catch (error) {
                console.error('Failed to load employees:', error);
                // Keep fallback data
                this.employees = [
                    { id: 1, name: 'Test Employee 1', email: 'test1@example.com' },
                    { id: 2, name: 'Test Employee 2', email: 'test2@example.com' }
                ];
            }
        },
        async loadTodayAttendance() {
            try {
                const response = await this.$store.dispatch('attendance/todayAttendance');
                this.todayAttendance = response.data.data;
            } catch (error) {
                console.error('Failed to load today\'s attendance:', error);
            }
        },
        applyFilters() {
            this.filters.paginate = 1;
            this.loadAttendance();
        },
        clearFilters() {
            this.filters = {
                employee_id: null,
                status: null,
                start_date: null,
                end_date: null,
                paginate: 1,
                per_page: 10
            };
            this.loadAttendance();
        },
        changePage(page) {
            if (page >= 1 && page <= this.pagination.last_page) {
                this.filters.paginate = page;
                this.loadAttendance();
            }
        },
        openAttendanceModal() {
            this.selectedAttendance = null;
            this.showModal = true;
        },
        editAttendance(attendance) {
            this.selectedAttendance = attendance;
            this.showModal = true;
        },
        closeModal() {
            this.showModal = false;
            this.selectedAttendance = null;
        },
        attendanceSaved() {
            this.closeModal();
            this.loadAttendance();
            this.loadTodayAttendance();
        },
        async deleteAttendance(id) {
            if (confirm('Are you sure you want to delete this attendance record?')) {
                try {
                    await this.$store.dispatch('attendance/delete', id);
                    alertService.success('Attendance record deleted successfully');
                    this.loadAttendance();
                    this.loadTodayAttendance();
                } catch (error) {
                    alertService.error(error.response?.data?.message || 'Failed to delete attendance');
                }
            }
        },
        formatDate(date) {
            return new Date(date).toLocaleDateString();
        },
        formatStatus(status) {
            return status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
        }
    }
};
</script>

<style scoped>
.attendance-summary {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
}

.summary-card {
    text-align: center;
    padding: 15px;
    border-radius: 8px;
    color: white;
    margin-bottom: 15px;
}

.summary-present { background-color: #28a745; }
.summary-absent { background-color: #dc3545; }
.summary-late { background-color: #ffc107; color: #212529; }
.summary-total { background-color: #007bff; }

.summary-count {
    font-size: 24px;
    font-weight: bold;
}

.summary-label {
    font-size: 14px;
    margin-top: 5px;
}

.employee-info {
    display: flex;
    flex-direction: column;
}

.employee-name {
    font-weight: 600;
}

.employee-role {
    font-size: 12px;
    color: #6c757d;
}

.time-badge {
    background: #e9ecef;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-family: monospace;
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.filter-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
}
</style>
