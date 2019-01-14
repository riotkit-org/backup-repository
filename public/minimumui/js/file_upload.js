
class FileUpload extends BaseUpload {
    constructor (tokenId) {
        super();

        this.serverUrl = '/repository/file/upload?_token=' + tokenId;
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
            allowMultiple: false,
            allowReplace: false,
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
        this.pond.addEventListener('FilePond:addfile', e => {
            let server = e.detail.pond.server.get();
            server.process.url = self.serverUrl + '&fileName=' + e.detail.file.filename;

            e.detail.pond.server.set(server);
        });
    }
}
