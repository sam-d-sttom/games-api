<?php

class GameController
{
    public function __construct(private GameGateway $gameGateway)
    {
        
    }


    //function to process request to the database depending on the request method and url.
    public function processRequest(string $method, $id)
    {
        if($id){
            $this->processResourceREquest($method, $id);
        }else{
            $this->processCollectionRequest($method);
        }
    }

    //gets all data if its a GET request and sends data to the database if its a POST request
    public function processCollectionRequest(string $method)
    {
        switch ($method){
            case 'GET':
                echo json_encode($this->gameGateway->getAll());
                break;

            case 'POST':
                $data = (array) json_decode(file_get_contents("php://input"), true);
                $error = $this->getPostDataValidationErrors($data);
                if(!empty($error)){
                    http_response_code(422);
                    echo json_encode($error);
                    exit;
                }
                $insertedGameId = $this->gameGateway->postGame($data);
                http_response_code(201);
                echo json_encode(["Message"=>"Game with the id($insertedGameId) has been inserted into the database successfully."]);
                break;

            case 'PATCH':
                $data = (array) json_decode(file_get_contents("php://input"), true);
                $ErrorResponse = $this->getPatchValidationErrors('', $data);
                    if(!empty($ErrorResponse)){
                        http_response_code(422);
                        echo json_encode($ErrorResponse);
                        exit;
                    }
                    echo $this->gameGateway->updateGame($data);
                break;


            default:
                http_response_code(405);
                header("Allow: POST, GET, PATCH");
                break;
        }
    }

    //gets specific elements with the GET method and a keyword
    public function processResourceREquest(string $method, $id)
    {
        $keyword = explode('=', $id);

        switch($method){
            case 'GET':
                if($keyword[0] == 'titles'){
                    echo json_encode($this->gameGateway->getTitles($id));
                }
                else if(preg_match('/=/', $id) && $keyword[0] == 'title'){
                    echo json_encode($this->gameGateway->getTitles($id));
                }
                else if(preg_match('/=/', $id) && $keyword[0] == 'year'){
                    echo json_encode($this->gameGateway->getByYear($id));
                }
                else if(preg_match('/=/', $id) && $keyword[0] == 'genre'){
                    echo json_encode($this->gameGateway->getByGenre($id));
                    // var_dump($this->gameGateway->getByGenre($id));
                }
                else{
                    http_response_code(404);
                    echo json_encode(["message"=>"Invalid endpoint"]);
                }
                break;


            case 'PATCH':
                if($keyword[0] == 'titles'){
                    $data = (array) json_decode(file_get_contents("php://input"), true);
                    $ErrorResponse = $this->getPatchValidationErrors($keyword[0], $data);
                    if(!empty($ErrorResponse)){
                        http_response_code(422);
                        echo json_encode($ErrorResponse);
                        exit;
                    }
                    echo $this->gameGateway->updateGameTitle($data);
                }
                else if($keyword[0] == 'genre'){
                    $data = (array) json_decode(file_get_contents("php://input"), true);
                    $ErrorResponse = $this->getPatchValidationErrors($keyword[0], $data);
                    if(!empty($ErrorResponse)){
                        http_response_code(422);
                        echo json_encode($ErrorResponse);
                        exit;
                    }
                    echo $this->gameGateway->updateGameGenre($data);
                }
                else if($keyword[0] == 'year'){
                    $data = (array) json_decode(file_get_contents("php://input"), true);
                    $ErrorResponse = $this->getPatchValidationErrors($keyword[0], $data);
                    if(!empty($ErrorResponse)){
                        http_response_code(422);
                        echo json_encode($ErrorResponse);
                        exit;
                    }
                    echo $this->gameGateway->updateGameYear($data);
                }

            default:
                http_response_code(405);
                header("Allow: POST, GET, PATCH");

        }
    }

    private function getPostDataValidationErrors(array $data): array
    {
        $error = [];

        if(array_key_exists('title', $data) && array_key_exists('genre', $data) && array_key_exists('year', $data)){
            $title = trim($data['title']);
            $genre = trim($data['genre']);
            $year = trim($data['year']);

            if(empty($title)){
                $error[] = ['Error'=>"Please make sure the title of the game is filled"];
            }
            if(empty($genre)){
                $error[] = ['Error'=>"Please make sure the genre of the game is filled"];
            }
            if(empty($year)){
                $error[] = ['Error'=>"Please make sure the year of the game is filled"];
            }
        }else{
            http_response_code(422);
            $error[] = ['Error'=>"Please make sure all valid entries('title', 'genre', 'year') are correctly filled"];
        }

        return $error;
    }


    private function getPatchValidationErrors(string $keyword, array $data): array
    {
        $response = [];

        if($keyword == 'title'){
            if(!array_key_exists( 'id', $data) || !array_key_exists( 'title', $data)){
                $response = ["Message"=>"Please make sure only the id and the title is filled when using a patch request with this end point. Both fieleds cannot be empty."];
            }else if(empty(trim($data['id'])) || empty(trim($data['title']))){
                $response = ["Message"=>"Please make sure only the id and the title is filled when using a patch request with this end point. Both fieleds cannot be empty."];
            }
        }

        if($keyword == 'genre'){
            if(!array_key_exists( 'id', $data) || !array_key_exists( 'genre', $data)){
                $response = ["Message"=>"Please make sure only the id and the genre is filled when using a patch request with this end point. Both fieleds cannot be empty."];
            }else if(empty(trim($data['id'])) || empty(trim($data['genre']))){
                $response = ["Message"=>"Please make sure only the id and the genre is filled when using a patch request with this end point. Both fieleds cannot be empty."];
            }
        }


        if($keyword == 'year'){
            if(!array_key_exists( 'id', $data) || !array_key_exists( 'year', $data)){
                $response = ["Message"=>"Please make sure only the id and the year is filled when using a patch request with this end point. Both fieleds cannot be empty."];
            }else if(empty(trim($data['id'])) || empty(trim($data['year']))){
                $response = ["Message"=>"Please make sure only the id and the year is filled when using a patch request with this end point. Both fieleds cannot be empty."];
            }
        }

        //for general update of a game data. function would be used in the
        if($keyword == ''){
            if(!array_key_exists( 'id', $data) || !array_key_exists( 'title', $data) || !array_key_exists( 'genre', $data) || !array_key_exists( 'year', $data)){
                $response = ["Message"=>"Please make sure all fieleds are filled when using a patch request with this end point. Fieleds cannot be empty."];
            }else if(empty(trim($data['id'])) || empty(trim($data['title'])) || empty(trim($data['genre'])) || empty(trim($data['year']))){
                $response = ["Message"=>"Please make sure all fieleds are filled when using a patch request with this end point. Fieleds cannot be empty."];
            }
        }

        return $response;
    }
}