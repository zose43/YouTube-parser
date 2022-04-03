<?php

namespace App\Exceptions;

use Exception;

class WrongArrayDataException extends Exception
{
    protected $message = 'Structure of array is changed or regexp is invalid';
}