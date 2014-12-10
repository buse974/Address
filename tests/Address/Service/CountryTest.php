<?php

namespace AddressTest\Service;

use Address\Service\Country;
use DalTest\bootstrap;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class CountryTest extends AbstractHttpControllerTestCase
{
	public function setUp()
	{
		$this->setApplicationConfig(
			include __DIR__ . '/../../config/application.config.php'
		);
		
		parent::setUp();
	}
	
	public function testGetServiceGeoloc()
	{
		$serviceManager = $this->getApplication()->getServiceManager();
	
		$serviceManager->setAllowOverride(true);
		$serviceManager->setService('geoloc', 'geoloc');
		 
		$country = $serviceManager->get('addr_service_country');
		 
		$this->assertEquals('geoloc', $country->getServiceGeoloc());
	}

    public function testGetList()
    {
    	$serviceManager = $this->getApplication()->getServiceManager();
    	 
    	$m_mapper = $this->getMockBuilder('Address\Mapper\Country')
    	                 ->setMethods(array('usePaginator','getList','count'))
    	                 ->disableOriginalConstructor()
    	                 ->getMock();
    	 
    	$m_mapper->expects($this->once())
    	         ->method('usePaginator')
    	         ->with(array())
    	         ->will($this->returnSelf());
    	
    	$m_mapper->expects($this->once())
    	         ->method('getList')
    	         ->with(array())
    	         ->will($this->returnValue('resultset'));
    	
    	$m_mapper->expects($this->once())
    	         ->method('count')
    	         ->will($this->returnValue(6));
    	 
    	$serviceManager->setAllowOverride(true);
    	$serviceManager->setService('addr_mapper_country', $m_mapper);
    	
    	$country = $serviceManager->get('addr_service_country');
    	
    	$out = $country->getList();
    	
    	$this->assertArrayHasKey('count', $out);
    	$this->assertArrayHasKey('results', $out);
    	
    	$this->assertEquals(6, $out['count']);
    	$this->assertEquals('resultset', $out['results']);
    }

    public function testGetCountryByArrayId()
    {
    	$serviceManager = $this->getApplication()->getServiceManager();
    	
    	$m_service_country = $this->getMockBuilder('Address\Service\Country')
    					          ->setMethods(array('getCountryById'))
    	                          ->disableOriginalConstructor()
    	                          ->getMock();
    	
    	$m_service_country->expects($this->once())
    	                  ->method('getCountryById')
    	                  ->will($this->returnValue('obj_country'));
    	
    	$serviceManager->setAllowOverride(true);
    	$serviceManager->setService('addr_service_country', $m_service_country);
    	
    	$country = $serviceManager->get('addr_service_country');
    	
    	$this->assertEquals('obj_country', $country->getCountry(array('id' => 1)));
    }
    
    public function testGetCountryByIdWhitoutArray()
    {
    	$serviceManager = $this->getApplication()->getServiceManager();
    	 
    	$m_service_country = $this->getMockBuilder('Address\Service\Country')
    	                          ->setMethods(array('getCountryById'))
    	                          ->disableOriginalConstructor()
    	                          ->getMock();
    	 
    	$m_service_country->expects($this->once())
    	                  ->method('getCountryById')
    	                  ->will($this->returnValue('obj_country'));
    	 
    	$serviceManager->setAllowOverride(true);
    	$serviceManager->setService('addr_service_country', $m_service_country);
    	 
    	$country = $serviceManager->get('addr_service_country');
    	 
    	$this->assertEquals('obj_country', $country->getCountry(1));
    }
    
    public function testGetCountryByNameWihtoutArray()
    {
    	$serviceManager = $this->getApplication()->getServiceManager();
    
    	$m_service_country = $this->getMockBuilder('Address\Service\Country')
    	                          ->setMethods(array('getCountryByName'))
    	                          ->disableOriginalConstructor()
    	                          ->getMock();
    
    	$m_service_country->expects($this->once())
    	                  ->method('getCountryByName')
    	                  ->will($this->returnValue('obj_country'));
    
    	$serviceManager->setAllowOverride(true);
    	$serviceManager->setService('addr_service_country', $m_service_country);
    
    	$country = $serviceManager->get('addr_service_country');
    
    	$this->assertEquals('obj_country', $country->getCountry('name'));
    }
    
    public function testGetCountryByArrayName()
    {
    	$serviceManager = $this->getApplication()->getServiceManager();
    
    	$m_service_country = $this->getMockBuilder('Address\Service\Country')
    	                          ->setMethods(array('getCountryByName'))
    	                          ->disableOriginalConstructor()
    	                          ->getMock();
    
    	$m_service_country->expects($this->once())
    	                  ->method('getCountryByName')
    	                  ->will($this->returnValue('obj_country'));
    
    	$serviceManager->setAllowOverride(true);
    	$serviceManager->setService('addr_service_country', $m_service_country);
    
    	$country = $serviceManager->get('addr_service_country');
    
    	$this->assertEquals('obj_country', $country->getCountry(array('name' => 'name')));
    }
    
    public function testGetCountryById()
    {
    	$serviceManager = $this->getApplication()->getServiceManager();
    	
    	$m_mapper = $this->getMockBuilder('Address\Mapper\Country')
    	                 ->setMethods(array('select','current'))
    	                 ->disableOriginalConstructor()
    	                 ->getMock();
    	
    	$m_mapper->expects($this->once())
    	         ->method('select')
    	         ->will($this->returnSelf());
    	 
    	$m_mapper->expects($this->once())
    	         ->method('current')
    	         ->will($this->returnValue('result'));
    	
    	$serviceManager->setAllowOverride(true);
    	$serviceManager->setService('addr_mapper_country', $m_mapper);
    	 
    	$country = $serviceManager->get('addr_service_country');
    	
    	$this->assertEquals('result', $country->getCountryById(1));
    }
    
    public function testGetCountryByName()
    {
    	$serviceManager = $this->getApplication()->getServiceManager();
    	 
    	$m_mapper = $this->getMockBuilder('\Address\Mapper\Country')
    	                 ->setMethods(array('getCountryByName','current','count'))
    	                 ->disableOriginalConstructor()
    	                 ->getMock();
    	 
    	$m_mapper->expects($this->once())
    	         ->method('getCountryByName')
    	         ->will($this->returnSelf());
    	
    	$m_mapper->expects($this->once())
    	         ->method('current')
    	         ->will($this->returnValue('result'));
    	
    	$m_mapper->expects($this->once())
    	         ->method('count')
    	         ->will($this->returnValue(1));
    	 
    	$serviceManager->setAllowOverride(true);
    	$serviceManager->setService('addr_mapper_country', $m_mapper);
    	
    	$country = $serviceManager->get('addr_service_country');
    	 
    	$this->assertEquals('result', $country->getCountryByName('france'));
    }
    
    public function testGetCountryByNameGeoloc()
    {
    	$serviceManager = $this->getApplication()->getServiceManager();
    
    	$m_mapper = $this->getMockBuilder('\Address\Mapper\Country')
    					 ->setMethods(array('getCountryByName','insert','count','getLastInsertValue'))
    					 ->disableOriginalConstructor()
    					 ->getMock();
    
    	$m_mapper->expects($this->once())
    			 ->method('getCountryByName')
    			 ->will($this->returnSelf());
    	 
    	$m_mapper->expects($this->once())
    			 ->method('insert')
    			 ->will($this->returnValue(1));
    	 
    	$m_mapper->expects($this->once())
    			 ->method('count')
    			 ->will($this->returnValue(0));
    
    	$m_mapper->expects($this->once())
    			 ->method('getLastInsertValue')
    			 ->will($this->returnValue(5));
    	
    	$serviceManager->setAllowOverride(true);
    	$serviceManager->setService('addr_mapper_country', $m_mapper);
    	 
    	$country = $serviceManager->get('addr_service_country');
    
    	$this->assertInstanceOf('\Address\Model\Country', $country->getCountryByName('france'));
    }
}
