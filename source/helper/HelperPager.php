<?php
class HelperPager 
{
	private static $total;//总记录
	private static $pagesize;//每页显示多少条
	private static $page;//当前页码
	private static $pagenum;//总页码
	private static $url;//地址
	private static $static;//静态化
	private static $bothnum;//两边保持数字分页的量
	
	//构造方法初始化
	private static function init($total, $pagesize, $cur_page, $url, $static=false)
	{
		self::$total    = $total ? $total : 1;
		self::$pagesize = $pagesize;
		self::$pagenum  = ceil(self::$total / self::$pagesize);
		self::$page     = $cur_page;
		self::$url      = $url;
		self::$static   = $static;
		self::$bothnum  = 2;
	}

	//数字目录
	private static function pageList() 
	{
		$pagelist = '';

		for($i=self::$bothnum;$i>0;$i--){
			$page = self::$page-$i;
			if ($page > 0){
				$pagelist .= '<a href="'.self::url($page).'"><em>'.$page.'</em></a>';
			}
		}
		//当前页
		$pagelist .= '<a class="active"><strong>'.self::$page.'</strong></a>';

		for($i=1;$i<=self::$bothnum;$i++) {
			$page = self::$page + $i;
			if ($page <= self::$pagenum){
				$pagelist .= ' <a href="'.self::url($page).'"><em>'.$page.'</em></a> ';
			}
		}

		return $pagelist;
	}
	
	//首页
	private static function first() 
	{
		if (self::$page > self::$bothnum+1) {
				return ' <a href="'.self::url(1).'"><em>1...</em></a>';
		}
	}
	
	//上一页
	private static function prev() 
	{
		if (self::$page == 1) {
				return '<a href="#" class="prev"><em class="prev-icon base-pager-left"></em></a>';
		}
		return '<a href="'.self::url(self::$page-1).'" class="prev"><em class="prev-icon base-pager-left"></em></a>';
	}
	
	//下一页
	private static function next() 
	{
		if (self::$page >= self::$pagenum) {
				return '<span class="disabled">下一页</span>';
		}
		return '<a href="'.self::url(self::$page+1).'" class="next"><em class="next-icon base-pager-right"></em></a>';
	}
	
	//尾页
	private static function last() 
	{
		if (self::$pagenum - self::$page > self::$bothnum) {
				return '...<a href="'.self::url(self::$pagenum).'"><em>...'.self::$pagenum.'</em></a>';
		}
	}

	//获取url
	private static function url($page)
	{
		$url = self::$url.'&page='.$page;
		
		//静态化url
		if(self::$static){
			$regs = array(
				'search'  => array('/index.php\\?mod=forum&fid=(\\d+)&page=(\\d+)/'),
				'replace' => array('forum-\\1-\\2.html')
			);
			$url = preg_replace($regs['search'], $regs['replace'], $url);
		}

		return $url;
	}
	
	//分页信息
	public static function paging($total, $pagesize, $cur_page, $url, $static=false) 
	{
		self::init($total, $pagesize, $cur_page, $url, $static);

		$page  = '<div class="bbs-page">';
		$page .= self::prev();
		$page .= self::first();
		$page .= self::pageList();
		$page .= self::last();
		$page .= self::next();
		$page .= '</div>';

		return $page;
	}

}
