class NoteManager extends NoteManagerTemplate {
  loadNotes(data, page = 0) {
    this.category = typeof data !== "string" ? "" : data;
    this.jsonAction({
      'load-notes': '',
      start: page * this.paginator.itemsPerPage,
      count: this.paginator.maxItemCount,
      category: this.category
    }, data => this.onLoad(data.list, data.offset));
  }

  insertNote(date, sub, info) {
    this.updateNote(null, date, sub, info);
  }

  updateNote(id, date, sub, info) {
    let input = this.getCols(id, date, sub, info);
    input[(id !== null ? 'edit' : 'add') + '-note'] = '';
    this.jsonAction(input, data => this.onNoteChange(data));
  }

  deleteNote(id) {
    let input = this.getCols(id, null, null, null);
    input['remove-note'] = '';
    this.jsonAction(input, data => this.onDelete(data));
  }

  getCols(id, date, subject, info) {
    let result = {};
    if (typeof id === "number") result['id'] = id;
    if (typeof date === "string") result['date'] = date;
    if (typeof subject === "string") result['subject'] = subject;
    if (typeof info === "string") result['info'] = info;
    return result;
  }

  jsonAction(input, output) {
    let fd = new FormData();
    if (window.AbortController && this.abort instanceof AbortController) {
      this.abort.abort();
      this.abort = new AbortController();
    }
    else this.abort = null;
    Object.keys(input).forEach(key => fd.append(key, input[key]));
    let meta = { method: "post", body: fd };
    if (window.AbortController && this.abort instanceof AbortController) meta['signal'] = this.post.signal;
    fetch("ajax-forms.php", meta)
    .then(res => res.json())
    .then(data => {
      output(data);
      this.abort = null;
    })
    .catch(err => { if (err.name === "AbortController") console.error(err) });
  }
}
