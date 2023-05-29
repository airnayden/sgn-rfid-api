<?php

namespace App\Actions;

use App\DataTransferObjects\DepartmentObject;
use App\DataTransferObjects\EmployeeObject;
use Carbon\Carbon;
use http\Exception\RuntimeException;
use Illuminate\Database\Capsule\Manager as Capsule;

class AttachDepartmentToEmployee
{
    /**
     * Attaches department to an employee
     *
     * @param EmployeeObject $employee
     * @param DepartmentObject $department
     * @return void
     */
    public static function execute(EmployeeObject $employee, DepartmentObject $department): void
    {
        // Check if we have a valid employee record
        if (is_null($employee->id)) {
            throw new \RuntimeException('Employee record does not exist yet!');
        }

        // Check if we have a valid department record
        if (is_null($department->id)) {
            throw new \RuntimeException('Department record does not exist yet!');
        }

        $exists = Capsule::table('employee_departments')->where([
            'employee_id' => $employee->id,
            'department_id' => $department->id
        ])->exists();

        if (!$exists) {
            Capsule::table('employee_departments')->insert([
                'employee_id' => $employee->id,
                'department_id' => $department->id
            ]);
        }
    }
}