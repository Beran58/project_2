<?php

abstract class BaseDBPage extends BasePage
{
    protected PDO $pdo;
    protected array $sort;
    protected function setUp(): void
    {
        session_start();
        parent::setUp();
        $this->pdo = DB::getInstance();
        $this->checkLogin();
        $this->setSort();
    }

    public function checkLogin()
    {
        if($_SESSION["logged"])
        {
            $this->logged = $_SESSION["logged"];
                if($_SESSION["admin"])
                {
                    $this->admin = $_SESSION["admin"];
                }
        }
        if(!$this->logged) {
            $username = filter_input(INPUT_POST, "username");
            $password = filter_input(INPUT_POST, "password");

            $stmt = $this->pdo->prepare("SELECT * from employee");
            $stmt->execute();
            foreach ($stmt as $row) {
                if ($username === $row->login && hash("sha256", $password) === $row->password) {
                    $this->logged = true;
                    $_SESSION["logged"] = true;
                    if($row->admin === 1)
                    {
                        $_SESSION["admin"] = true;
                    }
                    else $_SESSION["admin"] = false;
                }
            }
        }
        else return;
    }

    public function getSort() : array
    {
        if($this->sort)
        {
            return $this->sort;
        }
        else
            throw new Exception("You weren't supposed to get here",500);
            return "";

    }

    public function setSort() : bool
    {
        if(filter_input(INPUT_GET,"sortBy"))
        {
            $this->sort["orderBy"] = filter_input(INPUT_GET,"sortBy");
            $this->sort["orderHow"] = filter_input(INPUT_GET,"sortHow");
            return true;
        }
        else return false;
    }

}