<?php

namespace Realty\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Realty\Model\Realty;

class RealtyController extends AbstractActionController {

    public function indexAction() {
        $realtyId = (int) $this->params()->fromRoute(Realty::REALTY_ID);



        //$sm = $this->getServiceLocator();

        $form = new \Realty\Form\RealtyForm();

        return array(
            'form' => $form,
            'realtyId' => $realtyId,
        );
    }

    public function deleteAction() {
        $sm = $this->getServiceLocator();

        $realtyId = (int) $this->params()->fromRoute(Realty::REALTY_ID);

        $userId = $sm->get('logged_in_user_id');
        $realtyTable = $sm->get('realty_table');
        $realty = $realtyTable->getPostById($realtyId);

        if ($realty && $realty->get(Realty::USER_ID) != $userId) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $realtyTable->deleteRealtyById($realtyId);
        return $this->redirect()->toUrl("/realty/list");
    }

    public function addAction() {
        $sm = $this->getServiceLocator();
        $userId = $sm->get('logged_in_user_id');

        $data = array(
            Realty::USER_ID => $userId,
            Realty::CREATE_DATE => time(),
        );
        $realtyTable = $sm->get('realty_table');

        $realty = new \Realty\Model\Realty($data);
        $realtyTable->saveRealty($realty);

        $savedRealty = $realtyTable->getLastUserRealty($userId);
        $savedRealtyId = $savedRealty->get(Realty::REALTY_ID);

        return $this->redirect()->toUrl("/realty/edit/?realty_id=$savedRealtyId");
    }

    public function processAction() {
        if (!$this->request->isPost()) {
            return $this->redirect()->toUrl('/realty/add');
        }

        $realtyId = (int) $this->params()->fromRoute(Realty::REALTY_ID);
        $sm = $this->getServiceLocator();
        $userId = $sm->get('logged_in_user_id');

        $data = $this->request->getPost();

        $form = $sm->get('Realty\Form\RealtyForm');

        $form->setData($data);

        if (!$form->isValid()) { // todo valid form
            $view = new \Zend\View\Model\ViewModel();
            $view->setTemplate('/realty/realty/add');
            $view->setVariable('form', $form);

            return $view;
        } else {

            $data[Realty::REALTY_ID] = $realtyId;
            $data[Realty::CREATE_DATE] = time();
            $data[Realty::USER_ID] = $userId;

            $realtyTable = $sm->get('realty_table');
            $realty = $realtyTable->getRealtyById($realtyId);

            //$realty = new \Realty\Model\Realty();
            $realty->exchangeArray($data);

            \Application\Log\Logger::info(json_encode($data));

            $realtyTable->saveRealty($realty);

            $realtyId = $realtyTable->getLastUserRealty($userId)->get(Realty::REALTY_ID);

            return $this->redirect()->toUrl($data['redirect'] . '' . $realtyId);
        }
    }

    public function editAction() {
        $realtyId = (int) $this->params()->fromQuery(Realty::REALTY_ID);

        $sm = $this->getServiceLocator();
        $realtyTable = $sm->get('realty_table');
        $realty = $realtyTable->getRealtyById($realtyId);

        $step = $this->params()->fromQuery('step');

        if ($step == 'type' || empty($step)) {
            $form = $sm->get('Realty\Form\RealtyTypeForm');
        } else if ($step == 'contacts') {
            $form = $sm->get('Realty\Form\RealtyContactsForm');
        } else if ($step == 'address') {
            $form = $sm->get('Realty\Form\RealtyAddressForm');
        } else if ($step == 'map') {
            $form = $sm->get('Realty\Form\RealtyMapForm');
        } else if ($step == 'images') {
            $form = $sm->get('Realty\Form\RealtyMapForm');
        }

        $form->bind($realty);

        return array(
            'form' => $form,
            'realtyId' => $realtyId,
        );
    }

    public function viewAction() {
        $sm = $this->getServiceLocator();
        $userId = $sm->get('logged_in_user_id');

        $realtyId = (int) $this->params()->fromRoute(Realty::REALTY_ID);

        if (!$realtyId) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $realtyTable = $sm->get('realty_table');
        $realty = $realtyTable->getPostById($realtyId);

        if (!$realty) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        return array(
            'realty' => $realty,
        );
    }

    public function editTypeAction() {
        $realtyId = (int) $this->params()->fromRoute(Realty::REALTY_ID);

        $sm = $this->getServiceLocator();
        $realtyTable = $sm->get('realty_table');
        $realty = $realtyTable->getRealtyById($realtyId);

        $form = $sm->get('Realty\Form\RealtyTypeForm');

        $form->bind($realty);

        return array(
            'form' => $form,
            'realtyId' => $realtyId,
        );
    }

    public function editContactsAction() {
        $realtyId = (int) $this->params()->fromRoute(Realty::REALTY_ID);

        $sm = $this->getServiceLocator();
        $realtyTable = $sm->get('realty_table');
        $realty = $realtyTable->getRealtyById($realtyId);

        $form = $sm->get('Realty\Form\RealtyContactsForm');

        $form->bind($realty);

        return array(
            'form' => $form,
            'realtyId' => $realtyId,
        );
    }

    public function editAddressAction() {
        $realtyId = (int) $this->params()->fromRoute(Realty::REALTY_ID);

        $sm = $this->getServiceLocator();
        $realtyTable = $sm->get('realty_table');
        $realty = $realtyTable->getRealtyById($realtyId);

        $form = $sm->get('Realty\Form\RealtyAddressForm');

        $form->bind($realty);

        return array(
            'form' => $form,
            'realtyId' => $realtyId,
        );
    }

    public function editMapAction() {
        $realtyId = (int) $this->params()->fromRoute(Realty::REALTY_ID);

        $sm = $this->getServiceLocator();
        $realtyTable = $sm->get('realty_table');
        $realty = $realtyTable->getRealtyById($realtyId);

        $form = $sm->get('Realty\Form\RealtyMapForm');

        $form->bind($realty);

        return array(
            'form' => $form,
            'realtyId' => $realtyId,
        );
    }

    public function editFlatAction() {
        $realtyId = (int) $this->params()->fromRoute(Realty::REALTY_ID);

        $sm = $this->getServiceLocator();
        $realtyTable = $sm->get('realty_table');
        $realty = $realtyTable->getRealtyById($realtyId);

        $form = $sm->get('Realty\Form\RealtyFlatForm');

        $form->bind($realty);

        return array(
            'form' => $form,
            'realtyId' => $realtyId,
        );
    }

}