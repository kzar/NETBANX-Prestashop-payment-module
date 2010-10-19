{*
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
*}

<p class="payment_module">
  <a href="javascript:$('#IITC_NETBANX_FORM').submit();" title="{l s='Pay through NETBANX' mod='IITC_NETBANX'}">
    <img src="{$module_template_dir}netbanx.gif" alt="{l s='Pay through NETBANX' mod='IITC_NETBANX'}" />
    {l s='Pay through NETBANX' mod='IITC_NETBANX'}
  </a>
</p>

<form action="{$form_target}" method="post" id="IITC_NETBANX_FORM" class="hidden">
{foreach from=$parameters key=parameter_name item=parameter_value}
   <input type="hidden" name="{$parameter_name}" value="{$parameter_value}" />
{/foreach}
</form>