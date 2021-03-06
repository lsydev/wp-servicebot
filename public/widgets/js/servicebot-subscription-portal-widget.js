(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
    
    console.log("loaded servicebot widget js", php_props_sp_widget);

    const { billing_page_id, livemode, servicebot_id, hash, service, tier, interval, email, 
            customer_id, subscription_id, coupon, options, create_user, is_logged_in, 
            logged_in_email, login_redirect_url, admin_ajax_url, embed_type, js_version } = php_props_sp_widget;

    function handleResponse ({event, response, extras}) {
        if(event === "create_subscription" && create_user == 1){
            console.log("create_subscription event callback", response, extras);
            let email    = response.customer.email;
            let username = response.customer.email.substring(0, email.lastIndexOf("@"));
            let password = extras ? extras.password : null;

            var ajax_url = admin_ajax_url;

            if(event === "create_subscription" && !logged_in_email){
                console.log("Creating user", response, extras);
                let payload = {
                    action: "create_user", 
                    email: response.customer.email, 
                    name: username,
                    password: password
                };

                let callback = function(data) {
                    console.log("create_subscription create_user callback", data)

                    if(servicebot_wp_handle_response_create_subscription){
                        servicebot_wp_handle_response_create_subscription({event, response, extras});
                    }

                    if(login_redirect_url){
                        window.location = login_redirect_url;
                    }
                };

                jQuery.post(ajax_url, payload, callback);
            }

        }
        if(servicebot_wp_handle_response){
            servicebot_wp_handle_response({event, response, extras})
        }
    }
    
    //prepare options
    let parsedOptions = JSON.parse(options);
    if(parsedOptions.hideTiers && typeof parsedOptions.hideTiers == 'string')
        parsedOptions.hideTiers = parsedOptions.hideTiers.split(',');
    if(parsedOptions.disableTiers && typeof parsedOptions.disableTiers == 'string')
        parsedOptions.disableTiers = parsedOptions.disableTiers.split(',');


    if(billing_page_id){
        window.servicebotSettings = {
            'billing_page_id': billing_page_id,
            'email': logged_in_email || email || '',
            'hash': hash,
            'handleResponse' : handleResponse,
            'metadata': {
                'serverside_config': true,
                'widget_type': embed_type,
                'plugin_type': 'wordpress',
                'plugin_version': js_version,
            }
        }
    }else{
        window.servicebotSettings = {
            'servicebot_id': servicebot_id,
            'email': logged_in_email || email || '',
            'hash': hash,
            'service': service,
            'coupon': coupon,
            'options' : parsedOptions,
            'handleResponse' : handleResponse,
            'metadata': {
                'widget_type': embed_type,
                'plugin_type': 'wordpress',
                'plugin_version': js_version,
            }
            
        }
    }

    tier && (window.servicebotSettings.tier = tier);
    interval && (window.servicebotSettings.interval = interval);
    customer_id && (window.servicebotSettings.customer_id = customer_id);
    subscription_id && (window.servicebotSettings.subscription_id = subscription_id);

    console.log('servicebotSettings', servicebotSettings)

    if(document.querySelector('#billflow-embed') || document.querySelector('#servicebot-billing-settings-embed')){
        if(window.location.host == 'servicebot-wordpress.docksal'){
            (function () { 
                var s = document.createElement("script"); 
                s.src = "/wp-content/themes/twentynineteen/js/build/servicebot-billing-settings-embed.js"; 
                s.async = true; 
                s.type = "text/javascript"; 
                var x = document.getElementsByTagName("script")[0]; 
                x.parentNode.insertBefore(s, x); })();
        }else{
            (function () { 
                var s = document.createElement('script'); 
                s.src = 'https://js.billflow.io/billflow-embed.js'; 
                s.async = true; 
                s.type = 'text/javascript'; 
                var x = document.getElementsByTagName('script')[0]; 
                x.parentNode.insertBefore(s, x); })();
        }
    }else{
        console.warn("Please make sure <div id='billflow-embed'></div> is on the page. You can ignore this warning if you are on the Wordpress editor.")
    }
    
})( jQuery );
