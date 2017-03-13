<?php

/* Magedelight
 * Copyright (C) 2016 Magedelight <info@magedelight.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category Magedelight
 * @package Magedelight_Giftcard
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Giftcard\Controller\Adminhtml\Codes;

use Magento\Backend\App\Action;
use Magedelight\Giftcard\Model\Code;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\ItemFactory;
use Magedelight\Giftcard\Helper\Data;
use Magento\Backend\Model\Auth\Session;


/**
 * Class Save
 *
 * @package Magedelight\Giftcard\Controller\Adminhtml\Code
 */
class Save extends \Magento\Backend\App\Action {

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magedelight_Giftcard::save';

    /**
     * @var DataPersistorInterface
     */
    public $dataPersistor;

    
    /**
     * @var PostDataProcessor
     */
    public $dataProcessor;
    
    /**
     * @var ItemFactory
     */
    public $itemFactory;
    
    /**
     * @var \Magedelight\Giftcard\Helper\Data
     */
    public $helper;  
    
    /**
     * @var DataPersistorInterface
     */
    public $code;

    /**
     * @var Session
     */
    public $authSession;
    
    /**
     * @param Code $code
     * @param Action\Context $context
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Code $code,
        Action\Context $context, 
        PostDataProcessor $dataProcessor,
        DataPersistorInterface $dataPersistor,
        ItemFactory $itemFactory,
        Data $helper,
        Session $authSession
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->dataProcessor = $dataProcessor;
        parent::__construct($context);
        $this->code = $code;
        $this->itemFactory = $itemFactory;
        $this->helper = $helper;
        $this->authSession = $authSession;
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(){
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $codeId = null;
        $returnToEdit = null;
        if($data){
            $data = $this->dataProcessor->filter($data);
            try {
                if(empty($data['code_id'])){
                    $data['code_id'] = null;
                    if (isset($data['code']) AND !empty($data['code'])){
                        $codes = $this->code->loadByCode($data['code']);
                        if(count($codes->getData()) >= 1){
                            throw new \Magento\Framework\Exception\LocalizedException(__('Gifcard Code Already Exists'));
                        }
                    }
                }
                
                $id = $this->getRequest()->getParam('code_id');
                if($id){
                    $this->code->load($id);
                }
             
                $this->code->setData($data);
                $this->_eventManager->dispatch(
                    'giftcard_code_prepare_save', ['code' => $this->code, 'request' => $this->getRequest()]
                );

                $form = $this->code->save();
                $codeId = $form->getCodeId();
                
                if(isset($data['is_active']) AND $data['is_active'] == 1){
                    $this->sendMail($form);
                }
                $this->messageManager->addSuccess(__('You saved the code.'));
                $this->dataPersistor->clear('giftcard_code');
                
                if ($this->getRequest()->getParam('back')) {
                    $returnToEdit = true;
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $returnToEdit = true;
            } catch (\Exception $e) {
                $this->messageManager->addException($e, $e->getMessage());
                $returnToEdit = true;
            }
            
            if($codeId){
                //$this->dataPersistor->set('giftcard_code', $form->getData());
                if($id){
                    $action = 'Update';
                    $comments = 'Code Updated By Admin';
                }
                else{
                    $action = 'Create';
                    $comments = 'New Code Created By User';
                }
                $this->createTransacton($form,$action,$comments);
            }
            else{
                $this->dataPersistor->set('giftcard_code', $data);  
            }      
        }
       
        if ($returnToEdit){
            if ($codeId) {
                $resultRedirect->setPath('*/*/edit', ['code_id' => $codeId]);
            } else {
                $resultRedirect->setPath('*/*/new');
            }
        } else {
            $resultRedirect->setPath('*/*/');
        }
        return $resultRedirect;
    }
    
    public function getCurrentUser()
    {
        return $this->authSession->getUser();
    }
    
    public function sendMail($codeData){
        $itemsData = $codeData->getData();
        $item = $this->itemFactory->create()->load($codeData->getOrderItemId());
        $this->helper->sendToFriend($itemsData);
    }
      
    public function createTransacton($code,$action,$comments){
        $data = [];
        $data['code_id'] = $code->getId();
        $data['action'] = $action;
        $data['value'] = $code->getRemainingBalance();
        $data['status'] = $code->getStatus();
        $data['order'] = $code->getOrderId();
        $data['comments'] = $comments;
        $data['action_by'] = 'Created By '.$this->getCurrentUser()->getUsername();
        $this->helper->addGicardftTransaction($data);
    }
}
