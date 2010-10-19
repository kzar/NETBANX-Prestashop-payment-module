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
include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/iitc_netbanx.php');

if(intval(Configuration::get('PS_REWRITING_SETTINGS')) === 1)
    $rewrited_url = __PS_BASE_URI__;

// Init variables
$output = array();
$message = '';
$params = $_POST;

// Process the callback
if (!checkHost($_SERVER['REMOTE_ADDR']))
  $output[] = 'security check failed';
else {
  $output[] = 'security check passed';
  
  // Make sure orderID + amount is present
  if (!checkParams($params))
    $output[] = 'No orderID / payment amount given';
  else {
    // Open the order
    $netbanx = new Iitc_netbanx();
    $cartID = intval($params['nbx_merchant_reference']);
    $cart = new Cart($cartID);

    // Format payment amount correctly
    $orderTotal = formatAmount($params['nbx_payment_amount'], Configuration::get("IITC_NETBANX_MINORUNITS"));

    // Fun tip - Prestashop checks the order total for us :)

    // Check the order status 
    switch ($params['nbx_status']) {
    case "passed":
      // Check checksum
      $checksumResult = checkChecksum(Configuration::get("IITC_NETBANX_SECRETKEY"), 
				      $params['nbx_payment_amount'], 
				      $params['nbx_currency_code'], 
				      $params['nbx_merchant_reference'],
				      $params['nbx_netbanx_reference'],
				      $params['nbx_checksum']);
      $output[] = $checksumResult[1];
      
      // Checksum didn't match
      if (!$checksumResult[0]) {
	$message = $checksumResult[1];
	$orderState =  _PS_OS_ERROR_;
      }
      // Order passed
      else {
	$output[] = 'transaction success, order updated';
	$orderState =  _PS_OS_PAYMENT_;
	$message = "NETBANX Reference: " . $_POST['nbx_netbanx_reference'];
      }
      break;
    case "pending":
      $message = "Phone Netbanx, transaction 'pending'.";
      if (array_key_exists('nbx_netbanx_reference', $_POST))
	$message .= "\n(NETBANX Reference: " . $_POST['nbx_netbanx_reference'] . ")";
      
      $output[] = "order on hold, transaction pending";
      $orderState =  _PS_OS_BANKWIRE_;
      break;
    default:
      $message = "Transaction failed, phone Netbanx for details.";
      if (array_key_exists('nbx_netbanx_reference', $_POST))
	$message .= "\n(NETBANX Reference: " . $_POST['nbx_netbanx_reference'] . ")";

      $output[] = "transaction failed, order cancelled.";
      $orderState =  _PS_OS_CANCELED_;
      break;
    }
    
    // Update order
    $netbanx->validateOrder($cartID, $orderState, $orderTotal, $netbanx->displayName, $message);
  }
 }

// Return output for NETBANX's logs
echo implode(',', $output);
?>