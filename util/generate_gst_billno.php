<?
ini_set('display_errors', 1);
$url = 'http://santhyahms.cubosale.in/admin/index.php?_topRm=pharmacy&module=hms_order&_spAction=generateGstBillNo&showHTML=0';
$post_param = array();
require_once "Zend/Http/Client.php";
$client = new Zend_Http_Client();
$client->setUri($url);
$client->setParameterPost($post_param);
$response = $client->request(Zend_Http_Client::POST);
