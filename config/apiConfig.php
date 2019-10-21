<?php
return
    [
        'issueInvoice' => [
            'baseUri'   => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method'    => 'GET'
        ],

        'createPreInvoice' => [
            'baseUri'   => 'PLATFORM-ADDRESS',
            'subUri'    => 'nzh/doServiceCall',
            'method'    => 'GET'
        ],

        'getInvoiceList' =>  [
            'baseUri' =>  'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' =>  'GET'
        ],

        'payInvoice' =>  [
            'baseUri' =>  'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' =>  'GET'
        ],

        'getPayInvoiceByWalletLink' =>  [
            'baseUri' =>  'PRIVATE-CALL-ADDRESS',
            'subUri' =>  'v1/pbc/biz/payInvoice/',
            'method' =>  'GET'
        ],

        'getPayInvoiceByUniqueNumberLink' =>  [
            'baseUri' =>  'PRIVATE-CALL-ADDRESS',
            'subUri' =>  '/v1/pbc/payInvoiceByUniqueNumber/',
            'method' =>  'GET'
        ],

        'sendInvoicePaymentSMS' =>  [
            'baseUri' =>  'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' =>  'GET'
        ],

        'getInvoiceListByMetadata' =>  [
            'baseUri' =>  'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' =>  'POST'
        ],

        'getInvoiceListAsFile' =>  [
            'baseUri' =>  'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' =>  'GET'
        ],

        'verifyInvoice' =>  [
            'baseUri' =>  'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' =>  'GET'
        ],

        'cancelInvoice' =>  [
            'baseUri' =>  'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' =>  'GET'
        ],

        'reduceInvoice' =>  [
            'baseUri' =>  'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' =>  'GET'
        ],

        'verifyAndCloseInvoice' =>  [
            'baseUri' =>  'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' =>  'GET'
        ],

        'closeInvoice' =>  [
            'baseUri' =>  'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' =>  'GET'
        ],

        'getInvoicePaymentLink' =>  [
            'baseUri' =>  'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' =>  'GET'
        ],

        'payInvoiceByInvoice' =>  [
            'baseUri' =>  'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' =>  'GET'
        ],

        'payInvoiceInFuture' =>  [
            'baseUri' =>  'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' =>  'GET'
        ],

        'getExportList' =>  [
            'baseUri' =>  'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' =>  'GET'
        ],

         'payInvoiceByCredit' =>  [
            'baseUri' =>  'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' =>  'GET'
        ],

         'payAnyInvoiceByCredit' =>  [
            'baseUri' =>  'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' =>  'GET'
        ],

        'requestWalletSettlement' =>  [
            'baseUri' =>  'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' =>  'GET'
        ],

        'requestGuildSettlement' =>  [
            'baseUri' =>  'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' =>  'GET'
        ],

        'requestSettlementByTool' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' => 'GET'
        ],

        'listSettlements' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' => 'GET'
        ],

        'addAutoSettlement' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' => 'GET'
        ],

        'removeAutoSettlement' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' => 'GET'
        ],
# =========================== MULTI INVOICE APIS =========================
        'issueMultiInvoice' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' => 'POST'
        ],

        'reduceMultiInvoice' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' => 'POST'
        ],

        'reduceMultiInvoiceAndCashOut' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' => 'POST'
        ],

# ======================== VOUCHER  APIS CONFIG ============================
        // #1
        'defineCreditVoucher' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' => 'GET'
        ],

        // #2
        'defineDiscountAmountVoucher' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' => 'GET'
        ],

        // #3
        'defineDiscountPercentageVoucher' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' => 'GET'
        ],

        // #4
        'applyVoucher' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' => 'GET'
        ],

        // #5
        'getVoucherList' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' => 'GET'
        ],

        // #6
        'deactivateVoucher' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' => 'GET'
        ],

        // #7
        'activateVoucher' => [
            'baseUri' => 'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            'method' => 'GET'
        ],
# ============= DIRECT DEBIT APIS =========================

        "defineDirectWithdraw" => [
            "baseUri" =>'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            "method" =>'POST'
        ],

        "directWithdrawList" => [
            "baseUri" =>'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            "method" =>'GET'
        ],

        "updateDirectWithdraw" => [
            "baseUri" =>'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            "method" =>'POST'
        ],

        "revokeDirectWithdraw" => [
            "baseUri" =>'PLATFORM-ADDRESS',
            'subUri' => 'nzh/doServiceCall',
            "method" =>'POST'
        ]
    ];
