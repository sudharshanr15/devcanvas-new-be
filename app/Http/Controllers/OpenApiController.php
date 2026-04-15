<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;

class OpenApiController extends Controller
{
    use ApiResponse;

    public function show()
    {
        $docsPath = 'docs/api';
        $specPath = 'docs/' . ltrim(config('scramble.export_path', 'api.json'), '/');

        return $this->successResponse([
            'openapi_version' => '3.0.3',
            'name' => config('app.name'),
            'version' => config('scramble.info.version', '0.0.1'),
            'description' => config('scramble.info.description', ''),
            'ui_url' => url($docsPath),
            'spec_url' => url($specPath),
        ]);
    }
}