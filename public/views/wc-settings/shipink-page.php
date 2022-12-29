<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

            $user = wp_get_current_user();

            $params['source'] = 'woocommerce';
            $params['email'] = $user->user_email;
			$params['first_name'] = !empty($user->first_name) ? $user->first_name : $user->user_nicename;
            $params['last_name'] = !empty($user->last_name) ? $user->last_name : '';
            $params['mobile_phone'] = !empty($user->billing_phone) ? $user->billing_phone : '';
			$params['platform_store_id'] = get_current_network_id();
            $params['name'] = get_option('blogname');
            $params['url'] = get_option('home');
			$connectQuery = esc_html(http_build_query($params));
?>
<style>
@import url(@import url('https://fonts.googleapis.com/css?family=Lato:100,300,400');

.page-wrapper {
  height: 100vh;
  font-family: 'Lato', sans-serif;
  display: flex;
  justify-content: center;
  align-items: center;
}

.prompt-container {
  background-color: white;
  width: 400px;
  padding: 35px 45px;
  border-radius: 6px;
  display: flex;
  flex-direction: column;
  -webkit-box-shadow: 0px 0px 4px 0px rgba(0,0,0,0.5);
  -moz-box-shadow: 0px 0px 4px 0px rgba(0,0,0,0.5);
  box-shadow: 0px 0px 4px 0px rgba(0,0,0,0.5);
}

h1 {
  font-weight: 100;
  font-size: 1.25em; /* 20/16 */
  letter-spacing: 1px;
  text-transform: uppercase;
  margin-bottom: 24px;
  text-align: center;
}

input {
  border-radius: 5px;
  background-color: #E5E9ED;
  border: none;
  margin: 8px 0;
  padding: 16px 16px;
  font-size: 1em; /* 16/16 */
  font-weight: 300;
}

.checkbox-span {
  margin-top: 8px;
}

label {
  font-size: 1em;
  font-weight: 300;
}

#checkbox {
  margin-right: 8px;
  cursor: pointer;
}

button {
  border-radius: 6px;
  padding: 16px 0;
  text-align: center;
  background-color: #3a0ca3;
  border: none;
  font-size: 1em; /* 16/16 */
  font-weight: 400;
  letter-spacing: 1px;
  text-transform: uppercase;
  color: white;
  margin-top: 16px;
  cursor: pointer;
}

button:hover {
  background-color: #4339c6;
}
</style>
<div class="page-wrapper">
    <div class="prompt-container">
      <h1>Shipink</h1>
	  <label>Shipink is a platform that connects multiple carriers and sales channels and provides an easy-to-use interface to manage all of your label creation, tracking, returns, and many more.</label>

        <button id="woocommerce_shipink_connect">Connect Shipink</button>
    </div>
  </div>

  <script type="text/javascript">
  jQuery(document).ready(function ($) {
    $('#woocommerce_shipink_connect').on('click', function (e) {
		window.location.href = 'https://app.shipink.io/signup?<?php  echo esc_html($connectQuery); ?>';
    });
});

  </script>
  