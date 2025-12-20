<?php

namespace Plugin\Cash;

use App\Services\Plugin\AbstractPlugin;
use App\Contracts\PaymentInterface;
use App\Exceptions\ApiException;
use Curl\Curl;

class Plugin extends AbstractPlugin implements PaymentInterface
{
    public function boot(): void
    {
        $this->filter('available_payment_methods', function ($methods) {
            if ($this->getConfig('enabled', true)) {
                $methods['CASH'] = [
                    'name' => $this->getConfig('display_name', 'CASH'),
                    'icon' => $this->getConfig('icon', '💳'),
                    'plugin_code' => $this->getPluginCode(),
                    'type' => 'plugin'
                ];
            }
            return $methods;
        });
    }

    public function form(): array
    {
        return [
            'cash_url' => [
                'label' => 'API地址',
                'type' => 'string',
                'required' => true,
                'description' => 'CASH接口地址'
            ],
            'cash_api_key' => [
                'label' => 'API Key',
                'type' => 'string',
                'required' => true,
                'description' => 'CASH API Key'
            ],
            'cash_api_secret' => [
                'label' => 'API Secret',
                'type' => 'string',
                'required' => true,
                'description' => 'CASH API Secret'
            ],
            'cash_currency' => [
                'label' => '货币',
                'type' => 'string',
                'description' => '默认 CNY'
            ],
            'cash_channel' => [
                'label' => '支付渠道',
                'type' => 'string',
                'description' => '可选，留空则使用收银台'
            ],
            'cash_subject' => [
                'label' => '订单标题',
                'type' => 'string',
                'description' => '可选'
            ],
            'cash_expire_seconds' => [
                'label' => '订单有效期(秒)',
                'type' => 'string',
                'description' => '可选，60-86400'
            ],
        ];
    }

    public function pay($order): array
    {
        $path = '/api/v1/open/orders';
        $body = [
            'out_trade_no' => $order['trade_no'],
            'amount' => sprintf('%.2f', $order['total_amount'] / 100),
            'currency' => $this->getConfig('cash_currency', 'CNY'),
            'notify_url' => $order['notify_url'],
            'return_url' => $order['return_url'],
            'metadata' => [
                'user_id' => $order['user_id']
            ]
        ];

        if ($this->getConfig('cash_channel')) {
            $body['channel'] = $this->getConfig('cash_channel');
        }
        if ($this->getConfig('cash_subject')) {
            $body['subject'] = $this->getConfig('cash_subject');
        }
        if ($this->getConfig('cash_expire_seconds')) {
            $body['expire_seconds'] = (int) $this->getConfig('cash_expire_seconds');
        }

        $bodyJson = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $timestamp = (string) time();
        $signature = $this->sign($timestamp, 'POST', $path, $bodyJson);

        $curl = new Curl();
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, 0);
        $curl->setHeader('Content-Type', 'application/json');
        $curl->setHeader('X-API-Key', $this->getConfig('cash_api_key'));
        $curl->setHeader('X-API-Secret', $this->getConfig('cash_api_secret'));
        $curl->setHeader('X-Timestamp', $timestamp);
        $curl->setHeader('X-Signature', $signature);
        $curl->post($this->getConfig('cash_url') . $path, $bodyJson);

        $result = $curl->response;
        if (!$result) {
            $errorMessage = $curl->errorMessage ?: '网络异常';
            throw new ApiException('CASH请求失败: ' . $errorMessage);
        }
        if ($curl->error) {
            $message = $this->buildErrorMessage($curl, $result);
            throw new ApiException($message);
        }
        $curl->close();

        if (!isset($result->success) || !$result->success || !isset($result->data->checkout_url)) {
            $message = $this->buildErrorMessage($curl, $result);
            throw new ApiException($message);
        }

        return [
            'type' => 1, // 0:qrcode 1:url
            'data' => $result->data->checkout_url
        ];
    }

    public function notify($params): array|bool
    {
        $payload = trim(request()->getContent());
        $data = json_decode($payload, true);
        if (!$data) {
            $data = $params;
        }

        $headers = $this->getRequestHeaders();
        $signature = $headers['X-Signature'] ?? null;
        $timestamp = $headers['X-Timestamp'] ?? null;
        $nonce = $headers['X-Nonce'] ?? null;
        if (!$signature || !$timestamp || !$nonce) {
            return false;
        }

        $bodyJson = $payload ?: json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $stringToSign = $timestamp . "\n" . $nonce . "\n" . $bodyJson;
        $computedSignature = hash_hmac('sha256', $stringToSign, $this->getConfig('cash_api_secret'));
        if (!hash_equals($computedSignature, $signature)) {
            return false;
        }

        if (!isset($data['status']) || $data['status'] !== 'paid') {
            return false;
        }
        if (!isset($data['out_trade_no'], $data['order_id'])) {
            return false;
        }

        return [
            'trade_no' => $data['out_trade_no'],
            'callback_no' => $data['order_id'],
            'custom_result' => 'SUCCESS'
        ];
    }

    private function sign($timestamp, $method, $path, $body): string
    {
        $stringToSign = $timestamp . "\n" . strtoupper($method) . "\n" . $path . "\n" . $body;
        return hash_hmac('sha256', $stringToSign, $this->getConfig('cash_api_secret'));
    }

    private function buildErrorMessage($curl, $result): string
    {
        $messageParts = ['CASH接口错误'];
        if (isset($curl->httpStatusCode)) {
            $messageParts[] = 'HTTP ' . $curl->httpStatusCode;
        }
        if (isset($curl->errorMessage) && $curl->errorMessage) {
            $messageParts[] = $curl->errorMessage;
        }
        if (isset($result->message)) {
            $messageParts[] = 'message=' . $this->stringifyValue($result->message);
        }
        if (isset($result->error)) {
            $messageParts[] = 'error=' . $this->stringifyValue($result->error);
        }
        if (isset($result->code)) {
            $messageParts[] = 'code=' . $this->stringifyValue($result->code);
        }
        $raw = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($raw) {
            $messageParts[] = 'response=' . $raw;
        }
        $filtered = array_filter($messageParts, static function ($value) {
            return $value !== null && $value !== '';
        });
        return implode(' | ', $filtered);
    }

    private function stringifyValue($value): string
    {
        if (is_scalar($value) || $value === null) {
            return (string) $value;
        }
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function getRequestHeaders(): array
    {
        $headers = [];
        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $key => $value) {
                $headers[$key] = $value;
            }
        }
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$name] = $value;
            }
        }
        return $headers;
    }
}
