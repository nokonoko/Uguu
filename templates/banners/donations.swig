<p class="alert alert-info">
	<strong>{{siteName}} is free to use, but our hosting costs are far from it</strong> &mdash; donations are what keep {{siteName}} alive, free, and fast.</strong>
	<span class="donate-btns">
		{% if paypalUrl != '' %}
			<a class="donate-btn donate-paypal" href="{{paypalUrl}}" target="_BLANK">
				<span class="icon icon-paypal"></span>
				PayPal
			</a>
		{% endif %}
		{% if bitcoinAddress != '' %}
			<a class="donate-btn donate-bitcoin" href="bitcoin:{{bitcoinAddress}}?label={{siteName}}&amp;message=Hosting%20Costs" target="_BLANK">
				<span class="icon icon-bitcoin"></span>
				Bitcoin
			</a>
		{% endif %}
		{% if flattrUrl != '' %}
			<a class="donate-btn donate-flattr" href="{{flattrUrl}}" target="_BLANK">
				<span class="icon icon-flattr"></span>
				Flattr
			</a>
		{% endif %}
	</span>
</p>
