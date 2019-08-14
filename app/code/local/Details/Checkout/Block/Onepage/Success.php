<?php
/**
 * Checkout Success-module for Magento 
 *
 * @category    Checkout
 * @package     Details_Checkout
 * @author      Nikita Nautiyal
  @for any queries email at: nautiyal.nikita@gmail.com
*/
class Details_Checkout_Block_Onepage_Success extends Mage_Core_Block_Template
{
/* include all the methods in Mage_Core Checkout Onepage Success Block */
	  public function __construct()
	 {
	 parent::__construct();
	 } 

    public function getOrderId()
    {
        return $this->_getData('order_id');
    }

    public function canPrint()
    {
        return $this->_getData('can_view_order');
		
    }

    public function getPrintUrl()
    {
        return $this->_getData('print_url');
    }

    public function getViewOrderUrl()
    {
        return $this->_getData('view_order_id');
    }

    public function isOrderVisible()
    {
        return (bool)$this->_getData('is_order_visible');
    }

    public function getProfileUrl(Varien_Object $profile)
    {
        return $this->getUrl('sales/recurring_profile/view', array('profile' => $profile->getId()));
    }

    protected function _beforeToHtml()
    {
        $this->_prepareLastOrder();
        $this->_prepareLastBillingAgreement();
        $this->_prepareLastRecurringProfiles();
        return parent::_beforeToHtml();
    }

    protected function _prepareLastOrder()
    {
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        if ($orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->getId()) {
                $isVisible = !in_array($order->getState(),
                    Mage::getSingleton('sales/order_config')->getInvisibleOnFrontStates());
                $this->addData(array(
                    'is_order_visible' => $isVisible,
                    'view_order_id' => $this->getUrl('sales/order/view/', array('order_id' => $orderId)),
                    'print_url' => $this->getUrl('sales/order/print', array('order_id'=> $orderId)),
                    'can_print_order' => $isVisible,
                    'can_view_order'  => Mage::getSingleton('customer/session')->isLoggedIn() && $isVisible,
                    'order_id'  => $order->getIncrementId(),
                ));
            }
        }
    }

    protected function _prepareLastBillingAgreement()
    {
        $agreementId = Mage::getSingleton('checkout/session')->getLastBillingAgreementId();
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if ($agreementId && $customerId) {
            $agreement = Mage::getModel('sales/billing_agreement')->load($agreementId);
            if ($agreement->getId() && $customerId == $agreement->getCustomerId()) {
                $this->addData(array(
                    'agreement_ref_id' => $agreement->getReferenceId(),
                    'agreement_url' => $this->getUrl('sales/billing_agreement/view',
                        array('agreement' => $agreementId)
                    ),
                ));
            }
        }
    }

    protected function _prepareLastRecurringProfiles()
    {
        $profileIds = Mage::getSingleton('checkout/session')->getLastRecurringProfileIds();
        if ($profileIds && is_array($profileIds)) {
            $collection = Mage::getModel('sales/recurring_profile')->getCollection()
                ->addFieldToFilter('profile_id', array('in' => $profileIds))
            ;
            $profiles = array();
            foreach ($collection as $profile) {
                $profiles[] = $profile;
            }
            if ($profiles) {
                $this->setRecurringProfiles($profiles);
                if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                    $this->setCanViewProfiles(true);
                }
            }
        }
    } 
	
	protected function getItemCount()
	{
		$sOrderId = Mage::getSingleton('checkout/session')->getLastOrderId();
		$order = Mage::getModel('sales/order')->loadByIncrementId($this->getOrderId());
		$items = $order->getAllVisibleItems(); 
		$itemcount=count($items);
			return $itemcount;
	}
		
	protected function getItemsInfo()
	{
  		$sOrderId = Mage::getSingleton('checkout/session')->getLastOrderId();
		$order = Mage::getModel('sales/order')->loadByIncrementId($this->getOrderId()); 
		$items = $order->getAllVisibleItems();  
			return $items;

	}
 	
	protected function getShippingMethod()
	{
		$sOrderId = Mage::getSingleton('checkout/session')->getLastOrderId();
		$order = Mage::getModel('sales/order')->loadByIncrementId($this->getOrderId()); 
		$shipping_method = $order->getShippingMethod();
		$mname = explode("_", $shipping_method);
		return "Shipping & Handling(" . $mname[0] . ")" ;
	}

	protected function getSubTotal()
	{
		$sOrderId = Mage::getSingleton('checkout/session')->getLastOrderId();
		$order = Mage::getModel('sales/order')->loadByIncrementId($this->getOrderId());
		return $order->getSubtotal();
	}
	
	protected function getGrandtotal()
	{
		$sOrderId = Mage::getSingleton('checkout/session')->getLastOrderId();
		$order = Mage::getModel('sales/order')->loadByIncrementId($this->getOrderId());
		return $order->getGrandTotal();
	}

	protected function getShippingamount()
	{
		$sOrderId = Mage::getSingleton('checkout/session')->getLastOrderId();
		$order = Mage::getModel('sales/order')->loadByIncrementId($this->getOrderId());
		return $order->getShippingAmount();
	}
	
	protected function getPaymentmethod()
	{
		$order = Mage::getModel('sales/order')->loadByIncrementId($this->getOrderId());
		$payment_method = $order->getPayment()->getMethodInstance()->getTitle();   
		return $payment_method;
	}
	protected function getShippingaddress()
	{
		$sOrderId = Mage::getSingleton('checkout/session')->getLastOrderId();
		$order = Mage::getModel('sales/order')->loadByIncrementId($this->getOrderId());
		$shipping_address_data = $order->getShippingAddress(); 
		$shippingaddress = "<br><b> First Name: " . $shipping_address_data['firstname'] . " Last Name: " . $shipping_address_data['lastname'] . "<br> Street: " . $shipping_address_data['street'] . "<br> City: " . $shipping_address_data['city'] . ", Region:  " . $shipping_address_data['region'] . " Post Code:  " . $shipping_address_data['postcode'] . "<br> Country ID: " . $shipping_address_data['country_id'];
		
		return $shippingaddress;
	}
	
	protected function getBillingAddress()
	{
		$sOrderId = Mage::getSingleton('checkout/session')->getLastOrderId();
		$order = Mage::getModel('sales/order')->loadByIncrementId($this->getOrderId());
		$billing_address_data   = $order->getBillingAddress(); 
		$billingaddress = "<br><b> First name: " . $billing_address_data['firstname'] . " Last Name: " . $billing_address_data['lastname'] . "<br> Street: " . $billing_address_data['street'] . "<br> City: " . $billing_address_data['city'] . ", Region: " . $billing_address_data['region'] . " popstCode: " . $billing_address_data['postcode'] . "<br> Country ID: " . $billing_address_data['country_id'];
		
		return $billingaddress;
	}
}

