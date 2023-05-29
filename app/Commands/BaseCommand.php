<?php

namespace App\Command;

use App\Command\Contracts\ConsoleCommandInterface;

abstract class BaseCommand implements ConsoleCommandInterface
{
    /**
     * @param string $lineToPrint
     * @return void
     */
    public function line(string $lineToPrint = ''): void
    {
        print $lineToPrint;
        print "\n";
    }
}