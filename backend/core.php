<?php

require '../vendor/autoload.php';
require './Lemmatizer.php';

use DonatelloZa\RakePlus\RakePlus;


class BestWinApps
{
    private $conn;

    const SERVER_NAME = "localhost";
    const USER_NAME = "bestwinapps_dbusr";
    const PASSWORD = "GG8n8chMFdtGW";
    const DB_NAME = "bestwinapps_db";

    function __construct()
    {
        $this->conn = new mysqli(self::SERVER_NAME, self::USER_NAME, self::PASSWORD, self::DB_NAME);
        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function get_apps($query)
    {
        if ($query) {
            $sql = $this->select_query($query);
        } else {
            $sql = "Select * From bwa_apps Order By name Limit 20";
        }
        $result = $this->conn->query($sql);

        return $result;
    }

    public function save_stats_clicks($app_id, $clicked, $keyword)
    {
        $sql = "Insert Into stats_clicks (app_id, clicked, search_term) Values ($app_id, $clicked, $keyword)";
        $result = $this->conn->query($sql);
        return $result;
    }

    public function more_load($keywords, $count)
    {
        $sql = "Select *, From bwa_apps LIMIT " . 20 . " OFFSET " . $count;
        $result = $this->conn->query($sql);
        return $result;
    }

    private function select_query($query)
    {

        $keywords = str_replace('+', ' ', $query);

        $phrases = RakePlus::create($keywords)->get();

        print_r($phrases);
        return "Select * From bwa_apps Order By name Limit 20";
    }
    
}
