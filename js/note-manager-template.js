class NoteManagerTemplate {
  constructor(itemsPerPage, maxItemCount, scrollable, parentNode, itemDraw) {
    let determineIndex = (item, notes) => {
      let dnum = d => d instanceof Date ? d.getTime() : typeof d === "number" ? d : Infinity;
      let isDate = d => isNaN(dnum(d));
      let other = (n1, n2) => n1.subject.abb > n2.subject.abb
      || n1.subject.abb == n2.subject.abb && n1.info > n2.info
      || n1.subject.abb == n2.subject.abb && n1.info == n2.info && n1.id >= n2.id;
      let index = notes.findIndex(n => item.date === null
      ? isDate(n.date) || other(n, item) : dnum(n.date) > dnum(item.date)
      || dnum(n.date) == dnum(item.date) && other(n, item));
      return index > -1 ? index : notes.length;
    }
    let findItem = (item, notes) => notes.findIndex(x => x.id === item.id);
    this.paginator = new Paginator([], 0, itemsPerPage, maxItemCount,
      scrollable, parentNode, itemDraw, findItem, determineIndex);
    this.category = "";
  }

  setSubjectFilter(search, filter, maxResults, searchEnd) {
    return new SubjectBrowser(search, filter, maxResults, searchEnd);
  }

  loadNotes(data, page = 0) {}

  insertNote(date, sub, info) {}

  updateNote(id, date, sub, info) {}

  deleteNote(id) {}

  validateDate(date) {
    if (!date) return null;
    if (["number", "string"].find(x => typeof date === x)) {
      let d = new Date(date);
      return isNaN(d.getTime()) ? null : d;
    }
    return date instanceof Date && !isNaN(date.getTime()) ? date : null;
  }

  onLoad(noteList, offset) {
    this.paginator.setData(noteList.map(x => {
      x.date = this.validateDate(x.date);
      return x;
    }), offset);
  }

  onNoteChange(data) {
    if (data.msg !== undefined) new AlertDialog({ title: "Chyba", message: data.msg });
    else {
      data.note.date = this.validateDate(data.note.date);
      let belongs = true;
      if (this.category === 'late-notes' || this.category == 'recent') {
        let now = Date.now();
        belongs = data.note.date !== null && (this.category === 'late-notes'
        ? data.note.date < now : data.note.date > now
        && data.note.date < now + 7 * 24 * 60 * 60 * 1000);
      }
      else {
        belongs = this.category === 'long-term' ? data.note.date === null
        : this.category !== 'all-notes' ? data.note.subject.abb.toUpperCase()
        === this.category.toUpperCase() : true;
      }
      this.paginator[belongs ? "setItem" : "removeItem"](data.note);
      if (!belongs) new AlertDialog({ title: "Upozornenie!", message: "Táto úloha bola vylúčená z tejto kategórie!" });
    }
  }

  onDelete(data) {
    if (data.msg !== undefined) new AlertDialog({ title: "Chyba", message: data.msg });
    else this.paginator.removeItem(data.note);
  }
}
