<?php
/**
 * Created by PhpStorm.
 * User: keshtgar
 * Date: 11/11/19
 * Time: 9:49 AM
 */
use PHPUnit\Framework\TestCase;
use Pod\Billing\Service\BillingService;
use Pod\Common\Service\CommonService;
use Pod\Base\Service\BaseInfo;
use Pod\Base\Service\Exception\ValidationException;
use Pod\Base\Service\Exception\PodException;

final class BillingServiceTest extends TestCase
{
//    public static $apiToken;
    public static $billingService;
    public static $commonService;
    const TOKEN_ISSUER = 1;
    const API_TOKEN = '{Put Api Token}';
    const ACCESS_TOKEN = '{Put Access Token}';
    const CLIENT_ID = '{Put client Id}';
    const CLIENT_SECRET = '{Put client secret}';
    const CONFIRM_CODE = '{Put Confirm Code}';
    private $privateKey;

    public function setUp(): void
    {
        parent::setUp();
        # set serverType to SandBox or Production
        BaseInfo::initServerType(BaseInfo::PRODUCTION_SERVER);

        $baseInfo = new BaseInfo();
        $baseInfo->setTokenIssuer(self::TOKEN_ISSUER);
        $baseInfo->setToken(self::API_TOKEN);
        self::$commonService = new CommonService($baseInfo);
        self::$billingService = new BillingService($baseInfo);
        $this->privateKey = file_get_contents('file://' . __DIR__ . '/../private_key.xml');

    }

    public function testIssueInvoiceAllParameters()
    {
        $ottResult = self::$commonService->getOtt([]);
        $this->assertFalse($ottResult['hasError']);

        $params =
            [
                ## ============================ *Required Parameters  =========================
                'productList'   	=> [
                    [
                        # شناسه محصول . در صورتی که بند فاکتور محصول مرتبط ندارد مقدار آن 0 وارد شود
                        'productId'         => 0,
                        # مبلغ بند فاکتور. برای استفاده از قیمت محصول وارد شده از مقدار auto استفاده نمایید
                        'price'             => 100,
                        #تعداد محصول
                        'quantity'          => 1,
                        # توضیحات
                        'productDescription'=> 'unit test issue invoice',
                    ],
                    // اطلاعات محصولات دیگر
                ],
                'guildCode'			=> 'INFORMATION_TECHNOLOGY_GUILD', # *Required
                'ott' 				=> $ottResult['ott'],
                ## =========================== Optional Parameters  ===========================
                'redirectURL' 		=> 'http://www.google.com',
                'userId' 			=> 16849,
                'billNumber' 		=> '1234587',
                'description' 		=> 'unit test issue invoice',
                'deadline' 			=> '1398/12/05',
                'currencyCode' 		=> 'IRR',  # default : IRR
                'addressId' 		=> 10838,
//                'voucherHash' 		=> [''],
//                'scVoucherHash'     => ['123'],
                'scApiKey'          => '123',
                'preferredTaxRate' 	=> 0.09,
                'verificationNeeded'=> true,
                'verifyAfterTimeout'=> true,
                'preview' 			=> true,
                'safe'				=> true,
                'postVoucherEnabled'=> true,
                'hasEvent'			=> true,
                'eventTitle'		=> 'unit test event title',
                'eventTimeZone'		=> 'Asia/Tehran',
                'eventDescription'	=> '{put event description}',
                "metadata"			=> '{"name":"elham"}',
                'eventMetadata'		=> '{"name":"event"}',
                'eventReminders'	=> ['{"id":0,"minute":15,"alarmType":"Notification"}'],
            ];

        try {
            $result = self::$billingService->issueInvoice($params);
            $this->assertFalse($result['hasError']);

        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testIssueInvoiceRequiredParameters()
    {
        $ottResult = self::$commonService->getOtt([]);
        $this->assertFalse($ottResult['hasError']);
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'productList'   	=> [
                    [
                        # شناسه محصول . در صورتی که بند فاکتور محصول مرتبط ندارد مقدار آن 0 وارد شود
                        'productId'         => 0,
                        # مبلغ بند فاکتور. برای استفاده از قیمت محصول وارد شده از مقدار auto استفاده نمایید
                        'price'             => 100,
                        #تعداد محصول
                        'quantity'          => 1,
                        # توضیحات
                        'productDescription'=> 'unit test issue invoice',
                    ],
                    // اطلاعات محصولات دیگر
                ],
                'guildCode'			=> 'INFORMATION_TECHNOLOGY_GUILD', # *Required
                'ott' 				=> $ottResult['ott'],
            ];
        try {
            $result = self::$billingService->issueInvoice($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testIssueInvoiceValidationError()
    {
        $ottResult = self::$commonService->getOtt([]);
        $this->assertFalse($ottResult['hasError']);

        $paramsWithoutRequired = [];
        $paramsWrongValue = [
            ## ============================ *Required Parameters  =========================
            'productList'   	=> [
                [
                    # شناسه محصول . در صورتی که بند فاکتور محصول مرتبط ندارد مقدار آن 0 وارد شود
                    'productId'         => 0,
                    # مبلغ بند فاکتور. برای استفاده از قیمت محصول وارد شده از مقدار auto استفاده نمایید
                    'price'             => 100,
                    #تعداد محصول
                    'quantity'          => 1,
                    # توضیحات
                    'productDescription'=> 'unit test issue invoice',
                ],
                // اطلاعات محصولات دیگر
            ],
            'guildCode'			=> 'INFORMATION_TECHNOLOGY_GUILD', # *Required
            'ott' 				=> $ottResult['ott'],
            ## =========================== Optional Parameters  ===========================
            'redirectURL' 		=> 'www.google.com',
            'deadline' 		    => '1398-12-11',
            'eventReminders' 	=> 'string',
            'scVoucherHash' 	=> 'string',
        ];
        try {
            self::$billingService->issueInvoice($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('productList', $validation);
            $this->assertEquals('The property productList is required', $validation['productList'][0]);

            $this->assertArrayHasKey('guildCode', $validation);
            $this->assertEquals('The property guildCode is required', $validation['guildCode'][0]);

            $this->assertArrayHasKey('ott', $validation);
            $this->assertEquals('The property ott is required', $validation['ott'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
        try {
            self::$billingService->issueInvoice($paramsWrongValue);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();

            $this->assertArrayHasKey('redirectURL', $validation);
            $this->assertEquals('Invalid URL format', $validation['redirectURL'][0]);

            $this->assertArrayHasKey('deadline', $validation);
            $this->assertEquals('Does not match the regex pattern ^[1][3-5][0-9]{2}/([1-9]|0[1-9]|1[0-2])/([1-2][0-9]|0[1-9]|3[0-1]|[1-9])$', $validation['deadline'][0]);

            $this->assertArrayHasKey('eventReminders', $validation);
            $this->assertEquals('String value found, but an array is required', $validation['eventReminders'][0]);

            $this->assertArrayHasKey('scVoucherHash', $validation);
            $this->assertEquals('String value found, but an array is required', $validation['scVoucherHash'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testPayInvoiceAllParameters()
    {
        $params =
        [
        ## ====================  Optional Parameters  =====================
            'invoiceId'         => 55501,
            'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
            'scApiKey'          => '{Put service call Api Key}',
        ];

        try {
            $result = self::$billingService->payInvoice($params);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('فاکتور قبلاً پرداخت شده است' , $error['message']);
        }
    }

    public function testPayInvoiceRequiredParameters()
    {
        $params =
            [
                ## ====================  Optional Parameters  =====================
                'invoiceId' => 55434,
            ];
        try {
            $result = self::$billingService->payInvoice($params);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('فاکتور قبلاً پرداخت شده است' , $error['message']);
        }
    }

    public function testPayInvoiceValidationError()
    {
        $paramsWithoutRequired = [];

        try {
            self::$billingService->payInvoice($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('invoiceId', $validation);
            $this->assertEquals('The property invoiceId is required', $validation['invoiceId'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testPayInvoiceInFutureAllParameters()
    {
        $params1 =
        [
            ## ============================ *Required Parameters  =========================
            'invoiceId' => 56339,                     # شناسه فاکتور
            'date' => '1398/10/30',# تاریخ شمسی سررسید
            'wallet' => 'PODLAND_WALLET',  # کد کیف پول
            ## =========================== Optional Parameters  ===========================
            'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
            'scApiKey'           => '{Put service call Api Key}',
        ];

        try {
            $result = self::$billingService->payInvoiceInFuture($params1);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('فاکتور قبلا برای پرداخت در آینده ثبت شده است' , $error['message']);
        }

         $params2 =
        [
            ## ============================ *Required Parameters  =========================
            'invoiceId' => 56339,                     # شناسه فاکتور
            'date' => '1398/10/30',# تاریخ شمسی سررسید
            'guildCode' => 'INFORMATION_TECHNOLOGY_GUILD', # کد صنف
            ## =========================== Optional Parameters  ===========================
            'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
            'scApiKey'           => '{Put service call Api Key}',
        ];

        try {
            $result = self::$billingService->payInvoiceInFuture($params2);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('فاکتور قبلا برای پرداخت در آینده ثبت شده است' , $error['message']);
        }
    }

    public function testPayInvoiceInFutureRequiredParameters()
    {
        $params1 =
            [
                ## ============================ *Required Parameters  =========================
                'invoiceId' => 56339,                     # شناسه فاکتور
                'date' => '1398/10/30',# تاریخ شمسی سررسید
                'wallet' => 'PODLAND_WALLET',  # کد کیف پول
            ];

        try {
            $result = self::$billingService->payInvoiceInFuture($params1);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('فاکتور قبلا برای پرداخت در آینده ثبت شده است' , $error['message']);
        }

        $params2 =
            [
                ## ============================ *Required Parameters  =========================
                'invoiceId' => 56339,                     # شناسه فاکتور
                'date' => '1398/10/30',# تاریخ شمسی سررسید
                'guildCode' => 'INFORMATION_TECHNOLOGY_GUILD', # کد صنف
            ];

        try {
            $result = self::$billingService->payInvoiceInFuture($params2);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('فاکتور قبلا برای پرداخت در آینده ثبت شده است' , $error['message']);
        }
    }

    public function testPayInvoiceInFutureValidationError()
    {
        $paramsWithoutRequired = [];

        try {
            self::$billingService->payInvoiceInFuture($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();

            $this->assertArrayHasKey('invoiceId', $validation);
            $this->assertEquals('The property invoiceId is required', $validation['invoiceId'][0]);

            $this->assertArrayHasKey('date', $validation);
            $this->assertEquals('The property date is required', $validation['date'][0]);

            $this->assertArrayHasKey('guildCode', $validation);
            $this->assertEquals('The property guildCode is required', $validation['guildCode'][0]);

            $this->assertArrayHasKey('wallet', $validation);
            $this->assertEquals('The property wallet is required', $validation['wallet'][0]);

            $this->assertArrayHasKey('oneOf', $validation);
            $this->assertEquals('Failed to match exactly one schema', $validation['oneOf'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testPayInvoiceByInvoiceAllParameters()
    {
        $params =
        [
            ## ============================ *Required Parameters  =========================
            'creditorInvoiceId' => 2886790, # شناسه فاکتور بستانکار
            'debtorInvoiceId' => 3068096, # شناسه فاکتور بدهکار
            ## =========================== Optional Parameters  ===========================
            'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
            'scApiKey'           => '{Put service call Api Key}',
        ];

        try {
            $result = self::$billingService->payInvoiceByInvoice($params);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('فاکتور بستانکار یافت نشد' , $error['message']);
        }
    }

    public function testPayInvoiceByInvoiceRequiredParameters()
    {
        $params =
        [
            ## ============================ *Required Parameters  =========================
            'creditorInvoiceId' => 2886790, # شناسه فاکتور بستانکار
            'debtorInvoiceId' => 3068096, # شناسه فاکتور بدهکار
        ];

        try {
            $result = self::$billingService->payInvoiceByInvoice($params);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('فاکتور بستانکار یافت نشد' , $error['message']);
        }
    }

    public function testPayInvoiceByInvoiceValidationError()
    {
        $paramsWithoutRequired = [];

        try {
            self::$billingService->payInvoiceByInvoice($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();

            $this->assertArrayHasKey('creditorInvoiceId', $validation);
            $this->assertEquals('The property creditorInvoiceId is required', $validation['creditorInvoiceId'][0]);

            $this->assertArrayHasKey('debtorInvoiceId', $validation);
            $this->assertEquals('The property debtorInvoiceId is required', $validation['debtorInvoiceId'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testPayInvoiceByCreditAllParameters()
    {
        $ottResult = self::$commonService->getOtt([]);
        $this->assertFalse($ottResult['hasError']);

        $params =
        [
            ## ============================ *Required Parameters  =========================
            'ott' => $ottResult['ott'] , # one time token - این توکن را در سرویس قبلی دریافت کرده اید.
            'invoiceId' => 56624, # شناسه فاکتور
            ## =========================== Optional Parameters  ===========================
            'wallet' => 'PODLAND_WALLET',  # کد کیف پول
            'delegatorId' => [1234,1235],
            'delegationHash' => ['1456235','4534'],
            'forceDelegation' => false,
            'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
            'scApiKey'           => '{Put service call Api Key}',
        ];

        try {
            $result = self::$billingService->payInvoiceByCredit($params);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('فاکتور قبلاً پرداخت شده است' , $error['message']);
        }
    }

    public function testPayInvoiceByCreditRequiredParameters()
    {
        $ottResult = self::$commonService->getOtt([]);
        $this->assertFalse($ottResult['hasError']);

        $params =
            [
                ## ============================ *Required Parameters  =========================
                'ott' => $ottResult['ott'] , # one time token - این توکن را در سرویس قبلی دریافت کرده اید.
                'invoiceId' => 56624, # شناسه فاکتور
        ];

        try {
            $result = self::$billingService->payInvoiceByCredit($params);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('فاکتور قبلاً پرداخت شده است' , $error['message']);
        }
    }

    public function testPayInvoiceByCreditValidationError()
    {
        $paramsWithoutRequired = [];

        try {
            self::$billingService->payInvoiceByCredit($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();

            $this->assertArrayHasKey('invoiceId', $validation);
            $this->assertEquals('The property invoiceId is required', $validation['invoiceId'][0]);


            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetInvoiceListAllParameters()
    {
        $params1 =
            [
                ## ============================ *Required Parameters one of  =========================
                'offset' => 0,
                ## =========================== Optional Parameters  ===========================
                'size' => 10,
                'guildCode' => 'INFORMATION_TECHNOLOGY_GUILD', # کد صنف
                'id' => 55434,   # invoice id
                'billNumber' => '12345', # شماره قبض که به تنهایی با آن می توان جستجو نمود
                'uniqueNumber' => '123456', # شماره کد شده ی قبض که به تنهایی با آن می توان جستجو نمود
                'trackerId' => 11,
                'fromDate' => '1398/01/01 00:00:00',          # تاریخ شمسی صدور فاکتور yyyy/mm/dd hh:mi:ss
                'toDate' => '1398/12/29 00:00:00',            # تاریخ شمسی صدور فاکتور yyyy/mm/dd hh:mi:ss
                'isCanceled' => true,
                'isPayed' => true,
                'isClosed' => true,
                'isWaiting' => true,
                'referenceNumber' => 'put reference number',                             # شماره ارجاع
                'userId' => 16849,                                        # شناسه کاربری مشتری
                'issuerId' => [12121],                        # شناسه کاربری صادر کننده فاکتور
                'query' => 'web',                                      # عبارت برای جستجو
                'productIdList' => [0],  # لیست شماره محصولات
//                'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
//                'scApiKey'           => '{Put service call Api Key}',
            ];
        try {
            $result = self::$billingService->getInvoiceList($params1);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    $params2 =
            [
                ## ============================ *Required Parameters one of  =========================
                'firstId' => 1, # در صورتی که این فیلد وارد شود فیلدهای lastId و offset نباید وارد شوند و نتیجه صعودی مرتب می شود.
                ## =========================== Optional Parameters  ===========================
                'size' => 10,
                'guildCode' => 'INFORMATION_TECHNOLOGY_GUILD', # کد صنف
                'id' => 55434,   # invoice id
                'billNumber' => '12345', # شماره قبض که به تنهایی با آن می توان جستجو نمود
                'uniqueNumber' => '123456', # شماره کد شده ی قبض که به تنهایی با آن می توان جستجو نمود
                'trackerId' => 11,
                'fromDate' => '1398/01/01 00:00:00',          # تاریخ شمسی صدور فاکتور yyyy/mm/dd hh:mi:ss
                'toDate' => '1398/12/29 00:00:00',            # تاریخ شمسی صدور فاکتور yyyy/mm/dd hh:mi:ss
                'isCanceled' => true,
                'isPayed' => true,
                'isClosed' => true,
                'isWaiting' => true,
                'referenceNumber' => 'put reference number',                             # شماره ارجاع
                'userId' => 16849,                                        # شناسه کاربری مشتری
                'issuerId' => [12121],                        # شناسه کاربری صادر کننده فاکتور
                'query' => 'web',                                      # عبارت برای جستجو
                'productIdList' => [0],  # لیست شماره محصولات
                'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
                'scApiKey'           => '{Put service call Api Key}',
            ];

        try {
            $result = self::$billingService->getInvoiceList($params2);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }

    $params3 =
            [
                ## ============================ *Required Parameters one of  =========================
                'lastId' => 1000000, # در صورتی که این فیلد وارد شود فیلدهای firstId و offset نباید وارد شوند و نتیجه نزولی مرتب می شود.
                ## =========================== Optional Parameters  ===========================
                'size' => 10,
                'guildCode' => 'INFORMATION_TECHNOLOGY_GUILD', # کد صنف
                'id' => 55434,   # invoice id
                'billNumber' => '12345', # شماره قبض که به تنهایی با آن می توان جستجو نمود
                'uniqueNumber' => '123456', # شماره کد شده ی قبض که به تنهایی با آن می توان جستجو نمود
                'trackerId' => 11,
                'fromDate' => '1398/01/01 00:00:00',          # تاریخ شمسی صدور فاکتور yyyy/mm/dd hh:mi:ss
                'toDate' => '1398/12/29 00:00:00',            # تاریخ شمسی صدور فاکتور yyyy/mm/dd hh:mi:ss
                'isCanceled' => true,
                'isPayed' => true,
                'isClosed' => true,
                'isWaiting' => true,
                'referenceNumber' => 'put reference number',                             # شماره ارجاع
                'userId' => 16849,                                        # شناسه کاربری مشتری
                'issuerId' => [12121],                        # شناسه کاربری صادر کننده فاکتور
                'query' => 'web',                                      # عبارت برای جستجو
                'productIdList' => [0],  # لیست شماره محصولات
                'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
                'scApiKey'           => '{Put service call Api Key}',
            ];

        try {
            $result = self::$billingService->getInvoiceList($params3);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetInvoiceListRequiredParameters()
    {
        $reqParam1 =
            [
                ## ============================ *Required Parameters one of  =========================
                'offset' => 0, # در صورتی که این فیلد وارد شود فیلدهای lastId و firstId نباید وارد شوند و نتیجه نزولی مرتب می شود
            ];
        try {
            $result = self::$billingService->getInvoiceList($reqParam1);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
        $reqParam2 =
            [
                ## ============================ *Required Parameters one of  =========================
                'firstId' => 1, # در صورتی که این فیلد وارد شود فیلدهای lastId و offset نباید وارد شوند و نتیجه صعودی مرتب می شود.
            ];
        try {
            $result = self::$billingService->getInvoiceList($reqParam2);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
        $reqParam3 =
            [
                ## ============================ *Required Parameters one of  =========================
                'lastId' => 10000, # در صورتی که این فیلد وارد شود فیلدهای firstId و offset نباید وارد شوند و نتیجه نزولی مرتب می شود
            ];
        try {
            $result = self::$billingService->getInvoiceList($reqParam3);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetInvoiceListValidationError()
    {
        $paramsWithoutRequired = [];

        try {
            self::$billingService->getInvoiceList($paramsWithoutRequired);
        } catch (ValidationException $e) {
            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('lastId', $validation);
            $this->assertEquals('The property lastId is required', $validation['lastId'][0]);
            $this->assertArrayHasKey('offset', $validation);
            $this->assertEquals('The property offset is required', $validation['offset'][0]);
            $this->assertArrayHasKey('firstId', $validation);
            $this->assertEquals('The property firstId is required', $validation['firstId'][0]);
            $this->assertArrayHasKey('oneOf', $validation);
            $this->assertEquals('Failed to match exactly one schema', $validation['oneOf'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetInvoiceListByMetadataAllParameters()
    {
        $params =
        [
            'metaQuery' => ["field"=>"name","is"=>"elham"],
            ## ================== Optional Parameters  ====================
            'offset' => 0,
            'size' => 10,
            'isPayed' => false, # true/false
            'isCanceled' => false, # true/false
            'scVoucherHash' => ['{Put Service Call Voucher Hashes}'],
            'scApiKey' => '{Put service call Api Key}',
        ];

        try {
            $result = self::$billingService->getInvoiceListByMetadata($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetInvoiceListByMetadataRequiredParameters()
    {
        $reqParam1 =
            [
                ## ================ *Required Parameters one of  ================
                'metaQuery' => ["field"=>"name","is"=>"elham"],
            ];
        try {
            $result = self::$billingService->getInvoiceListByMetadata($reqParam1);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetInvoiceListByMetadataValidationError()
    {
        $paramsWithoutRequired = [];

        try {
            self::$billingService->getInvoiceListByMetadata($paramsWithoutRequired);
        } catch (ValidationException $e) {
            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('metaQuery', $validation);
            $this->assertEquals('The property metaQuery is required', $validation['metaQuery'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetInvoiceListAsFileAllParameters()
    {
        $params =
            [
                ## ============================ *Required Parameters  =========================
                # یکی از این سه فیلد اجباری است
                'lastNRows' => 10, # n ردیف آخر فاکتور
                'toDate' => '1398/12/12 10:10:10',            # تاریخ شمسی صدور فاکتور yyyy/mm/dd hh:mi:ss
                'fromDate' => '1398/01/01 10:10:10',          # تاریخ شمسی صدور فاکتور yyyy/mm/dd hh:mi:ss
                ## =========================== Optional Parameters  ===========================
                'guildCode' => 'INFORMATION_TECHNOLOGY_GUILD', # کد صنف
                'id' => 55434,
                'billNumber' => '12345', # شماره قبض که به تنهایی با آن می توان جستجو نمود
                'uniqueNumber' => '123456', # شماره کد شده ی قبض که به تنهایی با آن می توان جستجو نمود
                'trackerId' => 123,
                'isCanceled' => false,
                'isPayed' => false,
                'isClosed' => false,
                'isWaiting' => false,
                'referenceNumber' => 'put reference number',               # شماره ارجاع
                'userId' => 16849,                          # شناسه کاربری مشتری
                'query' => 'test',                               # عبارت برای جستجو
                'productIdList' => [0],  # لیست شماره محصولات
                'callbackUrl' => '{put call back url}',    # آدرس فراخوانی پس از اتمام تولید گزارش
                'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
                'scApiKey'           => '{Put service call Api Key}',
            ];

        try {
            $result = self::$billingService->getInvoiceListAsFile($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetInvoiceListAsFileRequiredParameters()
    {
        $reqParam1 =
        [
            ## ============================ *Required Parameters  =========================
            'lastNRows' => 10, # n ردیف آخر فاکتور
        ];
        try {
            $result = self::$billingService->getInvoiceListAsFile($reqParam1);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
        $reqParam2 =
        [
            ## ============================ *Required Parameters  =========================
            'fromDate' => '1398/01/01 10:10:10',          # تاریخ شمسی صدور فاکتور yyyy/mm/dd hh:mi:ss
        ];
        try {
            $result = self::$billingService->getInvoiceListAsFile($reqParam2);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
        $reqParam3 =
        [
            ## ============================ *Required Parameters  =========================
            'toDate' => '1398/12/12 10:10:10',            # تاریخ شمسی صدور فاکتور yyyy/mm/dd hh:mi:ss
        ];
        try {
            $result = self::$billingService->getInvoiceListAsFile($reqParam3);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetInvoiceListAsFileValidationError()
    {
        $paramsWithoutRequired = [];

        try {
            self::$billingService->getInvoiceListAsFile($paramsWithoutRequired);
        } catch (ValidationException $e) {
            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('lastNRows', $validation);
            $this->assertEquals('The property lastNRows is required', $validation['lastNRows'][0]);

            $this->assertArrayHasKey('fromDate', $validation);
            $this->assertEquals('The property fromDate is required', $validation['fromDate'][0]);

            $this->assertArrayHasKey('toDate', $validation);
            $this->assertEquals('The property toDate is required', $validation['toDate'][0]);

            $this->assertArrayHasKey('anyOf', $validation);
            $this->assertEquals('Failed to match at least one schema', $validation['anyOf'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testVerifyInvoiceAllParameters()
    {
        $params1 =
        [
        ## ====================  Optional Parameters  =====================
            'id' => 55803,
            'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
            'scApiKey'           => '{Put service call Api Key}',
        ];

        $params2 =
        [
        ## ====================  Optional Parameters  =====================
            'billNumber'        => 'unitTest',
            'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
            'scApiKey'          => '{Put service call Api Key}',
        ];

        try {
            $result = self::$billingService->verifyInvoice($params1);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('فاکتور منتظر تایید نمی باشد' , $error['message']);
        }

        try {
            $result = self::$billingService->verifyInvoice($params2);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('فاکتور منتظر تایید نمی باشد' , $error['message']);
        }
    }

    public function testVerifyInvoiceRequiredParameters()
    {
        $params1 =
            [
                ## ====================  Optional Parameters  =====================
                'id' => 55803,
            ];

        $params2 =
            [
                ## ====================  Optional Parameters  =====================
                'billNumber' => 'unitTest',
            ];

        try {
            $result = self::$billingService->verifyInvoice($params1);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('فاکتور منتظر تایید نمی باشد' , $error['message']);
        }

        try {
            $result = self::$billingService->verifyInvoice($params2);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('فاکتور منتظر تایید نمی باشد' , $error['message']);
        }
    }

    public function testVerifyInvoiceValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->verifyInvoice($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('id', $validation);
            $this->assertEquals('The property id is required', $validation['id'][0]);

            $this->assertArrayHasKey('billNumber', $validation);
            $this->assertEquals('The property billNumber is required', $validation['billNumber'][0]);

            $this->assertArrayHasKey('oneOf', $validation);
            $this->assertEquals('Failed to match exactly one schema', $validation['oneOf'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testVerifyAndCloseInvoiceAllParameters()
    {
        $params1 =
        [
        ## ====================  Optional Parameters  =====================
            'id' => 57019,
            'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
            'scApiKey'           => '{Put service call Api Key}',
        ];


        try {
            $result = self::$billingService->verifyAndCloseInvoice($params1);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('فاکتور منتظر تایید نمی باشد' , $error['message']);
        }

    }

    public function testVerifyAndCloseInvoiceRequiredParameters()
    {
        $params1 =
            [
                ## ====================  Optional Parameters  =====================
                'id' => 57019,
            ];

        try {
            $result = self::$billingService->verifyAndCloseInvoice($params1);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('فاکتور منتظر تایید نمی باشد' , $error['message']);
        }
    }

    public function testVerifyAndCloseInvoiceValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->verifyAndCloseInvoice($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('id', $validation);
            $this->assertEquals('The property id is required', $validation['id'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testCloseInvoiceAllParameters()
    {
        $params1 =
        [
        ## ====================  Optional Parameters  =====================
            'id' => 55803,
            'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
            'scApiKey'           => '{Put service call Api Key}',
        ];

        try {
            $result = self::$billingService->closeInvoice($params1);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('فاکتور پرداخت نشده است' , $error['message']);
        }


    }

    public function testCloseInvoiceRequiredParameters()
    {
        $params1 =
            [
                ## ====================  Optional Parameters  =====================
                'id' => 55803,
            ];

        try {
            $result = self::$billingService->closeInvoice($params1);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('فاکتور پرداخت نشده است' , $error['message']);
        }

    }

    public function testCloseInvoiceValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->closeInvoice($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('id', $validation);
            $this->assertEquals('The property id is required', $validation['id'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testCancelInvoiceAllParameters()
    {
        $params1 =
        [
            ## ============================ *Required Parameters  =========================
            'id' => 55803,
            ## ====================  Optional Parameters  =====================
            'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
            'scApiKey'           => '{Put service call Api Key}',
        ];

        try {
            $result = self::$billingService->cancelInvoice($params1);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('فاکتور قبلاً کنسل شده است' , $error['message']);
        }
    }

    public function testCancelInvoiceRequiredParameters()
    {
        $params1 =
            [
                ## ====================  Optional Parameters  =====================
                'id' => 55803,
            ];


        try {
            $result = self::$billingService->cancelInvoice($params1);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('فاکتور قبلاً کنسل شده است' , $error['message']);
        }
    }

    public function testCancelInvoiceValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->cancelInvoice($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('id', $validation);
            $this->assertEquals('The property id is required', $validation['id'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testReduceInvoiceAllParameters()
    {
        $params1 =
            [
                ## ============================ *Required Parameters  =========================
                'id' => 57019, # شناسه فاکتور
                'invoiceItemList' => [
                    [
                        'invoiceItemId' => 61598,    # شناسه بند فاکتور
                        'price' => 2000, # مبلغ بند فاکتور
                        'quantity' => 1,  # لیست تعداد محصول در هر بند فاکتور
                        'itemDescription' => 'reduce invoice', # لیست توضیحات بند فاکتور
                    ],
                    [
                        'invoiceItemId' => 61599,    # شناسه بند فاکتور
                        'price' => 2000, # مبلغ بند فاکتور
                        'quantity' => 1,  # لیست تعداد محصول در هر بند فاکتور
                        'itemDescription' => 'reduce invoice', # لیست توضیحات بند فاکتور
                    ]
                ],
                ## =========================== Optional Parameters  ===========================
                'preferredTaxRate' => 0.09, # نرخ مالیات برای این خرید که برای تمام آیتم های فاکتور اعمال می شود. اگر مقداری ارسال نشود مقدار مالیات بر ارزش افزوده پیش فرض محاسبه می شود
                'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
                'scApiKey'           => '{Put service call Api Key}',
        ];

        try {
            $result = self::$billingService->reduceInvoice($params1);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('فاکتور قبلاً کنسل شده است' , $error['message']);
        }
    }

    public function testReduceInvoiceRequiredParameters()
    {
        $params1 =
            [
                ## ============================ *Required Parameters  =========================
                'id' => 57019, # شناسه فاکتور
                'invoiceItemList' => [
                    [
                        'invoiceItemId' => 61598,    # شناسه بند فاکتور
                        'price' => 2000, # مبلغ بند فاکتور
                        'quantity' => 1,  # لیست تعداد محصول در هر بند فاکتور
                        'itemDescription' => 'reduce invoice', # لیست توضیحات بند فاکتور
                    ],
                    [
                        'invoiceItemId' => 61599,    # شناسه بند فاکتور
                        'price' => 2000, # مبلغ بند فاکتور
                        'quantity' => 1,  # لیست تعداد محصول در هر بند فاکتور
                        'itemDescription' => 'reduce invoice', # لیست توضیحات بند فاکتور
                    ]
                ]
            ];


        try {
            $result = self::$billingService->reduceInvoice($params1);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('فاکتور قبلاً کنسل شده است' , $error['message']);
        }
    }

    public function testReduceInvoiceValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->reduceInvoice($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('id', $validation);
            $this->assertEquals('The property id is required', $validation['id'][0]);

            $this->assertArrayHasKey('id', $validation);
            $this->assertEquals('The property invoiceItemList is required', $validation['invoiceItemList'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testSendInvoicePaymentSMSAllParameters()
    {
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'invoiceId'          => 55803 , # شناسه فاکتور
                ## =========================== Optional Parameters  ===========================
                'wallet'             => 'PODLAND_WALLET',
                'callbackUri'        => 'http://www.google.com',
                'redirectUri'        => 'http://www.google.com',
                'delegationId'       => [],
                'forceDelegation'    => true,
                'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
                'scApiKey'           => '{Put service call Api Key}',
            ];

        try {
            $result = self::$billingService->sendInvoicePaymentSMS($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }

    }

    public function testSendInvoicePaymentSMSRequiredParameters()
    {
        $params =
            [
            ## ============================ *Required Parameters  =========================
                'invoiceId'          => 55803 ,
            ];

        try {
            $result = self::$billingService->sendInvoicePaymentSMS($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testSendInvoicePaymentSMSValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->sendInvoicePaymentSMS($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('invoiceId', $validation);
            $this->assertEquals('The property invoiceId is required', $validation['invoiceId'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetInvoicePaymentLinkAllParameters()
    {
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'invoiceId'     => 57019,  # شناسه فاکتور
                ## =========================== Optional Parameters  ===========================
                'redirectUri'   => 'https://www.google.com',
                'callbackUri'   => '',      # The function that will be called at the end of payment
                'gateway'       => 'PEP',
                'scVoucherHash' => ['{Put Service Call Voucher Hashes}'],
                'scApiKey'      => '{Put service call Api Key}',
            ];

        try {
            $result = self::$billingService->getInvoicePaymentLink($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }

    }

    public function testGetInvoicePaymentLinkRequiredParameters()
    {
        $params =
            [
            ## ============================ *Required Parameters  =========================
                'invoiceId'          => 57019 ,
            ];

        try {
            $result = self::$billingService->getInvoicePaymentLink($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetInvoicePaymentLinkValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->getInvoicePaymentLink($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('invoiceId', $validation);
            $this->assertEquals('The property invoiceId is required', $validation['invoiceId'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetExportListAllParameters()
    {
        $params =
        [
            ## ============================ *Required Parameters  =========================
            'offset' => 0,
            'size' => 10,   # اندازه خروجی
            ## =========================== Optional Parameters  ==============================
            'id' => 1460,  # شناسه درخواست
            'statusCode' => ' EXPORT_SERVICE_STATUS_CREATED', # کد وضعیت
            'serviceUrl' => '/nzh/biz/getInvoiceList/', # آدرس سرویس
            'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
            'scApiKey'           => '{Put service call Api Key}',
        ];

        try {
            $result = self::$billingService->getExportList($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }

    }

    public function testGetExportListRequiredParameters()
    {
        $params =
            [
            ## ============================ *Required Parameters  =========================
                'offset' => 0,
                'size' => 10,   # اندازه خروجی
            ];

        try {
            $result = self::$billingService->getExportList($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetExportListValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->getExportList($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('offset', $validation);
            $this->assertEquals('The property offset is required', $validation['offset'][0]);

            $this->assertArrayHasKey('size', $validation);
            $this->assertEquals('The property size is required', $validation['size'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testRequestWalletSettlementAllParameters()
    {
        $ottResult = self::$commonService->getOtt([]);
        $this->assertFalse($ottResult['hasError']);
        $params =
            [
            ## ============================ *Required Parameters  =========================
            'ott'         => $ottResult['ott'] , # one time token - این توکن را در سرویس قبلی دریافت کرده اید.برای دریافت مجدد می توانید سرویس /nzh/ott/ را صدا کنید
            'amount'        => 1000, # مبلغ برداشت
            ## =========================== Optional Parameters  ===========================
            'wallet'        => 'PODLAND_WALLET',          # کد کیف پول
            'firstName'     => 'الهام',  # نام صاحب حسابی که تسویه به آن واریز می گردد
            'lastName'      => 'کشت گر',  # نام خانوادگی صاحب حسابی که تسویه به آن واریز می گردد
            'sheba'         => '980570100680013557234101',  # شماره شبا حسابی که تسویه به آن واریز می گردد
            'currencyCode'  => 'IRR',  # کد ارز پیش فرض IRR
            'uniqueId'      => uniqid('unitTest'),          # شناسه یکتا
            'description'   => 'requestWaltSettlement',          # شرح دلخواه
            'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
            'scApiKey'           => '{Put service call Api Key}',
            ];

        try {
            $result = self::$billingService->requestWalletSettlement($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }

    }

    public function testRequestWalletSettlementRequiredParameters()
    {
        $ottResult = self::$commonService->getOtt([]);
        $this->assertFalse($ottResult['hasError']);
        $params =
        [
            ## ============================ *Required Parameters  =========================
            'ott'         => $ottResult['ott'] , # one time token - این توکن را در سرویس قبلی دریافت کرده اید.برای دریافت مجدد می توانید سرویس /nzh/ott/ را صدا کنید
            'amount'        => 1000, # مبلغ برداشت
        ];

        try {
            $result = self::$billingService->requestWalletSettlement($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testRequestWalletSettlementValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->requestWalletSettlement($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('amount', $validation);
            $this->assertEquals('The property amount is required', $validation['amount'][0]);

            $this->assertArrayHasKey('ott', $validation);
            $this->assertEquals('The property ott is required', $validation['ott'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testRequestGuildSettlementAllParameters()
    {
        $ottResult = self::$commonService->getOtt([]);
        $this->assertFalse($ottResult['hasError']);
        $params =
            [
            ## ============================ *Required Parameters  =========================
            'ott'           => $ottResult['ott'] , # one time token - این توکن را در سرویس قبلی دریافت کرده اید.برای دریافت مجدد می توانید سرویس /nzh/ott/ را صدا کنید
            'amount'        => 1, # مبلغ برداش
            'guildCode'     => 'INFORMATION_TECHNOLOGY_GUILD',             # کد صنف
            ## =========================== Optional Parameters  ===========================
            'firstName'     => 'الهام',  # نام صاحب حسابی که تسویه به آن واریز می گردد
            'lastName'      => 'کشت گر',  # نام خانوادگی صاحب حسابی که تسویه به آن واریز می گردد
            'sheba'         => '980570100680013557234101',  # شماره شبا حسابی که تسویه به آن واریز می گردد
            'currencyCode'  => 'IRR',  # کد ارز پیش فرض IRR
            'uniqueId'      => uniqid('unitTest'),          # شناسه یکتا
            'description'   => 'requestWaltSettlement',          # شرح دلخواه
            'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
            'scApiKey'           => '{Put service call Api Key}',
            ];

        try {
            $result = self::$billingService->requestGuildSettlement($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }

    }

    public function testRequestGuildSettlementRequiredParameters()
    {
        $ottResult = self::$commonService->getOtt([]);
        $this->assertFalse($ottResult['hasError']);
        $params =
        [
            ## ============================ *Required Parameters  =========================
            'ott'           => $ottResult['ott'] , # one time token - این توکن را در سرویس قبلی دریافت کرده اید.برای دریافت مجدد می توانید سرویس /nzh/ott/ را صدا کنید
            'amount'        => 1, # مبلغ برداش
            'guildCode'     => 'INFORMATION_TECHNOLOGY_GUILD',             # کد صنف
        ];

        try {
            $result = self::$billingService->requestGuildSettlement($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testRequestGuildSettlementValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->requestGuildSettlement($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('amount', $validation);
            $this->assertEquals('The property amount is required', $validation['amount'][0]);

            $this->assertArrayHasKey('guildCode', $validation);
            $this->assertEquals('The property guildCode is required', $validation['guildCode'][0]);

            $this->assertArrayHasKey('ott', $validation);
            $this->assertEquals('The property ott is required', $validation['ott'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testRequestSettlementByToolAllParameters()
    {
        $ottResult = self::$commonService->getOtt([]);
        $this->assertFalse($ottResult['hasError']);
        $params =
        [
            ## ============================ *Required Parameters  =========================
            'ott'           => $ottResult['ott'] ,
            'amount'        => 100,      # مبلغ برداشت
            'guildCode'     => 'INFORMATION_TECHNOLOGY_GUILD',              # کد صنف
            'toolId'        => '5022291073765594',# شماره ابزاری که تسویه به آن واریز می گردد
            'toolCode'      => 'SETTLEMENT_TOOL_CARD',# نوع ابزار برای تسویه کارت به کارت،پایا،ساتنا
            # [SETTLEMENT_TOOL_SATNA | SETTLEMENT_TOOL_PAYA | SETTLEMENT_TOOL_CARD]
            ## =========================== Optional Parameters  ===========================
            'firstName'     => 'الهام',  # نام صاحب حسابی که تسویه به آن واریز می گردد
            'lastName'      => 'کشت گر',  # نام خانوادگی صاحب حسابی که تسویه به آن واریز می گردد
            'currencyCode'  => 'IRR',  # کد ارز پیش فرض IRR
            'uniqueId'      => uniqid('unitTest'),          # شناسه یکتا
            'description'   => 'requestWaltSettlement',          # شرح دلخواه
            'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
            'scApiKey'           => '{Put service call Api Key}',
        ];

        try {
            $result = self::$billingService->requestSettlementByTool($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }

    }

    public function testRequestSettlementByToolRequiredParameters()
    {
        $ottResult = self::$commonService->getOtt([]);
        $this->assertFalse($ottResult['hasError']);
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'ott'           => $ottResult['ott'] ,
                'amount'        => 100,      # مبلغ برداشت
                'guildCode'     => 'INFORMATION_TECHNOLOGY_GUILD',              # کد صنف
                'toolId'        => '5022291073765594',# شماره ابزاری که تسویه به آن واریز می گردد
                'toolCode'      => 'SETTLEMENT_TOOL_CARD',# نوع ابزار برای تسویه کارت به کارت،پایا،ساتنا
            ];


        try {
            $result = self::$billingService->requestSettlementByTool($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testRequestSettlementByToolValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->requestSettlementByTool($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('amount', $validation);
            $this->assertEquals('The property amount is required', $validation['amount'][0]);

            $this->assertArrayHasKey('guildCode', $validation);
            $this->assertEquals('The property guildCode is required', $validation['guildCode'][0]);

            $this->assertArrayHasKey('ott', $validation);
            $this->assertEquals('The property ott is required', $validation['ott'][0]);

            $this->assertArrayHasKey('toolId', $validation);
            $this->assertEquals('The property toolId is required', $validation['toolId'][0]);

            $this->assertArrayHasKey('toolCode', $validation);
            $this->assertEquals('The property toolCode is required', $validation['toolCode'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testListSettlementsAllParameters()
    {
        $ottResult = self::$commonService->getOtt([]);
        $this->assertFalse($ottResult['hasError']);
        $params =
        [
            ## ============================ *Required Parameters  =========================
            'offset'        => 0,      # pagination offset, default: 0
            'size'          => 10,        # اندازه خروجی
            ## =========================== Optional Parameters  ===========================
            'statusCode'    => 'SETTLEMENT_EXCEPTION_IN_SENDING',  # کد وضعیت درخواست "SETTLEMENT_DONE", "SETTLEMENT_REQUESTED", "SETTLEMENT_SENT", "SETTLEMENT_CANCELED", "SETTLEMENT_EXCEPTION_IN_SENDING", "SETTLEMENT_CONFIRMING"
            'invoiceId'     => 57019, # شناسه فاکتور
//            'id'            => 0, # شناسه درخواست
            'firstName'     => 'الهام',  # نام صاحب حسابی که تسویه به آن واریز می گردد
            'lastName'      => 'کشت گر',  # نام خانوادگی صاحب حسابی که تسویه به آن واریز می گردد
            'toolId'        => '5022291073765594',           # شماره ابزاری که تسویه به آن واریز می گردد
            'toolCode'      => 'SETTLEMENT_TOOL_CARD',# نوع ابزار برای تسویه کارت به کارت،پایا،ساتنا
            'currencyCode'  => 'IRR',  # کد ارز پیش فرض IRR
            'fromAmount'    => 1,  # حد پایین مبلغ درخواست شده
            'toAmount'      => 10000,  # حد بالای مبلغ درخواست شده
            'fromDate'      => '1398/01/01',  # حد پایین تاریخ درخواست شمسی yyyy/mm/dd
            'toDate'        => '1398/12/12',  # حد بالای تاریخ درخواست شمسی yyyy/mm/dd
            'uniqueId'      => '1234',          # شناسه یکتا
            'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
            'scApiKey'           => '{Put service call Api Key}',
        ];

        try {
            $result = self::$billingService->listSettlements($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }

    }

    public function testListSettlementsRequiredParameters()
    {
        $ottResult = self::$commonService->getOtt([]);
        $this->assertFalse($ottResult['hasError']);
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'offset'        => 0,      # pagination offset, default: 0
                'size'          => 10,        # اندازه خروجی
            ];


        try {
            $result = self::$billingService->listSettlements($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testListSettlementsValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->listSettlements($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('offset', $validation);
            $this->assertEquals('The property offset is required', $validation['offset'][0]);

            $this->assertArrayHasKey('size', $validation);
            $this->assertEquals('The property size is required', $validation['size'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testAddAutoSettlementAllParameters()
    {
        $ottResult = self::$commonService->getOtt([]);
        $this->assertFalse($ottResult['hasError']);
        $params =
        [
            ## ============================ *Required Parameters  =========================
            'guildCode'     => 'INFORMATION_TECHNOLOGY_GUILD',              # کد صنف
            # =========================== Optional Parameters  ===========================
            'firstName'     => 'الهام',  # نام صاحب حسابی که تسویه به آن واریز می گردد
            'lastName'      => 'کشت گر',  # نام خانوادگی صاحب حسابی که تسویه به آن واریز می گردد
            'currencyCode'  => 'IRR',  # کد ارز پیش فرض IRR
            'sheba'         => '980570100680013557234101',          # شماره شبا حسابی که تسویه به آن واریز می گردد
            'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
            'scApiKey'           => '{Put service call Api Key}',
        ];

        try {
            $result = self::$billingService->addAutoSettlement($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }

    }

    public function testAddAutoSettlementRequiredParameters()
    {
        $ottResult = self::$commonService->getOtt([]);
        $this->assertFalse($ottResult['hasError']);
        $params =
            [
                'guildCode'     => 'TOILETRIES_GUILD',              # کد صنف
            ];


        try {
            $result = self::$billingService->addAutoSettlement($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testAddAutoSettlementValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->addAutoSettlement($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('guildCode', $validation);
            $this->assertEquals('The property guildCode is required', $validation['guildCode'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testRemoveAutoSettlementAllParameters()
    {
        $ottResult = self::$commonService->getOtt([]);
        $this->assertFalse($ottResult['hasError']);
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'guildCode'     => 'INFORMATION_TECHNOLOGY_GUILD',              # کد صنف
                # =========================== Optional Parameters  ===========================
                'currencyCode'  => 'IRR',  # کد ارز پیش فرض IRR
                'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
                'scApiKey'           => '{Put service call Api Key}',
            ];

        try {
            $result = self::$billingService->removeAutoSettlement($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }

    }

    public function testRemoveAutoSettlementRequiredParameters()
    {
        $ottResult = self::$commonService->getOtt([]);
        $this->assertFalse($ottResult['hasError']);
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'guildCode'     => 'TOILETRIES_GUILD',              # کد صنف
            ];


        try {
            $result = self::$billingService->removeAutoSettlement($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testRemoveAutoSettlementValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->removeAutoSettlement($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('guildCode', $validation);
            $this->assertEquals('The property guildCode is required', $validation['guildCode'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetPayInvoiceByWalletLinkAllParameters()
    {
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'invoiceId'     => 3062025,
                ## =========================== Optional Parameters  ===========================
                'redirectUri'   => 'http://www.google.com',
                'callUri'       => 'test', # The function that will be called at the end of payment
            ];

        try {
            $result = self::$billingService->getPayInvoiceByWalletLink($params);
            $this->assertIsString($result);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }

    }

    public function testGetPayInvoiceByWalletLinkRequiredParameters()
    {
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'invoiceId'     => 3062025,
            ];

        try {
            $result = self::$billingService->getPayInvoiceByWalletLink($params);
            $this->assertIsString($result);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetPayInvoiceByWalletLinkValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->getPayInvoiceByWalletLink($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('invoiceId', $validation);
            $this->assertEquals('The property invoiceId is required', $validation['invoiceId'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetPayInvoiceByUniqueNumberLinkAllParameters()
    {
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'uniqueNumber'  => '9a1555a445ae3359',
                ## =========================== Optional Parameters  ===========================
                'redirectUri'   => 'http://www.google.com',
                'callUri'       => 'test', # The function that will be called at the end of payment
                'gateway'       => 'PEP',
            ];

        try {
            $result = self::$billingService->getPayInvoiceByUniqueNumberLink($params);
            $this->assertIsString($result);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }

    }

    public function testGetPayInvoiceByUniqueNumberLinkRequiredParameters()
    {
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'uniqueNumber'  => '9a1555a445ae3359',
            ];

        try {
            $result = self::$billingService->getPayInvoiceByUniqueNumberLink($params);
            $this->assertIsString($result);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetPayInvoiceByUniqueNumberLinkValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->getPayInvoiceByUniqueNumberLink($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('uniqueNumber', $validation);
            $this->assertEquals('The property uniqueNumber is required', $validation['uniqueNumber'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testIssueMultiInvoiceAllParameters()
    {
        $ottResult = self::$commonService->getOtt([]);
        $this->assertFalse($ottResult['hasError']);

        $params =
            [
                ## ============================ *Required Parameters  =========================
                'ott' => $ottResult['ott'] ,         # one time token - این توکن را در سرویس قبلی دریافت کرده اید.
                'data' =>
                    [  # رشته json حاوی اطلاعات فاکتورها
                        'redirectURL' => 'http:#www.google.com',
                        'userId' => 13380,
                        'currencyCode' => 'IRR',
//                        'voucherHashs' => [],               # اگر وجود ندارد این پارامتر ارسال نشود
                        'preferredTaxRate' => 0.08,
                        'verificationNeeded' => false,
                        'preview' => false,
                        'mainInvoice'=>                                 # فاکتور به نام خود معامله گر
                            [
                                'billNumber' => uniqid(),
                                'guildCode' => 'INFORMATION_TECHNOLOGY_GUILD',
                                'metadata' => '{"name":"testMainInvoice"}',
                                'description' => 'unitTestMainInvoice',
                                'invoiceItemVOs' =>            # بندهای فاکتور مربوط به سهم معامله گر
                                    [
                                        [
                                            'productId' => 0,
                                            'price' => 100,
                                            'quantity' => 1,
                                            'description' => 'unitTestMainInvoice'
                                        ],
                                    ],
                            ],
                        'subInvoices' =>            # فاکتورهای مربوط به سهم سایر کسب و کارهای ذینفع
                            [
                                [
                                    'businessId' => 4821,
                                    'guildCode' => 'INFORMATION_TECHNOLOGY_GUILD',
                                    'billNumber' => uniqid(),
                                    'metadata' => '{"name":"subInvoiceTest"}',
                                    'description' => 'unitTestSubInvoice',
                                    'invoiceItemVOs' =>       # بندهای فاکتور کسب و کار ذینفع
                                        [
                                            [
                                                'productId' => 0,
                                                'price' => 100,
                                                'quantity' => 1,
                                                'description' => 'unitTestSubInvoice'
                                            ],
                                        ],
                                ]
                            ],
                        'customerDescription' => 'customerDescription',
                        'customerMetadata' => '{"name":"CustomerTest"}',
                        'customerInvoiceItemVOs' =>         # بندهایی که به مشتری نمایش داده می شوند
                            [
                                [
                                    'productId' => 0,
                                    'price' => 100,
                                    'quantity' => 2,
                                    'description' => 'Hello'
                                ]
                            ]
                    ],

                ## =========================== Optional Parameters  ===============================
                'delegatorId'       => [3605,3612],            # شناسه تفویض کنندگان، ترتیب اولویت را مشخص می کند
                'delegationHash'    => ['dsd','edrfrd'],            # کد تفویض برای اشاره به یک تفویض مشخص
                'forceDelegation'   => false,              # پرداخت فقط از طریق تفویض
            'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
            'scApiKey'           => '{Put service call Api Key}',

        ];

        try {
            $result = self::$billingService->issueMultiInvoice($params);
            $this->assertFalse($result['hasError']);

        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testIssueMultiInvoiceRequiredParameters()
    {
        $ottResult = self::$commonService->getOtt([]);
        $this->assertFalse($ottResult['hasError']);
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'ott' => $ottResult['ott'] ,         # one time token - این توکن را در سرویس قبلی دریافت کرده اید.
                'data' =>
                    [  # رشته json حاوی اطلاعات فاکتورها
                        'mainInvoice'=>                                 # فاکتور به نام خود معامله گر
                            [
                                'guildCode' => 'TOILETRIES_GUILD',
                                'invoiceItemVOs' =>            # بندهای فاکتور مربوط به سهم معامله گر
                                    [
                                        [
                                            'productId' => 0,
                                            'price' => 100,
                                            'quantity' => 1,
                                            'description' => 'ss  '
                                        ],
                                    ],
                            ],
                        'subInvoices' =>            # فاکتورهای مربوط به سهم سایر کسب و کارهای ذینفع
                            [
                                [
                                    'businessId' => 4821,
                                    'guildCode' => 'INFORMATION_TECHNOLOGY_GUILD',
                                    'description' => 'test1',
                                    'invoiceItemVOs' =>       # بندهای فاکتور کسب و کار ذینفع
                                        [
                                            [
                                                'productId' => 0,
                                                'price' => 100,
                                                'quantity' => 1,
                                                'description' => 'Hello'
                                            ],
                                        ],
                                ]
                            ],
                        'customerInvoiceItemVOs' =>         # بندهایی که به مشتری نمایش داده می شوند
                            [
                                [
                                    'productId' => 0,
                                    'price' => 100,
                                    'quantity' => 2,
                                    'description' => 'Hello'
                                ]
                            ]
                    ]
        ];
        try {
            $result = self::$billingService->issueMultiInvoice($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testIssueMultiInvoiceValidationError()
    {
        $ottResult = self::$commonService->getOtt([]);
        $this->assertFalse($ottResult['hasError']);

        $paramsWithoutRequired = [];
        try {
            self::$billingService->issueMultiInvoice($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('data', $validation);
            $this->assertEquals('The property data is required', $validation['data'][0]);

            $this->assertArrayHasKey('ott', $validation);
            $this->assertEquals('The property ott is required', $validation['ott'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testReduceMultiInvoiceAllParameters()
    {
        // first get ott
        try {
            $ottResult = self::$commonService->getOtt([]);
            $this->assertFalse($ottResult['hasError']);
            // then create a shared invoice
            $multiInvoiceParams =
                [
                    ## ============================ *Required Parameters  =========================
                    'ott' => $ottResult['ott'] ,         # one time token - این توکن را در سرویس قبلی دریافت کرده اید.
                    'data' =>
                        [  # رشته json حاوی اطلاعات فاکتورها
                            'mainInvoice'=>                                 # فاکتور به نام خود معامله گر
                                [
                                    'guildCode' => 'TOILETRIES_GUILD',
                                    'invoiceItemVOs' =>            # بندهای فاکتور مربوط به سهم معامله گر
                                        [
                                            [
                                                'productId' => 0,
                                                'price' => 100,
                                                'quantity' => 1,
                                                'description' => 'ss  '
                                            ],
                                        ],
                                ],
                            'subInvoices' =>            # فاکتورهای مربوط به سهم سایر کسب و کارهای ذینفع
                                [
                                    [
                                        'businessId' => 4821,
                                        'guildCode' => 'INFORMATION_TECHNOLOGY_GUILD',
                                        'description' => 'test1',
                                        'invoiceItemVOs' =>       # بندهای فاکتور کسب و کار ذینفع
                                            [
                                                [
                                                    'productId' => 0,
                                                    'price' => 100,
                                                    'quantity' => 1,
                                                    'description' => 'Hello'
                                                ],
                                            ],
                                    ]
                                ],
                            'customerInvoiceItemVOs' =>         # بندهایی که به مشتری نمایش داده می شوند
                                [
                                    [
                                        'productId' => 0,
                                        'price' => 100,
                                        'quantity' => 2,
                                        'description' => 'Hello'
                                    ]
                                ]
                        ]
                ];
            $issueMultiInvoiceResult = self::$billingService->issueMultiInvoice($multiInvoiceParams);
            $this->assertFalse($issueMultiInvoiceResult['hasError']);
            // finally reduce the shared invoice
            $params =
                [
                    ## ============================ *Required Parameters  =========================
                    'data' =>
                        [
                            'preferredTaxRate' => 0.09,            # tax to be added between 0 and 1 default is 0.09
                            'mainInvoice' =>                             # فاکتور به نام خود معامله گر
                                [
                                    'id' => $issueMultiInvoiceResult['result']['id'],                # id of main invoice to be edited
                                    'reduceInvoiceItemVOs' =>       # بندهای فاکتور مربوط به سهم معامله گر
                                        [
                                            [
                                                'id' => $issueMultiInvoiceResult['result']['invoiceItemSrvs'][0]['id'],              # the id of item in invoice
                                                'price' => 90,           # the share of dealer
                                                'quantity' => 1,
                                                'description' => 'ss'
                                            ]
                                        ]
                                ],
                            'subInvoices' =>            # فاکتورهای مربوط به سهم سایر کسب و کارهای ذینفع
                                [
                                    [
                                        'id' => $issueMultiInvoiceResult['result']['subInvoices'][0]['id'],
                                        'reduceInvoiceItemVOs' =>       # بندهای فاکتور مربوط به سهم ذینفعان
                                            [
                                                [
                                                    'id' =>  $issueMultiInvoiceResult['result']['subInvoices'][0]['invoiceItemSrvs'][0]['id'],
                                                    'price' => 90,
                                                    'quantity' => 1,
                                                    'description' => 'Hello'
                                                ]
                                            ]
                                    ]
                                ],
                            'customerInvoiceItemVOs' =>         # بندهایی که به مشتری نمایش داده می شوند
                                [
                                    [
                                        'id' => $issueMultiInvoiceResult['result']['customerInvoice']['invoiceItemSrvs'][0]['id'],
                                        'price' => 90,
                                        'quantity' => 2,
                                        'description' => 'Hello'
                                    ]

                                ],
                        ],
                    'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
                    'scApiKey'           => '{Put service call Api Key}',
                ];

            $result = self::$billingService->reduceMultiInvoice($params);
            $this->assertFalse($result['hasError']);

        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testReduceMultiInvoiceValidationError()
    {
        $ottResult = self::$commonService->getOtt([]);
        $this->assertFalse($ottResult['hasError']);

        $paramsWithoutRequired = [];
        try {
            self::$billingService->reduceMultiInvoice($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('data', $validation);
            $this->assertEquals('The property data is required', $validation['data'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testReduceMultiInvoiceAndCashOutAllParameters()
    {
        // first get ott
        try {
            $ottResult = self::$commonService->getOtt([]);
            $this->assertFalse($ottResult['hasError']);
            // then create a shared invoice
            $multiInvoiceParams =
                [
                    ## ============================ *Required Parameters  =========================
                    'ott' => $ottResult['ott'] ,         # one time token - این توکن را در سرویس قبلی دریافت کرده اید.
                    'data' =>
                        [  # رشته json حاوی اطلاعات فاکتورها
                            'mainInvoice'=>                                 # فاکتور به نام خود معامله گر
                                [
                                    'guildCode' => 'TOILETRIES_GUILD',
                                    'invoiceItemVOs' =>            # بندهای فاکتور مربوط به سهم معامله گر
                                        [
                                            [
                                                'productId' => 0,
                                                'price' => 100,
                                                'quantity' => 1,
                                                'description' => 'ss  '
                                            ],
                                        ],
                                ],
                            'subInvoices' =>            # فاکتورهای مربوط به سهم سایر کسب و کارهای ذینفع
                                [
                                    [
                                        'businessId' => 4821,
                                        'guildCode' => 'INFORMATION_TECHNOLOGY_GUILD',
                                        'description' => 'test1',
                                        'invoiceItemVOs' =>       # بندهای فاکتور کسب و کار ذینفع
                                            [
                                                [
                                                    'productId' => 0,
                                                    'price' => 100,
                                                    'quantity' => 1,
                                                    'description' => 'Hello'
                                                ],
                                            ],
                                    ]
                                ],
                            'customerInvoiceItemVOs' =>         # بندهایی که به مشتری نمایش داده می شوند
                                [
                                    [
                                        'productId' => 0,
                                        'price' => 100,
                                        'quantity' => 2,
                                        'description' => 'Hello'
                                    ]
                                ]
                        ]
                ];
            $issueMultiInvoiceResult = self::$billingService->issueMultiInvoice($multiInvoiceParams);
            $this->assertFalse($issueMultiInvoiceResult['hasError']);
            // finally reduce the shared invoice
            $params =
                [
                    ## ============================ *Required Parameters  =========================
                    'data' =>
                        [
                            'preferredTaxRate' => 0.09,            # tax to be added between 0 and 1 default is 0.09
                            'mainInvoice' =>                             # فاکتور به نام خود معامله گر
                                [
                                    'id' => $issueMultiInvoiceResult['result']['id'],                # id of main invoice to be edited
                                    'reduceInvoiceItemVOs' =>       # بندهای فاکتور مربوط به سهم معامله گر
                                        [
                                            [
                                                'id' => $issueMultiInvoiceResult['result']['invoiceItemSrvs'][0]['id'],              # the id of item in invoice
                                                'price' => 90,           # the share of dealer
                                                'quantity' => 1,
                                                'description' => 'ss'
                                            ]
                                        ]
                                ],
                            'subInvoices' =>            # فاکتورهای مربوط به سهم سایر کسب و کارهای ذینفع
                                [
                                    [
                                        'id' => $issueMultiInvoiceResult['result']['subInvoices'][0]['id'],
                                        'reduceInvoiceItemVOs' =>       # بندهای فاکتور مربوط به سهم ذینفعان
                                            [
                                                [
                                                    'id' =>  $issueMultiInvoiceResult['result']['subInvoices'][0]['invoiceItemSrvs'][0]['id'],
                                                    'price' => 90,
                                                    'quantity' => 1,
                                                    'description' => 'Hello'
                                                ]
                                            ]
                                    ]
                                ],
                            'customerInvoiceItemVOs' =>         # بندهایی که به مشتری نمایش داده می شوند
                                [
                                    [
                                        'id' => $issueMultiInvoiceResult['result']['customerInvoice']['invoiceItemSrvs'][0]['id'],
                                        'price' => 90,
                                        'quantity' => 2,
                                        'description' => 'Hello'
                                    ]

                                ],
                        ],
                    ## ============================ Optional Parameters  ==========================
                    'toolCode' => 'SETTLEMENT_TOOL_PAYA',
                    'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
                    'scApiKey'           => '{Put service call Api Key}',
                ];

            $result = self::$billingService->reduceMultiInvoiceAndCashOut($params);
            $this->assertFalse($result['hasError']);

        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testReduceMultiInvoiceAndCashOutRequiredParameters()
    {
        // first get ott
        try {
            $ottResult = self::$commonService->getOtt([]);
            $this->assertFalse($ottResult['hasError']);
            // then create a shared invoice
            $multiInvoiceParams =
            [
                    ## ============================ *Required Parameters  =========================
                    'ott' => $ottResult['ott'] ,         # one time token - این توکن را در سرویس قبلی دریافت کرده اید.
                    'data' =>
                        [  # رشته json حاوی اطلاعات فاکتورها
                            'mainInvoice'=>                                 # فاکتور به نام خود معامله گر
                                [
                                    'guildCode' => 'TOILETRIES_GUILD',
                                    'invoiceItemVOs' =>            # بندهای فاکتور مربوط به سهم معامله گر
                                        [
                                            [
                                                'productId' => 0,
                                                'price' => 100,
                                                'quantity' => 1,
                                                'description' => 'ss  '
                                            ],
                                        ],
                                ],
                            'subInvoices' =>            # فاکتورهای مربوط به سهم سایر کسب و کارهای ذینفع
                                [
                                    [
                                        'businessId' => 4821,
                                        'guildCode' => 'INFORMATION_TECHNOLOGY_GUILD',
                                        'description' => 'test1',
                                        'invoiceItemVOs' =>       # بندهای فاکتور کسب و کار ذینفع
                                            [
                                                [
                                                    'productId' => 0,
                                                    'price' => 100,
                                                    'quantity' => 1,
                                                    'description' => 'Hello'
                                                ],
                                            ],
                                    ]
                                ],
                            'customerInvoiceItemVOs' =>         # بندهایی که به مشتری نمایش داده می شوند
                                [
                                    [
                                        'productId' => 0,
                                        'price' => 100,
                                        'quantity' => 2,
                                        'description' => 'Hello'
                                    ]
                                ]
                        ]
                ];
            $issueMultiInvoiceResult = self::$billingService->issueMultiInvoice($multiInvoiceParams);
            $this->assertFalse($issueMultiInvoiceResult['hasError']);
            // finally reduce the shared invoice
            $params =
            [
                    ## ============================ *Required Parameters  =========================
                    'data' =>
                        [
                            'preferredTaxRate' => 0.09,            # tax to be added between 0 and 1 default is 0.09
                            'mainInvoice' =>                             # فاکتور به نام خود معامله گر
                                [
                                    'id' => $issueMultiInvoiceResult['result']['id'],                # id of main invoice to be edited
                                    'reduceInvoiceItemVOs' =>       # بندهای فاکتور مربوط به سهم معامله گر
                                        [
                                            [
                                                'id' => $issueMultiInvoiceResult['result']['invoiceItemSrvs'][0]['id'],              # the id of item in invoice
                                                'price' => 90,           # the share of dealer
                                                'quantity' => 1,
                                                'description' => 'ss'
                                            ]
                                        ]
                                ],
                            'subInvoices' =>            # فاکتورهای مربوط به سهم سایر کسب و کارهای ذینفع
                                [
                                    [
                                        'id' => $issueMultiInvoiceResult['result']['subInvoices'][0]['id'],
                                        'reduceInvoiceItemVOs' =>       # بندهای فاکتور مربوط به سهم ذینفعان
                                            [
                                                [
                                                    'id' =>  $issueMultiInvoiceResult['result']['subInvoices'][0]['invoiceItemSrvs'][0]['id'],
                                                    'price' => 90,
                                                    'quantity' => 1,
                                                    'description' => 'Hello'
                                                ]
                                            ]
                                    ]
                                ],
                            'customerInvoiceItemVOs' =>         # بندهایی که به مشتری نمایش داده می شوند
                                [
                                    [
                                        'id' => $issueMultiInvoiceResult['result']['customerInvoice']['invoiceItemSrvs'][0]['id'],
                                        'price' => 90,
                                        'quantity' => 2,
                                        'description' => 'Hello'
                                    ]

                                ],
                        ]
                ];

            $result = self::$billingService->reduceMultiInvoiceAndCashOut($params);
            $this->assertFalse($result['hasError']);

        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }

    }

    public function testReduceMultiInvoiceAndCashOutValidationError()
    {
        $ottResult = self::$commonService->getOtt([]);
        $this->assertFalse($ottResult['hasError']);

        $paramsWithoutRequired = [];

        try {
            self::$billingService->reduceMultiInvoiceAndCashOut($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('data', $validation);
            $this->assertEquals('The property data is required', $validation['data'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testDefineCreditVoucherAllParameters()
    {
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'guildCode'     => "INFORMATION_TECHNOLOGY_GUILD",
                'expireDate'   => "1499/12/29",
                'vouchers'      => [
                    [
                        'count' => 1,
                        'amount' => 100,
                        'name' => 'بن تخفیف',
                        'description' => 'بن تخفیف ۱۰ تومانی',
                        ## ============= Optional Parameters  ==============
                        'hash'      => [uniqid('unitTestHash')]
                    ],
//                [
//                    'count' => 1,
//                    'amount' => 1,
//                    'name' => 'بن تخفیف',
//                    'description' => 'بن تخفیف ۱۰ تومانی',
//                    ## ============= Optional Parameters  ==============
////                    'hash'      => ['']
//                ]
                ],
                ## =========================== Optional Parameters  ==========================
            'limitedConsumerId' => 16849,
            'currencyCode'      => 'IRR',
            'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
            'scApiKey'          => '{Put service call Api Key}',
        ];

        try {
            $result = self::$billingService->defineCreditVoucher($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }

    }

    public function testDefineCreditVoucherRequiredParameters()
    {
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'guildCode'     => "INFORMATION_TECHNOLOGY_GUILD",
                'expireDate'   => "1498/12/29",
                'vouchers'      => [
                    [
                        'count' => 1,
                        'amount' => 100,
                        'name' => 'بن تخفیف',
                        'description' => 'بن تخفیف ۱۰ تومانی',
                    ],
                ],
        ];

        try {
            $result = self::$billingService->defineCreditVoucher($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testDefineCreditVoucherValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->defineCreditVoucher($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('guildCode', $validation);
            $this->assertEquals('The property guildCode is required', $validation['guildCode'][0]);

            $this->assertArrayHasKey('expireDate', $validation);
            $this->assertEquals('The property expireDate is required', $validation['expireDate'][0]);

            $this->assertArrayHasKey('vouchers', $validation);
            $this->assertEquals('The property vouchers is required', $validation['vouchers'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testDefineDiscountAmountVoucherAllParameters()
    {
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'guildCode'     => "INFORMATION_TECHNOLOGY_GUILD",
                'expireDate'   => "1498/12/29",
                'vouchers'      => [
                    [
                        'count' => 1,
                        'amount' => 100,
                        'name' => 'بن تخفیف',
                        'description' => 'بن تخفیف ۱۰ تومانی',
                        ## ============= Optional Parameters  ==============
                    'hash'      => [uniqid('unitTest')]
                    ],
//                [
//                    'count' => 1,
//                    'amount' => 1,
//                    'name' => 'بن تخفیف',
//                    'description' => 'بن تخفیف ۱۰ تومانی',
//                    ## ============= Optional Parameters  ==============
////                    'hash'      => ['']
//                ]
                ],
                ## =========================== Optional Parameters  ===========================
            'productId' => [36062, 35881],
            'dealerBusinessId' => [12121],
            'limitedConsumerId' => 16849,
            'currencyCode'      => 'IRR',
//            'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
//            'scApiKey'           => '{Put service call Api Key}',

        ];

        try {
            $result = self::$billingService->defineDiscountAmountVoucher($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }

    }

    public function testDefineDiscountAmountVoucherRequiredParameters()
    {
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'guildCode'     => "INFORMATION_TECHNOLOGY_GUILD",
                'expireDate'   => "1498/12/29",
                'vouchers'      => [
                    [
                        'count' => 1,
                        'amount' => 100,
                        'name' => 'بن تخفیف',
                        'description' => 'بن تخفیف ۱۰ تومانی',
                    ],
                ],
        ];

        try {
            $result = self::$billingService->defineDiscountAmountVoucher($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testDefineDiscountAmountVoucherValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->defineDiscountAmountVoucher($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('guildCode', $validation);
            $this->assertEquals('The property guildCode is required', $validation['guildCode'][0]);

            $this->assertArrayHasKey('expireDate', $validation);
            $this->assertEquals('The property expireDate is required', $validation['expireDate'][0]);

            $this->assertArrayHasKey('vouchers', $validation);
            $this->assertEquals('The property vouchers is required', $validation['vouchers'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testDefineDiscountPercentageVoucherAllParameters()
    {
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'guildCode'     => "INFORMATION_TECHNOLOGY_GUILD",
                'expireDate'    => "1498/12/29",
                "type"          => 4,
                'vouchers'      => [
                    [
                        'count' => 1,
                        'discountPercentage' => 10,
                        'name'  => 'بن تخفیف',
                        'description' => 'بن تخفیف ۱۰ تومانی',
                        ## ============= Optional Parameters  ==============
                        'hash'      => [uniqid('unitTest')],
                        'amount' => 100,
                    ],
//                    [
//                        'count' => 1,
//                        'discountPercentage' => 10,
//                        'name'  => 'بن تخفیف',
//                        'description' => 'بن تخفیف ۱۰ تومانی',
//                        ## ============= Optional Parameters  ==============
////                    'hash'      => [''],
//                        'amount' => 100,
//                    ]
                ],
                ## =========================== Optional Parameters  ===========================
            'productId' => [36062, 35881],
            'dealerBusinessId' => [12121],
            'limitedConsumerId' => 16849,
            'currencyCode'      => 'IRR',
            'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
            'scApiKey'           => '{Put service call Api Key}',

        ];

        try {
            $result = self::$billingService->defineDiscountPercentageVoucher($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }

    }

    public function testDefineDiscountPercentageVoucherRequiredParameters()
    {
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'guildCode'     => "INFORMATION_TECHNOLOGY_GUILD",
                'expireDate'    => "1498/12/29",
                "type"          => 4,
                'vouchers'      => [
                    [
                        'count' => 1,
                        'discountPercentage' => 10,
                        'name'  => 'بن تخفیف',
                        'description' => 'بن تخفیف ۱۰ تومانی',

                    ]
                ],
        ];

        try {
            $result = self::$billingService->defineDiscountPercentageVoucher($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testDefineDiscountPercentageVoucherValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->defineDiscountPercentageVoucher($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('guildCode', $validation);
            $this->assertEquals('The property guildCode is required', $validation['guildCode'][0]);

            $this->assertArrayHasKey('expireDate', $validation);
            $this->assertEquals('The property expireDate is required', $validation['expireDate'][0]);

            $this->assertArrayHasKey('type', $validation);
            $this->assertEquals('The property type is required', $validation['type'][0]);

            $this->assertArrayHasKey('vouchers', $validation);
            $this->assertEquals('The property vouchers is required', $validation['vouchers'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testApplyVoucherAllAndRequiredParameters()
    {
        // first get ott
        try {
            $ottResult = self::$commonService->getOtt([]);
            $this->assertFalse($ottResult['hasError']);

            // then create an invoice
            $invoiceParams =
                [
                    ## ============================ *Required Parameters  =========================
                    'productList'   	=> [
                        [
                            # شناسه محصول . در صورتی که بند فاکتور محصول مرتبط ندارد مقدار آن 0 وارد شود
                            'productId'         => 0,
                            # مبلغ بند فاکتور. برای استفاده از قیمت محصول وارد شده از مقدار auto استفاده نمایید
                            'price'             => 100,
                            #تعداد محصول
                            'quantity'          => 1,
                            # توضیحات
                            'productDescription'=> 'unit test issue invoice',
                        ],
                        // اطلاعات محصولات دیگر
                    ],
                    'guildCode'			=> 'INFORMATION_TECHNOLOGY_GUILD', # *Required
                    'ott' 				=> $ottResult['ott'],
                ];
            $issueInvoiceResult = self::$billingService->issueInvoice($invoiceParams);
            $this->assertFalse($issueInvoiceResult['hasError']);
            $params1 =
                [
                    ## ============================ *Required Parameters  =========================
                    'invoiceId'     => $issueInvoiceResult['result']['id'],
                    'voucherHash'    => ["JONS614FHTF3"],
                    'ott'     => 'dfada511dc94caa2',
                    ## =========================== Optional Parameters  ===========================
                    'preview'          => true,
                    'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
                    'scApiKey'           => '{Put service call Api Key}',
                ];
            $params2 =
                [
                    ## ============================ *Required Parameters  =========================
                    'invoiceId'     => $issueInvoiceResult['result']['id'],
                    'voucherHash'    => ["JONS614FHTF3"],
                    'ott'     => 'dfada511dc94caa2',
                    ## =========================== Optional Parameters  ===========================
                    'preview'          => true,
                    'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
                    'scApiKey'           => '{Put service call Api Key}',
                ];

            $result1 = self::$billingService->applyVoucher($params1);
            $this->assertFalse($result1['hasError']);
            $result2 = self::$billingService->applyVoucher($params2);
            $this->assertFalse($result2['hasError']);

        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }

    }

    public function testApplyVoucherValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->applyVoucher($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('invoiceId', $validation);
            $this->assertEquals('The property invoiceId is required', $validation['invoiceId'][0]);

            $this->assertArrayHasKey('voucherHash', $validation);
            $this->assertEquals('The property voucherHash is required', $validation['voucherHash'][0]);

            $this->assertArrayHasKey('ott', $validation);
            $this->assertEquals('The property ott is required', $validation['ott'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetVoucherListAllParameters()
    {
        $params =
            [
            ## ============================ *Required Parameters  =========================
            'offset'     => 0,
            ## =========================== Optional Parameters  ===========================
            'size'          => 1,
            'productId'     => [36062],
            'guildCode'     => ["TOURISM_GUILD"],
            'hash'          => "JONS614FHTF3",
            'consumerId'    => 16849,
            'type'    => 4,
            'currencyCode'    => 'IRR',
            'amountFrom'    => 1,
            'amountTo'    => 10000,
            'discountPercentageFrom'    => 1,
            'discountPercentageTo'    => 100,
            'expireDateFrom'    => '1490/12/10',
            'expireDateTo'    => '1398/12/10',
            'consumDateFrom'    => "1398/01/01",
            'consumDateTo'    => "1498/12/01",
            'usedAmountFrom'    => 1,
            'active'    => true,
            'used'    => true,
            'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
            'scApiKey'           => '{Put service call Api Key}',

        ];

        try {
            $result = self::$billingService->getVoucherList($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }

    }

    public function testGetVoucherListRequiredParameters()
    {
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'offset'     => 0,
            ];

        try {
            $result = self::$billingService->getVoucherList($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetVoucherListValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->getVoucherList($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('offset', $validation);
            $this->assertEquals('The property offset is required', $validation['offset'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testDeactivateVoucherAllAndRequiredParameters()
    {
        $voucherParams =
            [
                ## ============================ *Required Parameters  =========================
                'guildCode'     => "INFORMATION_TECHNOLOGY_GUILD",
                'expireDate'   => "1498/12/29",
                'vouchers'      => [
                    [
                        'count' => 1,
                        'amount' => 100,
                        'name' => 'بن تخفیف 1 ',
                        'description' => 'بن تخفیف ۱۰ تومانی',
                    ],
                    [
                        'count' => 1,
                        'amount' => 200,
                        'name' => 'بن تخفیف 2 ',
                        'description' => 'بن تخفیف ۱۰ تومانی',
                    ],
                ],
            ];

        try {
            $voucherResult = self::$billingService->defineDiscountAmountVoucher($voucherParams);
            $this->assertFalse($voucherResult['hasError']);
            $allParams =
                [
                    ## ============================ *Required Parameters  =========================
                    'id'     => $voucherResult['result'][0]['id'],
                    ## =========================== Optional Parameters  ===========================
                    'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
                    'scApiKey'           => '{Put service call Api Key}',
                ];
            $result = self::$billingService->deactivateVoucher($allParams);
            $this->assertFalse($result['hasError']);

            $requiredParams =
                [
                    ## ============================ *Required Parameters  =========================
                    'id'     => $voucherResult['result'][1]['id'],
                ];
            $result = self::$billingService->deactivateVoucher($requiredParams);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testDeactivateVoucherValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->deactivateVoucher($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('id', $validation);
            $this->assertEquals('The property id is required', $validation['id'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testActivateVoucherAllAndRequiredParameters()
    {
        $voucherParams =
            [
                ## ============================ *Required Parameters  =========================
                'guildCode'     => "INFORMATION_TECHNOLOGY_GUILD",
                'expireDate'   => "1498/12/29",
                'vouchers'      => [
                    [
                        'count' => 1,
                        'amount' => 100,
                        'name' => 'بن تخفیف 1 ',
                        'description' => 'بن تخفیف ۱۰ تومانی',
                    ],
                    [
                        'count' => 1,
                        'amount' => 200,
                        'name' => 'بن تخفیف 2 ',
                        'description' => 'بن تخفیف ۱۰ تومانی',
                    ],
                ],
            ];

        try {
            $voucherResult = self::$billingService->defineDiscountAmountVoucher($voucherParams);
            $this->assertFalse($voucherResult['hasError']);
            $allParams =
                [
                    ## ============================ *Required Parameters  =========================
                    'id'     => $voucherResult['result'][0]['id'],
                    ## =========================== Optional Parameters  ===========================
                    'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
                    'scApiKey'           => '{Put service call Api Key}',
                ];
            $result = self::$billingService->activateVoucher($allParams);
            $this->assertFalse($result['hasError']);

            $requiredParams =
                [
                    ## ============================ *Required Parameters  =========================
                    'id'     => $voucherResult['result'][1]['id'],
                ];
            $result = self::$billingService->activateVoucher($requiredParams);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }

    }

    public function testActivateVoucherValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->activateVoucher($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('id', $validation);
            $this->assertEquals('The property id is required', $validation['id'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testDefineDirectWithdrawAllParameters()
    {
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'username'      => "keshtgar",
                'privateKey'    => $this->privateKey,
                'depositNumber' => "87706",
                'onDemand'      => true,
                'minAmount'     => 10,
                'maxAmount'     => 10,
                'wallet'        => "PODLAND_WALLET",
                ## =========================== Optional Parameters  ===========================
                'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
                'scApiKey'           => '{Put service call Api Key}',
            ];

        try {
            $result = self::$billingService->defineDirectWithdraw($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }

    }

    public function testDefineDirectWithdrawRequiredParameters()
    {
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'username'      => "keshtgar",
                'privateKey'    => $this->privateKey,
                'depositNumber' => "87706",
                'onDemand'      => true,
                'minAmount'     => 10,
                'maxAmount'     => 10,
                'wallet'        => "PODLAND_WALLET",
            ];

        try {
            $result = self::$billingService->defineDirectWithdraw($params);
            $this->assertTrue($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->assertEquals('برداشت از سپرده برای این کیف پول قبلا فعال شده است' , $error['message']);
        }
    }

    public function testDefineDirectWithdrawValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->defineDirectWithdraw($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();

            $this->assertArrayHasKey('username', $validation);
            $this->assertEquals('The property username is required', $validation['username'][0]);

            $this->assertArrayHasKey('depositNumber', $validation);
            $this->assertEquals('The property depositNumber is required', $validation['depositNumber'][0]);

            $this->assertArrayHasKey('privateKey', $validation);
            $this->assertEquals('The property privateKey is required', $validation['privateKey'][0]);

            $this->assertArrayHasKey('onDemand', $validation);
            $this->assertEquals('The property onDemand is required', $validation['onDemand'][0]);

            $this->assertArrayHasKey('minAmount', $validation);
            $this->assertEquals('The property minAmount is required', $validation['minAmount'][0]);

            $this->assertArrayHasKey('maxAmount', $validation);
            $this->assertEquals('The property maxAmount is required', $validation['maxAmount'][0]);

            $this->assertArrayHasKey('wallet', $validation);
            $this->assertEquals('The property wallet is required', $validation['wallet'][0]);
            $this->assertEquals(887, $result['code']);

        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testDirectWithdrawListAllParameters()
    {
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'offset'        => 0,
                ## =========================== Optional Parameters  ===========================
                'wallet'        => "PODLAND_WALLET",
                'size'          => 1,
                'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
                'scApiKey'           => '{Put service call Api Key}',

            ];

        try {
            $result = self::$billingService->directWithdrawList($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }

    }

    public function testDirectWithdrawListRequiredParameters()
    {
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'offset'        => 0,
                'size'          => 1,
            ];

        try {
            $result = self::$billingService->directWithdrawList($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testDirectWithdrawListValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->directWithdrawList($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('offset', $validation);
            $this->assertEquals('The property offset is required', $validation['offset'][0]);

            $this->assertArrayHasKey('size', $validation);
            $this->assertEquals('The property size is required', $validation['size'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testUpdateDirectWithdrawAllParameters()
    {
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'id'            => 3,
                'username'      => "123",
                'privateKey'    => $this->privateKey,
                'depositNumber' => 87706,
                'onDemand'      => false,
                'minAmount'     => 10,
                'maxAmount'     => 10,
                'wallet'        => "PODLAND_WALLET",
                ## =========================== Optional Parameters  ===========================
                'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
                'scApiKey'           => '{Put service call Api Key}',
            ];

        try {
            $result = self::$billingService->updateDirectWithdraw($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }

    }

    public function testUpdateDirectWithdrawRequiredParameters()
    {
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'id'            => 3,
            ];

        try {
            $result = self::$billingService->updateDirectWithdraw($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testUpdateDirectWithdrawValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->updateDirectWithdraw($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('id', $validation);
            $this->assertEquals('The property id is required', $validation['id'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testRevokeDirectWithdrawAllParameters()
    {
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'id'     => 3,
                ## =========================== Optional Parameters  ===========================
                'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
                'scApiKey'           => '{Put service call Api Key}',
            ];

        try {
            $result = self::$billingService->revokeDirectWithdraw($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }

    }

    public function testRevokeDirectWithdrawRequiredParameters()
    {
        $params =
            [
                ## ============================ *Required Parameters  =========================
                'id'     => 3,
            ];

        try {
            $result = self::$billingService->revokeDirectWithdraw($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testRevokeDirectWithdrawValidationError()
    {
        $paramsWithoutRequired = [];
        try {
            self::$billingService->revokeDirectWithdraw($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();
            $this->assertArrayHasKey('id', $validation);
            $this->assertEquals('The property id is required', $validation['id'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }
}