<?php

namespace Hapex\Core\Helper;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;

class UrlHelper extends AbstractHelper
{
    protected $objectManager;
    protected $helperLog;
    protected $curl;
    protected $userAgent = "JRI/1.0";
    protected $connectTimeout = 30;
    protected $timeout = 60;

    public function __construct(Context $context, ObjectManagerInterface $objectManager, Curl $curl, LogHelper $helperLog)
    {
        parent::__construct($context, $objectManager);
        $this->curl = $curl;
        $this->helperLog = $helperLog;
    }

    public function getRemoteContent($url = null)
    {
        return $this->get($url)->getBody();
    }

    public function sendSlackWebhook($webhookUrl = null, $message = null)
    {
        return $this->sendWebhook($webhookUrl, json_encode(["text" => $message]), "application/json");
    }

    public function sendWebhook($webhookUrl = null, $message = null, $contentType = "text/plain")
    {
        return $this->sendRemoteContent($webhookUrl, $message, $contentType)->getBody();
    }

    public function sendRemoteContent($url = null, $data = null, $contentType = null)
    {
        return $this->post($url, $data, $contentType);
    }

    public function urlExists($remoteUrl = null)
    {
        $exists = false;
        try {
            if (filter_var($remoteUrl, FILTER_VALIDATE_URL) === FALSE) {
                $host = isset($_SERVER["HTTP_HOST"]) ? stripslashes($_SERVER["HTTP_HOST"]) : "jricards.com";
                $remoteUrl = "https://$host$remoteUrl";
            }
            $exists = $this->get($remoteUrl)->getStatus() === 200;
        } catch (\Throwable $e) {
            $this->helperLog->errorLog(__METHOD__, $this->getExceptionTrace($e));
            $exists = false;
        } finally {
            return $exists;
        }
    }

    public function ping($url = null)
    {
        try {
            $options = [
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_HEADER => 0,
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_NOBODY => 1
            ];
            $this->curl->setOptions($options);
            $this->curl->get($url);
        } catch (\Throwable $e) {
            $this->helperLog->errorLog(__METHOD__, $this->getExceptionTrace($e));
            return $this->ping($url);
        } finally {
            return $this->curl;
        }
    }

    private function get($url = null)
    {
        try {
            $options = [
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_HEADER => 0,
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_USERAGENT => $this->userAgent,
                CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
                CURLOPT_TIMEOUT => $this->timeout
            ];
            $this->curl->setOptions($options);
            $this->curl->get($url);
        } catch (\Throwable $e) {
            $this->helperLog->errorLog(__METHOD__, $this->getExceptionTrace($e));
            return $this->get($url);
        } finally {
            return $this->curl;
        }
    }

    private function post($url = null, $data = null, $contentType = "application/json")
    {
        try {
            $options = [
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_HEADER => 0,
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_USERAGENT => $this->userAgent,
                CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
                CURLOPT_TIMEOUT => $this->timeout
            ];
            $headers = ["Content-Type" => $contentType];
            $this->curl->setOptions($options);
            $this->curl->setHeaders($headers);
            $this->curl->post($url, $data);
        } catch (\Throwable $e) {
            $this->helperLog->errorLog(__METHOD__, $this->getExceptionTrace($e));
            return $this->post($url, $data, $contentType);
        } finally {
            return $this->curl;
        }
    }
}
