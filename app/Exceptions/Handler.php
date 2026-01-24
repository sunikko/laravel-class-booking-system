<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use DomainException;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        if ($request->expectsJson()) {

            if ($e instanceof DomainException) {
                return response()->json([
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ], 409);
            }
        }

        return parent::render($request, $e);
    }
}
