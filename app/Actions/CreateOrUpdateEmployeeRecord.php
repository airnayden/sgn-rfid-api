<?php

namespace App\Actions;

use App\DataTransferObjects\EmployeeObject;
use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as Capsule;

class CreateOrUpdateEmployeeRecord
{
    /**
     * Creates or updates an employee record and attaches it to departments
     *
     * @param EmployeeObject $object
     * @return int
     */
    public static function execute(EmployeeObject $object): int
    {
        $dataTemplate = [
            'first_name' => $object->firstName,
            'last_name' => $object->lastName,
            'email' => $object->email,
            'rfid' => $object->rfid ?? md5(random_bytes(20)),
            'updated_at' => Carbon::now()->toDateTimeString()
        ];

        if (!is_null($object->id)) {
            $id = $object->id;

            Capsule::table('employees')
                ->where('id', $id)
                ->update($dataTemplate);
        } else {
            $id = Capsule::table('employees')->insertGetId(
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