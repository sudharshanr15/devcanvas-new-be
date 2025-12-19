<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\HttpResponseCode;
use App\Models\Collection;
use App\Models\Database;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CollectionController extends Controller
{
    use ApiResponse;

    public function show(Request $request, string $database_id){
        $user = auth()->user();
        $database = $user->databases->where("document_id", $database_id)->first();

        if(!$database){
            return $this->errorResponse("Database not found", null, HttpResponseCode::NOT_FOUND);
        }

        $collections = $database->collections;

        return $this->successResponse($collections->toArray());
    }

    public function store(Request $request, string $database_id){
        $user = auth()->user();
        $database = $user->databases->where("document_id", $database_id)->first();

        if(!$database){
            return $this->errorResponse("Database not found", null, HttpResponseCode::NOT_FOUND);
        }

        $validation = $request->validate([
            'name' => ['required', 'string', 'regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/'],
            'columns' => ['required', 'array', 'min:1'],
            'columns.*.name' => ['required', 'string', 'regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/'],
            "columns.*.type" => ["required", "in:string,integer,text,boolean,float,date,datetime"],
            'columns.*.nullable' => ['boolean'],
            'columns.*.unique' => ['boolean'],
            'columns.*.default' => [],
        ]);

        $attributes = [
            "name" => $validation["name"],
            "schema" => json_encode($validation),
            "database_id" => $database->id,
            "document_id" => md5($user->id . time())
        ];

        $override = [
            'database' => $database->name,
        ];
        $config = array_merge(Config::get("database.connections.pgsql"), $override);
        Config::set("database.connections.user_connection", $config);

        $db = DB::connection();
        
        if(Schema::connection("user_connection")->hasTable($attributes['name'])){
            return $this->errorResponse("Table already exist", null, HttpResponseCode::BAD_REQUEST);
        }

        $columns = [
            [
                'name' => 'id',
                'type' => 'serial',
            ],
            [
                'name' => 'timestamp',
                'type' => 'timestamp',
            ]
        ];

        array_push($columns, ...$validation['columns']);

        try{
            Schema::connection("user_connection")->create($attributes["name"], function (Blueprint $table) use ($columns) {
                foreach ($columns as $column) {
                    // Define column type
                    $columnType = $column['type'];
                    $columnName = $column['name'];

                    // Initialize the column
                    $col = match ($columnType) {
                        'string' => $table->string($columnName),
                        'integer' => $table->integer($columnName),
                        'text' => $table->text($columnName),
                        'boolean' => $table->boolean($columnName),
                        'float' => $table->float($columnName),
                        'date' => $table->date($columnName),
                        'timestamp' => $table->timestampsTz(),
                        'serial' => $table->id(),
                        default => null,
                    };

                    // Apply additional properties
                    if (isset($column['nullable']) && $column['nullable']) {
                        $col->nullable();
                    }

                    if (isset($column['default'])) {
                        $col->default($column['default']);
                    }

                    if (isset($column['unique']) && $column['unique']) {
                        $table->unique($columnName);
                    }
                }
            });

            Collection::create($attributes);
        }catch(Exception $e){
            return $this->errorResponse("Unable to create collection", $e->getMessage());
        }

        return $this->successResponse();
    }

    public function destroy($database_id, $collection_id){
        $user = auth()->user();
        $database = $user->databases->where("document_id", $database_id)->first();

        if(!$database){
            return $this->errorResponse("Database not found", null, HttpResponseCode::NOT_FOUND);
        }

        $collection = $database->collections->where("document_id", $collection_id)->first();

        if(!$collection){
            return $this->errorResponse("Collection not found", null, HttpResponseCode::NOT_FOUND);
        }

        $override = [
            'database' => $database->name,
        ];
        $config = array_merge(Config::get("database.connections.pgsql"), $override);
        Config::set("database.connections.user_connection", $config);

        $db = DB::connection();

        try{
            Schema::connection("user_connection")->dropIfExists($collection->name);
            $collection->delete();
        }catch(Exception $e){
            return $this->errorResponse("Unable to delete collection", $e->getMessage());
        }

        return $this->successResponse();
    }
}
