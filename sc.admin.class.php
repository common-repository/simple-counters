<?php
if(class_exists('SimpleCounters') && !class_exists('SimpleCountersAdmin')) {
  class SimpleCountersAdmin extends SimpleCounters {
    public $settingsPage;
    public $welcome;
    public $updated;

    function __construct() {
      parent::__construct();
        
      add_action('admin_init', array(&$this, 'initSettings'));
      add_action('admin_menu', array(&$this, 'onAdminPage'));
      $this->welcome = sprintf( __('Welcome, <strong>%s</strong>! All Facebook settings are ok.', SC_DOMAIN), $this->settings['fb2FullName']);
      $this->updated =
        __('Hi', SC_DOMAIN).
        ", <span id='sc_fullname'></span>! ".
        __("If it is your name, just save plugin's options again to finish process of Facebook setup. If something goes wrong, leave this page without saving, exit your Facebook account and visit this page again to finish setup process.", SC_DOMAIN);

    }

    private function getList( $name, $count ) {
      for( $i = 0; $i < $count; $i++ ) {
        if( $i == 0 ) $fileName = $name.'.png';
        else $fileName = $name.'-'.$i.'.png';
        echo "<option>".SC_IMG_URL.$fileName."</option>";
      }
    }
    
    function doInfo($data, $value = -2, $value2 = null) {
      $options = $this->settings;
      if($options['fbCount'] == '' || $options['twCount'] == '') {
        parent::getCounters();
        $options = $this->settings;
      }
      $output = '';
      switch($data) {
        case 'facebook':
          if($value == -2) {
            $value = $options['fbCount'];
            $value2 = $options['fb2Count'];
          }
          $values = ( !empty( $options['fb2Count'] ) ) ? $value.' / '.$value2 : $value;
          $output = '<strong>'.__('Facebook', SC_DOMAIN).'</strong>: '."$values".'<span style="color:red">'.(($options['fbErrorCode'] !== '') ? '<br/>'.__('Error Code', SC_DOMAIN).': '.$options['fbErrorCode'].(($value === '-1') ? '. '.__('Data request to Facebook server will be run on client side.', SC_DOMAIN) : '') : '').'</span>';
          break;
          
        case 'twitter':
          if($value == -2) $value = $options['twCount'];
          $output = '<strong>'.__('Twitter', SC_DOMAIN).'</strong>: '.$value.'<span style="color:red">'.(($options['twErrorCode'] !== '') ? '<br/>'.__('Error Code', SC_DOMAIN).': '.$options['twErrorCode'].(($value === '-1') ? '. '.__('Data request to Twitter server will be run on client side.', SC_DOMAIN) : '') : '').'</span>';
          break;
      }
      
      return $output;
    }
    
    function adminHeaderScripts( $hook ) {
      if( $this->settingsPage == $hook ) {
        $options = $this->settings;
        $appId = $options['fb2AppId'];
        $userId = $options['fb2UserId'];
        $appSecret = $options['fb2Secret'];
        $channel = add_query_arg( 'sc-channel-file', 1, site_url( '/' ) );
        $locale = parent::getLocale();
        $connect = "//connect.facebook.net/$locale/all.js";

        $opts = array(
          'id' => $appId,
          'as' => ( '' !== $appSecret ),
          'channel' => $channel,
          'connect' => $connect,
          'userId' => $userId
        );

        wp_register_style('ColorPickerCSS', WP_PLUGIN_URL.'/simple-counters/css/colorpicker.css');
        wp_enqueue_style('ColorPickerCSS', WP_PLUGIN_URL.'/simple-counters/css/colorpicker.css', false, '1.0');
        wp_enqueue_style('jquery-ui-tabs', WP_PLUGIN_URL.'/simple-counters/css/jquery-ui-1.8.16.custom.css', false, '1.8.16');
        wp_enqueue_style('qtip', WP_PLUGIN_URL.'/simple-counters/css/jquery.qtip.css', false, '2.0.0');
        wp_enqueue_style('scLayout', SC_URL.'css/admin.layout.css', false, SC_VERSION);

        wp_enqueue_script('FacebookSDK', 'https://connect.facebook.net/en_US/all.js');
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('jquery-effects-core');
        wp_enqueue_script('jquery-effects-blind');
        wp_enqueue_script('ColorPicker', WP_PLUGIN_URL.'/simple-counters/js/colorpicker.js', array('jquery'), '1.0');
        wp_enqueue_script('qTip', WP_PLUGIN_URL.'/simple-counters/js/jquery.qtip.min.js', array('jquery'), '2.0.0');
        wp_enqueue_script('AdminLayout', WP_PLUGIN_URL.'/simple-counters/js/sc.admin.js', array('jquery', 'jquery-ui-core', 'jquery-ui-tabs', 'ColorPicker'), SC_VERSION);
        wp_enqueue_script('fbscConnect', WP_PLUGIN_URL.'/simple-counters/js/fb-sc.js', array('FacebookSDK'), SC_VERSION, true);
        wp_localize_script('fbscConnect' ,'scAdminOpts', $opts);
      }
    }
    
    function initSettings() {
      add_settings_section('sc_account_section', __('Account Settings', SC_DOMAIN), array(&$this, 'drawAccountSection'), 'simple-counters');
      //add_settings_section('sc_output_section', __('Output Data Settings', SC_DOMAIN), array(&$this, 'drawOutputSection'), 'simple-counters');
      add_settings_section('sc_tech_section', __('Technical Settings', SC_DOMAIN), array(&$this, 'drawTechSection'), 'simple-counters');
      add_settings_section('sc_badge_section', __('Badge Settings', SC_DOMAIN), array(&$this, 'drawBadgeSection'), 'simple-counters');
      add_settings_section('sc_hints_section', __('ToolTips Settings', SC_DOMAIN), array(&$this, 'drawHintsSection'), 'simple-counters');
      
      //add_settings_field('fbId', __("Define your FeedBurner ID", SC_DOMAIN), array(&$this, 'drawTextOption'), 'simple-counters', 'sc_account_section', array('width' => '250px', 'description' => '<span id="feed-uri">'.__("This is the URI of the feed (same as <strong>http://feedproxy.google.com/[feeduri]</strong>). Enter only <strong>[feeduri]</strong>.", SC_DOMAIN).'</span><br/><span id="awareness">'.__('You must enable <strong>Awareness API</strong> in your FeedBurner account (Publicize tab).', SC_DOMAIN).'</span>'));
      add_settings_field('fb2AppId', __("Facebook App ID", SC_DOMAIN), array(&$this, 'drawTextOption'), 'simple-counters', 'sc_account_section', array('width' => '250px', 'description' => __("This is App Id of your Facebook application.", SC_DOMAIN).' '.__('For more details see help (tab in right top corner of this screen).', SC_DOMAIN)));
      add_settings_field('fb2Secret', __("Facebook App Secret", SC_DOMAIN), array(&$this, 'drawTextOption'), 'simple-counters', 'sc_account_section', array('width' => '250px', 'description' => __("This is App Secret of your Facebook application.", SC_DOMAIN).' '.__('For more details see help (tab in right top corner of this screen).', SC_DOMAIN)));
      add_settings_field('fb2UserId', __("Facebook User ID", SC_DOMAIN), array(&$this, 'drawTextOptionX'), 'simple-counters', 'sc_account_section', array('width' => '250px', 'description' => __("This is your Facebook User ID.", SC_DOMAIN).' '.__('For more details see help (tab in right top corner of this screen).', SC_DOMAIN), 'hidden' => 'fb2UserName', 'hidden2' => 'fb2FullName', 'line' => true, 'readonly' => true, 'button' => true));
      add_settings_field('twId', __("Define your Twitter ID", SC_DOMAIN), array(&$this, 'drawTextOption'), 'simple-counters', 'sc_account_section', array('width' => '250px', 'description' => __("This is your nick name on Twitter.", SC_DOMAIN), 'line' => true));
      add_settings_field('pluginInfo', __('Your counters values', SC_DOMAIN), array(&$this, 'drawInfo'), 'simple-counters', 'sc_account_section', array("options" => array('facebook', 'twitter'), 'description' => __('It is result of the last data requests by a server from Facebook and Twitter.', SC_DOMAIN)));
      
      //add_settings_field('fbSubData', __('Define FeedBurner Output Page Data Format', SC_DOMAIN), array(&$this, 'drawRadioOption'), 'simple-counters', 'sc_output_section', array("options" => array( 'default' => __("Default", SC_DOMAIN), 'xml' => __("RSS XML", SC_DOMAIN)), 'style' => 'vertical', 'description' => __('Selecting "Default" will set output page data format to default FeedBurner Subscription page, selecting "XML" will set output page data format to RSS XML Data Format. In other words, if "Default" when user clicks FeedBurner icon he opens Default FeedBurner Subscription Page, in other case XML RSS Feed.', SC_DOMAIN)));
      
      add_settings_field('requestPeriod', __('Define requests period', SC_DOMAIN), array(&$this, 'drawRadioOption'), 'simple-counters', 'sc_tech_section', array('style' => 'vertical', "options" => array( 'hourly' => __("Hourly", SC_DOMAIN), 'twicedaily' => __("Twice Daily", SC_DOMAIN), 'daily' => __("Daily", SC_DOMAIN)), 'description' => __('Select requests period for updating Facebook and Twitter counters.', SC_DOMAIN)));
      add_settings_field('requestTimeOut', __("Define request timeout", SC_DOMAIN), array(&$this, 'drawTextOption'), 'simple-counters', 'sc_tech_section', array('description' => __("This is requests timeout in seconds. In case you have 'Gateway Time-out' request error you can grow this value.", SC_DOMAIN).' '.__('Only for Twitter!', SC_DOMAIN)));
      add_settings_field('errorAction', __('Define action at an answer error', SC_DOMAIN), array(&$this, 'drawRadioOption'), 'simple-counters', 'sc_tech_section', array('style' => 'vertical', "options" => array( 'error' => __("Error code output (for debug purposes only)", SC_DOMAIN), 'previous' => __("Data output of the last successful request of the data", SC_DOMAIN), 'client' => __("Redirect data request to client side (for Twitter only)", SC_DOMAIN)), 'description' => __('Define what to do in case a response error happened.', SC_DOMAIN)));
      
      add_settings_field('fontColor', __("Define font color", SC_DOMAIN), array(&$this, 'drawTextOption'), 'simple-counters', 'sc_badge_section', array('description' => __("This is font color of badge (six hex digits).", SC_DOMAIN)));
      add_settings_field('borderColor', __("Define border color", SC_DOMAIN), array(&$this, 'drawTextOption'), 'simple-counters', 'sc_badge_section', array('description' => __("This is semitransparent (40%) color of border (six hex digits).", SC_DOMAIN)));
      add_settings_field('borderStyle', __("Define border style", SC_DOMAIN), array(&$this, 'drawRadioOption'), 'simple-counters', 'sc_badge_section', array('options' => array('default' => __('Default', SC_DOMAIN), 'win7' => __('WindowZ Aero', SC_DOMAIN)), 'labelId' => true, 'description' => __("Select border style.", SC_DOMAIN)));
      add_settings_field('color', __("Define start gradient color", SC_DOMAIN), array(&$this, 'drawTextOption'), 'simple-counters', 'sc_badge_section', array('description' => __("This is start color of background gradient (six hex digits).", SC_DOMAIN)));
      add_settings_field('colorTo', __("Define stop gradient color", SC_DOMAIN), array(&$this, 'drawTextOption'), 'simple-counters', 'sc_badge_section', array('description' => __("This is stop color of background gradient (six hex digits).", SC_DOMAIN)));
      add_settings_field('gradient', __('Define gradient vector direction', SC_DOMAIN), array(&$this, 'drawRadioOption'), 'simple-counters', 'sc_badge_section', array('options' => array( 'vertical' => __("Vertical", SC_DOMAIN), 'horizontal' => __("Horizontal", SC_DOMAIN)), 'description' => __('Selecting "Vertical" will allow drawing gradient from top (start color) to bottom (stop color), "Horizontal" - from left side (start color) to right side (stop color).', SC_DOMAIN)));
      add_settings_field('fbImg', __("Define image for Facebook counter", SC_DOMAIN), array(&$this, 'drawTextOption'), 'simple-counters', 'sc_badge_section', array('width' => '100%', 'description' => __("This is image for Facebook badge button (Full URL). 50x50 pixels, transparent background PNG image recommended.", SC_DOMAIN), 'list' => array('name' => 'facebook', 'count' => 8)));
      add_settings_field('fb2Mode', __('Define Facebook counter data', SC_DOMAIN), array(&$this, 'drawRadioOption'), 'simple-counters', 'sc_badge_section', array('options' => array('friends' => __('Friends', SC_DOMAIN), 'fans' => __('Site Page Fans', SC_DOMAIN)), 'description' => __('You can show count of your friends on Facebook or fans of your site page on Facebook. More details in help.', SC_DOMAIN)));
      add_settings_field('fb2Page', __("Select Site's Page on Facebook", SC_DOMAIN), array(&$this, 'drawPageOption'), 'simple-counters', 'sc_badge_section', array('description' => __('Choose proper page from available pages of your sites on Facebook or select "None".', SC_DOMAIN)));
      add_settings_field('twImg', __("Define image for Twitter counter", SC_DOMAIN), array(&$this, 'drawTextOption'), 'simple-counters', 'sc_badge_section', array('width' => '100%', 'description' => __("This is image for Twitter badge button (Full URL). 50x50 pixels, transparent background PNG image recommended.", SC_DOMAIN), 'list' => array('name' => 'twitter', 'count' => 4)));
      add_settings_field('position', __('Define badge position', SC_DOMAIN), array(&$this, 'drawRadioOption'), 'simple-counters', 'sc_badge_section', array("options" => array( 'right' => __("Right", SC_DOMAIN), 'left' => __("Left", SC_DOMAIN)), 'description' => __('Selecting "Right" will set badge near the right side of browser window, "Left" near the left side.', SC_DOMAIN)));
      add_settings_field('delta', __("Define badge position displacement", SC_DOMAIN), array(&$this, 'drawTextOption'), 'simple-counters', 'sc_badge_section', array('description' => __("By default, badge is placed on the middle line of browser window. You can displace badge up (negative value) or down (positive value) relatively middle line by setting this value.", SC_DOMAIN)));
      add_settings_field('localizing', __('Localization of badge strings', SC_DOMAIN), array(&$this, 'drawRadioOption'), 'simple-counters', 'sc_badge_section', array('options' => array('true' => __('Localize', SC_DOMAIN), 'false' => __('Do not localize', SC_DOMAIN)), 'description' => __("If you don't want to display localized version of words \"reader(s)\" and \"friend(s)\", select \"Do not localize\". Also you can use filters <code>simple_counters_localization_facebook</code> and <code>simple_counters_localization_twitter</code> for defining your own localized versions of these words.", SC_DOMAIN)));
      
      add_settings_field('hintTheme', __("Select Theme", SC_DOMAIN), array(&$this, 'drawSelectOption'), 'simple-counters', 'sc_hints_section', array('options' => array( 'cream' => __('Cream', SC_DOMAIN), 'dark' => __('Dark', SC_DOMAIN), 'green' => __('Green', SC_DOMAIN), 'light' => __('Light', SC_DOMAIN), 'red' => __('Red', SC_DOMAIN), 'blue' => __('Blue', SC_DOMAIN), 'custom' => __('Custom (See below)', SC_DOMAIN)), 'description' => __("Color theme selecting.", SC_DOMAIN)));
      add_settings_field('hintFontColor', __("Define tooltip font color", SC_DOMAIN), array(&$this, 'drawTextOption'), 'simple-counters', 'sc_hints_section', array('description' => __("This is font color of tooltip (six hex digits).", SC_DOMAIN).'<br/>'.__('This parameter is useful only for tooltip custom theme.', SC_DOMAIN)));
      //add_settings_field('hintFontSize', __("Define tooltip font size", SC_DOMAIN), array(&$this, 'drawTextOption'), 'simple-counters', 'sc_hints_section', array('description' => __("This is font size of tooltip.", SC_DOMAIN)));
      add_settings_field('hintColor', __("Define tooltip background color", SC_DOMAIN), array(&$this, 'drawTextOption'), 'simple-counters', 'sc_hints_section', array('description' => __("This is background color of tooltip (six hex digits).", SC_DOMAIN).'<br/>'.__('This parameter is useful only for tooltip custom theme.', SC_DOMAIN)));
      add_settings_field('hintBorderColor', __("Define tooltip border color", SC_DOMAIN), array(&$this, 'drawTextOption'), 'simple-counters', 'sc_hints_section', array('description' => __("This is color of tooltip border (six hex digits).", SC_DOMAIN).'<br/>'.__('This parameter is useful only for tooltip custom theme.', SC_DOMAIN)));
      add_settings_field('templStyle', __("Define template-word style", SC_DOMAIN), array(&$this, 'drawCheckGroupOption'), 'simple-counters', 'sc_hints_section', array("options" => array('templBold' => __('bold', SC_DOMAIN), 'templItalic' => __('italic', SC_DOMAIN)), 'line' => true, 'description' => __("Select template-words representation style for displaying in the hints.", SC_DOMAIN)));
      add_settings_field('fbHint', __("Define text for Facebook counter tooltip (friends)", SC_DOMAIN), array(&$this, 'drawTextOption'), 'simple-counters', 'sc_hints_section', array('width' => '100%', 'description' => __("This is text for Facebook tooltip (friends display mode).", SC_DOMAIN).'<br/>'.__("You can use template-word <strong>[blog_name]</strong> to display name of your blog, <strong>[fb_fullname]</strong> to display your Full Name on Facebook, <strong>[fb_name]</strong> to display your First Name on Facebook or <strong>[twitter_nick]</strong> to display your Twitter Nickname.", SC_DOMAIN)));
      add_settings_field('fb2Hint', __("Define text for Facebook counter tooltip (site page fans)", SC_DOMAIN), array(&$this, 'drawTextOption'), 'simple-counters', 'sc_hints_section', array('width' => '100%', 'description' => __("This is text for Facebook tooltip (fan display mode).", SC_DOMAIN).'<br/>'.__("You can use template-word <strong>[blog_name]</strong> to display name of your blog, <strong>[fb_fullname]</strong> to display your Full Name on Facebook, <strong>[fb_name]</strong> to display your First Name on Facebook or <strong>[twitter_nick]</strong> to display your Twitter Nickname.", SC_DOMAIN)));
      add_settings_field('fbHintImg', __("Define image for Facebook counter tooltip", SC_DOMAIN), array(&$this, 'drawTextOption'), 'simple-counters', 'sc_hints_section', array('width' => '100%', 'description' => __("This is image for Facebook tooltip (Full URL). 40x40 pixels, transparent background PNG image recommended.", SC_DOMAIN), 'list' => array('name' => 'facebook-hint', 'count' => 3)));
      add_settings_field('fbHintImgSource', __('Define image source for Facebook counter tooltip', SC_DOMAIN), array(&$this, 'drawRadioOption'), 'simple-counters', 'sc_hints_section', array("options" => array( 'user' => __("User defined", SC_DOMAIN), 'avatar' => __("Facebook cover image", SC_DOMAIN)), 'line' => true, 'description' => __('Selecting "User defined" will set user defined image for Facebook counter tooltip, "Facebook cover image" will set your Facebook cover image (square profile picture) as image for Facebook counter tooltip.', SC_DOMAIN)));
      add_settings_field('twHint', __("Define text for Twitter counter tooltip", SC_DOMAIN), array(&$this, 'drawTextOption'), 'simple-counters', 'sc_hints_section', array('width' => '100%', 'description' => __("This is text for Twitter tooltip.", SC_DOMAIN).'<br/>'.__("You can use template-word <strong>[blog_name]</strong> to display name of your blog, <strong>[fb_fullname]</strong> to display your Full Name on Facebook, <strong>[fb_name]</strong> to display your First Name on Facebook or <strong>[twitter_nick]</strong> to display your Twitter Nickname.", SC_DOMAIN)));
      add_settings_field('twHintImg', __("Define image for Twitter counter tooltip", SC_DOMAIN), array(&$this, 'drawTextOption'), 'simple-counters', 'sc_hints_section', array('width' => '100%', 'description' => __("This is image for Twitter tooltip (Full URL). 40x40 pixels, transparent background PNG image recommended.", SC_DOMAIN), 'list' => array('name' => 'twitter-hint', 'count' => 1)));
      add_settings_field('twHintImgSource', __('Define image source for Twitter counter tooltip', SC_DOMAIN), array(&$this, 'drawRadioOption'), 'simple-counters', 'sc_hints_section', array("options" => array( 'user' => __("User defined", SC_DOMAIN), 'avatar' => __("Twitter avatar", SC_DOMAIN)), 'description' => __('Selecting "User defined" will set user defined image for Twitter counter tooltip, "Twitter avatar" will set your Twitter avatar as image for Twitter counter tooltip.', SC_DOMAIN)));
      
      register_setting('scOptions', SC_OPTIONS_NAME, array(&$this, 'sanitizeSettings'));
    }
    
    function sanitizeSettings($input) {
      $output = $input;
      if($input['requestPeriod'] !== $this->settings['requestPeriod']) {
        wp_clear_scheduled_hook('getCountersEvent');
        wp_schedule_event(time(), $input['requestPeriod'], 'getCountersEvent');
      }
      if($input['fb2Page'] == '0') $output['fb2Page'] = '';
      return $output;
    }
    
    function drawAccountSection() {
      echo '<p>'.__('You must define IDs for both accounts, Facebook and Twitter, for properly work of plugin.', SC_DOMAIN).'</p>';
    }
    
    function drawBadgeSection() {
      echo '<p>'.__('Use parameters below for customising Simple Counters badge style.', SC_DOMAIN).'</p>';
    }
    
    function drawOutputSection() {
      echo '<p>'.__('Use parameters below for customising output data format.', SC_DOMAIN).'</p>';
    }
    
    function drawHintsSection() {
      echo '<p>'.__('Use parameters below for customising Simple Counters tooltips.', SC_DOMAIN).'</p>';
    }
    
    function drawTechSection() {
      echo '<p>'.__('Use parameters below for customising technical parameters of Simple Counters.', SC_DOMAIN).'</p>';
    }
    
    public function drawSelectOption( $id, $args ) {
      $options = $args['options'];
      ?>
        <select id="<?php echo $id; ?>"
          name="<?php echo SC_OPTIONS_NAME.'['.$id.']'; ?>">
          <?php foreach($options as $key => $option) { ?>
            <option value="<?php echo $key; ?>" 
              <?php selected($key, $this->settings[$id]); ?> ><?php echo $option; ?>
            </option>
          <?php } ?>
        </select>
      <?php
    }
    
    public function drawRadioOption( $id, $args ) {
      $options = $args['options'];
      $labelId = $args['labelId'];
      foreach ($options as $key => $option) {
      ?>
        <input type="radio" 
          id="<?php echo $id.'_'.$key; ?>" 
          name="<?php echo SC_OPTIONS_NAME.'['.$id.']'; ?>" 
          value="<?php echo $key; ?>" <?php checked($key, $this->settings[$id]); ?> />
        <label <?php if($labelId) echo 'id="lb_'.$id.'_'.$key.'"'; ?> for="<?php echo $id.'_'.$key; ?>"> 
          <?php echo $option;?>
        </label>&nbsp;&nbsp;&nbsp;&nbsp;
      <?php
      if($args['style'] === 'vertical') echo '<br/>';
      }
    }
    
    public function drawTextOption( $id, $args ) {
      $width = $args['width'];
      $list = (isset($args['list'])) ? $args['list'] : false;
      ?>
        <input id="<?php echo $id; ?>"
          name="<?php echo SC_OPTIONS_NAME.'['.$id.']'; ?>"
          type="text"
          value="<?php echo $this->settings[$id]; ?>"
          <?php if($list) { ?>list="<?php echo $id.'_list'; ?>" <?php } ?>
          style="height: 22px; font-size: 11px; <?php if(!empty($width)) echo 'width: '.$width.';' ?>" />
      <?php
      if($list) {
        ?>
        <datalist id="<?php echo $id.'_list'; ?>">
          <?php self::getList( $list['name'], $list['count'] ); ?>
        </datalist>
        <?php
      }
    }

    public function drawTextOptionX( $id, $args ) {
      $width = $args['width'];
      $idn = $args['hidden'];
      $idf = $args['hidden2'];
      $button = $args['button'];
      $readonly = $args['readonly'];
      $ro = ($readonly) ? 'readonly' : '';
      ?>
        <input id="<?php echo $id; ?>"
          name="<?php echo SC_OPTIONS_NAME.'['.$id.']'; ?>"
          type="text"
          value="<?php echo $this->settings[$id]; ?>"
          style="height: 22px; font-size: 11px; <?php if(!empty($width)) echo 'width: '.$width.';' ?>"
          <?php echo $ro; ?>/>
      <?php
      if( $button ) {
        ?>
        <input id="clear_id" name="clear_id" type="button" class="button-secondary" value="<?php _e('Clear', SC_DOMAIN); ?>">
        <?php
      }
      if( $idn ) {
        ?>
        <span id='<?php echo $id.'_name'; ?>'></span>
        <input id="<?php echo $idn; ?>"
          name="<?php echo SC_OPTIONS_NAME.'['.$idn.']'; ?>"
          type="hidden"
          value="<?php echo $this->settings[$idn]; ?>"/>
        <input id="<?php echo $idf; ?>"
          name="<?php echo SC_OPTIONS_NAME.'['.$idf.']'; ?>"
          type="hidden"
          value="<?php echo $this->settings[$idf]; ?>"/>
        <?php
      }
    }

    public function drawTextString( $id, $args ) {
      ?>
        <input id="<?php echo $id; ?>"
          name="<?php echo SC_OPTIONS_NAME.'['.$id.']'; ?>"
          type="hidden"
          value="<?php echo $this->settings[$id]; ?>" />
        <span id="<?php echo $id.'_text'; ?>"><?php echo $this->settings[$id]; ?></span>
      <?php
    }

    public function drawCheckboxOption( $id, $args ) {
      ?>
        <input id="<?php echo $id; ?>"
          <?php checked('true', $this->settings[$id]); ?>
          name="<?php echo SC_OPTIONS_NAME.'['.$id.']'; ?>"
          type="checkbox"
          value="true" />
      <?php
    }
    
    public function drawCheckGroupOption( $id, $args ) {
      $options = $args['options'];
      foreach($options as $key => $option) {
      ?>
        <input id="<?php echo $key; ?>"
          <?php checked('true', $this->settings[$key]); ?>
          name="<?php echo SC_OPTIONS_NAME.'['.$key.']'; ?>"
          type="checkbox"
          value="true" />
        <label for='<?php echo $key; ?>'>
          <?php echo $option ?>
        </label>&nbsp;&nbsp;&nbsp;&nbsp;
      <?php
      if($args['style'] === 'vertical') echo '<br/>';
      }
    }

    public function drawPageOption( $id, $args ) {
      ?>
      <input
        id="<?php echo $id.'Val'; ?>"
        name="<?php echo SC_OPTIONS_NAME.'['.$id.'Val'.']'; ?>"
        type="hidden"
        value="<?php echo $this->settings[$id]; ?>" />
      <select id="<?php echo $id; ?>" name="<?php echo SC_OPTIONS_NAME.'['.$id.']'; ?>">
        <option value="0"><?php _e('None', SC_DOMAIN); ?></option>
      </select>
      <?php
    }
    
    public function drawInfo( $id, $args ) {
      $options = $args['options'];
      foreach($options as $option) {
        if($option === 'facebook') {
          $data = parent::getFacebookCount();
          $cid = 'fbCount';
          $cid2 = 'fb2Count';
          $count = $data['friends'];
          $count2 = $data['fans'];
          $aid = 'fbAvatar';
          $avatar = $data['avatar'];
          $aid2 = 'fb2Avatar';
          $avatar2 = $data['pageAvatar'];
          $lid = 'fbLink';
          $link = $data['link'];
          $lid2 = 'fb2Link';
          $link2 = $data['pageLink'];
        }
        elseif($option === 'twitter') {
          $data = parent::getTwCount();
          $count = $data['followers'];
          $cid = 'twCount';
          $avatar = $data['avatar'];
          $aid = 'twAvatar';
          $cid2 = null;
          $count2 = null;
          $aid2 = null;
          $avatar2 = null;
          $lid2 = null;
          $link2 = null;
        }
        ?>
          <input id='<?php echo $cid; ?>'
            type='hidden' 
            name='<?php echo SC_OPTIONS_NAME.'['.$cid.']'; ?>'  
            value='<?php echo $count; ?>'>
        <?php
        if(!is_null($cid2)) {
        ?>
          <input id='<?php echo $cid2; ?>'
            type='hidden'
            name='<?php echo SC_OPTIONS_NAME.'['.$cid2.']'; ?>'
            value='<?php echo $count2; ?>'>
        <?php
        }
        if(!is_null($avatar)) {
          ?>
            <input id='<?php echo $aid; ?>'
              type='hidden'
              name='<?php echo SC_OPTIONS_NAME.'['.$aid.']'; ?>'
              value='<?php echo $avatar; ?>'>
          <?php
        }
        if(!is_null($avatar2)) {
          ?>
            <input id='<?php echo $aid2; ?>'
              type='hidden'
              name='<?php echo SC_OPTIONS_NAME.'['.$aid2.']'; ?>'
              value='<?php echo $avatar2; ?>'>
          <?php
        }
        if(!is_null($link)) {
          ?>
            <input id='<?php echo $lid; ?>'
              type='hidden'
              name='<?php echo SC_OPTIONS_NAME.'['.$lid.']'; ?>'
              value='<?php echo $link; ?>'>
          <?php
        }
        if(!is_null($link2)) {
          ?>
            <input id='<?php echo $lid2; ?>'
              type='hidden'
              name='<?php echo SC_OPTIONS_NAME.'['.$lid2.']'; ?>'
              value='<?php echo $link2; ?>'>
          <?php
        }
        echo self::doInfo($option, $count, $count2).'<br />';
      }
    }
    
    function drawCount( $id, $args ) {
      if($args['count'] === 'feedburner') {
        $data = $this->getFbCount();
        $count = $data['circulation'];
      }
      elseif($args['count'] === 'twitter') {
        $data = $this->getTwCount();
        $count = $data['followers'];
      }
      ?>
        <input id='<?php echo $id; ?>'
          type='hidden' 
          name='<?php echo SC_OPTIONS_NAME.'['.$id.']'; ?>'  
          value='<?php echo $count; ?>'><?php echo $count; ?>
      <?php 
    }
    
    public function doSettingsSections($page) {
      global $wp_settings_sections, $wp_settings_fields;

      if ( !isset($wp_settings_sections) || !isset($wp_settings_sections[$page]) )
        return;

      foreach ( (array) $wp_settings_sections[$page] as $section ) {
        switch($section['id']) {
          case 'sc_account_section':
            echo "<div id='tab-general'>";
            break;
          case 'sc_badge_section':
            echo "<div id='tab-badge'>";
            break;
          case 'sc_hints_section':
            echo "<div id='tab-hints'>";
            break;
          default: break;
        }
        
        echo "<div id='poststuff' class='ui-sortable'>\n";
        echo "<div class='postbox opened'>\n";
        echo "<h3>{$section['title']}</h3>\n";
        echo '<div class="inside">';
        call_user_func($section['callback'], $section);
        if ( !isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section['id']]) )
          continue;
        self::doSettingsFields($page, $section['id']);
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        switch($section['id']) {
          case 'sc_tech_section':
            echo "</div>";
            break;
          case 'sc_badge_section':
            echo "</div>";
            break;
          case 'sc_hints_section':
            echo "</div>";
            break;
          default: break;
        }
      }
    }
    
    public function doSettingsFields($page, $section) {
      global $wp_settings_fields;

      if ( !isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section]) )
        return;

      foreach ( (array) $wp_settings_fields[$page][$section] as $field ) {
        echo '<p>';
        if ( !empty($field['args']['checkbox']) ) {
          call_user_func($field['callback'], $field['id'], $field['args']);
          echo '<label for="' . $field['args']['label_for'] . '">' . $field['title'] . '</label>';
          echo '</p>';
        }
        else {
          if ( !empty($field['args']['label_for']) )
            echo '<label for="' . $field['args']['label_for'] . '">' . $field['title'] . '</label>';
          else
            echo '<strong>' . $field['title'] . '</strong>';
          echo '</p>';
          echo '<p>';
          call_user_func($field['callback'], $field['id'], $field['args']);
          echo '</p>';
        }
        if(!empty($field['args']['description'])) echo '<p>' . $field['args']['description'] . '</p>';
        if(!empty($field['args']['hidden'])) {
          ?>
            <div id="sc_updated" class="message_fb" style="display: none">
              <p><?php echo $this->updated; ?></p>
            </div>
          <?php
        }
        if(!empty($field['args']['hidden2'])) {
          ?>
            <div id="sc_welcome" class="message" style="display: none">
              <p><?php echo $this->welcome; ?></p>
            </div>
          <?php
        }
        if(!empty($field['args']['line'])) {
          echo "<div class='clear-line'></div>";
        }
      }
    }
    
    public function onAdminPage() {
      if (function_exists('add_options_page')) {
        $this->settingsPage = add_options_page(__('Simple Counters', SC_DOMAIN), __('Simple Counters', SC_DOMAIN), 8, SC_DOMAIN, array(&$this, 'drawAdminPage'));
        add_action('admin_enqueue_scripts', array(&$this, 'adminHeaderScripts'));
        add_action('load-'.$this->settingsPage, array(&$this, 'addHelpTabs'));
      }
    }

    public function addHelpTabs() {
      $screen = get_current_screen();
      if($screen->id != $this->settingsPage) return;

      $screen->add_help_tab(array(
        'id' => 'fb_help',
        'title' => 'Facebook',
        'content' =>
          "<h3>".__("Creating Facebook App", SC_DOMAIN)."</h3>".
          "<p>".__('All data of your account on Facebook are confidential and are guarded. For retrieving some part of data, this plugin must have gateway to your Facebook account. Such gateway is Facebook application which you can create on the Facebook site of developers.', SC_DOMAIN)."</p>".
          "<p>".sprintf(__("If you don't already have an app for this website, go to %shttps://developers.facebook.com/apps%s and click the \"Create New App\" button. You'll see a dialog like the one below. Fill this in and click \"Continue\".", SC_DOMAIN), "<a href='https://developers.facebook.com/apps'>", "</a>")."</p>".
          sprintf("<p style='text-align: center'><img src='%s' /></p>", SC_IMG_URL.'fb_create_app.png').
          "<p>".sprintf(__("Next, set up your app so that it looks like the settings below. Make sure you set your app's icon and image, too. If you already have an app and skipped previous step, you can view your app settings by going to %shttps://developers.facebook.com/apps%s.", SC_DOMAIN), "<a href='https://developers.facebook.com/apps'>", "</a>")."</p>".
          sprintf("<p style='text-align: center'><img src='%s' /></p>", SC_IMG_URL.'fb_app_settings.png').
          "<p>".__('Here are for some recommendations for filling this form out, based on where this plugin is installed.', SC_DOMAIN)."</p>".
          "<ul><li><strong>App Domains</strong>: ".str_replace(array('http://', 'htpps://'), '', get_bloginfo('siteurl'))."</li><li><strong>Site URL</strong> ".__('and', SC_DOMAIN)." <strong>Mobile Web URL</strong>: ".get_bloginfo('siteurl')."</li></ul>".
          "<h3>".__("Plugin Facebook Settings", SC_DOMAIN)."</h3>".
          "<p>".__("Plugin's parameters for Facebook are configuring in two steps:", SC_DOMAIN)."</p>".
          "<h4>".__("Step One", SC_DOMAIN)."</h4>".
          "<p>".__("Fill up fields \"Facebook App Id\" and \"Facebook App Secret\" (only these fields) using App Id and App Secret of created Facebook app and save plugin's parameters.", SC_DOMAIN)."</p>".
          "<h4>".__("Step Two", SC_DOMAIN)."</h4>".
          "<p>".__("If the data in the previous step was entered correctly, the plugin will detects your full name. If it's your name, save again plugin's settings, for complete the connection settings with Facebook.", SC_DOMAIN)."</p>".
          "<h4>".__('Important', SC_DOMAIN).'</h4>'.
          "<ul><li>".__('If you have several accounts on Facebook, you must to be logged in to the Facebook account that contain the App to enable communication with this site. Or you must be logged out from any Facebook accounts.', SC_DOMAIN).'</li>'.
          /*"<li>".__('', SC_DOMAIN).'</li>'.*/'</ul>'
      ));

      $screen->add_help_tab(array(
        'id' => 'tw_help',
        'title' => 'Twitter',
        'content' =>
          "<h3>".__("Twitter Settings", SC_DOMAIN)."</h3>".
          "<p>".__("Just fill \"Twitter ID\" field with your Twitter nickname.", SC_DOMAIN)."</p>"
      ));
    }
    
    public function drawAdminPage() {
      $mem = ini_get('memory_limit');
      $version = $this->getEngineVersion();
      $wpVersion = $version['str'];
      ?>
      <div class="wrap">
        <?php screen_icon("options-general"); ?>
        <h2><?php  _e("Simple Counters Settings", SC_DOMAIN); ?></h2>
        <?php
        if(isset($_GET['settings-updated'])) $updated = $_GET['settings-updated'];
        elseif(isset($_GET['updated'])) $updated = $_GET['updated'];
        if($updated === 'true') {
          //$this->getCounters();
          //$this->settings = parent::getOptions();
        }
        ?>
        <div class="clear"></div>
        <form action="options.php" method="post">
          <div id='poststuff' class='metabox-holder has-right-sidebar'>
            <div id="side-info-column" class="inner-sidebar" style='width: 281px !important;'>
              <div class='postbox opened'>
                <h3><?php _e('System Info', SAM_DOMAIN) ?></h3>
                <div class="inside">
                  <p>
                    <?php 
                      echo __('Wordpress Version', SC_DOMAIN).': <strong>'.$wpVersion.'</strong><br/>';
                      echo __('Plugin Version', SC_DOMAIN).': <strong>'.$this->settings['version']/*SC_VERSION*/.'</strong><br/>';
                      echo __('PHP Version', SC_DOMAIN).': <strong>'.PHP_VERSION.'</strong><br/>';
                      echo __('Memory Limit', SC_DOMAIN).': <strong>'.$mem.'</strong>'; 
                    ?>
                  </p>
                  <p>
                    <?php _e('Note! If you have detected a bug, include this data to bug report.', SAM_DOMAIN); ?>
                  </p>
                </div>
              </div>
              <div class='postbox opened'>
                <h3><?php _e('Resources', SC_DOMAIN) ?></h3>
                <div class="inside">
                  <ul>
                    <li><a target='_blank' href='http://wordpress.org/extend/plugins/simple-counters/'><?php _e("Wordpress Plugin Page", SC_DOMAIN); ?></a></li>
                    <li><a target='_blank' href='http://www.simplelib.com/?p=256'><?php _e("Author Plugin Page", SC_DOMAIN); ?></a></li>
                    <li><a target='_blank' href='http://forum.simplelib.com/forumdisplay.php?10-Simple-Counters/'><?php _e("Support Forum", SC_DOMAIN); ?></a></li>
                    <li><a target='_blank' href='http://www.simplelib.com/'><?php _e("Author's Blog", SC_DOMAIN); ?></a></li>
                  </ul>                    
                </div>
              </div>  
              <div class='postbox opened'>
                <h3><?php _e('Donations', SC_DOMAIN) ?></h3>
                <div class="inside">
                  <p>
                    <?php
                      $format = __('If you have found this plugin useful, please consider making a %s to help support future development. Your support will be much appreciated. Thank you!', SC_DOMAIN);
                      $str = '<a title="'.__('Donate Now!', SC_DOMAIN).'" href="https://load.payoneer.com/LoadToPage.aspx?email=minimus@simplelib.com" target="_blank">'.__('donation', SC_DOMAIN).'</a>';
                      printf($format, $str);
                    ?>
                  </p>
                  <p style="color: #777777"><strong><?php _e('Donate via', SC_DOMAIN); ?> Payoneer:</strong></p>
                  <div style="text-align: center;">
                    <a title="Donate Now!" href="https://load.payoneer.com/LoadToPage.aspx?email=minimus@simplelib.com" target="_blank">
                      <img  title="<?php _e('Donate Now!', SC_DOMAIN); ?>" src="<?php echo SC_URL.'images/donate-now.png' ?>" alt="" width="100" height="34" style='margin-right: 5px;' />
                    </a>
                  </div>
                  <p style='margin: 3px; font-size: 0.8em !important;'>
                    <?php
                      $format = __("Warning! The default value of donation is %s. Don't worry! This is not my appetite, this is default value defined by Payoneer service.", SC_DOMAIN).'<strong>'.__(' You can change it to any value you want!', SC_DOMAIN).'</strong>';
                      $str = '<strong>$200</strong>';
                      printf($format, $str);
                    ?>
                  </p>
                  <p style="color: #777777"><strong><?php _e('Donate via', SC_DOMAIN); ?> PayPal:</strong></p>
                  <div style="text-align: center; margin: 10px;">
                    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                      <input type="hidden" name="cmd" value="_s-xclick">
                      <input type="hidden" name="hosted_button_id" value="FNPBPFSWX4TVC">
                      <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                      <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
                    </form>
                  </div>
                </div>
              </div>
              <div class='postbox opened'>
                <h3><?php _e('Another Plugins', SC_DOMAIN) ?></h3>
                <div class="inside">
                  <p><?php echo __('Another plugins from minimus', SC_DOMAIN).':'; ?></p>
                  <ul>
                    <li><a target='_blank' href='http://wordpress.org/extend/plugins/simple-ads-manager/'><strong>Simple Ads Manager</strong></a> - <?php _e("Advertisment rotation system with a flexible logic of displaying advertisements. ", SC_DOMAIN); ?></li>
                    <li><a target='_blank' href='http://wordpress.org/extend/plugins/wp-special-textboxes/'><strong>Special Text Boxes</strong></a> - <?php _e("Highlights any portion of text as text in the colored boxes.", SC_DOMAIN); ?></li>
                    <li><a target='_blank' href='http://wordpress.org/extend/plugins/simple-view/'><strong>Simple View</strong></a> - <?php _e("This plugin is WordPress shell for FloatBox library by Byron McGregor.", SC_DOMAIN); ?></li>
                    <li><a target='_blank' href='http://wordpress.org/extend/plugins/wp-copyrighted-post/'><strong>Copyrighted Post</strong></a> - <?php _e("Adds copyright notice in the end of each post of your blog. ", SC_DOMAIN); ?></li>
                  </ul>                    
                </div>
              </div>
            </div>
            <div id="post-body">
              <div id="post-body-content">
                <div id='tabs'>
                  <ul>
                    <li><a href='#tab-general'><?php _e('General', SC_DOMAIN); ?></a></li>
                    <li><a href='#tab-badge'><?php _e('Badge', SC_DOMAIN); ?></a></li>
                    <li><a href='#tab-hints'><?php _e('Tooltips', SC_DOMAIN); ?></a></li>
                  </ul>
                  <?php settings_fields('scOptions'); ?>
                  <?php $this->doSettingsSections('simple-counters'); ?>
                </div>
                <img id='fb-app-hint' src='<?php echo SC_IMG_URL.'fb-app-hint.jpg' ?>' alt='Facebook App Data' style='display: none;'/>
                <img id='fb-app-hint-2' src='<?php echo SC_IMG_URL.'fb-app-hint.jpg' ?>' alt='Facebook App Data' style='display: none;'/>
                <img id='win7-img' src='<?php echo SC_IMG_URL.'win7.jpg' ?>' alt='WindowZ Aero' style='display: none;'/>
                <img id='std-img' src='<?php echo SC_IMG_URL.'std.jpg' ?>' alt='Default' style='display: none;'/>
                <p class="submit">
                  <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
                </p>
                <p style='color: #777777; font-size: 12px; font-style: italic;'>Simple Counters plugin for Wordpress. Copyright &copy; 2010 - 2012, <a href='http://www.simplelib.com/'>minimus</a>. All rights reserved.</p>
              </div>
              <div id="fb-root"></div>
            </div>
          </div>
        </form>
      </div>
      <?php
    }
  }
}
?>
