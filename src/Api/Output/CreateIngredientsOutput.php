<?php

namespace App\Api\Output;

class CreateIngredientsOutput
{
    public string $message;
    public array $created;
    public array $existing;

    public function __construct(string $message, array $created, array $existing)
    {
        $this->message = $message;
        $this->created = $created;
        $this->existing = $existing;
    }
}
