=== Subscription Billing by Billflow ===
Contributors: servicebot
Tags: stripe,subscriptions,payments,pricing page,subscription management
Requires at least: 5.0
Tested up to: 5.5.1
Requires PHP: 7.1
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Stable tag: trunk

Integrate Stripe Billing with your Wordpress site in minutes. Allow customers to view pricing, sign up, and manage subscriptions.

== Upgrade Notice ==
IMPORTANT!! This 2.0.0 version upgrade could introduce some breaking change!
If you are using this plugin's 1.x.x version already, please test this plugin thoroughly!
Some standard tests you should do:
 * Check all you embed pages and test all flows
 * Check your webhook integrations and make sure they still work as you had before
 * If you are not using our Billing Pages to create your embeds, please create them at dashboard.billflow.io first and test them in your dev/stage sites.
 * Make a full backup of your site before upgrading!

== Description ==
= IMPORTANT Upgrade Notice =
This 2.0.0 version upgrade could introduce some breaking change!
If you are using this plugin's 1.x.x version already, please test this plugin thoroughly!
Some standard tests you should do:
 * Check all you embed pages and test all flows
 * Check your webhook integrations and make sure they still work as you had before
= Make a full backup of your site before upgrading! =

= Billflow =
Launch a subscription business faster than ever with the Stripe Billing plugin by Billflow. 
This plugin automatically connects your Stripe account to your Wordpress site allowing you to show beautiful pricing pages, take payments for Stripe Subscriptions, and let customers manage their accounts. The plugin uses Wordpress's default user authentication to allow your customers to log-in and manage their subscriptions. 
Features:
 * Beautiful Pricing pages that can handle any Stripe pricing model
 * Secure sign up forms allowing customers to subscribe to plans
 * Handle Free trials & freemium pricing
 * Supports Stripe coupons
 * Allow customers to manage their subscription. Cancel, upgrade, and downgrade.
 * Customers can update their payment information
 * Customers can download Stripe invoices
 * Subscription management portal can be used with Stripe Checkout
 * Automatically create your Stripe customers as WordPress users with Stripe Webhooks

== How To Use ==
 * Install this plugin
 * Create a billing page at [dashboard.billflow.io](https://dashboard.billflow.io)
 * Insert a shortcode and embed one any of the billflow embeds, pricing, checkout, customer portal etc.
 * Setup Stripe webhooks to sync your Stripe customer and subscription status to your WordPress user and user roles.

== Shortcodes Examples ==

= Embed a page. =
`[billflow billing_page_id="1234567890"]`

= Embed a page and gate it behind a login. =
`[billflow billing_page_id="1234567890" logged_in_only="true"]`

= Embed a page that is only for non-users and send user to another page if logged in. =
`[billflow billing_page_id="1234567890" logged_out_only="/my-account"]`

= Embed a page that is only for users with certain role =
`[billflow billing_page_id="1234567890" logged_in_only="true" gated="basic_tier"]`

= Or embed a page for users with any of the listed roles =
`[billflow billing_page_id="1234567890" logged_in_only="true" gated="basic_tier, premium_tier"]`

= Gate any page to a specific role =
Send basic tier users to an upgrade checkout page

`[billflow gated="basic_tier" upgrade_url="/upgrade"]`

Or send any other user / non-users to a sign up pricing page

`[billflow gated="basic_tier, premium_tier" upgrade_url="/pricing"]`

== Installation ==
STEP-BY-STEP INSTRUCTIONS
Log in to your site's dashboard (e.g. www.yourdomain.com/wp-admin).
Click on the "Plugins" tab in the left panel, then click "Add New".
Search for "Billflow" and find the plugin.
Install it by clicking the "Install Now" link.
When installation finishes, click "Activate Plugin".

Continue on with documentation [here](https://docs.billflow.io/subscription-portal/integrations/wordpress)


== Frequently Asked Questions ==
= Is this supported in my country? =
  
This is supported by every Stripe supported country. Go [here](https://stripe.com/global) to see if Stripe is available in your country. 

= Do I need to have a Billflow account? =
  
Yes, the plugin is there to connect your wordpress with your Billflow account. You can signup [here](https://dashboard.billflow.io/signup). 

= Can I change the style of these pages? =
  
Yes you can, check out our docs here to learn more about styling these pages [here](https://docs.billflow.io/subscription-portal/pricing-page/customize-style-and-features-of-pricing-page). 

= Where is the documentation for the plugin? =
  
Join us [here](https://docs.billflow.io/subscription-portal/integrations/wordpress), and feel free to message us on our website with any questions.

== Screenshots ==
1. Pricing Pages
2. Signup Forms
3. Subscription Management Portal