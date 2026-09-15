<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony;

use Symfony\Component\HttpFoundation\Request;

/**
 * Safely reads fields out of a JSON request body. Building a Payload directly
 * from `$data['x'] ?? ''` crashes with an uncaught TypeError (500, not a
 * clean 422) whenever the client sends a non-string value (a number, bool,
 * array, or a body that isn't even a JSON object) for a field typed as
 * `string` - json_decode()'s output is otherwise untyped and can be
 * anything the client sends.
 */
final class JsonBody
{
    /** @var array<string, mixed> */
    private array $data;

    public function __construct(Request $request)
    {
        $decoded = json_decode($request->getContent(), true);
        $this->data = is_array($decoded) ? $decoded : [];
    }

    public function string(string $key, string $default = ''): string
    {
        return isset($this->data[$key]) && is_string($this->data[$key]) ? $this->data[$key] : $default;
    }

    public function nullableString(string $key): ?string
    {
        return isset($this->data[$key]) && is_string($this->data[$key]) ? $this->data[$key] : null;
    }
}
