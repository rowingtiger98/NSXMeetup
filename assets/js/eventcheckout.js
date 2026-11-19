// -------------------------------------------------------------------------------------
// Checkout page (event_checkout.php)
// -------------------------------------------------------------------------------------

// -------------------------------------------------------------------------------------
// Function: 	SelectCheckoutPaymentMethod
// Description: Only one payment method at a time. Checking a method's box opens its
//              drawer and unchecks, greys out and closes every other method; unchecking
//              it closes its drawer and brings the others back.
// -------------------------------------------------------------------------------------
function SelectCheckoutPaymentMethod() {
    var selected = this;
    $(".checkout_payment_select").each(function() {
        var method = $(this).closest('.checkout_payment_method');
        var drawer = $("#" + $(this).attr('drawer'));
        var is_selected = (this === selected && selected.checked);

        if (this !== selected) {
            this.checked = false;
        }
        $(this).attr('aria-expanded', is_selected ? 'true' : 'false');
        method.toggleClass('selected', is_selected)
              .toggleClass('not_selected', selected.checked && !is_selected);
        if (is_selected) {
            drawer.slideDown(200);
        } else {
            drawer.slideUp(200);
        }
    });
}
