<?php
/******************************************************************************/
//                                                                            //
//                           InstantCMS v1.10.6                               //
//                        http://www.instantcms.ru/                           //
//                                                                            //
//                   written by InstantCMS Team, 2007-2015                    //
//                produced by InstantSoft, (www.instantsoft.ru)               //
//                                                                            //
//                        LICENSED BY GNU/GPL v2                              //
//                                                                            //
/******************************************************************************/

class p_demo_route extends cmsPlugin {

    public function __construct(){

        // Информация о плагине
        $this->info['plugin']      = 'p_demo_route';
        $this->info['title']       = 'Demo Plugin';
        $this->info['description'] = 'Пример плагина - для роутера /users/get_demo.html';
        $this->info['author']      = 'InstantCMS Team';
        $this->info['version']     = '1.10';

        // События, которые будут отлавливаться плагином
        $this->events[] = 'GET_ROUTE_USERS';
        $this->events[] = 'GET_USERS_ACTION_GET_DEMO';

        parent::__construct();

    }

// ==================================================================== //
    /**
     * Обработка событий
     * @param string $event
     * @param mixed $data
     * @return mixed
     */
    public function execute($event='', $data=array()){

        switch ($event){
            case 'GET_ROUTE_USERS': $data = $this->eventGetRoutes($data); break;
            case 'GET_USERS_ACTION_GET_DEMO': $data = $this->eventGetAction(); break;
        }

        return $data;

    }

// ==================================================================== //

    private function eventGetRoutes($routes) {

		// формируем массив по аналогии с router.php
		$add_routes[] = array(
					'_uri'  => '/^users\/get_demo.html$/i',
					'do'    => 'get_demo'
				 );

		// перебираем массив $add_routes, занося каждый в начало входного массива $routes
		foreach($add_routes as $route){
			array_unshift($routes, $route);
		}

        return $routes;

    }

// ==================================================================== //

    private function eventGetAction() {

		echo 'DEMO PLUGIN TEXT';

        return true;

    }

// ==================================================================== //

}
