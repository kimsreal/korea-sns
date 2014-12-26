var g_bInitKakao = false;

function InitKakao(strKey){
	if( !g_bInitKakao ){
		Kakao.init(strKey);
		g_bInitKakao = true;
	}
}