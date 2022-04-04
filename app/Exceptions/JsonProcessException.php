<?php

namespace App\Exceptions;

use JsonException;
use JetBrains\PhpStorm\Pure;
use Illuminate\Support\Facades\Log;

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

    public function report(): void
    {
        Log::channel( 'parsers' )->error( $this->message, [ 'json' => $this->getWrongJson() ] );
    }
}