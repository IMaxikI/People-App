<?php
/**
 * Автор: Савостьянов Максим
 *
 * Дата реализации: 02.11.2022 23:50
 *
 * СУБД: MySql
 */

require_once('User.php');
require_once('CollectionUser.php');

const HOST_NAME = 'localhost';
const USER_NAME = 'root';
const PASSWORD = '';
const DATABASE = 'people_app';

try {
    $mysql = mysqli_connect(HOST_NAME, USER_NAME, PASSWORD, DATABASE);

    User::$mysql = $mysql;
    CollectionUser::$mysql = $mysql;

    $obj = new User('sdf', 'Savo', '2002-01-23', 0, 'Minsk');
//    $obj = new User(id: '80');
//    var_dump($obj);
    var_dump($obj->formattingUser());

    // 'greater' 'less' 'neq'
//    $collUser = new CollectionUser(['date_of_birth' => 'greater'], ['date_of_birth' => '2000-01-20']);
//    var_dump($collUser->getUsers());
//     $collUser->deleteUsers();

} catch (Exception $exception) {
    echo $exception->getMessage();
}

