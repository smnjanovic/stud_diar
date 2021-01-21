class SubjectBrowser {
  constructor(search, filter, maxResultCount, searchEnd, worker = null) {
    let err = (num, finish) => { throw Error(`Parameter ${num} musí byť ${finish}`) };
    if (!DOMManager.isEl(search) || search.tagName !== "INPUT" || search.type !== "search") err(1, "element INPUT typu search!");
    if (!DOMManager.isEl(filter) || filter.tagName !== "UL") err(2, "element UL!");
    if (typeof maxResultCount !== "number" || maxResultCount < 1) err(3, "kladné celé číslo!");
    if (!(searchEnd instanceof Function)) err(4, "funkcia na vykonanie po načítaní výsledkov!");
    if (worker !== null && !(window.Worker && worker instanceof Worker)) err(5, "Worker alebo null!");

    this.browserId = Date.now();
    this.search = search;
    this.filter = filter;
    this.maxResultCount = maxResultCount;
    this.abort = null;
    this.searchEnd = searchEnd;
    this.worker = worker;

    if (this.worker !== null) {
      let defaultWorkerFn = this.worker.onmessage;
      this.worker.onmessage = msg => {
        if (msg.data.resultSubjects !== undefined && msg.data.resultSubjects.browserId === this.browserId)
          this.onResultsReceived(msg.data.resultSubjects.subjects);
        else if (defaultWorkerFn instanceof Function) defaultWorkerFn(msg);
      }
    }


    this.search.addEventListener("input", () => this.onSearchStart());
    this.search.addEventListener("search", () => {
      if (this.filter.childElementCount > 0)
        (DOMManager.getEl("li.active", this.filter) || this.filter.firstElementChild).click();
      else this.searchEnd(null, 0);
    });
    this.search.addEventListener("keydown", e => {
      if (this.filter.childElementCount > 0 && (e.which === 38 || e.which === 40)) {
        e.preventDefault();
        let active = DOMManager.getEl("li.active", this.filter);
        if (active) active.classList.remove("active");
        if (e.which === 38) active = active && active.previousElementSibling || this.filter.lastElementChild;
        else active = active && active.nextElementSibling || this.filter.firstElementChild;
        if (active) active.classList.add("active");
      }
    });
  }

  isSubject(subject) {
    return subject instanceof Object && typeof subject.id === "number"
    && subject.id % 1 === 0 && subject.id > 0 && typeof subject.abb === "string"
    && subject.abb && subject.abb.length <= 5 && !subject.abb.match(/[^a-zA-ZÀ-ž0-9-_]/)
    && typeof subject.name === "string" && subject.name && subject.name.length <= 48
    && !subject.name.match(/[^a-zA-ZÀ-ž0-9-_ ]/)
  }

  onSearchStart() {
    if (this.worker !== null) {
      let post = {};
      post.browserId = this.browserId;
      post.q = this.search.value;
      post.maxResultCount = this.maxResultCount;
      post.storage = { subjects: STORAGE.subjects, notes: STORAGE.notes };
      this.worker.postMessage({ searchSubjects: post });
    }
    else {
      let detail = { method: "post", body: new FormData() };
      detail.body.append("search-subjects", "");
      detail.body.append("q", this.search.value);
      detail.body.append("max-result-count", this.maxResultCount);
      if (window.AbortController) {
        if (this.abort instanceof AbortController) this.abort.abort();
        this.abort = new AbortController();
        detail.signal = this.abort.signal;
      }
      fetch("ajax-forms.php", detail)
      .then(response => response.json())
      .then(data => {
        this.onResultsReceived(data);
        this.abort = null;
      })
    }
  }

  onResultsReceived(list) {
    if (!Array.isArray(list)) return;
    let data = Array.isArray(list) && list.filter(x => this.isSubject(x)) || [];
    this.filter.innerHTML = "";
    for (let subject of data) {
      let q = this.search.value.toUpperCase();
      let nameMatch = subject.name.toUpperCase().indexOf(q);
      let output = "";

      const MAX_LENGTH = 24;
      output = `${subject.abb.toUpperCase().replace(q, q.bold())}: `
      if (subject.abb.length + 2 + subject.name.length <= MAX_LENGTH) {
        if (nameMatch > -1) {
          output += subject.name.substr(0, nameMatch);
          output += subject.name.substr(nameMatch, q.length).bold();
          output += subject.name.substr(nameMatch + q.length);
        }
        else {
          output += subject.name;
        }
      }
      else if (nameMatch === -1) {
        output += subject.name.substr(0, MAX_LENGTH - subject.abb.length - 3);
        output += "&hellip;";
      }
      // nekompletny nazov. Viditelna cast nazvu zhodna so zadanym vyrazom
      else {
        q = q.substr(0, MAX_LENGTH - subject.abb.length - 4);
        let start = Math.max(0, nameMatch + q.length - (MAX_LENGTH - subject.abb.length - 2));
        let end = start + MAX_LENGTH - subject.abb.length - 2;
        if (start > 0) output += "&hellip;";
        output += subject.name.substring(start, nameMatch);
        output += subject.name.substr(nameMatch, q.length).bold();
        output += subject.name.substring(nameMatch + q.length, end);
        if (end < subject.name.length) output += "&hellip;";
      }
      let li = DOMManager.newEl(`li{${output}}`, this.filter);
      li.addEventListener("click", () => this.onSubjectSelected(subject));
    }
    if (data.length === 1) {
      if (this.search.value.toUpperCase() === data[0].abb.toUpperCase()) {
        this.filter.innerHTML = "";
        this.onSubjectSelected(data[0]);
      }
    }
    else this.searchEnd(null, data.length);
  }

  onSubjectSelected(subject) {
    if (this.isSubject(subject)) {
      this.search.value = subject.abb;
      this.search.blur();
      this.filter.innerHTML = "";
      this.searchEnd(subject, 1);
    }
  }
}
