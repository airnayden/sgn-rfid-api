<?php

namespace App\Actions;

use App\DataTransferObjects\BuildingObject;
use App\DataTransferObjects\DepartmentObject;
use Illuminate\Database\Capsule\Manager as Capsule;

class AttachDepartmentToBuilding
{
    /**
     * Attaches department to an employee
     *
     * @param BuildingObject $building
     * @param DepartmentObject $department
     * @return void
     */
    public static function execute(BuildingObject $building, DepartmentObject $department): void
    {
        // Check if we have a valid building
        if (is_null($building->id)) {
            // TODO: Create and throw a proper exception with the appropriate code
            throw new \RuntimeException('Building record does not exist yet!');
        }

        // Check if we have a valid department record
        if (is_null($department->id)) {
            // TODO: Create and throw a proper exception with the appropriate code
            throw new \RuntimeException('Department record does not exist yet!');
        }

        $exists = Capsule::table('building_departments')->where([
            'building_id' => $building->id,
            'department_id' => $department->id
        ])->exists();

        if (!$exists) {
            Capsule::table('building_departments')->insert([
                'building_id' => $building->id,
                'department_id' => $department->id
            ]);
        }
    }
}