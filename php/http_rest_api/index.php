
<?php

header("Content-Type: application/json; charset=UTF-8");

// ================================================
//              CYBER SECURITY TOOLKIT
//                  HTTP REST API
// ================================================

$store_path = __DIR__ . "/items.json";


// ================================================
//              RESPONSE FUNCTION
// ================================================

function respond($status, $payload)
{
    http_response_code($status);

    echo json_encode(
        $payload,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );

    exit;
}


// ================================================
//              STORE FUNCTIONS
// ================================================

function load_store($path)
{
    if (!file_exists($path)) {
        return [];
    }

    $content = file_get_contents($path);

    if ($content === false) {
        respond(500, ["error" => "Unable to read data store."]);
    }

    $store = json_decode($content, true);

    if (!is_array($store)) {
        respond(500, ["error" => "Data store contains invalid JSON."]);
    }

    return $store;
}


function save_store($path, $store)
{
    $content = json_encode(
        $store,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );

    if ($content === false) {
        respond(500, ["error" => "Unable to encode data."]);
    }

    if (file_put_contents($path, $content, LOCK_EX) === false) {
        respond(500, ["error" => "Unable to save data."]);
    }
}


// ================================================
//              REQUEST INFORMATION
// ================================================

$method = $_SERVER["REQUEST_METHOD"];

$headers = function_exists("getallheaders")
    ? getallheaders()
    : [];

$raw_body = file_get_contents("php://input");

$data = null;


// Read JSON Body

if ($raw_body !== "") {

    $data = json_decode($raw_body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {

        respond(400, [
            "success" => false,
            "error" => "Invalid JSON body.",
            "detail" => json_last_error_msg()
        ]);
    }

    if (!is_array($data)) {

        respond(400, [
            "success" => false,
            "error" => "JSON body must be an object."
        ]);
    }
}


// Read Authorization Header

$authorization = null;

foreach ($headers as $name => $value) {

    if (strtolower($name) === "authorization") {
        $authorization = $value;
        break;
    }
}


// Load Stored Data

$store = load_store($store_path);


// ================================================
//              HTTP REQUEST HANDLER
// ================================================

switch ($method) {


    // --------------------------------------------
    // GET: READ ITEMS
    // --------------------------------------------

    case "GET":

        if (isset($_GET["id"])) {

            $id = $_GET["id"];

            if (!isset($store[$id])) {

                respond(404, [
                    "success" => false,
                    "error" => "Item not found.",
                    "id" => $id
                ]);
            }

            respond(200, [
                "success" => true,
                "message" => "Item retrieved successfully.",
                "item" => $store[$id]
            ]);
        }

        respond(200, [
            "success" => true,
            "message" => "Items retrieved successfully.",
            "total_items" => count($store),
            "items" => array_values($store)
        ]);

        break;


    // --------------------------------------------
    // POST: CREATE ITEM
    // --------------------------------------------

    case "POST":

        if (
            !isset($data["name"]) ||
            !is_string($data["name"]) ||
            trim($data["name"]) === ""
        ) {

            respond(400, [
                "success" => false,
                "error" => "A valid name is required."
            ]);
        }

        $id = uniqid("item_");

        $store[$id] = [
            "id" => $id,
            "name" => trim($data["name"]),
            "created_at" => date("c")
        ];

        save_store($store_path, $store);

        respond(201, [
            "success" => true,
            "message" => "Item created successfully.",
            "item" => $store[$id]
        ]);

        break;


    // --------------------------------------------
    // PUT: UPDATE ITEM
    // --------------------------------------------

    case "PUT":

        $id = $data["id"] ?? ($_GET["id"] ?? null);

        if (!$id) {

            respond(400, [
                "success" => false,
                "error" => "Item ID is required."
            ]);
        }

        if (!isset($store[$id])) {

            respond(404, [
                "success" => false,
                "error" => "Item not found.",
                "id" => $id
            ]);
        }

        if (
            !isset($data["name"]) ||
            !is_string($data["name"]) ||
            trim($data["name"]) === ""
        ) {

            respond(400, [
                "success" => false,
                "error" => "A valid name is required for update."
            ]);
        }

        $store[$id]["name"] = trim($data["name"]);
        $store[$id]["updated_at"] = date("c");

        save_store($store_path, $store);

        respond(200, [
            "success" => true,
            "message" => "Item updated successfully.",
            "item" => $store[$id]
        ]);

        break;


    // --------------------------------------------
    // DELETE: REMOVE ITEM
    // --------------------------------------------

    case "DELETE":

        $id = $data["id"] ?? ($_GET["id"] ?? null);

        if (!$id) {

            respond(400, [
                "success" => false,
                "error" => "Item ID is required."
            ]);
        }

        if (!isset($store[$id])) {

            respond(404, [
                "success" => false,
                "error" => "Item not found.",
                "id" => $id
            ]);
        }

        unset($store[$id]);

        save_store($store_path, $store);

        respond(200, [
            "success" => true,
            "message" => "Item deleted successfully.",
            "deleted_id" => $id
        ]);

        break;


    // --------------------------------------------
    // UNSUPPORTED METHODS
    // --------------------------------------------

    default:

        header("Allow: GET, POST, PUT, DELETE");

        respond(405, [
            "success" => false,
            "error" => "HTTP method not allowed.",
            "method" => $method
        ]);

}


// ================================================
//              END OF REST API
// ================================================

