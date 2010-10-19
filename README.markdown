About
-----

This is a payment module for the Prestashop shopping cart which allows you to process transactions through the NETBANX payment gateway.

The module was originally released commercially with IITC Ltd. but as the company has stopping trading I have decided to open source it.

You can still [view the original page](http://iitc.info/products/prestashop-netbanx) for now, any new information is going to be left here or [on my blog](http://kzar.co.uk/blog/view/netbanx-prestashop-payment-module).

Installation
------------

- Extract the archive you downloaded into the Prestashop modules directory
- Log into the Prestashop admin system and click "Modules".
- Scroll down to the Payment section and click the Install button next to "Netbanx (UPP)".
- Scroll down again and click the ">> Configure" button next to "Netbanx (UPP)".
- Follow the on screen instructions to set up the module. Make sure your Merchant name and other settings are correct and then push the "Save your changes" button when you're finished.

Usage
-----

- The NETBANX payment option should now be visible to your customers, orders using NETBANX will have a message saved detailing the NETBANX reference.
- When you first install the module I recommend performing tests and checking these details to make sure everything is working correctly.

Notes
-----

- Orders are updated when the NETBANX server performs a 'call back' reply to your system. There is sometimes a delay between the order being processed and this call back, if orders are taking a long time to complete ask NETBANX to perform call backs immediately for your integration.

Support
-------

For commercial support email Dave, kzar@kzar.co.uk .

License
-------

This file is part of the Prestashop Netbanx payment module (iitc).          
Copyright Dave Barker 2009                                                
                                                                          
The Prestashop Netbanx payment module (iitc) is free software: you can    
redistribute it and/or modify it under the terms of the GNU General Public
License as published by the Free Software Foundation, either version 3    
of the License, or (at your option) any later version.                    
                                                                          
the Prestashop Netbanx payment module (iitc) is distributed in the hope   
that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
GNU General Public License for more details.                              
                                                                          
You should have received a copy of the GNU General Public License         
along with the Prestashop Netbanx payment module (iitc).                  
If not, see <http://www.gnu.org/licenses/>.                                
