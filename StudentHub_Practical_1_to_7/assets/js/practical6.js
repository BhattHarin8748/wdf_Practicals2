const collections = {
  events: { url: "assets/data/events.json", cacheKey: "studenthub-cache-events", filter: "category", sort: "date", search: ["title", "category", "description", "location", "organizer"] },
  students: { url: "assets/data/students.json", cacheKey: "studenthub-cache-students", filter: "department", sort: "name", search: ["name", "department", "year", "email", "interests"] },
  faqs: { url: "assets/data/faqs.json", cacheKey: "studenthub-cache-faqs", filter: "category", sort: "question", search: ["question", "category", "answer"] }
};
const collectionLabels = { events: "events", students: "students", faqs: "FAQs" };
const pageSize = 6;
const state = { dataset: "events", records: [], page: 1, request: 0, offline: false };
const elements = {
  tabs: [...document.querySelectorAll("[data-dataset]")],
  search: document.querySelector("#dataSearch"),
  filter: document.querySelector("#dataFilter"),
  filterLabel: document.querySelector("#filterLabel"),
  sort: document.querySelector("#dataSort"),
  summary: document.querySelector("#dataSummary"),
  loading: document.querySelector("#dataLoading"),
  error: document.querySelector("#dataError"),
  errorMessage: document.querySelector("#dataErrorMessage"),
  retry: document.querySelector("#retryLoad"),
  results: document.querySelector("#dataResults"),
  pagination: document.querySelector("#pagination"),
  previous: document.querySelector("#previousPage"),
  next: document.querySelector("#nextPage"),
  pageStatus: document.querySelector("#pageStatus")
};

async function fetchCollection(name) {
  const collection = collections[name];
  const response = await fetch(collection.url);
  if (!response.ok) throw new Error(`Request failed (${response.status})`);
  const records = await response.json();
  if (!Array.isArray(records) || records.some(record => !record || typeof record !== "object")) {
    throw new Error("The response is not a valid JSON record list.");
  }
  localStorage.setItem(collection.cacheKey, JSON.stringify(records));
  return records;
}

function populateFilter(name, records) {
  const filterKey = collections[name].filter;
  const allLabel = filterKey === "category" ? "All categories" : "All departments";
  elements.filter.replaceChildren(new Option(allLabel, ""));
  [...new Set(records.map(record => record[filterKey]).filter(Boolean))].sort().forEach(value => {
    elements.filter.add(new Option(value, value));
  });
}

async function loadCollection(name) {
  const request = ++state.request;
  state.dataset = name;
  state.page = 1;
  state.records = [];
  state.offline = false;
  elements.loading.hidden = false;
  elements.error.hidden = true;
  elements.results.replaceChildren();
  elements.pagination.hidden = true;
  elements.summary.textContent = "";
  elements.tabs.forEach(tab => tab.setAttribute("aria-selected", String(tab.dataset.dataset === name)));
  document.querySelector("#collectionPanel").setAttribute("aria-labelledby", `tab-${name}`);
  elements.filterLabel.textContent = collections[name].filter[0].toUpperCase() + collections[name].filter.slice(1);
  elements.search.value = "";
  elements.sort.value = "default";
  try {
    state.records = await fetchCollection(name);
    if (request !== state.request) return;
  } catch (error) {
    if (request !== state.request) return;
    try {
      const cached = JSON.parse(localStorage.getItem(collections[name].cacheKey) || "null");
      if (!Array.isArray(cached)) throw new Error("No valid cached copy.");
      state.records = cached;
      state.offline = true;
    } catch {
      elements.loading.hidden = true;
      elements.errorMessage.textContent = `Could not load ${name}: ${error.message}`;
      elements.error.hidden = false;
      return;
    }
  }
  elements.loading.hidden = true;
  populateFilter(name, state.records);
  renderCollection();
}

function makeCard(record) {
  const card = document.createElement("article");
  card.className = `data-card ${state.dataset.slice(0, -1)}-card`;
  const addText = (tag, text, className) => {
    const node = document.createElement(tag);
    node.textContent = text;
    if (className) node.className = className;
    card.append(node);
  };
  if (state.dataset === "events") {
    addText("p", `${record.category} · ${new Date(`${record.date}T00:00:00`).toLocaleDateString(undefined, { dateStyle: "medium" })}`, "data-meta");
    addText("h2", record.title);
    addText("p", record.description);
    addText("p", `${record.location} · Organized by ${record.organizer}`);
  } else if (state.dataset === "students") {
    const initials = record.name.split(/\s+/).map(part => part[0]).slice(0, 2).join("").toUpperCase();
    addText("span", initials, "student-initials");
    addText("p", `${record.department} · ${record.year}`, "data-meta");
    addText("h2", record.name);
    addText("p", record.email);
    addText("p", `Interests: ${record.interests}`);
  } else {
    addText("p", record.category, "data-meta");
    addText("h2", record.question);
    addText("p", record.answer);
  }
  return card;
}

function renderCollection() {
  const collection = collections[state.dataset];
  const query = elements.search.value.trim().toLocaleLowerCase();
  const selectedFilter = elements.filter.value;
  let records = state.records
    .filter(record => !selectedFilter || record[collection.filter] === selectedFilter)
    .filter(record => !query || collection.search.some(key => String(record[key] ?? "").toLocaleLowerCase().includes(query)));
  const direction = elements.sort.value;
  if (direction !== "default") {
    records = [...records].sort((first, second) => {
      const comparison = String(first[collection.sort]).localeCompare(String(second[collection.sort]), undefined, { numeric: true });
      return direction === "ascending" ? comparison : -comparison;
    });
  }
  const pageCount = Math.max(1, Math.ceil(records.length / pageSize));
  state.page = Math.min(state.page, pageCount);
  const start = (state.page - 1) * pageSize;
  elements.results.replaceChildren(...records.slice(start, start + pageSize).map(makeCard));
  elements.summary.textContent = `${records.length} ${collectionLabels[state.dataset]} found${state.offline ? " · offline copy" : ""}`;
  elements.pagination.hidden = records.length === 0;
  elements.pagination.style.display = records.length === 0 ? "none" : "flex";
  elements.previous.disabled = state.page === 1;
  elements.next.disabled = state.page === pageCount;
  elements.pageStatus.textContent = `Page ${state.page} of ${pageCount}`;
  if (records.length === 0) {
    const empty = document.createElement("div");
    empty.className = "data-state";
    empty.textContent = "No matching records. Try another search or filter.";
    elements.results.append(empty);
  }
}

elements.tabs.forEach(tab => tab.addEventListener("click", () => loadCollection(tab.dataset.dataset)));
elements.search.addEventListener("input", () => { state.page = 1; renderCollection(); });
elements.filter.addEventListener("change", () => { state.page = 1; renderCollection(); });
elements.sort.addEventListener("change", () => { state.page = 1; renderCollection(); });
elements.previous.addEventListener("click", () => { state.page -= 1; renderCollection(); });
elements.next.addEventListener("click", () => { state.page += 1; renderCollection(); });
elements.retry.addEventListener("click", () => loadCollection(state.dataset));
loadCollection(state.dataset);
