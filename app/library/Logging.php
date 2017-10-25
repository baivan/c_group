<?php

date_default_timezone_set('Africa/Nairobi');

/**
 * Provides the system logging functionality
 */
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;

class Logging extends Phalcon\Mvc\Controller {

    /**
     * Logs an application message
     * 
     * @param string $system
     * @param string $module
     * @param string $user Currently logged in userID
     * @param string $function Function requesting logging
     * @param string $message Message to be logged
     * @param Logger $logger Logger instance
     * @param int $logLevel Logging level
     * 
     * @return void
     */
    public function logMessage($function, $message, $logLevel) {

        $infoLogger = new FileAdapter($this->config->log . INFO_FILE);
        $errorLogger = new FileAdapter($this->config->log . ERROR_FILE);

        $messageData = 'ENVIROFIT | ' . $function . ': ' . $message;

        switch ($logLevel) {
            case 1:
                $errorLogger->error($messageData);
                break;

            default:
                $infoLogger->info($messageData);
                break;
        }
    }

}
