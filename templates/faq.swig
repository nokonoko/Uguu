{% extends "layout.swig" %}

{% block title %}FAQ{% endblock %}
{% block body %}
<article>
	<div class="jumbotron">
		<h1><abbr title="Frequently asked questions">FAQ</abbr></h1>
	</div>
	<div class="alert alert-info">
	<h2>What is {{siteName}}?</h2>
	<p><span role="definition"><dfn>{{siteName}}</dfn> is a simple to use free file hosting service.</span> It lets you share your photos, documents, music, videos and more with others online for 24 hours.</p>
	<h2>What files are allowed?</h2>
	<p>{{siteName}} welcomes uploading all files, as long as the file is legal in {{ServerCountryLocation}} and you have the legal right to publish the file on our service.</p>
	<p>As an exception to this policy to prevent abuse, we do not allow malware on our service. Any malware that could be used to infect other computers may be removed from our service at our discretion.</p>
	<h2>Can you remove my copyrighted file?</h2>
	<p>Please submit your copyright takedown notice to <a href="mailto:{{abuseContact}}">{{abuseContact}}</a>. We will handle your notice within 24 hours and disable access to the infringing file after receiving a notice compliant with the Copyright Act 1968.</p>
	<h2>Can you remove files that are defaming me or otherwise infringing my non-copyright rights?</h2>
	<p>{{siteName}} respects takedowns for other files when accompanied with a certified {{ServerCountryLocation}} court order. If you are unable to obtain the order, a preliminary injuction or court order is typically also sufficient. Please forward the notice to <a href="mailto:{{abuseContact}}">{{abuseContact}}</a>.</p>
	<h2>Can you remove illegal files?</h2>
	<p>Please contact the appropriate law enforcement agency if you notice illegal files hosted on {{siteName}}. We have not been trained or qualified to investigate and fight crimes and enforce the law, so it's not appropriate to send accusations of illegal activity to us. <strong>You must contact the appropriate law enforcement office.</strong> They may then contact us if appropriate.</p>
	<p>If you are an {{ServerCountryLocation}}law enforcement official and you need our assistance, please contact <a href="mailto:{{abuseContact}}">{{abuseContact}}</a>. If you are a law enforcement official from another country, we may voluntarily cooperate if the crime you are investigating would also be illegal in {{ServerCountryLocation}}.</p>
	<h2>I have a question...</h2>
	<p>Send us an email at <a href="mailto:{{infoContact}}">{{infoContact}}</a> and let's talk!</p>
</div>
</article>
{% include "nav.swig" %}
{% endblock %}