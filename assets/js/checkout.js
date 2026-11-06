
// Fetches a payment intent and captures the client secret
function CaptureStripeIntentAndSecrets() {

}

function ProcessStripeCheckOut() {
    const items = JSON.parse(localStorage.getItem('shopping_cart'))['basket'];
    console.log("Start the Internal STRIPE Checkout Process, Creating the payment intent element with items = " + JSON.stringify(items));

    // This is your test publishable API key.
    // const stripe = Stripe("pk_test_51Sobo02eLAINkcsAGgRlJN2DguBLsAeBJKibuFOQmMoEGq5vyDBaTTbINWGCxZkkOKLWPuqy0NdivIY9VKeth2pw00samlseME");

    var driver_one_total = $(this).attr('reg_button_driver1_total');
    var driver_two_total = $(this).attr('reg_button_driver2_total');

	$.ajax({
	    url: 'CHECKOUTFunctions.php',
	    type: 'POST',
	    data: {method: 'CreateStripeIntent', items:localStorage.getItem('shopping_cart')},
	    success: function(data) {
	  	    console.log('JS - TEST - Create Stripe Intent ' + data);
	  	    results = eval(data);

            if(results[0] === 'OK') {
                console.log("Success! clientSecret = " + results[1]);
            } else {
                console.log("Failure getting client secret from Stripe: " + results[1]);
            }
	    },	
	  });


}

function DisplayShoppingCartAtCheckout() {
    console.log('DisplayShoppingCartAtCheckout() with Starting shopping cart:' + localStorage.getItem('shopping_cart'));
	$.ajax({
	    url: 'CHECKOUTFunctions.php',
	    type: 'POST',
	    data: {method: 'DisplayShoppingCartAtCheckout', items:localStorage.getItem('shopping_cart')},
	    success: function(data) {
	  	    console.log('DisplayShoppingCartAtCheckout ' + data);
	  	    $("#shopping_cart").html(data);

            // if(results[0] === 'OK') {
            //     console.log("Success! clientSecret = " + results[1]);
            // } else {
            //     console.log("Failure getting client secret from Stripe: " + results[1]);
            // }
	    },	
	  });    
}

function shopping_cart_actions(itemName, itemCost, itemDesc, driver, action) {
    console.log('Starting shopping cart:' + localStorage.getItem('shopping_cart'));
    console.log("Adding ("+itemDesc+") : " + itemName + ' : ' + itemCost + ' : ' + driver);

    if (action == 'add') {
        cart_information = JSON.parse(localStorage.getItem('shopping_cart'))
        new_item = {"id" : itemName , "amount" : itemCost, "description" : itemDesc, "driver" : driver};
        cart_information.basket.push(new_item);
        localStorage.setItem('shopping_cart', JSON.stringify(cart_information))   
    } else {
        console.log('Remove a Item: ' + itemName);
        cart_information = JSON.parse(localStorage.getItem('shopping_cart'))
        const item_index = cart_information.basket.findIndex(item => item.id === itemName);
        console.log("Index value = " + item_index);

        cart_information.basket = cart_information.basket.filter(item => item.id !== itemName);
        localStorage.setItem('shopping_cart', JSON.stringify(cart_information))   
    }
    console.log('Ending shopping cart:' + localStorage.getItem('shopping_cart'));
}

function update_driver_totals(driver_number) {
    console.log('update_driver_totals ('+driver_number+') : Starting shopping cart:' + localStorage.getItem('shopping_cart'));
    let new_driver_cost = 0; 
    const cart_information = JSON.parse(localStorage.getItem('shopping_cart'));
    const filteredObject = cart_information.basket.filter(item => item.driver === driver_number);
    console.log ("Filtered Object list: " + JSON.stringify(filteredObject));
    const sumQuantity = filteredObject.reduce((accumulator, currentValue) => {
        return accumulator + parseInt(currentValue.amount);
    }, 0);
    console.log(driver_number + ' new total = ' + sumQuantity);
    $("#"+driver_number+"_cost").attr('total', sumQuantity);
    $("#"+driver_number+"_cost").html("$"+sumQuantity/100+".00");
    $("#main_register_button").attr('reg_button_'+driver_number+'_total', sumQuantity);




}