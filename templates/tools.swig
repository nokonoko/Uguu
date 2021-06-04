{% extends "layout.swig" %}

{% block title %}Tools{% endblock %}
{% block body %}

<div class="jumbotron"><h1>Uguu Tools</h1></div>
<div class="alert alert-info">
  <section>
    <h3>
      To upload using curl or make a tool you can post using:
      <br/>
      curl -i -F files[]=@yourfile.jpeg {{siteUrl}}/upload.php (JSON Response)
    </h3>

<p>{{ToolsDesc}}</p>

  <section>
    <h2>ShareX</h2>
    <dl>
      <dt>Download</dt>
      <dd><a href="https://getsharex.com">https://getsharex.com</a></dd>
      <dt>Source</dt>
      <dd><a href="https://github.com/ShareX/ShareX">https://github.com/ShareX/ShareX</a></dd>
    </dl>
  </section>

  <h2>uguuecho</h2>
  <dl>
    <dt>Download</dt>
    <dd><a href="https://git.teknik.io/nekunekus/uguucho">https://git.teknik.io/nekunekus/uguucho</a></dd>
  </dl>
</section>

  <h2>pomfload</h2>
  <dl>
    <dt>Download</dt>
    <dd><a href="https://github.com/ewhal/pomfload">https://github.com/ewhal/pomfload</a></dd>
  </dl>
</section>

  <section>
    <h2>Pomfshare</h2>

    <dl>
      <dt>Download</dt>
      <dd><a
      href="https://github.com/Nyubis/Pomfshare">https://github.com/Nyubis/Pomfshare</a></dd>
    </dl>
  </section>

  <section>
    <h2>Pomf Rehost</h2>
    <dl>
      <dt>Download</dt>
      <dd>
        <a href="https://git.fuwafuwa.moe/lesderid/pomf-rehost">
          https://git.fuwafuwa.moe/lesderid/pomf-rehost
        </a>
      </dd>
    </dl>
</div>
  </section>
{% include "nav.swig" %}
{% endblock %}
