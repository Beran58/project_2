<?php
require_once "../_includes/bootstrap.inc.php";
final class Page extends BaseDBPage
{
    public function __construct()
    {
        parent::__construct();
        $this->title = "Employee Detail";
    }

    protected function body() : string
    {
        if($_SESSION["logged"]) {
            $emp_id = filter_input(INPUT_GET,"employeeId",FILTER_VALIDATE_INT);
            $employee = EmployeeModel::getById($emp_id);
            return $this->m->render("employeeDetail", ["employee" => $employee,"isAdmin"=>$_SESSION["admin"]]);
        }
        else return $this->m->render("login");
    }
}
(new Page())->render();