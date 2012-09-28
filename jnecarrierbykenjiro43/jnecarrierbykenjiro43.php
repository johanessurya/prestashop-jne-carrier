<?php

// Avoid direct access to the file
if (!defined('_PS_VERSION_'))
	exit;

class JneCarrierByKenjiro43 extends CarrierModule
{
	public  $id_carrier;

	private $_html = '';
	private $_postErrors = array();
	private $_moduleName;
	private $_data;

	/*
	** Construct Method
	**
	*/

	public function __construct()
	{
		// Init module
		$this->name = 'jnecarrierbykenjiro43';
		$this->_moduleName=$this->name;
		
		$this->tab = 'shipping_logistics';
		$this->version = '1.0';
		$this->author = 'Kenjiro43';
		$this->limited_countries = array('id');

		parent::__construct ();
		
		// Give title and description
		$this->displayName = $this->l('JNE Carrier By Kenjiro43');
		$this->description = $this->l('Pengiriman menggunakan JNE');

		// Check if this module has been installed
		if (self::isInstalled($this->name))
		{
			// Getting carrier list
			global $cookie;
			$carriers = Carrier::getCarriers($cookie->id_lang, true, false, false, NULL, PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);

			// Saving id carrier list
			$id_carrier_list = array();
			foreach($carriers as $carrier)
				$id_carrier_list[] .= $carrier['id_carrier'];

			// Testing if Carrier Id exists
			$warning = array();
			
			// Check JNE YES service
			$id_carrier=$this->getYesId();
			if (!in_array((int)($id_carrier), $id_carrier_list))
				$warning[] .= $this->l('"JNE (REG)"').' ';
			
			// Check JNE REG service
			$id_carrier=$this->getRegId();
			if (!in_array((int)($id_carrier), $id_carrier_list))
				$warning[] .= $this->l('"JNE (OKE)"').' ';
				
			// Check city
			$city=$this->getCity();
			if (empty($city))
				$warning[] .= $this->l('"City"').' ';
			
			// Show the warning if any
			if (count($warning))
				$this->warning .= implode(' , ',$warning).$this->l('must be configured to use this module correctly').' ';
		}
	}
	

	/*
	** Install / Uninstall Methods
	**
	*/

	public function install()
	{
		$carrierConfig = array(
			0 => array('name' => 'JNE (YES)',
				'id_tax_rules_group' => 0,
				'active' => true,
				'deleted' => 0,
				'shipping_handling' => false,
				'range_behavior' => 0,
				'delay' => array(
					'id' => '1 Hari', 
				),
				'id_zone' => 3,
				'is_module' => true,
				'shipping_external' => true,
				'external_module_name' => $this->_moduleName,
				'need_range' => true
			),
			1 => array('name' => 'JNE (REG)',
				'id_tax_rules_group' => 0,
				'active' => true,
				'deleted' => 0,
				'shipping_handling' => false,
				'range_behavior' => 0,
				'delay' => array(
					'id' => '2-3 Hari', 
				),
				'id_zone' => 3,
				'is_module' => true,
				'shipping_external' => true,
				'external_module_name' => $this->_moduleName,
				'need_range' => true
			),
			2 => array('name' => 'JNE (OKE)',
				'id_tax_rules_group' => 0,
				'active' => true,
				'deleted' => 0,
				'shipping_handling' => false,
				'range_behavior' => 0,
				'delay' => array(
					'id' => '1 Minggu', 
				),
				'id_zone' => 3,
				'is_module' => true,
				'shipping_external' => true,
				'external_module_name' => $this->_moduleName,
				'need_range' => true
			),
			3 => array('name' => 'JNE (SS)',
				'id_tax_rules_group' => 0,
				'active' => true,
				'deleted' => 0,
				'shipping_handling' => false,
				'range_behavior' => 0,
				'delay' => array(
					'id' => '???', 
				),
				'id_zone' => 3,
				'is_module' => true,
				'shipping_external' => true,
				'external_module_name' => $this->_moduleName,
				'need_range' => true
			)
		);

		// Set for YES
		$id_carrier = $this->installExternalCarrier($carrierConfig[0]);
		$this->setYesId($id_carrier);
		
		// Set for REG
		$id_carrier = $this->installExternalCarrier($carrierConfig[1]);
		$this->setRegId($id_carrier);
		
		// Set for OKE
		$id_carrier = $this->installExternalCarrier($carrierConfig[2]);
		$this->setOkeId($id_carrier);
		
		// Set for SS
		$id_carrier = $this->installExternalCarrier($carrierConfig[3]);
		$this->setSsId($id_carrier);
		
		if (!parent::install() ||
		    !$this->registerHook('updateCarrier') ||
			!$this->registerHook('extraCarrier') ||
			!$this->registerHook('header') ||
			!$this->registerHook('backOfficeHeader'))
			return false;
		return true;
	}
	
	public function uninstall()
	{
		// Delete External Carrier
		// JNE YES
		$Carrier1 = new Carrier((int)($this->getYesId()));
		
		// JNE REG
		$Carrier2 = new Carrier((int)($this->getRegId()));

		// JNE OKE
		$Carrier3 = new Carrier((int)($this->getOkeId()));
		
		// JNE SS
		$Carrier4 = new Carrier((int)($this->getSsId()));
		
		// If external carrier is default set other one as default
		if (Configuration::get('PS_CARRIER_DEFAULT') == (int)($Carrier1->id) || 
			Configuration::get('PS_CARRIER_DEFAULT') == (int)($Carrier2->id) || 
			Configuration::get('PS_CARRIER_DEFAULT') == (int)($Carrier3->id) || 
			Configuration::get('PS_CARRIER_DEFAULT') == (int)($Carrier4->id))
		{
			global $cookie;
			$carriersD = Carrier::getCarriers($cookie->id_lang, true, false, false, NULL, PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
			foreach($carriersD as $carrierD)
				if ($carrierD['active'] AND !$carrierD['deleted'] AND ($carrierD['name'] != $this->_config['name']))
					Configuration::updateValue('PS_CARRIER_DEFAULT', $carrierD['id_carrier']);
		}

		// Then delete Carrier
		$Carrier1->deleted = 1;		
		$Carrier2->deleted = 1;
		$Carrier3->deleted = 1;
		$Carrier4->deleted = 1;
		if (!$Carrier1->update() || 
			!$Carrier2->update() ||
			!$Carrier3->update() ||
			!$Carrier4->update())
			return false;
		
		// die();
		
		// Uninstall
		if (!parent::uninstall() ||
			!$this->deleteConfig('CITY') ||
			!$this->unregisterHook('updateCarrier') ||
		    !$this->unregisterHook('extraCarrier') ||
			!$this->unregisterHook('header') ||
			!$this->unregisterHook('backOfficeHeader'))
			return false;

		
		return true;
	}

	public static function installExternalCarrier($config)
	{
		$carrier = new Carrier();
		$carrier->name = $config['name'];
		$carrier->id_tax_rules_group = $config['id_tax_rules_group'];
		$carrier->id_zone = $config['id_zone'];
		$carrier->active = $config['active'];
		$carrier->deleted = $config['deleted'];
		$carrier->delay = $config['delay'];
		$carrier->shipping_handling = $config['shipping_handling'];
		$carrier->range_behavior = $config['range_behavior'];
		$carrier->is_module = $config['is_module'];
		$carrier->shipping_external = $config['shipping_external'];
		$carrier->external_module_name = $config['external_module_name'];
		$carrier->need_range = $config['need_range'];

		$languages = Language::getLanguages(true);
		foreach ($languages as $language)
		{
			if ($language['iso_code'] == 'id')
				$carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
		}

		if ($carrier->add())
		{
			$groups = Group::getGroups(true);
			foreach ($groups as $group)
				Db::getInstance()->autoExecute(_DB_PREFIX_.'carrier_group', array('id_carrier' => (int)($carrier->id), 'id_group' => (int)($group['id_group'])), 'INSERT');

			$rangePrice = new RangePrice();
			$rangePrice->id_carrier = $carrier->id;
			$rangePrice->delimiter1 = '0';
			$rangePrice->delimiter2 = '10000';
			$rangePrice->add();

			$rangeWeight = new RangeWeight();
			$rangeWeight->id_carrier = $carrier->id;
			$rangeWeight->delimiter1 = '0';
			$rangeWeight->delimiter2 = '10000';
			$rangeWeight->add();

			$zones = Zone::getZones(true);
			
			foreach ($zones as $zone)
			{
				if($zone['name']=='Asia'){
					Db::getInstance()->autoExecute(_DB_PREFIX_.'carrier_zone', array('id_carrier' => (int)($carrier->id), 'id_zone' => (int)($zone['id_zone'])), 'INSERT');
					Db::getInstance()->autoExecuteWithNullValues(_DB_PREFIX_.'delivery', array('id_carrier' => (int)($carrier->id), 'id_range_price' => (int)($rangePrice->id), 'id_range_weight' => NULL, 'id_zone' => (int)($zone['id_zone']), 'price' => '0'), 'INSERT');
					Db::getInstance()->autoExecuteWithNullValues(_DB_PREFIX_.'delivery', array('id_carrier' => (int)($carrier->id), 'id_range_price' => NULL, 'id_range_weight' => (int)($rangeWeight->id), 'id_zone' => (int)($zone['id_zone']), 'price' => '0'), 'INSERT');
				}
			}

			// Copy Logo
			if (!copy(dirname(__FILE__).'/carrier.jpg', _PS_SHIP_IMG_DIR_.'/'.(int)$carrier->id.'.jpg'))
				return false;

			// Return ID Carrier
			return (int)($carrier->id);
		}

		return false;
	}

	/*
	** Form Config Methods
	**
	*/

	public function getContent()
	{
		$this->_html .= '<h2>' . $this->l('My Carrier').'</h2>';
		if (!empty($_POST)){
			$this->_postProcess();
		}
		
		$this->_displayForm();
		
		return $this->_html;
	}

	private function _displayForm(){
		global $smarty;
		
		$city=$this->getCity();;
		
		$sourceCity=0;
		if(!empty($city)){
			$sourceCity=1;
		}
		
		$smarty->assign('sourceCity',$sourceCity);
		$smarty->assign('uri',$_SERVER['REQUEST_URI']);
		$smarty->assign('module',$this->_moduleName);
		$smarty->assign('city',$city);
		$smarty->assign('server',$_SERVER['HTTP_HOST']);

		$this->_html = $this->display(__FILE__,'config_page.tpl');	
	}

	private function _postValidation()
	{
		// Check configuration values
	}

	private function _postProcess()
	{
		// Saving new configurations
		$city=$this->setCity(Tools::getValue($this->_moduleName.'city'));
		if ($city)
			$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
		else
			$this->_html .= $this->displayError($this->l('Settings failed'));
	}
	
	public function hookbackOfficeHeader($params){
		global $smarty;
		$smarty->assign('module',$this->_moduleName);
		
		$html=$this->display(__FILE__,'script.tpl');
		
		return $html;
	}
	
	// Hook header(Front Office)
	public function hookheader($params){
		global $smarty;
		
		$html='';
		
		$smarty->assign('module',$this->_moduleName);
		
		$html .=$this->display(__FILE__,'script.tpl');
		
		$html .=$this->display(__FILE__,'savecity.php');
		
		return $html;
	}
	
	public function hookextraCarrier($params){
		$html='';
		// Check if this city valid
		$city=$params['address']->city;
		$cities=$this->getCitySuggestion($city);
		
		if(count($cities)>1){
			// If not
			// SMARTY
			global $smarty;
			
			// Set customer city
			$smarty->assign('city',$city);
			
			// Set this module name
			$smarty->assign('module',$this->_moduleName);
			
			// Set URI
			$smarty->assign('uri',$_SERVER['REQUEST_URI']);
			$html=$this->display(__FILE__,'extracarrier.tpl');
		}
		
		return '';
	}
	
	public function pr($params){
		echo '<pre>';
		var_dump($params);
		echo '</pre>';
	}
	
	/*
	** Hook update carrier
	**
	*/

	public function hookupdateCarrier($params)
	{
		if ((int)($params['id_carrier']) == (int)($this->getYesId()))
			$this->setYesId((int)($params['carrier']->id));
		else if ((int)($params['id_carrier']) == (int)($this->getRegId()))
			$this->setRegId((int)($params['carrier']->id));
		else if ((int)($params['id_carrier']) == (int)($this->getOkeId()))
			$this->setOkeId((int)($params['carrier']->id));
		else if ((int)($params['id_carrier']) == (int)($this->getSsId()))
			$this->setSsId((int)($params['carrier']->id));
	}

	/*
	** Front Methods
	**
	** If you set need_range at true when you created your carrier (in install method), the method called by the cart will be getOrderShippingCost
	** If not, the method called will be getOrderShippingCostExternal
	**
	** $params var contains the cart, the customer, the address
	** $shipping_cost var contains the price calculated by the range in carrier tab
	**
	*/
	
	public function getOrderShippingCost($params, $shipping_cost)
	{
		// Get city
		$city=$this->getCityByIdAddress($params->id_address_delivery);
		
		// Calculate Shipping cost and return
		
		// $this->pr($params);
		// $this->pr($shipping_cost);
		// echo $city;
		
		$data=$this->_data;
		if(empty($data))
			$data=$this->getPrice($city);
		
		// echo $city;
		// $this->pr($data);
		// die;
		
		// This example returns shipping cost with overcost set in the back-office, but you can call a webservice or calculate what you want before returning the final value to the Cart
		$price=false;
		if($this->id_carrier == $this->getYesId())
			$price=$this->getPriceYes($data);
		else if($this->id_carrier == $this->getRegId())
			$price=$this->getPriceReg($data);
		else if($this->id_carrier == $this->getOkeId())
			$price=$this->getPriceOke($data);
		else if($this->id_carrier == $this->getSsId())
			$price=$this->getPriceSs($data);
		
		// If the carrier is not known, you can return false, the carrier won't appear in the order process
		return $price;
	}
	
	public function getOrderShippingCostExternal($params){
		return false;
	}
	
	private function getCityByIdAddress($id){		
		$sql = 'SELECT * FROM '._DB_PREFIX_.'address WHERE id_address = '.$id;
		
		$db = Db::getInstance();
		// echo $sql;
		$row = $db->getRow($sql);
		
		return $row['city'];
	}
	
	private function getPrice($city){
		$temp=false;
	
		$api=$this->getApi();
		$url='http://api.ongkir.info/cost/find';

		$data=array(
			'API-Key'=>$api,
			'from'=>'SURABAYA',//Configuration::get($this->_moduleName.'CITY'),
			'to'=>$city,//$city,
			'weight'=>1000,
			'courier'=>'jne',
			'format'=>'json'
		);
		
		$data=json_decode($this->post_to_url($url,$data),1);

		if($data['status']['description']=='OK'){
			$temp=$data;
		}
		
		return $temp;
	}
	
	private function post_to_url($url, $data) {
		$fields = '';
		foreach($data as $key => $value) { 
			$fields .= $key . '=' . $value . '&'; 
		}
		rtrim($fields, '&');

		$post = curl_init();

		curl_setopt($post, CURLOPT_URL, $url);
		curl_setopt($post, CURLOPT_POST, count($data));
		curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);

		return curl_exec($post);

		curl_close($post);
	}
	
	// SETTER
	private function setConfig($name,$value){
		$newName=$this->getNewName($name);
		return Configuration::updateValue($newName, $value);			
	}
	
	private function setCity($value){
		return $this->setConfig('CITY', $value);	
	}
	
	private function setYesId($value){
		return $this->setConfig('YES_ID', (int)$value);
	}
	
	private function setRegId($value){
		return $this->setConfig('REG_ID', (int)$value);
	}
	
	private function setOkeId($value){
		return $this->setConfig('OKE_ID', (int)$value);
	}
	
	private function setSsId($value){
		return $this->setConfig('SS_ID', (int)$value);
	}
	
	// @params json
	private function setOngkir($value){
		return $this->setConfig('ONGKIR', $value);
	}
	
	// GETTER
	// This is return city of store which have save on database	
	private function getCity(){
		return $this->getConfig('CITY');
	}
	
	private function getCitySuggestion($city){		
		$temp=false;
		
		if(strlen($city)>=3){
			$api=$this->getApi();
			
			$url='http://api.ongkir.info/city/list';
			
			// City
			$query=$city;
			$type='destination';
			$courier='jne';
			$format='json';
			
			$data=array(
				'API-Key'=>$api,
				'query'=>$query,
				'type'=>$type,
				'courier'=>$courier,
				'format'=>$format
			);

			$arr=json_decode($this->post_to_url($url,$data),true);
			
			$temp=$arr['cities'];
		}
		
		return $temp;
	}
	
	private function getApi(){
		return 'a5355ba79864cee263e829b0d5ddef89';
	}
		
	private function getPriceByCode($data,$code){
		$price=false;
		
		if(!empty($data)){
			$priceList=$data['price'];
			$bol=true;
			$i=0;
			while($bol && $i<count($priceList)){
				if($priceList[$i]['service_code']==$code){
					$bol=false;
					$price=$priceList[$i]['value'];
				}
					
				$i++;
			}
		}
		
		return $price;	
	}
	
	private function getYesId(){
		return $this->getConfig('YES_ID');
	}
	
	private function getRegId(){
		return $this->getConfig('REG_ID');
	}
	
	private function getOkeId(){
		return $this->getConfig('OKE_ID');
	}
	
	private function getSsId(){
		return $this->getConfig('SS_ID');
	}
	
	private function getConfig($name){
		$newName=$this->getNewName($name);
		return Configuration::get($newName);
	}
		
	private function getNewName($name){
		return strtoupper($this->_moduleName.'_'.$name);
	}
	
	private function getPriceYes($data){
		return $this->getPriceByCode($data,'yes');
	}

	private function getPriceReg($data){
		return $this->getPriceByCode($data,'reg');
	}

	private function getPriceOke($data){
		return $this->getPriceByCode($data,'oke');
	}
	
	private function getPriceSs($data){
		return $this->getPriceByCode($data,'ss');
	}
	
	private function getOngkir(){
		$temp=$this->getConfig('ONGKIR');
		
		if($temp){
			$temp=json_decode($temp);
		}
		
		return $temp;
	}
	
	private function deleteConfig($name){
		$newName=$this->getNewName($name);
		return Configuration::deleteByName($newName);
	}
}

?>
