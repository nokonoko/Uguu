/*
 * Uguu
 *
 * @copyright Copyright (c) 2022 Go Johansson (nekunekus) <neku@pomf.se> <github.com/nokonoko>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/*jshint esversion: 6 */

document.addEventListener('DOMContentLoaded', () => {
    /**
     * Sets up the elements inside file upload rows.
     *
     * @param {File} file
     * @return {HTMLLIElement} row
     */
    const addRow = (file) => {
        const row = document.createElement('li');
        const name = document.createElement('span');
        name.textContent = file.name;
        name.className = 'file-name';

        const progressIndicator = document.createElement('span');
        progressIndicator.className = 'progress-percent';
        progressIndicator.textContent = '0%';

        const progressBar = document.createElement('progress');
        progressBar.className = 'file-progress';
        progressBar.setAttribute('max', '100');
        progressBar.setAttribute('value', '0');

        row.appendChild(name);
        row.appendChild(progressBar);
        row.appendChild(progressIndicator);

        document.getElementById('upload-filelist').appendChild(row);
        return row;
    };

    /**
     * Updates the page while the file is being uploaded.
     *
     * @param {ProgressEvent} evt
     */
    const handleUploadProgress = (evt) => {
        const xhr = evt.target;
        const bar = xhr.bar;
        const percentIndicator = xhr.percent;

        if (evt.lengthComputable) {
            const progressPercent = Math.floor((evt.loaded / evt.total) * 100);
            bar.setAttribute('value', progressPercent);
            percentIndicator.textContent = `${progressPercent}%`;
        }
    };

    /**
     * Complete the uploading process by checking the response status and, if the
     * upload was successful, writing the URL(s) and creating the copy element(s)
     * for the files.
     *
     * @param {ProgressEvent} evt
     */
    const handleUploadComplete = (evt) => {
        const xhr = evt.target;
        const bar = xhr.bar;
        const row = xhr.row;
        const percentIndicator = xhr.percent;

        percentIndicator.style.visibility = 'hidden';
        bar.style.visibility = 'hidden';
        row.removeChild(bar);
        row.removeChild(percentIndicator);
        const respStatus = xhr.status;

        const url = document.createElement('span');
        url.className = 'file-url';
        row.appendChild(url);

        const link = document.createElement('a');
        if (respStatus === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                link.textContent = response.files[0].url.replace(/.*?:\/\//g, '');
                link.href = response.files[0].url;
                link.target = "_BLANK";
                url.appendChild(link);

                const copy = document.createElement('button');
                copy.className = 'upload-clipboard-btn';

                const glyph = document.createElement('img');
                glyph.src = 'img/glyphicons-512-copy.png';
                copy.appendChild(glyph);

                url.appendChild(copy);

                copy.addEventListener("click", () => {
                    const element = document.createElement('a');
                    element.textContent = response.files[0].url;
                    link.appendChild(element);
                    const range = document.createRange();
                    range.selectNode(element);
                    window.getSelection().removeAllRanges();
                    window.getSelection().addRange(range);
                    document.execCommand("copy");
                    link.removeChild(element);
                });
            } else {
                bar.innerHTML = `Error: ${response.description}`;
            }
        } else if (respStatus === 413) {
            link.textContent = 'File too big!';
            url.appendChild(link);
        } else {
            const response = JSON.parse(xhr.responseText);
            link.textContent = response.description;
            url.appendChild(link);
        }
    };

    /**
     * Updates the page while the file is being uploaded.
     *
     * @param {File} file
     * @param {HTMLLIElement} row
     */
    const uploadFile = (file, row) => {
        const bar = row.querySelector('.file-progress');
        const percentIndicator = row.querySelector('.progress-percent');
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'upload.php');

        xhr.row = row;
        xhr.bar = bar;
        xhr.percent = percentIndicator;
        xhr.upload.bar = bar;
        xhr.upload.percent = percentIndicator;

        xhr.addEventListener('load', handleUploadComplete, false);
        xhr.upload.onprogress = handleUploadProgress;

        const form = new FormData();
        form.append('files[]', file);
        xhr.send(form);
    };

    /**
     * Prevents the browser for allowing the normal actions associated with an event.
     * This is used by event handlers to allow custom functionality without
     * having to worry about the other consequences of that action.
     *
     * @param {Event} evt
     */
    const stopDefaultEvent = (evt) => {
        evt.stopPropagation();
        evt.preventDefault();
    };

    /**
     * Adds 1 to the state and changes the text.
     *
     * @param {Object} state
     * @param {HTMLButtonElement} element
     * @param {DragEvent} evt
     */
    const handleDrag = (state, element, evt) => {
        stopDefaultEvent(evt);
        if (state.dragCount === 1) {
            element.textContent = 'Drop it here~';
        }
        state.dragCount += 1;
    };

    /**
     * Subtracts 1 from the state and changes the text back.
     *
     * @param {Object} state
     * @param {HTMLButtonElement} element
     * @param {DragEvent} evt
     */
    const handleDragAway = (state, element, evt) => {
        stopDefaultEvent(evt);
        state.dragCount -= 1;
        if (state.dragCount === 0) {
            element.textContent = 'Select or drop file(s)';
        }
    };

    /**
     * Prepares files for uploading after being added via drag-drop.
     *
     * @param {Object} state
     * @param {HTMLButtonElement} element
     * @param {DragEvent} evt
     */
    const handleDragDrop = (state, element, evt) => {
        stopDefaultEvent(evt);
        handleDragAway(state, element, evt);
        const len = evt.dataTransfer.files.length;
        for (let i = 0; i < len; i++) {
            const file = evt.dataTransfer.files[i];
            const row = addRow(file);
            uploadFile(file, row);
        }
    };

    /**
     * Prepares the files to be uploaded when they're added to the <input> element.
     *
     * @param {InputEvent} evt
     */
    const uploadFiles = (evt) => {
        const len = evt.target.files.length;
        // For each file, make a row, and upload the file.
        for (let i = 0; i < len; i++) {
            const file = evt.target.files[i];
            const row = addRow(file);
            uploadFile(file, row);
        }
    };

    /**
     * Opens up a "Select files" dialog window to allow users to select files to upload.
     *
     * @param {HTMLInputElement} target
     * @param {InputEvent} evt
     */
    const selectFiles = (target, evt) => {
        stopDefaultEvent(evt);
        target.click();
    };

    /* Handles the pasting function */
    window.addEventListener("paste", e => {
        const len = e.clipboardData.files.length;
        for (let i = 0; i < len; i++) {
            const file = e.clipboardData.files[i];
            const row = addRow(file);
            uploadFile(file, row);
        }
    });

    /* Set up the event handlers for the <button>, <input> and the window itself
       and also set the "js" class on selector "#upload-form", presumably to
       allow custom styles for clients running javascript. */
    const state = {dragCount: 0};
    const uploadButton = document.getElementById('upload-btn');
    window.addEventListener('dragenter', (evt) => handleDrag(state, uploadButton, evt), false);
    window.addEventListener('dragleave', (evt) => handleDragAway(state, uploadButton, evt), false);
    window.addEventListener('drop', (evt) => handleDragAway(state, uploadButton, evt), false);
    window.addEventListener('dragover', stopDefaultEvent, false);

    const uploadInput = document.getElementById('upload-input');
    uploadInput.addEventListener('change', uploadFiles);
    uploadButton.addEventListener('click', () => selectFiles(uploadInput, event));
    uploadButton.addEventListener('drop', (evt) => handleDragDrop(state, uploadButton, evt), false);
    document.getElementById('upload-form').classList.add('js');
});
