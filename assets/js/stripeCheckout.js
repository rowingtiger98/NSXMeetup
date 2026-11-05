// This is your test publishable API key.
const stripe = Stripe("pk_test_51Sobo02eLAINkcsAGgRlJN2DguBLsAeBJKibuFOQmMoEGq5vyDBaTTbINWGCxZkkOKLWPuqy0NdivIY9VKeth2pw00samlseME");

// The items the customer wants to buy
// const items = [{ id: "xl-tshirt", amount: 1000  }, { id: "M-tshirt", amount: 1500  }];

console.log("Items at checkout init: " + localStorage.getItem('shopping_cart'));
// const items = JSON.parse(localStorage.getItem('shopping_cart'));


let elements;

initialize();

document
  .querySelector("#payment-form")
  .addEventListener("submit", handleSubmit);

//   function ProcessStripeCheckOut() {
//     console.log("Start the STRIPE Checkout Process");

//     initialize();
//   }

// Fetches a payment intent and captures the client secret
async function initialize() {
    const items = JSON.parse(localStorage.getItem('shopping_cart'))['basket'];
    console.log("In initialize function on checkout.js, Creating the payment intent element with items = " + JSON.stringify(items));
    console.log("TESTING..." + JSON.stringify(items));
    const { clientSecret } = await fetch("/stripeCreate.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: localStorage.getItem('shopping_cart'),
  }).then((r) => r.json());



  console.log('Stripe Token Create: ' + clientSecret)


  elements = stripe.elements({ clientSecret });
  const options = { mode: 'shipping' };

  const paymentElementOptions = {
    layout: "accordion",
  };
  const addressElement = elements.create('address', options);
  const paymentElement = elements.create("payment", paymentElementOptions);
  addressElement.mount('#address-element');
  paymentElement.mount("#payment-element");
}

async function handleSubmit(e) {
  e.preventDefault();
  setLoading(true);
  console.log("In the handleSubmit function in checkout.js")
  const { error } = await stripe.confirmPayment({
    elements,
    confirmParams: {
      // Make sure to change this to your payment completion page
      return_url: "https://grabmywheel.org/ron/complete.html",
    },
  });

  // This point will only be reached if there is an immediate error when
  // confirming the payment. Otherwise, your customer will be redirected to
  // your `return_url`. For some payment methods like iDEAL, your customer will
  // be redirected to an intermediate site first to authorize the payment, then
  // redirected to the `return_url`.
  if (error.type === "card_error" || error.type === "validation_error") {
    showMessage(error.message);
  } else {
    showMessage("An unexpected error occurred.");
  }

  setLoading(false);
}

// ------- UI helpers -------

function showMessage(messageText) {
  const messageContainer = document.querySelector("#payment-message");

  messageContainer.classList.remove("hidden");
  messageContainer.textContent = messageText;

  setTimeout(function () {
    messageContainer.classList.add("hidden");
    messageContainer.textContent = "";
  }, 4000);
}

// Show a spinner on payment submission
function setLoading(isLoading) {
  if (isLoading) {
    // Disable the button and show a spinner
    document.querySelector("#submit").disabled = true;
    document.querySelector("#spinner").classList.remove("hidden");
    document.querySelector("#button-text").classList.add("hidden");
  } else {
    document.querySelector("#submit").disabled = false;
    document.querySelector("#spinner").classList.add("hidden");
    document.querySelector("#button-text").classList.remove("hidden");
  }
}