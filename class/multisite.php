<?php
define('MS_EXIST', -9200);
class ms extends multisite { }
class multisite {



	/**
	 *  @brief 새로운 사이트를 생성한다.
	 *  
	 *  @param [in] $o 사이트를 생성하기 위한 옵션 값
	 *  @return int 성공이면 0, 아니면 참
	 *  
	 *  @details 사이트를 생성하기 위해서는
	 *  <ol>
	 *  	<li> multisite 게시판 그룹이 존재하지 않으면 생성해야 한다.
	 *  	<li> 사이트 생성한다.
	 *  	<li> 사이트 게시판을 생성하고 기본 설정을 한다.
	 *  </ol>
	 *  
	 */
	static function create($o)
	{
		global $member;
		if ( ! g::group_exist('multisite') ) g::group_create(array('id'=>'multisite', 'subject'=>'multisite'));

		if ( self::exist($o['domain']) ) return MS_EXIST;
		$time = time();
		$q = "
			INSERT INTO multisite_config ( domain, mb_id, stamp_create, title, extra )
			VALUES ( '$o[domain]', '$member[mb_id]', $time, '$o[title]', '' )
		";
		db::query($q);
		return  0;
	}
	/**
	 *  @brief 사이트가 존재하는지 확인한다.
	 *  
	 *  @param [in] $domain 사이트 도메인
	 *  @return boolean
	 *  사이트가 이미 있으면 참, 없으면 거짓.
	 *  
	 *  @details 사이트가 다른 사람에 의해서 개설 되었는지 또는 사이트가 존재하는지 확인하려고 할 때 사용한다.
	 */
	static function exist($domain)
	{
		$info = self::get( $domain );
		if ( $info ) return true;
		else return false;
	}
	
	static function get( $domain )
	{
		$sql = "SELECT * FROM multisite_config WHERE domain='$domain'";
		return db::row( $sql );
	}

	/**
	 *  @brief returns my sites
	 *  
	 *  @return array list of login user's sites.
	 *  
	 *  @details simply returns all the records of user's site.
	 */	
	static function my_site()
	{
		global $member;
		return self::pre_site(db::rows("SELECT * FROM multisite_config WHERE mb_id='$member[mb_id]'"));
	}
	
	static function pre_site($sites)
	{
		$rets = array();
		foreach( $sites as $site ) {
			if ( empty($site['title']) ) $site['title'] = lang('no subject');
			$rets[] = $site;
		}
		return $rets;
	}
	
	
	static function url_create()
	{
		return x::url() . '/?module=multisite&action=create';
	}
	
	static function url_config()
	{
		return x::url() . '/?module=multisite&action=config';
	}
	
	
	/**
	 *  @brief 사이트 URL 주소를 리턴한다.
	 *  
	 *  @param [in] $domain 도메인
	 *  @return string URL
	 *  
	 *  @details 도메인을 입력받아서 멀티 사이트의 주소를 리턴한다.
	 *  그누보드가 도메인 최상위 폴더가 아니라 서브 폴더에 설치된 경우를 지원한다.
	 *  예) http://work.org/g5-5.0b17-2/
	 */
	static function url_site($domain)
	{
		$pi = pathinfo($_SERVER['PHP_SELF']);
		return 'http://' . $domain . $pi['dirname'];
	}
	
	
	
	/**
	 *  @brief 사이트의 게시판 아이디를 리턴한다.
	 *  
	 *  @param [in] $domain 사이트 도메인
	 *  @return string 게시판 아이디
	 *  
	 *  @details 게시판 아이디 형식은 "ms_[도메인]" 이다.
	 */
	static function board_id( $domain )
	{
		return 'ms_' . etc::last_domain($domain);
	}
	

}
