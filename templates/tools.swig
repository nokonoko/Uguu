{% extends "layout.swig" %}

{% block title %}Tools{% endblock %}
{% block body %}
<div class="jumbotron">
<h1>Uguu Tools</h1>
</div>
    <h3>
      To upload using curl or make a tool you can post using:

      <br/>

      curl -i -F files[]=@yourfile.jpeg {{siteUrl}}/upload.php (JSON Response)
    </h3>
<section>
	<h2>ShareX</h2>
	<dl>
		<dt>Download</dt>
		<dd><a href="https://getsharex.com">https://getsharex.com</a></dd>
		<dt>Source</dt>
		<dd><a href="https://github.com/ShareX/ShareX">https://github.com/ShareX/ShareX</a></dd>
		<dt>Contact</dt>
		<dd><a href="https://webchat.freenode.net/?channels=%23ShareX">#ShareX on irc.freenode.net</a></dd>
	</dl>
</section>
{% include "nav.swig" %}
{% endblock %}
