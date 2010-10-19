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

if (!parseBoolString(Configuration::get("IITC_NETBANX_IFRAME"))) 
  include(dirname(__FILE__).'/../../header.php');

// Prepare information
$smarty->assign('reference', htmlentities($_GET['nbx_netbanx_reference']));
$smarty->assign('amount', currencySymbol($_GET['nbx_currency_code']) . formatAmount($_GET['nbx_payment_amount'], Configuration::get("IITC_NETBANX_MINORUNITS")));

$smarty->display(dirname(__FILE__) . '/success.tpl');

if (!parseBoolString(Configuration::get("IITC_NETBANX_IFRAME")))
  include(dirname(__FILE__).'/../../footer.php');
