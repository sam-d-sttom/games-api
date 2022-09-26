<?php

class GameGateway
{
    private $conn;

    //initializing connection to the database.
    public function __construct(Database $database)
    {
        $this->conn = $database->connectDatabase();
    }

    //function for selecting all games from the database.
    public function getAll()
    {
        $sql = "SELECT * FROM games";

        $stmt = mysqli_query($this->conn, $sql);
        $data = [];

        while($row = $stmt->fetch_array(MYSQLI_ASSOC)){
            $data[] = $row;
        }

        // var_dump($data);
        return $data;
    }

    public function getTitles($id)
    {
        if(preg_match('/=/', $id)){
            //getting search title keyword from url if there is a search keyword.
            $name = explode('=', $id);
            $title = explode('_', $name[1]);
            
            $titleName = implode(" ", $title);
            $titleName = trim($titleName, " ");
            if(!preg_match('/[a-zA-Z0-9]/', $titleName)){
                http_response_code(422);
                echo json_encode(["message"=>"Please provide a keyword to search for."]);
                exit;
            }


            // Preparing the regex expression to search for.
            $titleName = "";
            if(count($title) == 1){
                $title = str_split($title[0], 1);
                foreach($title as $t){
                    $titleName .= "(?=.*$t)";
                }
            }else{
                foreach($title as $t){
                    $titleName .= "(?=.*$t)";
                }
            }

            //getting all title from the database
            $sql = "SELECT title FROM games";
        
                $stmt = mysqli_query($this->conn, $sql);
                $titles = [];
        
                while($row = $stmt->fetch_array(MYSQLI_ASSOC)){
                    $titles[] = $row;
                }

                $data = [];
                
            //checking for a match between the keyword and each title gotten from the database. if there is a match, all data regarding that title is gotten from the database and inserted into the data array.
            foreach($titles as $title){

                $title = $title['title'];

                if(preg_match("/$titleName.*/i", "$title")){
                    
                    $sql = "SELECT * FROM games WHERE title = ?";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bind_param('s', $bindTitle);

                    $bindTitle = $title;

                    $stmt->execute();
                    $result = $stmt->get_result();

                    while($row = $result->fetch_array(MYSQLI_ASSOC)){
                        $data[] = $row;
                    }
                }
            }

        }else{
            //getting all title from the database
            
            $sql = "SELECT title FROM games";
        
            $stmt = mysqli_query($this->conn, $sql);
            $data = [];
        
            while($row = $stmt->fetch_array(MYSQLI_ASSOC)){
                $data[] = $row;
            } 
        }

        if(empty($data)){
            http_response_code(404);
            $data = ["message"=>"No content with the search keyword was found in the database."];
        }

        return $data;
    }



    public function getByGenre($id)
    {
        //getting search genre keyword from url if there is a search keyword.
        $genre = explode('=', $id);
        $genre = explode('_', $genre[1]);

        // Preparing the regex expression to search for.
        $genreName = "";
        foreach($genre as $g){
            $genreName .= "(?=.*$g)";
        }


        //getting all genre from the database
        $sql = "SELECT genre FROM games";
        
            $stmt = mysqli_query($this->conn, $sql);
            $genres = [];
        
            while($row = $stmt->fetch_array(MYSQLI_ASSOC)){
                $genres[] = $row;
            }
            $data = [];
                
        //checking for a match between the keyword and each genre gotten from the database. if there is a match,all data regarding that genre is gotten from the database and inserted into the data array.
        foreach($genres as $genre){

            $genre = $genre['genre'];
            
            if(preg_match("/$genreName.*/i", "$genre")){
                    
                $sql = "SELECT * FROM games WHERE genre = ?";
                $stmt = $this->conn->prepare($sql);
                    $stmt->bind_param('s', $bindGenre);

                    $bindGenre = $genre;

                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                while($row = $result->fetch_array(MYSQLI_ASSOC)){
                    //logic that prevents data with a particular id from repeating twice in the final output
                    if(!empty($data)){
                        $count = 0;
                        foreach($data as $d){
                            if($row['id'] == $d['id']){
                                if($count > 0){
                                    break;
                                }else{
                                    $count++;
                                }
                            }
                        }
                        if($count == 0){
                            $data[] = $row;
                        }
                    }else{
                        $data[] = $row;
                    }
                }
            }
        }

        if(empty($data)){
            http_response_code(404);
            $data = ["message"=>"No content with the search keyword was found in the database."];
        }

        return $data;
    }


    public function getByYear($id)
    {
        //getting search year keyword from url if there is a search keyword.
        $year = explode('=', $id);

        //getting all games from the database with the year keyword.
        $sql = "SELECT * FROM games WHERE year = ? ";
        
            // $stmt = mysqli_query($this->conn, $sql);
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('i', $bindGenre);

            $bindGenre = $year[1];

            $stmt->execute();
            $result = $stmt->get_result();
            
            $data = [];
        
            while($row = $result->fetch_array(MYSQLI_ASSOC)){
                $data[] = $row;
            }

            if(empty($data)){
                http_response_code(404);
                $data = ["message"=>"No content with the search keyword was found in the database."];
            }
    
            return $data;
    }

    public function postGame(array $data): string
    {
        $sql = "INSERT INTO games (title, genre, year) VALUE (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssi', $bind_title, $bind_genre, $bind_year);

        // if(array_key_exists('title', $data) && array_key_exists('genre', $data) && array_key_exists('year', $data)){
            $data['year'] = intval($data['year']);
            $bind_title = $data['title'];
            $bind_genre = $data['genre'];
            $bind_year = $data['year']; 

        // }else{
        //     http_response_code(422);
        //     echo json_encode(['Error'=>"Please make sure all valid entries('title', 'genre', 'year') are correctky filled"]);
        //     exit;
        // }

        $stmt->execute();
        return $this->conn->insert_id;
    }

    public function updateGameTitle(array $data): string
    {
        $sql = "UPDATE games set title = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('si', $bind_title, $bind_id);

        $data['id'] = intval($data['id']);
        $bind_title = $data['title'];
        $bind_id = $data['id'];


        $stmt->execute();
        return json_encode(["Message"=>"Title of the game with the id of $bind_id has been updated successfully."]);
    }

    public function updateGameGenre(array $data): string
    {
        $sql = "UPDATE games set genre = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('si', $bind_genre, $bind_id);

        $data['id'] = intval($data['id']);
        $bind_genre = $data['genre'];
        $bind_id = $data['id'];


        $stmt->execute();
        return json_encode(["Message"=>"Genre of the game with the id of $bind_id has been updated successfully."]);
    }


    public function updateGameYear(array $data): string
    {
        $sql = "UPDATE games set year = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $bind_year, $bind_id);

        $data['id'] = intval($data['id']);
        $data['year'] = intval($data['year']);
        $bind_year = $data['year'];
        $bind_id = $data['id'];


        $stmt->execute();
        return json_encode(["Message"=>"Year the game with the id of $bind_id was made has been updated successfully."]);
    }

    public function updateGame(array $data): string
    {
        $sql = "UPDATE games set title = ?, genre = ?, year = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssii', $bind_title, $bind_genre, $bind_year, $bind_id);

        $data['id'] = intval($data['id']);
        $data['year'] = intval($data['year']);
        $bind_title = $data['title'];
        $bind_genre = $data['genre'];
        $bind_year = $data['year'];
        $bind_id = $data['id'];


        $stmt->execute();
        return json_encode(["Message"=>"All datas for the game with the id of $bind_id has been updated successfully."]);
    }

}