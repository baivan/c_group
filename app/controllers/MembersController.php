<?php

use Phalcon\Mvc\Model\Query\Builder as Builder;
use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;

class MembersController extends ControllerBase {

    public function initialize() {
        $this->curlRequestInstance = CurlRequestResponse::getInstance();
        $this->logger = new Logging();
        $this->curlRequestInstance->setRights($this);
    }

    public function indexAction() {
        $this->view->setVar("page_title", "Members");
    }

    //Create a new prospect
    public function createAction() { 

        $transactionManager = new TransactionManager();
        $dbTransaction = $transactionManager->get();

        $this->view->disable();
        $response = NULL;
 
        try {
            $token = $this->config->endpoints->token;
            $requestData = $this->request->getJsonRawBody();
          //  $createUrl = $this->config->endpoints->core . '/prospect/create?token=' . $token;
            $user = $this->session->get('user');

            $mobile = $this->formatMobileNumber($requestData->phoneNumber);

            $member = Member::findFirst("memberPhoneNumber = $mobile");

            $prev_member = $this->rawSelect("select memberId from member order by memberId desc limit 1");
            $membershipNumber = 'COV';

            if(($prev_member[0]['memberId']+1)<=9){
                $membershipNumber .='00'.($prev_member[0]['memberId']+1);
            }
            elseif(($prev_member[0]['memberId']+1)>9 && ($prev_member[0]['memberId']+1)<=99){
                 $membershipNumber .='0'.($prev_member[0]['memberId']+1);
            }
            elseif(($prev_member[0]['memberId']+1)>99 && ($prev_member[0]['memberId']+1)<=999){
                 $membershipNumber .=($prev_member[0]['memberId']+1);
            }

            if($member){
                $response = [
                    'status' => FALSE,
                    'error' => "Member  Exists"
                ];
            }
            else{
                $member = new Member();
                $member->memberName = $requestData->fullNames;
                $member->memberIdNumber = $requestData->idNumber;
                $member->memberPhoneNumber = $mobile;
                $member->memberRole = $requestData->roleID;
                $member->membershipNumber =  $membershipNumber;//$requestData->fullNames;
                $member->createdAt  = date("Y-m-d H:i:s");

                if ($member->save() === false) {
                    $errors = array();
                    $messages = $member->getMessages();
                    foreach ($messages as $message) {
                        $e["message"] = $message->getMessage();
                        $e["field"] = $message->getField();
                        $errors[] = $e;
                    }
                    $dbTransaction->rollback("member create failed " . json_encode($errors));
                }

                $code = rand(9999, 99999);

                $user = new Users();
                $user->memberId = $member->memberId;
                $user->username = $mobile;
                $user->password = $this->security->hash($code);
                $user->code = $code;
                $user->createdAt  = date("Y-m-d H:i:s");

                if ($user->save() === false) {
                    $errors = array();
                    $messages = $user->getMessages();
                    foreach ($messages as $message) {
                        $e["message"] = $message->getMessage();
                        $e["field"] = $message->getField();
                        $errors[] = $e;
                    }
                    $dbTransaction->rollback("user create failed " . json_encode($errors));
                }

                $dbTransaction->commit();

                $member_message = "Dear ".$member->memberName.", your login password is $code";
                $this->sendMessage($mobile,$member_message);

                $response = [
                    'status' => TRUE,
                    'success' => "User created successfully"
                ];
            }

            $this->curlRequestInstance->sendHttpResponse($response);
        } catch (Exception $e) {
            $response = [
                'status' => FALSE,
                'error' => 'unable to create prospect'
            ];

            $this->curlRequestInstance->sendHttpResponse($response);
        }
        catch (Phalcon\Mvc\Model\Transaction\Failed $e) {
            $response = [
                'status' => FALSE,
                'error' => 'unable to create prospect'
            ];
            $this->curlRequestInstance->sendHttpResponse($response);
        }
    }

    //Get all agents for the table
    public function membersAction() {
        $this->view->disable();
        $response = NULL;

        try {
            $token = $this->config->endpoints->token;
            $sortCriteria = $this->request->get('sort') ? $this->request->get('sort') : '';
            $filter = $this->request->get('filter') ? $this->request->get('filter') : '';
            $page = (int) $this->request->get('page') ? $this->request->get('page') : 1;
            $limit = (int) $this->request->get('per_page') ? $this->request->get('per_page') : 10;
           
            if ($sortCriteria) {
                list($sort, $order) = explode('|', $sortCriteria);
            } else {
                $sort = '';
                $order = '';
            }

            $lastPage = 1;
            $from = 1;
            $to = 1;
            $totalItems = 0;

            $countQuery = "SELECT count(u.userID) as totalUsers FROM users u join member m on u.memberId=m.memberId JOIN roles r on m.memberRole=r.roleID ";

            $selectQuery = "SELECT u.userID,m.memberId, m.memberName,m.memberIdNumber,m.memberPhoneNumber,r.roleID,r.roleName, u.createdAt,sum(s.savingsAmount) as totalSavings, sum(l.amountToPay) as amountToPay, sum(l.repaidAmount) as repaidAmount ";

           $baseQuery = " FROM users u join member m on u.memberId=m.memberId JOIN roles r on m.memberRole=r.roleID LEFT JOIN savings s on m.memberId=s.memberId LEFT JOIN loans l on m.memberId=l.memberId ";

        $whereArray = [
            'filter' => $filter,
            'date' => [$startDate, $endDate]
        ];

        $whereQuery = "";

        foreach ($whereArray as $key => $value) {

            if ($key == 'filter') {
                $searchColumns = ['m.memberName', 'm.memberPhoneNumber', 'r.roleName', 'm.memberIdNumber'];

                $valueString = "";
                foreach ($searchColumns as $searchColumn) {
                    $valueString .= $value ? "" . $searchColumn . " REGEXP '" . $value . "' ||" : "";
                }
                $valueString = chop($valueString, " ||");
                if ($valueString) {
                    $valueString = "(" . $valueString;
                    $valueString .= ") AND ";
                }
                $whereQuery .= $valueString;
            } else if ($key == 'u.status' && $value == 404) {
                $valueString = "" . $key . "=0" . " AND ";
                $whereQuery .= $valueString;
            } else if ($key == 'date') {
                if (!empty($value[0]) && !empty($value[1])) {
                    $valueString = " DATE(u.createdAt) BETWEEN '$value[0]' AND '$value[1]' ";
                    $whereQuery .= $valueString;
                }
            } else {
                $valueString = $value ? "" . $key . "=" . $value . " AND " : "";
                $whereQuery .= $valueString;
            }
        }

        if ($whereQuery) {
            $whereQuery = chop($whereQuery, " AND ");
        }

        $whereQuery = $whereQuery ? "WHERE $whereQuery " : "";

        $countQuery = $countQuery . $whereQuery;
        $selectQuery = $selectQuery . $baseQuery . $whereQuery."group by m.memberId ";
        $exportQuery = $selectQuery;

        $queryBuilder = $this->tableQueryBuilder($sort, $order, $page, $limit);
        $selectQuery .= $queryBuilder;

        $this->logger->logMessage('getAgents', 'Server response: ' . json_encode($countQuery), 0);

        $count = $this->rawSelect($countQuery);

        $users = $this->rawSelect($selectQuery);
        $exportUsers = $this->rawSelect($exportQuery);

            if ($users) {
                
                $totalItems = $count[0]['totalUsers'];//$dataObject->totalUsers;
                $data = $users;//$dataObject->users;
                $exportAgents = $count[0]['totalUsers'];//$dataObject->exportUsers;

                $from = ($page - 1) * $limit + 1;

                $rem = (int) ($totalItems % $limit);
                if ($rem !== 0) {
                    $lastPage = (int) ($totalItems / $limit) + 1;
                } else {
                    $lastPage = (int) ($totalItems / $limit);
                }

                if ($page == $lastPage) {
                    $to = $totalItems;
                } else {
                    $to = ($from + $limit) - 1;
                }

                $response = [
                    'type' => 'members',
                    'total' => $totalItems,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => $lastPage,
                    'from' => $from,
                    'to' => $to,
                    'data' => $data,
                    'exportAgents' => $exportAgents
                ];
            } else {
                $response = [
                    'type' => 'members',
                    'total' => $totalItems,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => $lastPage,
                    'from' => $from,
                    'to' => $to,
                    'data' => [],
                    'data' => []
                ];
            }

            $this->curlRequestInstance->sendHttpResponse($response);
        } catch (Exception $e) {
            $response = [
                'type' => 'members',
                'total' => 0,
                'per_page' => 10,
                'current_page' => 1,
                'last_page' => 1,
                'from' => 1,
                'to' => 1,
                'data' => [],
                'exportAgents' => []
            ];
            $this->curlRequestInstance->sendHttpResponse($response);
        }
    }

    //Get agent sales
    public function savingsAction($memberId = 0) {
        $this->view->disable();
        $response = NULL;

        try {

            $sortCriteria = $this->request->get('sort') ? $this->request->get('sort') : '';
            $page = (int) $this->request->get('page') ? $this->request->get('page') : 1;
            $limit = (int) $this->request->get('per_page') ? $this->request->get('per_page') : 10;
            $token = $this->config->endpoints->token;
            $memberId = $memberId;

            if ($sortCriteria) {
                list($sort, $order) = explode('|', $sortCriteria);
            } else {
                $sort = '';
                $order = '';
            }

            $lastPage = 1;
            $from = 1;
            $to = 1;
            $totalItems = 0;
            $countQuery = "SELECT count(DISTINCT s.savingsId) as totalSavings ";
            $selectQuery = "SELECT s.savingsId,m.memberName,s.savingsAmount,s.memberId,s.createdAt ";

             
            $defaultQuery = " FROM savings s join member m on s.memberId=m.memberId ";

            $whereQuery=" ";

             if($memberId){
                $whereQuery=" WHERE s.memberId=$memberId ";
            }



                $queryBuilder = $this->tableQueryBuilder($sort, $order, $page, $limit);
                $selectQuery .= $defaultQuery.$whereQuery.$queryBuilder;
                $countQuery .=$defaultQuery.$whereQuery;
                
                $this->logger->logMessage('customerRedirect', 'Customer: ' . json_encode($selectQuery), 0);


                $count = $this->rawSelect($countQuery);
                $savings = $this->rawSelect($selectQuery);


            if ($savings) {
               
                $totalItems = $count[0]['totalSavings'];//$dataObject->totalSales;
                $data = $savings;

                $from = ($page - 1) * $limit + 1;

                $rem = (int) ($totalItems % $limit);
                if ($rem !== 0) {
                    $lastPage = (int) ($totalItems / $limit) + 1;
                } else {
                    $lastPage = (int) ($totalItems / $limit);
                }

                if ($page == $lastPage) {
                    $to = $totalItems;
                } else {
                    $to = ($from + $limit) - 1;
                }

                $response = [
                    'total' => $totalItems,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => $lastPage,
                    'from' => $from,
                    'to' => $to,
                    'data' => $data
                ];
            } else {
                $response = [
                    'total' => $totalItems,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => $lastPage,
                    'from' => $from,
                    'to' => $to,
                    'data' => []
                ];
            }

            $this->curlRequestInstance->sendHttpResponse($response);
        } catch (Exception $e) {
            $response = [
                'type' => 'sales',
                'total' => 0,
                'per_page' => 10,
                'current_page' => 1,
                'last_page' => 1,
                'from' => 1,
                'to' => 1,
                'data' => []
            ];
            $this->curlRequestInstance->sendHttpResponse($response);
        }
    }

    //Get agent assigned products
    public function loansAction($memberId = 0) {
        $this->view->disable();
        $response = NULL;

        try {

            $sortCriteria = $this->request->get('sort') ? $this->request->get('sort') : '';
            $page = (int) $this->request->get('page') ? $this->request->get('page') : 1;
            $limit = (int) $this->request->get('per_page') ? $this->request->get('per_page') : 10;
            $memberId=$memberId;

            $token = $this->config->endpoints->token;
          //  $itemsUrl = $this->config->endpoints->core . '/userItem/crm/all?userID=' . $agentID . '&token=' . $token;

            if ($sortCriteria) {
                list($sort, $order) = explode('|', $sortCriteria);
            } else {
                $sort = '';
                $order = '';
            }

            $lastPage = 1;
            $from = 1;
            $to = 1;
            $totalItems = 0;
            
           $countQuery = "SELECT count(DISTINCT l.loanId) as totalLoans ";
           $totalLoanAmountQuery = "SELECT sum(l.amountToPay) as amountToPay, sum(l.repaidAmount) as repaidAmount ";
            
            $baseQuery = " FROM loans l join member m on l.memberId=m.memberId WHERE l.status>=0 ";
            $selectQuery = "SELECT l.loanId,l.memberId,m.memberName,l.loanAmount,l.loanOfferDate,l.loanRepayDate,l.interestRate,m.memberName,l.createdAt,l.status,l.repaidAmount,l.amountToPay ";
           $whereArray = " " ;
           if($memberId){
                $whereQuery=" AND l.memberId=$memberId ";
            }

            $countQuery = $countQuery . $baseQuery . $whereQuery;
            $totalLoanAmountQuery .= $baseQuery . $whereQuery;
            $selectQuery = $selectQuery . $baseQuery . $whereQuery;

           

            $queryBuilder = $this->tableQueryBuilder($sort, $order, $page, $limit);

           $selectQuery .= $queryBuilder;


        $count = $this->rawSelect($countQuery);
        $loans = $this->rawSelect($selectQuery);
        $totals = $this->rawSelect($totalLoanAmountQuery);
            
         $this->logger->logMessage('customerRedirect', 'Customer: ' . json_encode($selectQuery), 0);


           if ($loans) {
                $totalItems = $count[0]['totalLoans'];
                $data = $loans;


                $exportCustomers = $exportCustomers;

                $from = ($page - 1) * $limit + 1;

                $rem = (int) ($totalItems % $limit);
                if ($rem !== 0) {
                    $lastPage = (int) ($totalItems / $limit) + 1;
                } else {
                    $lastPage = (int) ($totalItems / $limit);
                }

                if ($page == $lastPage) {
                    $to = $totalItems;
                } else {
                    $to = ($from + $limit) - 1;
                }

                $response = [
                     'user' => $this->session->get('user'),
                     'type'=>'loans',
                    'total' => $totalItems,
                    'repaymentSummary'=>$totals[0],
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => $lastPage,
                    'from' => $from,
                    'to' => $to,
                    'data' => $data,
                    'exportCustomers' => $exportCustomers
                ];
            } else {
                $response = [
                     'user' => $this->session->get('user'),
                     'type'=>'loans',
                    'total' => $totalItems,
                    'repaymentSummary'=>$totals[0],
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => $lastPage,
                    'from' => $from,
                    'to' => $to,
                    'data' => [],
                    'exportCustomers'=>[]
                ];
            }

            $this->curlRequestInstance->sendHttpResponse($response);
        } catch (Exception $e) {
            $response = [
                 'user' => $this->session->get('user'),
                 'type'=>'loans',
                 'repaymentSummary'=>0,
                'total' => 0,
                'per_page' => 10,
                'current_page' => 1,
                'last_page' => 1,
                'from' => 1,
                'to' => 1,
                'data' => [],
                'exportCustomers' => []
            ];
            $this->curlRequestInstance->sendHttpResponse($response);
        }
    }

    public function rolesAction(){

        $this->view->disable();
        $response = NULL;

        try {
            $roles = $this->rawSelect("SELECT * FROM roles ");

            if ($roles) {
                
                $response = $roles;
            } else {
                $response = [];
            }

            $this->curlRequestInstance->sendHttpResponse($response);
        } catch (Exception $e) {
            $response = [];
            $this->curlRequestInstance->sendHttpResponse($response);
        }

    }

    public function sendmessageAction(){
        $this->view->disable();
        $response = NULL;

        try {

            $message = $this->request->get('message') ? $this->request->get('message') : '';

            $members = $this->rawSelect("SELECT memberId,memberPhoneNumber,memberName FROM members");
            $user = $this->session->get('user');

            //send this message to each member
            foreach ($members as $member) {
                $message = 'Habari, '.$member['memberName'].' '.$message;
                $this->sendMessage($member['memberPhoneNumber'],$message);

                //save this message to outbox
                $outbox = new Outbox();
                $outbox->memberID = $member['memberId'];
                $outbox->userID = $user['userId'];
                $outbox->message = $message;
                $outbox->createdAt  = date("Y-m-d H:i:s");

                if ($outbox->save() === false) {
                    $errors = array();
                    $messages = $outbox->getMessages();
                    foreach ($messages as $message) {
                        $e["message"] = $message->getMessage();
                        $e["field"] = $message->getField();
                        $errors[] = $e;
                    }
                    $this->logger->logMessage('sendmessage', 'Server response: ' . json_encode($messages), 0);
                }

            }
            $response = [
                    'status' => TRUE,
                    'success' => "Message sent successfully"
                ];

            $this->curlRequestInstance->sendHttpResponse($response);
        } catch (Exception $e) {
            $response = [
                    'status' => false,
                    'success' => "Message send error"
                ];
            $this->curlRequestInstance->sendHttpResponse($response);
        }
    }

    public function membernamesAction(){
            $this->view->disable();
        $response = NULL;

        try {
            $members = $this->rawSelect("SELECT memberName,memberId FROM member ");

            if ($members) {
                $response = $members;
            } else {
                $response = [];
            }

            $this->curlRequestInstance->sendHttpResponse($response);
        } catch (Exception $e) {
            $response = [];
            $this->curlRequestInstance->sendHttpResponse($response);
        }
    }

     public function getSaleItems($salesID) {
        $selectQuery = "select i.serialNumber, p.productName, c.categoryName from sales_item si join item i on si.itemID=i.itemID join product p on i.productID=p.productID join category c on p.categoryID=c.categoryID where saleID = $salesID";
        $items = $this->rawSelect($selectQuery);
        return $items;
    }

}
