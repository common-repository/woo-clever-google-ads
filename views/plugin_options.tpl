<?php
/**

* NOTICE OF LICENSE

*

* This file is licenced under the Software License Agreement.

* With the purchase or the installation of the software in your application

* you accept the licence agreement.

*

* You must not modify, adapt or create derivative works of this source code

*

*  @author    Carlos García Vega

*  @copyright 2010-2015 CleverPPC S.L.

*  @license   LICENSE.txt

*/
?>

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<body>
	<div class="d-flex flex-column bd-highlight mb-3">
		<div class="p-2 bd-highlight">
			<button type="button" class="btn float-right btn_begin" onclick="window.open('https://woocommerce.cleverecommerce.com/?hmac=<?php echo $data ?>', '_blank');">
				Begin installation
			</button>
		</div>
		<div class="p-2 bd-highlight row">
			<div class="col-xs-0 col-md-2 col-lg-3"></div>
			<div class="col-xs-12 col-md-8 col-lg-6">
				<img style="width: 100%" src="<?php echo plugins_url( 'images/woocommerce_logo_cczssx.png', __FILE__ ); ?>">
			</div>
		</div>
		<div class="p-2 row bd-highlight align-items-center" id="reverse">
			<div class="col-xs-12 col-md-6 col-lg-4 p-2 flex-fill bd-highlight">
				<div class="bd-highlight mb-3">
				  	<div class="p-2 bd-highlight clever_title">
				  		Start your ad on Google today!
				  	</div>
				  	<div class="p-2 bd-highlight clever_subtitle">
				  		Advertise your products all over Google's huge network and get more traffic. Install now and get a promotional coupon* up to 120&#8364; to spend on your campaigns.
				  	</div>
				  	<div class="p-2 bd-highlight clever_subtitle d-flex justify-content-center">
				  		<button type="button" class="btn btn_start" onclick="window.open('https://woocommerce.cleverecommerce.com/?hmac=<?php echo $data ?>', '_blank');">
							Start installation and get the coupon!
						</button>
				  	</div>
				</div>
			</div>
			<div class="col-4" id="first_img">
				<img style="width: 100%;" src="<?php echo plugins_url( 'images/charts-and-graphs-clever_logo-colour.svg', __FILE__ ); ?>"/>
			</div>
			<div class="col-xs-12 col-md-6 col-lg-4" id="second_img">
				<img style="width: 100%;" src="<?php echo plugins_url( 'images/woman-server.svg', __FILE__ ); ?>"/>
			</div>
		</div>
		<div class="p-2 d-flex bd-highlight clever_little_text">
			*The coupon will be automatically applied only if the Google Ads account selected complies with Google's requirements.
		</div>
	</div>
</body>
