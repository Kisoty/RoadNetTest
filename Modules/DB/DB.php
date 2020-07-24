<?php

declare(strict_types=1);

namespace Modules;

use PDO;

Class DB
{
    private $db_connect;

    public function __construct()
    {
        $this->connect();
        if (!is_int($this->db_connect->exec('use roadnet_db'))) //подключение к нужной БД,проверка ее существования. если нет - создать
        {
            $this->setUp();
        }
    }

    public function connect(): void
    {
        $config = require_once 'config/db_config.php';
        $this->db_connect = new PDO("mysql:host=" . $config['host'] . ";charset=" . $config['charset'], $config['user'], $config['pass']);
    }

    public function setUp(): void
    {
        $this->db_connect->exec('CREATE DATABASE IF NOT EXISTS `roadnet_db`; use `roadnet_db');
        $this->db_connect->exec('CREATE TABLE IF NOT EXISTS `reviews` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
            `review` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
            `refs` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
            `rating` TINYINT(1) NOT NULL , `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        );');
        $this->db_connect = NULL;
        throw new \Exception('Database and table created, send request again');
    }

    public function getReviews(string $dateSort = '', string $rateSort = '', int $page = 1, int $limit = 10): array
    {
        $sql = 'SELECT `id`, `name`, `rating`, SUBSTRING_INDEX(`refs`,\'&\',1) as \'first_ref\' FROM `reviews` ORDER BY ';

        //Что-то надо сделать с вставкой int и asc/desc в подготовленных запросах. он их экранирует кавычками, что ведет к ошибке в запросе.
        //Не стал тратить время на это
        if (!empty($rateSort))
            $sql .= "`rating` $rateSort, ";

        $sql .= $dateSort ? "`date` $dateSort" : '`date` DESC';
        $offset = ($page - 1) * $limit;
        $sql .= " LIMIT $limit OFFSET $offset ;";

        $stmt = $this->db_connect->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$result)
            throw new \Exception('No reviews found');
        return $result;
    }

    public function getReviewById(int $id, array $additionalFields = null): array
    {
        $sql = 'SELECT `name`, `rating`, SUBSTRING_INDEX(`refs`,\'&\',1) as \'first_ref\'';

        if ($additionalFields) {
            foreach ($additionalFields as $value) {
                $sql .= ", `$value`";
            }
        }

        $sql .= ' FROM `reviews` WHERE `id` = (:id)';
        $stmt = $this->db_connect->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$result)
            throw new \Exception('No reviews with given id');
        return $result[0];
    }

    public function createReview(string $name, string $review, int $rate, string $refs) : int
    {
        $sql = 'INSERT INTO `reviews` (`name`,`review`,`refs`,`rating`) VALUES (:name,:review,:refs,:rating); SELECT LAST_INSERT_ID();';
        $stmt = $this->db_connect->prepare($sql);

        if ($stmt->execute(['name' => $name, 'review' => $review, 'refs' => $refs, 'rating' => $rate]))
            return (int)$this->db_connect->lastInsertId();
        else
            throw new \Exception('Values can\'t be inserted into database');
    }

}