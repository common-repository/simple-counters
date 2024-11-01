<?php
if(!class_exists('SimpleCounters')) {
  class SimpleCounters {
    public $settings;
    public $adminOptionsName = "SimpleCountersAdminOptions";
    public $scInitOptions = array(
      // Id options
      'fbId'            => '',
      'fb2AppId' => '',
      'fb2Secret' => '',
      'fb2Access' => '',
      'fb2UserId' => '',
      'fb2UserName' => '',
      'fb2FullName' => '',
      'fbLink' => '',
      'fb2Link' => '',
      'fb2Mode' => 'friends',
      'fb2Page' => '',
      'twId'            => '',
      'fontColor'        => 'ffffff',
      'color'           => '1c82d0',
      'colorTo'         => '145181',
      'borderColor'     => '676767',
      'borderStyle'     => 'win7',
      'position'        => 'right',
      'delta'           => '0',
      'gradient'        => 'vertical',
      'fbImg'           => '',
      'twImg'           => '',
      'fbHint'          => '',
      'fb2Hint'         => '',
      'twHint'          => '',
      'fbCount'          => '',
      'fb2Count'         => '',
      'twCount'          => '',
      'hints'            => 'themed',
      'fbHintImg'        => '',
      'fbHintImgSource'  => 'user',
      'fbAvatar'         => '',
      'fb2Avatar'        => '',
      'twHintImg'        => '',
      'twHintImgSource'  => 'user',
      'twAvatar'        => '',
      'hintTheme'        => 'custom',
      'hintFontColor'    => 'ffffff',
      'hintFontSize'    => '12',
      'hintColor'        => '1c82d0',
      'hintBorderColor' => '145181',
      'requestTimeOut'  => '10',
      'requestPeriod'    => 'twicedaily', // hourly|twicedaily|daily
      'lastUpdated'      => '',
      'fbErrorCode'      => '',
      'twErrorCode'      => '',
      'errorAction'      => 'previous',
      'templBold'        => 'true',
      'templItalic'      => 'false',
      'fbSubData'        => 'default', // default|xml
      'fbStr'           => 'friends',
      'twStr'           => 'followers',
      'localizing'      => 'true',
      'version'          => '2.0.23',
      'fb2ApiData' => ''
    );
    
    public function __construct() {
      define('SC_PATH', dirname( SC_MAIN_FILE ));
      define('SC_DOMAIN', 'simple-counters');
      define('SC_OPTIONS_NAME', "SimpleCountersAdminOptions");
      define('SC_VERSION', '2.0.24');
      define('SC_URL', WP_PLUGIN_URL . '/' . str_replace( basename( __FILE__), "", plugin_basename( __FILE__ ) ));
      define('SC_IMG_URL', SC_URL.'images/');
      
      //$plugin_dir = basename(dirname(__FILE__));
      if (function_exists( 'load_plugin_textdomain' ))
        load_plugin_textdomain( SC_DOMAIN, false, basename( SC_PATH ) );

      $scOptions = $this->getOptions();

      register_activation_hook(SC_MAIN_FILE, array(&$this, 'onActivate'));
      register_deactivation_hook(SC_MAIN_FILE, array(&$this, 'onDeactivate'));

      add_action('init', array(&$this, 'registerRewriteRule'));
      add_action('query_vars', array(&$this, 'filterQueryVars'));
      add_action('template_redirect', array(&$this, 'handleChannelFile'));
      add_action('admin_init', array(&$this, 'flushRewriteRules'));

      if(($scOptions['fb2AppId'] !== '') && ($scOptions['twId'] !== ''))
        add_action('wp_enqueue_scripts', array(&$this, 'headerScripts'));
      add_action('getCountersEvent', array(&$this, 'getCounters'));

      $this->settings = $scOptions;
    }
    
    public function onActivate() {
      $options = $this->getOptions();
      update_option($this->adminOptionsName, $options);
      wp_schedule_event(time(), $options['requestPeriod'], 'getCountersEvent');
    }
    
    public function onDeactivate() {
      wp_clear_scheduled_hook('getCountersEvent');
      delete_option(SC_OPTIONS_NAME);
    }
    
    public function getEngineVersion() {
      global $wp_version;
      $version = array();
      
      $ver = explode('.', $wp_version);
      $version['major'] = $ver[0];
      $vc = count($ver);
      if($vc == 2) {
        $subver = explode('-', $ver[1]);
        $version['minor'] = $subver[0];
        $version['spec'] = $subver[1];
        $version['str'] = $version['major'].'.'.$version['minor'].((!empty($version['spec'])) ? ' ('.$version['spec'].')' : '');
      }
      else {
        $version['minor'] = $ver[1];
        $version['build'] = $ver[2];
        $version['str'] = $wp_version;
      }
      
      return $version;
    }
    
    public function getOptions() {
      $options = $this->scInitOptions;
      $scOptions = get_option($this->adminOptionsName);
      if (!empty($scOptions)) {
        foreach ($scOptions as $key => $option) {
          $options[$key] = $option;
        }
      }      
      if($options['fbImg'] === '') $options['fbImg'] = WP_PLUGIN_URL.'/simple-counters/images/facebook.png';
      if($options['twImg'] === '') $options['twImg'] = WP_PLUGIN_URL.'/simple-counters/images/twitter.png';
      if($options['fbHintImg'] === '') $options['fbHintImg'] = WP_PLUGIN_URL.'/simple-counters/images/facebook-hint.png';
      if($options['twHintImg'] === '') $options['twHintImg'] = WP_PLUGIN_URL.'/simple-counters/images/twitter-hint.png';
      if($options['fbHint'] === '')
        $options['fbHint'] = __('Be my friend', SC_DOMAIN).' '.__('on Facebook to receive breaking news as well as receive other site updates!', SC_DOMAIN);
      if($options['fb2Hint'] === '')
        $options['fb2Hint'] = __('Be fan of', SC_DOMAIN).' [blog_name] '.__('on Facebook to receive breaking news as well as receive other site updates!', SC_DOMAIN);
      if($options['twHint'] === '')
        $options['twHint'] = __('Share and discover what is happening right now, at the', SC_DOMAIN).' [blog_name]!<br/>'.__('Follow Me on Twitter!', SC_DOMAIN);
      if($options['version'] != SC_VERSION) {
        $version = explode('.', $options['version']);
        if((int)$version[0] < 2) {
          if(stristr($options['fbImg'], 'feedburner'))
            $options['fbImg'] = WP_PLUGIN_URL.'/simple-counters/images/facebook.png';
          if(stristr($options['fbHintImg'], 'feedburner'))
            $options['fbHintImg'] = WP_PLUGIN_URL.'/simple-counters/images/facebook-hint.png';
          $options['fb2Hint'] = __('Be fan of', SC_DOMAIN).' [blog_name] '.__('on Facebook to receive breaking news as well as receive other site updates!', SC_DOMAIN);
          $options['fbHint'] = __('Be my friend', SC_DOMAIN).' '.__('on Facebook to receive breaking news as well as receive other site updates!', SC_DOMAIN);
        }
        $options['version'] = SC_VERSION;
      }
      if($options['fbStr'] !== '') $options['fbStr'] = _n('friend', 'friends', $options['fbCount'], SC_DOMAIN);
      if($options['twStr'] !== '') $options['twStr'] = _n('follower', 'followers', $options['twCount'], SC_DOMAIN);
      return $options;
    }

    public function saveOptions( $options ) {
      update_option(SC_OPTIONS_NAME, $options);
      $this->settings = self::getOptions();
    }

    public function registerRewriteRule() {
      add_rewrite_rule( '^sc-channel-file/?', 'index.php?sc-channel-file=true', 'top' );
    }

    public function filterQueryVars( $query_vars ) {
      $query_vars[] = 'sc-channel-file';
      return $query_vars;
    }

    public function handleChannelFile() {
      if ( get_query_var( 'sc-channel-file' ) ) {
      	$locale = self::getLocale();
        $cache_expire = 60 * 60 * 24 * 365;
      	header('Pragma: public');
      	header('Cache-Control: max-age='.$cache_expire);
      	header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cache_expire) . ' GMT');
      	echo "<script src='//connect.facebook.net/{$locale}/all.js'></script>";
      	die();
      }
    }

    public function getLocale() {
      $validLocales = array(
      	'ca_ES', 'cs_CZ', 'cy_GB', 'da_DK', 'de_DE', 'eu_ES', 'en_PI', 'en_UD', 'ck_US', 'en_US', 'es_LA', 'es_CL', 'es_CO', 'es_ES', 'es_MX',
      	'es_VE', 'fb_FI', 'fi_FI', 'fr_FR', 'gl_ES', 'hu_HU', 'it_IT', 'ja_JP', 'ko_KR', 'nb_NO', 'nn_NO', 'nl_NL', 'pl_PL', 'pt_BR', 'pt_PT',
      	'ro_RO', 'ru_RU', 'sk_SK', 'sl_SI', 'sv_SE', 'th_TH', 'tr_TR', 'ku_TR', 'zh_CN', 'zh_HK', 'zh_TW', 'fb_LT', 'af_ZA', 'sq_AL', 'hy_AM',
      	'az_AZ', 'be_BY', 'bn_IN', 'bs_BA', 'bg_BG', 'hr_HR', 'nl_BE', 'en_GB', 'eo_EO', 'et_EE', 'fo_FO', 'fr_CA', 'ka_GE', 'el_GR', 'gu_IN',
      	'hi_IN', 'is_IS', 'id_ID', 'ga_IE', 'jv_ID', 'kn_IN', 'kk_KZ', 'la_VA', 'lv_LV', 'li_NL', 'lt_LT', 'mk_MK', 'mg_MG', 'ms_MY', 'mt_MT',
      	'mr_IN', 'mn_MN', 'ne_NP', 'pa_IN', 'rm_CH', 'sa_IN', 'sr_RS', 'so_SO', 'sw_KE', 'tl_PH', 'ta_IN', 'tt_RU', 'te_IN', 'ml_IN', 'uk_UA',
      	'uz_UZ', 'vi_VN', 'xh_ZA', 'zu_ZA', 'km_KH', 'tg_TJ', 'ar_AR', 'he_IL', 'ur_PK', 'fa_IR', 'sy_SY', 'yi_DE', 'gn_PY', 'qu_PE', 'ay_BO',
      	'se_NO', 'ps_AF', 'tl_ST'
      );

      $locale = get_locale();
      if (strlen($locale) == 2) {
      	$locale = strtolower($locale).'_'.strtoupper($locale);
      }
      $locale = str_replace('-', '_', $locale);
      if ( !in_array($locale, $validLocales) ) {
      	$locale = 'en_US';
      }

      return $locale;
    }

    public function flushRewriteRules() {
      if ( !get_option( 'sc_flush_rewrite_rules' ) ) {
      	flush_rewrite_rules( false );
      	update_option( 'sc_flush_rewrite_rules', 1 );
      }
    }

    public function channelFileLink() {
      global $wp_rewrite;
      if ( $wp_rewrite->using_permalinks() )
      	echo home_url( '/sc-channel-file/' );
      else
      	echo add_query_arg( 'sc-channel-file', 'true', home_url() );
    }
    
    public function headerScripts() {
      global $is_IE;
      $options = $this->settings;
      $wv = $this->getEngineVersion();
      $wp33plus = false;
      if((int)$wv['major'] == 3 && (int)$wv['minor'] >= 3) $wp33plus = true;
      if((int)$wv['major'] > 3) $wp33plus = true;
      
      $options['fbHint'] = self::parseKeywords($options['fbHint']);
      $options['twHint'] = self::parseKeywords($options['twHint']);
      
      $style = 'ui-tooltip-' . $options['hintTheme'];
      
      if($options['localizing'] == 'true') {
        $fbStr = ($options['fb2Mode'] == 'friends') ?
          apply_filters( 'simple_counters_localization_facebook_friends', _n('friend', 'friends', $options['fbCount'], SC_DOMAIN), (int)$options['fbCount'] ) :
          apply_filters( 'simple_counters_localization_facebook_fans', _n('fan', 'fans', $options['fb2Count'], SC_DOMAIN), (int)$options['fb2Count'] );
        $twStr = apply_filters( 'simple_counters_localization_twitter', _n('follower', 'followers', $options['twCount'], SC_DOMAIN), (int)$options['twCount'] );
      }
      else {
        $fbStr = ((int)$options['fbCount'] > 1) ? 'friend' : 'friends';
        $twStr = ((int)$options['twCount'] > 1) ? 'followers' : 'follower';
      }

      $fbAvatar =  ($options['fb2Mode'] === 'friends') ? $options['fbAvatar'] : $options['fb2Avatar'];
      $fbHint = ($options['fb2Mode'] === 'friends') ? $this->parseKeywords( $options['fbHint'] ) : $this->parseKeywords( $options['fb2Hint'] );
      
      $scOptions = array(
        'position' =>  $options['position'],
        'delta' => $options['delta'],
        'fontColor' => '#' . $options['fontColor'],
        'color' => '#' . $options['color'],
        'colorTo' => '#' . $options['colorTo'],
        'borderColor' => '#' . $options['borderColor'],
        'borderStyle' => $options['borderStyle'],
        'dgv' =>  $options['gradient'],
        'hints' =>  $options['hints'],
        'facebook' => array(
          'id' =>  $options['fbId'],
          'imgUrl' =>  $options['fbImg'],
          'count' => ($options['fb2Mode'] == 'friends') ? $options['fbCount'] : $options['fb2Count'],
          'hint' =>  $fbHint,
          'hintImg' =>  ( (($options['fbHintImgSource'] === 'avatar') && ($options['fbAvatar'] !== '')) ? $fbAvatar : $options['fbHintImg'] ),
          'useAvatar' => (($options['fbHintImgSource'] === 'avatar') ? 'true' : 'false'),
          'link' => ($options['fb2Mode'] == 'friends') ? $options['fbLink'] : $options['fb2Link'],
          'str' => $fbStr
        ),
        'twitter' => array(
          'id' =>  $options['twId'],
          'imgUrl' =>  $options['twImg'],
          'count' =>  $options['twCount'],
          'hint' =>  $this->parseKeywords( $options['twHint'] ),
          'hintImg' =>  ( (($options['twHintImgSource'] === 'avatar') && ($options['twAvatar'] !== '')) ? $options['twAvatar'] : $options['twHintImg'] ),
          'useAvatar' => (($options['twHintImgSource'] === 'avatar') ? 'true' : 'false'),
          'str' => $twStr
        ),
        'hintStyle' => $style
      );

      wp_enqueue_style('qtip', WP_PLUGIN_URL.'/simple-counters/css/jquery.qtip.css', false, '2.0.0');
      if($options['hintTheme'] === 'custom') echo
        "<style>"."\n".
        ".ui-tooltip-custom .ui-tooltip-titlebar, .ui-tooltip-custom .ui-tooltip-content{"."\n".
        "  border-color: #".$options['hintBorderColor']." !important;"."\n".
        "  color: #".$options['hintFontColor']." !important;"."\n".
        "}"."\n\n".
        ".ui-tooltip-custom .ui-tooltip-content{"."\n".
        "   background-color: #".$options['hintColor']." !important;"."\n".
        "}"."\n\n".
        ".ui-tooltip-custom .ui-tooltip-titlebar{"."\n".
        "   background-color: #".$options['hintColor']." !important;"."\n".
        "}"."\n".
        "</style>"."\n";
            
      //wp_register_script('excanvas', WP_PLUGIN_URL.'/simple-counters/js/excanvas.js');
      wp_enqueue_script('jquery');
      wp_enqueue_script('qTip', WP_PLUGIN_URL.'/simple-counters/js/jquery.qtip.min.js', array('jquery'), '2.0.0');
      wp_enqueue_script('simpleCounters', WP_PLUGIN_URL.'/simple-counters/js/sc.min.js', array('jquery', 'qTip'), $this->scInitOptions['version']);
      if($wp33plus) wp_localize_script('simpleCounters', 'scOptions', $scOptions);
      else wp_localize_script('simpleCounters', 'scOptions', array('l10n_print_after' => 'scOptions = ' . json_encode($scOptions) . ';'));
    }

    public function getFacebookCount() {
      //include_once('tools/facebook.php');
      include_once('tools/facebook.php');
      $options = self::getOptions();

      $appId = $options['fb2AppId']; //'298877050128114';
      $appSecret = $options['fb2Secret']; //'0b6ac37cd5e3ca4480ba1f563b476bba';
      $appAccess = $options['fb2Access'];
      $userId = $options['fb2UserId']; //'konstantin.leonov';
      //$myUrl = admin_url('options-general.php?page=simple-counters');

      $pageId = $options['fb2Page'];
      $mode = $options['fb2Mode'];

      $fc = -1;
      $fs = -1;
      $fbPic = '';
      $fbLink = '';
      $pagePic = '';
      $pageLink = '';
      $error = '';

      if(!empty($appId) && !empty($appSecret)) {
        /*$code = $_REQUEST["code"];

        if(empty($code)) {
          $_SESSION['state'] = md5(uniqid(rand(), TRUE)); // CSRF protection
          $dialog_url = "https://www.facebook.com/dialog/oauth?client_id="
            . $appId . "&redirect_uri=" . urlencode($myUrl) . "&state="
            . $_SESSION['state'] . "&scope=user_birthday,read_stream";

          echo("<script> top.location.href='" . $dialog_url . "'</script>");
        }*/

        if(empty($appAccess)) {
          $params = array();
          $response = file_get_contents("https://graph.facebook.com/oauth/access_token?client_id={$appId}&client_secret={$appSecret}&grant_type=client_credentials");
          parse_str($response, $params);
          $appAccess = $params['access_token'];
          $options['fb2Access'] = $appAccess;
          self::saveOptions($options);
        }

        if(!empty($appAccess)) {
          $facebook = new Facebook( array(
            'appId' => $appId,
            'secret' => $appSecret
          ) );

          $facebook->setAccessToken($appAccess); //'298877050128114|a4S9rlbLtRznlekN8QdRCISwo8s'

          try {
            $fql = "SELECT friend_count, pic_square, profile_url FROM user WHERE uid = $userId";
            $fqlData = $facebook->api(array(
              'method' => 'fql.query',
              'query' => $fql,
            ));
            $fc = $fqlData[0]['friend_count'];
            $fbPic = $fqlData[0]['pic_square'];
            $fbLink = $fqlData[0]['profile_url'];
          }
          catch( FacebookApiException $e ) {
            //  error_log($e);
            $fc = -1;

            $fbPic = '';
            $fbLink = '';
            $error = $e->__toString();
          }

          if( !empty( $pageId ) ) {
            try {
              $pFql = "SELECT page_id, name, username, page_url, pic_square, website, fan_count FROM page WHERE page_id = $pageId";
              $page = $facebook->api( array(
                'method' => 'fql.query',
                'query' => $pFql
              ) );

              $fs = $page[0]['fan_count'];
              $pagePic = $page[0]['pic_square'];
              $pageLink = $page[0]['page_url'];
            }
            catch( FacebookApiException $e ) {
              $error = $e->__toString();
              $page = null;
            }
          }

          /*try {
            $sFQL = "SELECT subscriber_id FROM subscription WHERE subscribed_id = $userId";
            $fbData = $facebook->api(array(
              'method' => 'fql.query',
              'query' => $sFQL
            ));
            $fs = count($fbData['data']);
          }
          catch ( FacebookApiException $e ) {
            $fs = $e->__toString();
            $error = $e->getResult();
          }*/
        }
      }
      else $fc = '';

      return array(
        'friends' => $fc,
        'fans' => $fs,
        'error' => $error,
        'avatar' => $fbPic,
        'link' => $fbLink,
        'pageAvatar' => $pagePic,
        'pageLink' => $pageLink
      );
    }
    
    public function getFbCount() {
      $options = $this->settings;
      $fbUrl = 'http://feedburner.google.com/api/awareness/1.0/GetFeedData?uri=' . $options['fbId'];
      $circulation = 0;
      $timeout = intval($options['requestTimeOut']);
      $errorCode = '';
    
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
      curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.3) Gecko/2008092417 Firefox/3.0.3');
      curl_setopt($ch, CURLOPT_URL, $fbUrl);
      $xml = curl_exec($ch);
      $info = curl_getinfo($ch);
    
      curl_close($ch);
    
      $http_code = $info['http_code'];
      
      if ($http_code == 200) {
        $rsp = new SimpleXMLElement($xml);
        $circulation = $rsp->feed[0]->entry['circulation'];
        settype($circulation, 'string');
      }
      else {
        switch($options['errorAction']){
          case 'error':
            $circulation = 'E:' . $http_code;
            break;
          
          case 'previous':
            $circulation = $options['fbCount'];
            break;
            
          case 'client':
            $circulation = '-1';
            break;
        }
        $errorCode = $http_code;
      }
            
      return array( 'circulation' => $circulation, 'errorCode' => $errorCode );
    }
    
    public function getTwCount() {
      $options = $this->settings;
      $twUrl = 'http://api.twitter.com/1/users/show.xml?screen_name=' . $options['twId'];
      $followers = 0;
      $timeout = intval($options['requestTimeOut']);
      $errorCode = '';
      $avatar = '';
      
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
      curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.3) Gecko/2008092417 Firefox/3.0.3');
      curl_setopt($ch, CURLOPT_URL, $twUrl);
      $xml = curl_exec($ch);
      $info = curl_getinfo($ch);
    
      curl_close($ch);
    
      $http_code = $info['http_code'];
      
      if ($http_code == 200) {
        $user = new SimpleXMLElement($xml);
        $followers = (string) $user->followers_count;
        $avatar = (string) $user->profile_image_url;
      }
      else {
        switch ($options['errorAction']) {
          case 'error':
            $followers = 'E:' . $http_code;
            break;
            
          case 'previous':
            $followers = $options['twCount'];
            break;
            
          case 'client':
            $followers = '-1';
            break;
        }
        $errorCode = $http_code;
        $avatar = '';        
      }
      
      return array( 'followers' => $followers, 'errorCode' => $errorCode, 'avatar' => $avatar );
    }
    
    public function mustSet($value, $option) {
      $result = TRUE;
      if( $value === '' ) $result = FALSE;
      elseif( $value === $option ) $result = FALSE;
      elseif(( $value === '0' ) && ( intval($option) > 0 )) $result = FALSE;
      return $result;
    }
    
    public function getCounters( $args = null ) {
      /*$options = array();*/
      $force = true;
      /*if(is_array($args)) {
        if(!empty($args['force'])) $force = $args['force'];
        else $force = true;
        if(!empty($args['settings']) && is_array($args['settings'])) $options = $args['settings'];
        else $options = $this->settings;
      }
      else {
        $force = true;*/
        $options = $this->settings;
      //}
      if(($options['fb2AppId'] !== '') && ($options['fb2Secret'] !== '') && ($options['twId'] !== '')) {
        $facebook = $this->getFacebookCount();
        $twitter = $this->getTwCount();
        if(self::mustSet($facebook['friends'], $options['fbCount']) || self::mustSet($facebook['fans'], $options['fb2Count'])) {
          $options['fbCount'] = $facebook['friends'];
          $options['fb2Count'] = $facebook['fans'];
          $options['fbErrorCode'] = $facebook['error'];
        }
        if($this->mustSet($twitter['followers'], $options['twCount'])) {
          $options['twCount'] = $twitter['followers'];
          $options['twErrorCode'] = $twitter['errorCode'];
        }
        $options['fbAvatar'] = $facebook['avatar'];
        $options['fb2Avatar'] = $facebook['pageAvatar'];
        $options['fbLink'] = $facebook['link'];
        $options['fb2Link'] = $facebook['pageLink'];
        $options['twAvatar'] = $twitter['avatar'];
        $options['lastUpdated'] = time();
        if($options['fbStr'] !== '') $options['fbStr'] = ($options['fb2Mode'] == 'friends') ? _n('friend', 'friends', $options['fbCount'], SC_DOMAIN) : _n('fan', 'fans', $options['fb2Count'], SC_DOMAIN);
        if($options['fbStr'] !== '') $options['twStr'] = _n('follower', 'followers', $options['twCount'], SC_DOMAIN);
        if($force) update_option(SC_OPTIONS_NAME, $options);
        return $options;
      }
    }
    
    public function parseKeywords($text) {
      $scOptions = $this->settings;
      $style = array('bold' => ($scOptions['templBold'] === 'true'), 'italic' => ($scOptions['templItalic'] === 'true'));
      $blogName = get_bloginfo('name');
      $twNick = $scOptions['twId'];
      $fullName = $scOptions['fb2FullName'];
      $names = explode(' ', $fullName);
      $name = $names[0];
      
      if($style['italic']) {
        $blogName = '<em>'.$blogName.'</em>';
        $twNick = '<em>'.$twNick.'</em>';
      }
      if($style['bold']) {
        $blogName = '<strong>'.$blogName.'</strong>';
        $twNick = '<strong>'.$twNick.'</strong>';
      }
      
      $text = str_replace('[blog_name]', $blogName, $text);
      $text = str_replace('[twitter_nick]', $twNick, $text);
      $text = str_replace('[fb_fullname]', $fullName, $text);
      $text = str_replace('[fb_name]', $name, $text);
      
      return $text;
    }    
  } // End of class
} //End of If
?>
