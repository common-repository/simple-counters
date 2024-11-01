/**
 * @author minimus
 * @copyright 2009 - 2011
 * @version 1.2.18
 */
 
(function($) {
  scOptions.delta = parseInt(scOptions.delta);
  
  var opts = {};
  var fbLink = '';
  var twFolUrl = '';
  var fbCount = '0';
  var twCount = '0';
  var winWidth;
  var winHeight;
  var canvas = null;
  var buttonCanvas = {
    facebook: null,
    twitter: null
  };
  var fbImg;
  var twImg;
  
  fbImg = new Image();
  fbImg.onload = function() {
    if(null != buttonCanvas.facebook)
      doDrawButtonImg(buttonCanvas.facebook, this);
    return false;
  };
  fbImg.src = scOptions.facebook.imgUrl;
  twImg = new Image();
  twImg.onload = function() {
    if(null != buttonCanvas.twitter)
      doDrawButtonImg(buttonCanvas.twitter, this); 
    return false;
  };
  twImg.src = scOptions.twitter.imgUrl;
  
  function scGetRGB(color) {
    var clr = color.replace('#', '');
    var r = parseInt('0x'+clr.slice(0,2));
    var g = parseInt('0x'+clr.slice(2,4));
    var b = parseInt('0x'+clr.slice(4,6));
    return r.toString()+','+g.toString()+','+b.toString();
  }
  
  function doDrawButtonImg(ctx, img) {
    if (!$.browser.safari && !$.browser.mozilla)
      ctx.drawImage(img, 2,  2, 50, 50);
    else {
      if (img.complete) ctx.drawImage(img, 2, 2, 50, 50);
    }
  }
  
  function doDrawButton(ctx, width, img, count, str) {
    if(null == ctx) return;
    doDrawButtonImg(ctx, img);
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillStyle = opts.fontColor;
    ctx.font = 'bold 15px Arial';
    if($.browser.safari) {
      fontSize = 15;
      textWidth = ctx.measureText(count).width;
      if(textWidth > width - 6) {
        fontSize = Math.floor(fontSize * ((width - 6)/textWidth));
        ctx.font = 'bold ' + fontSize + 'px Arial';
      }
      ctx.fillText(count, width/2, 63);
    }
    else ctx.fillText(count, width/2, 63, width - 6);

    ctx.font = 'bold 10px Arial';
    if($.browser.safari) {
      fontSize = 10;
      textWidth = ctx.measureText(str).width;
      if(textWidth > /*rect.*/width - 6) {
        fontSize = Math.floor(fontSize * ((width - 6)/textWidth));
        ctx.font = 'bold ' + fontSize + 'px Arial';
      }
      ctx.fillText(str, width/2, 77);
    }
    else ctx.fillText(str, width/2, 77, width - 6);
  }
  
  function doDraw(cvs) {
    if (null == cvs) return;        
    var cw = cvs.width;
    var ch = cvs.height;
    var ctx = cvs.getContext("2d");
    if (opts.position == 'right') {
      ctx.clearRect(0, 0, cw, ch);
      var brd = 'rgba(' + scGetRGB(opts.borderColor) + ', 0.4)';
      ctx.beginPath();
      ctx.moveTo(cw, 0);
      ctx.lineTo(12, 0);
      ctx.quadraticCurveTo(0, 0, 0, 12);
      ctx.lineTo(0, ch - 12);
      ctx.quadraticCurveTo(0, ch, 12, ch);
      ctx.lineTo(cw, ch);
      ctx.closePath();
      ctx.fillStyle = brd;
      ctx.fill();
      
      // Windows 7 Style      
      if(opts.borderStyle == 'win7') {
        ctx.strokeStyle = 'rgba(255,255,255,0.4)';
        ctx.stroke();
      
        var winGrd = null;
        //winGrd = ctx.createLinearGradient(0, ch/3, 0, 0);
        winGrd = ctx.createRadialGradient(0, ch/3, 0, 0, ch/3, ch/4);
        winGrd.addColorStop(0, 'rgba(255,255,255,0.5)');
        winGrd.addColorStop(1, 'rgba(255,255,255,0.1)');
        ctx.beginPath();
        ctx.moveTo(cw, 0);
        ctx.lineTo(12, 0);
        ctx.quadraticCurveTo(0, 0, 0, 12);
        ctx.lineTo(0, ch/3);
        ctx.lineTo(cw, ch/3);
        ctx.closePath();
        ctx.fillStyle = winGrd;
        ctx.fill();
      
        ctx.beginPath();
        ctx.moveTo(11, 11);
        ctx.lineTo(11, ch - 11);
        ctx.lineTo(cw, ch - 11);
        ctx.lineTo(cw, 11);
        ctx.closePath();
        ctx.strokeStyle = 'rgba(225, 225, 225, 0.1)';
        ctx.stroke();
      }
      // end of Windows 7 Style
      
      ctx.fillStyle  = 'rgba(255,255,255,1)';
      ctx.fillRect(12, 12, cw-12, ch-24);
    
      var gradient = null;
      if (opts.dgv == 'horizontal') 
        gradient = ctx.createLinearGradient(12, 12, cw, 12);
      else 
        gradient = ctx.createLinearGradient(12, 12, 12, ch);
      gradient.addColorStop(0, opts.color);
      gradient.addColorStop(0.4, opts.color);
      gradient.addColorStop(1, opts.colorTo);
      ctx.beginPath();
      ctx.moveTo(12, 12);
      ctx.lineTo(12, ch - 12);
      ctx.lineTo(cw, ch - 12);
      ctx.lineTo(cw, 12);
      ctx.closePath();
      ctx.fillStyle = gradient;
      ctx.fill();
      
      // Windows 7 Style
      if(opts.borderStyle == 'win7') {
        ctx.strokeStyle = 'rgba(102, 107, 114, 1)';
        ctx.stroke();
      }      
      // end of Windows 7 Style
    }
    else {
      ctx.clearRect(0, 0, cw, ch);
      var brd = 'rgba(' + scGetRGB(opts.borderColor) + ', 0.4)';
      ctx.beginPath();
      ctx.moveTo(0, 0);
      ctx.lineTo(cw - 12, 0);
      ctx.quadraticCurveTo(cw, 0, cw, 12);
      ctx.lineTo(cw, ch - 12);
      ctx.quadraticCurveTo(cw, ch, cw-12, ch);
      ctx.lineTo(0, ch);
      ctx.closePath();
      ctx.fillStyle = brd;
      ctx.fill();
      
      // Windows 7 Style      
      if(opts.borderStyle == 'win7') {
        ctx.strokeStyle = 'rgba(255,255,255,0.4)';
        ctx.stroke();
      
        var winGrd = null;
        //winGrd = ctx.createLinearGradient(0, ch/3, 0, 0);
        winGrd = ctx.createRadialGradient(cw, ch/3, 0, cw, ch/3, ch/4);
        winGrd.addColorStop(0, 'rgba(255,255,255,0.5)');
        winGrd.addColorStop(1, 'rgba(255,255,255,0.1)');
        ctx.beginPath();
        ctx.moveTo(0, 0);
        ctx.lineTo(cw - 12, 0);
        ctx.quadraticCurveTo(cw, 0, cw, 12);
        ctx.lineTo(cw, ch/3);
        ctx.lineTo(0, ch/3);
        ctx.closePath();
        ctx.fillStyle = winGrd;
        ctx.fill();
      
        ctx.beginPath();
        ctx.moveTo(0, 11);
        ctx.lineTo(cw - 11, 11);
        ctx.lineTo(cw - 11, ch - 11);
        ctx.lineTo(0, ch - 11);
        ctx.closePath();
        ctx.strokeStyle = 'rgba(225, 225, 225, 0.1)';
        ctx.stroke();
      }
      // end of Windows 7 Style
      
      ctx.fillStyle  = 'rgba(255,255,255,1)';
    
      var gradient = null;
      if (opts.dgv == 'horizontal') 
        gradient = ctx.createLinearGradient(0, 12, cw - 12, 12);
      else 
        gradient = ctx.createLinearGradient(0, 12, 0, ch - 12);
      gradient.addColorStop(0, opts.color);
      gradient.addColorStop(0.4, opts.color);
      gradient.addColorStop(1, opts.colorTo);
      ctx.beginPath();
      ctx.moveTo(0, 12);
      ctx.lineTo(0, ch - 12);
      ctx.lineTo(cw - 12, ch - 12);
      ctx.lineTo(cw - 12, 12);
      ctx.closePath();
      ctx.fillStyle = gradient;
      ctx.fill();
    }
  }
  
  function makeButtonVML(name, img, count, str) {
    var vml = '';
    vml += '<div style="position: absolute; width: 52px; height: 88px; overflow: hidden;">';
    vml += '  <?import namespace = "sc_btn_'+name+'" urn = "urn:schemas-microsoft-com:vml" implementation = "#default#VML" declareNamespace />';
    vml += '  <sc_btn_'+name+':group style="POSITION: absolute; WIDTH: 10px; HEIGHT: 10px; TOP: 1px; LEFT: 1px" coordsize = "100,100">';
    vml += '    <sc_btn_'+name+':image style="WIDTH: 500px; HEIGHT: 500px" src = "'+img.src+'" croptop = "0" cropbottom = "0" cropleft = "0" cropright = "0" coordsize = "21600,21600"></sc_btn_'+name+':image>';
    vml += '  </sc_btn_'+name+':group>';
    
    vml += '  <sc_btn_'+name+':shape type="path" style="POSITION: absolute; WIDTH: 50px; HEIGHT: 15px; TOP: 50px; LEFT: 1px;" strokecolor ="'+opts.fontColor+'" coordsize = "500,150" coordorigin = "0,0" textpathok="t" path="m 0,120 l 500,120 e" stroke="f" strokeweight="0.1px">';
    vml += '    <sc_btn_'+name+':path textpathok = "t"/>';
    vml += '    <sc_btn_'+name+':textpath on="t" style="font: 14px Arial;" fillcolor = "'+opts.fontColor+'" string="'+count+'"/>';
    vml += '  </sc_btn_'+name+':shape>';
    
    vml += '  <sc_btn_'+name+':shape type="path" style="POSITION: absolute; WIDTH: 50px; HEIGHT: 15px; TOP: 65px; LEFT: 1px;" strokecolor ="'+opts.fontColor+'" coordsize = "500,150" coordorigin = "0,0" textpathok="t" path="m 0,120 l 500,120 e" stroke="f" strokeweight="0.1px">';
    vml += '    <sc_btn_'+name+':path textpathok = "t"/>';
    vml += '    <sc_btn_'+name+':textpath on="t" style="font: 9px Verdana, Arial, sans-serif;" fillcolor = "'+opts.fontColor+'" string="'+str+'"/>';
    vml += '  </sc_btn_'+name+':shape>';
    
    vml += '</div>';
    return vml;
  }
  
  function makeVML() {
    var vml = '';
    vml += '<div style="position: absolute; width: 65px; height: 200px; overflow: hidden">';
    vml += '  <?import namespace = "sc_bg" urn = "urn:schemas-microsoft-com:vml" implementation = "#default#VML" declareNamespace />';
    if (opts.position == 'right') {
      vml += '  <sc_bg:shape style="POSITION: absolute; WIDTH: 10px; HEIGHT: 10px" coordsize = "100,100" filled = "t" fillcolor = "' + opts.borderColor + '" stroked = "f" path = " m645,-5 l115,-5 c35,-5,-5,35,-5,115 l-5,1875 c-5,1955,35,1995,115,1995 l645,1995 x e">';
      vml += '    <sc_bg:fill opacity = "26214f"></sc_bg:fill>';
      vml += '  </sc_bg:shape>';
      if(opts.borderStyle == 'win7') {
        vml += '  <sc_bg:shape style="POSITION: absolute; WIDTH: 10px; HEIGHT: 10px" coordsize = "100,100" filled = "f" stroked = "t" strokecolor = "white" strokeweight = ".75pt" path = " m645,-5 l115,-5 c35,-5,-5,35,-5,115 l-5,1875 c-5,1955,35,1995,115,1995 l645,1995 x e">';
        vml += '    <sc_bg:stroke opacity = "26214f" miterlimit = "10" joinstyle = "miter" endcap = "flat"></sc_bg:stroke>';
        vml += '  </sc_bg:shape>';
        vml += '  <sc_bg:shape style="POSITION: absolute; WIDTH: 10px; HEIGHT: 10px" coordsize = "100,100" filled = "t" fillcolor = "white" stroked = "f" path = " m645,-5 l115,-5 c35,-5,-5,35,-5,115 l-5,662,645,662 x e">';
        vml += '    <sc_bg:fill type = "gradientRadial" opacity = "6553f" color2 = "white" g_o_:opacity2 = ".5" angle = "0" focus = "100%" focusposition = "0,1" focussize = "0,0" method = "none" colors = "0 white;1.5 white"></sc_bg:fill>';
        vml += '  </sc_bg:shape>';
      }
      vml += '  <sc_bg:shape style="POSITION: absolute; WIDTH: 10px; HEIGHT: 10px" coordsize = "100,100" filled = "f" stroked = "t" strokecolor = "#e1e1e1" strokeweight = ".75pt" path = " m105,105 l105,1885,645,1885,645,105 xe">';
      vml += '    <sc_bg:stroke opacity = "6553f" miterlimit = "10" joinstyle = "miter" endcap = "flat"></sc_bg:stroke>';
      vml += '  </sc_bg:shape>';
      vml += '  <sc_bg:shape style="POSITION: absolute; WIDTH: 10px; HEIGHT: 10px" coordsize = "100,100" filled = "t" fillcolor = "white" stroked = "f" path = " m115,115 l645,115,645,1875,115,1875 xe">';
      vml += '    <sc_bg:fill opacity = "1"></sc_bg:fill>';
      vml += '  </sc_bg:shape>';
      vml += '  <sc_bg:shape style="POSITION: absolute; WIDTH: 10px; HEIGHT: 10px" coordsize = "100,100" filled = "t" fillcolor = "' + opts.color + '" stroked = "f" path = " m115,115 l115,1875,645,1875,645,115 xe">';
      vml += '    <sc_bg:fill type = "gradient" opacity = "1" color2 = "' + opts.colorTo + '" g_o_:opacity2 = "1" angle = "' + ((opts.dgv == 'horizontal') ? '90' : '0') + '" focus = "100%" method = "none" colors = "0 ' + opts.color + ';26214f ' + opts.color + ';1 ' + opts.colorTo + '"></sc_bg:fill>';
      vml += '  </sc_bg:shape>';
      if(opts.borderStyle == 'win7') {
        vml += '  <sc_bg:shape style="POSITION: absolute; WIDTH: 10px; HEIGHT: 10px" coordsize = "100,100" filled = "f" stroked = "t" strokecolor = "#666b72" strokeweight = ".75pt" path = " m115,115 l115,1875,645,1875,645,115 xe">';
        vml += '    <sc_bg:stroke opacity = "1" miterlimit = "10" joinstyle = "miter" endcap = "flat"></sc_bg:stroke>';
        vml += '  </sc_bg:shape>';
      }
    }
    else {
      vml += '  <sc_bg:shape style="POSITION: absolute; WIDTH: 10px; HEIGHT: 10px" coordsize = "100,100" filled = "t" fillcolor = "' + opts.borderColor + '" stroked = "f" path = " m-5,-5 l525,-5 c605,-5,645,35,645,115 l645,1875 c645,1955,605,1995,525,1995 l-5,1995 x e">';
      vml += '    <sc_bg:fill opacity = "26214f"></sc_bg:fill>';
      vml += '  </sc_bg:shape>';
      if(opts.borderStyle == 'win7') {
        vml += '  <sc_bg:shape style="POSITION: absolute; WIDTH: 10px; HEIGHT: 10px" coordsize = "100,100" filled = "f" stroked = "t" strokecolor = "white" strokeweight = ".75pt" path = " m-5,-5 l525,-5 c605,-5,645,35,645,115 l645,1875 c645,1955,605,1995,525,1995 l-5,1995 x e">';
        vml += '    <sc_bg:stroke opacity = "26214f" miterlimit = "10" joinstyle = "miter" endcap = "flat"></sc_bg:stroke>';
        vml += '  </sc_bg:shape>';
        vml += '  <sc_bg:shape style="POSITION: absolute; WIDTH: 10px; HEIGHT: 10px" coordsize = "100,100" filled = "t" fillcolor = "white" stroked = "f" path = " m-5,-5 l525,-5 c605,-5,645,35,645,115 l645,662,-5,662 x e">';
        vml += '    <sc_bg:fill type = "gradientRadial" opacity = "6553f" color2 = "white" g_o_:opacity2 = ".5" angle = "0" focus = "100%" focusposition = "1,1" focussize = "0,0" method = "none" colors = "0 white;1.5 white"></sc_bg:fill>';
        vml += '  </sc_bg:shape>';
      }
      vml += '  <sc_bg:shape style="POSITION: absolute; WIDTH: 10px; HEIGHT: 10px" coordsize = "100,100" filled = "f" stroked = "t" strokecolor = "#e1e1e1" strokeweight = ".75pt" path = " m-5,105 l535,105,535,1885,-5,1885 xe">';
      vml += '    <sc_bg:stroke opacity = "6553f" miterlimit = "10" joinstyle = "miter" endcap = "flat"></sc_bg:stroke>';
      vml += '  </sc_bg:shape>';
      vml += '  <sc_bg:shape style="POSITION: absolute; WIDTH: 10px; HEIGHT: 10px" coordsize = "100,100" filled = "t" fillcolor = "' + opts.color + '" stroked = "f" path = " m-5,115 l-5,1875,525,1875,525,115 xe">';
      vml += '    <sc_bg:fill type = "gradient" opacity = "1" color2 = "' + opts.colorTo + '" g_o_:opacity2 = "1" angle = "' + ((opts.dgv == 'horizontal') ? '90' : '0') + '" focus = "100%" method = "none" colors = "0 ' + opts.color + ';26214f ' + opts.color + ';1 ' + opts.colorTo + '"></sc_bg:fill>';
      vml += '  </sc_bg:shape>';
      if(opts.borderStyle == 'win7') {
        vml += '  <sc_bg:shape style="POSITION: absolute; WIDTH: 10px; HEIGHT: 10px" coordsize = "100,100" filled = "f" stroked = "t" strokecolor = "#666b72" strokeweight = ".75pt" path = " m-5,115 l-5,1875,525,1875,525,115 xe">';
        vml += '    <sc_bg:stroke opacity = "1" miterlimit = "10" joinstyle = "miter" endcap = "flat"></sc_bg:stroke>';
        vml += '  </sc_bg:shape>';
      }
    }
    vml += '</div>';
    
    return vml;
  }
  
  function doButton(name, count, url, img, hintImg, str, hint, index) {
    var btnTop = 88*index + 12;
    var noCanvas = ( !document.createElement('canvas').getContext );
    if( noCanvas ) {
      $("#sc-canvas").append('<div id="' + name + '-button" width="52" height="88"></div>');
      cvs = null;
    }
    else {
      $("#scdiv").append('<canvas id="' + name + '-button" width="52" height="88"></canvas>');
      cvs = $('#'+name+'-button').get(0);
    }
    $("#"+name+"-button").css({
      position: 'absolute',
      top: btnTop + 'px',
      left: ((opts.position == 'right') ? '12px' : '0px'),
      'min-width': '51px',
      'min-height': '87px',
      'max-width': '51px',
      'max-height': '87px',
      cursor: 'pointer',
      'z-index': 3
    }).click(function(){
      window.open(url, 'SubNew');
    });
    if(noCanvas) $('#' + name + '-button').append(makeButtonVML(name, img, count, str));
    if(opts.hints == 'standard') $("#"+name+"-button").attr({title: hint});
    else 
      $("#"+name+"-button").qtip({
        id: name + 'Tip',
        prerender: true,
        content: { 
          text: '<img src="'+ hintImg +'" height="40" width="40" style="float:left; margin-right:5px" />' + hint
        },
        style: {
          classes: opts.hintStyle
        }, 
        position: {
          my: ((opts.position == 'left')? 'left center' : 'right center'),
          at: ((opts.position == 'left')? 'right center' : 'left center'),
          target: $("#"+name+"-button"),
          adjust: { 
            x: ((opts.position == 'left') ? 13 : -13), 
            y: 0 
          }
        }
      });
      
    if(null != cvs) {
      var cw = cvs.width;
      var ch = cvs.height;
      var ctx = cvs.getContext("2d");
      switch(name) {
        case 'facebook':
          buttonCanvas.facebook = ctx;
          break;
        case 'twitter':
          buttonCanvas.twitter = ctx;
          break;
      }
      doDrawButton(ctx, cw, img, count, str);
    }
    else {
      switch(name) {
        case 'facebook':
          buttonCanvas.facebook = null;
          break;
        case 'twitter':
          buttonCanvas.twitter = null;
          break;
      }
    }
  }
  
  $(document).ready(function($){       
    if((typeof(scUserOptions) != 'undefined') && (null != scUserOptions)) $.extend(true, opts, scOptions, scUserOptions);
    else opts = scOptions;
    twUrl = 'http://api.twitter.com/1/users/show.json';
    fbLink = opts.facebook.link;
    twFolUrl = 'http://twitter.com/' + opts.twitter.id;
    var scDiv = document.createElement("div");
    scDiv.id = "scdiv";
    document.body.appendChild(scDiv);
    
    if( typeof( window.innerHeight ) == 'number' ) {
      winHeight = window.innerHeight;
      winWidth = window.innerWidth;
    }
    else if( document.documentElement && document.documentElement.clientHeight ) {
      winHeight = document.documentElement.clientHeight;
      winWidth = document.documentElement.clientWidth;
    }
    else if( document.body && document.body.clientHeight ) {
      winHeight = document.body.clientHeight;
      winWidth = document.body.clientWidth;
    }
    
    var dt = Math.floor(winHeight/2-100) + opts.delta;
    
    if(opts.position == 'right') $("#scdiv").css({right: '0px'});
    else $("#scdiv").css({left: '0px'});
      
    $("#scdiv").css({
      position: 'fixed', 
      top: ((dt < 0) ? 0 : ((dt > winHeight-200) ? winHeight-200 : dt)),
      background: "transparent", 
      height: 200, 
      width: 65
    });
    if ( !document.createElement('canvas').getContext ) {
      $("#scdiv").append('<div id="sc-canvas" width="65" height="200" style="position: "absolute"; top:0px; left:0px;"></div>');
      $("#sc-canvas").append(makeVML());
    } else {
      $("#scdiv").append('<canvas id="sc-canvas" width="65" height="200" style="top:0px; left:0px;"></canvas>');
      canvas = $("#sc-canvas").get(0);
      doDraw(canvas);
    }
    
    fbCount = opts.facebook.count;
    doButton('facebook', fbCount, fbLink, fbImg, opts.facebook.hintImg, opts.facebook.str, opts.facebook.hint, 0);

    if(opts.twitter.count == '-1') {
      $.ajax({
        type: 'GET',
        url: twUrl,
        data: {'screen_name': opts.twitter.id},
        dataType: 'jsonp',
        success: function(jsonData) {
          twCount = jsonData.followers_count;
          if(opts.twitter.useAvatar) opts.twitter.hintImg = jsonData.profile_image_url;
          doButton('twitter', twCount, twFolUrl, twImg, opts.twitter.hintImg, opts.twitter.str, opts.twitter.hint, 1);
        }
      });
    }
    else {
      twCount = opts.twitter.count;
      doButton('twitter', twCount, twFolUrl, twImg, opts.twitter.hintImg, opts.twitter.str, opts.twitter.hint, 1);
    }
  });
  
  $(window).resize(function(){
    if( typeof( window.innerHeight ) == 'number' ) {
      winHeight = window.innerHeight;
      winWidth = window.innerWidth;
    }
    else if( document.documentElement && document.documentElement.clientHeight ) {
      winHeight = document.documentElement.clientHeight;
      winWidth = document.documentElement.ClientWidth;
    }
    else if( document.body && document.body.clientHeight ) {
      winHeight = document.body.clientHeight;
      winWidth = document.body.clientWWidth;
    }
    var dt = Math.floor(winHeight/2-100) + opts.delta;
    $("#scdiv").css({  top: ((dt < 0) ? 0 : ((dt > winHeight-200) ? winHeight-200 : dt)) }); 
    return false;
  });
})(jQuery);