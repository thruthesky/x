<?php

define('DS', DIRECTORY_SEPARATOR, true);


class etc {
	
	static function dir()
	{
		return x::dir();
	}
	static function url()
	{
		return x::url();
	}
	
	/**
	 *  @brief 모듈의 스크립트를 로드한다.
	 *  
	 *  @param [in] $file 모듈 폴더 내의 파일 이름. 확장자 ".php" 는 제외하고 입력을 한다.
	 *  @return string 파일 경로
	 *  
	 *  @details 리턴되는 값은 스크립트의 경로를 지정하는 문자열이다. 이 함수 밖에서 따로 인클루드를 해야한다.
	 */
	static function module($file)
	{
		global $module;
		return "module/$module/$file.php";
	}
	/**
	 *  @brief html 폴더에 있는 스크립트 파일을 리턴하낟.
	 *  
	 *  @param [in] $file 파일 이름. 확장자 ".php" 는 제외.
	 *  @return 문자열
	 *  
	 *  @details 파일 이름을 입력하면 HTML 폴더 내에 있는 파일 경로를 리턴한다.
	 */
	static function html($file)
	{
		global $module;
		return "html/$file.php";
	}
	
	
	/**
	 *  @brief returns all the included files.
	 *  
	 *  @return array files.
	 *  
	 *  @details Intened to look into the files that are included in the run.
	 */
	static function included_files() {
		$inst = g::dir();
		$files = get_included_files();
		foreach ( $files as $file ) {
			$file = str_replace("\\", '/', $file);
			$file = str_replace( $inst . '/' , '', $file );
			$out[] = $file;
		}
		return $out;
	}
	

	
	// ################################################################## helper functions
	// get base domain (domain.tld)
	// usage : etc::base_domain($_SERVER['HTTP_HOST'])
	// usage : etc::base_domain("asdfasdfa.asdfasdf.asdfasdf.asdfasd.31413241234.123.4123.4.1234.1324.abc.com"); => abc.com
	// @return
	//						www.abc.com				=>		abc.com
	//
	/*____________________________________________________________________________*/
	static function base_domain($full_domain=null)
	{
		if ( $full_domain === null ) {
			$full_domain = $_SERVER['HTTP_HOST'];
		}
	  
	  // generic tlds (source: http://en.wikipedia.org/wiki/Generic_top-level_domain)
	  $G_TLD = array(
		'biz','com','edu','gov','info','int','mil','name','net','org',
		'aero','asia','cat','coop','jobs','mobi','museum','pro','tel','travel',
		'arpa','root',
		'berlin','bzh','cym','gal','geo','kid','kids','lat','mail','nyc','post','sco','web','xxx',
		'nato',
		'example','invalid','localhost','test',
		'bitnet','csnet','ip','local','onion','uucp',
		'co'   // note: not technically, but used in things like co.uk
	  );
	  
	  // country tlds (source: http://en.wikipedia.org/wiki/Country_code_top-level_domain)
	  $C_TLD = array(
		// active
		'ac','ad','ae','af','ag','ai','al','am','an','ao','aq','ar','as','at','au','aw','ax','az',
		'ba','bb','bd','be','bf','bg','bh','bi','bj','bm','bn','bo','br','bs','bt','bw','by','bz',
		'ca','cc','cd','cf','cg','ch','ci','ck','cl','cm','cn','co','cr','cu','cv','cx','cy','cz',
		'de','dj','dk','dm','do','dz','ec','ee','eg','er','es','et','eu','fi','fj','fk','fm','fo',
		'fr','ga','gd','ge','gf','gg','gh','gi','gl','gm','gn','gp','gq','gr','gs','gt','gu','gw',
		'gy','hk','hm','hn','hr','ht','hu','id','ie','il','im','in','io','iq','ir','is','it','je',
		'jm','jo','jp','ke','kg','kh','ki','km','kn','kr','kw','ky','kz','la','lb','lc','li','lk',
		'lr','ls','lt','lu','lv','ly','ma','mc','md','mg','mh','mk','ml','mm','mn','mo','mp','mq',
		'mr','ms','mt','mu','mv','mw','mx','my','mz','na','nc','ne','nf','ng','ni','nl','no','np',
		'nr','nu','nz','om','pa','pe','pf','pg','ph','pk','pl','pn','pr','ps','pt','pw','py','qa',
		're','ro','ru','rw','sa','sb','sc','sd','se','sg','sh','si','sk','sl','sm','sn','sr','st',
		'sv','sy','sz','tc','td','tf','tg','th','tj','tk','tl','tm','tn','to','tr','tt','tv','tw',
		'tz','ua','ug','uk','us','uy','uz','va','vc','ve','vg','vi','vn','vu','wf','ws','ye','yu',
		'za','zm','zw',
		// inactive
		'eh','kp','me','rs','um','bv','gb','pm','sj','so','yt','su','tp','bu','cs','dd','zr'
		);
	  
	  
	  
	  // now the fun
	  
		// break up domain, reverse
		$DOMAIN = explode('.', $full_domain);
		$DOMAIN = array_reverse($DOMAIN);
		
		// first check for ip address
		if ( count($DOMAIN) == 4 && is_numeric($DOMAIN[0]) && is_numeric($DOMAIN[3]) )
		{
		  return $full_domain;
		}
		
		// if only 2 domain parts, that must be our domain
		if ( count($DOMAIN) <= 2 ) return $full_domain;
		
		/* 
		  finally, with 3+ domain parts: obviously D0 is tld 
		  now, if D0 = ctld and D1 = gtld, we might have something like com.uk
		  so, if D0 = ctld && D1 = gtld && D2 != 'www', domain = D2.D1.D0
		  else if D0 = ctld && D1 = gtld && D2 == 'www', domain = D1.D0
		  else domain = D1.D0
		  these rules are simplified below 
		*/
		if ( in_array($DOMAIN[0], $C_TLD) && in_array($DOMAIN[1], $G_TLD) && $DOMAIN[2] != 'www' )
		{
		  $full_domain = $DOMAIN[2] . '.' . $DOMAIN[1] . '.' . $DOMAIN[0];
		}
		else
		{
		  $full_domain = $DOMAIN[1] . '.' . $DOMAIN[0];;
		}
	  
	  // did we succeed?  
	  return $full_domain;
	}

	
	static function lang( $code )
	{
		global $language;
		if ( isset($language[$code]) ) return $language[$code];
		else return $code;
	}
	
	
	
	
	/**
	 *  @brief 입력받은 도메인에서 첫 부분을 리턴한다.
	 *  
	 *  @param [in] $domain Parameter_Description
	 *  @return string 도메인 첫 부분.
	 *  
	 *  @details 
	 *  리턴 되는 값의 예는 아래와 같다.
	 *  abc.def.com			=> abc
	 *  last.domain.is.com	=> last
	 *  www.domain.com		=> www
	 *  domain.com			=> domain
	 *  com					=> com
	 */
	static function last_domain($domain)
	{
		list ( $last, $rest ) = explode('.', $domain, 2);
		return $last;
	}
}




function module($file)
{
	return etc::module($file);
}


function login_first()
{
	return etc::html('login_first');
}

function login()
{
	global $member;
	if ( empty($member['mb_id']) ) return false;
	return ture;
}


function lang($code)
{
	return etc::lang($code);
}

/**
 *  @brief 슈퍼 관리자이면 참을 리턴한다.
 *  
 *  @return true if super admin, otherwise false.
 *  
 *  @details 슈퍼 관리자이면 참을 리턴한다.
 */
function admin()
{
	global $is_admin;
	return $is_admin == 'super';
}

/**
 *  @brief 관리자 페이지이면 참을 리턴한다.
 *  
 *  @return boolean
 *  
 *  @details 접속한 사용자가 관리자 페이지를 보고 있다면 참을 리턴한다.
 */
function admin_page()
{
	$self = $_SERVER['PHP_SELF'];
	return preg_match("/\/adm\//", $self);
}


function patch( $file )
{
	return x::dir() . ds . 'patch' . ds . $file . '.php';
}

