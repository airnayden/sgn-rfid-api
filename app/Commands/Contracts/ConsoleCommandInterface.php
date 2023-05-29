<?php

namespace App\Command\Contracts;

interface ConsoleCommandInterface
{
    /**
     * Executes the command logic
     *
     * @return void
     */
    public function handle(): void;
}