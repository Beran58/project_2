<?php
require_once "../_includes/bootstrap.inc.php";
final class Page extends BaseDBPage{
    public function __construct()
    {
        parent::__construct();
        $this->title = "Employee List";
    }

    protected function body(): string
    {
        if($this->setSort()) {
            $order = $this->getSort();
            $orderBy = $order["orderBy"];
            $allowed = ["surname","job","wage"];
            $key = array_search($orderBy,$allowed);
            if ($key === false)
            {
                throw new Exception("Invalid sorting clause (orderBy)", 400);
            }
            $orderHow = $order["orderHow"];
            $allowed = ["ASC","DESC"];
            $key = array_search($orderHow,$allowed);
            if ($key === false)
            {
                throw new Exception("Invalid sorting clause (orderHow)", 400);
            }
            $stmt = $this->pdo->prepare("SELECT * from `employee` ORDER BY `$orderBy` $orderHow");
        }
        else {
            $stmt = $this->pdo->prepare("SELECT * from `employee`");
        }
            $stmt->execute();
        if($this->logged) {
            return $this->m->render("employeeList", ['employees' => $stmt, 'employeeDetailName'=>'employeeDetail.php','source'=>'employeeList.php',"isAdmin"=>$this->admin]);
        }
        else return $this->m->render("login",[]);
    }
}
(new Page())->render();