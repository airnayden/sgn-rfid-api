<?php

namespace App\DataTransferObjects;

class EmployeeObject extends BaseObject
{
    /**
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string|null $rfid
     * @param string|null $id
     */
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public ?string $rfid = null,
        public ?string $id = null
    ) {

    }
}