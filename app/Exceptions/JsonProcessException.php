<?php

namespace App\Exceptions;

use JsonException;
use JetBrains\PhpStorm\Pure;

class JsonProcessException extends JsonException
{
    protected $message = 'Invalid JSON data or empty array';

    #[Pure] public function __construct( private array $json )
    {
        parent::__construct();
    }

    public function getWrongJson(): array
    {
        return $this->json;
    }
}