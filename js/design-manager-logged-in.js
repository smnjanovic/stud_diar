class DesignManager extends DesignManagerTemplate {
  constructor(canvas, onload) {
    super(canvas);
    let fd = new FormData();
    fd.append('get-conf', '');
    fetch("ajax-forms.php", { method: 'post', body: fd })
    .then(resp => resp.json())
    .then(conf => {
      this.hiddenImage.src = conf.imageUrl;
      this.colors = conf.colors;
      this.aspectRatio = conf.aspectRatio;
      this.imageFit = conf.imageFit;
      this.imagePos = conf.imagePos;
      this.tablePos = conf.tablePos;
      if (onload instanceof Function) onload(this.getData());
    })
    .catch(err => console.log("err", err));
  }

  setConf(key, value) {
    let fd = new FormData();
    fd.append('set-conf', key);
    fd.append('value', value);
    fetch("ajax-forms.php", { method: "post", body: fd })
    .then(resp => resp.text())
    .then(data => console.log(data))
    .catch(err => console.error(err));
  }

  storeColor(key) {
    this.setConf('store-color', JSON.stringify({ key: key, value: this.colors[key] }));
  }
  storeAspectRatio() {
    this.setConf('store-aspect-ratio', JSON.stringify(this.aspectRatio));
  }
  storeImageUrl() {
    this.setConf('store-image-url', this.getImageUrl());
  }
  storeImageFit() {
    this.setConf('store-image-fit', this.imageFit);
  }
  storeImagePos() {
    this.setConf('store-image-pos', this.imagePos);
  }
  storeTablePos() {
    this.setConf('store-table-pos', this.tablePos);
  }
}
