<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Db
 */

namespace Address\Db\ResultSet;

use JsonSerializable;
use Zend\Db\ResultSet\ResultSet as BaseResultSet;
/**
 * @package    Address_Db
 * @subpackage ResultSet
 */
class ResultSet extends BaseResultSet implements JsonSerializable
{
    public function jsonSerialize()
    {
       return $this->toArray();
    }

    public function toArray()
    {
        $ret = array();

        if ($this->count()>0) {
            $ret = parent::toArray();
        }

        return $ret;
    }
}
