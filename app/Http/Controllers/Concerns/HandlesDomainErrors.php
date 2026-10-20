<?php

namespace App\Http\Controllers\Concerns;

use DomainException;
use Illuminate\Http\JsonResponse;
use Throwable;

trait HandlesDomainErrors
{
    protected function respondDomain(
        callable $callback,
        ?string $successMessage = null,
        int $successStatus = 200,
        string $errorMessage = 'Ocurrió un error inesperado. Inténtalo de nuevo.'
    ): JsonResponse {
        try {
            $result = $callback();

            if ($result instanceof JsonResponse) {
                return $result;
            }

            if (!is_array($result)) {
                $result = ['data' => $result];
            }

            if ($successMessage !== null) {
                $result = array_merge(['message' => $successMessage], $result);
            }

            return response()->json($result, $successStatus);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => $errorMessage], 500);
        }
    }
}