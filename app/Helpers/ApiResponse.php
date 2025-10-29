<?php

namespace App\Helpers;

trait ApiResponse{
    protected function successResponse($data = null, $message = "Success", int $status_code = HttpResponseCode::OK){
        return response()->json([
            "success" => true,
            "message" => $message,
            "data" => $data,
            "errors" => null
        ], $status_code);
    }

    protected function errorResponse($message = "Error", $errors = null, int $status_code = HttpResponseCode::INTERNAL_SERVER_ERROR){
        return response()->json([
            "success" => false,
            "message" => $message,
            "data" => null,
            "errors" => $errors
        ], $status_code);
    }
}