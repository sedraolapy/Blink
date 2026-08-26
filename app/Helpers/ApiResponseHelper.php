<?php

if (! function_exists('sendResponse')) {
    function sendResponse(
        string $message,
        mixed $data = null,
        int $code = 200,
        mixed $pagination = null
    ) {
        $response = [
            'success' => $code >= 200 && $code < 300,
            'message' => $message,
            'data' => $data,
        ];

        if ($pagination) {
            $response['pagination'] = getPaginationData($pagination);
        }

        return response()->json($response, $code);
    }
}

if (! function_exists('sendError')) {
    function sendError(
        string $message,
        int $code = 400,
        mixed $errors = null
    ) {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $code);
    }
}

if (! function_exists('getPaginationData')) {
    function getPaginationData($collection): array
    {
        return [
            'current_page' => $collection->currentPage(),
            'last_page' => $collection->lastPage(),
            'per_page' => $collection->perPage(),
            'total' => $collection->total(),
            'from' => $collection->firstItem(),
            'to' => $collection->lastItem(),
            'next_page_url' => $collection->nextPageUrl(),
            'prev_page_url' => $collection->previousPageUrl(),
        ];
    }
}