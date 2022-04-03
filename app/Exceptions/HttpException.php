<?php

namespace App\Exceptions;

use Exception;
use JetBrains\PhpStorm\Pure;

class HttpException extends Exception
{
    protected $message = 'Your link is wrong';

    #[Pure] public function __construct( private string $url )
    {
        parent::__construct();
    }

    public function getInvalidUrl(): string
    {
        return $this->url;
    }
}