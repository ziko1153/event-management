<?php

namespace App\Model;

use App\Exception\DatabaseException;
use App\Traits\DatabaseTrait;

abstract class BaseModel
{
    use DatabaseTrait;

    protected string $table;

    protected function ensureTableIsSet()
    {
        if (empty($this->table)) {
            throw new DatabaseException("The 'table' property is not set in the model.");
        }
    }
}
