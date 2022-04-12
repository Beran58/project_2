<?php

class RoomModel
{
    public ?int $room_id;
    public string $name = "";
    public string $no = "";
    public ?string $phone = null;

    public array $people;
    /*public array $peopleNames;*/

    private array $validationErrors = [];

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    public function __construct()
    {
    }

    public function insert() : bool {

        $sql = "INSERT INTO room (name, no, phone) VALUES (:name, :no, :phone)";

        $stmt = DB::connect()->prepare($sql);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':no', $this->no);
        $stmt->bindParam(':phone', $this->phone);

        return $stmt->execute();
    }

    public function update() : bool
    {
        $sql = "UPDATE room SET name=:name, no=:no, phone=:phone WHERE room_id=:room_id";
        $stmt = DB::Connect()->prepare($sql);
        $stmt->bindParam(':room_id', $this->room_id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':no', $this->no);
        $stmt->bindParam(':phone', $this->phone);

        return $stmt->execute();
    }

    public static function getAll($orderBy = "name",$orderDir =  "ASC") : POStatement
    {
        $stmt = DB::connect()->prepare("Select * from `room` ORDER BY `{$orderBy} {$orderDir}`");
        $stmt->execute();
        return $stmt;
    }

    public static function getById($roomId) : ?self
    {
        $stmt = DB::connect()->prepare("Select * from `room` WHERE `room_id`=:room_id");
        $stmt->bindParam(":room_id",$roomId);
        $stmt->execute();

        $record = $stmt->fetch();
        if(!$record)
        {
            return null;
        }

        $stmt = DB::connect()->prepare("SELECT `employee` FROM `key` WHERE `room`=:room_id");
        $stmt->bindParam(":room_id",$roomId);
        $stmt->execute();
        $keyRecord = [];
        foreach ($stmt as $row)
        {
            array_push($keyRecord,KeyModel::getByEmp($row->employee));
        }

        /*$keyNameRecord = [];
        foreach($keyRecord as $row) {
            $stmt = DB::connect()->prepare("SELECT `surname` FROM `employee` WHERE `employee_id`=:employee_id");
            $stmt->bindParam(":employee_id",$row);
            $stmt->execute();
            array_push($keyNameRecord,$stmt->fetch()->surname);
            }*/

        $model = new self();
        $model->room_id = $record->room_id;
        $model->name = $record->name;
        $model->phone = $record->phone;
        $model->no = $record->no;
        $model->people = $keyRecord;
      /*  $model->peopleNames = $keyNameRecord;*/

        return $model;
    }

    public static function deleteById(int $room_id) : bool
    {
        $deleteRoom = false;
        $deleteKeys = false;
        $deleteEmp = false;


        $sql = "DELETE FROM `key` WHERE `room` = :room_id";
        $stmt = DB::connect()->prepare($sql);
        $stmt->bindParam(":room_id",$room_id);
        $deleteKeys = $stmt->execute();

        $sql = "UPDATE `employee` SET `room`=NULL WHERE `room`=:room_id";
        $stmt = DB::connect()->prepare($sql);
        $stmt->bindParam(':room_id',$room_id);
        $deleteEmp = $stmt->execute();

        $sql = "DELETE FROM room WHERE room_id=:room_id";
        $stmt = DB::connect()->prepare($sql);
        $stmt->bindParam(':room_id', $room_id);
        $deleteRoom = $stmt->execute();

        if($deleteRoom && $deleteKeys && $deleteEmp)
        {return true;}
        else return false;
    }

    public function delete() : bool
    {
        return self::deleteById($this->room_id);
    }

    public static function getFromPost() : self {
        $room = new RoomModel();

        $room->room_id = filter_input(INPUT_POST, "room_id", FILTER_VALIDATE_INT);
        $room->name = filter_input(INPUT_POST, "name");
        $room->no = filter_input(INPUT_POST, "no");
        $room->phone = filter_input(INPUT_POST, "phone");

        return $room;
    }

    public function validate() : bool {
        $isOk = true;
        $errors = [];

        if (!$this->name){
            $isOk = false;
            $errors["name"] = "Room name cannot be empty";
        }

        if (!$this->no){
            $isOk = false;
            $errors["no"] = "Room number cannot be empty";
        }
        if ($this->phone === ""){
            $this->phone = null;
        }

        $this->validationErrors = $errors;
        return $isOk;
    }
}