<?php

class Person
{
    private $surname;
    private $name;
    private $mail;
    private $date_of_birth;
    private $date_of_reg;
    private $status;

    public function __construct()
    {

    }

    public function getSurname()
    {
        return $this->surname;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getMail()
    {
        return $this->mail;
    }

    public function getDateOfBirth()
    {
        return $this->date_of_birth;
    }

    public function getDateOfReg()
    {
        return $this->date_of_reg;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getFormattedDateOfBirth()
    {
        $date = date_create_from_format('d.m.Y', $this->date_of_birth);
        return date_format($date, 'Y-m-d');
    }

    public function getFormattedDateOfReg()
    {
        $date = date_create_from_format('d.m.Y H:i', $this->date_of_reg);
        return date_format($date, 'Y-m-d H:i');
    }

    public function getFormattedStatus()
    {
        if ($this->status === "Off") {
            return 0;
        } else {
            return 1;
        }
    }

    public function setSurname($surname)
    {
        $this->surname = $surname;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setMail($mail)
    {
        $this->mail = $mail;
    }

    public function setDateOfBirth($date_of_birth)
    {
        $this->date_of_birth = $date_of_birth;
    }

    public function setDateOfReg($date_of_reg)
    {
        $this->date_of_reg = $date_of_reg;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function setFormattedDateOfBirth($date_of_birth)
    {
        $date = date_create_from_format('Y-m-d', $date_of_birth);
        $this->date_of_birth = date_format($date, 'd.m.Y');
    }

    public function setFormattedDateOfReg($date_of_reg)
    {
        $date = date_create_from_format('Y-m-d H:i:s', $date_of_reg);
        $this->date_of_reg = date_format($date, 'd.m.Y H:i');
    }

    public function setFormattedStatus($status)
    {
        if ($status === '0') {
            $this->status = 'Off';
        } else {
            $this->status = 'On';
        }
    }

}

const HOST = "localhost";
const USERNAME = "root";
const PASSWORD = "";
const DATABASE = "test";
const CSV_FILE_NAME = "persons.csv";
const JSON_FILE_NAME = "persons.json";

function get_data_from_file()
{
    $persons = array();
    $index = 0;
    if (($handle = fopen(CSV_FILE_NAME, "r")) !== FALSE) {
        // Считываем строки, пока не конец файла
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $person = new Person();
            $person->setSurname($data[0]);
            $person->setName($data[1]);
            $person->setMail($data[2]);
            $person->setDateOfBirth($data[3]);
            $person->setDateOfReg($data[4]);
            $person->setStatus($data[5]);
            $persons[$index] = $person;
            $index++;
        }
        fclose($handle);
    } else {
        echo "Не удалось открыть файл!";
    }
    return $persons;
}

function insert_query($mysqli, $persons)
{
    foreach ($persons as $person) {
        $sql = "INSERT INTO persons (surname, name, mail, birthdate, regdate, status) 
                          VALUES ('" .  $person->getSurname() . "', '" .
                                        $person->getName() . "', '" .
                                        $person->getMail() . "', '" .
                                        $person->getFormattedDateOfBirth() . "', '" .
                                        $person->getFormattedDateOfReg() . "', '" .
                                        $person->getFormattedStatus() . "')";
        if (!$mysqli->query($sql)) {
            echo "Не удалось выполнить запрос: (" . $mysqli->errno . ") " . $mysqli->error . "\n";
        }
    }
}

function update_query($mysqli)
{
    $random_num = mt_rand(1, 8);

    $sql = "SELECT status FROM persons WHERE id = " . $random_num;
    $res = $mysqli->query($sql);
    $row = $res->fetch_assoc();
    if ($row['status'] === '0') {
        $new_value = 1;
    } else {
        $new_value = 0;
    }
    $sql = "UPDATE persons SET status = " . $new_value . " WHERE id = " . $random_num;
    if (!$mysqli->query($sql)) {
        echo "Не удалось выполнить запрос: (" . $mysqli->errno . ") " . $mysqli->error . "\n";
    }
}

function select_query($mysqli)
{
    $persons = array();
    $sql = "SELECT * FROM persons";
    $res = $mysqli->query($sql);
    while ($row = $res->fetch_assoc()) {
        $person = new Person('', '', '', '', '', '');
        $person->setSurname($row['surname']);
        $person->setName($row['name']);
        $person->setMail($row['mail']);
        $person->setFormattedDateOfBirth($row['birthdate']);
        $person->setFormattedDateOfReg($row['regdate']);
        $person->setFormattedStatus($row['status']);
        $persons[] = $person;
    }
    return $persons;
}

function object_to_array($persons)
{
    $data_arr = array();
    foreach ($persons as $person) {
        $temp_array = array();
        $temp_array['surname'] = $person->getSurname();
        $temp_array['name'] = $person->getName();
        $temp_array['mail'] = $person->getMail();
        $temp_array['birthdate'] = $person->getDateOfBirth();
        $temp_array['regdate'] = $person->getDateOfReg();
        $temp_array['status'] = $person->getStatus();
        $data_arr[] = $temp_array;
    }
    return $data_arr;
}

$mysqli = new mysqli(HOST, USERNAME, PASSWORD, DATABASE);
if ($mysqli->connect_errno) {
    echo "Не удалось подключиться к MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

if (!$mysqli->query("DROP TABLE IF EXISTS persons") ||
    !$mysqli->query("CREATE TABLE persons (
                            id TINYINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                            surname VARCHAR(10) NOT NULL,
                            name VARCHAR(10) NOT NULL,
                            mail VARCHAR(25) NOT NULL,
                            birthdate DATE NOT NULL,
                            regdate DATETIME NOT NULL,
                            status TINYINT(1)
                            )")
) {
    echo "Не удалось создать таблицу: (" . $mysqli->errno . ") " . $mysqli->error;
}

insert_query($mysqli, get_data_from_file());

update_query($mysqli);

$json_string = json_encode(object_to_array(select_query($mysqli)), JSON_UNESCAPED_UNICODE);

if (!$handle = fopen(JSON_FILE_NAME, "w")) {
    echo "Невозможно создать файл (" . JSON_FILE_NAME . ")";
}

if (fwrite($handle, $json_string) === FALSE) {
    echo "Невозможно произвести запись в файл (" . JSON_FILE_NAME . ")";
}
var_dump($json_string);
fclose($handle);
$mysqli->close();