<?php

$config = \App\Config::getInstance();
$routes = $config->getRouteCollection();
if (!$routes) return;

/** @var \Composer\Autoload\ClassLoader $composer */
$composer = $config->getComposer();
if ($composer)
    $composer->add('Tk\\Ev\\', dirname(__FILE__));


$routes->add('events-listing', new \Tk\Routing\Route('/events', 'Tk\Ev\Controller\Listing::doDefault', array()));

$routes->add('events-admin-settings', new \Tk\Routing\Route('/admin/eventsSettings.html', 'Tk\Ev\Controller\Settings::doDefault'));
$routes->add('events-admin-manager', new \Tk\Routing\Route('/admin/eventManager.html', 'Tk\Ev\Controller\Event\Manager::doDefault'));
$routes->add('events-admin-edit', new \Tk\Routing\Route('/admin/eventEdit.html', 'Tk\Ev\Controller\Event\Edit::doDefault'));
$routes->add('events-admin-view', new \Tk\Routing\Route('/admin/eventView.html', 'Tk\Ev\Controller\MailLog\View::doDefault'));





