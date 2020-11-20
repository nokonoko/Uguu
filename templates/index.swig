{% extends "layout.swig" %}

{% block body %}
<div class="jumbotron">
	<h1>{{siteName}}~</h1>
	<p class="lead">Max upload size is {{max_upload_size}}&nbsp;MiB & files expire after 24H, read the <a href="faq.html"><abbr title="Frequently asked questions">FAQ</abbr></a></p>
	{% include "upload_form.swig" %}
</div>
{% include "banners.swig" %}
{% include "nav.swig" %}
{% endblock %}
