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
				$pagelist .= '<li><a href="'.self::url($page).'">'.$page.'</a></li>';
			}
		}
		//当前页
		$pagelist .= '<li class="active"><a>'.self::$page.'</a></li>';

		for($i=1;$i<=self::$bothnum;$i++) {
			$page = self::$page + $i;
			if ($page <= self::$pagenum){
				$pagelist .= '<li><a href="'.self::url($page).'">'.$page.'</a></li>';
			}
		}

		return $pagelist;
	}
	
	//首页
	private static function first() 
	{
		if (self::$page > self::$bothnum+1) {
				return '<li><a href="'.self::url(1).'">1...</a></li>';
		}
	}
	
	//上一页
	private static function prev() 
	{
		if (self::$page == 1) {
				return '<li class="disabled"><span><span aria-hidden="true">&laquo;</span></span></li>';
		}
		return '<li class="disabled"><a href="'.self::url(self::$page-1).'" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
	}
	
	//下一页
	private static function next() 
	{
		if (self::$page >= self::$pagenum) {
				return '<li class="disabled"><span><span aria-hidden="true">&raquo;</span></span></li>';
		}
		return '<li class="disabled"><a href="'.self::url(self::$page+1).'" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
	}
	
	//尾页
	private static function last() 
	{
		if (self::$pagenum - self::$page > self::$bothnum) {
				return '<li><a href="'.self::url(self::$pagenum).'">...'.self::$pagenum.'</a></li>';
		}
	}

	//获取url
	private static function url($page)
	{
		$url = self::$url.'&page='.$page;
		return $url;
	}
	
	//分页信息
	public static function paging($total, $pagesize, $cur_page, $url, $static=false) 
	{
		self::init($total, $pagesize, $cur_page, $url, $static);

		if(self::$pagenum<2){
			return '';
		}

		$page  = '<div class ="row"><nav aria-label="Page navigation navbar-right"><ul class="pagination">';
		$page .= self::prev();
		$page .= self::first();
		$page .= self::pageList();
		$page .= self::last();
		$page .= self::next();
		$page .= '</ul></nav></div>';

		return $page;
	}

}
