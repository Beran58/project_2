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
        $this->title = "Employee update";
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->getState();

        if ($this->state === self::STATE_REPORT_RESULT) {
            if ($this->result === self::RESULT_SUCCESS) {
                $this->title = "Employee updated";
            } else {
                $this->title = "Employee update failed";
            }
            return;
        }

        if ($this->state === self::STATE_DATA_SENT) {
            $this->employeeModel = EmployeeModel::getFromPost();
            if ($this->employeeModel->validate()) {
                if ($this->employeeModel->update()) {
                    $this->redirect(self::RESULT_SUCCESS);
                } else {
                    $this->redirect(self::RESULT_FAIL);
                }
            }else {
                $this->state = self::STATE_FORM_REQUESTED;
                $this->title = "Employee update: Invalid data";
            }
        } else {
            $this->title = "Update employee";
            $employeeId = filter_input(INPUT_GET,"employeeId",FILTER_VALIDATE_INT);
            if($employeeId)
            {
                $this->employeeModel = EmployeeModel::getById($employeeId);
                if(!$this->employeeModel)
                {
                    throw new RequestException(404);
                }
            }
            else throw new RequestException(400);

        }

    }


    protected function body(): string {
        if($this->admin) {
            if ($this->state === self::STATE_FORM_REQUESTED) {
                $employeeId = filter_input(INPUT_GET, "employeeId", FILTER_VALIDATE_INT);
                if ($employeeId) {
                    $this->employeeModel = EmployeeModel::getById($employeeId);
                    if (!$this->employeeModel) {
                        throw new RequestException(404);
                    }
                } else throw new RequestException(400);

                $keys = [];

                $sql = "SELECT * FROM `room`";
                $stmt = DB::connect()->query($sql);
                $stmt->execute();

                $stmt2 = DB::connect()->query($sql);
                $stmt2->execute();

                foreach ($stmt2 as $row) {
                    $key = KeyModel::getByRoom($row->room_id);
                    foreach ($this->employeeModel->keys as $item) {
                        if (strval($row->room_id) === $item->room_id) {
                            $key->checked = true;
                        }
                    }
                    array_push($keys, $key);
                }

                if ($this->logged) {
                    return $this->m->render("employeeForm", ["employee" => $this->employeeModel, "rooms" => $stmt, "keys" => $keys,
                        "errors" => $this->employeeModel->getValidationErrors(),
                        "update" => true
                    ]);
                } else return $this->m->render("login");
            } elseif ($this->state === self::STATE_REPORT_RESULT) {
                if ($this->result === self::RESULT_SUCCESS) {
                    return $this->m->render("reportSuccess", ["data" => "Employee update successful"]);
                } else {
                    return $this->m->render("reportFail", ["data" => "Employee update failed. Please contact adiministrator or try again later."]);
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
        if ($action === "update") {
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
