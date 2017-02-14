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
            aspectRatio: 4 / 3,
            responsive: true,
            guides: true
        });
    };

    /**
     * Gets the base64 encoded image URL
     *
     * @returns {string}
     */
    this.getImage = function() {
        return image_upload.cropper.getCroppedCanvas().toDataURL("image/jpeg");
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
    this.accept = function () {
        if (image_upload.cropper != null) {

            // crop
            image_upload.image.src = image_upload.getImage();
            image_upload.destroy();
            image_upload.initializeCropper();

            // and upload
            image_upload.upload();
        }
    };

    /**
     * Get mime type name
     *
     * @returns {string}
     */
    this.getMime = function () {
        return image_upload.getImage().split(';base64')[0].replace('data:', '');
    };

    /**
     * Get image extension basing on mime type name
     *
     * @returns {string}
     */
    this.getExtension = function () {

        switch (this.getMime()) {
            case 'image/jpeg':
            case 'image/jpg':
                return '.jpg';
            case 'image/png':
                return '.png';
            case 'image/gif':
                return '.gif';
            case 'image/webp':
                return '.webp';
        }

        return '';
    };

    /**
     * Upload the image to the server
     * and redirect ot the callback url specified in the constructor
     */
    this.upload = function () {
        var xmlHttp = new XMLHttpRequest();
        xmlHttp.open('POST', '/public/upload/image?_token=' + image_upload.apiKey);
        xmlHttp.setRequestHeader('Content-Type', 'application/json');
        xmlHttp.onreadystatechange = function() {
            if (xmlHttp.readyState == XMLHttpRequest.DONE) {
                var response = JSON.parse(xmlHttp.responseText);
                var url = b64EncodeUnicode(response.url);

                window.location.href = image_upload.redirectUrl.replace('|url|', url).replace('%257Curl%257C', url);
            }
        };
        xmlHttp.send(JSON.stringify({
            'content': this.getImage(),
            'fileName': Math.random().toString(36).substr(2, 30) + image_upload.getExtension(),
            'mimeType': image_upload.getMime()
        }));
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