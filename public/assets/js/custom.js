// Shared request loader for frontend pages
(function () {
    const body = document.body;
    const overlay = document.querySelector("[data-request-loader-overlay]");
    let activeRequests = 0;

    function syncState() {
        if (!body) {
            return;
        }

        body.classList.toggle("request-loader-active", activeRequests > 0);

        if (overlay) {
            overlay.setAttribute(
                "aria-hidden",
                activeRequests > 0 ? "false" : "true",
            );
        }
    }

    function start() {
        activeRequests += 1;
        syncState();
    }

    function stop() {
        activeRequests = Math.max(0, activeRequests - 1);
        syncState();
    }

    window.requestLoader = {
        show: start,
        hide: stop,
        track(promise) {
            start();

            return Promise.resolve(promise).finally(stop);
        },
        async withLoading(callback) {
            start();

            try {
                return await callback();
            } finally {
                stop();
            }
        },
    };

    function markAppReady() {
        if (body) {
            body.classList.add("app-ready");
        }
        syncState();
    }

    // Keep the initial pre-loader visible until the browser finishes loading
    // (matches "tab is loading" expectation). Add a timeout so it can't hang forever.
    document.addEventListener("DOMContentLoaded", markAppReady, { once: true });
    window.addEventListener("load", markAppReady, { once: true });
    window.setTimeout(markAppReady, 10000);

    // When navigating back/forward, browsers may restore the page from bfcache without firing "load".
    // Reset loader state so it doesn't stay stuck visible after a back navigation.
    window.addEventListener(
        "pageshow",
        () => {
            activeRequests = 0;
            markAppReady();
        },
        true,
    );

    document.addEventListener(
        "submit",
        (event) => {
            const form = event.target;

            if (
                form instanceof HTMLFormElement &&
                form.matches("[data-request-loader]")
            ) {
                start();
            }
        },
        true,
    );

    document.addEventListener(
        "click",
        (event) => {
            const trigger = event.target.closest("[data-request-loader]");

            if (!trigger) {
                return;
            }

            const href = trigger.getAttribute("href");

            if (trigger.tagName === "A" && !(href && href.indexOf("#") === 0)) {
                start();
            }
        },
        true,
    );
})();

// TABS JS

function makeTabActive() {
    var url = window.location.href;

    var indexof = url.indexOf("#");

    if (indexof > 0) {
        var activeTab = url.substring(indexof + 1);

        if (
            typeof activeTab != "undefined" &&
            activeTab != "" &&
            activeTab != "#"
        ) {
            // to dispaly give tab content

            jQuery(".tab-pane").removeClass("active in show");

            jQuery("#" + activeTab).addClass("active in show");

            // to make active given tab

            jQuery(".nav-tabs li a").removeClass("active");

            jQuery(".nav-tabs li a[href='#" + activeTab + "']").addClass(
                "active",
            );
        }
    }
}

// QTY plus/minus
$(".qty-plus").click(function () {
    let qty = parseInt($("#qty").val()) || 1;
    $("#qty")
        .val(qty + 1)
        .trigger("input");
});

$(".qty-minus").click(function () {
    let qty = parseInt($("#qty").val()) || 1;
    if (qty > 1) {
        $("#qty")
            .val(qty - 1)
            .trigger("input");
    }
});

// Update price when qty changes
$("#qty")
    .off("input")
    .on("input", function () {
        const selectedType = $('input[name="trafficType"]:checked').val();
        const selectedData = $('input[name="dataAmount"]:checked').val();
        const selectedDay = $('input[name="day"]:checked').val();
        const qty = parseInt($(this).val()) || 1;

        if (selectedType && selectedData && selectedDay) {
            const pricePerItem =
                variationData[selectedType][selectedData][selectedDay];
            const totalPrice = pricePerItem * qty;
            $("#price-display").text(
                `Price: ${pricePerItem} MMK x ${qty} = ${totalPrice} MMK`,
            );
        }
    });

// Define prices based on service days and data
const priceMap = {
    "Daily Type": {
        "3 GB": 1,
        "5 GB": 2,
        "7 GB": 3,
    },
    "Total Type": {
        "3 GB": 5,
        "5 GB": 8,
        "7 GB": 12,
    },
    "Unlimited Type": {
        "3 GB": 10,
        "5 GB": 15,
        "7 GB": 20,
    },
};

// Function to calculate price
function calculatePrice() {
    const priceDisplay = document.getElementById("priceDisplay");
    const trafficTypeEl = document.getElementById("trafficType");
    const serviceDayEl = document.querySelector('input[name="sday"]:checked');
    const dataPlanEl = document.querySelector('input[name="sdata"]:checked');
    const trafficType = trafficTypeEl ? trafficTypeEl.value : null;
    const serviceDay = serviceDayEl ? serviceDayEl.dataset.day : null;
    const dataPlan = dataPlanEl ? dataPlanEl.value : null;
    const typePrices = trafficType ? priceMap[trafficType] : null;

    let basePrice = typePrices && typePrices[dataPlan] ? typePrices[dataPlan] : 0;

    // For demo: multiply base price by number of service days
    const totalPrice = basePrice * parseInt(serviceDay || 1);

    if (priceDisplay) {
        priceDisplay.textContent = `Total Price: $${totalPrice}`;
    }
}
// console.log("trafficType =", trafficType);
// console.log("days =", serviceDay);
// console.log("dataPlan =", dataPlan);
// console.log("priceMap[trafficType] =", priceMap[trafficType]);
// console.log("priceMap[trafficType][days] =", priceMap[trafficType] && priceMap[trafficType][days]);
// console.log("price =", basePrice);
// Event Listeners
document
    .querySelectorAll(
        'input[name="tType"], input[name="sday"], input[name="sdata"]',
    )
    .forEach((el) => {
        el.addEventListener("change", calculatePrice);
    });

// Call initially
calculatePrice();
