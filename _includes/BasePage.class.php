<?php

abstract class BasePage
{

    protected MustacheRunner $m;
    protected string $title;
    protected bool $logged = false;
    protected bool $admin = false;
    public function __construct()
    {
        $this->m = new MustacheRunner();
    }
    public function render() : void 
    {


            $this->setUp();
            $html = $this->header();
            $html .= $this->body();
            $html .= $this->footer();
            echo $html;

            $this->wrapUp();
            exit;

        /*catch(RequestException $e)
        {
            $errorPage = new ErrorPage($e->getCode());
            $errorPage->render();
        }*/

        /*catch(Exception $e)
        {
            if(localConfig::DEBUG){
                dump ($e);
            }
            $ePage = new ErrorPage();
            $ePage->render();
        }*/
    }

   protected function setUp() : void
    {
        
    }

    public function header() : string 
    {
        return $this->m->render("head", ["title" => $this->title]);
    }

    abstract protected function body() : string; 

    public function footer() : string 
    {
        return $this->m->render("foot");
    }

    public function  wrapUp() : string
    {
        return"";
    }
}