<?php

namespace api\controllers;

require_once(__DIR__ . '/../services/movie_service.php');

use MovieService;

class MoviesController
{
    private $db;
    private $requestMethod;
    private $movieId;
    private $movieService;
    private $page;
    private $size;

    public function __construct($db, $requestMethod, $userId, $page, $size)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->movieId = $userId;
        $this->movieService = new MovieService($db);
        $this->page = $page;
        $this->size = $size;
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->movieId) {
                    $response = $this->getMovie($this->movieId);
                } else {
                    $response = $this->getAllMovies($this->page, $this->size);
                };
                break;
            case 'POST':
                $response = $this->createMovieFromRequest();
                break;
            case 'PUT':
                $response = $this->updateMovieFromRequest($this->movieId);
                break;
            case 'DELETE':
                $response = $this->deleteMovie($this->movieId);
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

    private function getAllMovies($page, $size)
    {
        $result = $this->movieService->findAll($page, $size);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] =$this->apiResponse(true, "", $result);
        return $response;
    }

    private function getMovie($id)
    {
        $result = $this->movieService->find($id);
        if (!$result) {
            return $this->notFoundResponse("No data available with id $id");
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = $this->apiResponse(true,"", $result);
        return $response;
    }

    private function createMovieFromRequest()
    {
        $movie = (array) json_decode(file_get_contents('php://input'), true);
        $inputs = array("title", "runtime", "release_date");
        $validationError = $this->validateInput($movie, $inputs);
        if ($validationError) {
            return $this->unprocessableEntityResponse($validationError);
        }

        $result = $this->movieService->insert($movie);
        if ($result) {
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = $this->apiResponse(true,  "Movie created successfully");
        } else {
            $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
            $response['body'] = $this->apiResponse(false, "Movie creation failed");
        }
        return $response;
    }

    private function updateMovieFromRequest($id)
    {

        if(!$id) {
            $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
            $response['body'] = $this->apiResponse(false, "Id of the movie is required");;
            return $response;
        }

        $result = $this->movieService->find($id);
        if (! $result) {
            return $this->notFoundResponse("Id $id not found");
        }

        $movie = (array) json_decode(file_get_contents('php://input'), true);
        $inputs = array("title", "runtime", "release_dates");
        $validationError = $this->validateInput($movie, $inputs, 'update');
        if ($validationError) {
            return $this->unprocessableEntityResponse($validationError);
        }

        $result = $this->movieService->update($id, $movie);
        if ($result) {
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = $this->apiResponse(true, "Movie updated successfully");
        } else {
            $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
            $response['body'] = $this->apiResponse(false, "Movie update failed");
        }
        return $response;
    }

    private function deleteMovie($id) 
    {
        if(!$id) {
            $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
            $response['body'] = $this->apiResponse(false, "Id of the movie is required");;
            return $response;
        }

        $result = $this->movieService->find($id);
        if (! $result) {
            return $this->notFoundResponse("Id $id not found");
        }
        $this->movieService->delete($id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = $this->apiResponse(false, "Movie Deleted failed");;
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