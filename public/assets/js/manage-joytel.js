// manage status
var manageStatusModal = document.getElementById("manage-status");
manageStatusModal.addEventListener("show.bs.modal", function (event) {
    var button = event.relatedTarget;
    var planData = JSON.parse(button.getAttribute("data-plan"));

    var tbody = manageStatusModal.querySelector("#invoice-items");
    tbody.innerHTML = ""; // Clear previous rows

    planData.forEach((item, index) => {
        var row = document.createElement("tr");
        row.innerHTML = `
                    <td>${index + 1}</td>
                    <td class="text-start"><label class="form-label">${
                        item.product_code
                    }</label></td>
                    <td>
                        <div class="form-check form-switch form-check-secondary fs-xxl mb-2">
                            <input type="checkbox" class="form-check-input mt-1 code-status-toggle" data-product-code="${
                                item.product_code
                            }"
                                id="checkbox-${index}" ${
            item.code_status == 1 ? "checked" : ""
        }>
                            <label class="form-check-label fs-base" for="checkbox-${index}">${
            item.code_status == 1 ? "Enable" : "Disable"
        }</label>
                        </div>
                    </td>
                `;
        tbody.appendChild(row);
    });

    tbody.querySelectorAll(".code-status-toggle").forEach((checkbox) => {
        checkbox.addEventListener("change", function () {
            // label
            let label =
                this.closest(".form-check").querySelector(".form-check-label");
            label.textContent = this.checked ? "Enable" : "Disable";

            // update btn
            var updateBtn =
                manageStatusModal.querySelector("button.btn-primary");
            updateBtn.onclick = function () {
                let sim_id = button.getAttribute("data-id");
                let updates = [];

                tbody.querySelectorAll(".code-status-toggle").forEach((cb) => {
                    updates.push({
                        product_code: cb.getAttribute("data-product-code"),
                        code_status: cb.checked ? 1 : 0,
                    });
                });

                fetch("/joytel/update-code-status", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                    body: JSON.stringify({
                        sim_id: sim_id,
                        updates: updates,
                    }),
                })
                    .then((res) => res.json())
                    .then((data) => {
                        if (data.success) {
                            // Swal.fire("Success", "Status updated successfully");
                            window.location.reload();
                        } else {
                            // Swal.fire("Error", "Failed to update status");
                            console.log("err");
                        }
                    })
                    .catch((err) => {
                        console.error(err);
                        Swal.fire("Error", "Error while updating status");
                    });
            };
        });
    });
});
// end manage status

// manage-price exchange rate
var managePriceModal = document.getElementById("manage-price");
var updateBtn = document.getElementById("manage-price-update-btn");

managePriceModal.addEventListener("show.bs.modal", function (event) {
    var button = event.relatedTarget;
    var planData = JSON.parse(button.getAttribute("data-plan") || "[]");
    var existingRates = JSON.parse(button.getAttribute("data-existing-rates") || "{}");

    var joytelId = button.getAttribute("data-joytel-id");
    this.setAttribute("data-current-joytel-id", joytelId);

    var tbody = managePriceModal.querySelector("#price-invoice-items");
    tbody.innerHTML = "";

    planData.forEach((item, index) => {
        let portalPrice = Number(item.price_cny) || 0;
        let productCode = item.product_code ?? "";
        let trafficType = item.traffic_type ?? "";

        let cnyRate = parseFloat(window.cnyRate) || 0;

        // auto exchange rate
        let exchangeRateAuto = Math.round(portalPrice * cnyRate);

        // selling rate (manual input from DB)
        let sellingRate = parseFloat(existingRates[productCode]) || 0;

        // Logic for Total and Profit displays
        let totalDisplay = "-";
        let profitDisplay = "-";

        if (sellingRate > 0) {
            let totalVal = Math.round(portalPrice * sellingRate);
            let profitVal = Math.round(totalVal - exchangeRateAuto);
            totalDisplay = totalVal.toLocaleString();
            profitDisplay = profitVal.toLocaleString();
        }

        let row = document.createElement("tr");
        row.innerHTML = `
            <td>${index + 1}</td>
            <td class="text-start">${productCode}</td>
            <td>${trafficType}</td>
            <td class="exchange-auto">${exchangeRateAuto.toLocaleString()}</td>
            <td class="portal-price">${portalPrice}</td>
            <td>
            <input type="number" class="form-control selling-rate"
                value="${sellingRate}" step="0.01"
                data-product-code="${productCode}">
            </td>
            <td class="profit-label">${profitDisplay}</td>
            <td><span class="total-label">${totalDisplay}</span></td>
        `;
        tbody.appendChild(row);
    });

    // Dynamic total calculation
    tbody.querySelectorAll(".selling-rate").forEach((input) => {
        input.addEventListener("input", function () {
            let row = this.closest("tr");
            let portalPrice = parseFloat(row.querySelector(".portal-price").innerText) || 0;
            let sellingRate = parseFloat(this.value) || 0;
            
            let cnyRate = parseFloat(window.cnyRate) || 0;
            let exchangeRateAuto = portalPrice * cnyRate;

            if (sellingRate <= 0) {
                row.querySelector(".total-label").innerText = "-";
                row.querySelector(".profit-label").innerText = "-";
            
            }else {
                let totalVal = Math.round(portalPrice * sellingRate);
                let profitVal = Math.round(totalVal - exchangeRateAuto);
                
                row.querySelector(".total-label").innerText = totalVal.toLocaleString();
                row.querySelector(".profit-label").innerText = profitVal.toLocaleString();
            }
        });
    });
});

// Update button
updateBtn.addEventListener("click", function () {
    var tbody = managePriceModal.querySelector("#price-invoice-items");
    var joytelId = managePriceModal.getAttribute("data-current-joytel-id");
    var updates = [];

    tbody.querySelectorAll(".selling-rate").forEach((input) => {
        let row = input.closest("tr");

        let portalPrice = parseFloat(row.querySelector(".portal-price").innerText) || 0;
        let sellingRate = parseFloat(input.value) || 0;
        let cnyRate = parseFloat(window.cnyRate) || 0;

        let exchangeRateAuto = portalPrice * cnyRate;
        let totalVal = portalPrice * sellingRate;
        let profitVal = totalVal - exchangeRateAuto;

        updates.push({
            product_code: input.getAttribute("data-product-code"),
            exchange_rate: sellingRate,
            profit: sellingRate > 0 ? Math.round(profitVal) : 0,
            joytel_id: joytelId
        });
    });

    fetch("/joytel/update-price", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
        },
        body: JSON.stringify({ updates: updates }),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: 'Exchange rates have been updated successfully.',
                timer: 2000,
                showConfirmButton: false
            });

        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message || "Failed to update exchange rates."
            });
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: "error while updating exchange rates."
        });
    });
});

// delete sim card
let selectedSimId = null;
document.querySelectorAll(".delete-sim-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
        selectedSimId = this.getAttribute("data-id");
    });
});

document
    .getElementById("confirmDeleteBtn")
    .addEventListener("click", function () {
        if (!selectedSimId) return;

        fetch(`/joytel/delete-esim/${selectedSimId}`, {
            method: "DELETE",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]'
                ).content,
            },
        })
            .then((res) => {
                if (!res.ok) throw new Error("Failed to delete SIM.");
                return res.json();
            })
            .then(() => {
                const modal = bootstrap.Modal.getInstance(
                    document.getElementById("sim-delete")
                );
                modal.hide();

                document
                    .querySelector(`[data-id="${selectedSimId}"]`)
                    ?.closest("tr")
                    ?.remove();
                window.location.reload();
            })
            .catch((err) => {
                console.error(err);
            });
    });