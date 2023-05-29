<?php

namespace App\Actions;

use App\DataTransferObjects\BuildingObject;
use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as Capsule;

class CreateOrUpdateBuildingRecord
{
    /**
     * Creates or updates a building record
     *
     * TODO: Check if building with the name / country code combination already exists
     *
     * @param BuildingObject $object
     * @return int
     */
    public static function execute(BuildingObject $object): int
    {
        $dataTemplate = [
            'name' => $object->name,
            'country_iso2_code' => $object->countryIso2Code,
            'updated_at' => Carbon::now()->toDateTimeString()
        ];

        if (!is_null($object->id)) {
            $id = $object->id;

            Capsule::table('buildings')
                ->where('id', $id)
                ->update($dataTemplate);
        } else {
            $id = Capsule::table('buildings')->insertGetId(
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