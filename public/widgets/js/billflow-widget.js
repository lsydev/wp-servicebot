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
	 * Ideally, it is not considered best practice to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

    const { billing_page_id, livemode, hash, email, customer_id } = php_props_billflow_settings
    const { subscription_id, create_user, is_logged_in, logged_in_email } = php_props_billflow_settings
    const { login_redirect_url, admin_ajax_url, embed_type, js_version } = php_props_billflow_settings;
    const { logged_out_only, logged_in_only, gated, user_roles, upgrade_url, can_edit_site } = php_props_billflow_settings;

    /**
     * Gating logics
     */
    //console.log("gated", gated)
    //console.log("roles", user_roles)

    if(!can_edit_site){
        if(!is_logged_in){
            if((logged_in_only || gated.length)){
                kick_out()
            }
        }else{
            if(logged_out_only){
                kick_back()
            }else{
                if(gated.length){
                    if( !user_roles || !user_roles.length){
                        kick_out()
                    }else if(!hasRole()){
                        upgrade()
                    }
                }
            }
        }
    }

    /**
     * functions
     */
    function upgrade(){
        if(upgrade_url){
            window.location.replace(upgrade_url + "?redirect_to=" + window.location.pathname + window.location.search)
        }else{
            // alert("Please add upgrade_url to the billflow shortcode with the upgrade checkout page to send this user to upgrade, otherwise, it goes to home page by default")
        }
    }

    function kick_out(){
        if(logged_in_only === "true" || gated.length){
            window.location.replace(login_redirect_url + "?redirect_to=" + window.location.pathname + window.location.search)
        }else{
            window.location.href = logged_in_only
        }
    }

    function kick_back(){
        if(logged_out_only === "true"){
            window.location.href = '/'
        }else{
            window.location.href = logged_out_only
        }
    }

    function hasRole(){

        for(let i = 0; i < gated.length; i++){
            if(user_roles.find( role => role == gated[i] )){
                return true
            }
        }
        return false
    }

    function handleResponse ({event, response, extras}) {
        if(event === "create_subscription" && create_user == 1){
            console.debug("create_subscription event callback", response, extras);
            const email    = response.customer.email;
            const username = response.customer.email.substring(0, email.lastIndexOf("@"));
            const password = extras ? extras.password : null;
            const ajax_url = admin_ajax_url;

            if(event === "create_subscription" && !logged_in_email){
                console.debug("Creating user", response, extras);
                const payload = {
                    action: "create_user", 
                    email: response.customer.email, 
                    name: username,
                    subscription_id: response && response.id,
                    password: password
                };

                jQuery.post(ajax_url, payload, function(data) {
                    console.debug("create_subscription create_user callback", data)

                    /**
                     * deprecated: to be removed in 2022
                     */
                    if(servicebot_wp_handle_response_create_subscription){
                        servicebot_wp_handle_response_create_subscription({event, response, extras});
                    }
                    /**
                     * new handle response hook for 2021
                     */
                    if(billflow_wp_handle_response_create_subscription){
                        billflow_wp_handle_response_create_subscription({event, response, extras});
                    }

                    if(login_redirect_url){
                        console.debug("redirect url set", login_redirect_url);
                        window.location = login_redirect_url;
                    }else if(data.refresh){
                        window.location.reload();
                    }

                }).done(function(){
                    // console.log("Billflow WP account creation successful")
                }).fail(function(){
                    // alert('checkout create usr failed');
                    console.error("Billflow WP account creation encountered an error");
                })
            }

        }
        /**
         * deprecated: to be removed in 2022
         */
        if(window.servicebot_wp_handle_response || servicebot_wp_handle_response){
            servicebot_wp_handle_response({event, response, extras})
        }
        if(window.billflow_wp_handle_response){
            console.debug("billflow_wp_handle_response")
            billflow_wp_handle_response({event, response, extras})
        }
    }

    if(billing_page_id){
        const settings = {
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

        window.servicebotSettings = settings
        window.billflowSettings = settings
        customer_id && (window.servicebotSettings.customer_id = customer_id);
        subscription_id && (window.servicebotSettings.subscription_id = subscription_id);

        

        if(document.querySelector("#billflow-embed")){
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
            console.log("Please make sure <div id='billflow-embed'></div> is on the page. You can ignore this warning if you are on the Wordpress editor.")
        }
    }
    
})( jQuery );
