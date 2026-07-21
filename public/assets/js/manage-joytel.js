const csrfToken =
    document.querySelector('meta[name="csrf-token"]')?.content || "";
const manageStatusModal = document.getElementById("manage-status");
const managePriceModal = document.getElementById("manage-price");
const priceUpdateBtn = document.getElementById("manage-price-update-btn");

let currentStatusTrigger = null;
let currentPriceTrigger = null;

function toNumber(value) {
    const parsed = parseFloat(value);
    return Number.isFinite(parsed) ? parsed : 0;
}

function getProductCode(item) {
    return item?.product_code ?? item?.code ?? "";
}

function getStatusValue(item) {
    return Number(item?.code_status ?? item?.status ?? 0);
}

function getPortalPrice(item) {
    return toNumber(item?.price_cny ?? item?.price ?? 0);
}

function buildExistingRateMap() {
    const rates = {};
    const rows = Array.isArray(window.additionalPrices)
        ? window.additionalPrices
        : [];

    rows.forEach((row) => {
        if (!row || row.product_code == null) return;
        rates[row.product_code] = toNumber(row.exchange_rate);
    });

    return rates;
}

function mergeExistingRates(button) {
    const rates = buildExistingRateMap();

    if (button) {
        try {
            const buttonRates = JSON.parse(
                button.getAttribute("data-existing-rates") || "{}",
            );
            Object.assign(rates, buttonRates);
        } catch (error) {
            console.warn("Invalid existing rates payload", error);
        }
    }

    return rates;
}

function syncExistingRateCache(updates) {
    if (!Array.isArray(window.additionalPrices)) {
        window.additionalPrices = [];
    }

    const cache = new Map(
        window.additionalPrices.map((row) => [row.product_code, { ...row }]),
    );

    updates.forEach((update) => {
        if (!update?.product_code) return;

        const existing = cache.get(update.product_code) || {
            product_code: update.product_code,
        };
        existing.exchange_rate = update.exchange_rate;
        existing.profit = update.profit;
        cache.set(update.product_code, existing);
    });

    window.additionalPrices = Array.from(cache.values());

    if (currentPriceTrigger) {
        currentPriceTrigger.setAttribute(
            "data-existing-rates",
            JSON.stringify(buildExistingRateMap()),
        );
    }
}

function getJoytelType(trigger) {
    return trigger?.getAttribute("data-joytel-type") || "esim";
}

if (manageStatusModal) {
    manageStatusModal.addEventListener("show.bs.modal", function (event) {
        currentStatusTrigger = event.relatedTarget;
        const productName =
            currentStatusTrigger?.getAttribute("data-product-name") || "";

        document.getElementById("product-title").textContent = productName;

        const planData = JSON.parse(
            currentStatusTrigger?.getAttribute("data-plan") || "[]",
        );

        const joytelType = getJoytelType(currentStatusTrigger);
        const tbody = manageStatusModal.querySelector("#invoice-items");
        tbody.innerHTML = "";

        planData.forEach((item, index) => {
            const productCode = getProductCode(item);
            const data = item?.data ?? "";
            const serviceDay = item?.service_day ?? "";
            const trafficType = item?.traffic_type ?? "";
            const status = getStatusValue(item);
            const rowId = item?.id ?? "";
            const tr = document.createElement("tr");

            tr.innerHTML = `
                <td>${index + 1}</td>
                <td class="text-start"><label class="form-label">${productCode}</label></td>
                <td class="text-start"><label class="form-label">${data}: ${trafficType.charAt(0).toUpperCase() + trafficType.slice(1)}: ${serviceDay}</label></td>
                <td>
                    <div class="form-check form-switch form-check-secondary fs-xxl mb-2">
                        <input
                            type="checkbox"
                            class="form-check-input mt-1 code-status-toggle"
                            data-id="${rowId}"
                            id="status-${rowId || index}"
                            ${status === 1 ? "checked" : ""}
                        >
                        <label class="form-check-label fs-base" for="status-${rowId || index}">
                            ${status === 1 ? "Enable" : "Disable"}
                        </label>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });

        tbody.querySelectorAll(".code-status-toggle").forEach((checkbox) => {
            const label = checkbox
                .closest(".form-check")
                ?.querySelector(".form-check-label");
            if (label) {
                label.textContent = checkbox.checked ? "Enable" : "Disable";
            }

            checkbox.addEventListener("change", () => {
                if (label) {
                    label.textContent = checkbox.checked ? "Enable" : "Disable";
                }
            });
        });

        const originalBtn =
            manageStatusModal.querySelector("button.btn-primary");
        const newBtn = originalBtn.cloneNode(true);
        originalBtn.parentNode.replaceChild(newBtn, originalBtn);

        newBtn.addEventListener("click", function () {
            const updates = [];

            tbody
                .querySelectorAll(".code-status-toggle")
                .forEach((checkbox) => {
                    updates.push({
                        id: checkbox.dataset.id,
                        status: checkbox.checked ? 1 : 0,
                    });
                });

            newBtn.disabled = true;

            fetch("/joytel/update-code-status", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                },
                body: JSON.stringify({ joytel_type: joytelType, updates }),
            })
                .then((res) => res.json())
                .then((data) => {
                    if (data && data.success) {
                        const modal =
                            bootstrap.Modal.getInstance(manageStatusModal);
                        if (modal) modal.hide();
                        window.location.href = `${window.location.pathname}?saved=1`;
                        return;
                    }

                    Swal.fire(
                        "Error",
                        (data && data.message) || "Failed to update status",
                    );
                })
                .catch((error) => {
                    console.error(error);
                    Swal.fire("Error", "Error while updating status");
                })
                .finally(() => {
                    newBtn.disabled = false;
                });
        });
    });
}

if (managePriceModal) {
    managePriceModal.addEventListener("show.bs.modal", function (event) {
        currentPriceTrigger = event.relatedTarget;
        const productName =
            currentPriceTrigger?.getAttribute("data-product-name") || "";
        const usageLocations =
            currentPriceTrigger?.getAttribute("data-usage-locations") || "";

        const usageLocationArray = JSON.parse(usageLocations || []);

        document.getElementById("product-name-title").textContent = productName;
        document.getElementById("country-list").textContent = usageLocationArray
            ? usageLocationArray.join(", ")
            : "-";

        const planData = JSON.parse(
            currentPriceTrigger?.getAttribute("data-plan") || "[]",
        );
        const existingRates = mergeExistingRates(currentPriceTrigger);
        const joytelType = getJoytelType(currentPriceTrigger);
        const tbody = managePriceModal.querySelector("#price-invoice-items");
        tbody.innerHTML = "";

        planData.forEach((item, index) => {
            const productCode = getProductCode(item);
            const portalPrice = getPortalPrice(item);
            const data = item?.data ?? "";
            const serviceDay = item?.service_day ?? "";
            const trafficType = item?.traffic_type ?? "";
            const sellingRate = toNumber(existingRates[productCode]);
            const cnyRate = toNumber(window.cnyRate);
            const exchangeRateAuto = Math.round(portalPrice * cnyRate);
            const totalValue =
                sellingRate > 0 ? Math.round(portalPrice * sellingRate) : 0;
            const profitValue =
                sellingRate > 0 ? Math.round(totalValue - exchangeRateAuto) : 0;

            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${index + 1}</td>
                <td class="text-start">${productCode}</td>
                <td class="export-plan-name">${data}: ${trafficType.charAt(0).toUpperCase() + trafficType.slice(1)}: ${serviceDay}</td>
                <td class="exchange-auto">${exchangeRateAuto.toLocaleString()}</td>
                <td class="portal-price">${portalPrice}</td>
                <td>
                    <input
                        type="number"
                        class="form-control selling-rate"
                        value="${sellingRate || "0"}"
                        step="0.01"
                        min="0"
                        data-product-code="${productCode}"
                    >
                </td>
                <td class="profit-label">${sellingRate > 0 ? profitValue.toLocaleString() : "-"}</td>
                <td><span class="total-label">${sellingRate > 0 ? totalValue.toLocaleString() : "-"}</span></td>
            `;
            tbody.appendChild(tr);
        });

        tbody.querySelectorAll(".selling-rate").forEach((input) => {
            input.addEventListener("input", function () {
                const row = this.closest("tr");
                const portalPrice = toNumber(
                    row.querySelector(".portal-price")?.innerText,
                );
                const sellingRate = toNumber(this.value);
                const cnyRate = toNumber(window.cnyRate);
                const exchangeRateAuto = portalPrice * cnyRate;

                if (sellingRate <= 0) {
                    row.querySelector(".total-label").innerText = "-";
                    row.querySelector(".profit-label").innerText = "-";
                    return;
                }

                const totalValue = Math.round(portalPrice * sellingRate);
                const profitValue = Math.round(totalValue - exchangeRateAuto);
                row.querySelector(".total-label").innerText =
                    totalValue.toLocaleString();
                row.querySelector(".profit-label").innerText =
                    profitValue.toLocaleString();
            });
        });
    });
}

if (priceUpdateBtn) {
    priceUpdateBtn.addEventListener("click", function () {
        const tbody = managePriceModal.querySelector("#price-invoice-items");
        const joytelId =
            currentPriceTrigger?.getAttribute("data-joytel-id") ||
            managePriceModal.getAttribute("data-current-joytel-id");
        const updates = [];
        const joytelType = getJoytelType(currentPriceTrigger);

        tbody.querySelectorAll(".selling-rate").forEach((input) => {
            const row = input.closest("tr");
            const portalPrice = toNumber(
                row.querySelector(".portal-price")?.innerText,
            );
            const sellingRate = toNumber(input.value);
            const cnyRate = toNumber(window.cnyRate);
            const exchangeRateAuto = portalPrice * cnyRate;
            const totalValue = portalPrice * sellingRate;
            const profitValue = totalValue - exchangeRateAuto;

            updates.push({
                product_code: input.getAttribute("data-product-code"),
                exchange_rate: sellingRate,
                profit: sellingRate > 0 ? Math.round(profitValue) : 0,
                joytel_id: joytelId,
            });
        });

        priceUpdateBtn.disabled = true;

        fetch("/joytel/update-price", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
                Accept: "application/json",
            },

            body: JSON.stringify({ joytel_type: joytelType, updates }),
        })
            .then((res) => res.json())
            .then((data) => {
                if (data && data.success) {
                    syncExistingRateCache(updates);

                    const modal = bootstrap.Modal.getInstance(managePriceModal);
                    if (modal) modal.hide();

                    window.location.href = `${window.location.pathname}?saved=1`;
                    return;
                }

                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text:
                        (data && data.message) ||
                        "Failed to update exchange rates.",
                });
            })
            .catch((error) => {
                console.error(error);
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: "Error while updating exchange rates.",
                });
            })
            .finally(() => {
                priceUpdateBtn.disabled = false;
            });
    });
}

//for delete
let selectedSimId = null;

document.querySelectorAll(".delete-sim-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
        selectedSimId = this.getAttribute("data-id");
    });
});

document
    .getElementById("confirmDeleteBtn")
    ?.addEventListener("click", function () {
        if (!selectedSimId) return;

        fetch(`/joytel/delete-esim/${selectedSimId}`, {
            method: "DELETE",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
                Accept: "application/json",
            },
        })
            .then((res) => {
                if (!res.ok) throw new Error("Failed to delete SIM.");
                return res.json();
            })
            .then(() => {
                const modal = bootstrap.Modal.getInstance(
                    document.getElementById("sim-delete"),
                );
                if (modal) modal.hide();

                document
                    .querySelector(`[data-id="${selectedSimId}"]`)
                    ?.closest("tr")
                    ?.remove();
                window.location.reload();
            })
            .catch((error) => {
                console.error(error);
            });
    });
