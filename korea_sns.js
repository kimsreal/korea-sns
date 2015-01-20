var g_bInitKakao = false;

function InitKakao(strKey){
	if( !g_bInitKakao ){
		Kakao.init(strKey);
		g_bInitKakao = true;
	}
}

function SendKakaostory(strKey, strUrl)
{
	InitKakao(strKey);
	
	Kakao.Auth.login({
    success: function() {
      Kakao.API.request( {
        url : '/v1/api/story/linkinfo',
        data : {
          url : strUrl
        }
      }).then(function(res) {
        return Kakao.API.request( {
          url : '/v1/api/story/post/link',
          data : {
            link_info : res
          }
        });
      }).then(function(res) {
        return Kakao.API.request( {
          url : '/v1/api/story/mystory',
          data : { id : res.id }
        });
      }).then(function(res) {
        alert("successfully Shared");
      }, function (err) {
        alert(JSON.stringify(err));
      });
    },
    fail: function(err) {
      alert(JSON.stringify(err));
    }
  });
}

function SendSNS(sns, title, url, image)
{
    var o;
    var _url = encodeURIComponent(url);
    var _title = encodeURIComponent(title);
    var _br  = encodeURIComponent('\r\n');
 
    switch(sns)
    {
        case 'facebook':
            o = {
                method:'popup',
                height:600,
                width:600,
                url:'http://www.facebook.com/sharer/sharer.php?u=' + _url
            };
            break;
 
        case 'twitter':
            o = {
                method:'popup',
                height:600,
                width:600,
                url:'http://twitter.com/intent/tweet?text=' + _title + '&url=' + _url
            };
            break;
        
        case 'google':
            o = {
                method:'popup',
                height:600,
                width:600,
                url:'https://plus.google.com/share?url={' + _url + '}'
            };
            break;    
            
        case 'kakaotalk':
            o = {
                method:'web2app',
                param:'sendurl?msg=' + _title + '&url=' + _url + '&type=link&apiver=2.0.1&appver=2.0&appid=icansoft.com&appname=' + encodeURIComponent('icansoft.com'),
                a_store:'itms-apps://itunes.apple.com/app/id362057947?mt=8',
                g_store:'market://details?id=com.kakao.talk',
                a_proto:'kakaolink://',
                g_proto:'scheme=kakaolink;package=com.kakao.talk'
            };
            break;
 
        case 'kakaostory':
            o = {
                method:'web2app',
                param:'posting?post=' + _title + _br + _url + '&apiver=1.0&appver=2.0&appid=icansoft.com&appname=' + encodeURIComponent('icansoft.com'),
                a_store:'itms-apps://itunes.apple.com/app/id486244601?mt=8',
                g_store:'market://details?id=com.kakao.story',
                a_proto:'storylink://',
                g_proto:'scheme=kakaolink;package=com.kakao.story'
            };
            break;
					
        case 'naverband':
            o = {
                method:'web2app',
                param:'create/post?text=' + _title + _br + _url,
                a_store:'itms-apps://itunes.apple.com/app/id542613198?mt=8',
                g_store:'market://details?id=com.nhn.android.band',
                a_proto:'bandapp://',
                g_proto:'scheme=bandapp;package=com.nhn.android.band'
            };
            break;
 
        default:
            return false;
    }
 
    switch(o.method)
    {
	    case 'popup':
	    	if( o.height > 0 && o.width > 0 ){
		    	window.open(o.url,'', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height='+o.height+',width='+o.width);
	    	}
	    	else{
		    	 window.open(o.url);
	    	}
	     
	      break;	    	
	    
	    case 'web2app':
	      if(navigator.userAgent.match(/android/i)){
	          setTimeout(function(){ location.href = 'intent://' + o.param + '#Intent;' + o.g_proto + ';end'}, 100);
	      }
	      else if(navigator.userAgent.match(/(iphone)|(ipod)|(ipad)/i)){
	          setTimeout(function(){ location.href = o.a_store; }, 200);          
	          setTimeout(function(){ location.href = o.a_proto + o.param }, 100);
	      }
	      else{
	          alert('Only mobile');
	      }
	      break;
    }
}