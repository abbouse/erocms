<?php
/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/
class WapkassaClass
{
    private $url = 'https://wapkassa.ru/api/';
    private $userAgent = 'Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20100101 Firefox/12.0';
    private $site_id;
    private $site_secret;
    private $params = array();

    public function __construct($site_id, $site_secret)
    {
        if (empty($site_id)) {
            throw new Exception('Пустой id площадки');
        }

        if (empty($site_id)) {
            throw new Exception('Пустой секретный код площадки');
        }

        $this->site_id = $site_id;
        $this->site_secret = $site_secret;
    }

    public function getValue()
    {
        $this->setHash();

        return $this->params;
    }

    public function getPaymentUrl()
    {
        $this->setHash();

        $result = $this->curl('merchant/getPaymentUrl', $this->params);

        if (empty($result)) {
            throw new Exception('Неверный ответ от wapkassa api');
        }

        return $result;
    }

    public function setHash()
    {
        $this->params['WK_PAYMENT_HASH'] = $this->genHash($this->params);

        return true;
    }

    public function setParams($amount, $comment, $encode = null)
    {
        if (empty($amount) || $amount <= 0) {
            throw new Exception('Неверная сумма');
        }

        if (empty($comment) || mb_strlen($comment) < 5) {
            throw new Exception('Неверный комментарий');
        }

        if (!empty($encode)) {
            $comment = iconv($encode, 'UTF-8', $comment);
        }

        $this->params['WK_PAYMENT_SITE'] = $this->site_id;
        $this->params['WK_PAYMENT_AMOUNT'] = $amount;
        $this->params['WK_PAYMENT_COMM'] = base64_encode($comment);

        return true;
    }

    public function setParamsAdd($params = array(), $encode = null)
    {
        if (!is_array($params) || count($params) > 10) {
            throw new Exception('Неверные параметры');
        }

        ksort($params);

        if (!empty($encode)) {
            foreach ($params as $key => $value) {
                $params[$key] = iconv($encode, 'UTF-8', $value);
            }
        }

        if (PHP_VERSION_ID >= 50400) {
            $this->params['WK_PAYMENT_ADD'] = base64_encode(json_encode($params, JSON_UNESCAPED_UNICODE));
        } else {
            $this->params['WK_PAYMENT_ADD'] = base64_encode(json_encode($params));
        }

        return true;
    }

    public function ping($request)
    {
        if (empty($request['WK_PAY_PING'])) {
            return false;
        }
        $hash = $request['WK_PAY_HASH'];
        unset($request['WK_PAY_HASH']);

        if (strcasecmp($hash, $this->genHash($request)) !== 0) {
            throw new Exception('Неверный HASH');
        }

        return true;
    }

    public function parseRequest($request, $encode = null)
    {
        if (empty($request) || !is_array($request) || !isset($request['WK_PAY_ID'], $request['WK_PAY_SITE'], $request['WK_PAY_TIME'], $request['WK_PAY_AMOUNT'], $request['WK_PAY_COMM'], $request['WK_PAY_ADD'], $request['WK_PAY_HASH'])) {
            throw new Exception('Неверный запрос');
        }

        $hash = $request['WK_PAY_HASH'];
        unset($request['WK_PAY_HASH']);

        if (strcasecmp($hash, $this->genHash($request)) !== 0) {
            throw new Exception('Неверный HASH');
        }

        $this->params['id'] = $request['WK_PAY_ID'];
        $this->params['site_id'] = $request['WK_PAY_ID'];
        $this->params['time'] = $request['WK_PAY_TIME'];
        $this->params['amount'] = $request['WK_PAY_AMOUNT'];
        $this->params['comm'] = base64_decode($request['WK_PAY_COMM']);
        $this->params['add'] = json_decode(base64_decode($request['WK_PAY_ADD']), true);

        if (!empty($encode)) {
            foreach ($this->params as $key => $value) {
                $this->params[$key] = iconv('UTF-8', $encode, $value);
            }
        }

        return $this->params;
    }

    public function successPayment()
    {
        return $this->params['id'] . '|YES';
    }

    public function successPing()
    {
        return 'PONG';
    }

    private function genHash($arr_value)
    {
        ksort($arr_value);
        $arr_value[] = $this->site_secret;
        return strtoupper(hash('sha256', implode(":", $arr_value)));
    }

    private function curl($method, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url . $method);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        $res = curl_exec($ch);
        if ($res === false) {
            return false;
        }
        return json_decode($res, true);
    }
}
