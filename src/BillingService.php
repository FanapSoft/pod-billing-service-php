<?php
/**
 * $baseInfo BaseInfo
 */
namespace Pod\Billing\Service;
use Pod\Base\Service\BaseService;
use Pod\Base\Service\ApiRequestHandler;
use Pod\Base\Service\Exception\PodException;

class BillingService extends BaseService
{
    private $header;
    private static $jsonSchema;
    private static $billingApi;
    private static $serviceProductId;
    private static $baseUri;

    public function __construct($baseInfo)
    {
        parent::__construct();
        self::$jsonSchema = json_decode(file_get_contents(__DIR__ . '/../config/validationSchema.json'), true);
        $this->header = [
            '_token_issuer_'    =>  $baseInfo->getTokenIssuer(),
            '_token_'           => $baseInfo->getToken()
        ];
        self::$billingApi = require __DIR__ . '/../config/apiConfig.php';
        self::$serviceProductId = require __DIR__ . '/../config/serviceProductId.php';
        self::$baseUri = self::$config[self::$serverType];
        self::$serviceProductId = self::$serviceProductId[self::$serverType];

    }

    public function issueInvoice($params) {
        $apiName = 'issueInvoice';
        $header = $this->header;
        array_walk_recursive($params, 'self::prepareData');

        // send ott in header
        $header['_ott_'] = isset($params['ott']) ? $params['ott'] : '';

        $relativeUri = self::$billingApi[$apiName]['subUri'];
        $option = [
            'headers' => $header,
            'query' => $params, // set query param for validation
        ];
        self::validateOption($option, self::$jsonSchema[$apiName], 'query');
        unset($option['query']['ott']); // send ott in header

        // prepare params to send
        $withBracketParams = [];

        if (isset($params['productList'])) {
            foreach ($params['productList'] as $list){
                foreach ($list as $key => $value) {
                    $withBracketParams[$key][] = $value;
                }
            }
            unset($params['productList']);
        }

        # set service call product Id
        $params['scProductId'] = self::$serviceProductId[$apiName];
        $option['withBracketParams'] = $withBracketParams;
        $option['withoutBracketParams'] = $params;
        //  unset `query` key because query string will be build in ApiRequestHandler and will be added to uri so dont need send again in query params
        unset($option['query']);

        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            $relativeUri,
            $option,
            false,
            true
        );
    }

    public function createPreInvoice($params) {
        $apiName = 'createPreInvoice';
        $method = self::$billingApi[$apiName]['method'];
        $paramKey = ($method == 'GET') ? 'query' : 'form_params';

        array_walk_recursive($params, 'self::prepareData');
        if (!isset($params['token'])) {
            $params['token'] = $this->header['_token_'];
        }

        $relativeUri = self::$billingApi[$apiName]['subUri'];

        $option = [
            'headers' => $this->header,
            $paramKey => $params, // set query param for validation
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        // prepare params to send
        $withBracketParams = [];

        if (isset($params['productList'])) {
            foreach ($params['productList'] as $list){
                foreach ($list as $key => $value) {
                    $withBracketParams[$key][] = $value;
                }
            }
            unset($params['productList']);
        }

        # set service call product Id
        $params['scProductId'] = self::$serviceProductId[$apiName];
        $option['withBracketParams'] = $withBracketParams;
        $option['withoutBracketParams'] = $params;
        //  unset `query` key because query string will be build in ApiRequestHandler and will be added to uri so dont need send again in query params
        unset($option[$paramKey]);

        $result = ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            $relativeUri,
            $option,
            false,
            true
        );

        $result = json_decode($result['result']['result'], true);
        if ($result["HasError"]) {
            throw new PodException($result['ErrorMessage'], $result['ErrorCode'], null, $result);
        } else {
            $preInvoiceUri = rtrim(self::$baseUri['PRIVATE-CALL-ADDRESS'], '/'). '/v1/pbc/preinvoice/' . $result['Result'];
            $hashCode = $result['Result'];
            unset($result['Result']);
            $result['Result']['hashCode'] = $hashCode;
            $result['Result']['preInvoiceUri'] = $preInvoiceUri;
            return $result;
        }
    }

    public function getInvoiceList($params) {
        $apiName = 'getInvoiceList';
        $paramKey = self::$billingApi[$apiName]['method'] == 'GET' ? 'query' : 'form_params';
        $relativeUri = self::$billingApi[$apiName]['subUri'];

        array_walk_recursive($params, 'self::prepareData');

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        // prepare params to send
        # set service call product Id
        $params['scProductId'] = self::$serviceProductId[$apiName];
        $option['withBracketParams'] = [];
        $option['withoutBracketParams'] = $params;
        //  unset `query` key because query string will be build in ApiRequestHandler and will be added to uri so dont need send again in query params
        unset($option['query']);

        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            $relativeUri,
            $option,
            false,
            true
        );
    }

    public function payInvoice($params) {
        $apiName = 'payInvoice';
        $optionHasArray = false;
        $method = self::$billingApi[$apiName]['method'];
        $paramKey = ($method == 'GET') ? 'query' : 'form_params';
        array_walk_recursive($params, 'self::prepareData');

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

         self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);

        # prepare params to send
        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceProductId[$apiName];

        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            $optionHasArray = true;
            $method = 'GET';
            unset($option[$paramKey]);
        }

        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            $method,
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray);
    }

    public function getPayInvoiceByWalletLink($params) {
        $apiName = 'getPayInvoiceByWalletLink';

        $option = [
            'headers' => [],
            'query' => $params,
        ];

        $httpQuery = self::buildHttpQuery($params);

         self::validateOption($option, self::$jsonSchema[$apiName]);

        return self::$baseUri['PRIVATE-CALL-ADDRESS'] . self::$billingApi[$apiName]['subUri'] . '?' . $httpQuery;
    }

    public function getPayInvoiceByUniqueNumberLink($params) {
        $apiName = 'getPayInvoiceByUniqueNumberLink';
        $option = [
            'headers' => [],
            'query' => $params,
        ];

        $httpQuery = self::buildHttpQuery($params);

        self::validateOption($option, self::$jsonSchema[$apiName]);
        return self::$baseUri['PRIVATE-CALL-ADDRESS'] . self::$billingApi[$apiName]['subUri'] . '?' . $httpQuery;
    }

    public function sendInvoicePaymentSMS($params) {
        $apiName = 'sendInvoicePaymentSMS';
        array_walk_recursive($params, 'self::prepareData');

        $option = [
            'headers' => $this->header,
            'query' => $params,
        ];

         self::validateOption($option, self::$jsonSchema[$apiName]);

        # prepare params to send
        # set service call product Id
        $params['scProductId'] = self::$serviceProductId[$apiName];
        // prepare params to send
        $option['withBracketParams'] = [];
        $option['withoutBracketParams'] = $params;
        //  unset `query` key because query string will be build in ApiRequestHandler and will be added to uri so dont need send again in query params
        unset($option['query']);
        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            true
        );
    }

    public function getInvoiceListByMetadata($params) {
        $apiName = 'getInvoiceListByMetadata';
        $optionHasArray = false;
        $header = $this->header;

        $method = self::$billingApi[$apiName]['method'];
        $paramKey = ($method == 'GET') ? 'query' : 'form_params';

        array_walk_recursive($params, 'self::prepareData');

        $option = [
            'headers' => $header,
            $paramKey => $params,
        ];
         self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);

        # prepare params to send
        if (isset($params['metaQuery'])) {
            $option[$paramKey]['metaQuery'] = json_encode($params['metaQuery']);
        }
        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceProductId[$apiName];

        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            $optionHasArray = true;
            $method = 'GET';
            unset($option[$paramKey]);
        }

        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            $method,
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );
    }

    public function getInvoiceListAsFile($params) {
        $apiName = 'getInvoiceListAsFile';
        $relativeUri = self::$billingApi[$apiName]['subUri'];

//        $paramKey = self::$billingApi[$apiName]['method'] == 'GET' ? 'query' : 'form_params';

        array_walk_recursive($params, 'self::prepareData');

        $option = [
            'headers' => $this->header,
            'query' => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], 'query');
        // prepare params to send
        # set service call product Id
        $params['scProductId'] = self::$serviceProductId[$apiName];
        $option['withBracketParams'] = [];
        $option['withoutBracketParams'] = $params;
        //  unset `query` key because query string will be build in ApiRequestHandler and will be added to uri so dont need send again in query params
        unset($option['query']);

        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            $relativeUri,
            $option,
            false,
            true
        );
    }

    public function verifyInvoice($params) {
        $apiName = 'verifyInvoice';
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');
        $method = self::$billingApi[$apiName]['method'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceProductId[$apiName];

        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            $optionHasArray = true;
            $method = 'GET';
            unset($option[$paramKey]);
        }
        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            $method,
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );
    }

    public function cancelInvoice($params) {
        $apiName = 'cancelInvoice';
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');

        $method = self::$billingApi[$apiName]['method'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

         self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        # prepare params to send
        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceProductId[$apiName];

        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            $optionHasArray = true;
            $method = 'GET';
            unset($option[$paramKey]);
        }

        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            $method,
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );
    }

    public function reduceInvoice($params) {
        $apiName = 'reduceInvoice';
        array_walk_recursive($params, 'self::prepareData');

        $method = self::$billingApi[$apiName]['method'];
        $paramKey = ($method == 'GET') ? 'query' : 'form_params';

        $relativeUri = self::$billingApi[$apiName]['subUri'];

        $option = [
            'headers' => $this->header,
            $paramKey => $params, // set query param for validation
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);

        // prepare params to send
        $withBracketParams = [];
        if (isset($params['invoiceItemList'])){
            foreach ($params['invoiceItemList'] as $list){
                foreach ($list as $key => $value) {
                    $withBracketParams[$key][] = $value;
                }
            }
            unset($params['invoiceItemList']);
        }

        # set service call product Id
        $params['scProductId'] = self::$serviceProductId[$apiName];
        $option['withBracketParams'] = $withBracketParams;
        $option['withoutBracketParams'] = $params;
        //  unset `query` key because query string will be build in ApiRequestHandler and will be added to uri so dont need send again in query params
        unset($option[$paramKey]);
        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            $relativeUri,
            $option,
            false,
            true
        );
    }

    public function verifyAndCloseInvoice($params) {
        $apiName = 'verifyAndCloseInvoice';
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');

        $method = self::$billingApi[$apiName]['method'];
        $paramKey = ($method == 'GET') ? 'query' : 'form_params';

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        # prepare params to send
        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceProductId[$apiName];

        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            $optionHasArray = true;
            $method = 'GET';
            unset($option[$paramKey]);
        }

        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            $method,
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );
    }

    public function closeInvoice($params) {
        $apiName = 'closeInvoice';
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');

        $option = [
            'headers' => $this->header,
            'query' => $params,
        ];

         self::validateOption($option, self::$jsonSchema[$apiName]);
        # prepare params to send
        # set service call product Id
        $option['query']['scProductId'] = self::$serviceProductId[$apiName];

        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option['query'];
            $optionHasArray = true;
            unset($option['query']);
        }

        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );
    }

    public function getInvoicePaymentLink($params) {
        $apiName = 'getInvoicePaymentLink';
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');
        $redirectUri = !empty($params['redirectUri']) ? 'redirectUri=' . $params['redirectUri'] . '&' : '';
        $callbackUri = !empty($params['callbackUri']) ? 'callbackUri=' . $params['callbackUri'] . '&'  : '';
        $gateway = !empty($params['gateway']) ? 'gateway=' . $params['gateway'] : '';
        unset($params['redirectUri']);
        unset($params['callbackUri']);
        unset($params['gateway']);

        $option = [
            'headers' => $this->header,
            'query' => $params,
        ];
         self::validateOption($option, self::$jsonSchema[$apiName]);
        # prepare params to send
        # set service call product Id
        $option['query']['scProductId'] = self::$serviceProductId[$apiName];

        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option['query'];
            $optionHasArray = true;
            unset($option['query']);
        }

        $result = ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );

        $result['result'] = $result['result']. $redirectUri . $callbackUri . $gateway ;
        return $result;
    }

    public function payInvoiceByInvoice($params) {
        $apiName = 'payInvoiceByInvoice';
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');

        $option = [
            'headers' => $this->header,
            'query' => $params,
        ];

         self::validateOption($option, self::$jsonSchema[$apiName]);
        # prepare params to send
        # set service call product Id
        $option['query']['scProductId'] = self::$serviceProductId[$apiName];
        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option['query'];
            $optionHasArray = true;
            unset($option['query']);
        }

        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );
    }

    public function payInvoiceByCredit($params) {
        $apiName = 'payInvoiceByCredit';
        $header = $this->header;
        array_walk_recursive($params, 'self::prepareData');

        // send ott in header
        $header['_ott_'] = isset($params['ott']) ? $params['ott'] : '';

        $option = [
            'headers' => $header,
            'query' => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName]);
        unset($option['query']['ott']); // send ott in header

        # prepare params to send
        # set service call product Id
        $option['query']['scProductId'] = self::$serviceProductId[$apiName];
        $option['withoutBracketParams'] =  $option['query'];
        unset($option['query']);

        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            true
        );
    }

    public function payAnyInvoiceByCredit($params) {
        $apiName = 'payAnyInvoiceByCredit';
        $header = $this->header;

        array_walk_recursive($params, 'self::prepareData');

        // send ott in header
        $header['_ott_'] = isset($params['ott']) ? $params['ott'] : '';

        $option = [
            'headers' => $header,
            'query' => $params,
        ];

         self::validateOption($option, self::$jsonSchema[$apiName]);
        unset($option['query']['ott']); // send ott in header

        # prepare params to send
        # set service call product Id
        $option['query']['scProductId'] = self::$serviceProductId[$apiName];
        $option['withoutBracketParams'] =  $option['query'];
        unset($option['query']);

        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            true
        );
    }

    public function payInvoiceInFuture($params) {
        $apiName = 'payInvoiceInFuture';
        $optionHasArray = false;
        $header = $this->header;
        array_walk_recursive($params, 'self::prepareData');

        if (isset($params['ott'])) {
            $header['_ott_'] = $params['ott'];
            unset($params['ott']);
        }

        $option = [
            'headers' => $header,
            'query' => $params,
        ];

         self::validateOption($option, self::$jsonSchema[$apiName]);
        # prepare params to send
        # set service call product Id
        $option['query']['scProductId'] = self::$serviceProductId[$apiName];
        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option['query'];
            $optionHasArray = true;
            unset($option['query']);
        }

        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );
    }

    public function getExportList($params) {
        $apiName = 'getExportList';
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');

        $method = self::$billingApi[$apiName]['method'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        # prepare params to send
        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceProductId[$apiName];

        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            $optionHasArray = true;
            $method = 'GET';
            unset($option[$paramKey]);
        }

        $result = ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            $method,
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );

        // add downloadPath to result
        foreach ($result['result'] as $key => $fileInfo){
            if ($fileInfo['statusCode'] === 'EXPORT_SERVICE_STATUS_SUCCESSFUL'){
                $hashCode = $fileInfo['resultFile']['hashCode'];
                $fileId = $fileInfo['resultFile']['id'];
                $result['result'][$key]['downloadPath'] = self::$baseUri['FILE-SERVER-ADDRESS'] ."/nzh/file/?fileId=$fileId&hashCode=$hashCode";
            }
        }
        return $result;
    }

    public function requestWalletSettlement($params) {
        $apiName = 'requestWalletSettlement';
        $optionHasArray = false;
        $header = $this->header;
        array_walk_recursive($params, 'self::prepareData');

        // send ott in header
        $header['_ott_'] = isset($params['ott']) ? $params['ott'] : '';

        $option = [
            'headers' => $header,
            'query' => $params,
        ];

         self::validateOption($option, self::$jsonSchema[$apiName]);
        unset($option['query']['ott']); // send ott in header

        # prepare params to send
        # set service call product Id
        $option['query']['scProductId'] = self::$serviceProductId[$apiName];

        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option['query'];
            $optionHasArray = true;
            unset($option['query']);
        }

        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );
    }

    public function requestGuildSettlement($params) {
        $apiName = 'requestGuildSettlement';
        $optionHasArray = false;
        $header = $this->header;
        array_walk_recursive($params, 'self::prepareData');

        // send ott in header
        $header['_ott_'] = isset($params['ott']) ? $params['ott'] : '';

        $option = [
            'headers' => $header,
            'query' => $params,
        ];
         self::validateOption($option, self::$jsonSchema[$apiName]);
        unset($option['query']['ott']); // send ott in header

        # prepare params to send
        # set service call product Id
        $option['query']['scProductId'] = self::$serviceProductId[$apiName];

        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option['query'];
            $optionHasArray = true;
            unset($option['query']);
        }

        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );
    }

    public function requestSettlementByTool($params) {
        $apiName = 'requestSettlementByTool';
        $optionHasArray = false;
        $header = $this->header;
        array_walk_recursive($params, 'self::prepareData');

        // send ott in header
        $header['_ott_'] = isset($params['ott']) ? $params['ott'] : '';

        $option = [
            'headers' => $header,
            'query' => $params,
        ];

         self::validateOption($option, self::$jsonSchema[$apiName]);
        unset($option['query']['ott']); // send ott in header

        # prepare params to send
        # set service call product Id
        $option['query']['scProductId'] = self::$serviceProductId[$apiName];
        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option['query'];
            $optionHasArray = true;
            unset($option['query']);
        }

        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );
    }

    public function listSettlements($params) {
        $apiName = 'listSettlements';
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');

        $option = [
            'headers' => $this->header,
            'query' => $params,
        ];
         self::validateOption($option, self::$jsonSchema[$apiName]);
        # prepare params to send
        # set service call product Id
        $option['query']['scProductId'] = self::$serviceProductId[$apiName];
        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option['query'];
            $optionHasArray = true;
            unset($option['query']);
        }

        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );
    }

    public function addAutoSettlement($params) {
        $apiName = 'addAutoSettlement';
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');

        $option = [
            'headers' => $this->header,
            'query' => $params,
        ];
         self::validateOption($option, self::$jsonSchema[$apiName]);
        # prepare params to send
        # set service call product Id
        $option['query']['scProductId'] = self::$serviceProductId[$apiName];
        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option['query'];
            $optionHasArray = true;
            unset($option['query']);
        }

        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );
    }

    public function removeAutoSettlement($params) {
        $apiName = 'removeAutoSettlement';
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');

        $option = [
            'headers' => $this->header,
            'query' => $params,
        ];

         self::validateOption($option, self::$jsonSchema[$apiName]);
        # prepare params to send
        # set service call product Id
        $option['query']['scProductId'] = self::$serviceProductId[$apiName];

        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option['query'];
            $optionHasArray = true;
            unset($option['query']);
        }

        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );
    }

# ============================================== MULTI INVOICE APIS ====================================================
# ======================================================================================================================
    public function issueMultiInvoice($params) {
        $apiName = 'issueMultiInvoice';
        $optionHasArray = false;
        $header = $this->header;
        array_walk_recursive($params, 'self::prepareData');
        $method = self::$billingApi[$apiName]['method'];
        $paramKey = ($method == 'GET') ? 'query' : 'form_params';
        $relativeUri = self::$billingApi[$apiName]['subUri'];

        // send ott in header
        $header['_ott_'] = isset($params['ott']) ? $params['ott'] : '';

        $option = [
            'headers' => $header,
            $paramKey => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        unset($option[$paramKey]['ott']); // send ott in header

        $option[$paramKey]['data'] = json_encode($params['data']);

        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceProductId[$apiName];

        if (isset($option[$paramKey]['scVoucherHash']) || isset($option[$paramKey]['delegatorId']) || isset($option[$paramKey]['delegationHash'])) {
            $option['withoutBracketParams'] = $option[$paramKey];
            unset($option[$paramKey]);
            $optionHasArray = true;
            $method = 'GET';
        }

        return  ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            $method,
            $relativeUri,
            $option,
            false,
            $optionHasArray
        );
    }

    public function reduceMultiInvoice($params) {
        $apiName = 'reduceMultiInvoice';
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');
        $method = self::$billingApi[$apiName]['method'];
        $paramKey = ($method == 'GET') ? 'query' : 'form_params';

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        # prepare params to send
        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceProductId[$apiName];
        $option[$paramKey]['data'] = json_encode($params['data']);

        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            $optionHasArray = true;
            $method = 'GET';
            unset($option[$paramKey]);
        }
        return  ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            $method,
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );

    }

    public function reduceMultiInvoiceAndCashOut($params) {
        $apiName = 'reduceMultiInvoiceAndCashOut';
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');
        $method = self::$billingApi[$apiName]['method'];
        $paramKey = ($method == 'GET') ? 'query' : 'form_params';

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        # prepare params to send
        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceProductId[$apiName];
        $option[$paramKey]['data'] = json_encode($params['data']);

        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            $optionHasArray = true;
            $method = 'GET';
            unset($option[$paramKey]);
        }

        return  ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            $method,
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );

    }

# ================================================== VOUCHER  APIS =====================================================
# ======================================================================================================================
    public function defineCreditVoucher($params) {
        $apiName = 'defineCreditVoucher';
        $header = $this->header;
        array_walk_recursive($params, 'self::prepareData');

        $relativeUri = self::$billingApi[$apiName]['subUri'];
        $option = [
            'headers' => $header,
            'query' => $params, // set query param for validation
        ];
        self::validateOption($option, self::$jsonSchema[$apiName], 'query');

        // prepare params to send
        $withBracketParams = [];

        if (isset($params['vouchers'])) {
            foreach ($params['vouchers'] as $list){
                foreach ($list as $key => $value) {
                    $withBracketParams[$key][] = $value;
                }
            }
            unset($params['vouchers']);
        }
        # set service call product Id
        $params['scProductId'] = self::$serviceProductId[$apiName];

        $option['withBracketParams'] = $withBracketParams;
        $option['withoutBracketParams'] = $params;
        //  unset `query` key because query string will be build in ApiRequestHandler and will be added to uri so dont need send again in query params
        unset($option['query']);

        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            $relativeUri,
            $option,
            false,
            true
        );
    }

    public function defineDiscountAmountVoucher($params) {
        $apiName = 'defineDiscountAmountVoucher';
        $header = $this->header;
        array_walk_recursive($params, 'self::prepareData');

        $relativeUri = self::$billingApi[$apiName]['subUri'];
        $option = [
            'headers' => $header,
            'query' => $params, // set query param for validation
        ];
        self::validateOption($option, self::$jsonSchema[$apiName], 'query');

        // prepare params to send
        $withBracketParams = [];

        if (isset($params['vouchers'])) {
            foreach ($params['vouchers'] as $list) {
                foreach ($list as $key => $value) {
                    $withBracketParams[$key][] = $value;
                }
            }
            unset($params['vouchers']);
        }

        if (isset($params['productId'])) {
            $withBracketParams['productId'] = $params['productId'];
            unset($params['productId']);
        }

        if (isset($params['dealerBusinessId'])) {
            $withBracketParams['dealerBusinessId'] = $params['dealerBusinessId'];
            unset($params['dealerBusinessId']);
        }

        # set service call product Id
        $params['scProductId'] = self::$serviceProductId[$apiName];
        $option['withBracketParams'] = $withBracketParams;
        $option['withoutBracketParams'] = $params;
        //  unset `query` key because query string will be build in ApiRequestHandler and will be added to uri so dont need send again in query params
        unset($option['query']);

        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            $relativeUri,
            $option,
            false,
            true
        );
    }

    public function defineDiscountPercentageVoucher($params) {
        $apiName = 'defineDiscountPercentageVoucher';
        $header = $this->header;
        array_walk_recursive($params, 'self::prepareData');

        $relativeUri = self::$billingApi[$apiName]['subUri'];
        $option = [
            'headers' => $header,
            'query' => $params, // set query param for validation
        ];
        self::validateOption($option, self::$jsonSchema[$apiName], 'query');

        // prepare params to send
        $withBracketParams = [];

        if (isset($params['vouchers'])) {
            foreach ($params['vouchers'] as $list) {
                foreach ($list as $key => $value) {
                    $withBracketParams[$key][] = $value;
                }
                if (!isset($list['amount'])) {
                    $withBracketParams['amount'][] = 0;
                }
            }
            unset($params['vouchers']);
        }

        if (isset($params['productId'])) {
            $withBracketParams['productId'] = $params['productId'];
            unset($params['productId']);
        }

        if (isset($params['dealerBusinessId'])) {
            $withBracketParams['dealerBusinessId'] = $params['dealerBusinessId'];
            unset($params['dealerBusinessId']);
        }

        # set service call product Id
        $params['scProductId'] = self::$serviceProductId[$apiName];
        $option['withBracketParams'] = $withBracketParams;
        $option['withoutBracketParams'] = $params;
        //  unset `query` key because query string will be build in ApiRequestHandler and will be added to uri so dont need send again in query params
        unset($option['query']);

        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            $relativeUri,
            $option,
            false,
            true
        );
    }

    public function applyVoucher($params) {
        $apiName = 'applyVoucher';
        $header = $this->header;
        array_walk_recursive($params, 'self::prepareData');
//        $paramKey = self::$billingApi[$apiName]['method'] == 'GET' ? 'query' : 'form_params';

        // send ott in header
        $header['_ott_'] = isset($params['ott']) ? $params['ott'] : '';

        $option = [
            'headers' => $header,
            'query' => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], 'query');
        unset($option['query']['ott']); // send ott in header

        # set service call product Id
        $params['scProductId'] = self::$serviceProductId[$apiName];
        $option['withoutBracketParams'] = $params;
        //  unset `query` key because query string will be build in ApiRequestHandler and will be added to uri so dont need send again in query params
        unset($option['query']);
        return  ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            true
        );
    }

    public function getVoucherList($params) {
        $apiName = 'getVoucherList';
        $header = $this->header;
        array_walk_recursive($params, 'self::prepareData');

        $relativeUri = self::$billingApi[$apiName]['subUri'];
        $option = [
            'headers' => $header,
            'query' => $params, // set query param for validation
        ];
        self::validateOption($option, self::$jsonSchema[$apiName], 'query');

        // prepare params to send
        $withBracketParams = [];

        if (isset($params['productId'])) {
            $withBracketParams['productId'] = $params['productId'];
            unset($params['productId']);
        }

        if (isset($params['guildCode'])) {
            $withBracketParams['guildCode'] = $params['guildCode'];
            unset($params['guildCode']);
        }

        # set service call product Id
        $params['scProductId'] = self::$serviceProductId[$apiName];
        $option['withBracketParams'] = $withBracketParams;
        $option['withoutBracketParams'] = $params;
        //  unset `query` key because query string will be build in ApiRequestHandler and will be added to uri so dont need send again in query params
        unset($option['query']);

        return ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            $relativeUri,
            $option,
            false,
            true
        );
    }

    public function deactivateVoucher($params) {
        $apiName = 'deactivateVoucher';
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');
        $method = self::$billingApi[$apiName]['method'];
        $paramKey = ($method == 'GET') ? 'query' : 'form_params';
        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        # prepare params to send
        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceProductId[$apiName];
        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            $optionHasArray = true;
            $method = 'GET';
            unset($option[$paramKey]);
        }
        return  ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            $method,
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );
    }

    public function activateVoucher($params) {
        $apiName = 'activateVoucher';
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');
        $method = self::$billingApi[$apiName]['method'];
        $paramKey = ($method == 'GET') ? 'query' : 'form_params';
        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        # prepare params to send
        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceProductId[$apiName];
        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            $optionHasArray = true;
            $method = 'GET';
            unset($option[$paramKey]);
        }
        return  ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            $method,
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );
    }

    public function defineDirectWithdraw($params) {
        $apiName = 'defineDirectWithdraw';
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');
        $method = self::$billingApi[$apiName]['method'];
        $paramKey = ($method == 'GET') ? 'query' : 'form_params';
        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        # prepare params to send
        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceProductId[$apiName];
        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            $optionHasArray = true;
            $method = 'GET';
            unset($option[$paramKey]);
        }
        return  ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            $method,
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );
    }

    public function directWithdrawList($params) {
        $apiName = 'directWithdrawList';
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');
        $method = self::$billingApi[$apiName]['method'];
        $paramKey = ($method == 'GET') ? 'query' : 'form_params';
        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        # prepare params to send
        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceProductId[$apiName];
        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            $optionHasArray = true;
            $method = 'GET';
            unset($option[$paramKey]);
        }
        return  ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            $method,
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );
    }

    public function updateDirectWithdraw($params) {
        $apiName = 'updateDirectWithdraw';
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');
        $method = self::$billingApi[$apiName]['method'];
        $paramKey = ($method == 'GET') ? 'query' : 'form_params';
        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        # prepare params to send
        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceProductId[$apiName];
        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            $optionHasArray = true;
            $method = 'GET';
            unset($option[$paramKey]);
        }
        return  ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            $method,
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );
    }

    public function revokeDirectWithdraw($params) {
        $apiName = 'revokeDirectWithdraw';
        $optionHasArray = false;
        array_walk_recursive($params, 'self::prepareData');
        $method = self::$billingApi[$apiName]['method'];
        $paramKey = ($method == 'GET') ? 'query' : 'form_params';
        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        # prepare params to send
        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceProductId[$apiName];
        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            $optionHasArray = true;
            $method = 'GET';
            unset($option[$paramKey]);
        }
        return  ApiRequestHandler::Request(
            self::$baseUri[self::$billingApi[$apiName]['baseUri']],
            $method,
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            $optionHasArray
        );
    }
}