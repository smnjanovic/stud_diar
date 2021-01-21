class DesignManagerTemplate {
  constructor(canvas) {
    if (!(canvas instanceof HTMLCanvasElement))
      throw Error("Prvý parameter musí byť objekt typu 'HTMLCanvasElement'!");
    this.canvas = canvas;
    this.hiddenImage = new Image();
    this.hiddenImage.src = "";
    this.hiddenImage.alt = "";
    this.hiddenImage.addEventListener("error", error => {
      if (error.target.src === "") {
        this.redraw();
        this.storeImageUrl();
      }
    });
    this.hiddenImage.addEventListener("load", load => {
      this.redraw();
      this.storeImageUrl();
    });

    this.scheduleStart = 18;
    this.scheduleEnd = 0;
    this.schedule = [];

    this.colors = {
      background: [255, 255, 255, 100],
      heading: [210, 65, 45, 100],
      courses: [210, 75, 65, 100],
      lectures: [210, 75, 55, 100],
      free: [0, 0, 0, 35],
    }
    this.aspectRatio = [720, 1280];
    this.imageFit = "contain";
    this.imagePos = 50;
    this.tablePos = 10;
    this.redraw();
  }

  getHSLColorWithContrast(hsla) {
    if (Array.isArray(hsla) && hsla.length == 4 && hsla.every((c, i) => c % 1 === 0 && c >= 0 && c <= (i === 0 ? 359 : 100))) {
      let color = `hsla(${hsla[0]}, ${hsla[1]}%, ${hsla[2]}%, ${hsla[3]/100})`;
      //let contrastLuminance = if (l < if (s > 35 && h in 45..200) 35 else 50) 85 else 15
      let contrastLuminance = hsla[2] < (hsla[1] > 35 && hsla[0] >= 45 && hsla[0] <= 200 ? 35 : 50) ? 85 : 15;
      let contrast = `hsl(${hsla[0]}, ${hsla[1]}%, ${contrastLuminance}%)`;
      return { color: color, contrast: contrast };
    }
    return { color: "hsla(0, 0%, 85%)", contrast: "hsl(0, 0%, 15%)" }
  }

  drawSchedule() {
    let can = DOMManager.newEl(`canvas[width=1000][height=${90 * 5 + 40}]`);
    let ctx = can.getContext('2d');

    let colors = {
      background: this.getHSLColorWithContrast(this.colors.background),
      heading: this.getHSLColorWithContrast(this.colors.heading),
      lectures: this.getHSLColorWithContrast(this.colors.lectures),
      courses: this.getHSLColorWithContrast(this.colors.courses),
      free: this.getHSLColorWithContrast(this.colors.free)
    };

    if (this.scheduleStart > this.scheduleEnd) {
      ctx.lineWidth = 1;
      ctx.strokeStyle = colors.heading.contrast;
      ctx.fillStyle = colors.heading.color;
      ctx.fillRect(0, 0, 1000, 40);
      ctx.strokeRect(0, 0, 1000, 40);
      ctx.fillStyle = colors.free.color;
      ctx.strokeStyle = colors.background.contrast;
      for (let day = 1; day <= 5; day++) {
        ctx.fillRect(0, day * 90 - 50, 1000, 90);
        ctx.strokeRect(0, day * 90 - 50, 1000, 90);
      }
      return can;
    }

    let columns = (this.scheduleEnd - this.scheduleStart + 1);
    let colW = parseInt(can.width / columns);
    can.width = colW * columns;
    let headSpan = columns < 8 ? 1 : columns < 12 || columns % 3 == 1 ? 2 : 3;

    //hlavicka
    for (let i = 0; i < columns; i+=headSpan) {
      if (i + headSpan > columns) headSpan = columns - i;
      ctx.lineWidth = 1;
      ctx.strokeStyle = colors.heading.contrast;
      ctx.fillStyle = colors.heading.color;
      ctx.fillRect(colW * i, 0, colW * headSpan, 40);
      ctx.strokeRect(colW * i, 0, colW * headSpan, 40);
      ctx.fillStyle = colors.heading.contrast;
      ctx.font = "bold 24px monospace";
      ctx.textAlign = "left";
      ctx.fillText(i + this.scheduleStart, i * colW + 10, 30);
    }

    //telo
    for (let item of this.schedule) {
      let cx = colW * (item.start - this.scheduleStart);
      let cy = 90 * item.day - 50;
      let cw = colW * item.dur;
      let ch = 90;
      let color = (item.type === 0 ? colors.courses : item.type === 1 ? colors.lectures : colors.free).color;
      let contrast = (item.type === 0 ? colors.courses : item.type === 1 ? colors.lectures : colors.background).contrast;
      //bunka
      ctx.lineWidth = 1;
      ctx.strokeStyle = contrast;
      ctx.fillStyle = color;
      ctx.fillRect(cx, cy, cw, ch);
      ctx.strokeRect(cx, cy, cw, ch);

      //text
      if (item.type !== undefined) {
        let txtCan = document.createElement("canvas");
        txtCan.width = colW * item.dur;
        txtCan.height = 90;
        let tctx = txtCan.getContext('2d');
        tctx.fillStyle = contrast;
        tctx.font = `bold 40px monospace`;
        tctx.textAlign = "center";
        tctx.fillText(item.subject.abb, txtCan.width / 2, 40);
        tctx.font = `26px monospace`;
        tctx.fillText(item.room, txtCan.width / 2, 75);
        ctx.drawImage(txtCan, colW * (item.start - this.scheduleStart), 90 * item.day - 50, colW * item.dur, 90);
      }
    }
    return can;
  }

  drawImage() {
    let imgW = this.hiddenImage.naturalWidth;
    let imgH = this.hiddenImage.naturalHeight;
    if (imgW * imgH === 0) return null;

    let frameRatio = this.aspectRatio[0] / this.aspectRatio[1];
    let imageRatio = imgW / imgH;
    let imageWider = frameRatio < imageRatio && imageRatio - frameRatio > 0.1;
    let imageTaller = frameRatio > imageRatio && frameRatio - imageRatio > 0.1;

    let x = 0;
    let y = 0;
    let w = this.aspectRatio[0];
    let h = this.aspectRatio[1];

    // ←→ horizontálny posun obrázku
    if (this.imageFit === "cover" && imageWider || this.imageFit === "contain" && imageTaller) {
      w = imgW * this.aspectRatio[1] / imgH;
      let boundX1 = imageTaller ? 0 : w - this.aspectRatio[0];
      let boundX2 = imageWider ? 0 : this.aspectRatio[0] - w;
      x = parseInt((boundX2 - boundX1) / 100 * this.imagePos);
    }

    // ↑↓ vertikálny posun obrázku
    else if (this.imageFit === "cover" && imageTaller || this.imageFit === "contain" && imageWider) {
      h = imgH * this.aspectRatio[0] / imgW;
      let boundY1 = imageWider ? 0 : h - this.aspectRatio[1];
      let boundY2 = imageTaller ? 0 : this.aspectRatio[1] - h;
      y = parseInt((boundY2 - boundY1) / 100 * this.imagePos);
    }

    // pozicia obrazku spocitana uz len vykreslit
    let imgCanvas = DOMManager.newEl(`canvas[width=${this.aspectRatio[0]}][height=${this.aspectRatio[1]}]`);
    imgCanvas.getContext('2d').drawImage(this.hiddenImage, x, y, w, h);
    return imgCanvas;
  }

  redraw() {
    this.canvas.width = this.aspectRatio[0];
    this.canvas.height = this.aspectRatio[1];
    let ctx = this.canvas.getContext('2d');
    ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
    let col = this.colors.background;
    ctx.fillStyle = `hsl(${this.colors.background[0]},${this.colors.background[1]}%, ${this.colors.background[2]}%)`;
    ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
    let image = this.drawImage();
    if (image) ctx.drawImage(image, 0, 0, this.canvas.width, this.canvas.height);
    let schedule = this.drawSchedule();
    if (schedule) {
      let padding = 20;
      let frmRat = (this.canvas.width - padding * 2) / (this.canvas.height - padding * 2);
      let tblRat = schedule.width / schedule.height;
      let tableWider = frmRat < tblRat && tblRat - frmRat > 0.01;
      let tableTaller = frmRat > tblRat && frmRat - tblRat > 0.01;

      let x = padding;
      let y = padding;
      let w = this.canvas.width - 2 * x;
      let h = this.canvas.height - 2 * y;
      let bound = 20;

      if (tableWider) {
        h = schedule.height * w / schedule.width;
        bound = this.canvas.height - padding - h;
        y = ((bound - padding) / 100 * this.tablePos) + padding;
      }
      else if (tableTaller) {
        w = schedule.width * h / schedule.height;
        bound = this.canvas.width - padding - w;
        x = ((bound - padding) / 100 * this.tablePos) + padding;
      }
      ctx.drawImage(schedule, x, y, w, h);
    }
  }

  setScheduleData(lessons) {
    lessons = Array.isArray(lessons) && lessons.filter(x =>
      x.day > 0 && x.day <= 5 && x.day % 1 === 0
      && x.start > 0 && x.start < 18 && x.start % 1 === 0
      && x.dur > 0 && x.dur < 18 && x.start + x.dur <= 18
      && x.type % 2 === x.type && x.type % 1 === 0
      && x.subject instanceof Object && x.subject.abb && `${x.subject.abb}`.length <= 5
      && `${x.room || ''}`.length <= 12
    ).sort((a,b) => (a.day - b.day) || (a.start - b.start) || (a.dur - b.dur)) || [];

    this.schedule = [];
    this.scheduleStart = 18;
    this.scheduleEnd = 0;

    if (lessons.length > 0) {
      let day = 1;
      lessons.forEach(les => {
        this.scheduleStart = Math.min(this.scheduleStart, les.start);
        this.scheduleEnd = Math.max(this.scheduleEnd, les.start + les.dur - 1);
      });
      let col = this.scheduleStart;

      lessons.forEach(les => {
        if (day < les.day) {
          // vyplnenie konca rozvrhu
          if (col <= this.scheduleEnd) this.schedule.push({day: day, start: col, dur: this.scheduleEnd - col + 1});
          col = this.scheduleStart;
          day++;
          // vyplnenie volnych dni
          for (; day < les.day; day++) this.schedule.push({day: day, start: this.scheduleStart, dur: this.scheduleEnd - this.scheduleStart + 1});
          // vyplnenie zaciatku rozvrhu
          if (les.start > this.scheduleStart) this.schedule.push({day: les.day, start: this.scheduleStart, dur: les.start - this.scheduleStart});
        }
        // vyplnenie volna medzi hodinami
        else if (col < les.start) this.schedule.push({day: les.day, start: col, dur: les.start - col});
        //prekrytie hodiny
        else if (col > les.start && les.start + les.dur > col) {
          les.dur -= col - les.start;
          les.start = col;
        }
        //vlozit sucasny prvok, pokial sa stale jedna o nevykreslenu cast
        if (col < les.start + les.dur) {
          this.schedule.push(les);
          col = les.start + les.dur;
        }
      });

      // vyplnenie zvysku rozvrhu
      if (col <= this.scheduleEnd) this.schedule.push({day: day, start: col, dur: this.scheduleEnd - col + 1});
      day++;
      for (; day <= 5; day++) this.schedule.push({day: day, start: this.scheduleStart, dur: this.scheduleEnd - this.scheduleStart + 1});
    }

    this.redraw();
  }

  setColor(key, partitions) {
    if (typeof key !== "string") throw Error(`Prvý parameter musí byť reťazec!`);
    if (!(["background", "heading", "courses", "lectures", "free"].find(x => x === key)))
      throw Error(`Prvý parameter obsahuje názov neexistujúcej farebnej skupiny!`);
    if (!Array.isArray(partitions) || partitions.length < 3
      || !partitions.every((x, i) => typeof x === "number"
      && x % 1 === 0 && x >= 0 && x <= (i == 0 ? 359 : 100)))
      throw Error(`Druhý parameter musí byť 3-prvkové alebo`
        + ` 4-prvkové pole číslic platných pre HSLA model`
        + ` (min: [0,0,0,0], max: [359, 100, 100, 100])!`);
    if (partitions[3] === undefined) partitions[3] = 100;
    this.colors[key] = partitions;
    this.redraw();
  }

  setAspectRatio(ratio) {
    let r = typeof ratio === "string" && ratio.match(/^([0-9]+)x([0-9]+)$/);
    if (r) {
      let w = parseInt(r[1]);
      let h = parseInt(r[2]);
      if (!isNaN(w - h) && Math.min(w, h) >= 320 && Math.max(w, h) <= 6016) {
        this.aspectRatio[0] = w;
        this.aspectRatio[1] = h;
        this.redraw();
      }
    }
    this.storeAspectRatio();
  }

  setImageUrl(url) {
    if (!url) {
      this.hiddenImage.removeAttribute("src");
      this.redraw();
      this.storeImageUrl();
    }
    else this.hiddenImage.src = `${url}`;
  }

  setImageFit(fit) {
    this.imageFit = ["cover", "contain", "fill"].find(x => x === fit) || "fill";
    this.redraw();
    this.storeImageFit();
  }

  setImagePos(pos) {
    if (pos >= 0 && pos <= 100) {
      this.imagePos = pos - 0;
      this.redraw();
    }
  }

  setTablePos(pos) {
    if (pos >= 0 && pos <= 100) {
      this.tablePos = pos - 0;
      this.redraw();
    }
  }

  getData() {
    return {
      colors: {
        background: this.colors.background.map(x => x),
        heading: this.colors.heading.map(x => x),
        courses: this.colors.courses.map(x => x),
        lectures: this.colors.lectures.map(x => x),
        free: this.colors.free.map(x => x)
      },
      aspectRatio: this.aspectRatio.map(x => x),
      imageFit: this.imageFit,
      imagePos: this.imagePos,
      tablePos: this.tablePos
    }
  }

  getImageUrl() {
    return this.hiddenImage.naturalWidth * this.hiddenImage.naturalHeight > 0 && this.hiddenImage.src || "";
  }

  getColor(key) {
    return this.colors[key] && this.colors[key].map(x => x);
  }

  getAspectRatio() {
    return this.aspectRatio.map(x => x);
  }

  // nasledujuce triedy maju za ulohu ulozit jednotlive zmeny do trvalej pamate
  storeColor(key) {}
  storeAspectRatio() {}
  storeImageUrl() {}
  storeImageFit() {}
  storeImagePos() {}
  storeTablePos() {}
}
