<?php

namespace Bpost\BpostApiClient;

use Bpost\BpostApiClient\ApiCaller\ApiCaller;
use Bpost\BpostApiClient\Bpost\HttpRequestBuilder\CreateLabelForBoxBuilder;
use Bpost\BpostApiClient\Bpost\HttpRequestBuilder\CreateLabelForOrderBuilder;
use Bpost\BpostApiClient\Bpost\HttpRequestBuilder\CreateLabelInBulkForOrdersBuilder;
use Bpost\BpostApiClient\Bpost\HttpRequestBuilder\CreateOrReplaceOrderBuilder;
use Bpost\BpostApiClient\Bpost\HttpRequestBuilder\FetchOrderBuilder;
use Bpost\BpostApiClient\Bpost\HttpRequestBuilder\FetchProductConfigBuilder;
use Bpost\BpostApiClient\Bpost\HttpRequestBuilder\HttpRequestBuilderInterface;
use Bpost\BpostApiClient\Bpost\HttpRequestBuilder\ModifyOrderBuilder;
use Bpost\BpostApiClient\Bpost\Labels;
use Bpost\BpostApiClient\Bpost\Order;
use Bpost\BpostApiClient\Bpost\Order\Box;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\Insured;
use Bpost\BpostApiClient\Bpost\ProductConfiguration;
use Bpost\BpostApiClient\Common\ValidatedValue\LabelFormat;
use Bpost\BpostApiClient\Exception\BpostApiResponseException\BpostCurlException;
use Bpost\BpostApiClient\Exception\BpostApiResponseException\BpostInvalidResponseException;
use Bpost\BpostApiClient\Exception\BpostApiResponseException\BpostInvalidSelectionException;
use Bpost\BpostApiClient\Exception\BpostApiResponseException\BpostInvalidXmlResponseException;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidValueException;
use Bpost\BpostApiClient\Exception\BpostNotImplementedException;
use Bpost\BpostApiClient\Exception\XmlException\BpostXmlInvalidItemException;
use Bpost\BpostApiClient\Exception\XmlException\BpostXmlNoReferenceFoundException;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;

/**
 * Bpost class
 *
 * @author    Tijs Verkoyen <php-bpost@verkoyen.eu>
 *
 * @version   3.0.0
 *
 * @copyright Copyright (c), Tijs Verkoyen. All rights reserved.
 * @license   BSD License
 */
class Bpost
{
    const LABEL_FORMAT_A4 = 'A4';
    const LABEL_FORMAT_A6 = 'A6';

    const NS_V3_GLOBAL = 'http://schema.post.be/shm/deepintegration/v3/';
    const NS_V3_COMMON = 'http://schema.post.be/shm/deepintegration/v3/common';
    const NS_V3_NATIONAL = 'http://schema.post.be/shm/deepintegration/v3/national';
    const NS_V3_INTERNATIONAL = 'http://schema.post.be/shm/deepintegration/v3/international';
    const NS_V5_GLOBAL = 'http://schema.post.be/shm/deepintegration/v5/';
    const NS_V5_COMMON = 'http://schema.post.be/shm/deepintegration/v5/common';
    const NS_V5_NATIONAL = 'http://schema.post.be/shm/deepintegration/v5/national';
    const NS_V5_INTERNATIONAL = 'http://schema.post.be/shm/deepintegration/v5/international';

    // URL for the api
    const API_URL = 'https://shm-rest.bpost.cloud/services/shm';

    // current version
    const VERSION = '3.7.0';

    /** Min weight, in grams, for a shipping */
    const MIN_WEIGHT = 0;

    /** Max weight, in grams, for a shipping */
    const MAX_WEIGHT = 30000;

    /** @var ApiCaller */
    private $apiCaller;

    /**
     * The account id
     *
     * @var string
     */
    private $accountId;

    /**
     * A cURL instance
     *
     * @var resource
     */
    private $curl;

    /**
     * The passPhrase
     *
     * @var string
     */
    private $passPhrase;

    /**
     * The port to use.
     *
     * @var int
     */
    private $port;

    /**
     * The timeout
     *
     * @var int
     */
    private $timeOut = 30;

    /**
     * The user agent
     *
     * @var string
     */
    private $userAgent;

    private $apiUrl;

    /** @var Logger */
    private $logger;

    /**
     * Create Bpost instance
     *
     * @param string $accountId
     * @param string $passPhrase
     * @param string $apiUrl
     */
    public function __construct($accountId, $passPhrase, $apiUrl = self::API_URL)
    {
        $this->accountId = (string) $accountId;
        $this->passPhrase = (string) $passPhrase;
        $this->apiUrl = (string) $apiUrl;
        $this->logger = new Logger();
    }

    /**
     * @return ApiCaller
     */
    public function getApiCaller()
    {
        if ($this->apiCaller === null) {
            $this->apiCaller = new ApiCaller($this->logger);
        }

        return $this->apiCaller;
    }

    /**
     * @param ApiCaller $apiCaller
     */
    public function setApiCaller(ApiCaller $apiCaller)
    {
        $this->apiCaller = $apiCaller;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        if ($this->curl !== null) {
            curl_close($this->curl);
            $this->curl = null;
        }
    }

    /**
     * Decode the response
     *
     * @param SimpleXMLElement $item   The item to decode.
     * @param array            $return Just a placeholder.
     * @param int              $i      A internal counter.
     *
     * @return array
     *
     * @throws BpostXmlInvalidItemException
     */
    private static function decodeResponse($item, $return = null, $i = 0)
    {
        if (!$item instanceof SimpleXMLElement) {
            throw new BpostXmlInvalidItemException();
        }

        $arrayKeys = array(
            'barcode',
            'orderLine',
            Insured::INSURANCE_TYPE_ADDITIONAL_INSURANCE,
            Box\Option\Messaging::MESSAGING_TYPE_INFO_DISTRIBUTED,
            'infoPugo',
        );
        $integerKeys = array('totalPrice');

        /** @var SimpleXMLElement $value */
        foreach ($item as $key => $value) {
            $attributes = (array) $value->attributes();

            if (!empty($attributes) && isset($attributes['@attributes'])) {
                $return[$key]['@attributes'] = $attributes['@attributes'];
            }

            // empty
            if (isset($value['nil']) && (string) $value['nil'] === 'true') {
                $return[$key] = null;
            } // empty
            elseif (isset($value[0]) && (string) $value == '') {
                if (in_array($key, $arrayKeys)) {
                    $return[$key][] = self::decodeResponse($value);
                } else {
                    $return[$key] = self::decodeResponse($value, null, 1);
                }
            } else {
                // arrays
                if (in_array($key, $arrayKeys)) {
                    $return[$key][] = (string) $value;
                } // booleans
                elseif ((string) $value == 'true') {
                    $return[$key] = true;
                } elseif ((string) $value == 'false') {
                    $return[$key] = false;
                } // integers
                elseif (in_array($key, $integerKeys)) {
                    $return[$key] = (int) $value;
                } // fallback to string
                else {
                    $return[$key] = (string) $value;
                }
            }
        }

        return $return;
    }

    /**
     * Make the call
     *
     * @return string|SimpleXMLElement
     *
     * @throws BpostCurlException
     * @throws BpostInvalidResponseException
     * @throws BpostInvalidSelectionException
     * @throws BpostInvalidXmlResponseException
     */
    private function doCall(HttpRequestBuilderInterface $builder)
    {
        $headers = $builder->getHeaders();

        // build Authorization header
        $headers[] = 'Authorization: Basic ' . $this->getAuthorizationHeader();

        // set options
        $options = array();
        $options[CURLOPT_URL] = $this->apiUrl . '/' . $this->accountId . $builder->getUrl();
        if ($this->getPort() != 0) {
            $options[CURLOPT_PORT] = $this->getPort();
        }
        $options[CURLOPT_USERAGENT] = $this->getUserAgent();
        $options[CURLOPT_RETURNTRANSFER] = true;
        $options[CURLOPT_TIMEOUT] = (int) $this->getTimeOut();
        $options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;
        $options[CURLOPT_HTTPHEADER] = $headers;

        if ($builder->getMethod() == 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $builder->getXml();
        }

        $this->getApiCaller()->doCall($options);

        $response = $this->getApiCaller()->getResponseBody();
        $httpCode = $this->getApiCaller()->getResponseHttpCode();
        $contentType = $this->getApiCaller()->getResponseContentType();

        // valid HTTP-code
        if (!in_array($httpCode, array(0, 200, 201))) {
            // convert into XML
            $xml = @simplexml_load_string($response);

            // validate
            if ($xml !== false && (substr($xml->getName(), 0, 7) == 'invalid')
            ) {
                // message
                $message = (string) $xml->error;
                $code = isset($xml->code) ? (int) $xml->code : 0;

                // throw exception
                throw new BpostInvalidSelectionException($message, $code);
            }

            $message = '';
            if (
                ($contentType !== null && substr_count($contentType, 'text/plain') > 0) ||
                in_array($httpCode, array(400, 404))
            ) {
                $message = $response;
            }

            throw new BpostInvalidResponseException($message, $httpCode);
        }

        // if we don't expect XML we can return the content here
        if (!$builder->isExpectXml()) {
            return $response;
        }

        // convert into XML
        $xml = @simplexml_load_string($response);
        if ($xml === false) {
            throw new BpostInvalidXmlResponseException();
        }

        // return the response
        return $xml;
    }

    /**
     * Get the account id
     *
     * @return string
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * Generate the secret string for the Authorization header
     *
     * @return string
     */
    private function getAuthorizationHeader()
    {
        return base64_encode($this->accountId . ':' . $this->passPhrase);
    }

    /**
     * Get the passPhrase
     *
     * @return string
     */
    public function getPassPhrase()
    {
        return $this->passPhrase;
    }

    /**
     * Get the port
     *
     * @return int
     */
    public function getPort()
    {
        return (int) $this->port;
    }

    /**
     * Get the timeout that will be used
     *
     * @return int
     */
    public function getTimeOut()
    {
        return (int) $this->timeOut;
    }

    /**
     * Get the useragent that will be used.
     * Our version will be prepended to yours.
     * It will look like: "PHP Bpost/<version> <your-user-agent>"
     *
     * @return string
     */
    public function getUserAgent()
    {
        return (string) 'PHP Bpost/' . self::VERSION . ' ' . $this->userAgent;
    }

    /**
     * Set the timeout
     * After this time the request will stop. You should handle any errors triggered by this.
     *
     * @param int $seconds The timeout in seconds.
     */
    public function setTimeOut($seconds)
    {
        $this->timeOut = (int) $seconds;
    }

    /**
     * Set the user-agent for you application
     * It will be appended to ours, the result will look like: "PHP Bpost/<version> <your-user-agent>"
     *
     * @param string $userAgent Your user-agent, it should look like <app-name>/<app-version>.
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = (string) $userAgent;
    }

    // webservice methods
    // orders
    /**
     * Creates a new order. If an order with the same orderReference already exists
     *
     * @param Order $order
     *
     * @return bool
     *
     * @throws BpostCurlException
     * @throws BpostInvalidResponseException
     * @throws BpostInvalidSelectionException
     * @throws BpostInvalidXmlResponseException
     */
    public function createOrReplaceOrder(Order $order)
    {
        $builder = new CreateOrReplaceOrderBuilder($order, $this->accountId);

        return $this->doCall($builder) == '';
    }

    /**
     * Fetch an order
     *
     * @param string $reference
     *
     * @return Order
     *
     * @throws BpostCurlException
     * @throws BpostInvalidResponseException
     * @throws BpostInvalidSelectionException
     * @throws BpostInvalidXmlResponseException
     * @throws BpostNotImplementedException
     * @throws BpostXmlNoReferenceFoundException
     */
    public function fetchOrder($reference)
    {
        $builder = new FetchOrderBuilder($reference);

        $xml = $this->doCall($builder);

        return Order::createFromXML($xml);
    }

    /**
     * Get the products configuration
     *
     * @return ProductConfiguration
     *
     * @throws BpostCurlException
     * @throws BpostInvalidResponseException
     * @throws BpostInvalidSelectionException
     * @throws BpostInvalidXmlResponseException
     */
    public function fetchProductConfig()
    {
        $builder = new FetchProductConfigBuilder();

        $xml = $this->doCall($builder);

        return ProductConfiguration::createFromXML($xml);
    }

    /**
     * Modify the status for an order.
     *
     * @param string $reference The reference for an order
     * @param string $status    The new status, allowed values are: OPEN, PENDING, CANCELLED, COMPLETED, ON-HOLD or PRINTED
     *
     * @return bool
     *
     * @throws BpostCurlException
     * @throws BpostInvalidResponseException
     * @throws BpostInvalidSelectionException
     * @throws BpostInvalidValueException
     * @throws BpostInvalidXmlResponseException
     */
    public function modifyOrderStatus($reference, $status)
    {
        $builder = new ModifyOrderBuilder($reference, $status);

        return $this->doCall($builder) == '';
    }

    // labels

    /**
     * Get the possible label formats
     *
     * @return array
     */
    public static function getPossibleLabelFormatValues()
    {
        return array(
            self::LABEL_FORMAT_A4,
            self::LABEL_FORMAT_A6,
        );
    }

    /**
     * Create the labels for all unprinted boxes in an order.
     * The service will return labels for all unprinted boxes for that order.
     * Boxes that were unprinted will get the status PRINTED, the boxes that
     * had already been printed will remain the same.
     *
     * @param string $reference        The reference for an order
     * @param string $format           The desired format, allowed values are: A4, A6
     * @param bool   $withReturnLabels Should return labels be returned?
     * @param bool   $asPdf            Should we retrieve the PDF-version instead of PNG
     *
     * @return Bpost\Label[]
     *
     * @throws BpostCurlException
     * @throws BpostInvalidResponseException
     * @throws BpostInvalidSelectionException
     * @throws BpostInvalidXmlResponseException
     */
    public function createLabelForOrder(
        $reference,
        $format = self::LABEL_FORMAT_A6,
        $withReturnLabels = false,
        $asPdf = false
    ) {
        $builder = new CreateLabelForOrderBuilder($reference, new LabelFormat($format), $asPdf, $withReturnLabels);

        $xml = $this->doCall($builder);

        return Labels::createFromXML($xml);
    }

    /**
     * Create a label for a known barcode.
     *
     * @param string $barcode          The barcode of the parcel
     * @param string $format           The desired format, allowed values are: A4, A6
     * @param bool   $withReturnLabels Should return labels be returned?
     * @param bool   $asPdf            Should we retrieve the PDF-version instead of PNG
     *
     * @return Bpost\Label[]
     *
     * @throws BpostCurlException
     * @throws BpostInvalidResponseException
     * @throws BpostInvalidSelectionException
     * @throws BpostInvalidXmlResponseException
     */
    public function createLabelForBox(
        $barcode,
        $format = self::LABEL_FORMAT_A6,
        $withReturnLabels = false,
        $asPdf = false
    ) {
        $builder = new CreateLabelForBoxBuilder($barcode, new LabelFormat($format), $asPdf, $withReturnLabels);

        $xml = $this->doCall($builder);

        return Labels::createFromXML($xml);
    }

    /**
     * Create labels in bulk, according to the list of order references and the
     * list of barcodes. When there is an order reference specified in the
     * request, the service will return a label of every box of that order. If
     * a certain box was not yet printed, it will have the status PRINTED
     *
     * @param array  $references       The references for the order
     * @param string $format           The desired format, allowed values are: A4, A6
     * @param bool   $withReturnLabels Should return labels be returned?
     * @param bool   $asPdf            Should we retrieve the PDF-version instead of PNG
     * @param bool   $forcePrinting    Reprint a already printed label
     *
     * @return Bpost\Label[]
     *
     * @throws BpostCurlException
     * @throws BpostInvalidResponseException
     * @throws BpostInvalidSelectionException
     * @throws BpostInvalidXmlResponseException
     */
    public function createLabelInBulkForOrders(
        array $references,
        $format = LabelFormat::FORMAT_A6,
        $withReturnLabels = false,
        $asPdf = false,
        $forcePrinting = false
    ) {
        $builder = new CreateLabelInBulkForOrdersBuilder(
            $references,
            new LabelFormat($format),
            $asPdf,
            $withReturnLabels,
            $forcePrinting
        );

        $xml = $this->doCall($builder);

        return Labels::createFromXML($xml);
    }

    /**
     * Set a logger to permit to the plugin to log events
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger->setLogger($logger);
    }

    /**
     * @param int $weight in grams
     *
     * @return bool
     */
    public function isValidWeight($weight)
    {
        return self::MIN_WEIGHT <= $weight && $weight <= self::MAX_WEIGHT;
    }
}
