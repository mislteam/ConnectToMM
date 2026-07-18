document.addEventListener("click", (event) => {
    const button = event.target.closest(".plan-export-btn");
    if (!button) return;
    exportPlanDataToExcel(button);
});

function exportPlanDataToExcel(button) {
    const modal = button.closest(".modal") || document;
    const simType = button.dataset.simType;
    const usageLocations = modal.querySelector("#country-list").innerText || "";

    const configs = {
        "joytel-esim": {
            titleSelector: "#product-name-title",
            rechargePrefix: false,
            sheetName: "Joytel eSIM",
        },
        "joytel-physical": {
            titleSelector: "#product-name-title",
            rechargePrefix: true,
            sheetName: "Joytel Physical",
        },
        "roam-esim": {
            titleSelector: "#product-name-title",
            rechargePrefix: false,
            sheetName: "Roam eSIM",
        },
        "roam-physical": {
            titleSelector: ".modal-body .modal-title span",
            rechargePrefix: true,
            sheetName: "Roam Physical",
        },
    };

    const config = configs[simType];
    if (!config) {
        console.warn("Unknown sim type:", simType);
        return;
    }

    let productName =
        modal.querySelector(config.titleSelector)?.innerText.trim() || "";

    if (config.rechargePrefix) {
        productName = `Recharge - ${productName}`;
    }

    // why array
    const rows = [...modal.querySelectorAll("table tbody tr")];

    const exportRows = rows
        .filter((row) => row.querySelectorAll("td").length > 0)
        .map((row, index) => {
            const plan = cleanText(
                row.querySelector(".export-plan-name")?.innerText,
            );
            const totalPriceText = cleanText(
                row.querySelector(".total-label").innerText,
            );
            const totalPrice = parseNumber(totalPriceText);

            // why double code and not
            return {
                Plan: plan,
                Country: usageLocations,
                "Total Price": totalPrice,
            };
        })
        .filter((row) => row.Plan !== "" && row["Total Price"] !== "");

    if (exportRows.length === 0) {
        Swal.fire("Error", "No data to export.");
        return;
    }

    const worksheet = XLSX.utils.json_to_sheet(exportRows);

    worksheet["!cols"] = [{ wch: 45 }, { wch: 45 }, { wch: 18 }];
    const workbook = XLSX.utils.book_new();

    XLSX.utils.book_append_sheet(workbook, worksheet, config.sheetName);

    const fileName = `${slugify(productName)}.xlsx`;

    XLSX.writeFile(workbook, fileName);
}

// why cleanText is needed
function cleanText(value) {
    return String(value ?? "")
        .replace(/\s+/g, " ")
        .trim();
}

function parseNumber(value) {
    const cleaned = String(value ?? "")
        .replace(/,/g, "")
        .replace(/[^\d.-]/g, "")
        .trim();

    if (cleaned === "") return "";

    return Number(cleaned);
}

function slugify(value) {
    return String(value ?? "export")
        .replace(/recharge\s+/g, "Recharge-")
        .replace(/^-+|-+$/g, "");
}

$(document).on("change", "#wallet-status", function () {
    const checkbox = $(this);
    const updateUrl = checkbox.data("url");
    const previousStatus = !checkbox.is(":checked");

    const status = checkbox.is(":checked") ? 1 : 0;

    checkbox.prop("disabled", true);

    $.ajax({
        url: updateUrl,
        type: "PATCH",
        data: {
            _token: $('meta[name="csrf-token"]').attr("content"),
            status: status,
        },
        success: function (response) {
            Swal.fire("success", response.message);
            window.location.reload();
        },
        error: function (xhr) {
            checkbox.prop("checked", previousStatus);

            Swal.fire(
                "error",
                xhr.responseJSON?.message ?? "Wallet status update failed.",
            );
        },
        complete: function () {
            checkbox.prop("disabled", false);
        },
    });
});
