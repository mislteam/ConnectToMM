// Rewritten manage-joytel.js: stable handlers for status/price updates
// Manage Status
const manageStatusModal = document.getElementById("manage-status");
if (manageStatusModal) {
    manageStatusModal.addEventListener("show.bs.modal", function (event) {
        const trigger = event.relatedTarget;
        const planData = JSON.parse(trigger.getAttribute("data-plan") || "[]");

        const tbody = manageStatusModal.querySelector("#invoice-items");
        tbody.innerHTML = "";

        planData.forEach((item, index) => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${index + 1}</td>
                <td class="text-start"><label class="form-label">${item.product_code}</label></td>
                <td>
                    <div class="form-check form-switch form-check-secondary fs-xxl mb-2">
                        <input type="checkbox" class="form-check-input mt-1 code-status-toggle" data-product-code="${item.product_code}" id="checkbox-${index}" ${item.code_status == 1 ? 'checked' : ''}>
                        <label class="form-check-label fs-base" for="checkbox-${index}">${item.code_status == 1 ? 'Enable' : 'Disable'}</label>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });

        // update labels on change
        tbody.querySelectorAll(".code-status-toggle").forEach((cb) => {
            const lbl = cb.closest(".form-check").querySelector(".form-check-label");
            lbl.textContent = cb.checked ? 'Enable' : 'Disable';
            cb.addEventListener('change', () => {
                lbl.textContent = cb.checked ? 'Enable' : 'Disable';
            });
        });

        // bind a single update handler (replace to avoid duplicate listeners)
        const origBtn = manageStatusModal.querySelector("button.btn-primary");
        const newBtn = origBtn.cloneNode(true);
        origBtn.parentNode.replaceChild(newBtn, origBtn);

        newBtn.addEventListener('click', function () {
            const sim_id = trigger.getAttribute('data-id');
            const updates = [];
            tbody.querySelectorAll('.code-status-toggle').forEach((cb) => {
                updates.push({ product_code: cb.getAttribute('data-product-code'), code_status: cb.checked ? 1 : 0 });
            });

            newBtn.disabled = true;

            fetch('/joytel/update-code-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ sim_id: sim_id, updates: updates })
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.success) {
                    const modal = bootstrap.Modal.getInstance(manageStatusModal);
                    if (modal) modal.hide();
                    // redirect to same page with saved flag so server-side or blade can show alert
                    window.location.href = window.location.pathname + '?saved=1';
                } else {
                    Swal.fire('Error', (data && data.message) || 'Failed to update status');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Error while updating status');
            })
            .finally(() => {
                newBtn.disabled = false;
            });
        });
    });
}

// Manage Price
const managePriceModal = document.getElementById('manage-price');
const priceUpdateBtn = document.getElementById('manage-price-update-btn');

if (managePriceModal) {
    managePriceModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const planData = JSON.parse(button.getAttribute('data-plan') || '[]');
        const existingRates = JSON.parse(button.getAttribute('data-existing-rates') || '{}');

        const joytelId = button.getAttribute('data-joytel-id');
        this.setAttribute('data-current-joytel-id', joytelId);

        const tbody = managePriceModal.querySelector('#price-invoice-items');
        tbody.innerHTML = '';

        planData.forEach((item, index) => {
            const portalPrice = Number(item.price_cny) || 0;
            const productCode = item.product_code || '';
            const trafficType = item.traffic_type || '';
            const cnyRate = parseFloat(window.cnyRate) || 0;

            const exchangeRateAuto = Math.round(portalPrice * cnyRate);
            const sellingRate = parseFloat(existingRates[productCode]) || 0;

            let totalDisplay = '-';
            let profitDisplay = '-';
            if (sellingRate > 0) {
                const totalVal = Math.round(portalPrice * sellingRate);
                const profitVal = Math.round(totalVal - exchangeRateAuto);
                totalDisplay = totalVal.toLocaleString();
                profitDisplay = profitVal.toLocaleString();
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${index + 1}</td>
                <td class="text-start">${productCode}</td>
                <td>${trafficType}</td>
                <td class="exchange-auto">${exchangeRateAuto.toLocaleString()}</td>
                <td class="portal-price">${portalPrice}</td>
                <td><input type="number" class="form-control selling-rate" value="${sellingRate}" step="0.01" data-product-code="${productCode}"></td>
                <td class="profit-label">${profitDisplay}</td>
                <td><span class="total-label">${totalDisplay}</span></td>
            `;
            tbody.appendChild(tr);
        });

        // dynamic calculation
        tbody.querySelectorAll('.selling-rate').forEach((input) => {
            input.addEventListener('input', function () {
                const row = this.closest('tr');
                const portalPrice = parseFloat(row.querySelector('.portal-price').innerText) || 0;
                const sellingRate = parseFloat(this.value) || 0;
                const cnyRate = parseFloat(window.cnyRate) || 0;
                const exchangeRateAuto = portalPrice * cnyRate;

                if (sellingRate <= 0) {
                    row.querySelector('.total-label').innerText = '-';
                    row.querySelector('.profit-label').innerText = '-';
                } else {
                    const totalVal = Math.round(portalPrice * sellingRate);
                    const profitVal = Math.round(totalVal - exchangeRateAuto);
                    row.querySelector('.total-label').innerText = totalVal.toLocaleString();
                    row.querySelector('.profit-label').innerText = profitVal.toLocaleString();
                }
            });
        });
    });
}

// Price update handler (bind once)
if (priceUpdateBtn) {
    priceUpdateBtn.addEventListener('click', function () {
        const tbody = managePriceModal.querySelector('#price-invoice-items');
        const joytelId = managePriceModal.getAttribute('data-current-joytel-id');
        const updates = [];

        tbody.querySelectorAll('.selling-rate').forEach((input) => {
            const row = input.closest('tr');
            const portalPrice = parseFloat(row.querySelector('.portal-price').innerText) || 0;
            const sellingRate = parseFloat(input.value) || 0;
            const cnyRate = parseFloat(window.cnyRate) || 0;
            const exchangeRateAuto = portalPrice * cnyRate;
            const totalVal = portalPrice * sellingRate;
            const profitVal = totalVal - exchangeRateAuto;

            updates.push({
                product_code: input.getAttribute('data-product-code'),
                exchange_rate: sellingRate,
                profit: sellingRate > 0 ? Math.round(profitVal) : 0,
                joytel_id: joytelId
            });
        });

        priceUpdateBtn.disabled = true;

        fetch('/joytel/update-price', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ updates: updates })
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.success) {
                const modal = bootstrap.Modal.getInstance(managePriceModal);
                if (modal) modal.hide();
                // redirect so blade can render the same saved alert
                window.location.href = window.location.pathname + '?saved=1';
                return;
            } else {
                Swal.fire({ icon: 'error', title: 'Error!', text: (data && data.message) || 'Failed to update exchange rates.' });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({ icon: 'error', title: 'Error!', text: 'Error while updating exchange rates.' });
        })
        .finally(() => {
            priceUpdateBtn.disabled = false;
        });
    });
}

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