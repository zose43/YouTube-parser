<?php

namespace App\Exceptions;

use Exception;

class CredentialsException extends Exception
{
    protected $message = 'Json file with config must be named: credentials.json and located in dir "Storages"';
}