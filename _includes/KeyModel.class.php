<?php
class KeyModel
{
    public int $employee_id;
    public int $room_id;
    public string $employeeName;
    public string $roomName;

    public ?bool $checked;

    public static function getByEmp($employee_id) : ?self
    {
        $pdo = DB::getInstance();

        $key = new KeyModel();
        $sql = "SELECT * from `key` WHERE `employee`=:employee_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":employee_id",$employee_id);
        $isOk = $stmt->execute();
        $record = $stmt->fetch();
        $key->employee_id = $record->employee;
        $key->room_id = $record->room;

        $sql = "SELECT `name` FROM room WHERE `room_id`=:room_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":room_id",$key->room_id);
        $isOkR = $stmt->execute();
        $roomRecord = $stmt->fetch();
        $key->roomName = $roomRecord->name;

        $sql = "SELECT `surname` FROM employee WHERE `employee_id`=:employee_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":employee_id",$key->employee_id);
        $isOkE = $stmt->execute();
        $empRecord = $stmt->fetch();
        $key->employeeName = $empRecord->surname;

        if($isOk && $isOkR && $isOkE) {
            return $key;
                }
        else return NULL;
    }
    public static function getByRoom($room_id) : ?self
    {
        $pdo = DB::getInstance();

        $key = new KeyModel();
        $sql = "SELECT * from `key` WHERE `room`=:room_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":room_id",$room_id);
        $isOk = $stmt->execute();
        $record = $stmt->fetch();
        $key->employee_id = $record->employee;
        $key->room_id = $record->room;

        $sql = "SELECT `name` FROM room WHERE `room_id`=:room_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":room_id",$key->room_id);
        $isOkR = $stmt->execute();
        $roomRecord = $stmt->fetch();
        $key->roomName = $roomRecord->name;

        $sql = "SELECT `surname` FROM employee WHERE `employee_id`=:employee_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":employee_id",$key->employee_id);
        $isOkE = $stmt->execute();
        $empRecord = $stmt->fetch();
        $key->employeeName = $empRecord->surname;


        if($isOk && $isOkR && $isOkE) {
            return $key;
        }
        else return NULL;
    }

    public function setChecked($set)
    {
        if($set === true) {
            $this->checked = true;
        }
        else $this->checked = false;
    }
}