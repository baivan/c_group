<?php

use Phalcon\Mvc\Model\Query\Builder as Builder;
use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;

class ExpensesController extends ControllerBase {

    public function initialize() {
        $this->curlRequestInstance = CurlRequestResponse::getInstance();
        $this->logger = new Logging();
        $this->curlRequestInstance->setRights($this);
    }

    public function indexAction() {
        $this->view->setVar("page_title", "Expeses");
        $this->session->remove("customerID");
        $this->session->remove("prospectsID");
    }

    //Get all reconciled transactions
    public function expensesAction() {

        $this->view->disable();
        $response = NULL;

        $this->logger->logMessage('getTransactions', 'Server response: ' . json_encode($response), 0);
 
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

                $countQuery = "SELECT count(DISTINCT e.expenseId) as totalExpeses ";
                $totalQuery =  "SELECT sum(e.amount) as totalAmount ";
            $selectQuery = "SELECT e.expenseId,e.description,m.memberName,e.amount,e.memberId,e.createdAt,e.userId,um.memberName as officialName";
            $baseQuery = " FROM expenses e join member m on e.memberId=m.memberId join users u on e.userId=u.userId join member um on u.memberId=um.memberId ";


                $whereArray = [
                    'filter' => $filter,
                    'e.expenseId' => $expenseId,
                    'e.memberId' => $memberId,
                    'date' => [$start, $end]
                ];

                $whereQuery = "";

                foreach ($whereArray as $key => $value) {

                    if ($key == 'filter') {
                        $searchColumns = ['m.memberName', 'e.amount','m.memberPhoneNumber'];

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
                            $valueString = " DATE(e.createdAt) BETWEEN '$value[0]' AND '$value[1]'";
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
                $totalExpenesesAmount = $this->rawSelect($totalQuery);

                if($isExport){
                     $exportTransactions = $this->rawSelect($exportQuery);
                   
                }
                else{
                   
                     $exportTransactions= [];
                }



            

            if ($items) {
               
                $totalItems = $count[0]['totalExpeses'];
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
                    'totalExpenesesAmount'=>$totalExpenesesAmount[0]['totalAmount'],
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
                    'totalExpenesesAmount'=>$totalExpenesesAmount[0]['totalAmount'],
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

            $this->logger->logMessage('getTransactions', 'Server response: ' . json_encode($e), 0); 

            $response = [
                'total' => 0,
                'totalExpenesesAmount'=>0,
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

            $amount =$requestData->amount;
            $memberId = $requestData->memberId;
            $description = $requestData->description;



            if(!$amount || !$description){
               $response = [
                        'status' => Error,
                        'success' => "Expense required data not found"
                    ]; 
                 $this->curlRequestInstance->sendHttpResponse($response);

            }

            
              $expense = new Expenses();
              $expense->amount = $amount;
              $expense->memberId = $memberId;
              $expense->userId=$user['userId'];
              $expense->description = $description;
              $expense->createdAt = date("Y-m-d H:i:s");

             

             if ($expense->save() === false) {
                    $errors = array();
                    $messages = $expense->getMessages();
                    foreach ($messages as $message) {
                        $e["message"] = $message->getMessage();
                        $e["field"] = $message->getField();
                        $errors[] = $e;
                    }
                    $dbTransaction->rollback("Expeses create failed " . json_encode($errors));

                    $response = [
                        'status' => FALSE,
                        'error' => "Expeses created failed $errors"
                    ];
                }
                else{
                    $dbTransaction->commit();
                    $response = [
                        'status' => TRUE,
                        'success' => "Expeses created successfully"
                    ];
                }

            $this->curlRequestInstance->sendHttpResponse($response);
        } catch (Exception $e) {
             $message = $e->getMessage();
            $response = [
                        'status' => FALSE,
                        'error' => "Expeses create failed $message"
                    ];
            $this->curlRequestInstance->sendHttpResponse($response);
        }
        catch (Phalcon\Mvc\Model\Transaction\Failed $e) {
            $message = $e->getMessage();
            $response = [
                        'status' => FALSE,
                        'error' => "Expeses create failed $message"
                    ];
            $this->curlRequestInstance->sendHttpResponse($response);
        }
    }

   
        



}
