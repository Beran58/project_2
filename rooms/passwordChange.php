<?php
require_once "../_includes/bootstrap.inc.php";
class Page extends BaseDBPage
{
    public function __construct()
    {
        parent::__construct();
        $this->title = "Password change";
    }

    public function change() : bool
    {
        $username = filter_input(INPUT_POST,"username");
        $passwordOld = filter_input(INPUT_POST,"passwordOld");
        $passwordNew = filter_input(INPUT_POST,"passwordNew");

        $stmt = DB::connect()->prepare("SELECT `login`,`password` FROM employee WHERE `login`=:login");
        $stmt->bindParam(":login",$username);
        $stmt->execute();
                if(hash("sha256",$passwordOld) === $stmt->fetch()->password)
                {
                    $passwordNewHashed = hash("sha256",$passwordNew);
                    $stmt = DB::connect()->prepare("UPDATE employee SET `password`=:password");
                    $stmt->bindParam(":password",$passwordNewHashed);
                    return $stmt->execute();
                }
                else return false;
        }

    protected function body(): string
{
    if (filter_input(INPUT_POST,"passwordNew"))
    {
        if($this->change())
        {
           return $this->m->render("reportSuccess",["data"=>"Password changed successfully"]);
        }
        else return $this->m->render("reportFail",["data"=>"Failed to change password"]);
    }
    else return $this->m->render("passwordChange");
}
}
(new Page)->render();
