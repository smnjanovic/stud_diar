const SUB_ID = "id";
const SUB_ABB = "abb";
const SUB_NAME = "name";

function parseArray(item) {
  if (typeof item === "string") {
    try {
      let res = JSON.parse(item);
      return res instanceof Object ? Object.assign([], res).map(x => x) : [];
    }
    catch (err) {
      return [];
    }
  }
  return item instanceof Object ? Object.assign([], item).map(x => x) : [];
}

function parseObject(item) {
  if (typeof item === "string") {
    try {
      let res = JSON.parse(item);
      return res instanceof Array ? Object.assign({}, res) : res;
    }
    catch (err) {
      return {}
    }
  }
  return item instanceof Object ? Object.assign({}, item) : {};
}

function validateSubjects(subjects) {
  return parseArray(subjects).filter((x, i, a) => x.id > 0 && x.id % 1 === 0
    && typeof x.abb === "string" && x.abb.length <= 5 && x.abb.length > 0
    && typeof x.name === "string" && x.name.length <= 48 && x.name.length > 0
    && a.findIndex(sub => sub.id === x.id) === i).sort((a,b) => a.abb <= b.abb ? -1 : 1);
}

function validateNotes(notes, subjects) {
  subjects = validateSubjects(subjects);
  let num = n => n instanceof Date ? n.getTime() : typeof n === "number" ? n : Infinity;

  return parseArray(notes).map(note => {
    note.subject = subjects.find(sub => sub.id === note.subject);
    return note;
  })
  .filter((x, i, a) => x.id % 1 === 0 && x.id > 0 && a.findIndex(note => note.id === x.id) === i
    && x.subject && (x.date === null || typeof x.date === "number")
    && typeof x.info === "string" && x.info.length > 0 && x.info.length < 256)
  .sort((n1,n2) => (num(n1) - num(n2)) || n1.subject.abb < n2.subject.abb ? -1 :
    n1.subject.abb > n2.subject.abb ? 1 : n1.info < n2.info ? -1 :
    n1.info > n2.info ? 1 : n1.id - n2.id);
}

function stringifyNotes(notes) {
  return notes instanceof Object ? JSON.stringify(notes.map(note => {
    note.subject = note.subject.id;
    note.date = typeof note.date === "number" ? note.date
    : note.date instanceof Date ? note.date.getTime() : null;
    return note;
  })) : null;
}

function stringifyLessons(list) {
  return JSON.stringify(parseArray(list).map(x => {
    let les = Object.assign({}, x);
    les.subject = x.subject.id;
    return les;
  }));
}

function isValidLesson(lesson) {
  return lesson instanceof Object
  && lesson.day > 0 && lesson.day <= 5 && lesson.day % 1 === 0
  && lesson.start > 0 && lesson.start < 18 && lesson.start % 1 === 0
  && lesson.dur > 0 && lesson.dur < 18 && lesson.start + lesson.dur <= 18
  && lesson.type % 2 === lesson.type && lesson.type % 1 === 0
  && typeof lesson.subject === "number" && lesson.subject > 0 && lesson.subject % 1 === 0
  && typeof lesson.room === "string" && lesson.room.length <= 12
}

function getScheduleMatrice(lessons) {
  let les = parseArray(lessons).filter(isValidLesson);
  let matrice = [
    [null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null],
    [null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null],
    [null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null],
    [null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null],
    [null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null],
  ];
  les.forEach((item, j) => {
    for (let i = item.start; i < item.start + item.dur; i++)
      matrice[item.day - 1][i - 1] = item;
  });
  return matrice;
}

function lessonMatriceToList(matrice) {
  let storage = [];
  previous = null;
  for (let day = 0; day < 5; day++) {
    for (let les = 0; les < 18; les++) {
      if (previous && matrice[day][les] && previous.type === matrice[day][les].type
        && previous.subject === matrice[day][les].subject
        && previous.room === matrice[day][les].room
      ) {
        previous.dur++;
      }
      else if (matrice[day][les]) {
        previous = Object.assign({}, matrice[day][les]);
        previous.day = day + 1;
        previous.start = les + 1;
        previous.dur = 1;
        storage.push(previous);
      }
      else previous = null;
    }
  }
  return storage;
}

function scheduleCheck(day, start, dur) {
  if (day < 1 && day > 5) return `Neplatný deň (${day})!`;
  if (start < 1 && start > 17) return `Začiatok hodiny mimo rozsahu [1..17] (${start})!`;
  if (dur < 1 && dur > 17) return `Trvanie hodiny mimo rozsahu [1..17] (${dur})!`;
  if (start + dur > 18) return `Koniec hodiny prekročil rozsah [2..18] (${start} + ${dur})!`;
  return null;
}

this.onmessage = function (e) {
  // načítať pole všetkých predmetov, v určitom poradí
  if (e.data.loadSubjects !== undefined) {
    let d = e.data.loadSubjects;
    this.postMessage(validateSubjects(d.storage).slice(d.start, d.start + d.count));
  }

  // vložiť predmet
  else if (e.data.addSubject !== undefined) {
    let storage = validateSubjects(e.data.addSubject.storage);
    let abb = e.data.addSubject.abb.trim().toUpperCase();
    let name = e.data.addSubject.name.trim().toLowerCase();
    name = name.length ? name[0].toUpperCase() + name.substr(1) : "";

    let ret = {}

    let abbValid = abb.match(/^[a-zA-ZÀ-ž0-9-]{1,5}$/);
    let nameValid = name.match(/^[a-zA-ZÀ-ž0-9-_ ]{1,48}$/);

    if (!abbValid || !nameValid)
      ret.msg = `Zlý formát ${!abbValid && !nameValid ? "názvu a skratky" :
        !abbValid ? "skratky" : "názvu"} predmetu!`;
    else if (storage.find(sub => sub.abb.toUpperCase() == abb))
      ret.msg = "Predmet s touto skratkou už existuje!";
    else {
      let id = 0;
      storage.forEach(sub => { if (sub.id > id) id = sub.id });
      id++;
      ret.subject = {id: id, abb: abb, name: name};
      storage.splice(storage.filter(sub => sub.abb < abb).length, 0, ret.subject);
      ret.storage = JSON.stringify(storage);
    }
    this.postMessage(ret);
  }

  // upraviť predmet
  else if (e.data.editSubject !== undefined) {
    let result = {};
    let storage = validateSubjects(e.data.editSubject.storage);
    let id = e.data.editSubject.id;
    let index = storage.findIndex(item => item.id == id);

    if (index === -1) result.msg = "Upravovaný predmet neexistuje!";
    else {
      let oldAbb = storage[index].abb.trim().toUpperCase();
      let oldName = storage[index].name.trim().toLowerCase();
      let newAbb = e.data.editSubject.abb.trim().toUpperCase();
      let newName = e.data.editSubject.name.trim().toLowerCase();

      oldName = oldName.length ? oldName[0].toUpperCase() + oldName.substr(1) : "";
      newName = newName.length ? newName[0].toUpperCase() + newName.substr(1) : "";

      result.msg = oldAbb == newAbb && oldName == newName ? "K žiadnej zmene nedošlo!" :
        !newAbb.match(/^[a-zA-ZÀ-ž0-9-]{1,5}$/) ? "Neplatná skratka!" :
        !newName.match(/^[a-zA-ZÀ-ž0-9-_ ]{1,48}$/) ? "Neplatný názov!" :
        oldAbb != newAbb && storage.find(sub => sub.abb.toUpperCase() == newAbb) ?
        "Skratka je už obsadená!" : undefined;

      if (result.msg === undefined) {
        delete result.msg;
        storage[index].abb = newAbb;
        storage[index].name = newName;
        result.subject = storage[index];
        result.storage = JSON.stringify(storage);
      }
    }
    this.postMessage(result);
  }

  //vymazať predmet
  else if (e.data.removeSubject !== undefined) {
    let result = {};
    let subjects = validateSubjects(e.data.removeSubject.subjects);
    let index = subjects.findIndex(sub => sub.id == e.data.removeSubject.id);
    if (index === -1) result.msg = "Predmet už neexistuje!";
    else {
      result.subject = subjects.splice(index, 1)[0];
      result.storage = JSON.stringify(subjects);
    }
    this.postMessage(result);
  }

  //prehladavat predmety
  else if (e.data.searchSubjects !== undefined) {
    let id = e.data.searchSubjects.browserId;
    let q =e.data.searchSubjects.q.toUpperCase();
    let max = e.data.searchSubjects.maxResultCount;
    let match = sub => sub.toUpperCase().match(q);
    let results = q.length ? validateSubjects(e.data.searchSubjects.storage.subjects)
    .filter(x => match(x.abb) || match(x.name)).sort((a, b) => match(a.abb)
    ? (!match(b.abb) ? -1 : (a.abb < b.abb ? -1 : 1))
    : (match(b.abb) ? 1 : (a.name < a.name ? -1 : 1)))
    .slice(0, e.data.searchSubjects.maxResultCount) : [];
    this.postMessage({ resultSubjects: { browserId: id, subjects: results } });
  }

  // nacitanie zoznamu uloh
  else if (e.data.loadNotes !== undefined) {
    let notes = validateNotes(e.data.loadNotes.storage.notes, e.data.loadNotes.storage.subjects);
    let cat = typeof e.data.loadNotes.category !== "string" ? "" : e.data.loadNotes.category;
    if (cat === 'late-notes' || cat == 'recent') {
      let now = (new Date()).getTime();
      if (cat === 'late-notes') notes = notes.filter(x => x.date !== null && x.date < now);
      else {
        let week = now + 7 * 24 * 60 * 60 * 1000;
        notes = notes.filter(x => x !== null && x > now && x < week);
      }
    }
    else if (cat === 'long-term') {
      notes = notes.filter(x => x.date === null || x.date === "null");
    }
    else if (cat !== 'all-notes') {
      notes = notes.filter(x => x.subject.abb.toUpperCase() === cat.toUpperCase());
    }
    notes = notes.slice(e.data.loadNotes.start, e.data.loadNotes.start + e.data.loadNotes.count);
    this.postMessage({ resultNotes: { list: notes, offset: e.data.loadNotes.start } });
  }

  // pridanie ulohy
  else if (e.data.insertNote !== undefined) {
    let data = e.data.insertNote;
    let subjects = validateSubjects(data.storage.subjects);
    let notes = validateNotes(data.storage.notes, subjects);

    let ret = {};
    let id = 1;
    notes.forEach(note => id = Math.max(id, note.id + 1));
    let date = data.date === null ? null : new Date(data.date || "").getTime();
    if (date !== null && isNaN(date)) date = null;
    let sub = subjects.find(sub => sub.abb.toUpperCase() === data.sub.toUpperCase());
    let info = `${data.info}`;

    if (notes.length >= 10000000) ret.msg = "Zoznam úloh je plný!";
    else if (id % 1 !== 0) ret.msg = "Neplatné ID!";
    else if (date && date < Date.now() + 2000) ret.msg = "Čas uplynul!";
    else if (!sub) ret.msg = `Predmet ${data.sub} neexistuje!`;
    else if (info.length === 0) ret.msg = "Chýba popis úlohy!";
    else if (info.length > 255) ret.msg = "Popis úlohy je príliš dlhý!";
    else {
      ret.note = { id: id, date: date, subject: sub, info: info };
      notes.push(Object.assign({}, ret.note));
      ret.storage = { notes: stringifyNotes(notes), subjects: JSON.stringify(subjects) };
    }
    this.postMessage({ addedNote: ret });
  }

  // zmena úlohy
  else if (e.data.updateNote !== undefined) {
    let data = e.data.updateNote;
    let subjects = validateSubjects(data.storage.subjects);
    let notes = validateNotes(data.storage.notes, subjects);

    let ret = {};
    let note = notes.find(note => note.id === data.id);
    if (!note) ret.msg = "Úloha neexistuje!";
    else {
      let date = data.date === null ? null : new Date(data.date || "").getTime();
      if (date !== null && isNaN(date)) date = null;
      let sub = subjects.find(sub => sub.abb.toUpperCase() === data.sub.toUpperCase());
      let info = `${data.info}`;

      if (date && date < Date.now() + 2000) ret.msg = "Čas uplynul!";
      else if (!sub) ret.msg = `Predmet ${data.sub} neexistuje!`;
      else if (info.length === 0) ret.msg = "Chýba popis úlohy!";
      else if (info.length > 255) ret.msg = "Popis úlohy je príliš dlhý!";
      else {
        note.date = date;
        note.subject = sub;
        note.info = info;
        ret.note = Object.assign({}, note);
        ret.storage = { notes: stringifyNotes(notes), subjects: JSON.stringify(subjects) };
      }
    }
    this.postMessage({ editedNote: ret });
  }

  // zmazanie úlohy
  else if (e.data.deleteNote !== undefined) {
    let data = e.data.deleteNote;
    let notes = validateNotes(data.storage.notes, data.storage.subjects);
    let ret = {};
    let index = notes.findIndex(note => note.id === data.id);

    if (index === -1) ret.msg = "Predmet už neexistuje!";
    else {
      ret.note = notes.splice(index, 1)[0];
      ret.notes = stringifyNotes(notes);
    }
    this.postMessage({ removedNote: ret });
  }

  // ziskanie rozvrhu
  else if (e.data.getLessons) {
    let data = e.data.getLessons;
    let subs = validateSubjects(data.subjects);
    let matrice = getScheduleMatrice(data.lessons);
    let list = lessonMatriceToList(matrice).map(x => {
      x.subject = subs.find(s => s.id === x.subject);
      return x;
    }).filter(x => x.subject);
    this.postMessage({ lessons: list });
  }

  else if (e.data.clearSchedule) {
    let data = e.data.clearSchedule;
    data.day = parseInt(data.day);
    data.start = parseInt(data.start);
    data.dur = parseInt(data.dur);
    let err = scheduleCheck(data.day, data.start, data.dur);
    if (err) this.postMessage({ msg: err });
    else {
      let subs = validateSubjects(data.storage.subjects);
      let matrice = getScheduleMatrice(data.storage.lessons);
      for (let i = data.start; i < data.start + data.dur; i++) matrice[data.day - 1][i - 1] = null;
      let list = lessonMatriceToList(matrice).map(x => {
        x.subject = subs.find(s => s.id === x.subject);
        return x;
      }).filter(x => x.subject);
      this.postMessage({ storage: stringifyLessons(list), lessons: list });
    }
  }

  else if (e.data.addLesson) {
    let data = e.data.addLesson;
    data.day = parseInt(data.day);
    data.start = parseInt(data.start);
    data.dur = parseInt(data.dur);
    data.type = parseInt(data.type);
    data.subject = `${data.subject || ''}`;
    data.room = `${data.room || ''}`;
    let err = scheduleCheck(data.day, data.start, data.dur)
    || (data.type !== 0 && data.type !== 1 ? "Zadaný typ vyučovania neexistuje!" : null)
    || (data.room.length > 12 ? "Popis miestnosti je príliš dlhý!" : null);
    if (!err) {
      let subs = validateSubjects(data.storage.subjects);
      let sub = subs.find(s => s.abb.toUpperCase() == data.subject.toUpperCase());
      if (sub) {
        let matrice = getScheduleMatrice(data.storage.lessons);
        let item = { type: data.type, subject: sub.id, room: data.room };
        for (let i = data.start; i < data.start + data.dur; i++) matrice[data.day - 1][i - 1] = item;
        let list = lessonMatriceToList(matrice).map(x => {
          x.subject = subs.find(s => s.id === x.subject);
          return x;
        }).filter(x => x.subject);
        this.postMessage({ storage: stringifyLessons(list), lessons: list });
      }
      else this.postMessage({ msg: "Predmet neexistuje!" });
    }
    else this.postMessage({ msg: err });
  }
}
