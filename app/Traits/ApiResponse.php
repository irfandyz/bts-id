<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return a standardized success JSON envelope response.
     *
     * @param  array<string, mixed>  $extra
     */
    public function success(mixed $data = null, string $message = 'Success', int $code = 200, array $extra = []): JsonResponse
    {
        $payload = [
            'code' => $code,
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            ...$extra,
        ];

        return response()->json($payload, $code);
    }

    /**
     * Return a standardized error JSON envelope response.
     */
    public function error(string $message = 'Error', int $code = 400, mixed $errors = null): JsonResponse
    {
        $payload = [
            'code' => $code,
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ];

        return response()->json($payload, $code);
    }

    /**
     * Format pagination links and metadata into a standardized structure.
     *
     * @return array{links: array<string, ?string>, meta: array<string, int|null>}
     */
    public function formatPagination(LengthAwarePaginator $paginator): array
    {
        return [
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
        ];
    }

    /**
     * Return a standardized paginated success JSON envelope response.
     */
    public function paginated(LengthAwarePaginator $paginator, mixed $items = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        $pagination = $this->formatPagination($paginator);

        return $this->success(
            $items ?? $paginator->items(),
            $message,
            $code,
            $pagination
        );
    }
}
