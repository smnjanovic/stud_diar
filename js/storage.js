const STORAGE = {};

(() => {
  let coerceIn = (value, min, max)=> Math.max(min, Math.min(value, max));

  let prop = (key, maxLen) => {
    Object.defineProperty(STORAGE, key, {
      get: () => {
        let storage = localStorage.getItem(key);
        if (!storage || storage[0] !== "[" || storage[storage.length - 1] !== "]") return null;
        return storage.length <= maxLen ? storage : storage.substr(0, maxLen).replace(/\,[ ]?{[^\}]*$/, "]");
      },
      set: value => localStorage.setItem(key, typeof value === "object" ? null : value)
    });
  }

  let propgs = (key, getter, setter) => Object.defineProperty(STORAGE, key, {
    get: () => getter(key),
    set: (value) => setter(key, value)
  });

  let propcol = (key, defaultH, defaultS, defaultL, defaultA = 100) => {
    let toNum = (num, def) => isNaN(num-0) ? def : num;
    let hsla = arr => [
      parseInt((toNum(arr[0], defaultH) < 0 ? 360 : 0) + toNum(arr[0], defaultH) % 360),
      parseInt(coerceIn(toNum(arr[1], defaultS), 0, 100)),
      parseInt(coerceIn(toNum(arr[2], defaultL), 0, 100)),
      parseInt(coerceIn(toNum(arr[3], defaultA), 0, 100))
    ];
    Object.defineProperty(STORAGE, key, {
      get: () => hsla(((localStorage.getItem(key)||"").substring(0, 47).match(/[0-9]+/g)||[]).slice(0,4)),
      set: (value) => localStorage.setItem(key, JSON.stringify(hsla(!Array.isArray(value)
        ? [] : value.map(x => parseInt(x)).filter(x => !isNaN(x)).slice(0, 4))))
    });
  }

  Object.defineProperty(STORAGE, "bgImage", {
    get: () => localStorage.getItem("bgImage"),
    set: value => localStorage.setItem("bgImage", `${value}`)
  });

  Object.defineProperty(STORAGE, "aspectRatio", {
    get: () => {
      let result = localStorage.getItem("aspectRatio");
      let m = result && result.match(/^([0-9]+)x([0-9]+)$/);
      let w = Math.min(Math.max(320, m ? parseInt(m[1]) : 720), 6016);
      let h = Math.min(Math.max(320, m ? parseInt(m[2]) : 1280), 6016);
      return `${w}x${h}`;
    },
    set: value => {
      let m = `${value}`.match(/^([0-9]+)x([0-9]+)$/);
      let w = Math.min(Math.max(320, m ? parseInt(m[1]) : 720), 6016);
      let h = Math.min(Math.max(320, m ? parseInt(m[2]) : 1280), 6016);
      localStorage.setItem("aspectRatio", `${w}x${h}`);
    }
  });

  prop("subjects", ('{"id":,"abb":"", name:""},'.length + 5 + 5 + 48) * 10000 + 1);
  prop("notes", Math.min(('{"id":,"date":,"sub":, "info":""},'.length + 6 + 13 + 5 + 255) * 100000 + 1, 5000000));
  prop("schedule", ('{"day":,"start":,"dur":,"type":,"subject":, "room":""},'.length + 1 + 2 + 2 + 1 + 5 + 12) * 90 + 1);
  propgs(
    "imageFit",
    key => ["cover", "contain", "fill"].find(x => x === localStorage.getItem(key)) || "cover",
    (key, value) => localStorage.setItem(key, ["cover", "contain", "fill"].find(x => x === value) || "cover")
  );
  propgs(
    "imagePos",
    key => {
      let stored = localStorage.getItem(key);
      if (stored === null) stored = 50;
      return Math.max(0, Math.min(stored, 100));
    },
    (key, value) => localStorage.setItem(key, Math.max(0, Math.min(
      typeof value === "number" && !isNaN(value) ? value : 50, 100)))
  );
  propgs(
    "tablePos",
    key => {
      let stored = localStorage.getItem(key);
      if (stored === null) stored = 10;
      return Math.max(0, Math.min(stored, 100));
    },
    (key, value) => {
      (key, value) => localStorage.setItem(key, Math.max(0, Math.min(
        typeof value === "number" && !isNaN(value) ? value : 10, 100)));
    }
  );
  propcol("background", 45, 50, 100);
  propcol("heading", 45, 100, 60);
  propcol("lectures", 45, 95, 70);
  propcol("courses", 45, 90, 80);
  propcol("free", 45, 0, 0, 35);
})();
