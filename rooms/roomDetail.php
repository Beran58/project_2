<?php
require_once "../_includes/bootstrap.inc.php";
final class Page extends BaseDBPage
{
    public function __construct()
    {
        parent::__construct();
        $this->title = "Room Detail";
    }

    protected function body(): string
    {
        if($_SESSION["logged"])
        {
            if(filter_input(INPUT_GET,"roomId",FILTER_VALIDATE_INT));
            {
                $room = RoomModel::getById(filter_input(INPUT_GET,"roomId",FILTER_VALIDATE_INT));
                return $this->m->render("roomDetail",["room"=>$room]);
            }

        }
        else return $this->m->render("login");
    }
}
(new Page())->render();
