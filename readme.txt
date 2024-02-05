 

 1 - install database in folder /database

 2 - set up settings.php in /site/default/settings.php 

       $databases['default']['default'] = array (
		  'database' => 'dashboard_transfer',
		  'username' => 'root',
		  'password' => 'root',
		  'prefix' => '',
		  'host' => 'localhost',
		  'port' => '3306',
		  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
		  'driver' => 'mysql',
		  'autoload' => 'core/modules/mysql/src/Driver/Database/mysql/'
		);


  3- custom is stripe_custom 

        service  is here  /stripe_custom/src/StripeCustomService.php	
        config service : stripe_custom.services.yml


        page is here stripe_custom\Controller\StripeCustomController  
        route config for page is  stripe_custom.routing.yml 