<?php declare(strict_types=1);

namespace App\Exception;

final class QueryException extends \Exception
{
    public static function failed(string $message, string $query, $previous = null): self
    {
        return new self("[$message] in the query \n " . $query, 0, $previous);
    }
}