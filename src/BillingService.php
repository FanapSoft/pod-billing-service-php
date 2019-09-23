<?php
/**
 * Created by PhpStorm.
 * User :  keshtgar
 * Date :  6/19/19
 * Time : 12:29 PM
 *
 * $baseInfo BaseInfo
 */
namespace Pod\Billing\Service;

use Pod\Base\Service\BaseService;
use Pod\Base\Service\ApiRequestHandler;


class BillingService extends BaseService
{
    private $header;
    private static $billingApi;

    public function __construct($baseInfo)
    {
        parent::__construct();
        self::$jsonSchema = json_decode(file_get_contents(__DIR__. '/../jsonSchema.json'), true);
        $this->header = [
            '_token_issuer_'    =>  $baseInfo->getTokenIssuer(),
            '_token_'           => $baseInfo->getToken()
        ];
        self::$billingApi = require __DIR__ . '/../config/apiConfig.php';
    }

    public function issueInvoice($params) {
        $apiName = 'issueInvoice';
        $header = $this->header;
        array_walk_recursive($params, 'self::prepareData');

//        $header = array_filter(array_merge($this->header, (array) $header), 'self::filterNotEmptyValue');
        if (isset($params['_ott_'])) {
            $header['_ott_'] = $params['_ott_'];
            unset($params['_ott_']);
        }
//        $paramKey = 'query';

        $relativeUri = self::$billingApi[$apiName]['subUri'];
        $option = [
            'headers' => $header,
            'query' => $params, // set query param for validation
        ];
        self::validateOption($apiName, $option, 'query');

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

        $option['withBracketParams'] = $withBracketParams;
        $option['withoutBracketParams'] = $params;
        //  unset `query` key because query string will be build in ApiRequestHandler and will be added to uri so dont need send again in query params
        unset($option['query']);

        return ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            $relativeUri,
            $option,
            false,
            true
        );
    }

    public function createPreInvoice($params) {
        $apiName = 'createPreInvoice';
        $baseUri =  self::$config[self::$serverType]['PRIVATE-CALL-ADDRESS'];

        array_walk_recursive($params, 'self::prepareData');
        if (!isset($params['token'])) {
            $params['token'] = $this->header['_token_'];
        }

        $paramKey = self::$billingApi[$apiName]['method'] == 'GET' ? 'query' : 'form_params';

        $relativeUri = self::$billingApi[$apiName]['subUri'];

        $option = [
            'headers' => [],
            $paramKey => $params, // set query param for validation
        ];

        self::validateOption($apiName, $option, $paramKey);
        // prepare params to send
        if (isset($params['productList'])) {
            foreach ($params['productList'] as $list) {
                foreach ($list as $key => $value) {
                    $params[$key][] = $value;
                }
            }
            unset($params['productList']);
        }
        $option[$paramKey] = $params;

        $result = ApiRequestHandler::Request(
            $baseUri,
            self::$billingApi[$apiName]['method'],
            $relativeUri,
            $option
        );
        $preInvoiceUri = rtrim(self::$config[self::$serverType]['PRIVATE-CALL-ADDRESS'], '/'). '/v1/pbc/preinvoice/' . $result['Result'];
        $hashCode = $result['Result'];
        unset($result['Result']);
        $result['Result']['hashCode'] = $hashCode;
        $result['Result']['preInvoiceUri'] = $preInvoiceUri;
        return $result;
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

        self::validateOption($apiName, $option, $paramKey);
        // prepare params to send
        $option['withBracketParams'] = [];
        $option['withoutBracketParams'] = $params;
        //  unset `query` key because query string will be build in ApiRequestHandler and will be added to uri so dont need send again in query params
        unset($option['query']);

        return ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            $relativeUri,
            $option,
            false,
            true
        );
    }

    public function payInvoice($params) {
        $apiName = 'payInvoice';

        array_walk_recursive($params, 'self::prepareData');

        $option = [
            'headers' => $this->header,
            'query' => $params,
        ];

        self::validateOption($apiName, $option);
        return ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option);
    }

    public function getPayInvoiceByWalletLink($params) {
        $apiName = 'getPayInvoiceByWalletLink';

        $option = [
            'headers' => [],
            'query' => $params,
        ];

        $httpQuery = self::buildHttpQuery($params);

        self::validateOption($apiName, $option);
        return self::$config[self::$serverType]['PRIVATE-CALL-ADDRESS'] . self::$billingApi[$apiName]['subUri'] . '?' . $httpQuery;
    }

    public function getPayInvoiceByUniqueNumberLink($params) {
        $apiName = 'getPayInvoiceByUniqueNumberLink';
        $option = [
            'headers' => [],
            'query' => $params,
        ];

        $httpQuery = self::buildHttpQuery($params);

        self::validateOption($apiName, $option);
        return self::$config[self::$serverType]['PRIVATE-CALL-ADDRESS'] . self::$billingApi[$apiName]['subUri'] . '?' . $httpQuery;
    }

    public function sendInvoicePaymentSMS($params) {
        $apiName = 'sendInvoicePaymentSMS';
        array_walk_recursive($params, 'self::prepareData');

        $option = [
            'headers' => $this->header,
            'query' => $params,
        ];

        self::validateOption($apiName, $option);
        // prepare params to send
        $option['withBracketParams'] = [];
        $option['withoutBracketParams'] = $params;
        //  unset `query` key because query string will be build in ApiRequestHandler and will be added to uri so dont need send again in query params
        unset($option['query']);
        return ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option,
            false,
            true
        );
    }

    public function getInvoiceListByMetadata($params) {
        $apiName = 'getInvoiceListByMetadata';
        $header = $this->header;
        $header['content-type'] = 'application/json'; # set content type to json

        array_walk_recursive($params, 'self::prepareData');
        $jsonParams = [];
        if (isset($params['metaQuery'])) {
            $jsonParams['metaQuery'] = $params['metaQuery'];
            unset($params['metaQuery']);
        }

        $option = [
            'headers' => $header,
            'query' => $params,
            'json' => $jsonParams,
        ];

        self::validateOption($apiName, $option);
        return ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
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

        self::validateOption($apiName, $option, 'query');
        // prepare params to send
        $option['withBracketParams'] = [];
        $option['withoutBracketParams'] = $params;
        //  unset `query` key because query string will be build in ApiRequestHandler and will be added to uri so dont need send again in query params
        unset($option['query']);

        return ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            $relativeUri,
            $option,
            false,
            true
        );
    }

    public function verifyInvoice($params) {
        $apiName = 'verifyInvoice';

        array_walk_recursive($params, 'self::prepareData');
        $paramKey = self::$billingApi[$apiName]['method'] == 'GET' ? 'query' : 'form_params';

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($apiName, $option, $paramKey);
        return ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );
    }

    public function cancelInvoice($params) {
        $apiName = 'cancelInvoice';

        array_walk_recursive($params, 'self::prepareData');

        $option = [
            'headers' => $this->header,
            'query' => $params,
        ];

        self::validateOption($apiName, $option);
        return ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option,
            false
        );
    }

    public function reduceInvoice($params) {
        $apiName = 'reduceInvoice';
        array_walk_recursive($params, 'self::prepareData');

        $paramKey = self::$billingApi[$apiName]['method'] == 'GET' ? 'query' : 'form_params';

        $relativeUri = self::$billingApi[$apiName]['subUri'];

        $option = [
            'headers' => $this->header,
            $paramKey => $params, // set query param for validation
        ];

        self::validateOption($apiName, $option, $paramKey);

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

        $option['withBracketParams'] = $withBracketParams;
        $option['withoutBracketParams'] = $params;
        //  unset `query` key because query string will be build in ApiRequestHandler and will be added to uri so dont need send again in query params
        unset($option['query']);
        return ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            $relativeUri,
            $option,
            false,
            true
        );
    }

    public function verifyAndCloseInvoice($params) {
        $apiName = 'verifyAndCloseInvoice';

        array_walk_recursive($params, 'self::prepareData');

        $option = [
            'headers' => $this->header,
            'query' => $params,
        ];

        self::validateOption($apiName, $option);
        return ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );
    }

    public function closeInvoice($params) {
        $apiName = 'closeInvoice';

        array_walk_recursive($params, 'self::prepareData');

        $option = [
            'headers' => $this->header,
            'query' => $params,
        ];

        self::validateOption($apiName, $option);
        return ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );
    }

    public function getInvoicePaymentLink($params) {
        $apiName = 'getInvoicePaymentLink';

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
        self::validateOption($apiName, $option);
        $result = ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );

        $result['result'] = $result['result']. $redirectUri . $callbackUri . $gateway ;
        return $result;
    }

    public function payInvoiceByInvoice($params) {
        $apiName = 'payInvoiceByInvoice';

        array_walk_recursive($params, 'self::prepareData');

        $option = [
            'headers' => $this->header,
            'query' => $params,
        ];

        self::validateOption($apiName, $option);
        return ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );
    }

    public function payInvoiceInFuture($params) {
        $apiName = 'payInvoiceInFuture';
        $header = $this->header;
        array_walk_recursive($params, 'self::prepareData');

        if (isset($params['_ott_'])) {
            $header['_ott_'] = $params['_ott_'];
            unset($params['_ott_']);
        }

        $option = [
            'headers' => $header,
            'query' => $params,
        ];

        self::validateOption($apiName, $option);
        return ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );
    }

    public function getExportList($params) {
        $apiName = 'getExportList';

        array_walk_recursive($params, 'self::prepareData');

        $option = [
            'headers' => $this->header,
            'query' => $params,
        ];

        self::validateOption($apiName, $option);
        $result =  ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );
        // add downloadPath to result
        foreach ($result['result'] as $key => $fileInfo){
            if ($fileInfo['statusCode'] === 'EXPORT_SERVICE_STATUS_SUCCESSFUL'){
                $hashCode = $fileInfo['resultFile']['hashCode'];
                $fileId = $fileInfo['resultFile']['id'];
                $result['result'][$key]['downloadPath'] = self::$config[self::$serverType]['FILE-SERVER-ADDRESS'] .'/nzh/file/?fileId=$fileId&hashCode=$hashCode';
            }
        }
        return $result;
    }

    public function requestWalletSettlement($params) {
        $apiName = 'requestWalletSettlement';
        $header = $this->header;
        array_walk_recursive($params, 'self::prepareData');

        if (isset($params['_ott_'])) {
            $header['_ott_'] = $params['_ott_'];
            unset($params['_ott_']);
        }

        $option = [
            'headers' => $header,
            'query' => $params,
        ];

        self::validateOption($apiName, $option);
        return  ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );
    }

    public function requestGuildSettlement ($params) {
        $apiName = 'requestGuildSettlement';
        $header = $this->header;
        array_walk_recursive($params, 'self::prepareData');

        if (isset($params['_ott_'])) {
            $header['_ott_'] = $params['_ott_'];
            unset($params['_ott_']);
        }

        $option = [
            'headers' => $header,
            'query' => $params,
        ];
        self::validateOption($apiName, $option);
        return  ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );
    }

    public function requestSettlementByTool ($params) {
        $apiName = 'requestSettlementByTool';
        $header = $this->header;
        array_walk_recursive($params, 'self::prepareData');

        if (isset($params['_ott_'])) {
            $header['_ott_'] = $params['_ott_'];
            unset($params['_ott_']);
        }

        $option = [
            'headers' => $header,
            'query' => $params,
        ];

        self::validateOption($apiName, $option);
        return  ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );
    }

    public function listSettlements ($params) {
        $apiName = 'listSettlements';
        array_walk_recursive($params, 'self::prepareData');

        $option = [
            'headers' => $this->header,
            'query' => $params,
        ];
        self::validateOption($apiName, $option);
        return  ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );
    }

    public function addAutoSettlement ($params) {
        $apiName = 'addAutoSettlement';
        array_walk_recursive($params, 'self::prepareData');

        $option = [
            'headers' => $this->header,
            'query' => $params,
        ];
        self::validateOption($apiName, $option);
        return  ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );
    }

    public function removeAutoSettlement  ($params) {
        $apiName = 'removeAutoSettlement';
        array_walk_recursive($params, 'self::prepareData');

        $option = [
            'headers' => $this->header,
            'query' => $params,
        ];

        self::validateOption($apiName, $option);
        return  ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );
    }

# ============================================== MULTI INVOICE APIS ====================================================
# ======================================================================================================================
    public function addDealer($params) {
        $apiName = 'addDealer';
        array_walk_recursive($params, 'self::prepareData');
        $paramKey = self::$billingApi[$apiName]['method'] == 'GET' ? 'query' : 'form_params';

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($apiName, $option, $paramKey);
        return  ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );
    }

    public function dealerList($params) {
        $apiName = 'dealerList';
        array_walk_recursive($params, 'self::prepareData');
        $paramKey = self::$billingApi[$apiName]['method'] == 'GET' ? 'query' : 'form_params';

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($apiName, $option, $paramKey);
        return  ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );
    }

    public function enableDealer($params) {
        $apiName = 'enableDealer';
        array_walk_recursive($params, 'self::prepareData');
        $paramKey = self::$billingApi[$apiName]['method'] == 'GET' ? 'query' : 'form_params';

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];
        self::validateOption($apiName, $option, $paramKey);
        return  ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );
    }

    public function disableDealer($params) {
        $apiName = 'disableDealer';
        array_walk_recursive($params, 'self::prepareData');
        $paramKey = self::$billingApi[$apiName]['method'] == 'GET' ? 'query' : 'form_params';

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($apiName, $option, $paramKey);
        return  ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );
    }

    public function businessDealingList($params) {
        $apiName = 'businessDealingList';
        array_walk_recursive($params, 'self::prepareData');
        $paramKey = self::$billingApi[$apiName]['method'] == 'GET' ? 'query' : 'form_params';

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($apiName, $option, $paramKey);
        return  ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );
    }

    public function issueMultiInvoice($params) {
        $apiName = 'issueMultiInvoice';
        $header = $this->header;
        array_walk_recursive($params, 'self::prepareData');
//        $paramKey = self::$billingApi[$apiName]['method'] == 'GET' ? 'query' : 'form_params';
        $relativeUri = self::$billingApi[$apiName]['subUri'];

        if (isset($params['_ott_'])) {
            $header['_ott_'] = $params['_ott_'];
            unset($params['_ott_']);
        }

        $option = [
            'headers' => $header,
            'query' => $params,
        ];

        self::validateOption($apiName, $option, 'query');
        $option['query']['data'] = json_encode($params['data']);
        $option['withBracketParams'] = [];
        $option['withoutBracketParams'] = $option['query'];
        //  unset `query` key because query string will be build in ApiRequestHandler and will be added to uri so dont need send again in query params
        unset($option['query']);
        return  ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            $relativeUri,
            $option,
            false,
            true
        );
    }

    public function reduceMultiInvoice($params) {
        $apiName = 'reduceMultiInvoice';
        array_walk_recursive($params, 'self::prepareData');
        $paramKey = self::$billingApi[$apiName]['method'] == 'GET' ? 'query' : 'form_params';

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($apiName, $option, $paramKey);
        $option[$paramKey]['data'] = json_encode($params['data']);

        return  ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );

    }

    public function reduceMultiInvoiceAndCashOut($params) {
        $apiName = 'reduceMultiInvoiceAndCashOut';
        array_walk_recursive($params, 'self::prepareData');
        $paramKey = self::$billingApi[$apiName]['method'] == 'GET' ? 'query' : 'form_params';

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($apiName, $option, $paramKey);
        $option[$paramKey]['data'] = json_encode($params['data']);

        return  ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );

    }

    public function addDealerProductPermission($params) {
        $apiName = 'addDealerProductPermission';
        array_walk_recursive($params, 'self::prepareData');
        $paramKey = self::$billingApi[$apiName]['method'] == 'GET' ? 'query' : 'form_params';

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($apiName, $option, $paramKey);
        return  ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );

    }

    public function dealerProductPermissionList($params) {
        $apiName = 'dealerProductPermissionList';
        array_walk_recursive($params, 'self::prepareData');
        $paramKey = self::$billingApi[$apiName]['method'] == 'GET' ? 'query' : 'form_params';

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($apiName, $option, $paramKey);
        return  ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );

    }

    public function dealingProductPermissionList($params) {
        $apiName = 'dealingProductPermissionList';
        array_walk_recursive($params, 'self::prepareData');
        $paramKey = self::$billingApi[$apiName]['method'] == 'GET' ? 'query' : 'form_params';

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($apiName, $option, $paramKey);
        return  ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );
    }

    public function disableDealerProductPermission($params) {
        $apiName = 'disableDealerProductPermission';
        array_walk_recursive($params, 'self::prepareData');
        $paramKey = self::$billingApi[$apiName]['method'] == 'GET' ? 'query' : 'form_params';

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($apiName, $option, $paramKey);
        return  ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );
    }

    public function enableDealerProductPermission($params) {
        $apiName = 'enableDealerProductPermission';
        array_walk_recursive($params, 'self::prepareData');
        $paramKey = self::$billingApi[$apiName]['method'] == 'GET' ? 'query' : 'form_params';

        $option = [
            'headers' => $this->header,
            $paramKey => $params,
        ];

        self::validateOption($apiName, $option, $paramKey);
        return  ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$billingApi[$apiName]['baseUri']],
            self::$billingApi[$apiName]['method'],
            self::$billingApi[$apiName]['subUri'],
            $option
        );

    }

}