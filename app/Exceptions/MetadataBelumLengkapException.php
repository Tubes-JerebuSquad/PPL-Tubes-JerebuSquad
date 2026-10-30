<?php

namespace App\Exceptions;

use RuntimeException;

class MetadataBelumLengkapException extends RuntimeException
{
    /**
     * @param  array<int, string>  $field
     */
    public function __construct(
        public readonly array $field,
        ?string $message = null,
    ) {
        parent::__construct($message ?? 'Metadata belum lengkap: '.implode(', ', $field));
    }
}
