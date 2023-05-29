<?php

namespace App\Actions;

use App\DataTransferObjects\DepartmentObject;
use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as Capsule;

class CreateOrUpdateDepartmentRecord
{
    /**
     * Creates or updates a building record
     *
     * TODO: Check if department with the same code already exists
     *
     * @param DepartmentObject $object
     * @return int
     */
    public static function execute(DepartmentObject $object): int
    {
        $dataTemplate = [
            'name' => $object->name,
            'code' => $object->code,
            'updated_at' => Carbon::now()->toDateTimeString()
        ];

        if (!is_null($object->id)) {
            $id = $object->id;

            Capsule::table('departments')
                ->where('id', $id)
                ->update($dataTemplate);
        } else {
            $id = Capsule::table('departments')->insertGetId(
                array_merge(
                    $dataTemplate,
                    [
                        'created_at' => Carbon::now()->toDateTimeString()
                    ]
                )
            );
        }

        return $id;
    }
}