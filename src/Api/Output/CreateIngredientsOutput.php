<?php

namespace App\Api\Output;

class CreateIngredientsOutput
{
    public string $message;

    /** @var array<int, string> */
    public array $created;

    /** @var array<int, string> */
    public array $existing;

    /**
     * @param array<int, string> $created
     * @param array<int, string> $existing
     */
    public function __construct(string $message, array $created, array $existing)
    {
        $this->message = $message;
        $this->created = $created;
        $this->existing = $existing;
    }
}
