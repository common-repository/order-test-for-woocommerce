
var enabledProduct = document.querySelector("[name='woocommerce_order-test-for-woocommerce_enabled_product']");
var enabledBillingForm = document.querySelector("[name='woocommerce_order-test-for-woocommerce_enabled_billing_form']");

var productRow = document.querySelector("label[for='woocommerce_order-test-for-woocommerce_product']").parentNode.parentNode;
var firstNameRow = document.querySelector("label[for='woocommerce_order-test-for-woocommerce_first_name']").parentNode.parentNode;
var lastNameRow = document.querySelector("label[for='woocommerce_order-test-for-woocommerce_last_name']").parentNode.parentNode;
var phoneRow = document.querySelector("label[for='woocommerce_order-test-for-woocommerce_phone']").parentNode.parentNode;
var emailRow = document.querySelector("label[for='woocommerce_order-test-for-woocommerce_email']").parentNode.parentNode;
var address1Row = document.querySelector("label[for='woocommerce_order-test-for-woocommerce_address1']").parentNode.parentNode;
var cityRow = document.querySelector("label[for='woocommerce_order-test-for-woocommerce_city']").parentNode.parentNode;
var countryRow = document.querySelector("label[for='woocommerce_order-test-for-woocommerce_country']").parentNode.parentNode;

enabledProduct.addEventListener("change", function(event) {
    productChecked(event.target);
});
enabledBillingForm.addEventListener("change", function(event) {
    billingFormChecked(event.target);
});

productChecked(enabledProduct);
billingFormChecked(enabledBillingForm);
 
function productChecked(element) {
    if (!element.checked) {
        productRow.classList.add("hidden");
    }
    else {
        productRow.classList.remove("hidden");
    }
}
function billingFormChecked(element) {
    if (!element.checked) {
        firstNameRow.classList.add("hidden");
        lastNameRow.classList.add("hidden");
        phoneRow.classList.add("hidden");
        emailRow.classList.add("hidden");
        address1Row.classList.add("hidden");
        cityRow.classList.add("hidden");
        countryRow.classList.add("hidden");
    }
    else {
        firstNameRow.classList.remove("hidden");
        lastNameRow.classList.remove("hidden");
        phoneRow.classList.remove("hidden");
        emailRow.classList.remove("hidden");
        address1Row.classList.remove("hidden");
        cityRow.classList.remove("hidden");
        countryRow.classList.remove("hidden");
    }
}