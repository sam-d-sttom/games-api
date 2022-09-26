<?php

//class to connect to database
class Database
{
    public function __construct(private string $hostName, private string $user, private string $password, private string $dbName)
    {
        

    }

    public function connectDatabase()
    {
        $conn = new mysqli($this->hostName, $this->user, $this->password, $this->dbName);

        // echo 'connected';
        return $conn;
    }
}