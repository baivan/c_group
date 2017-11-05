<?php

use Phalcon\Mvc\Controller;
use Phalcon\Http\Request;
use Phalcon\Mvc\Model\Query;
use Phalcon\Mvc\Model\Query\Builder as Builder;
use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;
use Phalcon\Logger\Adapter\File as FileAdapter;

class LoginController extends Controller {

    public function initialize() {
        $this->curlRequestInstance = CurlRequestResponse::getInstance();
        $this->logger = new Logging();
    }

    public function indexAction() {
        $this->view->disable();
       
         $token = $this->config->endpoints->token;

         $requestData = $this->request->getJsonRawBody();

         $username = $this->formatMobileNumber($requestData->username);

        $user = Users::findFirst(array("username=:username:",
                    'bind' => array("username" => $username)));

        if ($user) {
            if ($this->security->checkHash($requestData->password, $user->password)) {
                $userData = $this->rawSelect("SELECT m.memberName as fullName,r.roleID,u.userId from users u join member m on u.memberId=m.memberId JOIN roles r on m.memberRole=r.roleID WHERE u.username=$username");
                 $response = ['authenticated' => TRUE];

                 $this->session->set("user", $userData[0]);   

//                $role = $user->roleID;

                /*switch ($role) {
                        case 1://agent
                            $authorize = [
                                'ticket' => FALSE,
                                'sale' => FALSE,
                                'customer' => FALSE,
                                'transaction' => FALSE,
                                'product' => FALSE,
                                'sms' => FALSE,
                                'report' => FALSE,
                                'agent' => FALSE,
                                'user' => FALSE
                            ];
                            break;
                        case 2://admin
                            $authorize = [
                                'ticket' => TRUE,
                                'sale' => TRUE,
                                'customer' => TRUE,
                                'transaction' => TRUE,
                                'product' => TRUE,
                                'sms' => TRUE,
                                'report' => TRUE,
                                'agent' => TRUE,
                                'user' => TRUE
                            ];
                            break;
                        case 3://customer care agent
                            $authorize = [
                                'ticket' => TRUE,
                                'sale' => TRUE,
                                'customer' => TRUE,
                                'transaction' => TRUE,
                                'product' => TRUE,
                                'sms' => TRUE,
                                'report' => TRUE,
                                'agent' => TRUE,
                                'user' => FALSE
                            ];
                            break;
                        case 4://logistics manager
                            $authorize = [
                                'ticket' => FALSE,
                                'sale' => FALSE,
                                'customer' => FALSE,
                                'transaction' => FALSE,
                                'product' => TRUE,
                                'sms' => TRUE,
                                'report' => FALSE,
                                'agent' => TRUE,
                                'user' => FALSE
                            ];
                            break;
                        case 5://super admin
                            $authorize = [
                                'ticket' => TRUE,
                                'sale' => TRUE,
                                'customer' => TRUE,
                                'transaction' => TRUE,
                                'product' => TRUE,
                                'sms' => TRUE,
                                'report' => TRUE,
                                'agent' => TRUE,
                                'user' => TRUE
                            ];
                            break;
                        case 6://sales manager
                            $authorize = [
                                'ticket' => TRUE,
                                'sale' => TRUE,
                                'customer' => TRUE,
                                'transaction' => TRUE,
                                'product' => TRUE,
                                'sms' => TRUE,
                                'report' => TRUE,
                                'agent' => TRUE,
                                'user' => FALSE
                            ];
                            break;
                        case 7://sales team
                            $authorize = [
                                'ticket' => FALSE,
                                'sale' => TRUE,
                                'customer' => TRUE,
                                'transaction' => TRUE,
                                'product' => TRUE,
                                'sms' => TRUE,
                                'report' => TRUE,
                                'agent' => TRUE,
                                'user' => FALSE
                            ];
                            break;
                        case 8://customer care manager
                            $authorize = [
                                'ticket' => TRUE,
                                'sale' => TRUE,
                                'customer' => TRUE,
                                'transaction' => TRUE,
                                'product' => TRUE,
                                'sms' => TRUE,
                                'report' => TRUE,
                                'agent' => TRUE,
                                'user' => TRUE
                            ];
                            break;
                        case 9://carbon monitoring manager
                            $authorize = [
                                'ticket' => FALSE,
                                'sale' => FALSE,
                                'customer' => TRUE,
                                'transaction' => FALSE,
                                'product' => TRUE,
                                'sms' => TRUE,
                                'report' => FALSE,
                                'agent' => FALSE,
                                'user' => FALSE
                            ];
                            break;
                        case 10://finance manager
                            $authorize = [
                                'ticket' => TRUE,
                                'sale' => TRUE,
                                'customer' => FALSE,
                                'transaction' => TRUE,
                                'product' => TRUE,
                                'sms' => TRUE,
                                'report' => TRUE,
                                'agent' => FALSE,
                                'user' => FALSE
                            ];
                            break;
                        default:
                            break;
                        }
                        */
                        $authorize = [
                                'savings' => TRUE,
                                'loans' => TRUE,
                                'members' => TRUE,
                                'expenses'=>TRUE,
                                'banked'=>TRUE
                            ];

               $this->session->set('authorize', $authorize);

            }

        } else {
            $response = [
                'authenticated' => FALSE,
                'error' => "User not found"
            ];
        }
         $this->curlRequestInstance->sendHttpResponse($response);
      }
    //Logs a user out
    public function logoutAction() {
        $this->view->disable();

        if ($this->session->has("user")) {
            $this->session->destroy();
            return $this->response->redirect('');
        }
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

    protected function rawSelect($statement) {
        $connection = $this->di->getShared("db");
        $success = $connection->query($statement);
        $success->setFetchMode(Phalcon\Db::FETCH_ASSOC);
        $success = $success->fetchAll($success);
        return $success;
    }


}
