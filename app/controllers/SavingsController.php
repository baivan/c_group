<?php

use Phalcon\Mvc\Model\Query\Builder as Builder;
use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;

class SavingsController extends ControllerBase {

    public function initialize() {
        $this->curlRequestInstance = CurlRequestResponse::getInstance();
        $this->logger = new Logging();
        $this->curlRequestInstance->setRights($this);
    }

    public function indexAction() {
        $this->view->setVar("page_title", "Savings");
        $this->session->remove("customerID");
        $this->session->remove("prospectsID");
    }

    //Get all reconciled transactions
    public function savingsAction() {

        $this->view->disable();
        $response = NULL;
 
        try {
            $token = $this->config->endpoints->token;
            $sortCriteria = $this->request->get('sort') ? $this->request->get('sort') : '';
            $filter = $this->request->get('filter') ? $this->request->get('filter') : '';
            $start = $this->request->get('start') ? $this->request->get('start') : '';
            $end = $this->request->get('end') ? $this->request->get('end') : '';
            $page = (int) $this->request->get('page') ? $this->request->get('page') : 1;
            $limit = (int) $this->request->get('per_page') ? $this->request->get('per_page') : 10;
             $isExport = $this->request->get('isExport') ? $this->request->get('isExport') : FALSE;

            //$transactionsUrl = $this->config->endpoints->core . '/transaction/crm/all?token=' . $token;

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
/*
            $transactionsUrl .= '&sort=' . $sortField . '&order=' . $orderBy . '&page=' . $currentPage . '&limit=' . $perPage . '&filter=' . $filter . '&start=' . $start . '&end=' . $end.'&isExport='.$isExport;

            $result = $this->curlRequestInstance->makeHttpGetRequest($transactionsUrl);
            $this->logger->logMessage('getTransactions', 'Server response: ' . json_encode($result), 0);
            */

                $countQuery = "SELECT count(DISTINCT s.savingsId) as totalSavings ";
                $totalQuery =  "SELECT sum(s.savingsAmount) as totalAmount ";
            $selectQuery = "SELECT s.savingsId,m.memberName,s.savingsAmount,s.memberId,s.createdAt ";
            $baseQuery = " FROM savings s join member m on s.memberId=m.memberId ";


                $whereArray = [
                    'filter' => $filter,
                    's.savingsId' => $savingsId,
                    's.memberId' => $memberId,
                    'date' => [$start, $end]
                ];

                $whereQuery = "";

                foreach ($whereArray as $key => $value) {

                    if ($key == 'filter') {
                        $searchColumns = ['m.memberName', 's.savingsAmount','m.memberPhoneNumber'];

                        $valueString = "";
                        foreach ($searchColumns as $searchColumn) {
                            $valueString .= $value ? "" . $searchColumn . " REGEXP '" . $value . "' ||" : "";
                        }
                        $valueString = chop($valueString, " ||");
                        if ($valueString) {
                            $valueString = "(" . $valueString;
                            $valueString .= ") AND";
                        }
                        $whereQuery .= $valueString;
                    }  else if ($key == 'date') {
                        if (!empty($value[0]) && !empty($value[1])) {
                            $valueString = " DATE(s.createdAt) BETWEEN '$value[0]' AND '$value[1]'";
                            $whereQuery .= $valueString;
                        }
                    } else {
                        $valueString = $value ? " " . $key . "=" . $value . " AND " : "";
                        $whereQuery .= $valueString;
                    }
                }
 
                if ($whereQuery) {
                    $whereQuery = chop($whereQuery, " AND ");
                }

                $whereQuery = $whereQuery ? "WHERE $whereQuery " : "";

                $countQuery = $countQuery . $baseQuery . $whereQuery;
                $totalQuery .=$baseQuery . $whereQuery;
                $selectQuery = $selectQuery . $baseQuery . $whereQuery;
                $exportQuery = $selectQuery;

                $queryBuilder = $this->tableQueryBuilder($sort, $order, $page, $limit);
                $selectQuery .= $queryBuilder;


                $count = $this->rawSelect($countQuery);
                $items = $this->rawSelect($selectQuery);
                $totalSavingsAmount = $this->rawSelect($totalQuery);

                if($isExport){
                     $exportTransactions = $this->rawSelect($exportQuery);
                   
                }
                else{
                   
                     $exportTransactions= [];
                }

            

            if ($items) {
               
                $totalItems = $count[0]['totalSavings'];
                $data = $items;
                $exportTransactions = $dataObject->exportTransactions;

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
                    'totalSavingsAmount'=>$totalSavingsAmount[0]['totalAmount'],
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => $lastPage,
                    'from' => $from,
                    'to' => $to,
                    'data' => $data,
                    'exportTransactions'=>$exportTransactions
                ];
            } else {
                $response = [
                    'total' => $totalItems,
                    'totalSavingsAmount'=>$totalSavingsAmount[0]['totalAmount'],
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => $lastPage,
                    'from' => $from,
                    'to' => $to,
                    'data' => [],
                    'exportTransactions'=>[]
                ];
            }

            $this->curlRequestInstance->sendHttpResponse($response);
        } catch (Exception $e) {
            $response = [
                'total' => 0,
                'totalSavingsAmount'=>0,
                'per_page' => 10,
                'current_page' => 1,
                'last_page' => 1,
                'from' => 1,
                'to' => 1,
                'data' => [],
                'exportTransactions'=>[]
            ];
            $this->curlRequestInstance->sendHttpResponse($response);
        }
    }

    public function createAction() {
        $this->view->disable();
        $response = NULL;

        $transactionManager = new TransactionManager();
        $dbTransaction = $transactionManager->get();

        try {
            $token = $this->config->endpoints->token;
            $requestData = $this->request->getJsonRawBody();
            $user = $this->session->get('user');

            $amount =$requestData->savingAmount;
            $memberId = $requestData->memberId;



            if(!$amount){
               $response = [
                        'status' => Error,
                        'success' => "Savings Amount not found"
                    ]; 
                 $this->curlRequestInstance->sendHttpResponse($response);

            }

             /*$savings = Savings::findFirst(array("savingsId=:id: ",
                    'bind' => array("id" => $savingsId)));
                    */
              $savings = new Savings();
              $savings->savingsAmount = $amount;
              $savings->memberId = $memberId;
              $savings->userId=$user['userId'];
              $savings->createdAt = date("Y-m-d H:i:s");

             /*if(!$savings){
                $response = [
                        'status' => Error,
                         'success' => "Savings not found"
                    ]; 
                 $this->curlRequestInstance->sendHttpResponse($response);
             }*/

            

            //$savings->userId=$user['userId'];

             if ($savings->save() === false) {
                    $errors = array();
                    $messages = $savings->getMessages();
                    foreach ($messages as $message) {
                        $e["message"] = $message->getMessage();
                        $e["field"] = $message->getField();
                        $errors[] = $e;
                    }
                    $dbTransaction->rollback("savings create failed " . json_encode($errors));

                    $response = [
                        'status' => FALSE,
                        'error' => "Savings create failed $errors"
                    ];
                }
                else{
                    $dbTransaction->commit();
                    $response = [
                        'status' => TRUE,
                        'success' => "Savings create successfully"
                    ];
                }

            $this->curlRequestInstance->sendHttpResponse($response);
        } catch (Exception $e) {
             $message = $e->getMessage();
            $response = [
                        'status' => FALSE,
                        'error' => "Savings create failed $message"
                    ];
            $this->curlRequestInstance->sendHttpResponse($response);
        }
        catch (Phalcon\Mvc\Model\Transaction\Failed $e) {
            $message = $e->getMessage();
            $response = [
                        'status' => FALSE,
                        'error' => "Savings edit failed $message"
                    ];
            $this->curlRequestInstance->sendHttpResponse($response);
        }
    }

   
     public function editAction() {
        $this->view->disable();
        $response = NULL;

        $transactionManager = new TransactionManager();
        $dbTransaction = $transactionManager->get();

        try {
            $token = $this->config->endpoints->token;
            $requestData = $this->request->getJsonRawBody();
            $user = $this->session->get('user');

            $amount =$requestData->amount;
            $savingsId = $requestData->savingsId;

            if(!$savingsId){
               $response = [
                        'status' => Error,
                        'success' => "Savings not found"
                    ]; 
                 $this->curlRequestInstance->sendHttpResponse($response);

            }

             $savings = Savings::findFirst(array("savingsId=:id: ",
                    'bind' => array("id" => $savingsId)));

             if(!$savings){
                $response = [
                        'status' => Error,
                         'success' => "Savings not found"
                    ]; 
                 $this->curlRequestInstance->sendHttpResponse($response);
             }

           
            if($amount){
                $savings->amount = $requestData->amount;
            }

            

            $savings->userId=$user['userId'];

             if ($loan->save() === false) {
                    $errors = array();
                    $messages = $loan->getMessages();
                    foreach ($messages as $message) {
                        $e["message"] = $message->getMessage();
                        $e["field"] = $message->getField();
                        $errors[] = $e;
                    }
                    $dbTransaction->rollback("savings create failed " . json_encode($errors));

                    $response = [
                        'status' => FALSE,
                        'error' => "Savings edit failed $errors"
                    ];
                }
                else{
                    $dbTransaction->commit();
                    $response = [
                        'status' => TRUE,
                        'success' => "Savings edit successfully"
                    ];
                }

            $this->curlRequestInstance->sendHttpResponse($response);
        } catch (Exception $e) {
             $message = $e->getMessage();
            $response = [
                        'status' => FALSE,
                        'error' => "Savings edit failed $message"
                    ];
            $this->curlRequestInstance->sendHttpResponse($response);
        }
        catch (Phalcon\Mvc\Model\Transaction\Failed $e) {
            $message = $e->getMessage();
            $response = [
                        'status' => FALSE,
                        'error' => "Savings edit failed $message"
                    ];
            $this->curlRequestInstance->sendHttpResponse($response);
        }
    }


    public function transferAction() {
        $this->view->disable();
        $response = NULL;

        $transactionManager = new TransactionManager();
        $dbTransaction = $transactionManager->get();

        try {
            $token = $this->config->endpoints->token;
            $requestData = $this->request->getJsonRawBody();
            $user = $this->session->get('user');

            $amount =$requestData->amount;
            $originMemberId = $requestData->originMemberId; 
            $destMemberId = $requestData->destMemberId;

             $totalSavings = $this->rawSelect("SELECT sum(savingsAmount) as totalSavings from savings where memberId=$originMemberId");
             $totalSavings  = $totalSavings[0]['totalSavings'];
             $totalLoans = $this->rawSelect("SELECT sum(amountToPay)-sum(repaidAmount) as totalLoan from loans where memberId=$originMemberId and status=1");
             $totalLoans =$totalLoan[0]['totalLoan'];

             if($totalSavings <= $totalLoans ){
                $response = [
                        'status' => Error,
                        'success' => "Savings not found"
                    ]; 
                 $this->curlRequestInstance->sendHttpResponse($response);

             }
             elseif ($totalSavings < $amount) {
                $response = [
                        'status' => Error,
                        'success' => "Savings not found"
                    ]; 
                 $this->curlRequestInstance->sendHttpResponse($response);
                 
             }


             $savings = new Savings();
             $savings->savingsAmount = ($amount*-1);
             $savings->userId = $user['userId'];
             $savings->memberId = $originMemberId;
             $savings->createdAt = date("Y-m-d H:i:s");

             $t_savings = new Savings();
             $t_savings->savingsAmount = $amount;
             $t_savings->userId = $user['userId'];
             $t_savings->memberId = $destMemberId;
             $t_savings->createdAt = date("Y-m-d H:i:s");


             if ($savings->save() === false) {
                    $errors = array();
                    $messages = $savings->getMessages();
                    foreach ($messages as $message) {
                        $e["message"] = $message->getMessage();
                        $e["field"] = $message->getField();
                        $errors[] = $e;
                    }
                    $dbTransaction->rollback("savings transfer failed " . json_encode($errors));

                    $response = [
                        'status' => FALSE,
                        'error' => "Savings transfer failed $errors"
                    ];
                }
                elseif ($t_savings->save() === false) {
                        $errors = array();
                        $messages = $t_savings->getMessages();
                        foreach ($messages as $message) {
                            $e["message"] = $message->getMessage();
                            $e["field"] = $message->getField();
                            $errors[] = $e;
                        }
                        $dbTransaction->rollback("savings transfer failed " . json_encode($errors));

                        $response = [
                            'status' => FALSE,
                            'error' => "Savings transfer failed $errors"
                        ];
                }
                else{
                    $dbTransaction->commit();
                    $response = [
                        'status' => TRUE,
                        'success' => "Savings transfer successfully"
                    ];
                }

            $this->curlRequestInstance->sendHttpResponse($response);
        } catch (Exception $e) {
             $message = $e->getMessage();
            $response = [
                        'status' => FALSE,
                        'error' => "Savings transfer failed $message"
                    ];
            $this->curlRequestInstance->sendHttpResponse($response);
        }
        catch (Phalcon\Mvc\Model\Transaction\Failed $e) {
            $message = $e->getMessage();
            $response = [
                        'status' => FALSE,
                        'error' => "Savings transfer failed $message"
                    ];
            $this->curlRequestInstance->sendHttpResponse($response);
        }
    }




   



}
