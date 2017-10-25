<?php
use Phalcon\Mvc\Controller;
use Phalcon\Http\Request;
use Phalcon\Mvc\Model\Query;
use Phalcon\Mvc\Model\Query\Builder as Builder;
use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;
use Phalcon\Logger\Adapter\File as FileAdapter;

class ControllerBase extends Phalcon\Mvc\Controller {

    public function beforeExecuteRoute() {

        $self = $_SERVER['REQUEST_URI'];
        $paths = explode('/', $self);
        $controller = $paths[2];

        if ($controller !== "") {

            if (!$this->session->has('user')) {
                return $this->response->redirect("");
            }
        } else {
            
//            $authorize = $this->session->get('authorize');
//            $this->view->setVar('allowed', $authorize);

//            if ($this->session->has("user")) {
//                return $this->response->redirect("dashboard");
//            }
        }
    }

    /*
    Raw query select function to work in any version of phalcon
    */

    protected function rawSelect($statement) {
        $connection = $this->di->getShared("db");
        $success = $connection->query($statement);
        $success->setFetchMode(Phalcon\Db::FETCH_ASSOC);
        $success = $success->fetchAll($success);
        return $success;
    }

    public function tableQueryBuilder($sort = "", $order = "", $page = 0, $limit = 10) {

        $sortClause = "ORDER BY $sort $order";

        if (!$page || $page <= 0) {
            $page = 1;
        }
        if (!$limit) {
            $limit = 10;
        }

        $ofset = (int) ($page - 1) * $limit;
        $limitQuery = "LIMIT $ofset, $limit";

        return "$sortClause $limitQuery";
    }


    public function formatMobileNumber($mobile) {
        $mobile = preg_replace('/\s+/', '', $mobile);
        $input = substr($mobile, 0, -strlen($mobile) + 1);
        $number = '';
        if ($input == '0') {
            $number = substr_replace($mobile, '254', 0, 1);

            return $number;
        } elseif ($input == '+') {
            $number = substr_replace($mobile, '', 0, 1);
        } elseif ($input == '7') {
            $number = substr_replace($mobile, '2547', 0, 1);
        } else {
            $number = $mobile;
        }
        return $number;
    }

    public function calculateRate($numberOfDays=0){

        if($numberOfDays==0){
            return 0;
        }
        elseif ($numberOfDays>0 && $numberOfDays<=31) {
            return 0.1 ;
        }
        elseif ($numberOfDays>31 && $numberOfDays<=62) {
           return 0.15;
        }
        elseif ($numberOfDays>62 && $numberOfDays<=93) {
            return 0.2;
        }
        elseif ($numberOfDays>93 && $numberOfDays<=124) {
            return 0.25;
        }
        elseif ($numberOfDays>93 && $numberOfDays<=124) {
            return 0.3;
        }
        elseif ($numberOfDays>124 && $numberOfDays<=155) {
            return 0.35;
        }
        elseif ($numberOfDays>155 && $numberOfDays<=186) {
            return 0.35;
        }
    }

}
