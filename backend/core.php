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
            $result_query =  $this->executable_query($query, 0);
            $sql = $result_query['select'];
            $count_sql = $result_query['count'];
        } else {
            $sql = "SELECT * From bwa_apps Order By name Limit 20";
            $count_sql = 'SELECT COUNT(*) as total_rows From bwa_apps';
        }

        if ($sql == null) {
            return array('result' => array(), 'more' => false);
        } else {
            $result = $this->conn->query($sql);
    
            // count items
            $count_result = $this->conn->query($count_sql);
            $count_row = $count_result->fetch_assoc();
            $total_rows = $count_row['total_rows'];
            return array('result' => $result, 'more' => ($total_rows > 20));
        }
    }

    public function more_load($query, $count)
    {
        if ($query) {
            $result_query =  $this->executable_query($query, $count);
            $sql = $result_query['select'];
            $count_sql = $result_query['count'];
        } else {
            $sql = "SELECT * From bwa_apps Order By name LIMIT 20 OFFSET " . $count;
            $count_sql = 'SELECT COUNT(*) as total_rows From bwa_apps';
        }
        
        if ($sql == null) {
            return array('result' => array(), 'more' => false);
        } else {
            $query_result = $this->conn->query($sql);
    
            // Count items
            $count_result = $this->conn->query($count_sql);
            $count_row = $count_result->fetch_assoc();
            $total_rows = $count_row['total_rows'];
    
            $result = array();
            while ($row = $query_result->fetch_assoc()) {
                array_push($result, $row);
            }
    
            return array('result' => $result, 'more' => ($total_rows > $count + 20));
        }
    }

    public function save_stats_clicks($app_id, $clicked, $keyword)
    {
        $sql = "Insert Into stats_clicks (app_id, clicked, search_term) Values ('$app_id', '$clicked', '$keyword')";
        $result = $this->conn->query($sql);
        return $result;
    }

    private function executable_query($query, $count)
    {
        $keywords = $this->get_keywords($query);
        
        $origin_database = $this->get_data_based_first_value($keywords['origin']);
        $lemmatized_database = $this->get_data_based_first_value($keywords['lemmatized']);

        $filtered_app_ids = array();
        foreach ($origin_database as $data) {
            if ($this->search(0, $keywords['origin'], json_decode($data['keywords']))) {
                array_push($filtered_app_ids, $data['app_id']);
            }
        }

        foreach ($lemmatized_database as $data) {
            if ($this->search(0, $keywords['lemmatized'], json_decode($data['keywords']))) {
                array_push($filtered_app_ids, $data['app_id']);
            }
        }

        $select_sql = "SELECT * From bwa_apps Where app_id In (" . implode(', ', $filtered_app_ids) . ") Limit $count, 20";
        $count_sql = "SELECT Count(*) as total_rows From bwa_apps Where app_id In (" . implode(', ', $filtered_app_ids) . ")";
        
        if (count($filtered_app_ids) > 0) {
            return array('select' => $select_sql, 'count' => $count_sql);
        } else {
            return array('select' => null, 'count' => null);
        }
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
            array_push($lemmatized_keywords, Lemmatizer::getLemma($keyword));
        }
        
        return array('origin' => $keywords, 'lemmatized' => $lemmatized_keywords);
    }

    private function search($n, $keywords, $database)
    {
        $count = count($keywords);

        if ($n >= $count) {
            return true;
        } else {
            if (in_array($keywords[$n], $database)) {
                return $this->search($n + 1, $keywords, $database);
            } else if ($n < $count - 1 && in_array($keywords[$n] . $keywords[$n + 1], $database)) {
                return $this->search($n + 2, $keywords, $database);
            } else {
                return false;
            }
        }
    }

    private function get_data_based_first_value($keywords)
    {
        $all_keywords = array();
        $get_all_keywords_sql =
            "SELECT app_id, JSON_ARRAYAGG(keyword) AS keywords
             FROM (Select bwa_keywords.keyword, bwa_keywords_to_apps.app_id
                  From bwa_keywords
                  Inner Join bwa_keywords_to_apps
                  On bwa_keywords.keyword_id = bwa_keywords_to_apps.keyword_id) as t1
             Where app_id 
             IN (Select bwa_keywords_to_apps.app_id
                 From bwa_keywords
                 Inner Join bwa_keywords_to_apps
                 On bwa_keywords.keyword_id = bwa_keywords_to_apps.keyword_id
                 Where bwa_keywords.keyword = '" . $keywords[0] . "' Or bwa_keywords.keyword = '" . $keywords[0] . $keywords[1] . "')
             Group By app_id";
             
        $query_result = $this->conn->query($get_all_keywords_sql);
        
        while ($row = $query_result->fetch_assoc()) {
            array_push($all_keywords, $row);
        }
        return $all_keywords;
    }
}
