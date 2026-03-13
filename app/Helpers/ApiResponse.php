<?php

if (! function_exists('success_response')) {
    function success_response(mixed $data = null, string $message = 'Success', int $status = 200): \Illuminate\Http\JsonResponse
    {
        $payload = ['success' => true, 'message' => $message];

        if (! is_null($data)) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $status);
    }
}

if (! function_exists('error_response')) {
    function error_response(string $message = 'Error', int $status = 400, mixed $errors = null): \Illuminate\Http\JsonResponse
    {
        $payload = ['success' => false, 'message' => $message];

        if (! is_null($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}