<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <title>IPA Checker</title>
    <!-- Path to Framework7 Library CSS-->
    <link rel="stylesheet" href="css/framework7.ios.min.css">
    <link rel="stylesheet" href="css/framework7.ios.colors.min.css">
    <!-- Path to your custom app styles-->
    <link rel="stylesheet" href="css/my-app.css">
    <style type="text/css">
    	#ipaView {
    		-webkit-box-shadow: 0px 0px 22px -4px rgba(0, 0, 0, 0.16);
    		-moz-box-shadow: 0px 0px 22px -4px rgba(0, 0, 0, 0.16);
    		box-shadow: 0px 0px 22px -4px rgba(0, 0, 0, 0.16);
    		width: 95%;
    		border-radius: 12px;
    		margin-left: auto;
    		margin-right: auto;
    	}
    	#ipaView .card-header {
    	  height: 40vw;
    	  border-radius: 12px 12px 0px 0px;
    	  /*background-size: cover;*/
    	  /*background-position: center;*/
    	  background-size: cover;
    		background-repeat: no-repeat;
    		background-position: center center;
    	}
    	.navbar:after {
    		display: none;
    	}

    	.toolbar:before {
    		display: none;
    	}
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  </head>
  <body>
    <!-- Status bar overlay for fullscreen mode-->
    <div class="statusbar-overlay"></div>
    <!-- Panels overlay-->
    <div class="panel-overlay"></div>
    <!-- Left panel with reveal effect-->
    <div class="panel panel-left panel-reveal">
      <div class="content-block">
        <p>Left panel content goes here</p>
      </div>
    </div>
    <!-- Right panel with cover effect-->
    <div class="panel panel-right panel-cover">
      <div class="content-block">
        <p>Right panel content goes here</p>
      </div>
    </div>
    <!-- Views-->
    <div class="views">
      <!-- Your main view, should have "view-main" class-->
      <div class="view view-main">
        <!-- Top Navbar-->
        <div class="navbar" style="background: #FFF; -webkit-box-shadow: 0px 0px 22px -4px rgba(0, 0, 0, 0.16); -moz-box-shadow: 0px 0px 22px -4px rgba(0, 0, 0, 0.16); box-shadow: 0px 0px 22px -4px rgba(0, 0, 0, 0.16);">
          <div class="navbar-inner">
            <div class="center sliding" style="font-weight: 500;">IPA Checker</div>
          </div>
        </div>
        <!-- Pages, because we need fixed-through navbar and toolbar, it has additional appropriate classes-->
        <div class="pages navbar-through toolbar-through">
          <!-- Page, data-page contains page name-->
          <div data-page="index" class="page">
            <!-- Scrollable page content-->
            <div class="page-content" style="background: #FFF;">
              <div class="card" id="ipaView" style="display: none;">
                <div id="ipaHeader" valign="bottom" class="card-header color-white no-border"></div>
                <div class="card-content">
                  <div class="card-content-inner">
                    <p class="color-gray"><span id="ipaStatus"></span> • <span id="service"></span></p>
                    <p><span id="certName"></span></p>
                  </div>
                </div>
                <!-- <div class="card-footer"> -->
                  <!-- <a href="#" class="link">Like</a> -->
                  <!-- <a href="#" class="link">Read more</a> -->
                <!-- </div> -->
              </div>

              <div class="list-block">
                <ul style="background: #fff; -webkit-box-shadow: 0px 0px 22px -4px rgba(0, 0, 0, 0.16); -moz-box-shadow: 0px 0px 22px -4px rgba(0, 0, 0, 0.16); box-shadow: 0px 0px 22px -4px rgba(0, 0, 0, 0.16); width: 95%; border-radius: 12px; margin-left: auto; margin-right: auto;">
                  <li>
                    <div class="item-content">
                      <div class="item-inner">
                        <!-- <div class="item-title label">IPA URL</div> -->
                        <div class="item-input">
                            <input type="text" name="name" id="ipaURL" placeholder="IPA URL">
                        </div>
                      </div>
                    </div>
                  </li>
                </ul>
              </div>  

              <a href="#" class="button button-big button-fill" style="background: #5d74d8; width: 95%; margin-left: auto; margin-right: auto; -webkit-box-shadow: 0px 0px 22px -4px rgba(0, 0, 0, 0.16); -moz-box-shadow: 0px 0px 22px -4px rgba(0, 0, 0, 0.16); box-shadow: 0px 0px 22px -4px rgba(0, 0, 0, 0.16); border-radius: 12px;"  onclick="checkOCSP(document.getElementById('ipaURL').value);">Check Signature</a>
              <!-- <center>
                <h1>Check IPA Signature</h1>
                <input type="text" id="ipaURL" name="url" placeholder="IPA URL">
                <br>
                <button onclick="checkOCSP(document.getElementById('ipaURL').value);">Check Signature</button>
                <hr>
                <div id="info" style="display: none;">
                  <h3>Certificate Name: <span id="certName"></span></h3>
                  <h3>IPA Status: <span id="ipaStatus"></span></h3>
                  <h3>Service (Educated Guess): <span id="service"></span></h3>
                  <h3>IPA Name: <span id="ipaName"></span></h3>
                  <img id="ipaIcon" src="https://ipas.fun/images/ibox.png" width="60px" height="60px">
                </div>
              </center> -->
            </div>
          </div>
        </div>
        <!-- Bottom Toolbar-->
        <div class="toolbar" style="background: #FFF; -webkit-box-shadow: 0px 0px 22px -4px rgba(0, 0, 0, 0.16); -moz-box-shadow: 0px 0px 22px -4px rgba(0, 0, 0, 0.16); box-shadow: 0px 0px 22px -4px rgba(0, 0, 0, 0.16);">
          <div class="toolbar-inner"><a href="#" class="link">Twitter</a><a href="#" class="link">GitHub</a></div>
        </div>
      </div>
    </div>
    <!-- Path to Framework7 Library JS-->
    <script type="text/javascript" src="js/framework7.min.js"></script>
    <!-- Path to your app js-->
    <script type="text/javascript" src="js/my-app.js"></script>
    <script type="text/javascript">
      function checkOCSP(url) {

      	myApp.showPreloader("Checking");
        $.ajax({
          url: 'checkOCSP.php',
          type: 'POST',
          data: {url: url},
          success: function(data) {
          	myApp.hidePreloader();
            var json = data;
            $("#ipaView").show();
            $("#certName").html(json.certificate_name);
            $("#ipaStatus").html(json.certificate_status);
            $("#service").html(json.certificate_service);
            $("#ipaHeader").html(json.certificate_ipa);
            // $("#ipaIcon").attr('src', json.ceritficate_icon);
            $("#ipaHeader").css('background-image', 'url("'+json.ceritficate_icon+'")');
          }
        });
        
      }
    </script>
  </body>
</html>