<?php
return
    [
        "getOtt" => [
            "baseUri"   =>  'PLATFORM-ADDRESS',
            "subUri"    =>  'nzh/ott/',
            "method"    =>  'GET'
        ],

        "getOttFromPrivateCallAddress" => [
            "baseUri"   =>  'PRIVATE-CALL-ADDRESS',
            "subUri"    =>  'nzh/ott/',
            "method"    =>  'GET'
        ],

        "issueInvoice" => [
            'baseUri'   => 'PLATFORM-ADDRESS',
            'subUri'    => 'nzh/biz/issueInvoice/',
            'method'    => 'GET'
        ],

        "createPreInvoice" => [
            'baseUri'   => 'PRIVATE-CALL-ADDRESS',
            'subUri'    => 'service/createPreInvoice',
            'method'    => 'POST'
        ],

        "getInvoiceList" =>  [
            "baseUri" =>  'PLATFORM-ADDRESS',
            "subUri" =>  'nzh/biz/getInvoiceList',
            "method" =>  'GET'
        ],

        "payInvoice" =>  [
            "baseUri" =>  'PLATFORM-ADDRESS',
            "subUri" =>  'nzh/biz/payInvoice/',
            "method" =>  'GET'
        ],

        "getPayInvoiceByWalletLink" =>  [
            "baseUri" =>  'PRIVATE-CALL-ADDRESS',
            "subUri" =>  'v1/pbc/biz/payInvoice/',
            "method" =>  'GET'
        ],

        "getPayInvoiceByUniqueNumberLink" =>  [
            "baseUri" =>  'PRIVATE-CALL-ADDRESS',
            "subUri" =>  '/v1/pbc/payInvoiceByUniqueNumber/',
            "method" =>  'GET'
        ],

        "sendInvoicePaymentSMS" =>  [
            "baseUri" =>  'PLATFORM-ADDRESS',
            "subUri" =>  'nzh/biz/sendInvoicePaymentSMS/',
            "method" =>  'GET'
        ],

        "getInvoiceListByMetadata" =>  [
            "baseUri" =>  'PLATFORM-ADDRESS',
            "subUri" =>  'nzh/biz/getInvoiceListByMetadata/',
            "method" =>  'GET'
        ],

        "getInvoiceListAsFile" =>  [
            "baseUri" =>  'PLATFORM-ADDRESS',
            "subUri" =>  'nzh/biz/getInvoiceListAsFile/',
            "method" =>  'GET'
        ],

        "verifyInvoice" =>  [
            "baseUri" =>  'PLATFORM-ADDRESS',
            "subUri" =>  'nzh/biz/verifyInvoice/',
            "method" =>  'GET'
        ],

        "cancelInvoice" =>  [
            "baseUri" =>  'PLATFORM-ADDRESS',
            "subUri" =>  'nzh/biz/cancelInvoice',
            "method" =>  'GET'
        ],

        "reduceInvoice" =>  [
            "baseUri" =>  'PLATFORM-ADDRESS',
            "subUri" =>  'nzh/biz/reduceInvoice/',
            "method" =>  'GET'
        ],

        "verifyAndCloseInvoice" =>  [
            "baseUri" =>  'PLATFORM-ADDRESS',
            "subUri" =>  'nzh/biz/verifyAndCloseInvoice/',
            "method" =>  'GET'
        ],

        "closeInvoice" =>  [
            "baseUri" =>  'PLATFORM-ADDRESS',
            "subUri" =>  'nzh/biz/closeInvoice/',
            "method" =>  'GET'
        ],

        "getInvoicePaymentLink" =>  [
            "baseUri" =>  'PLATFORM-ADDRESS',
            "subUri" =>  'nzh/biz/getInvoicePaymentLink/',
            "method" =>  'GET'
        ],

        "payInvoiceByInvoice" =>  [
            "baseUri" =>  'PLATFORM-ADDRESS',
            "subUri" =>  'nzh/biz/issueInvoice/',
            "method" =>  'GET'
        ],

        "payInvoiceInFuture" =>  [
            "baseUri" =>  'PLATFORM-ADDRESS',
            "subUri" =>  'nzh/biz/payInvoiceInFuture/',
            "method" =>  'GET'
        ],

        "getExportList" =>  [
            "baseUri" =>  'PLATFORM-ADDRESS',
            "subUri" =>  'nzh/biz/getExportList/',
            "method" =>  'GET'
        ],

        "requestWalletSettlement" =>  [
            "baseUri" =>  'PLATFORM-ADDRESS',
            "subUri" =>  'nzh/requestSettlement/',
            "method" =>  'GET'
        ],

        "requestGuildSettlement" =>  [
            "baseUri" =>  'PLATFORM-ADDRESS',
            "subUri" =>  'nzh/biz/requestSettlement/',
            "method" =>  'GET'
        ],

        'requestSettlementByTool' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/biz/requestSettlementByTool',
            'method' => 'GET'
        ],

        'listSettlements' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/listSettlements',
            'method' => 'GET'
        ],

        'addAutoSettlement' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/biz/addAutoSettlement',
            'method' => 'GET'
        ],

        'removeAutoSettlement' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/biz/removeAutoSettlement',
            'method' => 'GET'
        ],

        'addDealer' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/biz/addDealer',
            'method' => 'POST'
        ],

        'dealerList' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/biz/dealerList',
            'method' => 'POST'
        ],

        'enableDealer' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/biz/enableDealer',
            'method' => 'POST'
        ],

        'disableDealer' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/biz/disableDealer',
            'method' => 'POST'
        ],

        'businessDealingList' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/biz/businessDealingList',
            'method' => 'POST'
        ],

        'issueMultiInvoice' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/biz/issueMultiInvoice',
            'method' => 'GET'
        ],

        'reduceMultiInvoice' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/biz/reduceMultiInvoice',
            'method' => 'GET'
        ],

        'reduceMultiInvoiceAndCashOut' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/biz/reduceMultiInvoiceAndCashout',
            'method' => 'GET'
        ],

        'addDealerProductPermission' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/biz/addDealerProductPermission',
            'method' => 'POST'
        ],

        'dealerProductPermissionList' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/biz/dealerProductPermissionList',
            'method' => 'POST'
        ],

        'dealingProductPermissionList' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/biz/dealingProductPermissionList',
            'method' => 'POST'
        ],

        'disableDealerProductPermission' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/biz/disableDealerProductPermission',
            'method' => 'POST'
        ],

        'enableDealerProductPermission' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/biz/enableDealerProductPermission',
            'method' => 'POST'
        ],

        //  tag: common, token: API_TOKEN
        'guildCode' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/guildList',
            'method' => 'GET'
        ],
    ];




