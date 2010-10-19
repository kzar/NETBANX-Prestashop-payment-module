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

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__) . '/iitc_netbanx.php');

if(intval(Configuration::get('PS_REWRITING_SETTINGS')) === 1)
    $rewrited_url = __PS_BASE_URI__;

$netbanx = new Iitc_netbanx();

// Only allow certain parameters to be passed through
$valid_params = array('nbx_checksum', 'nbx_payment_amount', 'nbx_currency_code', 'nbx_merchant_reference', 'nbx_email', 'nbx_cardholder_name', 'nbx_houseno', 'nbx_postcode', 'nbx_success_url', 'nbx_failure_url', 'nbx_success_redirect_url', 'nbx_failure_redirect_url', 'nbx_return_url', 'nbx_payment_amount', 'nbx_currency_code', 'nbx_merchant_reference', 'test');

// Grab the acceptable parameters and filter to prevent XSS
$parameters = array();
foreach ($valid_params as $param_name)
  if (array_key_exists($param_name, $_GET))
    $parameters[$param_name] = htmlspecialchars($_GET[$param_name]);

if (array_key_exists('test', $parameters) && $parameters['test'] == "True")
  $netbanx_page = "https://pay.test.netbanx.com/" . Configuration::get("IITC_NETBANX_MERCHANTNAME") ;
else
  $netbanx_page = "https://pay.netbanx.com/" . Configuration::get("IITC_NETBANX_MERCHANTNAME");

$smarty->assign('form_tag', '<form name="redirectform" method="POST" action="' . $netbanx_page . '">');

// Setup the form for the template
$form_source = '';
foreach ($parameters as $param => $value)
  $form_source .= '<input type="hidden" name="' . $param . '" value="' . $value . '" />';

$smarty->assign('form', $form_source);

$smarty->display(dirname(__FILE__) . '/redirect.tpl');