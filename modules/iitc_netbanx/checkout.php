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

include(dirname(__FILE__).'/../../header.php');

$netbanx = new Iitc_netbanx();

// Only allow certain parameters to be passed through
$valid_params = array('nbx_checksum', 'nbx_payment_amount', 'nbx_currency_code', 'nbx_merchant_reference', 'nbx_email', 'nbx_cardholder_name', 'nbx_houseno', 'nbx_postcode', 'nbx_success_url', 'nbx_failure_url', 'nbx_success_redirect_url', 'nbx_failure_redirect_url', 'nbx_return_url', 'nbx_payment_amount', 'nbx_currency_code', 'nbx_merchant_reference');

// Grab the acceptable parameters and filter to prevent XSS
$parameters = array();
foreach ($valid_params as $param_name)
  if (array_key_exists($param_name, $_POST))
      $parameters[$param_name] = htmlspecialchars($_POST[$param_name]);

// Setup the query_string to pass paramters to the iframe
$query_string = "?";
if (Configuration::get("IITC_NETBANX_TESTMODE") == "True")
  $query_string .= "test=True&";

foreach ($parameters as $param => $value)
  $query_string .= urlencode($param) . "=" . urlencode($value) . "&";

// Hide the sidebars
$html  = '<style type="text/css">.column {width:0px !important; display:none;}' .
         '#center_column {width:100%;}' .
         'iframe {border:none;}</style>';

// Draw the iframe
$html .= '<iframe id="IITC_NETBANX_IFRAME" height="700px" width="100%" border="0px" src="modules/iitc_netbanx/redirect.php' . $query_string . '"></iframe>';
echo($html);

include(dirname(__FILE__).'/../../footer.php');
?>