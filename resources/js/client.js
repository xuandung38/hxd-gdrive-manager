const fileManagerClient = {
    files: [],
    onSelected(payload) {
        this.files = payload;
        // Do something
    }
}
window.fileManagerClient = fileManagerClient;
