<?php if(!class_exists('raintpl')){exit;}?><div class="container">
    <form class="col s12" action="api.php?d=upload" method="post" enctype="multipart/form-data">
        <input type="hidden" name="MAX_FILE_SIZE" value="150000000" />
            <div class="file-field input-field">
                <input class="file-path validate" type="text" readonly="readonly"/>
                    <div class="btn ie-btn-fix">
                        <span>File</span>
                        <input id="file" type="file" name="file" />
                    </div>
                </div>

                <p>
                    <input type="checkbox" id="randomname" name="randomname" />
                    <label for="randomname">Generate random filename</label>
                </p>

                <div class="row">
                    <div class="input-field col s12">
                        <input id="customname" type="text" name="name">
                        <label for="customname">Custom filename, e.g cat.flac (optional)</label>
                    </div>
                </div>

                <button class="btn waves-effect waves-light" type="submit">Upload
                    <i class="mdi-content-send right"></i>
                </button>
            </div>
    </form>
</div>
