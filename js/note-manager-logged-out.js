class NoteManager extends NoteManagerTemplate {
  constructor(itemsPerPage, maxItemCount, scrollable, parentNode, itemDraw) {
    super(itemsPerPage, maxItemCount, scrollable, parentNode, itemDraw)

    this.worker = new Worker("js/worker.js");
    // tu sa budu uchovavat data pouzitelne po spatnej odozve workera
    this.worker.onmessage = msg => {
      //prehladavanie uloh
      if (msg.data.resultNotes !== undefined)
        this.onLoad(msg.data.resultNotes.list, msg.data.resultNotes.offset);
      // nova alebo upravena uloha
      else if ((msg.data.addedNote || msg.data.editedNote) !== undefined) {
        let data = msg.data.addedNote || msg.data.editedNote;
        this.onNoteChange(data);
        if (data.msg === undefined) {
          STORAGE.subjects = data.storage.subjects;
          STORAGE.notes = data.storage.notes;
        }
      }
      // odstranena uloha
      else if (msg.data.removedNote) {
        this.onDelete(msg.data.removedNote);
        if (msg.data.removedNote.notes) STORAGE.notes = msg.data.removedNote.notes;
      }
    }
  }

  setSubjectFilter(search, filter, maxResults, searchEnd) {
    return new SubjectBrowser(search, filter, maxResults, searchEnd, this.worker);
  }

  loadNotes(data, page = 0) {
    this.category = typeof data !== "string" ? "" : data;
    this.worker.postMessage({
      loadNotes: {
        storage: { subjects: STORAGE.subjects, notes: STORAGE.notes },
        category: this.category,
        start: page * this.paginator.itemsPerPage,
        count: this.paginator.maxItemCount
      }
    });
  }

  insertNote(date, sub, info) {
    this.worker.postMessage({
      insertNote: {
        storage: { subjects: STORAGE.subjects, notes: STORAGE.notes },
        date: date, sub: sub, info: info
      }
    });
  }

  updateNote(id, date, sub, info) {
    this.worker.postMessage({
      updateNote: {
        storage: { subjects: STORAGE.subjects, notes: STORAGE.notes },
        id: id, date: date, sub: sub, info: info
      }
    });
  }

  deleteNote(id) {
    this.worker.postMessage({ deleteNote: { storage: { notes: STORAGE.notes,
      subjects: STORAGE.subjects }, id: id } });
  }
}
