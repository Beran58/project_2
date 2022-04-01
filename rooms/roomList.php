<?php
require_once "../_includes/bootstrap.inc.php";
final class Page extends BaseDBPage{
    public function __construct()
    {
        parent::__construct();
        $this->title = "Room Listings";
    }

    protected function body(): string
    {
        if($this->setSort())
        {
            $order = $this->getSort();
            $orderBy = $order["orderBy"];
            $allowed = ["name","no","phone"];
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
            $stmt = $this->pdo->prepare("SELECT * FROM `room` ORDER BY `$orderBy` $orderHow");
        }
        else $stmt = $this->pdo->prepare("SELECT * FROM `room`");
        $stmt->execute();
        if($this->logged) {
            return $this->m->render("roomList", ["rooms" => $stmt, "roomDetailName" => "roomDetail.php","source"=>"roomList.php","admin"=>$this->admin]);
        }
        else return $this->m->render("login",[]);
    }
}
(new Page())->render();

