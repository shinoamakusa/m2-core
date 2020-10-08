<?php

namespace Hapex\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\DataObject;

class BaseHelper extends AbstractHelper
{
    protected $objectManager;
    protected $helperDb;
    protected $helperLog;
    protected $helperFile;
    protected $helperDate;
    protected $helperUrl;

    public function __construct(Context $context, ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
        $this->helperDb = $this->generateClassObject(DbHelper::class);
        $this->helperLog = $this->generateClassObject(LogHelper::class);
        $this->helperFile = $this->generateClassObject(FileHelper::class);
        $this->helperDate = $this->generateClassObject(DateHelper::class);
        $this->helperUrl = $this->generateClassObject(UrlHelper::class);
        parent::__construct($context);
    }

    public function getLogHelper()
    {
        return $this->helperLog;
    }

    public function getDbHelper()
    {
        return $this->helperDb;
    }

    public function getFileHelper()
    {
        return $this->helperFile;
    }

    public function getDateHelper()
    {
        return $this->helperDate;
    }

    public function getUrlHelper()
    {
        return $this->helperUrl;
    }

    protected function getArrayValue($array = [], $index = 0, $defaultValue = null)
    {
        return $array[$index] ?? $defaultValue;
    }

    public function sendOutput($output = null)
    {
        try {
            print_r($output);
            return true;
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            return false;
        }
    }

    public function displayBlock($blockId = null)
    {
        try {
            $this->sendOutput($this->getBlockHtml($blockId));
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
        }
    }

    public function getBlockHtml($blockId = null)
    {
        $html  = null;
        try {
            $block = $this->generateClassObject("Magento\Cms\Block\Block");
            $block->setBlockId($blockId);
            $html = $block->toHtml();
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $html = null;
        } finally {
            return $html;
        }
    }

    public function generateClassObject($class = null)
    {
        $object = $this->objectManager->create(DataObject::class);
        try {
            $object = $this->objectManager->get($class);
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $object = $this->objectManager->create(DataObject::class);
        } finally {
            return $object;
        }
    }

    public function sortDataByColumn(&$data = [], $sortColumn = "qty", $sortDirection = SORT_DESC)
    {
        array_multisort(array_column($data, $sortColumn), $sortDirection, $data);
    }

    public function sortDataBy2Columns(&$data = [], $sortColumn = "qty", $sortDirection = SORT_DESC, $sortColumn2 = "qty", $sortDirection2 = SORT_DESC)
    {
        array_multisort(array_column($data, $sortColumn), $sortDirection, array_column($data, $sortColumn2), $sortDirection2, $data);
    }
}
