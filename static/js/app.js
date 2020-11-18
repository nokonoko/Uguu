/**
 * Copyright (c) 2016 Luminarys <postmaster@gensok.io>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

document.addEventListener('DOMContentLoaded', function() {
  /**
   * Sets up the elements inside file upload rows.
   * 
   * @param {File} file
   * @return {HTMLLIElement} row
   */
  function addRow(file) {
    var row = document.createElement('li');

    var name = document.createElement('span');
    name.textContent = file.name;
    name.className = 'file-name';

    var progressIndicator = document.createElement('span');
    progressIndicator.className = 'progress-percent';
    progressIndicator.textContent = '0%';

    var progressBar = document.createElement('progress');
    progressBar.className = 'file-progress';
    progressBar.setAttribute('max', '100');
    progressBar.setAttribute('value', '0');

    row.appendChild(name);
    row.appendChild(progressBar);
    row.appendChild(progressIndicator);

    document.getElementById('upload-filelist').appendChild(row);
    return row;
  }

  /**
   * Updates the page while the file is being uploaded.
   * 
   * @param {ProgressEvent} evt
   */
  function handleUploadProgress(evt) {
    var xhr = evt.target;
    var bar = xhr.bar;
    var percentIndicator = xhr.percent;
    
    /* If we have amounts of work done/left that we can calculate with 
       (which, unless we're uploading dynamically resizing data, is always), calculate the percentage. */
    if (evt.lengthComputable) {
      var progressPercent = Math.floor((evt.loaded / evt.total) * 100);
      bar.setAttribute('value', progressPercent);
      percentIndicator.textContent = progressPercent + '%';
    }
  }

  /**
   * Complete the uploading process by checking the response status and, if the
   * upload was successful, writing the URL(s) and creating the copy element(s)
   * for the files.
   * 
   * @param {ProgressEvent} evt
   */
  function handleUploadComplete(evt) {
    var xhr = evt.target;
    var bar = xhr.bar;
    var row = xhr.row;
    var percentIndicator = xhr.percent;

    percentIndicator.style.visibility = 'hidden';
    bar.style.visibility = 'hidden';
    row.removeChild(bar);
    row.removeChild(percentIndicator);
    var respStatus = xhr.status;

    var url = document.createElement('span');
    url.className = 'file-url';
    row.appendChild(url);

    var link = document.createElement('a');
    if (respStatus === 200) {
      var response = JSON.parse(xhr.responseText);
      if (response.success) {
        link.textContent = response.files[0].url.replace(/.*?:\/\//g, '');
        link.href = response.files[0].url;
        url.appendChild(link);
        var copy = document.createElement('button');
        copy.className = 'upload-clipboard-btn';
        var glyph = document.createElement('img');
        glyph.src = 'img/glyphicons-512-copy.png';
        copy.appendChild(glyph);
        url.appendChild(copy);
        copy.addEventListener("click", function(event) {
          /* Why create an element?  The text needs to be on screen to be
             selected and thus copied. The only text we have on-screen is the link
             without the http[s]:// part. So, this creates an element with the
             full link for a moment and then deletes it. 
            
             See the "Complex Example: Copy to clipboard without displaying
             input" section at: https://stackoverflow.com/a/30810322 */
          var element = document.createElement('a');
          element.textContent = response.files[0].url;
          link.appendChild(element); 
          var range = document.createRange();
          range.selectNode(element);
          window.getSelection().removeAllRanges();
          window.getSelection().addRange(range);
          document.execCommand("copy");
          link.removeChild(element);
        });
      } else {
        bar.innerHTML = 'Error: ' + response.description;
      }
    } else if (respStatus === 413) {
      link.textContent = 'File too big!';
      url.appendChild(link);
    } else {
      link.textContent = 'Server error!';
      url.appendChild(link);
    }
  }

  /**
   * Updates the page while the file is being uploaded.
   * 
   * @param {File} file
   * @param {HTMLLIElement} row
   */
  function uploadFile(file, row) {
    var bar = row.querySelector('.file-progress');
    var percentIndicator = row.querySelector('.progress-percent');
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'upload.php');

    xhr['row'] = row;
    xhr['bar'] = bar;
    xhr['percent'] = percentIndicator;
    xhr.upload['bar'] = bar;
    xhr.upload['percent'] = percentIndicator;

    xhr.addEventListener('load', handleUploadComplete, false);
    xhr.upload.onprogress = handleUploadProgress;

    var form = new FormData();
    form.append('files[]', file);
    xhr.send(form);
  }

  /**
   * Prevents the browser for allowing the normal actions associated with an event.
   * This is used by event handlers to allow custom functionality without
   * having to worry about the other consequences of that action.
   * 
   * @param {Event} evt
   */
  function stopDefaultEvent(evt) {
    evt.stopPropagation();
    evt.preventDefault();
  }

  /**
   * Adds 1 to the state and changes the text.
   * 
   * @param {Object} state
   * @param {HTMLButtonElement} element
   * @param {DragEvent} evt
   */
  function handleDrag(state, element, evt) {
    stopDefaultEvent(evt);
    if (state.dragCount == 1) {
      element.textContent = 'Drop it here~';
    }
    state.dragCount += 1;
  }

  /**
   * Subtracts 1 from the state and changes the text back.
   * 
   * @param {Object} state
   * @param {HTMLButtonElement} element
   * @param {DragEvent} evt
   */
  function handleDragAway(state, element, evt) {
    stopDefaultEvent(evt);
    state.dragCount -= 1;
    if (state.dragCount == 0) {
      element.textContent = 'Select or drop file(s)';
    }
  }

  /**
   * Prepares files for uploading after being added via drag-drop.
   * 
   * @param {Object} state
   * @param {HTMLButtonElement} element
   * @param {DragEvent} evt
   */
  function handleDragDrop(state, element, evt) {
    stopDefaultEvent(evt);
    handleDragAway(state, element, evt);
    var len = evt.dataTransfer.files.length;
    for (var i = 0; i < len; i++) {
      var file = evt.dataTransfer.files[i];
      var row = addRow(file);
      uploadFile(file, row);
    }
  }
  
  /**
   * Prepares the files to be uploaded when they're added to the <input> element.
   * 
   * @param {InputEvent} evt
   */
  function uploadFiles(evt) {
    var len = evt.target.files.length;
    // For each file, make a row, and upload the file.
    for (var i = 0; i < len; i++) {
      var file = evt.target.files[i];
      var row = addRow(file);
      uploadFile(file, row);
    }
  }
  
  /**
   * Opens up a "Select files.." dialog window to allow users to select files to upload.
   * 
   * @param {HTMLInputElement} target
   * @param {InputEvent} evt
   */
  function selectFiles(target, evt) {
    stopDefaultEvent(evt);
    target.click();
  }

  /* Set-up the event handlers for the <button>, <input> and the window itself
     and also set the "js" class on selector "#upload-form", presumably to
     allow custom styles for clients running javascript. */
  var state = { dragCount: 0 };
  var uploadButton = document.getElementById('upload-btn');
  window.addEventListener('dragenter', handleDrag.bind(this, state, uploadButton), false);
  window.addEventListener('dragleave', handleDragAway.bind(this, state, uploadButton), false);
  window.addEventListener('drop', handleDragAway.bind(this, state, uploadButton), false);
  window.addEventListener('dragover', stopDefaultEvent, false);

  var uploadInput = document.getElementById('upload-input');
  uploadInput.addEventListener('change', uploadFiles);
  uploadButton.addEventListener('click', selectFiles.bind(this, uploadInput));
  uploadButton.addEventListener('drop', handleDragDrop.bind(this, state, uploadButton), false);
  document.getElementById('upload-form').classList.add('js');
});
