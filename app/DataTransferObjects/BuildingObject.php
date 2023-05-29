<?php

namespace App\DataTransferObjects;

class BuildingObject extends BaseObject
{
    /**
     * @param string $name
     * @param string $countryIso2Code
     * @param string|null $id
     */
    public function __construct(
        public string $name,
        public string $countryIso2Code,
        public ?string $id = null
    ) {

    }
}