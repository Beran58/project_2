<?php
require_once "../_includes/bootstrap.inc.php";
final class Page extends BaseDBPage{
    public function __construct(){
        parent::__construct();
        $this->title = "Main Menu";
        $this->logged = false;
        if(filter_input(INPUT_POST, "logged", FILTER_VALIDATE_BOOLEAN)) {
            $this->logged = filter_input(INPUT_POST, "logged", FILTER_VALIDATE_BOOLEAN);
        }
}
    protected function body():string
    {
        if($this->logged) {
            return $this->m->render("menu", []);
        }
        else return $this->m->render("login",["page"=>"index.php"]);
    }
}
(new Page())->render();
