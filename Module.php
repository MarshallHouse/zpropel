<?php

namespace Zpropel;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
     public function init(ModuleManager $moduleManager) {
        // Include the main Propel script
        require_once 'vendor/propel/propel1/runtime/lib/Propel.php';

        // Add the generated 'classes' directory to the include path
        set_include_path("data/zpropel/proxy/build/classes" . PATH_SEPARATOR . get_include_path());
     }
     
     public function onBootstrap(MvcEvent $e) {
        $serviceManager = $e->getApplication()->getServiceManager();
        $config = $serviceManager->get('Config');
        
        // Set up static service/event manager manager
        require_once('src/Zpropel/Model/StaticManager.php');
        Model\StaticManager::setServiceLocator($serviceManager);
        $e = $serviceManager->get('SharedEventManager');
        Model\StaticManager::getEventManager()->setSharedManager($e);
        
        // Initialize Propel with the runtime configuration
        $runtime_conf = $config['zpropel']['runtime-conf'];
        if (file_exists($runtime_conf)) {
            \Propel::init($runtime_conf);
        }
     }

     public function getAutoloaderConfig() {
         return array(
             'Zend\Loader\StandardAutoloader' => array(
                 'namespaces' => array(
                     __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                 ),
             ),
         );
     }

     public function getConfig() {
         return array(
            'zpropel' => array(
                'runtime-conf' => 'data/zpropel/proxy/build/conf/zpropel-conf.php',
            ),
            'console' => array(
                'router' => array(
                    'routes' => array(
                        'gen' => array(
                            'options' => array(
                                'route'    => 'propel-gen [convert-conf|insert-sql|sql|om]:script',
                                'defaults' => array(
                                    'controller' => 'Zpropel\Controller\Index',
                                    'action'     => 'gen'
                                )
                            )
                        ),
                    ),
                ),
            ),
            'controllers' => array(
                'invokables' => array(
                    'Zpropel\Controller\Index' => 'Zpropel\Controller\IndexController',
                ),
            ),
         );
     }
}
