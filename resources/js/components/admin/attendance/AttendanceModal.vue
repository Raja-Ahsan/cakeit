<template>
    <div class="modal-overlay" @click="closeModal">
        <div class="modal-content" @click.stop>
            <div class="modal-header">
                <h4>{{ isEditing ? $t('button.edit_attendance') : $t('button.add_attendance') }}</h4>
                <button @click="closeModal" class="modal-close">
                    <i class="lab lab-times"></i>
                </button>
            </div>

            <div class="modal-body">
                <form @submit.prevent="saveAttendance">
                    <div class="form-row">
                        <div class="form-col-12">
                            <label class="db-field-title required">{{ $t("label.employee") }}</label>
                                                    <vue-select 
                            v-model="form.employee_id" 
                            :options="formattedEmployees" 
                            label-by="name" 
                            value-by="id"
                            placeholder="Select Employee"
                            class="db-field-control"
                            :class="errors.employee_id ? 'is-invalid' : ''"
                            :disabled="isEditing"
                        />
                        <small class="text-muted">Available employees: {{ employees.length }}</small>
                        </div>

                        <div class="form-col-12">
                            <label class="db-field-title required">{{ $t("label.date") }}</label>
                            <input 
                                v-model="form.date" 
                                type="date" 
                                class="db-field-control"
                                :class="errors.date ? 'invalid' : ''"
                                :max="today"
                            />
                            <small class="db-field-alert" v-if="errors.date">{{ errors.date[0] }}</small>
                        </div>

                        <div class="form-col-12">
                            <label class="db-field-title required">{{ $t("label.status") }}</label>
                            <vue-select 
                                v-model="form.status" 
                                :options="statusOptions" 
                                label-by="label"
                                value-by="value"
                                placeholder="Select Status"
                                class="db-field-control"
                                :class="errors.status ? 'is-invalid' : ''"
                            />
                            <small class="db-field-alert" v-if="errors.status">{{ errors.status[0] }}</small>
                        </div>

                        <div class="form-col-6" v-if="form.status === 'present'">
                            <label class="db-field-title required">{{ $t("label.check_in") }}</label>
                            <input 
                                v-model="form.check_in" 
                                type="time" 
                                step="60"
                                class="db-field-control"
                                :class="errors.check_in ? 'invalid' : ''"
                                placeholder="HH:MM"
                            />
                            <small class="text-muted">Format: HH:MM (will be converted to HH:MM:SS)</small>
                            <small class="db-field-alert" v-if="errors.check_in">{{ errors.check_in[0] }}</small>
                        </div>

                        <div class="form-col-6" v-if="form.status === 'present'">
                            <label class="db-field-title">{{ $t("label.check_out") }}</label>
                            <input 
                                v-model="form.check_out" 
                                type="time" 
                                step="60"
                                class="db-field-control"
                                :class="errors.check_out ? 'invalid' : ''"
                                placeholder="HH:MM"
                            />
                            <small class="text-muted">Format: HH:MM (will be converted to HH:MM:SS)</small>
                            <small class="db-field-alert" v-if="errors.check_out">{{ errors.check_out[0] }}</small>
                        </div>

                        <div class="form-col-12">
                            <label class="db-field-title">{{ $t("label.notes") }}</label>
                            <textarea 
                                v-model="form.notes" 
                                class="db-field-control"
                                :class="errors.notes ? 'invalid' : ''"
                                rows="3"
                                placeholder="Optional notes about the attendance"
                            ></textarea>
                            <small class="db-field-alert" v-if="errors.notes">{{ errors.notes[0] }}</small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" @click="closeModal" class="db-btn text-white bg-secondary bg-[#874215]">
                            {{ $t("button.cancel") }}
                        </button>
                        <button type="submit" class="db-btn text-white bg-primary" :disabled="loading.isActive">
                            <i v-if="loading.isActive" class="lab lab-spinner"></i>
                            <span>{{ isEditing ? $t("button.update") : $t("button.save") }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import alertService from "../../../services/alertService";

export default {
    name: "AttendanceModal",
    props: {
        attendance: {
            type: Object,
            default: null
        },
        employees: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            form: {
                employee_id: null,
                date: null,
                status: 'present',
                check_in: null,
                check_out: null,
                notes: ''
            },
            errors: {}
        };
    },
    computed: {
        isEditing() {
            return this.attendance !== null;
        },
        today() {
            return new Date().toISOString().split('T')[0];
        },
        statusOptions() {
            return [
                { label: 'Present', value: 'present' },
                { label: 'Absent', value: 'absent' },
                { label: 'Late', value: 'late' },
                { label: 'Half Day', value: 'half_day' },
                { label: 'Leave', value: 'leave' }
            ];
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
    watch: {
        attendance: {
            handler(newVal) {
                if (newVal) {
                    this.populateForm();
                } else {
                    this.resetForm();
                }
            },
            immediate: true
        },
        employees: {
            handler(newEmployees) {
                console.log('AttendanceModal - Employees received:', newEmployees);
                console.log('Employee count:', newEmployees.length);
                if (newEmployees.length > 0) {
                    console.log('First employee:', newEmployees[0]);
                }
            },
            immediate: true
        },
        'form.status'(newStatus) {
            if (newStatus !== 'present') {
                this.form.check_in = null;
                this.form.check_out = null;
            }
        }
    },
    mounted() {
        console.log('AttendanceModal mounted');
        console.log('Initial employees prop:', this.employees);
        console.log('Initial attendance prop:', this.attendance);
    },
    methods: {
        populateForm() {
            if (this.attendance) {
                this.form = {
                    employee_id: this.attendance.employee_id,
                    date: this.attendance.date,
                    status: this.attendance.status,
                    check_in: this.attendance.check_in,
                    check_out: this.attendance.check_out,
                    notes: this.attendance.notes || ''
                };
            }
        },
        resetForm() {
            this.form = {
                employee_id: null,
                date: this.today,
                status: 'present',
                check_in: null,
                check_out: null,
                notes: ''
            };
            this.errors = {};
        },
        async saveAttendance() {
            try {
                this.loading.isActive = true;
                this.errors = {};

                // Validate form before submission
                if (!this.validateForm()) {
                    return;
                }

                // Format time to HH:MM:SS format before sending
                const formData = { ...this.form };
                if (formData.check_in) {
                    formData.check_in = this.formatTimeToSeconds(formData.check_in);
                }
                if (formData.check_out) {
                    formData.check_out = this.formatTimeToSeconds(formData.check_out);
                }

                if (this.isEditing) {
                    await this.$store.dispatch('attendance/update', {
                        id: this.attendance.id,
                        ...formData
                    });
                    alertService.success('Attendance updated successfully');
                } else {
                    await this.$store.dispatch('attendance/store', formData);
                    alertService.success('Attendance created successfully');
                }

                this.$emit('saved');
            } catch (error) {
                if (error.response?.data?.errors) {
                    this.errors = error.response.data.errors;
                } else {
                    alertService.error(error.response?.data?.message || 'Failed to save attendance');
                }
            } finally {
                this.loading.isActive = false;
            }
        },
        closeModal() {
            this.$emit('close');
        },
        
        formatTimeToSeconds(timeString) {
            // Convert HH:MM to HH:MM:SS format
            if (timeString && timeString.includes(':')) {
                // If time is already in HH:MM:SS format, return as is
                if (timeString.split(':').length === 3) {
                    return timeString;
                }
                // If time is in HH:MM format, add :00 seconds
                if (timeString.split(':').length === 2) {
                    return timeString + ':00';
                }
            }
            return timeString;
        },
        
        validateForm() {
            this.errors = {};
            let isValid = true;
            
            // Validate required fields
            if (!this.form.employee_id) {
                this.errors.employee_id = ['Employee is required'];
                isValid = false;
            }
            
            if (!this.form.date) {
                this.errors.date = ['Date is required'];
                isValid = false;
            }
            
            if (!this.form.status) {
                this.errors.status = ['Status is required'];
                isValid = false;
            }
            
            // Validate time format for present status
            if (this.form.status === 'present') {
                if (!this.form.check_in) {
                    this.errors.check_in = ['Check-in time is required for present status'];
                    isValid = false;
                } else if (!this.isValidTimeFormat(this.form.check_in)) {
                    this.errors.check_in = ['Check-in time must be in HH:MM format'];
                    isValid = false;
                }
                
                // Check-out is optional but if provided, validate format
                if (this.form.check_out && !this.isValidTimeFormat(this.form.check_out)) {
                    this.errors.check_out = ['Check-out time must be in HH:MM format'];
                    isValid = false;
                }
            }
            
            return isValid;
        },
        
        isValidTimeFormat(timeString) {
            // Validate HH:MM format
            const timeRegex = /^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/;
            return timeRegex.test(timeString);
        }
    }
};
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #e9ecef;
}

.modal-header h4 {
    margin: 0;
    color: #333;
}

.modal-close {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: #6c757d;
}

.modal-close:hover {
    color: #333;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 20px;
    border-top: 1px solid #e9ecef;
}

.form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.form-col-6 {
    flex: 0 0 calc(50% - 10px);
}

.form-col-12 {
    flex: 0 0 100%;
}

@media (max-width: 768px) {
    .form-col-6 {
        flex: 0 0 100%;
    }
    
    .modal-content {
        width: 95%;
        margin: 10px;
    }
}
</style>
