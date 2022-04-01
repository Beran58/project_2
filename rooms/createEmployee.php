<?php

require_once "../_includes/bootstrap.inc.php";

final class Page extends BaseDBPage{

    const STATE_FORM_REQUESTED = 1;
    const STATE_DATA_SENT = 2;
    const STATE_REPORT_RESULT = 3;

    const RESULT_SUCCESS = 1;
    const RESULT_FAIL = 2;

    private EmployeeModel $employeeModel;
    private int $state;
    private int $result;

    public function __construct()
    {
        parent::__construct();
        $this->title = "Employee register";
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->getState();

        if ($this->state === self::STATE_REPORT_RESULT) {
            if ($this->result === self::RESULT_SUCCESS) {
                $this->title = "Employee registered";
            } else {
                $this->title = "Employee registration failed";
            }
            return;
        }

        if ($this->state === self::STATE_DATA_SENT) {

            $this->employeeModel = EmployeeModel::getFromPost();
            if ($this->employeeModel->validate()) {
                //uložím
                if ($this->employeeModel->insert()) {
                    $this->redirect(self::RESULT_SUCCESS);
                } else {
                    $this->redirect(self::RESULT_FAIL);
                }
            } else {
                $this->state = self::STATE_FORM_REQUESTED;
                $this->title = "Invalid data";
            }
        } else {
            $this->title = "Register new employee";
            $this->employeeModel = new EmployeeModel();
        }

    }


    protected function body(): string {
        if ($this->admin) {
            $keys = [];

            $sql = "SELECT * FROM `room`";
            $stmt = DB::connect()->query($sql);
            $stmt->execute();
            foreach ($stmt as $row)

                $stmt2 = DB::connect()->query($sql);
            $stmt2->execute();


            if ($this->state === self::STATE_FORM_REQUESTED) {
                if ($this->logged) {
                    return $this->m->render("employeeForm", ["employee" => $this->employeeModel, "rooms" => $stmt, "keys" => $stmt2, "errors" => $this->employeeModel->getValidationErrors(), "create" => true]);
                } else return $this->m->render("login");
            } elseif ($this->state === self::STATE_REPORT_RESULT) {
                if ($this->result === self::RESULT_SUCCESS) {
                    return $this->m->render("reportSuccess", ["data" => "Employee registered successfully."]);
                } else {
                    return $this->m->render("reportFail", ["data" => "Employee registration failed. Please contact adiministrator or try again later."]);
                }

            } else return "";
        }
        else return $this->m->render("reportFail",["data"=>"You do not have permission to access this page"]);
    }

    private function getState() : void {
        //je už hotovo?
        $result = filter_input(INPUT_GET, "result", FILTER_VALIDATE_INT);
        if ($result === self::RESULT_SUCCESS) {
            $this->state = self::STATE_REPORT_RESULT;
            $this->result = self::RESULT_SUCCESS;
            return;
        } elseif ($result === self::RESULT_FAIL) {
            $this->state = self::STATE_REPORT_RESULT;
            $this->result = self::RESULT_FAIL;
            return;
        }

        //byl odeslán formulář
        $action = filter_input(INPUT_POST, "action");
        if ($action === "create") {
            $this->state = self::STATE_DATA_SENT;
            return;
        }

        $this->state = self::STATE_FORM_REQUESTED;
    }

    private function redirect(int $result) : void {
        //odkaz sám na sebe, bez query string atd.
        $location = strtok($_SERVER['REQUEST_URI'], '?');

        header("Location: {$location}?result={$result}");
        exit;
    }
}

(new Page())->render();