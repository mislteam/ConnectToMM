// Modal input & results
const input = document.getElementById("modal-search-input");
const resultsContainer = document.getElementById("modal-search-results");

// Build dynamic search index
async function buildDynamicSearchIndex() {
    const index = [];

    // 1️⃣ Get all sidebar links
    const sidebarLinks = document.querySelectorAll(
        ".side-nav-item .side-nav-link"
    );

    for (let link of sidebarLinks) {
        const url = link.href;
        const pageName =
            link.querySelector(".menu-text")?.innerText.trim() ||
            link.innerText.trim();

        const pageData = { page: pageName, url, items: [] };

        try {
            // 2️⃣ Fetch page HTML
            const res = await fetch(url);
            const html = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");

            // 3️⃣ Grab headings and paragraphs
            const elements = doc.querySelectorAll("h1, h2, h3, p");
            elements.forEach((el) => {
                if (el.innerText.trim()) {
                    pageData.items.push({
                        text: el.innerText.trim(),
                        anchor: el.id ? "#" + el.id : "",
                    });
                }
            });
        } catch (err) {
            console.error("Failed to fetch page:", url, err);
        }

        index.push(pageData);
    }

    return index;
}

// Initialize Fuse.js after building index
let fuse;
let searchIndex = [];

buildDynamicSearchIndex().then((index) => {
    searchIndex = index;

    // Flatten for Fuse.js
    const fuseData = [];
    searchIndex.forEach((page) => {
        page.items.forEach((item) => {
            fuseData.push({
                page: page.page,
                pageUrl: page.url,
                text: item.text,
                anchor: item.anchor || "",
            });
        });
    });

    fuse = new Fuse(fuseData, { keys: ["text"], threshold: 0.3 });
});

// Input listener
input.addEventListener("input", () => {
    const keyword = input.value.trim();
    if (!keyword || !fuse) {
        resultsContainer.innerHTML = "";
        return;
    }

    const results = fuse.search(keyword);

    // Group by page
    const grouped = {};
    results.forEach((r) => {
        if (!grouped[r.item.page]) grouped[r.item.page] = [];
        grouped[r.item.page].push(r.item);
    });

    renderResults(grouped, keyword);
});

// Render results in modal
function renderResults(grouped, keyword) {
    resultsContainer.innerHTML = "";

    for (const page in grouped) {
        const card = document.createElement("div");
        card.className = "card mb-2";

        const header = document.createElement("div");
        header.className = "card-header p-2";
        const pageLink = document.createElement("a");
        pageLink.href = grouped[page][0].pageUrl;
        pageLink.style.color = "#0049ad";
        pageLink.textContent = page;
        header.appendChild(pageLink);

        const body = document.createElement("div");
        body.className = "card-body p-1";
        const ul = document.createElement("ul");
        ul.className = "m-0";

        grouped[page].forEach((item) => {
            const li = document.createElement("li");
            li.className = "my-2";

            const a = document.createElement("a");
            a.href = item.pageUrl + item.anchor;
            a.className = "text-muted";

            // Highlight keyword
            const regex = new RegExp(`(${keyword})`, "gi");
            a.innerHTML = item.text.replace(regex, "<mark>$1</mark>");

            li.appendChild(a);
            ul.appendChild(li);
        });

        body.appendChild(ul);
        card.appendChild(header);
        card.appendChild(body);
        resultsContainer.appendChild(card);
    }
}
