<?php

/**
 * @author minimus
 * @copyright 2009 - 2010
 * @version 0.5.11
 */

header("Content-type: text/javascript"); 
include("../../../../wp-load.php");
if($minimus_simple_counters != NULL)
	$scOptions = $minimus_simple_counters->getOptions();
if($scOptions['hintTheme'] === 'custom') $style = 'scStyle'; 
else $style = $scOptions['hintTheme'];

?>
(function($) {
	var scOptions = {
		position: '<?php echo $scOptions['position'] ?>',
		delta: <?php echo $scOptions['delta'] ?>,
		fontColor: '#<?php echo $scOptions['fontColor'] ?>',
		color: '#<?php echo $scOptions['color'] ?>',
		colorTo: '#<?php echo $scOptions['colorTo'] ?>',
		borderColor: '#<?php echo $scOptions['borderColor'] ?>',
		dgv: '<?php echo $scOptions['gradient'] ?>',
		hints: '<?php echo $scOptions['hints'] ?>',
		feedBurner: {
			id: '<?php echo $scOptions['fbId'] ?>',
			imgUrl: '<?php echo $scOptions['fbImg'] ?>',
			count: '<?php echo $scOptions['fbCount'] ?>',
			hint: '<?php echo $minimus_simple_counters->parseKeywords( $scOptions['fbHint'] ); ?>',
			hintImg: '<?php echo $scOptions['fbHintImg'] ?>',
			subData: '<?php echo $scOptions['fbSubData'] ?>'
		},
		twitter: {
			id: '<?php echo $scOptions['twId'] ?>',
			imgUrl: '<?php echo $scOptions['twImg'] ?>',
			count: '<?php echo $scOptions['twCount'] ?>',
			hint: '<?php echo $minimus_simple_counters->parseKeywords( $scOptions['twHint'] ); ?>',
			hintImg: '<?php echo ( (($scOptions['twHintImgSource'] === 'avatar') && ($scOptions['twAvatar'] !== '')) ? $scOptions['twAvatar'] : $scOptions['twHintImg'] ); ?>',
			useAvatar: <?php echo (($scOptions['twHintImgSource'] === 'avatar') ? 'true' : 'false');?>
		}
	};
	var opts = {};
	var fbSubUrl = '';
	var twFolUrl = '';
	var fbCount = '0';
	var twCount = '0';
	var winWidth;
	var winHeight;
	var canvas = null;
	var fbImg;
	var twImg;
	var fbImgLoaded = false;
	var twImgLoaded = false;
	
	
	fbImg = new Image();
	fbImg.onload = function() {
		fbImgLoaded = true;
		if(null != canvas) 
			if($.browser.safari || $.browser.mozilla) doDraw(canvas); 
		return false;
	}
	fbImg.src = scOptions.feedBurner.imgUrl;
	twImg = new Image();
	twImg.onload = function() {
		twImgLoaded = true;
		if(null != canvas)
			if($.browser.safari || $.browser.mozilla) doDraw(canvas); 
		return false;
	}
	twImg.src = scOptions.twitter.imgUrl;
	
	$.fn.qtip.styles.scStyle = { 
		tip: true,
		width: 350,
		padding: 5,
		<?php if($style === 'scStyle') {?>
		background: '#<?php echo $scOptions['hintColor'] ?>',
		color: '#<?php echo $scOptions['hintFontColor'] ?>',
		//'font-family': 'Verdana, "Lucida Grande", Arial, sans-serif',
		'font-family': 'sans-serif',
		<?php } else { ?> 
		name: '<?php echo $scOptions['hintTheme'];?>',
		<?php } ?>
		'font-size': <?php echo $scOptions['hintFontSize'] ?>,
		//textAlign: 'center',
		border: {
			width: 7,
			radius: 5 <?php if($style === 'scStyle') {?>,
			color: '#<?php echo $scOptions['hintBorderColor'] ?>' <?php } ?>
		}
	};
	
	function scGetRGB(color) {
		var clr = color.replace('#', '');
		var r = parseInt('0x'+clr.slice(0,2));
		var g = parseInt('0x'+clr.slice(2,4));
		var b = parseInt('0x'+clr.slice(4,6));
		return r.toString()+','+g.toString()+','+b.toString();
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
			
			ctx.fillStyle	= 'rgba(255,255,255,1)';
			ctx.fillRect(12, 12, cw-12, ch-24)
		
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
		
			if (!$.browser.safari && !$.browser.mozilla) {
				ctx.drawImage(fbImg, 14, 14, 50, 50);
				ctx.drawImage(twImg, 14, 100, 50, 50);
			}
			else {
				if (fbImgLoaded) ctx.drawImage(fbImg, 14, 14, 50, 50);
				if (twImgLoaded) ctx.drawImage(twImg, 14, 100, 50, 50);
			}
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillStyle = opts.fontColor;
      ctx.font = 'bold 15px Arial';
      ctx.fillText(fbCount, (12 + (cw -12)/2), 78);

      ctx.font = 'bold 10px Arial';
      ctx.fillText('readers', (12 + (cw -12)/2), 90);
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
			
			ctx.fillStyle	= 'rgba(255,255,255,1)';
		
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
		
			if (!$.browser.safari && !$.browser.mozilla) {
				ctx.drawImage(fbImg, 2, 14, 50, 50);
				ctx.drawImage(twImg, 2, 100, 50, 50);
			}
			else {
				if (fbImgLoaded) ctx.drawImage(fbImg, 2, 14, 50, 50);
				if (twImgLoaded) ctx.drawImage(twImg, 2, 100, 50, 50);
			}

      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillStyle = opts.fontColor;
      ctx.font = 'bold 15px Arial';
      ctx.fillText(fbCount, (cw - 12)/2, 78);

      ctx.font = 'bold 10px Arial';
      ctx.fillText('readers', (cw - 12)/2, 90);
		}
	}
	
	function doFBDivs(count) {
		$("#scdiv").append('<div id="fb-count" width="52" height="20"></div>');
		$("#fb-count").css({
			position: 'absolute',
			'min-width': '51px',
			top: '65px',
			left: ((opts.position == 'right') ? '13px' : '0px'),
			color: '#' + opts.fontColor,
			'font-family': 'sans-serif',
			'font-size': ((count.length > 6) ? '10px' : '14px'),
			'font-weight': 'bold',
			'text-align': 'center',
			cursor: 'pointer',
			'z-index': 2
		}).html('<span>' + count + '</span><br/><span style="font-size: 10px">' + ((count == 1) ? 'reader' : 'readers') + '</span>');
		$("#scdiv").append('<div id="fb-button" width="52"></div>');
		$("#fb-button").css({
			position: 'absolute',
			top: '12px',
			left: ((opts.position == 'right') ? '13px' : '0px'),
			'min-width': '51px',
			'min-height': '87px',
			cursor: 'pointer',
			'z-index': 3
		}).click(function(){
			window.open(fbSubUrl, 'fbNew');
		});
		if(opts.hints == 'standard') $("#fb-button").attr({title: opts.feedBurner.hint});
		else $("#fb-button").qtip({
				content: '<img src="'+ opts.feedBurner.hintImg +'" height="40" width="40" style="float:left; margin-right:5px" />' + opts.feedBurner.hint,
				style: 'scStyle', 
				position: {
					corner: {
						target: ((opts.position == 'left')? 'rightMiddle' : 'leftMiddle'),
						tooltip: ((opts.position == 'left')? 'leftMiddle' : 'rightMiddle')
					},
					adjust: { x: ((opts.position == 'left') ? 13 : -13), y: 0 }
				}
			});
	}
	
	function doTWDivs(count) {
		$("#scdiv").append('<div id="tw-count" width="52" height="20"></div>');
		$("#tw-count").css({
			position: 'absolute',
			'min-width': '51px',
			top: '150px', 
			left: ((opts.position == 'right') ? '13px' : '0px'),
			color: '#' + opts.fontColor,
			'font-family': 'sans-serif',
			'font-size': ((count.length > 6) ? '10px' : '14px'),
			'font-weight': 'bold',
			'text-align': 'center', 
			'z-index': 2
		}).html('<span>' + count + '</span><br/><span style="font-size: 10px">'+((count == 1) ? 'follower' : 'followers')+'</span>');
		$("#scdiv").append('<div id="tw-button" width="52"></div>');
		$("#tw-button").css({
			position: 'absolute',
			top: '100px',
			left: ((opts.position == 'right') ? '13px' : '0px'),
			'min-width': '51px',
			'min-height': '87px',
			cursor: 'pointer',
			'z-index': 3
		}).click(function() {
			window.open(twFolUrl, 'twNew');
		});
		if(opts.hints == 'standard') $("#tw-button").attr({title: opts.twitter.hint});
		else $("#tw-button").qtip({
				content: '<img src="' + opts.twitter.hintImg + '" height="40" width="40" style="float:left; margin-right:5px" />' + opts.twitter.hint,
				style: 'scStyle', 
				position: {
					corner: {
						target: ((opts.position == 'left')? 'rightMiddle' : 'leftMiddle'),
						tooltip: ((opts.position == 'left')? 'leftMiddle' : 'rightMiddle')
					},
					adjust: { x: ((opts.position == 'left') ? 13 : -13), y: 0 }
				} 
			});
	}
	
	$(document).ready(function($){
		if((typeof(scUserOptions) != 'undefined') && (null != scUserOptions)) $.extend(true, opts, scOptions, scUserOptions);
		else opts = scOptions;
		fbUrl = 'http://pipes.yahoo.com/pipes/pipe.run';
		twUrl = 'http://twitter.com/users/show.json';
		fbSubUrl = 'http://feeds.feedburner.com/' + opts.feedBurner.id + ((opts.feedBurner.subData == 'xml') ? '?format=xml' : '');
		twFolUrl = 'http://twitter.com/' + opts.twitter.id;
		var scDiv = document.createElement("div");
		scDiv.id = "scdiv";
		document.body.appendChild(scDiv);
		
		if( typeof( window.innerHeight ) == 'number' ) winHeight = window.innerHeight;
  	else if( document.documentElement && document.documentElement.clientHeight ) winHeight = document.documentElement.clientHeight;
  	else if( document.body && document.body.clientHeight ) winHeight = document.body.clientHeight;
		
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
		if (window.G_vmlCanvasManager) {
      $("#scdiv").append('<div id="sc-canvas" width="65" height="200" style="position:absolute; top:0px; left:0px;"></div>');
      canvas = G_vmlCanvasManager.initElement($("#scdiv").children("div").get(0));
    } else {
      $("#scdiv").append('<canvas id="sc-canvas" width="65" height="200" style="position:absolute; top:0px; left:0px;"></canvas>');
      canvas = $("#scdiv").children("canvas").get(0);
    }
		if (opts.feedBurner.count == '-1') {
			$.getJSON(fbUrl + '?_id=b47b5cb1a615935b43858618ebe5ee32&uri=' + opts.feedBurner.id + '&_render=json&_callback=?', 
				function(data){
					fbCount = data.value.items[0].circulation;
					doDraw(canvas);
					doFBDivs(fbCount);
				}
			);
		}
		else {
			fbCount = opts.feedBurner.count;
			doDraw(canvas);
			//doFBDivs(fbCount);
		}
		if(opts.twitter.count == '-1') {
			$.ajax({
				type: 'GET',
				url: twUrl,
				data: {'screen_name': opts.twitter.id},
				dataType: 'jsonp',
				success: function(jsonData) {
					twCount = jsonData.followers_count;
					if(opts.twitter.useAvatar) opts.twitter.hintImg = jsonData.profile_image_url;
					doDraw(canvas);
					doTWDivs(twCount);
				}
			});
		}
		else {
			twCount = opts.twitter.count;
			doDraw(canvas);
			doTWDivs(twCount);
		}
		doDraw(canvas);
		//doFBDivs(fbCount);
		//doTWDivs(twCount);
	});
	
	$(window).resize(function(){
		if( typeof( window.innerHeight ) == 'number' ) winHeight = window.innerHeight;
  	else if( document.documentElement && document.documentElement.clientHeight ) winHeight = document.documentElement.clientHeight;
  	else if( document.body && document.body.clientHeight ) winHeight = document.body.clientHeight;
		var dt = Math.floor(winHeight/2-100) + opts.delta;
		$("#scdiv").css({	top: ((dt < 0) ? 0 : ((dt > winHeight-200) ? winHeight-200 : dt)) }); 
		return false;
	});
})(jQuery);
