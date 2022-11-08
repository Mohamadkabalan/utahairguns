<?php

class RRMainApi
{
    /**
     * RRMainApi constructor.
     * @param tad_DI52_Container $container
     */
    public function __construct($container)
    {
        $controller = new RRApiFullCalendar($container['db_models'], $container['options'], $container['mail']);
        $controller->register_routes();

        $logController = new RRLogActions($container['db_models']);
        $logController->register_routes();

        $gdpr = new RRGDPRActions($container['db_models']);
        $gdpr->register_routes();

        $closure = new RRClosureActions($container['db_models'], $container['options']);
        $closure->register_routes();

        $location = new RRLocations($container['db_models'], $container['options']);
        $location->register_routes();

        $lanes = new RRLanes($container['db_models'], $container['options']);
        $lanes->register_routes();

        $bays = new RRBays($container['db_models'], $container['options']);
        $bays->register_routes();

        $schedules = new RRSchedules($container['db_models'], $container['options']);
        $schedules->register_routes();

        $reservations = new RRReservations($container['db_models'], $container['options']);
        $reservations->register_routes();

        $userRoles = new UserRoles($container['db_models'], $container['options']);
        $userRoles->register_routes();

    }

}