<?php

namespace App\Console\Storages;

use Illuminate\Support\Collection;

abstract class Storage
{
    abstract protected function cleanAll(): void;

    abstract public function add( array $data ): int;

    abstract public function getTableInfo(): Collection;
}