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
                "active"
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
                `Price: ${pricePerItem} MMK x ${qty} = ${totalPrice} MMK`
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
    const trafficType = document.getElementById("#trafficType")?.value;
    const serviceDay = document.querySelector('input[name="sday"]:checked')
        ?.dataset.day;
    const dataPlan = document.querySelector(
        'input[name="sdata"]:checked'
    )?.value;

    let basePrice = priceMap[trafficType]?.[dataPlan] || 0;

    // For demo: multiply base price by number of service days
    const totalPrice = basePrice * parseInt(serviceDay || 1);

    document.getElementById(
        "priceDisplay"
    ).textContent = `Total Price: $${totalPrice}`;
}
// console.log("trafficType =", trafficType);
// console.log("days =", serviceDay);
// console.log("dataPlan =", dataPlan);
// console.log("priceMap[trafficType] =", priceMap[trafficType]);
// console.log("priceMap[trafficType][days] =", priceMap[trafficType]?.[days]);
// console.log("price =", basePrice);
// Event Listeners
document
    .querySelectorAll(
        'input[name="tType"], input[name="sday"], input[name="sdata"]'
    )
    .forEach((el) => {
        el.addEventListener("change", calculatePrice);
    });

// Call initially
calculatePrice();
