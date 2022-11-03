<?php
/**
 * Автор: Савостьянов Максим
 *
 * Дата реализации: 02.11.2022 23:50
 *
 * СУБД: MySql
 */

declare(strict_types=1);

/**
 * Класс для работы с базой данных людей.
 *
 * Конструктор класса либо создает человека в БД с заданной информацией, либо берет информацию из БД по id.
 *
 * save - сохраняет данные из объекта в БД с помощью mysqli.
 *
 * load - загружает данные из БД по id.
 *
 * delete - удаляет запись по id в БД.
 *
 * convertAge - преобразует дату рождения в возраст
 *
 * convertGender - преобразует пол из двоичной системы в текстовую (жен, муж)
 *
 * formattingUser - форматирование человека с преобразованием возраста и (или) пола в зависимотси от параметров
 * (возвращает новый экземпляр StdClass со всеми полями изначального класса)
 */
class User
{
    private int $id;
    private string $name;
    private string $surname;
    private string $dateOfBirth;
    private int $gender;
    private string $cityOfBirth;

    public static $mysql;

    public function __construct(
        string $name = null,
        string $surname = null,
        string $dateOfBirth = null,
        int    $gender = null,
        string $cityOfBirth = null,
        int    $id = null
    ) {
        if (isset($id)) {
            $this->id = $id;
            $this->load();
        } elseif (isset($name, $surname, $dateOfBirth, $gender, $cityOfBirth)) {
            $this->name = $name;
            $this->surname = $surname;
            $this->dateOfBirth = $dateOfBirth;
            $this->gender = $gender;
            $this->cityOfBirth = $cityOfBirth;
            $this->validationData();
            $this->save();
        } else {
            throw new Exception('Pass all data to the constructor');
        }
    }

    public function save(): void
    {
        $query = 'INSERT INTO Users (name, surname, date_of_birth, gender, city_of_birth) VALUES (?, ?, ?, ?, ?)';
        $this->stmtExecute($query, 'sssis', $this->name, $this->surname, $this->dateOfBirth, $this->gender, $this->cityOfBirth);
    }

    public function delete(): void
    {
        $query = 'DELETE FROM users WHERE id=?';
        $this->stmtExecute($query, 'i', $this->id);
    }

    public static function convertAge($dateOfBirth): int
    {
        $birthday_timestamp = strtotime($dateOfBirth);
        $age = date('Y') - date('Y', $birthday_timestamp);
        if (date('md', $birthday_timestamp) > date('md')) {
            $age--;
        }

        return $age;
    }

    public static function convertGender($gender): string
    {
        return $gender == 0 ? 'жен' : 'муж';
    }

    public function formattingUser(bool $age = true, bool $gender = true): object
    {
        $resultUser = new stdClass();

        if ($age) {
            $resultUser->age = self::convertAge($this->dateOfBirth);
        }

        if ($gender) {
            $resultUser->strGender = self::convertGender($this->gender);
        }

        $resultUser->name = $this->name;
        $resultUser->surname = $this->surname;
        $resultUser->dateOfBirth = $this->dateOfBirth;
        $resultUser->gender = $this->gender;
        $resultUser->cityOfBirth = $this->cityOfBirth;

        return $resultUser;
    }

    private function load(): void
    {
        $query = 'SELECT * FROM users WHERE id=?';

        $result = mysqli_stmt_get_result($this->stmtExecute($query, 'i', $this->id));

        $row = mysqli_fetch_array($result);
        if (!$row) {
            throw new Exception("Записи с id=$this->id не существует!");
        }

        $this->name = $row['name'];
        $this->surname = $row['surname'];
        $this->dateOfBirth = $row['date_of_birth'];
        $this->gender = $row['gender'];
        $this->cityOfBirth = $row['city_of_birth'];
    }

    private function stmtExecute($query, $types, ...$vars): bool|mysqli_stmt
    {
        $stmt = mysqli_prepare(self::$mysql, $query);
        mysqli_stmt_bind_param($stmt, $types, ...$vars);
        mysqli_stmt_execute($stmt);

        return $stmt;
    }

    private function validationData(): void
    {
        $this->check_letters($this->name, 'name');
        $this->check_letters($this->surname, 'surname');
        $this->check_letters($this->cityOfBirth, 'cityOfBirth');

        if ($this->gender != 0 && $this->gender != 1) {
            throw new Exception('The gender should only consist of 1 or 0.');
        }

        $date = DateTime::createFromFormat('Y-m-d', $this->dateOfBirth);
        if (!($date && $date->format('Y-m-d') < date('Y-m-d'))) {
            throw new Exception('Wrong date entry. Example: YY-mm-dd');
        }
    }

    private function check_letters($var, $field): void
    {
        if (!ctype_alpha($var)) {
            throw new Exception("The $field must consist only of letters.");
        }
    }
}