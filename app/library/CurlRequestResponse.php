<?php

/**
 * Description of CurlRequest
 *
 * @author anam
 */
class CurlRequestResponse {

    private static $instance = null;

    private function __construct() {
        
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new CurlRequestResponse();
        }

        return self::$instance;
    }

    public function makeHttpRequest($url, $data) {
        $postData = json_encode($data);

        $httpRequest = curl_init($url);
        curl_setopt($httpRequest, CURLOPT_POST, 1);
        curl_setopt($httpRequest, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($httpRequest, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($httpRequest, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData))
        );
        $response = curl_exec($httpRequest);
        $status = curl_getinfo($httpRequest, CURLINFO_HTTP_CODE);
        curl_close($httpRequest);

        return array('status' => $status, 'result' => json_decode($response));
    }

    public function makeHttpGetRequest($url) {
        $httpRequest = curl_init($url);
        curl_setopt($httpRequest, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($httpRequest);
        $status = curl_getinfo($httpRequest, CURLINFO_HTTP_CODE);
        curl_close($httpRequest);

        return array('status' => $status, 'result' => json_decode($response));
    }

    public function sendHttpResponse($resp) {
        $response = new \Phalcon\Http\Response();
        $response->setStatusCode(200, "Success");
        $response->setHeader("Content-Type", "application/json");
        $response->setJsonContent($resp);
        $response->send();
    }

    public function setRights($instance) {
        $authorize = $instance->session->get('authorize');
        $instance->view->setVar('allowed', $authorize);
        $user = $instance->session->get('user');
        $instance->view->setVar('roleName', $user->roleName);
        $instance->view->setVar('fullName', $user->fullName);
    }

}
