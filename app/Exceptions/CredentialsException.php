<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class CredentialsException extends Exception
{
    protected $message = 'Json file with config must be named: credentials.json and located in dir "Storages"';

    public function report(): void
    {
        Log::channel( 'parsers' )->error( 'File credentials.json does not exist' );
    }
}