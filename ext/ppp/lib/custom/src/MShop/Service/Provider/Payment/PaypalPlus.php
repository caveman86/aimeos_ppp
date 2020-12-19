<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015-2020
 * @package MShop
 * @subpackage Service
 */


namespace Aimeos\MShop\Service\Provider\Payment;


use Omnipay\Omnipay as OPay;
use Aimeos\MShop\Order\Item\Base as Status;

use Illuminate\Support\Facades\Auth;


/**
 * Payment provider for payment gateways supported by the PaypalPlus library.
 *
 * @package MShop
 * @subpackage Service
 */
class PaypalPlus
extends    \Aimeos\MShop\Service\Provider\Payment\OmniPay
//	extends \Aimeos\MShop\Service\Provider\Payment\Base
	implements \Aimeos\MShop\Service\Provider\Payment\Iface
{

	private $beConfig = array(
		'type' => array(
			'code' => 'type',
			'internalcode'=> 'type',
			'label'=> 'Payment provider type',
			'type'=> 'string',
			'internaltype'=> 'string',
			'default'=> '',
			'required'=> true,
		),
		'onsite' => array(
			'code' => 'onsite',
			'internalcode'=> 'onsite',
			'label'=> 'Collect data locally',
			'type'=> 'boolean',
			'internaltype'=> 'boolean',
			'default'=> '0',
			'required'=> false,
		),
		'address' => array(
			'code' => 'address',
			'internalcode'=> 'address',
			'label'=> 'Send address to payment gateway too',
			'type'=> 'boolean',
			'internaltype'=> 'boolean',
			'default'=> '0',
			'required'=> false,
		),
		'authorize' => array(
			'code' => 'authorize',
			'internalcode'=> 'authorize',
			'label'=> 'Authorize payments and capture later',
			'type'=> 'boolean',
			'internaltype'=> 'boolean',
			'default'=> '0',
			'required'=> false,
		),
		'createtoken' => array(
			'code' => 'createtoken',
			'internalcode'=> 'createtoken',
			'label'=> 'Request token for recurring payments',
			'type'=> 'boolean',
			'internaltype'=> 'boolean',
			'default'=> '1',
			'required'=> false,
		),
		'testmode' => array(
			'code' => 'testmode',
			'internalcode'=> 'testmode',
			'label'=> 'Test mode without payments',
			'type'=> 'boolean',
			'internaltype'=> 'boolean',
			'default'=> '0',
			'required'=> false,
		),
	);

	private $feConfig1 = array(
		'payment.firstname' => array(
			'code' => 'payment.firstname',
			'internalcode'=> 'firstName',
			'label'=> 'First name',
			'type'=> 'string',
			'internaltype'=> 'string',
			'default'=> '',
			'required'=> false
		),
		'payment.lastname' => array(
			'code' => 'payment.lastname',
			'internalcode'=> 'lastName',
			'label'=> 'Last name',
			'type'=> 'string',
			'internaltype'=> 'string',
			'default'=> '',
			'required'=> true
		),
		'payment.cardno' => array(
			'code' => 'payment.cardno',
			'internalcode'=> 'number',
			'label'=> 'Credit card number',
			'type'=> 'number',
			'internaltype'=> 'integer',
			'default'=> '',
			'required'=> true
		),
		'payment.cvv' => array(
			'code' => 'payment.cvv',
			'internalcode'=> 'cvv',
			'label'=> 'Verification number',
			'type'=> 'number',
			'internaltype'=> 'integer',
			'default'=> '',
			'required'=> true
		),
		'payment.expirymonth' => array(
			'code' => 'payment.expirymonth',
			'internalcode'=> 'expiryMonth',
			'label'=> 'Expiry month',
			'type'=> 'select',
			'internaltype'=> 'integer',
			'default'=> '',
			'required'=> true
		),
		'payment.expiryyear' => array(
			'code' => 'payment.expiryyear',
			'internalcode'=> 'expiryYear',
			'label'=> 'Expiry year',
			'type'=> 'select',
			'internaltype'=> 'integer',
			'default'=> '',
			'required'=> true
		),
		'payment.company' => array(
			'code' => 'payment.company',
			'internalcode'=> 'company',
			'label'=> 'Company',
			'type'=> 'string',
			'internaltype'=> 'string',
			'default'=> '',
			'required'=> false,
			'public' => false,
		),
		'payment.address1' => array(
			'code' => 'payment.address1',
			'internalcode'=> 'billingAddress1',
			'label'=> 'Street',
			'type'=> 'string',
			'internaltype'=> 'string',
			'default'=> '',
			'required'=> false,
			'public' => false,
		),
		'payment.address2' => array(
			'code' => 'payment.address2',
			'internalcode'=> 'billingAddress2',
			'label'=> 'Additional',
			'type'=> 'string',
			'internaltype'=> 'string',
			'default'=> '',
			'required'=> false,
			'public' => false,
		),
		'payment.city' => array(
			'code' => 'payment.city',
			'internalcode'=> 'billingCity',
			'label'=> 'City',
			'type'=> 'string',
			'internaltype'=> 'string',
			'default'=> '',
			'required'=> false,
			'public' => false,
		),
		'payment.postal' => array(
			'code' => 'payment.postal',
			'internalcode'=> 'billingPostcode',
			'label'=> 'Zip code',
			'type'=> 'string',
			'internaltype'=> 'string',
			'default'=> '',
			'required'=> false,
			'public' => false,
		),
		'payment.state' => array(
			'code' => 'payment.state',
			'internalcode'=> 'billingState',
			'label'=> 'State',
			'type'=> 'string',
			'internaltype'=> 'string',
			'default'=> '',
			'required'=> false,
			'public' => false,
		),
		'payment.countryid' => array(
			'code' => 'payment.countryid',
			'internalcode'=> 'billingCountry',
			'label'=> 'Country',
			'type'=> 'string',
			'internaltype'=> 'string',
			'default'=> '',
			'required'=> false,
			'public' => false,
		),
		'payment.telephone' => array(
			'code' => 'payment.telephone',
			'internalcode'=> 'billingPhone',
			'label'=> 'Telephone',
			'type'=> 'string',
			'internaltype'=> 'string',
			'default'=> '',
			'required'=> false,
			'public' => false,
		),
		'payment.email' => array(
			'code' => 'payment.email',
			'internalcode'=> 'email',
			'label'=> 'E-Mail',
			'type'=> 'string',
			'internaltype'=> 'string',
			'default'=> '',
			'required'=> false,
			'public' => false,
		),
	);
	private $feConfig = array(
		'payment.firstname' => array(
			'code' => 'payment.firstname',
			'internalcode'=> 'firstName',
			'label'=> 'First name',
			'type'=> 'string',
			'internaltype'=> 'string',
			'default'=> '',
		//	'public' => false ,
 			'required'=> false
		),
	);

	protected function sendRequest( \Aimeos\MShop\Order\Item\Iface $order, array $data ) : \Omnipay\Common\Message\ResponseInterface
	{
		$provider = $this->getProvider();

		if( $this->getValue( 'authorize', false ) && $provider->supportsAuthorize() )
		{
			$response = $provider->authorize( $data )->send();
			$order->setPaymentStatus( Status::PAY_AUTHORIZED );
			dd(33) ;
		}
		else
		{
			$data["payerid"] = "7q7" ;
			$response = $provider->purchase( $data )->send();
			$order->setPaymentStatus( Status::PAY_RECEIVED );
		//	dd($response) ;
		}

		return $response;
	}

	protected function getPaymentFormm( \Aimeos\MShop\Order\Item\Iface $order, array $params ) : \Aimeos\MShop\Common\Helper\Form\Iface
	{
		$list = [];
		$feConfig = $this->feConfig;
		$baseItem = $this->getOrderBase( $order->getBaseId(), \Aimeos\MShop\Order\Item\Base\Base::PARTS_ADDRESS );
		$addresses = $baseItem->getAddress( \Aimeos\MShop\Order\Item\Base\Address\Base::TYPE_PAYMENT );


		$year = date( 'Y' );

		foreach( $feConfig as $key => $config ) {
			$list[$key] = new \Aimeos\MW\Criteria\Attribute\Standard( $config );
		//		dd($list[$key]) ;
		}












		// /*

		/////////////////////////////////////////////////////

		$parts = \Aimeos\MShop\Order\Item\Base\Base::PARTS_SERVICE
		| \Aimeos\MShop\Order\Item\Base\Base::PARTS_PRODUCT
		| \Aimeos\MShop\Order\Item\Base\Base::PARTS_ADDRESS;

		$base = $this->getOrderBase( $order->getBaseId(), $parts );
		$data = $this->getData( $base, $order->getId(), $params );
		$urls = $this->getPaymentUrls();

		$data['cancelUrl'] = $this->getConfigValue( 'cancelUrl', '' ) ;

		if(empty($data['cancelUrl'])) {
			$data['cancelUrl'] = $_SERVER['HTTP_ORIGIN'] ;
		}
		//	echo"ppp";die() ;
		try
		{
		//	dd(1) ;
			$response = $this->sendRequest( $order, $data );
			$isSuccessful = $response->isSuccessful()  ;

			$approval_url = "" ;
			$testmode = "" ;
			$countryid = "" ;

			if( $isSuccessful )
			{
				$this->setOrderData( $order, ['Transaction' => $response->getTransactionReference()] );
				$this->saveRepayData( $response, $base->getCustomerId() );

				$status = $this->getValue( 'authorize', false ) ? Status::PAY_AUTHORIZED : Status::PAY_RECEIVED;
				$this->saveOrder( $order->setPaymentStatus( $status ) );
				
				if(!empty($response->getData()['links']['1']['href'])){
					$approval_url = $response->getData()['links']['1']['href'] ;
				}
		
				
				$testmode = $this->getConfigValue( 'testmode', '' ) ;
				if(!empty($testmode) && $testmode=="1"){
					$testmode = "sandbox" ;
				}elseif(!empty($testmode) && $testmode=="0"){
					$testmode = "live" ;
				}

				$countryid = Auth::user()->countryid;

				if(empty($approval_url)  || empty($testmode)  || empty($countryid) || empty($data['cancelUrl']) ){
					throw new \Aimeos\MShop\Service\Exception( $response->getMessage() );
				}else{
					// $this->getPayPalPlusJs($approval_url,$testmode ,$countryid ) ;
					
				}

			}
			elseif( $response->isRedirect() )
			{
				$this->setOrderData( $order, ['Transaction' => $response->getTransactionReference()] );
			//	return $this->getRedirectForm( $response );
			}
			else
			{
				$this->saveOrder( $order->setPaymentStatus( Status::PAY_REFUSED ) );
				throw new \Aimeos\MShop\Service\Exception( $response->getMessage() );
			}
		}
		catch( \Exception $e )
		{
			dd($e ) ;
			throw new \Aimeos\MShop\Service\Exception( $e->getMessage() );
		} 


		/////////////////////////////////////////////////////// */
		


		return new \Aimeos\MShop\Common\Helper\Form\Standard('','',[], true ,$this->getPayPalPlusJs($approval_url,$testmode ,$countryid ) );


		$url = $this->getConfigValue( 'payment.url-self', '' );
		return new \Aimeos\MShop\Common\Helper\Form\Standard( $url, 'POST', $list, false , $this->getPayPalPlusJs($approval_url,$testmode ,$countryid ) );
	}
	
	public function process( \Aimeos\MShop\Order\Item\Iface $order, array $params = [] ) : ?\Aimeos\MShop\Common\Helper\Form\Iface
	{
		if( $this->getValue( 'onsite' ) == true && ( !isset( $params['number'] ) || !isset( $params['cvv'] ) ) ) {
			return $this->getPaymentFormm( $order, $params );
		}
		return $this->getPaymentFormm( $order, $params );
		return $this->processOrder( $order, $params );
	}

	protected function processOrder( \Aimeos\MShop\Order\Item\Iface $order,
		array $params = [] ) : ?\Aimeos\MShop\Common\Helper\Form\Iface
	{
		$parts = \Aimeos\MShop\Order\Item\Base\Base::PARTS_SERVICE
			| \Aimeos\MShop\Order\Item\Base\Base::PARTS_PRODUCT
			| \Aimeos\MShop\Order\Item\Base\Base::PARTS_ADDRESS;

		$base = $this->getOrderBase( $order->getBaseId(), $parts );
		$data = $this->getData( $base, $order->getId(), $params );
		$urls = $this->getPaymentUrls();

		$data['cancelUrl'] = $this->getConfigValue( 'cancelUrl', '' ) ;

		if(empty($data['cancelUrl'])) {
			$data['cancelUrl'] = $_SERVER['HTTP_ORIGIN'] ;
		}
		//	echo"ppp";die() ;
		try
		{
			$response = $this->sendRequest( $order, $data );
			$isSuccessful = $response->isSuccessful()  ;

			$approval_url = "" ;
			$testmode = "" ;
			$countryid = "" ;

			if( $isSuccessful )
			{
				$this->setOrderData( $order, ['Transaction' => $response->getTransactionReference()] );
				$this->saveRepayData( $response, $base->getCustomerId() );

				$status = $this->getValue( 'authorize', false ) ? Status::PAY_AUTHORIZED : Status::PAY_RECEIVED;
				$this->saveOrder( $order->setPaymentStatus( $status ) );
				
				if(!empty($response->getData()['links']['1']['href'])){
					$approval_url = $response->getData()['links']['1']['href'] ;
				}
		
				
				$testmode = $this->getConfigValue( 'testmode', '' ) ;
				if(!empty($testmode) && $testmode=="1"){
					$testmode = "sandbox" ;
				}elseif(!empty($testmode) && $testmode=="0"){
					$testmode = "live" ;
				}
	
				$countryid = Auth::user()->countryid;


				if(empty($approval_url)  || empty($testmode)  || empty($countryid) || empty($data['cancelUrl']) ){
					throw new \Aimeos\MShop\Service\Exception( $response->getMessage() );
				}else{
			//		$this->getPayPalPlusJs($approval_url,$testmode ,$countryid ) ;
				}

			}
			elseif( $response->isRedirect() )
			{
				$this->setOrderData( $order, ['Transaction' => $response->getTransactionReference()] );
				return $this->getRedirectForm( $response );
			}
			else
			{
				$this->saveOrder( $order->setPaymentStatus( Status::PAY_REFUSED ) );
				throw new \Aimeos\MShop\Service\Exception( $response->getMessage() );
			}
		}
		catch( \Exception $e )
		{
			throw new \Aimeos\MShop\Service\Exception( $e->getMessage() );
		} 
 		return new \Aimeos\MShop\Common\Helper\Form\Standard( $urls['returnUrl'] ?? '', 'POST', [], false );

	}



		/**
	 * Returns the configuration attribute definitions of the provider to generate a list of available fields and
	 * rules for the value of each field in the administration interface.
	 *
	 * @return array List of attribute definitions implementing \Aimeos\MW\Common\Critera\Attribute\Iface
	 */
	public function getConfigBE() : array
	{
		$list = [];

		foreach( $this->beConfig as $key => $config ) {
			$list[$key] = new \Aimeos\MW\Criteria\Attribute\Standard( $config );
		}

		return $list;
	}


}
