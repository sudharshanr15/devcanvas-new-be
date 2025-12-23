<?php

namespace App\Http\Controllers\Helpers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Database;
use Config;
use DB;

class DocumentController extends Controller{
    use ApiResponse;

    public function index(string $database_id, string $collection_id){
        $database = Database::where("document_id", $database_id)->first();
        if(!$database){
            return $this->errorResponse("Database not found", null, 404);
        }

        $collection = $database->collections->where("document_id", $collection_id)->first();
        if(!$collection){
            return $this->errorResponse("Collection not found", null, 404);
        }

        $override = [
            'database' => $database->name,
        ];
        $config = array_merge(Config::get("database.connections.pgsql"), $override);
        Config::set("database.connections.user_connection", $config);

        $db = DB::connection("user_connection");

        try{
            $documents = $db->table($collection->name)->get();
            return $this->successResponse($documents->toArray());
        }catch(\Exception $e){
            return $this->errorResponse("Error fetching documents: " . $e->getMessage(), null, 500);
        }
    }

    public function store(string $database_id, string $collection_id){
        $database = Database::where("document_id", $database_id)->first();
        if(!$database){
            return $this->errorResponse("Database not found", null, 404);
        }

        $collection = $database->collections->where("document_id", $collection_id)->first();
        if(!$collection){
            return $this->errorResponse("Collection not found", null, 404);
        }

        $override = [
            'database' => $database->name,
        ];
        $config = array_merge(Config::get("database.connections.pgsql"), $override);
        Config::set("database.connections.user_connection", $config);

        $db = DB::connection("user_connection");

        $data = request()->all();

        try{
            if(is_array($data)){
                $inserted_ids = [];
                foreach($data as $doc){
                    $inserted_ids[] = $db->table($collection->name)->insertGetId(array_merge(
                        $doc,
                        ['created_at' => now(), 'updated_at' => now()]
                    ));
                }
                return $this->successResponse(["ids" => $inserted_ids], "Documents inserted successfully");
            }
            $inserted_id = $db->table($collection->name)->insertGetId(array_merge(
                $data,
                ['created_at' => now(), 'updated_at' => now()]
            ));
            return $this->successResponse(["id" => $inserted_id], "Document inserted successfully");
        }catch(\Exception $e){
            return $this->errorResponse("Error inserting document: " . $e->getMessage(), null, 500);
        }
    }

    public function update(string $database_id, string $collection_id, string $document_id){
        $database = Database::where("document_id", $database_id)->first();
        if(!$database){
            return $this->errorResponse("Database not found", null, 404);
        }

        $collection = $database->collections->where("document_id", $collection_id)->first();
        if(!$collection){
            return $this->errorResponse("Collection not found", null, 404);
        }

        $override = [
            'database' => $database->name,
        ];
        $config = array_merge(Config::get("database.connections.pgsql"), $override);
        Config::set("database.connections.user_connection", $config);

        $db = DB::connection("user_connection");

        $data = request()->all();

        try{
            $updated = $db->table($collection->name)->where('id', $document_id)->update(array_merge(
                $data,
                ['updated_at' => now()]
            ));
            if($updated){
                return $this->successResponse(null, "Document updated successfully");
            }else{
                return $this->errorResponse("Document not found or no changes made", null, 404);
            }
        }catch(\Exception $e){
            return $this->errorResponse("Error updating document: " . $e->getMessage(), null, 500);
        }
    }

    public function destroy(string $database_id, string $collection_id, string $document_id){
        $database = Database::where("document_id", $database_id)->first();
        if(!$database){
            return $this->errorResponse("Database not found", null, 404);
        }

        $collection = $database->collections->where("document_id", $collection_id)->first();
        if(!$collection){
            return $this->errorResponse("Collection not found", null, 404);
        }

        $override = [
            'database' => $database->name,
        ];
        $config = array_merge(Config::get("database.connections.pgsql"), $override);
        Config::set("database.connections.user_connection", $config);

        $db = DB::connection("user_connection");

        try{
            $deleted = $db->table($collection->name)->where('id', $document_id)->delete();
            if($deleted){
                return $this->successResponse(null, "Document deleted successfully");
            }else{
                return $this->errorResponse("Document not found", null, 404);
            }
        }catch(\Exception $e){
            return $this->errorResponse("Error deleting document: " . $e->getMessage(), null, 500);
        }
    }
}