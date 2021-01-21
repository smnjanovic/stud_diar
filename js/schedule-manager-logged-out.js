class ScheduleManager extends ScheduleManagerTemplate {
  constructor(search, filter, onDataSetChanged) {
    super(onDataSetChanged);
    this.worker = new Worker("js/worker.js");
    this.worker.onmessage = msg => this.onScheduleReceived(msg.data);
    new SubjectBrowser(search, filter, 5, ()=>{}, this.worker);
  }

  getStorage() {
    return { lessons: STORAGE.schedule, subjects: STORAGE.subjects };
  }

  addLesson(day, start, dur, type, subject, room) {
    this.worker.postMessage({
      addLesson: {
        storage: this.getStorage(),
        day: day, start: start, dur: dur, type: type, subject: subject, room: room
      }
    });
  }

  clearSchedule(day, start, dur) {
    this.worker.postMessage({
      clearSchedule: {
        storage: this.getStorage(),
        day: day, start: start, dur: dur
      }
    });
  }

  getLessons() {
    this.worker.postMessage({ getLessons: this.getStorage() });
  }

  onScheduleReceived(data) {
    if (data.storage !== undefined) STORAGE.schedule = data.storage;
    super.onScheduleReceived(data);
  }
}
