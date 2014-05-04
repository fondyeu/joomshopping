<?php
defined('_JEXEC') or die('Restricted access');

class pm_oplata extends PaymentRoot
{
    const ORDER_APPROVED = 'approved';
    const ORDER_DECLINED = 'declined';

    const SIGNATURE_SEPARATOR = '|';

    const ORDER_SEPARATOR = "#";

    const URL = 'https://api.oplata.com/api/checkout/redirect/';

    protected static $responseFields = array('rrn',
                                             'masked_card',
                                             'sender_cell_phone',
                                             'response_status',
                                             'currency',
                                             'fee',
                                             'reversal_amount',
                                             'settlement_amount',
                                             'actual_amount',
                                             'order_status',
                                             'response_description',
                                             'order_time',
                                             'actual_currency',
                                             'order_id',
                                             'tran_type',
                                             'eci',
                                             'settlement_date',
                                             'payment_system',
                                             'approval_code',
                                             'merchant_id',
                                             'settlement_currency',
                                             'payment_id',
                                             'sender_account',
                                             'card_bin',
                                             'response_code',
                                             'card_type',
                                             'amount',
                                             'sender_email');

    function showPaymentForm($params, $pmconfigs)
    {
        include(dirname(__FILE__)."/paymentform.php");
    }

	//function call in admin
	function showAdminFormParams($params)
    {
	  $array_params = array('merchant_id', 'merchant_salt');
	  foreach ($array_params as $key){
	  	if (!isset($params[$key])) $params[$key] = '';
	  } 
	  $orders = JModelLegacy::getInstance('orders', 'JshoppingModel'); //admin model
      include(dirname(__FILE__)."/adminparamsform.php");	  
	}

	function checkTransaction($pmconfigs, $order, $act)
    {
        $response = $_POST;

        if ($response['order_status'] == self::ORDER_DECLINED) {
            return array(0, 'Order was declined.');
        }

        $responseSignature = $response['signature'];
        foreach ($response as $k => $v) {
            if (!in_array($k, self::$responseFields)) {
                unset($response[$k]);
            }
        }

        if ($this->getSignature($response, $pmconfigs['merchant_salt']) != $responseSignature) {
            return array(0, 'An error has occurred during payment. Signature is not valid.');
        }

        return array(1, '');
	}

    protected function getSignature($data, $password, $encoded = true)
    {
        $data = array_filter($data);
        ksort($data);

        $str = $password;
        foreach ($data as $k => $v) {
            $str .= self::SIGNATURE_SEPARATOR . $v;
        }

        if ($encoded) {
            return sha1($str);
        } else {
            return $str;
        }
    }

    private function getProductInfo($order_id)
    {
        return "Order: $order_id";
    }

    private function getAmount($order)
    {
        return round($order->order_total * 100);
    }

    function showEndForm($pmconfigs, $order)
    {
        $return = JURI::root(). "index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=pm_oplata";
        $callback = JURI::root(). "index.php?option=com_jshopping&controller=checkout&task=step7&act=notify&js_paymentclass=pm_oplata";

        list($lang,) = explode('-', JFactory::getLanguage()->getTag());

        $oplataArgs = array('order_id' => $order->order_id . self::ORDER_SEPARATOR . time(),
                             'merchant_id' => $pmconfigs['merchant_id'],
                             'order_desc' => $this->getProductInfo($order->order_id),
                             'amount' => $this->getAmount($order),
                             'currency' => strtoupper($order->currency_code_iso),
                             'server_callback_url' => $callback,
                             'response_url' => $return,
                             'lang' => strtoupper($lang),
                             'sender_email' => $order->email);

        $oplataArgs['signature'] = $this->getSignature($oplataArgs, $pmconfigs['merchant_salt']);

        ?>
        <html>
        <head>
            <meta http-equiv="content-type" content="text/html; charset=utf-8" />            
        </head>
        <body>
        <form id="paymentform" action="<?php print pm_oplata::URL; ?>" name = "paymentform" method = "post">
        <?php
            foreach ($oplataArgs as $key => $value) :
        ?>
                <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
        <?php
            endforeach;
        ?>
        </form>        
        <?php print _JSHOP_REDIRECT_TO_PAYMENT_PAGE ?>
        <br>
        <script type="text/javascript">document.getElementById('paymentform').submit();</script>
        </body>
        </html>
        <?php
        die();
	}
    
    function getUrlParams($pmconfigs)
    {
        list($order_id,) = explode(self::ORDER_SEPARATOR, $_POST['order_id']);

        $params = array(); 
        $params['order_id'] = $order_id;
        $params['hash'] = "";
        $params['checkHash'] = 0;
        $params['checkReturnParams'] = 1;//$pmconfigs['checkdatareturn'];

        return $params;
    }

    
}