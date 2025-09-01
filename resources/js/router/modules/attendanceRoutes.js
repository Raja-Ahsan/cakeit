import EmployeeAttendanceComponent from "../../components/admin/attendance/EmployeeAttendanceComponent";

export default [
    {
        path: "/admin/attendance",
        component: EmployeeAttendanceComponent,
        name: "admin.attendance",
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "employee-attendance",
            breadcrumb: "attendance",
        },
    }
];
