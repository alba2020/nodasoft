<?php

namespace Gateway;

use PDO;

class UserRepo
{
    /**
     * @var PDO
     */
    private static $instance;

    /**
     * Реализация singleton
     * @return PDO
     */
    public static function getInstance(): PDO
    {
        if (is_null(self::$instance)) {
            $dsn = 'mysql:dbname=db;host=127.0.0.1';
            $user = 'dbuser';
            $password = 'dbpass';
            self::$instance = new PDO($dsn, $user, $password);
        }

        return self::$instance;
    }

    /**
     * Возвращает список пользователей старше заданного возраста.
     * @param int $ageFrom
     * @return array
     */
    public static function getByAge(int $ageFrom, int $limit=10): array
    {
        $sql =  "SELECT id, name, lastName, from, age, settings ";
        $sql .= "FROM Users ";
        $sql .= "WHERE age > :ageFrom ";
        $sql .= "LIMIT :limit";

        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute([':ageFrom' => $ageFrom, ':limit' => $limit]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $users = [];
        foreach ($rows as $row) {
            try {
                $settings = json_decode($row['settings']);
            } catch (\Exception $e) {
                // log $e->getMessage();
                $settings = [];
            }
            $users[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'lastName' => $row['lastName'],
                'from' => $row['from'],
                'age' => $row['age'],
                'key' => $settings['key'] ?? 'no_data',
            ];
        }

        return $users;
    }

    /**
     * Возвращает пользователя по имени.
     * @param string $name
     * @return array
     */
    public static function getOneByName(string $name): array
    {
        $sql = "SELECT id, name, lastName, from, age, settings ";
        $sql .= "FROM Users WHERE name = :name LIMIT 1";

        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute([':name' => $name]);

        $user_by_name = $stmt->fetch(PDO::FETCH_ASSOC);

        try {
            $settings = json_decode($user_by_name['settings']);
        } catch (\Exception $e) {
            // log $e->getMessage();
            $settings = [];
        }

        return [
            'id' => $user_by_name['id'],
            'name' => $user_by_name['name'],
            'lastName' => $user_by_name['lastName'],
            'from' => $user_by_name['from'],
            'age' => $user_by_name['age'],
            'settings' => $settings,
        ];
    }

    /**
     * Возвращает список пользователей по имени.
     * @param array $names
     * @return array
     */
    public static function getManyByName(array $names, $limit = 10): array
    {
        $sql = "SELECT id, name, lastName, from, age, settings ";
        $sql .= "FROM Users WHERE name IN (:names) LIMIT :limit";

        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute([':names' => join(', ', $names),
                        ':limit' => $limit]);

        $user_by_name = $stmt->fetch(PDO::FETCH_ASSOC);

        try {
            $settings = json_decode($user_by_name['settings']);
        } catch (\Exception $e) {
            // log $e->getMessage();
            $settings = [];
        }

        return [
          'id' => $user_by_name['id'],
          'name' => $user_by_name['name'],
          'lastName' => $user_by_name['lastName'],
          'from' => $user_by_name['from'],
          'age' => $user_by_name['age'],
          'settings' => $settings,
        ];
    }


    /**
     * Добавляет пользователя в базу данных.
     * @param string $name
     * @param string $lastName
     * @param int $age
     * @return string
     */
    public static function addOne(string $name, string $lastName, int $age): string
    {
        $sql =  "INSERT INTO Users (name, lastName, age) ";
        $sql .= "VALUES (:name, :lastName, :age)";

        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute([':name' => $name, ':lastName' => $lastName, ':age' => $age]);

        return self::getInstance()->lastInsertId();
    }

    /**
     * Добавляет список пользователей в базу данных
     * @param  array  $users
     *
     * @return array $ids
     */
    public static function addMany(array $users): array
    {
        $sql =  "INSERT INTO Users (name, lastName, age) ";
        $sql .= "VALUES (:name, :lastName, :age)";

        $stmt = self::getInstance()->prepare($sql);

        $ids = [];
        try {
            self::getInstance()->beginTransaction();
            foreach($users as $user) {
                $stmt->execute([':name' => $user['name'] ?? '',
                  ':lastName' => $user['lastName'] ?? '',
                  'age' => $user['age'] ?? 0
                ]);
                $ids = self::getInstance()->lastInsertId();
            }
            self::getInstance()->commit();
            return $ids;
        } catch (\Exception $e) {
            self::getInstance()->rollBack();
            return [];
        }
    }
}
