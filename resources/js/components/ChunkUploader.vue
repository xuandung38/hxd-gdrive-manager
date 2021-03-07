<template>
    <div class="file-uploader">
        <div
            v-show="!uploading"
            class="file-selector">
            <input
                :id="uploaderId"
                type="file"
            />
            <el-button
                type="primary"
                small
                @click="uploadFile"
                class="box-shadow"
            >
                {{ $t('common.upload')}}
            </el-button>
        </div>
        <div
            v-show="uploading"
            class="process">
            <h3>{{ $t('common.uploading')}}</h3>
            <el-progress
                :text-inside="true"
                :stroke-width="25"
                :percentage="uploadPercent"
            ></el-progress>
        </div>
    </div>
</template>
<script>
export default {
    props: {
        chunkSize: {
            Type: Number,
            default: 1048576 * 2, //2mb
        },
        domain: {
            Type: String,
            default: '',
        },
        directory: {
            Type: String,
            default: '',
        },
        uploaderId: {
            Type: String,
            default: 'uploader',
        },
        onSuccess: {
            Type: Function,
            default () {
                return null
            },
        }
    },
    data () {
        return {
            reader: {},
            uploadPercent: 0,
            uploading: false,
        }
    },
    methods: {
        uploadFile () {
            if (this.uploading) {
                return
            }

            const file = document.querySelector('#' + this.uploaderId).files[0]

            if (this._.isUndefined(file)) {
                return
            }

            this.uploading = true
            this.reader = new FileReader()
            this.uploadPercent = 0
            this.uploadChunk(file, 0, this.chunkSize, '')
        },
        uploadChunk (file, offset, range, hash) {
            if (offset >= file.size) {
                this.uploadPercent = 100
                this.uploading = false
                return
            }
            const that = this
            this.reader.onloadend = async e => {
                try {
                    if (e.target.readyState !== FileReader.DONE) {
                        return
                    }
                    let payload = {
                        dir: that.directory,
                        hash: hash,
                        name: file.name,
                        type: file.name.split('.').pop().toLowerCase(),
                        mime: file.type,
                        offset: offset / that.chunkSize,
                        data: e.target.result.split('base64,')[1],
                        eof: (offset + range) >= file.size,
                    }

                    const response = await that.Request.post(that.domain + '/chunk-upload', payload)
                    that.uploadPercent = Math.floor((offset / file.size) * 100)
                    that.uploadChunk(file, offset + range, range, response.data.hashName)

                    if (payload.eof) {
                        that.hashName = ''
                        that.uploading = false
                        that.onSuccess(response.data)
                        this.$notify({
                            type: 'success',
                            title: this.$t('common.success'),
                            message: this.$t('common.upload_success'),
                        })
                    }
                } catch (e) {
                    this.uploadPercent = 0
                    this.uploading = false
                    const errorMessages = this.Request.errors(e.response);
                    this.$notify({
                        type: 'error',
                        title: this.$t('common.error'),
                        message: this._.isEmpty(errorMessages) ? this.$t('common.unknown_error') : errorMessages[0],
                    });
                }
            }

            const chunk = file.slice(offset, offset + range)
            this.reader.readAsDataURL(chunk)
        },
    }
}
</script>
