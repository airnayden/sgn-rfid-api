<?php

namespace App\DataTransferObjects;

class DepartmentObject extends BaseObject
{
    /**
     * @param string $name
     * @param string $code
     * @param string|null $id
     */
    public function __construct(
        public string $name,
        public string $code,
        public ?string $id = null,
    ) {

    }
}