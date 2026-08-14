<?php

declare(strict_types=1);

/**
 * Stripe API credentials.
 *
 * Get your keys from https://dashboard.stripe.com/test/apikeys (test mode)
 * or https://dashboard.stripe.com/apikeys (live mode). Never commit live
 * secret keys to version control.
 *
 * STRIPE_WEBHOOK_SECRET comes from the webhook endpoint you create at
 * https://dashboard.stripe.com/test/webhooks pointing to:
 *   https://yourdomain.com/Madixx/stripe-webhook.php
 * Select the "checkout.session.completed" and
 * "checkout.session.async_payment_succeeded" events.
 */
define('STRIPE_SECRET_KEY', 'sk_test_REPLACE_WITH_YOUR_SECRET_KEY');
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_REPLACE_WITH_YOUR_PUBLISHABLE_KEY');
define('STRIPE_WEBHOOK_SECRET', 'whsec_REPLACE_WITH_YOUR_WEBHOOK_SECRET');

/** Three-letter ISO currency code Stripe should charge in. */
define('STRIPE_CURRENCY', 'usd');
