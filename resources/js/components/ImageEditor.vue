<template>
    <div class="image-editor">
        <vue-cropper
            ref="cropper"
            class="cropper"
            :aspectRatio="1"
            :src="imageSrc"
            preview=".previewer"
        />
      <div class="navigator">
          <div class="previewer"></div>
          <div class="control-buttons">
              <el-button-group>
                  <el-button
                      type="warning"
                      size="mini"
                      @click="rotate(90)"
                  >
                      <i class="fas fa-sync-alt"></i>
                  </el-button>
                  <el-button
                      title="FlipX"
                      type="warning"
                      size="mini"
                      @click="flipX"
                  >
                      <i class="fas fa-arrows-alt-h"></i>
                  </el-button>
                  <el-button
                      title="FlipY"
                      type="warning"
                      size="mini"
                      @click="flipY"
                  >
                      <i class="fas fa-arrows-alt-v"></i>
                  </el-button>
              </el-button-group>
              <el-button-group>
                  <el-button
                      title="Scale 16:9"
                      type="primary"
                      size="mini"
                      @click="setAspectRatio(16/9)"
                  >
                      16:9
                  </el-button>
                  <el-button
                      title="Scale 4:3"
                      type="primary"
                      size="mini"
                      @click="setAspectRatio(4/3)"
                  >
                      4:3
                  </el-button>
                  <el-button
                      title="Scale 2:3"
                      type="primary"
                      size="mini"
                      @click="setAspectRatio(2/3)"
                  >
                      2:3
                  </el-button>
                  <el-button
                      title="Scale 1:1"
                      type="primary"
                      size="mini"
                      @click="setAspectRatio(1)"
                  >
                      1:1
                  </el-button>
                  <el-button
                      title="Free Scale"
                      type="primary"
                      size="mini"
                      @click="setAspectRatio(null)"
                  >
                      *:*
                  </el-button>
              </el-button-group>
              <el-button-group>
                  <el-button
                      size="mini"
                      @click="reset"
                  >
                      <i class="fas fa-undo"></i>
                  </el-button>
                  <el-button
                      type="success"
                      size="mini"
                      @click="cropImage()"
                  >
                      <i class="fas fa-save"></i>
                  </el-button>
                  <el-button
                      type="danger"
                      size="mini"
                      @click="onCancel()"
                  >
                      <i class="fas fa-window-close"></i>
                  </el-button>
              </el-button-group>
          </div>
      </div>
    </div>
</template>

<script>
    import VueCropper from 'vue-cropperjs';
    import 'cropperjs/dist/cropper.css';

    export default {
        components: {
            VueCropper,
        },
        props: {
            domain: {
                type: String,
                default: ''
            },
            targetImage: {
                type: Object,
                default() {
                    return {};
                }
            },
            directory: {
                type: String,
                default: '',
            },
            onSuccess: {
                type: Function,
                default() {
                    return null;
                }
            },
            onCancel: {
                type: Function,
                default() {
                    return null;
                }
            }
        },
        data() {
            return {
                scaleX: 1,
                scaleY: 1,
                img: 'images/bg.jpg',
            };
        },
        computed: {
            imageSrc() {
                return this._.isEmpty(this.targetImage.url) ? this.img : this.targetImage.url;
            }
        },
        watch: {
            imageSrc() {
                this.replaceImg(this.targetImage.url);
            }
        },
        methods: {
            replaceImg(url) {
                this.$refs.cropper.replace(url);
            },
            setAspectRatio(ratio) {
                this.$refs.cropper.setAspectRatio(ratio);
            },
            async callbackUpload(blob) {
                this.$loading({
                    lock: true,
                    text: 'Loading',
                    spinner: 'el-icon-loading',
                    background: 'rgba(0, 0, 0, 0.7)'
                });
                try {
                    const uri = this.domain + '/single-upload';
                    const formData = new FormData();
                    formData.append('file', blob);
                    formData.append('dir', this.directory);
                    formData.append('mime', 'image/png');
                    const response = await this.Request.upload(uri, formData);
                    this.onSuccess(response.data);
                    this.$notify({
                        type: 'success',
                        title: this.$t('common.success'),
                        message: this.$t('message.edit_success'),
                    });
                } catch (e) {
                    const errorMessages = this.Request.errors(e.response);
                    this.$notify({
                        type: 'error',
                        title: this.$t('common.error'),
                        message: this._.isEmpty(errorMessages) ? this.$t('common.unknown_error') : errorMessages[0],
                    });
                }
                this.$loading().close();
            },
            cropImage() {
                this.$refs.cropper.getCroppedCanvas().toBlob(this.callbackUpload);
            },
            flipX() {
                this.scaleX = (this.scaleX === -1) ? 1 : -1;
                this.$refs.cropper.scaleX(this.scaleX);
            },
            flipY() {
                this.scaleY = (this.scaleY === -1) ? 1 : -1;
                this.$refs.cropper.scaleY(this.scaleY);
            },
            reset() {
                this.$refs.cropper.reset();
            },
            rotate(deg) {
                this.$refs.cropper.rotate(deg);
            },

        },
    };
</script>
