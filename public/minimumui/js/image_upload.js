class ImageUpload extends BaseUpload {

    constructor(aspectRatio, apiKey, redirectUrl) {
        super();

        this.aspectRatio = aspectRatio;
        this.apiKey = apiKey;
        this.cropper = null;
        this.image = document.getElementById('image');
        this.redirectUrl = redirectUrl;
    }

    /**
     * Initializes and re-initializes the cropper
     */
    initializeCropper() {
        this.image.style.display = 'block';

        this.cropper = new Cropper(image, {
            aspectRatio: this.aspectRatio,
            responsive: true,
            guides: true
        });

        window.setTimeout(this._setCropperToCoverWholeImage, 100);
    }

    setCropperToCoverWholeImage() {
        var imageData = this.cropper.getImageData();
        var pos = this.cropper.getCanvasData();

        this.cropper.setCropBoxData({
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
    }

    /**
     * Gets the base64 encoded image URL
     *
     * @returns {string}
     */
    getImage() {
        if (!this.cropper.canvas) {
            return;
        }

        return this.getRawImage().split('base64,')[1];
    };

    getRawImage() {
        if (!this.cropper.canvas) {
            return;
        }

        return this.cropper.canvas.children[0].src;
    }

    getCroppedImage() {
        if (!this.cropper.canvas) {
            return;
        }

        return this.cropper.getCroppedCanvas().toDataURL();
    }

    /**
     * Change the mode between moving and cropping
     *
     * @param {string} mode
     */
    changeMode(mode) {
        if (this.cropper != null) {
            this.cropper.setDragMode(mode);
        }
    }

    /**
     * Crop the image
     */
    crop() {
        if (this.cropper === null) {

            return null;
        }

        // crop
        this.image.src = this.getCroppedImage();
        this.destroy();
        this.initializeCropper();
    }

    /**
     * Upload the image
     */
    accept() {
        if (this.cropper === null) {
            return null;
        }

        this.toggleModal();
    }

    toggleModal() {
        $('#submitModal').modal('toggle');
    }

    /**
     * Upload the image to the server
     * and redirect ot the callback url specified in the constructor
     */
    upload() {
        var params = $('#submitForm').serialize();
        var image_upload = this;

        var xmlHttp = new XMLHttpRequest();
        xmlHttp.open('POST', '/repository/file/upload?encoding=base64&_token=' + this.apiKey + '&' + params);
        xmlHttp.setRequestHeader('Content-Type', 'application/json');
        xmlHttp.onreadystatechange = () => {
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
    }

    uploadFinished(url) {
        this.toggleModal();

        if (this.redirectUrl) {
            window.location.href = this.redirectUrl.replace('FILE_REPOSITORY_URL', encodeURIComponent(url));
            return null;
        }

        if (typeof window.parent.file_repository_callback !== 'undefined') {
            window.parent.file_repository_callback(url);
            return;
        }

        this.showFileUrl(url);
        window.close();
    }

    /**
     * Deinitialize the cropper
     */
    destroy() {
        if (this.cropper != null) {
            this.cropper.destroy();
        }
    }

    /**
     * Handles upload event
     *
     * @param e
     */
    handleImageUpload(e) {
        var reader = new FileReader();
        var self = window.app;

        reader.onload = (event) => {
            // replace image and initialize the cropper again
            self.image.src = event.target.result;
            self.destroy();
            self.initializeCropper();
        };
        reader.readAsDataURL(e.target.files[0]);
    }
}

function b64EncodeUnicode(str) {
    return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function(match, p1) {
        return String.fromCharCode('0x' + p1);
    }));
}

function b64Decode(str) {
    return atob(str);
}
