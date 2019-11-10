
class FileUpload extends BaseUpload {
    constructor (tokenId, formOpts, tags, tagsAreEnforced, maxFileSize, labelUploadFiles, labelUploadLimit) {
        super();

        this.serverUrl        = '/repository/file/upload?_token=' + tokenId;
        this.formOpts         = formOpts;
        this.maxFileSize      = maxFileSize;
        this.tags             = tags;
        this.tagsAreEnforced  = tagsAreEnforced;
        this.labelUploadFiles = labelUploadFiles;
        this.labelUploadLimit = labelUploadLimit;

        this.initializeFilepond();
    }

    onError(response) {
        var jsonData = JSON.parse(response);

        if (jsonData.http_code > 399) {
            this.validationFailure(jsonData.fields, jsonData.status);
        }

        return true;
    }

    onLoad(response) {
        let jsonData = JSON.parse(response);
        this.showFileUrl(jsonData.url);
        this.addUploadedUrlToFileGui(jsonData.url, jsonData.requested_filename);
    }

    addUploadedUrlToFileGui(url, imageName) {
        let element = $('.filepond--item:contains(' + imageName + ')').find('.filepond--image-preview-wrapper').first();

        $('.filepond--item').each((k, i) => {
            let item = $(i);

            if (item.html().indexOf(imageName) !== -1) {
                item.on('click', () => {
                    this.showFileUrl(url);
                });
            }
        });
    }

    getFormOpts() {
        if (this.formOpts) {
            return '&' + this.formOpts
        }

        return '';
    }

    static shouldFileBePublic() {
        return document.getElementById('is_public').checked;
    }

    static getPassword() {
        let password = document.getElementById('password');

        if (!password) {
            return '';
        }

        return password.value;
    }

    getLabel() {
        let msg = this.labelUploadFiles;

        if (this.maxFileSize) {
            msg += this.labelUploadLimit.replace('%size%', this.maxFileSize);
        }

        return msg;
    }

    getTags() {
        if (this.tagsAreEnforced) {
            return this.tags;
        }

        return document.getElementById('tags_selector').value.split(',');
    }

    initializeFilepond() {
        let self = this;

        FilePond.registerPlugin(
            // encodes the file as base64 data
            //FilePondPluginFileEncode,

            // validates the size of the file
            FilePondPluginFileValidateSize,

            // corrects mobile image orientation
            FilePondPluginImageExifOrientation,

            // previews dropped images
            FilePondPluginImagePreview
        );

        FilePond.setOptions({
            allowMultiple: true,
            allowReplace: false,
            allowRevert: false,
            maxParallelUploads: 1,
            itemInsertInterval: 1000,
            allowPaste: false,
            labelIdle: self.getLabel(),
            server: {
                process: {
                    url: this.serverUrl,
                    method: 'POST',
                    onload: (response) => {
                        return this.onLoad(response);
                    },
                    onerror: (response) => {
                        return this.onError(response);
                    }
                },
                restore: null
            }
        });

        // Select the file input and use create() to turn it into a pond
        FilePond.create(
            document.querySelector('input')
        );

        this.pond = document.querySelector('.filepond--root');

        /**
         * Set filename in the request basing on the uploaded file information
         */
        this.pond.addEventListener('FilePond:addfilestart', e => {
            let server = e.detail.pond.server.get();
            server.process.url = self.serverUrl;

            // support decoded from opts passed via query string to the form
            server.process.url += self.getFormOpts();

            // tags from the token
            let tags = self.getTags();
            for (let tag in tags) {
                server.process.url += '&tags[]=' + tags[tag];
            }

            // public/private
            server.process.url += '&public=' + FileUpload.shouldFileBePublic();

            // password
            if (FileUpload.getPassword()) {
                server.process.url += '&password=' + FileUpload.getPassword();
            }

            e.detail.pond.server.set(server);
        });
    }
}
