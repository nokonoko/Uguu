<?php if(!class_exists('raintpl')){exit;}?><div class="container">
    <div class="row">
        <div class="col s12">
            <div class="card-panel blue-grey darken-1">
                <div class="card-content white-text">
                    <span class="card-title">Info</span>
                    <p>Store any filetype with a size up to 150MB for up to 1 hour.
                    Uguu cares about your privacy and stores NO logs.
                    </p>

                    <p>
                    If you would like to upload using ShareX read <a style="color: #bbdefb" href="https://github.com/ShareX/ShareX/wiki/Custom-Uploader-examples#uguuse">this</a>.<br />
                    To upload using curl or make a tool you can post using:<br />
                        <code>curl -i -F name=test.jpg -F file=@localfile.jpg http://uguu.se/api.php?d=upload</code> (HTML Response)<br />
                        <code>curl -i -F name=test.jpg -F file=@localfile.jpg http://uguu.se/api.php?d=upload-tool</code> (Plain text Response)</p>
                </div>
            </div>
        </div>
    </div>
</div>
