<template>
    <div>
        <el-dialog
            :title="title"
            :visible.sync="isShow"
            class="dialog-small"
            center
            @close="onCancel"
        >
            <p v-if="message !== ''" class="text-center">{{ message }}</p>
            <p v-else class="text-center">{{ this.$t('message.confirm_delete_message')}}</p>
            <span slot="footer" class="dialog-footer">
            <el-button type="danger" class="box-shadow" @click="onConfirm">{{ this.$t('common.confirm')}}</el-button>
            <el-button class="box-shadow" @click="onCancel">{{ this.$t('common.cancel')}}</el-button>
        </span>
        </el-dialog>
    </div>
</template>
<script>
    export default {
        props: {
            title: {
                Type: String,
                default: '',
            },
            message: {
                Type: String,
                default: '',
            },
            visible: {
                Type: Boolean,
                default: false,
            },
            onCancel: {
                Type: Function,
                default() {
                    return null;
                },
            },
            onConfirm: {
                Type: Function,
                default() {
                    return null;
                },
            },
        },
        data() {
            return {
                isShow: this._.clone(this.visible),
            };
        },
        watch: {
            isShow(data) {
                if(!data) {
                    this.onCancel();
                }
            },
            visible(data) {
                this.isShow = data;
            }
        },
    }
</script>
