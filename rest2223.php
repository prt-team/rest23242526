<?php

header('Content-Type: application/json');
require_once("wp-config.php");
$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die('Ð½Ðµ Ð¿Ð¾Ð»ÑƒÑ‡Ð¸Ð»Ð¾ÑÑŒ');
mysql_select_db(DB_NAME, $link) or die('Ð½Ðµ Ð¿Ð¾Ð»ÑƒÑ‡Ð¸Ð»Ð¾ÑÑŒ Ð±Ð°Ð·Ð°');
mysql_set_charset("utf8");
$answer = array();


class restController
{

    public function showCategories($parent = 1)
    {
        $answer = array();
        $posts = array();
        $cats = array();
        $query = "SELECT `wp_terms`.`term_id`,`wp_terms`.`name` FROM `wp_term_taxonomy` join `wp_terms`
										on `wp_terms`.term_id = `wp_term_taxonomy`.term_id
										WHERE `wp_term_taxonomy`.`taxonomy` = 'category'
											AND `wp_term_taxonomy`.`parent` = {$parent}";
        $result = mysql_query($query);
        while ($row = mysql_fetch_array($result)) {
            unset($row[0]);
            unset($row[1]);
            if ($row['term_id'] != 4 && $row['term_id'] != 8) {
                $cats[] = $row;
            }
        }


        if ($parent != 1) {
//        if (count($cats) == 0) {
            $query = "SELECT `wp_posts`.post_title, `wp_posts`.ID
        			  FROM `wp_term_taxonomy`
        			  JOIN `wp_terms` ON `wp_terms`.term_id = `wp_term_taxonomy`.term_id
        			  JOIN `wp_term_relationships` ON `wp_term_relationships`.term_taxonomy_id = `wp_term_taxonomy`.term_taxonomy_id
        			  JOIN `wp_posts` ON `wp_posts`.ID = `wp_term_relationships`.object_id
					  WHERE `wp_term_taxonomy`.`taxonomy` = 'category'
					  AND `wp_term_taxonomy`.`term_id` = {$parent}
					  AND `wp_posts`.post_status = 'publish'
                      AND `wp_posts`.post_title != ''";
            $result = mysql_query($query);
            while ($row = mysql_fetch_array($result)) {
                unset($row[0]);
                unset($row[1]);
                $posts[] = array(
                    "post_title" => preg_replace('/^(.*)<!--:en-->(.+?)(?=<!--:-->)(.*)$/s', '\\2', $row['post_title']),
                    "ID" => $row["ID"],
                );
            }
            $answer['posts'] = $posts;
        }
        $answer["categories"] = $cats;
        return $answer;
    }

    public
    function showPost($id)
    {
        $query = "SELECT `wp_posts`.post_title, `wp_posts`.post_content
    			  FROM  `wp_posts`
    			  WHERE `wp_posts`.post_status = 'publish' AND `wp_posts`.ID = {$id}";

        $result = mysql_query($query);
        while ($row = mysql_fetch_array($result)) {
            unset($row[0]);
            unset($row[1]);
            $answer['post'] = array(
                "post_title" => preg_replace('/^(.*)<!--:en-->(.+?)(?=<!--:-->)(.*)$/s', '\\2', $row['post_title']),
                "post_content" => preg_replace('/^(.*)<!--:en-->(.+?)(?=<!--:-->)(.*)$/s', '\\2', $row['post_content']),
            );
        }
        return $answer;
    }

}

class router
{

    public function __construct()
    {
        if (!isset($_GET['q'])) {
            $_GET['q'] = 'categories';
        }
        if (!isset($_GET['id'])) {
            $_GET['id'] = 1;
        }

        $_GET['id'] = preg_replace("/\D/", "", $_GET['id']);
    }


    protected $_routes = array(
        "",
        "categories",
        "posts"
    );


    public function getAction()
    {
        if (in_array($_GET['q'], $this->_routes)) {
            switch ($_GET['q']) {
                case 'categories':
                    $restController = new restController();
                    $answer = $restController->showCategories($_GET['id']);
                    break;
                case 'posts':
                    $restController = new restController();
                    $answer = $restController->showPost($_GET['id']);
                    break;

                default:
                    break;
            }

        } else {
            $answer = array("error" => "action not found");
        }
        echo json_encode($answer);
    }
}

$router = new router();
$router->getAction();

mysql_close($link);mysql_close($link);
