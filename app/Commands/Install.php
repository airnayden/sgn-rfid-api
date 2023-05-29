<?php

namespace App\Command;

use App\Actions\AttachDepartmentToBuilding;
use App\Actions\AttachDepartmentToEmployee;
use App\Actions\CreateOrUpdateBuildingRecord;
use App\Actions\CreateOrUpdateDepartmentRecord;
use App\Actions\CreateOrUpdateEmployeeRecord;
use App\DataTransferObjects\BuildingObject;
use App\DataTransferObjects\DepartmentObject;
use App\DataTransferObjects\EmployeeObject;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

class Install extends BaseCommand
{
    /**
     * @return void
     */
    public function handle(): void
    {
        if (file_exists(__DIR__.'/../../install.lock')) {
            $this->line('Already installed!');
            return;
        }

        $demoDataPath = __DIR__.'/../../example_data/demoData.json';

        if (!file_exists($demoDataPath)) {
            $this->line('Missing demo data JSON file!!');
            return;
        }

        $demoData = json_decode(file_get_contents($demoDataPath), true);

        try {
            // Create `employees` table
            $this->line('Creating data tables...');

            Capsule::schema()->create('employees', function(Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('first_name');
                $table->string('last_name');
                $table->string('email')->unique();
                $table->string('rfid')->unique();
                $table->timestamps();
            });

            // Create `buildings` table
            Capsule::schema()->create('buildings', function(Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('country_iso2_code');
                $table->timestamps();
            });

            // Create `departments` table
            Capsule::schema()->create('departments', function(Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('code')->unique();
                $table->timestamps();
            });

            // Create pivot tables
            $this->line('Creating pivot tables...');

            Capsule::schema()->create('employee_departments', function(Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('employee_id');
                $table->unsignedBigInteger('department_id');
            });

            Capsule::schema()->create('building_departments', function(Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('building_id');
                $table->unsignedBigInteger('department_id');
            });

            // Add constraints
            $this->line('Adding constraints....');

            Capsule::schema()->table('employee_departments', function (Blueprint $table) {
                $table->foreign('employee_id')->references('id')->on('employees');
                $table->foreign('department_id')->references('id')->on('departments');
            });

            Capsule::schema()->table('building_departments', function (Blueprint $table) {
                $table->foreign('building_id')->references('id')->on('buildings');
                $table->foreign('department_id')->references('id')->on('departments');
            });

            // Insert demo data
            $this->line('Inserting demo data...');

            // Map department code to an object
            $departmentCodeToObject = [];

            // Process Departments
            $this->line('Inserting departments...');

            foreach ($demoData['departments'] as $department) {
                $departmentObject = new DepartmentObject($department['name'], $department['code']);
                $departmentId = CreateOrUpdateDepartmentRecord::execute($departmentObject);

                $departmentObject->id = $departmentId;

                if (!isset($departmentCodeToObject[$department['code']])) {
                    $departmentCodeToObject[$department['code']] = $departmentObject;
                }
            }

            // Process buildings
            $this->line('Inserting buildings and attaching them to departments...');

            foreach ($demoData['buildings'] as $building) {
                $buildingObject = new BuildingObject($building['name'], $building['country_iso2_code']);
                $buildingId = CreateOrUpdateBuildingRecord::execute($buildingObject);

                $buildingObject->id = $buildingId;

                // Attach the building to the departments
                foreach ($building['departments'] as $buildingDepartment) {
                    if (isset($departmentCodeToObject[$buildingDepartment])) {
                        AttachDepartmentToBuilding::execute($buildingObject, $departmentCodeToObject[$buildingDepartment]);
                    }
                }
            }

            // Process employees
            $this->line('Inserting employees and attaching them to departments...');

            foreach ($demoData['employees'] as $employee) {
                $employeeObject = new EmployeeObject(
                    $employee['firstName'],
                    $employee['lastName'],
                    $employee['email']
                );
                $employeeId = CreateOrUpdateEmployeeRecord::execute($employeeObject);

                $employeeObject->id = $employeeId;

                // Attach the building to the departments
                foreach ($employee['departments'] as $employeeDepartment) {
                    if (isset($departmentCodeToObject[$employeeDepartment])) {
                        AttachDepartmentToEmployee::execute($employeeObject, $departmentCodeToObject[$employeeDepartment]);
                    }
                }
            }

            // Lock setup
            file_put_contents('install.lock', 'done');

            $this->line('Schema created! Don\'t try this again!');
        } catch (\Throwable $e) {
            $this->line('Error while running the installation! - ' . $e->getMessage());
        }
    }
}