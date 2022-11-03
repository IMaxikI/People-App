<?php
/**
 * Автор: Савостьянов Максим
 *
 * Дата реализации: 02.11.2022 23:50
 *
 * СУБД: MySql
 */

require_once('User.php');

if (!class_exists('User')) {
    exit('No User class');
}

/**
 *  Класс для работы со списками людей
 *
 * В конструктор передается два ассоциативных массива. В $options передаются выражения(больше, меньше, не равно).
 * ['nameColumn' => 'greater'|'less'|'neq']. В массиве $values передаются значения для сравнения. ['nameColumn' => 'value'].
 * Далее выполняется запрос в БД и возвращаются id записей удовлетворяющие заданному условию.
 *
 * getUsers - Получение массива экземпляров класса User из массива с id людей полученного в конструкторе.
 *
 * deleteUsers - Удаление людей из БД с помощью экземпляров класса User в соответствии с массивом, полученным в конструкторе
 */
class CollectionUser
{
    private array $userIds = [];
    public static $mysql;

    public function __construct($options, $values)
    {
        $query = "SELECT id FROM users WHERE";

        foreach ($options as $key => $val) {
            $sign = match ($val) {
                'neq' => '!=',
                'less' => '<',
                'greater' => '>',
            };

            $query .= " $key $sign '$values[$key]' AND";
        }
        $query = trim($query, 'AND');

        $result = mysqli_query(self::$mysql, $query);

        while ($row = mysqli_fetch_array($result)) {
            $this->userIds[] = $row['id'];
        }
    }

    public function getUsers(): array
    {
        $users = [];

        foreach ($this->userIds as $id) {
            $users[] = new User(id: $id);
        }

        return $users;
    }

    public function deleteUsers(): void
    {
        $users = $this->getUsers();

        foreach ($users as $user) {
            $user->delete();
        }
    }
}