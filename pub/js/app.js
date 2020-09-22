/* AJAX */

/**
 * Vraca sva polja forme u obliku JSON-a
 * @param {string} formId id forme koja se pretvara u JSON
 */
const formToJSON = formId => {
  const form = document.getElementById(formId);
  const fd = new FormData(form);
  let jsonObject = {};
  fd.forEach((value, key) => {
    if (!jsonObject.hasOwnProperty(key)) {
      jsonObject[key] = value;
      return;
    }
    if (!Array.isArray(jsonObject[key])) {
      jsonObject[key] = [jsonObject[key]];
    }
    jsonObject[key].push(value);
  });
  return jsonObject;
};

// XMLHttpRequest

/**
 * Salje GET ili POST zahtev
 * @param {string} method
 * @param {string} url
 * @param {string|JSON} data form id | JSON object
 */
const sendAjaxRequest = (method, url, data) => {
  const promise = new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url);
    xhr.responseType = "json";
    if (data) {
      if (typeof data === "string") {
        data = formToJSON(data);
      } else {
        addCsrfToken(data);
      }
      xhr.setRequestHeader("Content-Type", "application/json");
    }
    xhr.onload = () => {
      if (xhr.status >= 400) {
        reject(xhr.response);
      } else {
        resolve(xhr.response);
      }
    };
    xhr.onerror = () => {
      reject("Prso ajax!");
    };
    xhr.send(JSON.stringify(data));
  });
  return promise;
};

const updateCsrfToken = data => {
  const csrfNames = document.querySelectorAll(".csrf_name");
  const csrfValues = document.querySelectorAll(".csrf_value");
  csrfNames.forEach(name => {
    name.value = data.csrf_name;
  });
  csrfValues.forEach(val => {
    val.value = data.csrf_value;
  });
};

/**
 * Proveriti da li formToJSON() ubacije csrf,
 * ako ubacuje onda ovo nije potrebno
 * @param {*} data
 */
const addCsrfToken = data => {
  const csrfName = document.querySelector(".csrf_name");
  const csrfValue = document.querySelector(".csrf_value");
  data.csrf_name = csrfName.value;
  data.csrf_value = csrfValue.value;
};
