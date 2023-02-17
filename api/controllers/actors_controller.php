<?php

namespace api\controllers;

require_once(__DIR__ . '/../services/actor_service.php');

use ActorService;

class ActorsController
{
    private $db;
    private $requestMethod;
    private $userId;
    private $actorService;
    private $page;
    private $size;

    public function __construct($db, $requestMethod, $userId, $page, $size)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->userId = $userId;
        $this->actorService = new ActorService($db);
        $this->page = $page;
        $this->size = $size;
    }


    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->userId) {
                    $response = $this->getActor($this->userId);
                } else {
                    $response = $this->getAllActors($this->page, $this->size);
                };
                break;
            case 'POST':
                $response = $this->createActorFromRequest();
                break;
            case 'PUT':
                $response = $this->updateActorFromRequest($this->userId);
                break;
            case 'DELETE':
                $response = $this->deleteActor($this->userId);
                break;
            case 'OPTIONS':
                $response['status_code_header'] = 'HTTP/1.1 200 OK';
                $response['body'] = null;
                break;
            default:
                $response = $this->notFoundResponse("Method not found");
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getAllActors($page, $size)
    {
        $result = $this->actorService->findAll($page, $size);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] =$this->apiResponse(true, "", $result);
        return $response;
    }

    private function getActor($id)
    {
        $result = $this->actorService->find($id);
        if (!$result) {
            return $this->notFoundResponse("No data available with id $id");
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = $this->apiResponse(true,"", $result);
        return $response;
    }

    private function createActorFromRequest()
    {
        $actor = (array) json_decode(file_get_contents('php://input'), true);
        $inputs = array("title", "runtime", "release_date");
        $validationError = $this->validateInput($actor, $inputs);
        if ($validationError) {
            return $this->unprocessableEntityResponse($validationError);
        }

        $result = $this->actorService->insert($actor);
        if ($result) {
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = $this->apiResponse(true,  "Actor created successfully");
        } else {
            $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
            $response['body'] = $this->apiResponse(false, "Actor creation failed");
        }
        return $response;
    }

    private function updateActorFromRequest($id)
    {

        if(!$id) {
            $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
            $response['body'] = $this->apiResponse(false, "Id of the movie is required");;
            return $response;
        }

        $result = $this->actorService->find($id);
        if (! $result) {
            return $this->notFoundResponse("Id $id not found");
        }

        $actor = (array) json_decode(file_get_contents('php://input'), true);
        $inputs = array("title", "runtime", "release_dates");
        $validationError = $this->validateInput($actor, $inputs, 'update');
        if ($validationError) {
            return $this->unprocessableEntityResponse($validationError);
        }

        $result = $this->actorService->update($id, $actor);
        if ($result) {
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = $this->apiResponse(true, "Actor updated successfully");
        } else {
            $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
            $response['body'] = $this->apiResponse(false, "Actor update failed");
        }
        return $response;
    }

    private function deleteActor($id)
    {
        if(!$id) {
            $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
            $response['body'] = $this->apiResponse(false, "Id of the movie is required");;
            return $response;
        }

        $result = $this->actorService->find($id);
        if (! $result) {
            return $this->notFoundResponse("Id $id not found");
        }
        $this->actorService->delete($id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = $this->apiResponse(false, "Actor Deleted failed");;
        return $response;
    }

    private function unprocessableEntityResponse($messsage)
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = $messsage;
        return $response;
    }

    private function notFoundResponse($message)
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = $this->apiResponse(false, $message);
        return $response;
    }

    private function apiResponse(bool $status, string $message, $data = array())
    {
        return json_encode(["hasResult"=> $status, "message"=> $message, "data"=> $data]);
    }

    private function validateInput($data, array $inputs, $method = 'create')
    {
        foreach ($inputs as $property) {
            if (!hasProperty($data, $property)) {
                return $this->apiResponse( false, "Failed to $method, $property is needed");
            }
        }

        return null;
    }
}