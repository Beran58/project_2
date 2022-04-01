<?php

class EmployeeModel
{
    public ?int $employee_id;
    public string $name = "";
    public string $surname = "";
    public string $job = "";
    public int $wage;
    public ?int $room;
    public ?string $roomName;
    public array $keys = [];
    public ?string $login = "";
    public ?string $password = "";
    public ?bool $admin;

    private array $validationErrors = [];

    public function __construct()
    {
        $this->setId();
    }

    public function setId(): bool
    {
        if(filter_input(INPUT_GET,"employeeId"))
        {
            $this->employee_id = filter_input(INPUT_GET,"employeeId");
            return true;
        }
        else return false;
    }
    public function getId(): int
    {
        return $this->employee_id;
    }

    public function insert() : bool
    {
        $sql = "INSERT INTO employee (name, surname, job, wage, room, login, password, admin) VALUES(:name, :surname, :job, :wage, :room, :login, :password, :admin)";
        $stmt = DB::connect()->prepare($sql);

        $stmt->bindParam(":name",$this->name);
        $stmt->bindParam(":surname",$this->surname);
        $stmt->bindParam(":job",$this->job);
        $stmt->bindParam(":wage",$this->wage);
        $stmt->bindParam(":room",$this->room);
        $stmt->bindParam(":login",$this->login);
        $this->password = hash("sha256",$this->password);
        $stmt->bindParam(":password", $this->password);
        $t = 1;
        $f = 0;
        if($this->admin === true) {
            $stmt->bindParam(":admin", $t);
        }
        else $stmt->bindParam(":admin", $f);
        $insertEmp = $stmt->execute();

        $sql="SELECT employee_id FROM employee WHERE login=:login";
        $stmt = DB::Connect()->prepare($sql);
        $stmt->bindParam(":login",$this->login);
        $stmt->execute();
        $this->employee_id = $stmt->fetch()->employee_id;


        $sql = "DELETE FROM `key` WHERE `employee`=:employee_id";
        $stmt = DB::connect()->prepare($sql);
        $stmt->bindParam(":employee_id",$this->employee_id);
        $stmt->execute();

        foreach ($this->keys as $row)
        {
            $sql = "INSERT INTO `key`(employee,room) VALUES (:employee , :room)";
            $stmt = DB::connect()->prepare($sql);
            $stmt->bindParam(":employee",$this->employee_id);
            $stmt->bindParam(":room",$row);
            $insertKeys=$stmt->execute();
        }
        if($insertEmp && $insertKeys)
        {
            return true;
        }
        else return false;
    }
    public function update() : bool
    {
        $updateEmp = false;

        $sql = "UPDATE employee SET name=:name, surname=:surname, job=:job, wage=:wage, room=:room, login=:login, password=:password, admin=:admin WHERE employee_id=:employee_id";
        $stmt = DB::Connect()->prepare($sql);
        $stmt->bindParam(":name",$this->name);
        $stmt->bindParam(":surname",$this->surname);
        $stmt->bindParam(":job",$this->job);
        $stmt->bindParam(":wage",$this->wage);
        $stmt->bindParam(":room",$this->room);
        $stmt->bindParam(":login",$this->login);
        $this ->password = hash("sha256",$this->password);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":employee_id",$this->employee_id);
        $t = 1;
        $f = 0;
        if($this->admin === true) {
            $stmt->bindParam(":admin", $t);
        }
        else $stmt->bindParam(":admin", $f);
        $updateEmp = $stmt->execute();

        $updateKeys = false;

        $sql = "DELETE FROM `key` WHERE `employee`=:employee_id";
        $stmt = DB::connect()->prepare($sql);
        $stmt->bindParam(":employee_id",$this->employee_id);
        $stmt->execute();

        foreach ($this->keys as $row)
        {
                   $sql = "INSERT INTO `key`(employee,room) VALUES (:employee , :room)";
                   $stmt = DB::connect()->prepare($sql);
                   $stmt->bindParam(":employee",$this->employee_id);
                   $stmt->bindParam(":room",$row);

                   $updateKeys=$stmt->execute();
        }
        if($updateEmp && $updateKeys) {
            return true;
        }
        else return false;
    }
    public static function getById($employeeId) : ?self
    {
        $stmt = DB::connect()->prepare("SELECT * from `employee` WHERE `employee_id`=:employee_id");
        $stmt->bindParam(":employee_id",$employeeId);
        $stmt->execute();

        $record = $stmt->fetch();

        $stmt = DB::connect()->prepare("SELECT name FROM `room` WHERE `room_id`=:room_id");
        $stmt->bindParam(":room_id",$record->room);
        $stmt->execute();

        $roomRecord = $stmt->fetch();

        $stmt = DB::connect()->prepare("SELECT `room` FROM `key` WHERE `employee`=:employee_id");
        $stmt->bindParam(":employee_id",$employeeId);
        $stmt->execute();

        $keyRecord = [];
        foreach ($stmt as $row)
        {
            array_push($keyRecord,KeyModel::getByRoom($row->room));
        }

       /* $stmt = DB::connect()->prepare("SELECT `name`,`room_id` FROM `room` WHERE `room_id`=:room_id");
        foreach ($keyRecord as $row)
        {
            $stmt->bindParam(":room_id",$row);
            $stmt->execute();
            $name = $stmt->fetch()->name;
            $keyRecord[$row] = $name;
            array_push($keyNameRecord,$name);
        }*/

       /* if(!$record || !$roomRecord)
        {
            return null;
        }*/
        $model = new self();
        $model->employee_id = $record->employee_id;
        $model->name = $record->name;
        $model->surname = $record->surname;
        $model->job = $record->job;
        $model->wage = $record->wage;
        $model->room = $record->room;
        $model->roomName = $roomRecord->name;
        $model->keys = $keyRecord;
        $model->login = $record->login;
        $model->password = $record->password;


        return $model;
    }

    public function rewriteById(EmployeeModel $employee)
    {
        $this->employee_id = $employee->employee_id;
        $this->name = $employee->name;
        $this->surname = $employee->surname;
        $this->job = $employee->job;
        $this->wage = $employee->wage;
        if($employee->room === NULL)
        {
            $this->room = NULL;
        }
        else $this->room = $employee->room;
        $this->roomName = $employee->roomName;
        $this->login = $employee->login;
        $this->keys = $employee->keys;
        $this->password = $employee->password;
    }

    public static function deleteById(int $employee_id) : bool
    {
        $deleteKeys = false;
        $sql = "DELETE FROM `key` WHERE `employee`=:employee_id";
        $stmt = DB::connect()->prepare($sql);
        $stmt->bindParam(":employee_id",$employee_id);
        $deleteKeys = $stmt->execute();

        $deleteEmp = false;
        $sql = "DELETE FROM employee WHERE employee_id=:employee_id";
        $stmt = DB::Connect()->prepare($sql);
        $stmt->bindParam(':employee_id', $employee_id);
        $deleteEmp = $stmt->execute();

        if($deleteKeys && $deleteEmp)
        {
            return true;
        }
        else return false;
    }

    static function getFromPost() : self
    {

        $employee = new EmployeeModel();
        $employee->employee_id = filter_input(INPUT_POST,"employee_id",FILTER_VALIDATE_INT);
        /*$employee = $employee->getById($employee->employee_id);*/
        $employee->name = filter_input(INPUT_POST,"name");
        $employee->surname = filter_input(INPUT_POST,"surname");
        $employee->job = filter_input(INPUT_POST,"job");
        $employee->wage = filter_input(INPUT_POST,"wage",FILTER_VALIDATE_INT);
        $employee->login = filter_input(INPUT_POST,"login");
        $employee->password = filter_input(INPUT_POST,"password");
        $employee->room = filter_input(INPUT_POST,"room",FILTER_VALIDATE_INT);
        $employee->keys = $_POST["keys"];
        $employee->admin = filter_input(INPUT_POST,"admin",FILTER_VALIDATE_BOOLEAN);
        if($employee->admin === NULL)
        {
            $employee->admin = false;
        }
        return $employee;
    }

    public function delete() : bool
    {
        return self::deleteById($this->employee_id);
    }

    public function getValidationErrors() : array
    {
        return $this->validationErrors;
    }

    public function validate() : bool {
        $isOk = true;
        $errors = [];

        if (!$this->name){
            $isOk = false;
            $errors["name"] = "Employee name cannot be empty";
        }

        if (!$this->surname){
            $isOk = false;
            $errors["surname"] = "Employee surname cannot be empty";
        }

       if (!$this->job){
            $isOk = false;
            $errors["job"] = "Employee job cannot be empty";
        }

        if (!$this->wage){
            $isOk = false;
            $errors["wage"] = "Employee wage cannot be empty";
        }

        if (!$this->room){
            $isOk = false;
            $errors["room"] = "Employee room cannot be empty";
        }

        if (!$this->login){
            $isOk = false;
            $errors["login"] = "Employee login cannot be empty";
        }
        if (!$this->password){
            $isOk = false;
            $errors["password"] = "Employee password cannot be empty";
        }
        $this->validationErrors = $errors;
        return $isOk;
    }
}