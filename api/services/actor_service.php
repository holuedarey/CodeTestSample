<?php

use helpers\OrmHelper;

require_once(__DIR__ . '/../configs/database_config.php');
require_once(__DIR__ . '/../helpers/property_functions.php');
require_once(__DIR__ . '/../helpers/orm_helper.php');

class ActorService
{
    private $db = null;
    public function __construct($db)
    {
        $this->db = $db;
    }


    public function findAll(int $page, int $size)
    {
        try {
            $offset = ($page - 1) * $size;
            $total_pages_sql = "SELECT COUNT(*) FROM actors";
            $result = mysqli_query($this->db, $total_pages_sql);
            $total_rows = mysqli_fetch_array($result)[0];
            $total_pages = ceil($total_rows / $size);

            $query = "SELECT * FROM actors LIMIT $offset, $size";
            $result = mysqli_query($this->db, $query);

            $products = array();
            while ($row = OrmHelper::getRows($result)) {
                $products[] = $row;
            }
            $data = array();
            $data["total"] = $total_rows;
            $data["pages"] = $total_pages;
            $data["items"] = $products;
            return $data;
        } catch (Exception $e) {
            exit($e->getMessage());
        }
    }


    public function find($id)
    {
        try {
            $query = "SELECT * FROM actors WHERE id = '$id'";
            $result = mysqli_query($this->db, $query);

            $product = null;
            if (OrmHelper::hasRows($result)) {
                $product = OrmHelper::getRows($result);
            }

            return $product;
        } catch (Exception $e) {
            exit($e->getMessage());
        }
    }

    public function insert(Array $input)
    {
        try {
            $name = $input['actor_name'];
            $movie_id = $input['movie_id'];
            $date_of_birth = $input['date_of_birth'];
            $id = uniqid('ac_');
            $date = date('Y-m-d H:i:s');

            $query = "INSERT INTO actors (id, actor_name, movie_id, date_of_birth) VALUES ('$id', '$name', '$movie_id', '$date_of_birth')";
            return mysqli_query($this->db, $query);
        } catch (Exception $e) {
            exit($e->getMessage());
        }
    }

    public function update($id, Array $input)
    {
        try {
            $name = $input['actor_name'];
            $movie_id = $input['movie_id'];
            $date_of_birth = $input['date_of_birth'];

            $query = "UPDATE actors SET actor_name = '$name', movie_id = '$movie_id', date_of_birth = '$date_of_birth' WHERE id = '$id'";
            return mysqli_query($this->db, $query);
        } catch (Exception $e) {
            exit($e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $query = "Delete from actors WHERE id = '$id'";
            return mysqli_query($this->db, $query);
        } catch (Exception $e) {
            exit($e->getMessage());
        }
    }
}