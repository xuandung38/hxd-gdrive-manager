<template>
    <div class="file-manager">
        <div class="main">
            <div class="navbar box-shadow">
                <div class="breadcrumb">
                    <el-breadcrumb separator-class="el-icon-arrow-right">
                        <el-breadcrumb-item
                            v-for="item in breadCrumb"
                            :key="item.path"
                            class="breadcrumb-item"
                        >
                            <a @click="handleDiscover(item.path)">{{ item.name }}</a>
                        </el-breadcrumb-item>
                    </el-breadcrumb>
                </div>
                <div class="wrapper">
                    <div class="common-button">
                        <el-button
                            :title="$t('common.chunk_upload')"
                            type="primary"
                            icon="el-icon-upload"
                            class="mr-left box-shadow"
                            @click="isShowChunkUpload = true"
                        ></el-button>
                        <el-upload
                            ref="upload"
                            name="upload"
                            :multiple="true"
                            :http-request="handleUpload"
                            :show-file-list="false"
                            :action="this.domain + '/single-upload'"
                        >
                            <el-button
                                :title="$t('common.upload')"
                                type="success"
                                class="box-shadow"
                                icon="el-icon-upload2"
                            ></el-button>
                        </el-upload>
                        <el-button
                            :title="$t('common.create_folder')"
                            type="warning"
                            icon="el-icon-folder-add"
                            class="mr-left box-shadow"
                            @click="isShowMakeDirectory = true"
                        ></el-button>
                        <el-button
                            :title="$t('common.delete')"
                            class="box-shadow"
                            type="danger"
                            icon="el-icon-delete"
                            @click="beforeExec('delete')"
                        ></el-button>
                        <el-button
                            v-if="currentSelected > 0"
                            type="primary"
                            class="box-shadow"
                            @click="handleSendListToSelector"
                        >
                            {{ $t('common.select') }} {{ currentSelected }} {{ $t('common.object')}}
                        </el-button>
                        <el-dropdown
                            @command="handleSort"
                        >
                            <el-button
                                :title="$t('common.sort')"
                                class="box-shadow"
                            >
                                <i class="fa fa-sort"></i>
                            </el-button>
                            <el-dropdown-menu slot="dropdown">
                                <el-dropdown-item command="1">{{ $t('common.lastest') }}</el-dropdown-item>
                                <el-dropdown-item command="2">{{ $t('common.oldest') }}</el-dropdown-item>
                                <el-dropdown-item command="3">{{ $t('common.biggest') }} </el-dropdown-item>
                                <el-dropdown-item command="4" >{{ $t('common.smallest') }}</el-dropdown-item>
                            </el-dropdown-menu>
                        </el-dropdown>
                    </div>
                </div>
            </div>
            <div class="main-wrapper">
                <div class="content">
                    <div class="folder-list">
                        <div
                            class="folder-item box-shadow"
                            v-for="directory in directoriesSource" :key="directory.name"
                        >
                            <img src="/images/folder.jpg"
                                 alt=""
                                 @click="handleDiscover(directory.path)">
                            <span class="folder-name">{{ directory.name }}</span>
                            <el-button
                                :type="directory.is_selected ? 'success' : '' "
                                icon="el-icon-check"
                                class="selector"
                                circle
                                size="mini"
                                @click="handleTick(directory)"
                            ></el-button>
                        </div>
                    </div>
                    <div class="image-list">
                        <div
                            class="image-item box-shadow"
                            v-for="file in filesSource" :key="file.name"
                        >
                            <el-image
                                :src="getFileThumbnail(file)"
                                lazy
                                @click="handleChooseImage(file)"
                            >
                                <div class="loading" slot="placeholder">
                                    <i class="fas fa-spinner"></i>
                                </div>
                                <div class="error" slot="error">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                            </el-image>
                            <div class="image-detail" @click="handleChooseImage(file)">
                                <p>
                                    Mime: {{ file.mime }} <br>
                                    {{ $t('common.size')}}: {{ Math.round(file.size/1024) }} KB <br>
                                    {{ $t('common.last_modified')}}: {{ getDate(file.last_modified) }}
                                </p>
                            </div>

                            <strong class="image-name">{{ file.name }}</strong>
                            <el-button-group class="control-button">
                                <el-button
                                    :title="$t('common.edit')"
                                    type="warning"
                                    icon="el-icon-edit"
                                    size="small"
                                    @click="showImageEditor(file)"
                                ></el-button>
                                <a
                                    :href="file.url"
                                    download
                                >
                                    <el-button
                                        :title="$t('common.download')"
                                        type="success"
                                        icon="el-icon-download"
                                        size="small"
                                    ></el-button>
                                </a>
                            </el-button-group>
                            <el-button
                                :title="$t('common.multi_select')"
                                :type="file.is_selected ? 'success' : '' "
                                icon="el-icon-check"
                                class="selector"
                                circle
                                size="mini"
                                @click="handleTick(file)"
                            ></el-button>
                        </div>
                    </div>
                    <el-dialog
                        :visible.sync="isShowImageEditor"
                        center
                        fullscreen
                    >
                        <image-editor
                            :domain="domain"
                            :target-image="targetImage"
                            :directory="directory"
                            :on-success="onEditSuccess"
                            :on-cancel="onEditCancel"
                        ></image-editor>
                    </el-dialog>
                    <el-dialog
                        :visible.sync="isShowChunkUpload"
                        class="dialog-medium"
                        center
                    >
                       <chunk-uploader
                           :domain="domain"
                           :directory="directory"
                           :on-success="onChunkUploadSuccess"
                       ></chunk-uploader>
                    </el-dialog>
                    <el-dialog
                        :title="$t('common.create_folder')"
                        :visible.sync="isShowMakeDirectory"
                        class="dialog-small"
                        center
                    >
                        <div class="text-center">
                            <el-input v-model="directoryName"></el-input>
                        </div>
                        <span slot="footer" class="dialog-footer">
                                <el-button class="box-shadow" type="primary" @click="handleMakeDirectory">{{ $t('common.create')}}</el-button>
                                <el-button class="box-shadow" @click="isShowMakeDirectory = false">{{ $t('common.cancel')}}</el-button>
                            </span>
                    </el-dialog>
                    <delete-confirm-dialog
                        class="dialog-small"
                        :title="this.$t('message.confirm_delete_label')"
                        :visible="isShowConfirmDelete"
                        :on-confirm="handleDelete"
                        :on-cancel="closeDeleteDialog"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import ImageEditor from "./ImageEditor";
import DeleteConfirmDialog from './DeleteConfirmDialog';
import ChunkUploader from './ChunkUploader';

export default {
    components: { ImageEditor, DeleteConfirmDialog, ChunkUploader },
    props: {
        hasEditor: {
            type: Boolean,
            default: false
        },
        hasSelector: {
            type: Boolean,
            default: false
        },
        domain: {
            type: String,
            default: ''
        },
        rootDirectory: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            filesSource: [],
            directoriesSource: [],
            directory: this._.clone(this.rootDirectory),
            breadCrumb: [],
            targetSource: [],
            isShowConfirmDelete: false,
            isShowImageEditor: false,
            targetImage: null,
            isShowMakeDirectory: false,
            currentSelected: 0,
            directoryName: '',
            isShowChunkUpload: false,
        };
    },
    mounted() {
        this.handleDiscover();
    },
    methods: {
        isImage(mime) {
           return mime.includes('image');
        },
        isVideo(mime) {
            return mime.includes('video');
        },
        getFileThumbnail(file) {
            if(this.isImage(file.mime)) {
                return file.url;
            }
            if(this.isVideo(file.mime)) {
                return '/images/video.png';
            }

            return '/images/file.png';

        },
        handleChooseImage(payload) {
            if(this.hasEditor) {
                this.handleSendToEditor(payload);
            } else if(this.hasSelector) {
                this.handleSendToSelector(payload);
            } else {
                window.open(payload.url);
            }
        },
        buildBreadCrumb() {
            this.breadCrumb = [
                {
                    name: this.$t('common.root_folder'),
                    path: this.rootDirectory,
                },
            ];

            const absolutePath = this.directory.replace(this.rootDirectory, '');

            if(absolutePath === "") {
                return;
            }

            const children = absolutePath.split('/');
            console.log(children);

            if(children.length < 2) {
                return;
            }

            children.splice(0, 1);

            for(let i = 0; i < children.length; i++) {
                let tempArr = [this.rootDirectory];
                for(let y = 0; y <= i; y++) {
                    tempArr.push(children[y]);
                }
                this.breadCrumb.push({
                    name: children[i],
                    path: tempArr.join('/'),
                })
            }
        },
        handleSendToSelector(file) {
            window.opener.fileManagerClient.onSelected([file.url]);
            window.close();
        },
        handleSendListToSelector() {
            let files = [];
            this._.forEach(this.filesSource, file => {
                if(file.is_selected) {
                    files.push(file.url);
                }
            });
            window.opener.fileManagerClient.onSelected(files);
            window.close();
        },
        isSelected(file) {
            return !this._.isEmpty(file.is_selected) ? 'success' : ''
        },
        handleTick(targetFile) {
            let before = false;

            if(targetFile.is_selected === true) {
                before = true;
            }

            targetFile.is_selected = !targetFile.is_selected;

            if(before) {
                this.currentSelected--;
            } else {
                this.currentSelected++;
            }
        },
        onEditSuccess(file) {
            this.filesSource.push(file);
            this.handleSort(1);
            this.isShowImageEditor = false;
        },
        onEditCancel() {
            this.isShowImageEditor = false;
        },
        handleSort(command) {
            switch (parseInt(command)) {
                case 1:
                    this.sortFiles('last_modified', 'desc');
                    break;
                case 2:
                    this.sortFiles('last_modified', 'asc');
                    break;
                case 3:
                    this.sortFiles('size', 'desc');
                    break;
                case 4:
                    this.sortFiles('size', 'asc');
                    break;
                default:
                    this.sortFiles('last_modified', 'asc');
                    break;
            }
        },
        sortFiles(field, type) {
            this.filesSource = this._.orderBy(this.filesSource, [field], [type]);
        },
        handleSendToEditor(file) {
            try {
                this.Editor.returnFileUrl(file.url);
            } catch (e) {
                this.$notify({
                    type: 'error',
                    title: this.$t('common.error'),
                    message: this.$t('message.cannot_send_file_to_editor'),
                });
            }
        },
        showImageEditor(file){
            this.targetImage = file;
            this.isShowImageEditor = true;
        },
        beforeExec(action) {
            if(this.currentSelected > 0) {
                switch(action) {
                    case 'delete':
                        this.isShowConfirmDelete = true;
                        break;
                    default:
                        break;
                }
            } else {
                this.$notify({
                    type: 'warning',
                    title: this.$t('common.warning'),
                    message: this.$t('message.not_selected_any_files'),
                });
            }
        },
        closeDeleteDialog() {
            this.isShowConfirmDelete = false;
        },
        onChunkUploadSuccess(data) {
            this.filesSource.push(data);
            this.handleSort(1);
            this.isShowChunkUpload = false;
        },
        async handleUpload(data) {
            const loading = this.$loading({
                lock: true,
                text: 'Loading',
                spinner: 'el-icon-loading',
                background: 'rgba(0, 0, 0, 0.7)'
            });
            try {
                let formData = new FormData();
                formData.append('file', data.file);
                formData.append('dir', this.directory);
                formData.append('mime', data.file.type);
                const uri = this.domain + '/single-upload';
                const response = await this.Request.upload(uri, formData);
                this.filesSource.push(response.data);
                this.handleSort(1);
                this.$notify({
                    type: 'success',
                    title: this.$t('common.success'),
                    message: this.$t('message.upload_success'),
                });
            } catch (e) {
                const errorMessages = this.Request.errors(e.response);
                this.$notify({
                    type: 'error',
                    title: this.$t('common.error'),
                    message: this._.isEmpty(errorMessages) ? this.$t('common.unknown_error') : errorMessages[0],
                });
            }
            loading.close();
        },
        async handleDiscover(path) {
            const loading = this.$loading({
                lock: true,
                text: 'Loading',
                spinner: 'el-icon-loading',
                background: 'rgba(0, 0, 0, 0.7)'
            });
            try {
                const directory = path || this.rootDirectory;
                const uri = this.domain + '/discover?dir=' + directory;
                const response = await this.Request.get(uri);
                this.filesSource = response.data.files;
                this.directoriesSource = response.data.directories;
                this.directory = directory;
                this.buildBreadCrumb();
                this.sortFiles('last_modified', 'desc');
            } catch (e) {
                const errorMessages = this.Request.errors(e.response);
                this.$notify({
                    type: 'error',
                    title: this.$t('common.error'),
                    message: this._.isEmpty(errorMessages) ? this.$t('common.unknown_error') : errorMessages[0],
                });
            }
            loading.close();
        },
        async handleDelete() {
            const loading = this.$loading({
                lock: true,
                text: 'Loading',
                spinner: 'el-icon-loading',
                background: 'rgba(0, 0, 0, 0.7)'
            });
            try {

                let directories = [];
                console.log(this.directoriesSource.count)
                for(let i = 0; i < this.directoriesSource.length; i++) {
                    if(this.directoriesSource[i].is_selected) {
                        directories.push(this.directoriesSource[i].path);
                    }
                }

                let files = [];
                for(let i = 0; i < this.filesSource.length; i++) {
                    if(this.filesSource[i].is_selected) {
                        files.push(this.filesSource[i].path);
                    }
                }


                const uri = this.domain + '/delete';
                await this.Request.post(uri, {
                    files,
                    directories
                });

                this._.remove(this.filesSource, (item) => {
                    return item.is_selected === true;
                });

                this._.remove(this.directoriesSource, (item) => {
                    return item.is_selected === true;
                });

                this.isShowConfirmDelete = false;
                this.$notify({
                    type: 'success',
                    title: this.$t('common.success'),
                    message: this.$t('message.delete_success'),
                });
            } catch (e) {
                const errorMessages = this.Request.errors(e.response);
                this.$notify({
                    type: 'error',
                    title: this.$t('common.error'),
                    message: this._.isEmpty(errorMessages) ? this.$t('common.unknown_error') : errorMessages[0],
                });
            }
            loading.close();
        },
        async handleMakeDirectory() {
            const loading = this.$loading({
                lock: true,
                text: 'Loading',
                spinner: 'el-icon-loading',
                background: 'rgba(0, 0, 0, 0.7)'
            });
            try {

                const uri = this.domain + '/mkdir';
                const response = await this.Request.post(uri, {
                    dir: this.directory,
                    name: this.directoryName,
                });
                this.directoriesSource.push(response.data);
                this.isShowMakeDirectory = false;
                this.directoryName = '';
                this.$notify({
                    type: 'success',
                    title: this.$t('common.success'),
                    message: this.$t('message.mkdir_success'),
                });
            } catch (e) {
                const errorMessages = this.Request.errors(e.response);
                this.$notify({
                    type: 'error',
                    title: this.$t('common.error'),
                    message: this._.isEmpty(errorMessages) ? this.$t('common.unknown_error') : errorMessages[0],
                });
            }
            loading.close();
        },
        getDate(timestamp) {
            const date = new Date(timestamp * 1000);
            const hours = date.getHours();
            const minutes = "0" + date.getMinutes();
            const seconds = "0" + date.getSeconds();
            const formattedTime = hours + ':' + minutes.substr(-2) + ':' + seconds.substr(-2);

            return date.getFullYear() + '/' + date.getMonth() + '/' + date.getDate() + ' ' + formattedTime;
        }
    }
}
</script>
