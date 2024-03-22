<?php

require 'vendor/autoload.php';
require 'Lemmatizer.php';

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
            $sql = $this->select_query($query, 0);
            $count_sql = $this->count_query($query);
        } else {
            $sql = "SELECT * From bwa_apps Order By name Limit 20";
            $count_sql = 'SELECT COUNT(*) as total_rows From bwa_apps';
        }
        
        $result = $this->conn->query($sql);

        // count items
        $count_result = $this->conn->query($count_sql);
        $count_row = $count_result->fetch_assoc();
        $total_rows = $count_row['total_rows'];
        return array('result' => $result, 'more' => ($total_rows > 20));
    }

    public function more_load($query, $count)
    {
         if ($query) {
            $sql = $this->select_query($query, $count);
            $count_sql = $this->count_query($query);
        } else {
            $sql = "SELECT * From bwa_apps Order By name LIMIT 20 OFFSET " . $count;
            $count_sql = 'SELECT COUNT(*) as total_rows From bwa_apps';
        }
        $query_result = $this->conn->query($sql);
    
        // Count items
        $count_result = $this->conn->query($count_sql);
        $count_row = $count_result->fetch_assoc();
        $total_rows = $count_row['total_rows'];

        $result = array();
        while($row = $query_result->fetch_assoc()) {
            array_push($result, $row);
        }
        
        return array('result' => $result, 'more' => ($total_rows > $count + 20));
    }

    public function save_stats_clicks($app_id, $clicked, $keyword)
    {
        $sql = "Insert Into stats_clicks (app_id, clicked, search_term) Values ('$app_id', '$clicked', '$keyword')";
        $result = $this->conn->query($sql);
        return $result;
    }

    private function select_query($query, $count)
    {
        $values = $this->get_keywords($query);
        $keywords_count = count(explode('+', $query));
        
        $sql = "SELECT t1.app_id, t1.name, t1.icon_name, t1.description, t1.url, t3.keyword
                FROM bwa_apps AS t1 
                JOIN bwa_keywords_to_apps AS t2 ON t1.app_id = t2.app_id 
                JOIN bwa_keywords AS t3 ON t2.keyword_id = t3.keyword_id
                Where t3.keyword IN ($values)
                GROUP BY t1.app_id
                HAVING COUNT(DISTINCT t3.keyword) = $keywords_count Order By name Limit 20 Offset " . $count;

        return $sql;
    }

    private function count_query($query)
    {
        $values = $this->get_keywords($query);
        $keywords_count = count(explode('+', $query));

        $sql = "SELECT COUNT(*) as total_rows
                FROM (SELECT t1.app_id, COUNT(DISTINCT t3.keyword) AS keyword_count
                    FROM bwa_apps AS t1 
                    JOIN bwa_keywords_to_apps AS t2 ON t1.app_id = t2.app_id 
                    JOIN bwa_keywords AS t3 ON t2.keyword_id = t3.keyword_id
                    Where t3.keyword IN ($values)
                    GROUP BY t1.app_id
                    HAVING COUNT(DISTINCT t3.keyword) = $keywords_count) AS t_1";

        return $sql;
    }

    private function get_keywords($text)
    {
        $text = str_replace("+", " ", $text);
        
        // Get Keywords
        $phrases = RakePlus::create($text)->get();
        $keywords = array();

        foreach ($phrases as $phrase) {
            $keywords = array_merge($keywords, explode(' ', $phrase));
        }

        // Transform keywords
        $lemmatized_keywords = array();
        foreach ($keywords as $keyword) {
            if ($keyword != Lemmatizer::getLemma($keyword))
            {
                array_push($lemmatized_keywords, Lemmatizer::getLemma($keyword));
            }
        }

        $search_keywords = array_merge($keywords, $lemmatized_keywords);
        return "'" . implode("', '" , $search_keywords) . "'";
    }
    
    
}
