export const Editor = {
    getUrlParam(n) {
        const r = new RegExp("(?:[?&]|&)" + n + "=([^&]+)", "i"),
            o = window.location.search.match(r);
        return o && o.length > 1 ? o[1] : null
    },
    returnFileUrl(n) {
        const r = this.getUrlParam("CKEditorFuncNum");
        window.opener.CKEDITOR.tools.callFunction(r, n);
        window.close();
    },
};


