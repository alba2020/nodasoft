<?php

namespace Manager;

use Gateway\UserRepo;

class User
{
    const limit = 10;

    /**
     * Возвращает пользователей старше заданного возраста.
     * @param int $ageFrom
     * @return array
     */
    function getByAge(int $ageFrom): array
    {
        $ageFrom = (int)trim($ageFrom);

        return UserRepo::getByAge($ageFrom, self::limit);
    }

    /**
     * Возвращает пользователей по списку имен.
     * @return array
     */
    public static function getByNames(): array
    {
//        $users = [];
//        foreach ($_GET['names'] as $name) {
//            $users[] = UserRepo::getOneByName($name);
//        }
//
//        return $users;

        return UserRepo::getManyByName($_GET['names'], self::limit);
    }

    /**
     * Добавляет пользователей в базу данных.
     * @param $users
     * @return array
     */
    public function addUsers(array $users): array
    {
//        $ids = [];
//        UserRepo::getInstance()->beginTransaction();
//        foreach ($users as $user) {
//            try {
//                UserRepo::addOne($user['name'], $user['lastName'], $user['age']);
//                UserRepo::getInstance()->commit();
//                $ids[] = UserRepo::getInstance()->lastInsertId();
//            } catch (\Exception $e) {
//                UserRepo::getInstance()->rollBack();
//            }
//        }

        return UserRepo::addMany($users);
    }
}
