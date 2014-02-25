<?php
/**
 * The Template for displaying all the dynamic sidebar
 *
 *
 * @package  WordPress
 * @subpackage  Timber
 */

$context = array();
$context['sidebar'] = Timber::get_widgets('actu-sidebar');
Timber::render(array('sidebar.twig'), $context);
