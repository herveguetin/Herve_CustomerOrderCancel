<?php
/**
 * @category    Herve
 * @package     Herve_CustomerOrderCancel
 * @copyright   Copyright (c) 2013 Hervé Guétin (http://www.herveguetin.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Herve_CustomerOrderCancel_OrderController extends Mage_Core_Controller_Front_Action {

    /**
     * Cancel order on customer request
     */
    public function cancelAction()
    {

        // Retrieve order_id passed by clicking on "Cancel Order" in customer account
        $orderId = $this->getRequest()->getParam('order_id');

        // Load Mage_Sales_Model_Order object
        $order = Mage::getModel('sales/order')->load($orderId);

        // Retrieve catalog session.
        // We must use catalog session as customer session messages are not initiated for sales order view
        // and this is where we want to redirect at the end of this action
        // @see Mage_Sales_Controller_Abstract::_viewAction()
        $session = Mage::getSingleton('catalog/session');

        try {

            // Make sure that the order can still be canceled since customer clicked on "Cancel Order"
            if(!Mage::helper('customerordercancel')->canCancel($order)) {
                throw new Exception('Order cannot be canceled anymore.');
            }

            // Cancel and save the order
            $order->cancel();
            $order->save();

            // If sending transactionnal email is enabled in system configuration, we send the email
            if(Mage::getStoreConfigFlag('sales/cancel/send_email')) {
                $order->sendOrderUpdateEmail();
            }

            $session->addSuccess($this->__('The order has been canceled.'));
        }
        catch (Exception $e) {
            Mage::logException($e);
            $session->addError($this->__('The order cannot be canceled.'));
        }

        // Redirect to current sale order view
        $this->_redirect('sales/order/view', array('order_id' => $orderId));
    }
}