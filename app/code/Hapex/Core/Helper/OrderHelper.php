<?php

namespace Hapex\Core\Helper;

use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\OrderRepository;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;

class OrderHelper extends BaseHelper
{
    protected $tableOrder;
    protected $tableOrderGrid;
    protected $tableOrderItem;
    protected $tableOrderAddress;
    protected $orderRepository;
    protected $address;

    public function __construct(Context $context, ObjectManagerInterface $objectManager, OrderRepository $orderRepository, Address $address)
    {
        parent::__construct($context, $objectManager);
        $this->orderRepository = $orderRepository;
        $this->address = $address;
        $this->tableOrder = $this->helperDb->getSqlTableName('sales_order');
        $this->tableOrderGrid = $this->helperDb->getSqlTableName('sales_order_grid');
        $this->tableOrderItem = $this->helperDb->getSqlTableName('sales_order_item');
        $this->tableOrderAddress = $this->helperDb->getSqlTableName('sales_order_address');
    }

    public function getOrder($orderId)
    {
        return $this->getOrderById($orderId);
    }

    public function getOrderIdsByProductSku($productSku = null)
    {
        $result = null;
        try {
            $sql = "SELECT order_id FROM " . $this->tableOrderItem . " WHERE sku LIKE '$productSku' GROUP BY order_id";
            $result = array_column($this->helperDb->sqlQueryFetchAll($sql), "order_id");
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $result = null;
        } finally {
            return $result;
        }
    }

    public function getOrderIdsByCustomerId($customerId = 0)
    {
        $result = null;
        try {
            $sql = "SELECT entity_id FROM " . $this->tableOrder . " WHERE customer_id = $customerId GROUP BY entity_id ORDER BY created_at DESC";
            $result = array_column($this->helperDb->sqlQueryFetchAll($sql), "entity_id");
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $result = null;
        } finally {
            return $result;
        }
    }

    public function getOrderCreatedDate($orderId = null)
    {
        $date = null;
        try {
            $date =  $this->getOrderFieldValue($orderId, "created_at");
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $date = null;
        } finally {
            return $date;
        }
    }

    public function getOrderUpdatedDate($orderId = null)
    {
        $date = null;
        try {
            $date =  $this->getOrderFieldValue($orderId, "updated_at");
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $date = null;
        } finally {
            return $date;
        }
    }

    public function getOrderIdEmail($orderId = 0)
    {
        return $this->getCustomerEmail($orderId);
    }

    public function getOrderEmail($order = null)
    {
        return $this->getOrderCustomerEmail($order);
    }

    public function getOrderIdName($orderId = 0)
    {
        return $this->getBillingName($orderId);
    }

    public function getOrderName($order = null)
    {
        return $this->getOrderCustomerName($order);
    }

    public function getOrderIdCustomerid($orderId = 0)
    {
        $customerId = 0;
        try {
            $customerId = (int) $this->getOrderFieldValue($orderId, "customer_id");
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $customerId = 0;
        } finally {
            return $customerId;
        }
    }

    public function getOrderCustomerId($order = null)
    {
        $customerId = 0;
        try {
            $customerId = $order->getCustomer()->getId();
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $customerId = 0;
        } finally {
            return $customerId;
        }
    }

    public function getQtyCanceled($orderId = null, $productSku = null)
    {
        $qty = 0;
        try {
            $qty = (int) $this->getOrderItemFieldValue($orderId, $productSku, "sum(qty_canceled)");
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $qty = 0;
        } finally {
            return $qty;
        }
    }

    public function getQtyOrdered($orderId = null, $productSku = null)
    {
        $qty = 0;
        try {
            $qty = (int) $this->getOrderItemFieldValue($orderId, $productSku, "sum(qty_ordered)");
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $qty = 0;
        } finally {
            return $qty;
        }
    }

    public function getQtyRefunded($orderId = null, $productSku = null)
    {
        $qty = 0;
        try {
            $qty = (int) $this->getOrderItemFieldValue($orderId, $productSku, "sum(qty_refunded)");
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $qty = 0;
        } finally {
            return $qty;
        }
    }

    protected function getBillingName($orderId = null)
    {
        $fullName = null;
        try {
            $fullName = $this->getOrderGridFieldValue($orderId, "billing_name");
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $fullName = null;
        } finally {
            return $fullName;
        }
    }

    protected function getCustomerEmail($orderId = null)
    {
        $email = null;
        try {
            $email = $this->getOrderGridFieldValue($orderId, "customer_email");
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $email = null;
        } finally {
            return $email;
        }
    }

    protected function getOrderBillingAddress($order = null)
    {
        $address = $this->address;
        try {
            $address = $order->getBillingAddress();
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $address = $this->address;
        } finally {
            return $address;
        }
    }

    protected function getOrderShippingAddress($order = null)
    {
        $address = $this->address;
        try {
            $address = $order->getShippingAddress();
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $address = $this->address;
        } finally {
            return $address;
        }
    }

    protected function getOrderCustomerName($order = null)
    {
        $customerName = null;
        try {
            $address = $this->getOrderBillingAddress($order);
            $customerName = $address->getName();
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $customerName = null;
        } finally {
            return $customerName;
        }
    }

    protected function getOrderCustomerEmail($order = null)
    {
        $customerEmail = null;
        try {
            $address = $this->getOrderBillingAddress($order);
            $customerEmail = $address->getEmail();
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $customerEmail = null;
        } finally {
            return $customerEmail;
        }
    }

    protected function getOrderItems($order = null)
    {
        $items = [];
        try {
            foreach ($order->getItems() as $item) {
                if (!$item->isDeleted() && !$item->getParentItem()) {
                    $items[] = $item;
                }
            }
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $items = [];
        } finally {
            return $items;
        }
    }

    protected function getShippingName($orderId = null)
    {
        $fullName = null;
        try {
            $fullName = $this->getOrderGridFieldValue($orderId, "shipping_name");
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $fullName = null;
        } finally {
            return $fullName;
        }
    }

    private function getOrderById($orderId)
    {
        $order = null;
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $order = null;
        } finally {
            return $order;
        }
    }

    private function getOrderFieldValue($orderId = null, $fieldName = null)
    {
        try {
            $sql = "SELECT $fieldName FROM " . $this->tableOrder . " where entity_id = $orderId";
            $result = $this->helperDb->sqlQueryFetchOne($sql);
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $result = null;
        } finally {
            return $result;
        }
    }

    private function getOrderItemFieldValue($orderId = null, $productSku = null, $fieldName = null)
    {
        try {
            $sql = "SELECT $fieldName FROM " . $this->tableOrderItem . " where order_id = $orderId AND sku LIKE '$productSku'";
            $result = $this->helperDb->sqlQueryFetchOne($sql);
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $result = null;
        } finally {
            return $result;
        }
    }

    private function getOrderGridFieldValue($orderId = null, $fieldName = null)
    {
        try {
            $sql = "SELECT $fieldName FROM " . $this->tableOrderGrid . " where entity_id = $orderId";
            $result = $this->helperDb->sqlQueryFetchOne($sql);
        } catch (\Exception $e) {
            $this->helperLog->errorLog(__METHOD__, $e->getMessage());
            $result = null;
        } finally {
            return $result;
        }
    }
}
