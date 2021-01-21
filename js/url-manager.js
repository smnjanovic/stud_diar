const URLManager = {};

(() => {
  const DEFINE = (name, fn) => Object.defineProperty(URLManager, name, { value: fn });

  DEFINE("getUrlData", () => {
    let data = {};
    let search = location.search.replace(/^\??/, "");
    search && search.split("&").map(x => x.split("=")).forEach(x => data[decodeURI(x[0])] = decodeURI(x[1]));
    return data;
  });

  DEFINE("dataToUrl", data => {
    data = data instanceof Object ? data : {};
    let search = Object.keys(data).map(key => `${encodeURI(key)}=${encodeURI(data[key])}`).join("&");
    return `${location.origin}${location.pathname}?${search}${location.hash}`;
  });

  DEFINE("setUrlData", (data, reload = false) => {
    if (reload) window.location.href = URLManager.dataToUrl(data);
    else history.pushState({}, "", URLManager.dataToUrl(data));
  });

  DEFINE("getItem", key => URLManager.getUrlData()[key])

  DEFINE("setItem", (key, value, reload = false) => {
    let data = URLManager.getUrlData();
    data[key] = value;
    URLManager.setUrlData(data, reload);
  })

  DEFINE("removeItem", (key, reload = false) => {
    let data = URLManager.getUrlData();
    delete data[key];
    URLManager.setUrlData(data, reload);
  })
})()
