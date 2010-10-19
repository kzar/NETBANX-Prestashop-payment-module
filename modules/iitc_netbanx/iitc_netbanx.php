<?php
/* This file is part of the Prestashop Netbanx payment module (iitc).        *
 * Copyright Dave Barker 2009                                                *
 *                                                                           *
 * The Prestashop Netbanx payment module (iitc) is free software: you can    *
 * redistribute it and/or modify it under the terms of the GNU General Public*
 * License as published by the Free Software Foundation, either version 3    *
 * of the License, or (at your option) any later version.                    *
 *                                                                           *
 * the Prestashop Netbanx payment module (iitc) is distributed in the hope   *
 * that it will be useful, but WITHOUT ANY WARRANTY; without even the implied*
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the *
 * GNU General Public License for more details.                              *
 *                                                                           *
 * You should have received a copy of the GNU General Public License         *
 * along with the Prestashop Netbanx payment module (iitc).                  *
 * If not, see <http://www.gnu.org/licenses/>.                               */

// Helper functions
function parseBoolString($boolString) {
  if (!$boolString || (strcasecmp($boolString, "false") == 0) || $boolString == "0")
    return False;
  else
    return True;
}
    
function formatAmount($amount, $minorUnits) { 
  if (parseBoolString($minorUnits))
    $amount = $amount / 100;

  return floatval($amount);
}

function currencySymbol($currencyCode) {
  switch ($currencyCode) {
  case "GBP": 
    return "&pound;";
    break;
  case "USD": 
    return "$";
    break;
  case "EUR": 
    return "&euro;";
    break;
  default:
    return htmlentities($currencyCode);
    break;
  }
}
     
function checkHost($remoteIP) {
  $netbanxHosts = array('80.65.254.6', '217.33.219.147');
  return in_array($remoteIP, $netbanxHosts);
}

function checkParams($params) {
  if ((empty($params)) || (!array_key_exists('nbx_merchant_reference', $params)) || (!array_key_exists('nbx_payment_amount', $params)))
    return false;
  else
    return true;
}

function checkChecksum($secretKey, $amount, $currencyCode, $merchantRef, $netbanxRef, $netbanxChecksum) {
  if (empty($secretKey))
    return array(true, "checksum ignored, no secretkey");
  else {
    if (empty($netbanxChecksum))
      return array(false, "checksum expected but missing (check secret key)");
    else {
      $checksum = sha1($amount . $currencyCode . $merchantRef . $netbanxRef . $secretKey);
      if ($checksum != $netbanxChecksum)
	return array(false, "checksum mismatch (check secret key)");
      else
	return array(true, "checksum matched");
    }
  }
}

// Netbanx payment module class
class Iitc_netbanx extends PaymentModule {
  public function __construct() {
    $this->name = "iitc_netbanx";
    $this->tab = "Payment";
    $this->version = "0.3";
    
    $this->currencies = true;
    $this->currencies_mode = 'radio'; 
    parent::__construct(); 

    $this->displayName = $this->l('Netbanx (UPP)');
    $this->description = $this->l('Process transactions through the NETBANX payment gateway.');
    $this->confirmUninstall = $this->l('Are you sure?');
  }

  public function install() {
    if (parent::install() && Configuration::updateValue('IITC_NETBANX_MERCHANTNAME', 'changeme') &&
	Configuration::updateValue('IITC_NETBANX_TESTMODE', 'True') &&
	Configuration::updateValue('IITC_NETBANX_SECRETKEY', '') &&
	Configuration::updateValue('IITC_NETBANX_MINORUNITS', 'True') &&
	Configuration::updateValue('IITC_NETBANX_REDIRECTION', 'False') &&
	Configuration::updateValue('IITC_NETBANX_IFRAME', 'True') &&
	$this->registerHook('payment') && $this->registerHook('paymentReturn'))
      return true;
    else
      return false;
  }
  
  public function uninstall() {
    if (Configuration::deleteByName('IITC_NETBANX_MERCHANTNAME') &&
	Configuration::deleteByName('IITC_NETBANX_TESTMODE') &&
	Configuration::deleteByName('IITC_NETBANX_SECRETKEY') &&
	Configuration::deleteByName('IITC_NETBANX_MINORUNITS') &&
	Configuration::deleteByName('IITC_NETBANX_REDIRECTION') &&
	Configuration::deleteByName('IITC_NETBANX_IFRAME') &&
	parent::uninstall())
      return true;
    else
      return false;
  }

  private function getSetting($name) {
    if (array_key_exists($name, $_POST))
      return $_POST[$name];
    else if (Configuration::get($name))
      return Configuration::get($name);
  }      

  private function validateTrueFalseString($value) {
    if ($value == "True" || $value == "False")
      return $value;
  }

  private function trueFalseOption($name, $label, $trueLabel = "True" , $falseLabel = "False") {
    if ($this->getSetting($name) == 'True') {
      $trueSelected = ' selected';
      $falseSelected = '';
    }
    else {
      $trueSelected = '';
      $falseSelected = ' selected';
    }

    $html = '<label>' . $this->l($label) . '</label><div class="margin-form"><select name="' . $name . '">' .
      '<option' . $trueSelected . ' value="True">' . $this->l($trueLabel) . '</option>' .
      '<option' . $falseSelected . ' value="False">' . $this->l($falseLabel) . '</option>' .
      '</select></div>';

    return $html;
  }

  public function getContent() {
    $this->_html .= '<h2>NETBANX (UPP)</h2>';

    // Validate + save their input
    $errors = "";
    if (isset($_POST['IITC_NETBANX_SUBMIT'])) {
      // Prestashop's pSQL prevents XSS and SQL injection for us using the pSQL function :)
      if (empty($_POST['IITC_NETBANX_MERCHANTNAME']))
	$errors .= '<li><b>' . $this->l('Merchant Name') . '</b> - ' . 
	  $this->l('The Merchant name field can\'t be left blank. Please check the correct value with NETBANX if you\'re unsure.') . '</li>';
      else
	Configuration::updateValue('IITC_NETBANX_MERCHANTNAME', $_POST['IITC_NETBANX_MERCHANTNAME']);

      Configuration::updateValue('IITC_NETBANX_SECRETKEY', $_POST['IITC_NETBANX_SECRETKEY']);

      if ($this->validateTrueFalseString($_POST['IITC_NETBANX_TESTMODE']) &&
	  $this->validateTrueFalseString($_POST['IITC_NETBANX_MINORUNITS']) &&
	  $this->validateTrueFalseString($_POST['IITC_NETBANX_REDIRECTION']) &&
	  $this->validateTrueFalseString($_POST['IITC_NETBANX_IFRAME'])) {	
	Configuration::updateValue('IITC_NETBANX_TESTMODE', $_POST['IITC_NETBANX_TESTMODE']);
	Configuration::updateValue('IITC_NETBANX_MINORUNITS', $_POST['IITC_NETBANX_MINORUNITS']);
	Configuration::updateValue('IITC_NETBANX_REDIRECTION', $_POST['IITC_NETBANX_REDIRECTION']);
	Configuration::updateValue('IITC_NETBANX_IFRAME', $_POST['IITC_NETBANX_IFRAME']);
      }
      else
	$errors .= '<li>' . $this->l('Problem updating settings, invalid information. If this problem persists get in touch, sales@iitc.info') . '</li>';
    }

    // Display the instructions
    $this->_html .= '<fieldset><legend>' . $this->l('Explanation') . '</legend><img src="http://iitc.info/prestashop_netbanx_393.gif" />' . 
      '<p><b>' . $this->l('Merchant Name') . '</b> - ' . $this->l('This needs to be set to the merchant name given to you by NETBANX. It\'s important that this is correct.') . '</p>' .
      '<p><b>' . $this->l('Test or Live Mode?') . '</b> - ' . $this->l('This option lets you put transactions through either the test NETBANX server or the live one. After testing when you are ready to go live change this option.') . '</p>' .
      '<p><b>' . $this->l('Secret Key') . '</b> - ' . $this->l('If you have chosen to use the checksum SHA1 checksum security feature provided by NETBANX enter your secret key below. Otherwise please make sure the field is completely empty.') . '</p>' .
      '<p><b>' . $this->l('Payment amount in Minor / Major units?') . '</b> - ' . $this->l('Usually this should be set to "Minor Units" but if your integration has been customised to accept the payment amount in major units you will need to change this setting.') . '</p>' .
      '<p><b>' . $this->l('Enable Redirection?') . '</b> - ' . $this->l('Enable this setting to have customers redirected back to your website after the transaction has been processed instead of displaying the standard NETBANX page. This has the added benefit of clearing the customer\'s shopping cart instantly. (Ocassionally this feature can be incompatible with various security settings so make sure you have tested that everything works properly.)') . '</p>' .
      '<p><b>' . $this->l('Enable the IFrame?') . '</b> - ' . $this->l('This feature should be left on unless you are experiencing problems. It keeps the customer on your site by loading the NETBANX checkout page inside an IFrame.') . '</p>' .
      '</fieldset><br>';

    // Display errors / confirmation
    if (isset($_POST['IITC_NETBANX_SUBMIT']))
      if ($errors)
	$this->_html .= '<div class="alert error"><ul>' . $errors . '</ul></div>';
      else
	$this->_html .= '<div class="conf confirm">' . $this->l('Changes have all been saved') . '</div>';
    
    // Display the form
    $this->_html .= '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">' .
      '<fieldset class="width2"><legend>' . $this->l('Configuration') . '</legend>' . 
      '<label>' . $this->l('Merchant Name:') . '</label><div class="margin-form"><input name="IITC_NETBANX_MERCHANTNAME" type="text" value="' . $this->getSetting('IITC_NETBANX_MERCHANTNAME') . '" /></div>' .
      $this->trueFalseOption('IITC_NETBANX_TESTMODE', 'Test or Live Mode?', 'Test Mode', 'Live Mode') .
      '<label>' . $this->l('Secret Key:') . '</label><div class="margin-form"><input type="text" name="IITC_NETBANX_SECRETKEY" value="' . $this->getSetting('IITC_NETBANX_SECRETKEY') . '"/>' . $this->l('(Leave blank to disable.)') . '</div>' .
      $this->trueFalseOption('IITC_NETBANX_MINORUNITS', 'Payment amount in', 'Minor Units', 'Major Units') .
      $this->trueFalseOption('IITC_NETBANX_REDIRECTION', 'Enable Redirection?', 'Yes, use redirection', 'No, disable redirection') .
      $this->trueFalseOption('IITC_NETBANX_IFRAME', 'Enable the IFrame?', 'Yes, use the IFrame', 'No, disable the IFrame') .

      '<input type="submit" name="IITC_NETBANX_SUBMIT" id="IITC_NETBANX_SUBMIT" value="' . $this->l('Save your changes') . '" />' .
      '</fieldset></form>';

    return $this->_html;
  }

  public function hookPayment($params) {
    if (!$this->active)
      return ;

    $address = new Address(intval($params['cart']->id_address_invoice));
    $customer = new Customer(intval($params['cart']->id_customer));
    $currency = $this->getCurrency();

    // Grab the order total and format it properly
    $amount = number_format(Tools::convertPrice($params['cart']->getOrderTotal(true, 3), $currency), 2, '.', '');
    $amount = sprintf('%0.2f', $amount);
    if ($this->getSetting('IITC_NETBANX_MINORUNITS') == 'True')
      $amount = preg_replace('/[^\d]+/', '', $amount);

    $parameters = array();

    $module_url = 'http://' . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__ . 'modules/iitc_netbanx/';
    $parameters['nbx_success_url'] = $module_url . 'callback.php';
    $parameters['nbx_failure_url'] = $module_url . 'callback.php';
    $parameters['nbx_return_url'] = $module_url . 'return.php';

    if ($this->getSetting('IITC_NETBANX_REDIRECTION') == "True") {
      $parameters['nbx_success_redirect_url'] = $module_url . 'success.php';
      $parameters['nbx_failure_redirect_url'] = $module_url . 'failure.php';
    }
    
    $parameters['nbx_houseno'] = $address->address1;
    $parameters['nbx_merchant_reference'] = $params['cart']->id;
    $parameters['nbx_currency_code'] = $currency->iso_code;
    $parameters['nbx_email'] = $customer->email;
    $parameters['nbx_cardholder_name'] = $customer->firstname . ' ' . $customer->lastname;
    $parameters['nbx_payment_amount'] = $amount;

    if (!$address->postcode)
      $parameters['nbx_postcode'] = 'NONE';
    else
      $parameters['nbx_postcode'] = $address->postcode;

    $secret_key = $this->getSetting('IITC_NETBANX_SECRETKEY');
    if ($secret_key)
      $parameters['nbx_checksum'] = sha1($parameters['nbx_payment_amount'] . $parameters['nbx_currency_code'] . $parameters['nbx_merchant_reference'] . $secret_key);

    // Change the form's target to use / not use iframe
    if ($this->getSetting('IITC_NETBANX_IFRAME') == 'True')
      $form_target = $module_url . 'checkout.php';
    else {
      if (Configuration::get("IITC_NETBANX_TESTMODE") == "True")
	$form_target = "https://pay.test.netbanx.com/" . Configuration::get("IITC_NETBANX_MERCHANTNAME");
      else 
	$form_target = "https://pay.netbanx.com/" . Configuration::get("IITC_NETBANX_MERCHANTNAME");
    }

    global $smarty;
    $smarty->assign(array('parameters' => $parameters,
			  'form_target' => $form_target));
    
    return $this->display(__FILE__, 'netbanx.tpl');
  }
}
?>
