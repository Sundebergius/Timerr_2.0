<x-app-layout>
    <div class="container">
        <h2>Upgrade to Freelancer Package</h2>

        <!-- Payment form for upgrading to Freelancer plan -->
        <form action="{{ route('stripe.process') }}" method="POST" id="payment-form">
            @csrf
            <div class="form-group">
                <label for="card-element">
                    Enter your credit card details
                </label>
                <div id="card-element">
                    <!-- A Stripe Element will be inserted here -->
                </div>
                <!-- Used to display form errors -->
                <div id="card-errors" role="alert"></div>
            </div>
            <button id="card-button" class="btn btn-primary" data-secret="{{ $intent }}">
                Upgrade Plan
            </button>
        </form>
    </div>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        const stripe = Stripe('{{ env('STRIPE_KEY') }}');
        const elements = stripe.elements();
        const cardElement = elements.create('card');
        cardElement.mount('#card-element');

        const form = document.getElementById('payment-form');
        const cardButton = document.getElementById('card-button');
        const clientSecret = cardButton.dataset.secret;

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const { paymentIntent, error } = await stripe.confirmCardPayment(clientSecret, {
                payment_method: {
                    card: cardElement,
                    billing_details: {
                        name: '{{ $user->name }}'
                    }
                }
            });

            if (error) {
                // Display error message
                document.getElementById('card-errors').textContent = error.message;
            } else {
                // Submit the form to the backend if payment succeeds
                form.submit();
            }
        });
    </script>
</x-app-layout>
