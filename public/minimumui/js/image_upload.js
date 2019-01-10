function ImageUpload(aspectRatio, apiKey, redirectUrl) {
    var image_upload = this;

    this.aspectRatio = aspectRatio;
    this.apiKey      = apiKey;
    this.cropper     = null;
    this.image       = document.getElementById('image');
    this.redirectUrl = redirectUrl;

    /**
     * Initializes and re-initializes the cropper
     */
    this.initializeCropper = function () {
        image_upload.image.style.display = 'block';

        image_upload.cropper = new Cropper(image, {
            aspectRatio: this.aspectRatio,
            responsive: true,
            guides: true
        });

        window.setTimeout(this._setCropperToCoverWholeImage, 100);
    };

    this._setCropperToCoverWholeImage = function() {
        var imageData = image_upload.cropper.getImageData();
        var pos = image_upload.cropper.getCanvasData();

        image_upload.cropper.setCropBoxData({
            "rotate":0,
            "scaleX":1,
            "scaleY":1,
            "naturalWidth": imageData.naturalWidth,
            "naturalHeight": imageData.naturalHeight,
            "aspectRatio":  imageData.aspectRatio,
            "width":imageData.naturalWidth,
            "height":imageData.naturalHeight,
            "left": pos.left,
            "top": pos.top
        });
    };

    /**
     * Gets the base64 encoded image URL
     *
     * @returns {string}
     */
    this.getImage = function() {
        if (!image_upload.cropper.canvas) {
            return;
        }

        return this.getRawImage().split('base64,')[1];
    };

    this.getRawImage = function () {
        if (!image_upload.cropper.canvas) {
            return;
        }

        return image_upload.cropper.canvas.children[0].src;
    };

    this.getCroppedImage = function () {
        if (!image_upload.cropper.canvas) {
            return;
        }

        return image_upload.cropper.getCroppedCanvas().toDataURL();
    };

    /**
     * Change the mode between moving and cropping
     *
     * @param {string} mode
     */
    this.changeMode = function (mode) {
        if (image_upload.cropper != null) {
            image_upload.cropper.setDragMode(mode);
        }
    };

    /**
     * Crop the image
     */
    this.crop = function () {
        if (image_upload.cropper === null) {

            return null;
        }

        // crop
        image_upload.image.src = image_upload.getCroppedImage();
        image_upload.destroy();
        image_upload.initializeCropper();
    };

    /**
     * Upload the image
     */
    this.accept = function () {
        if (image_upload.cropper === null) {
            return null;
        }

        image_upload.toggleModal();
    };

    this.toggleModal = function () {
        $('#submitModal').modal('toggle');
    };

    this.showUploadedUrlModal = function (url) {
        $('#linkConfirmationModal').modal();
        $('#resultUrl').val(url);
    };

    /**
     * Upload the image to the server
     * and redirect ot the callback url specified in the constructor
     */
    this.upload = function () {
        var params = $('#submitForm').serialize();

        var xmlHttp = new XMLHttpRequest();
        xmlHttp.open('POST', '/repository/file/upload?encoding=base64&_token=' + image_upload.apiKey + '&' + params);
        xmlHttp.setRequestHeader('Content-Type', 'application/json');
        xmlHttp.onreadystatechange = function() {
            if (xmlHttp.readyState === XMLHttpRequest.DONE) {
                var response = JSON.parse(xmlHttp.responseText);

                if (response.http_code < 400) {
                    image_upload.uploadFinished(response.url);
                }

                if (response.http_code >= 400) {
                    image_upload.validationFailure(response.fields, response.status);
                }
            }
        };
        xmlHttp.send(this.getRawImage());
    };

    this.validationFailure = function (byField, overallStatus) {

        var statusByField = '';

        for (var field in byField) {
            statusByField += field + ": \n";

            for (var messageNum in byField[field]) {
                statusByField += ' - ' + byField[field][messageNum] + "\n"
            }

            statusByField += "\n\n";
        }

        window.alert(overallStatus + "\n\n" + statusByField);
    };

    this.uploadFinished = function (url) {
        image_upload.toggleModal();

        window.console.info('URL:', url);

        if (this.redirectUrl) {
            window.location.href = this.redirectUrl.replace('FILE_REPOSITORY_URL', encodeURIComponent(url));
            return null;
        }

        if (typeof window.parent.file_repository_callback !== 'undefined') {
            window.parent.file_repository_callback(url);
            return;
        }

        this.showUploadedUrlModal(url);
        window.close();
    };

    /**
     * Deinitialize the cropper
     */
    this.destroy = function () {
        if (image_upload.cropper != null) {
            image_upload.cropper.destroy();
        }
    };

    /**
     * Handles upload event
     *
     * @param e
     */
    this.handleImageUpload = function(e) {
        var reader = new FileReader();

        reader.onload = function(event){
            // replace image and initialize the cropper again
            image_upload.image.src = event.target.result;
            image_upload.destroy();
            image_upload.initializeCropper();
        };
        reader.readAsDataURL(e.target.files[0]);
    };
}

function b64EncodeUnicode(str) {
    return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function(match, p1) {
        return String.fromCharCode('0x' + p1);
    }));
}

function b64Decode(str) {
    return atob(str);
}
