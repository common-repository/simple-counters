(function($){
  var
    userData = {
      id: '',
      name: '',
      fullName: '',
      pic: '',
      link: '',
      friends: '',
      fans: ''
    },
    shOpts = {direction:'vertical'};

  $(document).ready(function(){
    var
      $clearId = $('#clear_id'),
      $divUpdated = $('#sc_updated'),
      $divWelcome = $('#sc_welcome'),
      $userId = $('#fb2UserId'),
      $fullName = $("#fb2FullName"),
      $userName = $("#fb2UserName"),
      $userIdSpan = $('#fb2UserId_name'),
      $divFullName = $('#sc_fullname'),
      $avatar = $('#fbAvatar'),
      $fbLink = $('#fb2Link'),
      $friends = $('#fbCount'),
      $fans = $('#fb2Count'),
      $pageSelect = $('#fb2Page'),
      pageVal = $('#fb2PageVal').val();

    function login() {
      FB.login(function(response) {
        if (response.authResponse) {
          // connected
          userData.id = response.authResponse.userID;
          FB.api({
            method: 'fql.query',
            query: 'SELECT uid, name, username, friend_count, pic_square, profile_url FROM user WHERE uid = ' + response.authResponse.userID
          }, function(response) {
            userData.name = response[0].username;
            userData.fullName = response[0].name;
            userData.pic = response[0].pic_squre;
            userData.link = response[0].profile_url;
            userData.friends = response[0].friend_count;
            setData(userData, 1);
          });
          var pageQuery = 'SELECT page_id, name, website, fan_count FROM page WHERE page_id IN (SELECT page_id, type from page_admin WHERE uid = ' + response.authResponse.userID  + ' AND type = "WEBSITE")';
          FB.api({
            method: 'fql.query',
            query: pageQuery
          }, function(response) {
            setOptions(response, true);
          });
        } else {
          // cancelled
        }
      }, {scope: 'manage_pages'});
    }

    function setData(data, dt) {
      if(1 == dt) {
        if(scAdminOpts.userId == "") {
          $userId.val(data.id);
          $userName.val(data.name);
          $fullName.val(data.fullName);
          $userIdSpan.text('(' + data.name + ')');
          $divFullName.text(data.fullName);
          $avatar.val(data.pic);
          $fbLink.val(data.link);
          $friends.val(data.friends);
          $divUpdated.show('blind', shOpts, 500);
          $clearId.prop('disabled', false);
        }
      }
      else if(2 == dt) $fans.val(data.fans);
    }

    function setOptions(options, first) {
      var
        aDomain = window.location.hostname.split('.'),
        domain = aDomain[aDomain.length - 2] + '.' + aDomain[aDomain.length - 1];
        page = pageVal;
      $.each(options, function(idx, option) {
        $pageSelect.append('<option value="' + option.page_id + '">' + option.name + '</option>');
        if(first) {
          if(option.website.indexOf(domain) >= 0) page = option.page_id;
        }
      });
      $pageSelect.val(page);
    }

    $clearId.click(function() {
      $userId.val("");
      $userName.val("");
      $fullName.val("");
      $userIdSpan.text('');
      $divFullName.text("");
      $avatar.val("");
      $fbLink.val("");
      $clearId.prop('disabled', true);
      if ($divWelcome.is(':visible')) $divWelcome.hide('blind', shOpts, 500);
    });

    // Additional JS functions here
    if(('' != scAdminOpts.id) && scAdminOpts.as) {
      var ud = $userId.val();
      if('' == ud) {
        $clearId.prop('disabled', true);

        FB.init({
          appId      : scAdminOpts.id, // App ID
          channelUrl : scAdminOpts.channel, // Channel File
          status     : true, // check login status
          cookie     : true, // enable cookies to allow the server to access the session
          xfbml      : true  // parse XFBML
        });

        // Additional init code here
        FB.getLoginStatus(function(response) {
          if (response.status === 'connected') {
            // connected
            userData.id = response.authResponse.userID;
            /*FB.api('/me', {fields: "id,name,username,picture,link"}, function(response) {
              userData.name = response.username;
              userData.fullName = response.name;
              userData.pic = response.picture;
              userData.link = response.link;
              setData(userData);
            });*/
            FB.api({
              method: 'fql.query',
              query: 'SELECT uid, name, username, friend_count, pic_square, profile_url FROM user WHERE uid = ' + response.authResponse.userID
            }, function(response) {
              userData.name = response[0].username;
              userData.fullName = response[0].name;
              userData.pic = response[0].pic_square;
              userData.link = response[0].profile_url;
              userData.friends = response[0].friend_count;
              setData(userData, 1);
            });
            var pageQuery = 'SELECT page_id, name, website, fan_count FROM page WHERE page_id IN (SELECT page_id, type from page_admin WHERE uid = ' + response.authResponse.userID  + ' AND type = "WEBSITE")';
            FB.api({
              method: 'fql.query',
              query: pageQuery
            }, function(response) {
              setOptions(response);
            });
          } else if (response.status === 'not_authorized') {
            // not_authorized
            login();
          } else {
            // not_logged_in
            login();
          }
        });
      } else {
        $divWelcome.show('blind', shOpts, 500);

        FB.init({
          appId      : scAdminOpts.id, // App ID
          channelUrl : scAdminOpts.channel, // Channel File
          status     : true, // check login status
          cookie     : true, // enable cookies to allow the server to access the session
          xfbml      : true  // parse XFBML
        });

        FB.getLoginStatus(function(response) {
          if (response.status === 'connected') {
            // connected
            userData.id = response.authResponse.userID;
            var pageQuery = 'SELECT page_id, name, fan_count FROM page WHERE page_id IN (SELECT page_id, type from page_admin WHERE uid = ' + response.authResponse.userID  + ' AND type = "WEBSITE")';
            FB.api({
              method: 'fql.query',
              query: pageQuery
            }, function(response) {
              setOptions(response, false);
            });
          } else if (response.status === 'not_authorized') {
            // not_authorized
            login();
          } else {
            // not_logged_in
            login();
          }
        });
      }
    }
  });
})(jQuery);