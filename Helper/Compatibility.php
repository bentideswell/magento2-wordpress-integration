<?php
/*
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Helper;

class Compatibility extends \Magento\Framework\App\Helper\AbstractHelper
{
	/*
	 *
	 * @param  string $s
	 * @return string
	 */
	public function createZendDbSqlExpression($s)
	{
		$exprClass = $this->getZendDbSqlExpressionClass();
		
		$expr = new $exprClass($s);
		
		if ($expr instanceof \Zend\Db\Sql\Expression) {
			$expr = $expr->getExpression();
		}
		
		return $expr;
	}

	/*
	 *
	 * @return string
	 */
	private function getZendDbSqlExpressionClass()
	{		
		if ($this->classExists('\Zend_Db_Expr')) {
			return \Zend_Db_Expr::class;
		}
		
		if ($this->classExists('\Zend\Db\Sql\Expression')) {
			return \Zend\Db\Sql\Expression::class;
		}
		
		throw new \Exception('Unable to find \Zend\Db\Sql\Expression class.');
	}
	
	/*
	 *
	 * @param  string $class
	 * @return bool
	 */
	private function classExists($class)
	{
		try {
			if (@class_exists($class)) {
				return true;
			}
		}
		catch (\Exception $e) {}
		
		return false;
	}
}
