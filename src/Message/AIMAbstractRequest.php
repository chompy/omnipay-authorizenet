<?php

namespace Omnipay\AuthorizeNet\Message;

use Omnipay\AuthorizeNet\Model\CardReference;
use Omnipay\AuthorizeNet\Model\TransactionReference;
use Omnipay\AuthorizeNet\APICharacterLimitReference;
use Omnipay\Common\CreditCard;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;

/**
 * Authorize.Net AIM Abstract Request
 */
abstract class AIMAbstractRequest extends AbstractRequest
{
    protected $requestType = 'createTransactionRequest';
    protected $action = null;

    public function getApiLoginId()
    {
        return $this->getParameter('apiLoginId');
    }

    public function setApiLoginId($value)
    {
        return $this->setParameter('apiLoginId', $value);
    }

    public function getTransactionKey()
    {
        return $this->getParameter('transactionKey');
    }

    public function setTransactionKey($value)
    {
        return $this->setParameter('transactionKey', $value);
    }

    public function getDeveloperMode()
    {
        return $this->getParameter('developerMode');
    }

    public function setDeveloperMode($value)
    {
        return $this->setParameter('developerMode', $value);
    }

    public function getCustomerId()
    {
        return $this->getParameter('customerId');
    }

    public function setCustomerId($value)
    {
        return $this->setParameter('customerId', $value);
    }

    public function getHashSecret()
    {
        return $this->getParameter('hashSecret');
    }

    public function setHashSecret($value)
    {
        return $this->setParameter('hashSecret', $value);
    }

    public function setDuplicateWindow($value)
    {
        $this->setParameter('duplicateWindow', $value);
    }

    private function getDuplicateWindow()
    {
        return $this->getParameter('duplicateWindow');
    }

    public function getLiveEndpoint()
    {
        return $this->getParameter('liveEndpoint');
    }

    public function setLiveEndpoint($value)
    {
        return $this->setParameter('liveEndpoint', $value);
    }

    public function getDeveloperEndpoint()
    {
        return $this->getParameter('developerEndpoint');
    }

    public function setDeveloperEndpoint($value)
    {
        return $this->setParameter('developerEndpoint', $value);
    }

    public function getEndpoint()
    {
        return $this->getDeveloperMode() ? $this->getDeveloperEndpoint() : $this->getLiveEndpoint();
    }

    public function getSolutionId()
    {
        return $this->getParameter('solutionId');
    }

    public function setSolutionId($value)
    {
        return $this->setParameter('solutionId', $value);
    }

    public function getAuthCode()
    {
        return $this->getParameter('authCode');
    }

    public function setAuthCode($value)
    {
        return $this->setParameter('authCode', $value);
    }

    /**
     * @return TransactionReference
     */
    public function getTransactionReference()
    {
        return $this->getParameter('transactionReference');
    }

    public function setTransactionReference($value)
    {
        if (substr($value, 0, 1) === '{') {
            // Value is a complex key containing the transaction ID and other properties
            $transactionRef = new TransactionReference($value);
        } else {
            // Value just contains the transaction ID
            $transactionRef = new TransactionReference();
            $transactionRef->setTransId($value);
        }

        return $this->setParameter('transactionReference', $transactionRef);
    }

    /**
     * @param string|CardReference $value
     * @return AbstractRequest
     */
    public function setCardReference($value)
    {
        if (!($value instanceof CardReference)) {
            $value = new CardReference($value);
        }

        return parent::setCardReference($value);
    }

    /**
     * @param bool $serialize Determines whether the return value will be a string or object
     * @return string|CardReference
     */
    public function getCardReference($serialize = true)
    {
        $value = parent::getCardReference();

        if ($serialize) {
            $value = (string)$value;
        }

        return $value;
    }

    public function getInvoiceNumber()
    {
        return $this->getParameter('invoiceNumber');
    }

    public function setInvoiceNumber($value)
    {
        return $this->setParameter('invoiceNumber', $value);
    }

    /**
     * @link http://developer.authorize.net/api/reference/features/acceptjs.html Documentation on opaque data
     * @return string
     */
    public function getOpaqueDataDescriptor()
    {
        return $this->getParameter('opaqueDataDescriptor');
    }

    /**
     * @link http://developer.authorize.net/api/reference/features/acceptjs.html Documentation on opaque data
     * @return string
     */
    public function getOpaqueDataValue()
    {
        return $this->getParameter('opaqueDataValue');
    }

    /**
     * @link http://developer.authorize.net/api/reference/features/acceptjs.html Documentation on opaque data
     * @param string
     * @return string
     */
    public function setOpaqueDataDescriptor($value)
    {
        return $this->setParameter('opaqueDataDescriptor', $value);
    }

    /**
     * @link http://developer.authorize.net/api/reference/features/acceptjs.html Documentation on opaque data
     * @param string
     * @return string
     */
    public function setOpaqueDataValue($value)
    {
        return $this->setParameter('opaqueDataValue', $value);
    }

    public function sendData($data)
    {
        $headers = array('Content-Type' => 'text/xml; charset=utf-8');

        $data = $data->saveXml();
        $httpResponse = $this->httpClient->post($this->getEndpoint(), $headers, $data)->send();

        return $this->response = new AIMResponse($this, $httpResponse->getBody());
    }

    /**
     * @return mixed|\SimpleXMLElement
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getBaseData()
    {
        $data = new \SimpleXMLElement('<' . $this->requestType . '/>');

        $data->addAttribute('xmlns', 'AnetApi/xml/v1/schema/AnetApiSchema.xsd');
        $this->addAuthentication($data);
        $this->addReferenceId($data);
        $this->addTransactionType($data);

        return $data;
    }

    protected function addAuthentication(\SimpleXMLElement $data)
    {
        $data->merchantAuthentication->name = $this->getApiLoginId();
        $data->merchantAuthentication->transactionKey = $this->getTransactionKey();
    }

    protected function addReferenceId(\SimpleXMLElement $data)
    {
        $txnId = $this->getTransactionId();

        if (!empty($txnId)) {
            $data->refId = $this->getTransactionId();
        }
    }

    protected function addTransactionType(\SimpleXMLElement $data)
    {
        if (!$this->action) {
            // The extending class probably hasn't specified an "action"
            throw new InvalidRequestException();
        }

        $data->transactionRequest->transactionType = $this->action;
    }

    protected function addSolutionId(\SimpleXMLElement $data)
    {
        $solutionId = $this->getSolutionId();

        if (!empty($solutionId)) {
            $data->transactionRequest->solution->id = $solutionId;
        }
    }

    /**
     * Adds billing data to a partially filled request data object.
     *
     * @param \SimpleXMLElement $data
     *
     * @return \SimpleXMLElement
     */
    protected function addBillingData(\SimpleXMLElement $data)
    {
        /** @var mixed $req */
        $req = $data->transactionRequest;

        // The order must come before the customer ID.
        $req->order->invoiceNumber = $this->getInvoiceNumber();
        $req->order->description = $this->getDescription();

        // Merchant assigned customer ID
        $customer = $this->getCustomerId();
        if (!empty($customer)) {
            $req->customer->id = $customer;
        }

        //$req->order->description = $this->getDescription();

        /** @var CreditCard $card */
        if ($card = $this->getCard()) {
            // A card is present, so include billing and shipping details
            $req->customer->email = $card->getEmail();

            $req->billTo->firstName = substr($card->getBillingFirstName(), 0, 50);
            $req->billTo->lastName = substr($card->getBillingLastName(), 0, 50);
            $req->billTo->company = substr($card->getBillingCompany(), 0, 50);
            $req->billTo->address = substr(
                trim($card->getBillingAddress1() . " \n" . $card->getBillingAddress2()),
                0,
                60
            );
            $req->billTo->city = substr($card->getBillingCity(), 0, 40);
            $req->billTo->state = substr($card->getBillingState(), 0, 40);
            $req->billTo->zip = substr($card->getBillingPostcode(), 0, 20);
            $req->billTo->country = substr($card->getBillingCountry(), 0, 60);
            $req->billTo->phoneNumber = substr($card->getBillingPhone(), 0, 25);

            APICharacterLimitReference::truncateFields($req->billTo);

            $req->shipTo->firstName = substr($card->getShippingFirstName(), 0, 50);
            $req->shipTo->lastName = substr($card->getShippingLastName(), 0, 50);
            $req->shipTo->company = substr($card->getShippingCompany(), 0, 50);
            $req->shipTo->address = substr(
                trim($card->getShippingAddress1() . " \n" . $card->getShippingAddress2()),
                0,
                60
            );
            $req->shipTo->city = substr($card->getShippingCity(), 0, 40);
            $req->shipTo->state = substr($card->getShippingState(), 0, 40);
            $req->shipTo->zip = substr($card->getShippingPostcode(), 0, 20);
            $req->shipTo->country = substr($card->getShippingCountry(), 0, 60);

            APICharacterLimitReference::truncateFields($req->shipTo);

        }

        return $data;
    }

    protected function addTransactionSettings(\SimpleXMLElement $data)
    {
        $i = 0;

        // The test mode setting indicates whether or not this is a live request or a test request
        $data->transactionRequest->transactionSettings->setting[$i]->settingName = 'testRequest';
        $data->transactionRequest->transactionSettings->setting[$i]->settingValue = $this->getTestMode()
            ? 'true'
            : 'false';

        // The duplicate window setting specifies the threshold for AuthorizeNet's duplicate transaction detection logic
        if (!is_null($this->getDuplicateWindow())) {
            $i++;
            $data->transactionRequest->transactionSettings->setting[$i]->settingName = 'duplicateWindow';
            $data->transactionRequest->transactionSettings->setting[$i]->settingValue = $this->getDuplicateWindow();
        }

        return $data;
    }
}
