class DesignManager extends DesignManagerTemplate {
  constructor(canvas, onload) {
    super(canvas);
    this.hiddenImage.src = STORAGE.bgImage || "";
    Object.keys(this.colors).forEach(key => this.colors[key] = STORAGE[key]);
    this.aspectRatio = STORAGE.aspectRatio.match(/[0-9]+/g).map(x => parseInt(x));
    this.imageFit = STORAGE.imageFit;
    this.imagePos = STORAGE.imagePos;
    this.tablePos = STORAGE.tablePos;
    if (onload instanceof Function) onload(this.getData());
    this.redraw();
  }
  storeColor(key) {
    STORAGE[key] = this.colors[key];
  }
  storeAspectRatio() {
    STORAGE.aspectRatio = `${this.aspectRatio[0]}x${this.aspectRatio[1]}`;
  }
  storeImageUrl() {
    STORAGE.bgImage = this.getImageUrl();
  }
  storeImageFit() {
    STORAGE.imageFit = this.imageFit;
    console.log(STORAGE.imageFit, this.imageFit);
  }
  storeImagePos() {
    STORAGE.imagePos = this.imagePos;
  }
  storeTablePos() {
    STORAGE.tablePos = this.tablePos;
  }
}
