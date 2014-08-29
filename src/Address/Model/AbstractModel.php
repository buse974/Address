<?php

namespace Address\Model;

use JsonSerializable;
use Address\Stdlib\Hydrator\ClassMethods;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Predicate\IsNull;

abstract class AbstractModel implements JsonSerializable, ServiceLocatorAwareInterface
{
    /**
     * Prefix name
     * @var string
     */
    protected $prefix;
    protected $parent_model;
    protected $service_locator = null;
    private $delimiter = '$';

    /**
     * Construct model with associate table name
     * @param AbstractModel $parent_model
     * @param string        $prefix
     */
    public function __construct($parent_model = null, $prefix = null)
    {
        if ($prefix!==null) {
            $this->prefix = $prefix;
        }
        if ($parent_model!==null) {
            $this->parent_model = $parent_model;
        }
    }

    /**
     * Populate from an array
     * @param  array         $data
     * @return AbstractModel
     */
    public function exchangeArray(array &$data)
    {
        foreach ($data as &$val) {
            if (empty($val) && !is_numeric($val)) {
              $val = new IsNull();
            }
        }
        $formatted = array();

        if ($this->prefix !== null) {
             foreach ($data as $key => $value) {
                if (0===strpos($key, $this->allParent() . $this->delimiter)) {
                    $formatted[substr($key, strlen($this->allParent() . $this->delimiter))] = $value;
                    unset($data[$key]);
                } elseif (0===strpos($key, $this->prefix . $this->delimiter)) {
                    $formatted[substr($key, strlen($this->prefix . $this->delimiter))] = $value;
                    unset($data[$key]);
                }
            }
        }
        $hydrator = new ClassMethods();
        if (!empty($formatted)) {
            $hydrator->hydrate($formatted, $this);
        } else {
            $hydrator->hydrate($data, $this);
        }

        return $this;
    }

    /**
     * Convert the model to an array
     * @return array
     */
    public function toArray()
    {
        $hydrator = new ClassMethods();
        $vars = $hydrator->extract($this);

        foreach ($vars as $key => &$value) {
            if ($value===null) {
                unset($vars[$key]);
            } elseif (is_object($value) && method_exists($value, 'toArray')) {
                $value = $value->toArray();
                if (count($value)==0) {
                    unset($vars[$key]);
                }
            } elseif (is_object($value) && $value instanceof IsNull) {
                    $vars[$key] = null;
            } elseif (is_array($value)) {
                foreach ($value as &$tabval) {
                    if (is_object($tabval) && method_exists($value, 'toArray')) {
                        $tabval = $tabval->toArray();
                        if (count($tabval)==0) {
                            unset($tabval);
                        } elseif (is_object($value) && $value instanceof IsNull) {
                            $tabval = null;
                        }
                    }
                }
            } elseif (is_object($value)) {
                unset($vars[$key]);
            }
        }

        return $vars;
    }

    public function toArrayCurrent()
    {
        $hydrator = new ClassMethods();
        $vars = $hydrator->extract($this);

        foreach ($vars as $key => &$value) {
            if ($value===null || (is_object($value) && !$value instanceof IsNull) || is_array($value)) {
                unset($vars[$key]);
            } elseif ( is_object($value) && $value instanceof IsNull ) {
                $vars[$key] = null;
            }
        }

        return $vars;
    }

    public function allParent()
    {
        return (null !== $this->parent_model) ? $this->parent_model->allParent() . '_' . $this->prefix : $this->prefix;
    }

    public function toArrayKeys()
    {
        $array = get_object_vars($this);
        unset($array['service_manager']);
        unset($array['prefix']);

        return array_keys($array);
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    protected function getStaticUrl()
    {
        return ($this->getServiceLocator()) ? $this->getServiceLocator()->get('config')['path_static'] : null;
    }

    protected function getDmsUrl()
    {
        return ($this->getServiceLocator()) ? $this->getServiceLocator()->get('config')['path_dms'] : null;
    }

    /**
     * @see \Zend\ServiceManager\ServiceLocatorAwareInterface::setServiceLocator()
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->service_locator = $serviceLocator;

        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        if (null === $this->service_locator && null !== $this->parent_model) {
            $this->service_locator = $this->parent_model->getServiceLocator();
        }

        return $this->service_locator;
    }

    /**
     * Convert to string
     * @return string
     */
    public function __toString()
    {
        return get_class($this);
    }

}
