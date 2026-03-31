//pagination
const grid = document.getElementById("grid");
const pagination = document.getElementById("pagination");
const paginationInfo = document.getElementById("pagination-info");
const images = Array.from(document.querySelectorAll("#grid .col"));

const imagesPerPage = 20;
let currentPage = 1;

function renderPage(page) {
    grid.innerHTML = "";
    const start = (page - 1) * imagesPerPage;
    const end = start + imagesPerPage;
    const items = images.slice(start, end);

    if (items.length === 0) {
        const msg = document.createElement("p");
        msg.className = "text-center text-muted py-3 w-100";
        msg.textContent = "No images found.";
        grid.appendChild(msg);
    } else {
        items.forEach((item) => grid.appendChild(item));
    }
    renderPagination();
    updatePaginationInfo();
}

function renderPagination() {
    pagination.innerHTML = "";
    const totalPages = Math.ceil(images.length / imagesPerPage);
    const ul = document.createElement("ul");
    ul.className = "pagination pagination-boxed mb-0 justify-content-center";

    function createPageItem(label, page, disabled = false, active = false) {
        const li = document.createElement("li");
        li.className = `page-item ${disabled ? "disabled" : ""} ${
            active ? "active" : ""
        }`;
        li.innerHTML = `<a href="#" class="page-link">${label}</a>`;
        if (!disabled && !active) {
            li.addEventListener("click", (e) => {
                e.preventDefault();
                currentPage = page;
                renderPage(currentPage);
            });
        }
        return li;
    }

    ul.appendChild(
        createPageItem(
            `<i class="ti ti-chevron-left"></i>`,
            currentPage - 1,
            currentPage === 1
        )
    );

    const maxVisible = 5;
    let start = Math.max(1, currentPage - Math.floor(maxVisible / 2));
    let end = Math.min(totalPages, start + maxVisible - 1);

    if (end - start < maxVisible - 1) {
        start = Math.max(1, end - maxVisible + 1);
    }

    if (start > 1) {
        ul.appendChild(createPageItem(1, 1));
        if (start > 2) {
            const dots = document.createElement("li");
            dots.className = "page-item disabled";
            dots.innerHTML = `<a href="#" class="page-link">...</a>`;
            ul.appendChild(dots);
        }
    }

    for (let i = start; i <= end; i++) {
        ul.appendChild(createPageItem(i, i, false, i === currentPage));
    }

    if (end < totalPages) {
        if (end < totalPages - 1) {
            const dots = document.createElement("li");
            dots.className = "page-item disabled";
            dots.innerHTML = `<a href="#" class="page-link">...</a>`;
            ul.appendChild(dots);
        }
        ul.appendChild(createPageItem(totalPages, totalPages));
    }

    ul.appendChild(
        createPageItem(
            `<i class="ti ti-chevron-right"></i>`,
            currentPage + 1,
            currentPage === totalPages
        )
    );

    pagination.appendChild(ul);
}

function updatePaginationInfo() {
    const total = images.length;
    const start = (currentPage - 1) * imagesPerPage + 1;
    const end = Math.min(currentPage * imagesPerPage, total);
    paginationInfo.innerHTML = `
          Showing <span class="fw-semibold">${start}</span> to 
         <span class="fw-semibold">${end}</span> of 
         <span class="fw-semibold">${total}</span> entries
     `;
}

if (grid) renderPage(currentPage);
