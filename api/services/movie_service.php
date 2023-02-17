<?php

use helpers\OrmHelper;

require_once(__DIR__ . '/../configs/database_config.php');
require_once(__DIR__ . '/../helpers/property_functions.php');
require_once(__DIR__ . '/../helpers/orm_helper.php');

class MovieService
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
            $total_pages_sql = "SELECT COUNT(*) FROM movies";
            $result = mysqli_query($this->db, $total_pages_sql);
            $total_rows = mysqli_fetch_array($result)[0];
            $total_pages = ceil($total_rows / $size);

            $query = "SELECT * FROM movies LIMIT $offset, $size";
            $query = "SELECT movies.*,  actors.actor_name, actors.date_of_birth, actors.movie_id FROM movies  JOIN actors ON movies.id=actors.movie_id GROUP BY actors.id";
//            $query = "SELECT
//                         movies.*, actors.actor_name
//                        FROM movies
//                        LEFT JOIN
//                         actors  ON movies.id = actors.movie_id;";
            $result = mysqli_query($this->db, $query);

            $movies = array();
            while ($row = OrmHelper::getRows($result)) {
                $movies[] = $row;
            }
            $data = array();
            $data["total"] = $total_rows;
            $data["pages"] = $total_pages;
            $data["items"] = $movies;
            return $data;
        } catch (Exception $e) {
            exit($e->getMessage());
        }
    }

    public function find($id)
    {
        try {
            $query = "SELECT * FROM movies WHERE id = '$id'";
            $result = mysqli_query($this->db, $query);

            $movie = null;
            if (OrmHelper::hasRows($result)) {
                $movie = OrmHelper::getRows($result);
            }

            return $movie;
        } catch (Exception $e) {
            exit($e->getMessage());
        }
    }

    public function insert(array $movie)
    {
        try {
            $title = $movie['title'];
            $runtime = $movie['runtime'];
            $release_date = $movie['release_date'];
            $id = uniqid('mv_');
            $date = date('Y-m-d H:i:s');

            $query = "INSERT INTO movies (id, title, runtime, release_date, created_at) VALUES ('$id', '$title', '$runtime', '$release_date', '$date')";
            return mysqli_query($this->db, $query);
        } catch (Exception $e) {
            exit($e->getMessage());
        }
    }

    public function update($id, array $movie)
    {
        try {
            $title = $movie['title'];
            $runtime = $movie['runtime'];
            $release_date = $movie['release_date'];

            $query = "UPDATE movies SET title = '$title', runtime = '$runtime', release_date = '$release_date' WHERE id = '$id'";
            return mysqli_query($this->db, $query);
        } catch (Exception $e) {
            exit($e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $query = "Delete from movies WHERE id = '$id'";
            return mysqli_query($this->db, $query);
        } catch (Exception $e) {
            exit($e->getMessage());
        }
    }
}