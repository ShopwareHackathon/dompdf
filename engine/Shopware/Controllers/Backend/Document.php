<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */
use Shopware\Components\Document\Order;

/**
 * Shopware document / pdf controller
 */
class Shopware_Controllers_Backend_Document extends Enlight_Controller_Action
{
    /**
     * Generate pdf invoice
     * @access public
     */
    public function indexAction()
    {
        if ((bool)$this->Request()->useLegacyTemplate) {
            $this->createLegacyDocument($this->Request()->preview);
        } else {
            // Init document component
            $documentTypeId = $this->Request()->typ;
            /** @var Shopware\Components\Document\Order $document */
            $document = $this->get('document_factory')->createOrderInstance($documentTypeId);
            $documentType = $document->getDocumentType();
            $document->setTemplate('documents/' . $documentType->getTemplate());
            $document->loadElements();

            // TODO: Set the sample data
            $this->setPreviewDemoData($document);
            // Render and respond with document
            $document->renderPDF();
            $document->respondWithPDF($documentType->getName() . '.pdf');
        }
    }

    /**
     * Duplicate document properties
     */
    public function duplicatePropertiesAction()
    {
        $this->View()->setTemplate();
        $id = $this->Request()->id;

        // Update statement
        $getDocumentTypes = Shopware()->Db()->fetchAll(
            "SELECT DISTINCT id FROM s_core_documents WHERE id != ?",
            array($id)
        );
        foreach ($getDocumentTypes as $targetID) {
            $deleteOldRows = Shopware()->Db()->query(
                "DELETE FROM s_core_documents_box WHERE documentID = ?",
                array($targetID["id"])
            );
            $sqlDuplicate = "INSERT IGNORE INTO s_core_documents_box
                SELECT NULL AS id, ? AS documentID , name, style, value
                FROM s_core_documents_box WHERE `documentID` = ?;
            ";
            Shopware()->Db()->query($sqlDuplicate, array($targetID["id"], $id));
        }
    }

    /**
     * Creates and responds a document using the legacy document component.
     *
     * @param boolean $useSampleData
     */
    private function createLegacyDocument($useSampleData = true) {
        $document = Shopware_Components_Document::initDocument(
            $this->Request()->id,
            $this->Request()->typ,
            array(
                "netto" => ($this->Request()->ust_free != "false"),
                "bid" => $this->Request()->bid,
                "voucher" => $this->Request()->voucher,
                "date" => $this->Request()->date,
                "delivery_date" => $this->Request()->delivery_date,
                "shippingCostsAsPosition" => true,
                "_renderer" => "pdf",
                "_preview" => $this->Request()->preview,
                "_previewForcePagebreak" => $this->Request()->pagebreak,
                "_previewSample" => $useSampleData,
                "_compatibilityMode" => $this->Request()->compatibilityMode,
                "docComment" => utf8_decode($this->Request()->docComment),
                "forceTaxCheck" => $this->Request()->forceTaxCheck
            )
        );
        $this->View()->setTemplate();
        $document->render();
    }

    /**
     * @param Order $document
     * @return Order
     */
    private function setPreviewDemoData($document)
    {
        $document->setCustomerNumber(12345);
        $document->setOrderNumber('10000');
        $document->setContentInfo('This is a  test content info just for the preview of your template.');
        $document->setOrderAmount(299.99);
        $document->setOrderAmountNet(202.33);
        $document->setCustomerComment('This is the customer comment.');
        $document->setDocumentDate(new DateTime());
        $document->setDocumentNumber('9999999');

        $document->setReceiverAddress([
            'company' => 'Demo GmbH',
            'firstName' => 'Max',
            'lastName' => 'Exampleman',
            'street' => 'Examplestreet 666',
            'zipCode' => '48565',
            'city' => 'dompdf City'
        ]);

        $document->setItems(
            [
                [
                    'articleName' => 'Test product',
                    'amount' => 150,
                    'amountNet' => 130,
                    'price' => 25,
                    'quantity' => 6,
                    'articleNumber' => 'SW10002',
                    'tax' => 20
                ],
                [
                    'articleName' => 'Football',
                    'amount' => 20.99,
                    'amountNet' => 10,
                    'quantity' => 1,
                    'price' => 20.99,
                    'articleNumber' => 'SW10002',
                    'tax' => 15
                ],
                [
                    'articleName' => 'Discount',
                    'amount' => -10,
                    'amountNet' => -10,
                    'price' => -10,
                    'quantity' => 1,
                    'articleNumber' => 'SW10003',
                    'tax' => 0
                ]
            ],
            false
        );
        return $document;
    }

}
