<?php

namespace App\Controller;

use App\Helpers\ResponseHelper;
use Illuminate\Database\Capsule\Manager as Capsule;

class BuildingController extends BaseController
{
    /**
     * @param array $params
     * @return string
     */
    public function enter(array $params = []): string
    {
        $employeeData = [
            'full_name' => '',
            'departments' => []
        ];

        // Sanitization is handled by the ORM package when building the queries, no no need to perform it here.
        $query = Capsule::table('employees')
            ->select('employees.id', 'employees.first_name', 'employees.last_name', 'employees.email')
            ->where('employees.rfid', $params['rfid']);

        $employee = $query->first();

        $employeeData['full_name'] = sprintf('%s %s', $employee->first_name, $employee->last_name);

        if (is_null($employee)) {
            // TODO: Create and throw a proper exception with the appropriate code
            throw new \RuntimeException('Invalid RFID card!');
        }

        // Check the building we're trying to enter
        if (!isset($params['building_id'])) {
            // TODO: Create and throw a proper exception with the appropriate code
            throw new \RuntimeException('Missing building ID parameter!');
        }

        $employeeDepartmentIds = [];
        $employeeDepartments = Capsule::table('employee_departments')
            ->select(
                'departments.name as department_name',
                'departments.code as department_code',
                'departments.id as department_id'
            )
            ->join('departments', 'departments.id', '=', 'employee_departments.department_id')
            ->where('employee_departments.employee_id', $employee->id)
            ->get()
            ->toArray();

        foreach ($employeeDepartments as $employeeDepartment) {
            $employeeData['departments'][] = $employeeDepartment;
            $employeeDepartmentIds[] = $employeeDepartment->department_id;
        }

        $building = Capsule::table('buildings')->where('id', $params['building_id'])->first();

        if (is_null($building)) {
            // TODO: Create and throw a proper exception with the appropriate code
            throw new \RuntimeException('Invalid building ID');
        }

        // In case our employee has restricted access to certain departments, make sure the building we try to enter actually has these departments
        if (count($employeeDepartments) > 0) {
            $buildingDepartments = Capsule::table('building_departments')
                ->select(
                    'buildings.name as building_name',
                    'departments.name as department_name',
                    'departments.code as department_code')
                ->join('buildings', 'buildings.id', '=', 'building_departments.building_id')
                ->join('departments', 'departments.id', '=', 'building_departments.department_id')
                ->where('building_departments.building_id', $building->id)
                ->whereIn('departments.id', $employeeDepartmentIds)
                ->get()
                ->toArray();

            if (count($buildingDepartments) < 1) {
                throw new \RuntimeException('The building you are trying to enter does not have the departments that you are assigned to!');
            }
        }

        return ResponseHelper::formatResponseOk($employeeData);
    }
}