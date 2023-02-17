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
        $statement = "
            INSERT INTO person 
                (firstname, lastname, firstparent_id, secondparent_id)
            VALUES
                (:firstname, :lastname, :firstparent_id, :secondparent_id);
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'firstname' => $input['firstname'],
                'lastname'  => $input['lastname'],
                'firstparent_id' => $input['firstparent_id'] ?? null,
                'secondparent_id' => $input['secondparent_id'] ?? null,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function update($id, Array $input)
    {
        $statement = "
            UPDATE person
            SET 
                firstname = :firstname,
                lastname  = :lastname,
                firstparent_id = :firstparent_id,
                secondparent_id = :secondparent_id
            WHERE id = :id;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'id' => (int) $id,
                'firstname' => $input['firstname'],
                'lastname'  => $input['lastname'],
                'firstparent_id' => $input['firstparent_id'] ?? null,
                'secondparent_id' => $input['secondparent_id'] ?? null,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function delete($id)
    {
        $statement = "
            DELETE FROM person
            WHERE id = :id;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('id' => $id));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
}