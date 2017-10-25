<?php
use Phalcon\Mvc\Model\Query\Builder as Builder;
use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;

class LoansController extends ControllerBase {

    public function initialize() {
        $this->curlRequestInstance = CurlRequestResponse::getInstance();
        $this->logger = new Logging();
        $this->curlRequestInstance->setRights($this);
    }

    public function indexAction() {
        $this->view->setVar("page_title", "Member Loans");
        $this->view->setVar("is_customer", FALSE);
        $this->view->setVar("is_prospect", FALSE);
        $this->session->remove("detail");
        $this->session->remove("customerID");
        $this->session->remove("prospectsID");
    }

    //Get all customers
    public function loansAction() {
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
            $status=0;

           // $customersUrl = $this->config->endpoints->core . '/customer/crm/all?token=' . $token;



            if ($sortCriteria) {
                list($sort, $order) = explode('|', $sortCriteria);
            } else {
                $sort = '';
                $orderBy = '';
            }

            $lastPage = 1;
            $from = 1;
            $to = 1;
            $totalItems = 0;

            
           $countQuery = "SELECT count(DISTINCT l.loanId) as totalLoans ";
           $totalLoanAmountQuery = "SELECT sum(l.amountToPay) as amountToPay, sum(l.repaidAmount) as repaidAmount ";
            
            $baseQuery = " FROM loans l join member m on l.memberId=m.memberId ";
            $selectQuery = "SELECT l.loanId,l.memberId,m.memberName,l.loanAmount,l.loanOfferDate,l.loanRepayDate,l.interestRate,m.memberName,l.createdAt,l.status,l.repaidAmount,l.amountToPay  ";




        $whereArray = [
            'filter' => $filter,
            'date' => [$start, $end]
        ];

        $whereQuery = "";

        foreach ($whereArray as $key => $value) {

            if ($key == 'filter') {
                $searchColumns = ['m.memberName', 'l.loanAmount','l.amountToPay'];

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
            } else if ($key == 'l.status' && $value == 0) {
                $valueString = "" . $key . ">=0" . " AND ";
                $whereQuery .= $valueString;
            } else if ($key == 'date') {
                if (!empty($value[0]) && !empty($value[1])) {
                    $valueString = " DATE(l.createdAt) BETWEEN '$value[0]' AND '$value[1]'";
                    $whereQuery .= $valueString;
                }
            } else {
                $valueString = $value ? "" . $key . "=" . $value . " AND" : "";
                $whereQuery .= $valueString;
            }
        }

        if ($whereQuery) {
            $whereQuery = chop($whereQuery, " AND ");
        }

        $whereQuery = $whereQuery ? "WHERE l.status>=0 AND $whereQuery " : " WHERE l.status>=0 ";

        $countQuery = $countQuery . $baseQuery . $whereQuery;
        $totalLoanAmountQuery .= $baseQuery . $whereQuery;
        $selectQuery = $selectQuery . $baseQuery . $whereQuery;
        $exportQuery = $selectQuery;

        // $this->logger->logMessage('customerRedirect', 'Customer: ' . json_encode($selectQuery), 0);

        $queryBuilder = $this->tableQueryBuilder($sort, $order, $page, $limit);

        $selectQuery .= $queryBuilder;

        // $this->logger->logMessage('customerRedirect', 'Customer: ' . json_encode($selectQuery), 0);

        $count = $this->rawSelect($countQuery);
        $customers = $this->rawSelect($selectQuery);
        $totals = $this->rawSelect($totalLoanAmountQuery);

            if ($customers) {
                $totalItems = $count[0]['totalLoans'];
                $data = $customers;


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

    

     public function deleteAction() {
        $this->view->disable();
        $response = NULL;

        $transactionManager = new TransactionManager();
        $dbTransaction = $transactionManager->get();

        try {
            $token = $this->config->endpoints->token;
            $requestData = $this->request->getJsonRawBody();
            $user = $this->session->get('user');

            $loanId = $requestData->loanId;

            if(!$loanId){
               $response = [
                        'status' => Error,
                        'success' => "Loan not found"
                    ]; 
                 $this->curlRequestInstance->sendHttpResponse($response);

            }

             $loan = Loans::findFirst(array("loanId=:id: ",
                    'bind' => array("id" => $loanId)));

            $loan->status=-2;
            $loan->userId=$user['userId'];

             if ($loan->save() === false) {
                    $errors = array();
                    $messages = $loan->getMessages();
                    foreach ($messages as $message) {
                        $e["message"] = $message->getMessage();
                        $e["field"] = $message->getField();
                        $errors[] = $e;
                    }
                    $dbTransaction->rollback("loan delete failed " . json_encode($errors));

                    $response = [
                        'status' => FALSE,
                        'error' => "Loan delete failed $errors"
                    ];
                }
                else{
                    $dbTransaction->commit();
                    $response = [
                        'status' => TRUE,
                        'success' => "Loan delete successfully"
                    ];
                }

            $this->curlRequestInstance->sendHttpResponse($response);
        } catch (Exception $e) {
             $message = $e->getMessage();
            $response = [
                        'status' => FALSE,
                        'error' => "Loan delete failed $message"
                    ];
            $this->curlRequestInstance->sendHttpResponse($response);
        }
        catch (Phalcon\Mvc\Model\Transaction\Failed $e) {
            $message = $e->getMessage();
            $response = [
                        'status' => FALSE,
                        'error' => "Loan delete failed $message"
                    ];
            $this->curlRequestInstance->sendHttpResponse($response);
        }
    }

    public function awardAction() {
        $this->view->disable();
        $response = NULL;

        $transactionManager = new TransactionManager();
        $dbTransaction = $transactionManager->get();

        try {
            $token = $this->config->endpoints->token;
            $requestData = $this->request->getJsonRawBody();
            $user = $this->session->get('user');

            $loanId = $requestData->loanId;

            if(!$loanId){
               $response = [
                        'status' => Error,
                        'success' => "Loan not found"
                    ]; 
                 $this->curlRequestInstance->sendHttpResponse($response);

            }

             $loan = Loans::findFirst(array("loanId=:id: ",
                    'bind' => array("id" => $loanId)));

            $loan->status=1;
            $loan->userId=$user['userId'];

             if ($loan->save() === false) {
                    $errors = array();
                    $messages = $loan->getMessages();
                    foreach ($messages as $message) {
                        $e["message"] = $message->getMessage();
                        $e["field"] = $message->getField();
                        $errors[] = $e;
                    }
                    $dbTransaction->rollback("loan award failed " . json_encode($errors));

                    $response = [
                        'status' => FALSE,
                        'error' => "Loan award failed $errors"
                    ];
                }
                else{
                    $dbTransaction->commit();
                    $response = [
                        'status' => TRUE,
                        'success' => "Loan award successfully"
                    ];
                }

            $this->curlRequestInstance->sendHttpResponse($response);
        } catch (Exception $e) {
             $message = $e->getMessage();
            $response = [
                        'status' => FALSE,
                        'error' => "Loan award failed $message"
                    ];
            $this->curlRequestInstance->sendHttpResponse($response);
        }
        catch (Phalcon\Mvc\Model\Transaction\Failed $e) {
            $message = $e->getMessage();
            $response = [
                        'status' => FALSE,
                        'error' => "Loan award failed $message"
                    ];
            $this->curlRequestInstance->sendHttpResponse($response);
        }
    }

    public function payAction() {
        $this->view->disable();
        $response = NULL;

        $transactionManager = new TransactionManager();
        $dbTransaction = $transactionManager->get();

        try {
            $token = $this->config->endpoints->token;
            $requestData = $this->request->getJsonRawBody();
            $user = $this->session->get('user');

            $loanId = $requestData->loanId;
            $loanRepaymentAmount = $requestData->loanRepaymentAmount;
            $isFromShares = $requestData->isFromShares;

            if(!$loanId){
               $response = [
                        'status' => Error,
                        'success' => "Loan not found"
                    ]; 
                 $this->curlRequestInstance->sendHttpResponse($response);
                 return;

            }

             $loan = Loans::findFirst(array("loanId=:id: ",
                    'bind' => array("id" => $loanId)));

             if($isFromShares){
                $totalSavings = $this->rawSelect("SELECT sum(savingsAmount) as totalSavings from savings where memberId=".$loan->memberId);
                $totalSavings = $totalSavings[0]['totalSavings'];

                if($totalSavings<=$loanRepaymentAmount){
                     $response = [
                        'status' => Error,
                        'success' => "Savings amount not enough"
                    ]; 
                    $this->curlRequestInstance->sendHttpResponse($response);
                     //return;
                }
                else {
                     $savings = new Savings();
                     $savings->savingsAmount = ($loanRepaymentAmount*-1);
                     $savings->userId = $user['userId'];
                     $savings->memberId = $loan->memberId;
                     $savings->createdAt = date("Y-m-d H:i:s");

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
                            'error' => "Savings deduction failed $errors"
                        ];

                        $this->curlRequestInstance->sendHttpResponse($response);
                        //return;
                   }
                }
             }
             

             if($loan->repaidAmount+$loanRepaymentAmount == $loan->loanAmount){
                $loan->status=2;
             }
             else{
                $loan->status=1;
             }

            $loan->repaidAmount=$loan->repaidAmount+$loanRepaymentAmount;
            $loan->userId=$user['userId'];


             if ($loan->save() === false) {
                    $errors = array();
                    $messages = $loan->getMessages();
                    foreach ($messages as $message) {
                        $e["message"] = $message->getMessage();
                        $e["field"] = $message->getField();
                        $errors[] = $e;
                    }
                    $dbTransaction->rollback("loan pay failed " . json_encode($errors));

                    $response = [
                        'status' => FALSE,
                        'error' => "Loan pay failed $errors"
                    ];
                }
                else{
                    $dbTransaction->commit();
                    $response = [
                        'status' => TRUE,
                        'success' => "Loan pay successfully"
                    ];
                }

            $this->curlRequestInstance->sendHttpResponse($response);
        } catch (Exception $e) {
             $message = $e->getMessage();
            $response = [
                        'status' => FALSE,
                        'error' => "Loan pay failed $message"
                    ];
            $this->curlRequestInstance->sendHttpResponse($response);
        }
        catch (Phalcon\Mvc\Model\Transaction\Failed $e) {
            $message = $e->getMessage();
            $response = [
                        'status' => FALSE,
                        'error' => "Loan pay failed $message"
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

            $now = date("y-m-d");
            $loanRepayDate = $requestData->loanRepayDate;
            $loanAmount=$requestData->loanAmount;
            $loanRepaymentAmount = $requestData->loanRepaymentAmount;
            $loanRepayDate =$requestData->loanRepayDate;
            $loanId = $requestData->loanId;

            if(!$loanId){
               $response = [
                        'status' => Error,
                        'success' => "Loan not found"
                    ]; 
                 $this->curlRequestInstance->sendHttpResponse($response);

            }

             $loan = Loans::findFirst(array("loanId=:id: ",
                    'bind' => array("id" => $loanId)));

             if(!$loan){
                $response = [
                        'status' => Error,
                        'success' => "Loan not found"
                    ]; 
                 $this->curlRequestInstance->sendHttpResponse($response);
             }

            // $this->logger->logMessage('customerRedirect', 'Customer: ' . $user['userId'], 0);
             //exit;

            if($loanRepayDate){
                $datetime1 = new DateTime($loanRepayDate);
                $datetime2 = new DateTime($now);
                $datediff = $datetime1->diff($datetime2);
                $datediff = abs($datediff->format('%R%a '));

                $interestRate = $this->calculateRate($datediff);
                if(!$loanAmount){
                    $loanAmount=$loan->loanAmount;
                }

                $amountToPay = ($interestRate*$loanAmount)+$loanAmount;

                 $loan->loanRepayDate = $loanRepayDate;
                 $loan->amountToPay = $amountToPay;
                 $loan->interestRate = $interestRate;

            }

            if($loanAmount){
                $loan->loanAmount = $requestData->loanAmount;
            }

            if($loanRepaymentAmount){
                $loan->repaidAmount = $loanRepaymentAmount;
            }

            $loan->userId=$user['userId'];

             if ($loan->save() === false) {
                    $errors = array();
                    $messages = $loan->getMessages();
                    foreach ($messages as $message) {
                        $e["message"] = $message->getMessage();
                        $e["field"] = $message->getField();
                        $errors[] = $e;
                    }
                    $dbTransaction->rollback("loan create failed " . json_encode($errors));

                    $response = [
                        'status' => FALSE,
                        'error' => "Loan edit failed $errors"
                    ];
                }
                else{
                    $dbTransaction->commit();
                    $response = [
                        'status' => TRUE,
                        'success' => "Loan edit successfully"
                    ];
                }

            $this->curlRequestInstance->sendHttpResponse($response);
        } catch (Exception $e) {
             $message = $e->getMessage();
            $response = [
                        'status' => FALSE,
                        'error' => "Loan edit failed $message"
                    ];
            $this->curlRequestInstance->sendHttpResponse($response);
        }
        catch (Phalcon\Mvc\Model\Transaction\Failed $e) {
            $message = $e->getMessage();
            $response = [
                        'status' => FALSE,
                        'error' => "Loan edit failed $message"
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

            $now = date("y-m-d");
            $loanRepayDate = $requestData->loanRepayDate;
            $loanAmount=$requestData->loanAmount;


            $datetime1 = new DateTime($loanRepayDate);
            $datetime2 = new DateTime($now);
            $datediff = $datetime1->diff($datetime2);
            $datediff = abs($datediff->format('%R%a '));

            $interestRate = $this->calculateRate($datediff);
            $amountToPay = ($interestRate*$loanAmount)+$loanAmount;

          

            $loan = new Loans();
            $loan->loanAmount = $requestData->loanAmount;
            $loan->memberId = $requestData->memberId;
            $loan->loanRepayDate = $loanRepayDate;
            $loan->amountToPay = $amountToPay;
            $loan->interestRate = $interestRate;
            $loan->status = 0;
            $loan->loanOfferDate =  date("Y-m-d H:i:s");
            $loan->createdAt  = date("Y-m-d H:i:s");
            $loan->userId=$user['userId'];

             if ($loan->save() === false) {
                    $errors = array();
                    $messages = $loan->getMessages();
                    foreach ($messages as $message) {
                        $e["message"] = $message->getMessage();
                        $e["field"] = $message->getField();
                        $errors[] = $e;
                    }
                    $dbTransaction->rollback("loan create failed " . json_encode($errors));

                    $response = [
                        'status' => FALSE,
                        'error' => "Loan create failed $errors"
                    ];
                }
                else{
                    $dbTransaction->commit();
                    $response = [
                        'status' => TRUE,
                        'success' => "Loan created successfully"
                    ];
                }

            $this->curlRequestInstance->sendHttpResponse($response);
        } catch (Exception $e) {
             $message = $e->getMessage();
            $response = [
                        'status' => FALSE,
                        'error' => "Loan create failed $message"
                    ];
            $this->curlRequestInstance->sendHttpResponse($response);
        }
        catch (Phalcon\Mvc\Model\Transaction\Failed $e) {
            $message = $e->getMessage();
            $response = [
                        'status' => FALSE,
                        'error' => "Loan create failed $message"
                    ];
            $this->curlRequestInstance->sendHttpResponse($response);
        }
    }

}
