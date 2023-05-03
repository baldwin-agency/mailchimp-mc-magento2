<?php

namespace Ebizmarts\MailChimp\Controller\Adminhtml\Orders;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\OrderRepository;
use Ebizmarts\MailChimp\Helper\Data as MailChimpHelper;

class Member extends Action
{
    /**
     * @var MailChimpHelper
     */
    private $helper;
    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param Context $context
     * @param MailChimpHelper $helper
     * @param OrderRepository $orderRepository
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Context $context,
        MailChimpHelper $helper,
        OrderRepository $orderRepository,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->orderRepository = $orderRepository;
        $this->urlBuilder = $urlBuilder;
    }
    public function execute()
    {
        $param = $this->getRequest()->getParams();
        $orderId = $this->getRequest()->getParam('orderId');
        $order = $this->orderRepository->get($orderId);
        $api = $this->helper->getApi($order->getStoreId());
        try {
            $email = $order->getCustomerEmail();
            $listId = $this->helper->getDefaultList($order->getStoreId());
            $member = $api->lists->members->get($listId, hash('md5', strtolower($email)));
            $memberId = $member['contact_id'];
            $url = $this->urlBuilder->getUrl('https://admin.mailchimp.com/audience/contact-profile') . "?contact_id=$memberId";
            $this->_redirect($url);
        } catch(\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect($this->urlBuilder->getUrl('sales/order'));
        }
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ebizmarts_MailChimp::mailchimp_access');
    }
}
