<?php

/*
 * ------------------------------------------------------------------------
 * GLPI Plugin MantisBT
 * Copyright (C) 2014 by the GLPI Plugin MantisBT Development Team.
 *
 * https://forge.indepnet.net/projects/mantis
 * ------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI Plugin MantisBT project.
 *
 * GLPI Plugin MantisBT is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * GLPI Plugin MantisBT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI Plugin MantisBT. If not, see <http://www.gnu.org/licenses/>.
 *
 * ------------------------------------------------------------------------
 *
 * @package GLPI Plugin MantisBT
 * @author Stanislas Kita (teclib')
 * @co-author François Legastelois (teclib')
 * @co-author Le Conseil d'Etat
 * @copyright Copyright (c) 2014 GLPI Plugin MantisBT Development team
 * @license GPLv3 or (at your option) any later version
 * http://www.gnu.org/licenses/gpl.html
 * @link https://forge.indepnet.net/projects/mantis
 * @since 2014
 *
 * ------------------------------------------------------------------------
 */
include ('../../../inc/includes.php');

$mantis = new PluginMantisMantis ();

if (isset ( $_GET ['action'] ) && $_GET ['action'] == 'linkToIssue') {
   
   Html::popHeader ( 'Mantis', $_SERVER ['PHP_SELF'] );
   $mantis->getFormForLinkGlpiTicketToMantisTicket ( $_GET ['idTicket'], $_GET ['itemType'] );
   Html::popFooter ();
} else if (isset ( $_GET ['action'] ) && $_GET ['action'] == 'linkToProject') {
   
   Html::popHeader ( 'Mantis', $_SERVER ['PHP_SELF'] );
   $mantis->getFormForLinkGlpiTicketToMantisProject ( $_GET ['idTicket'], $_GET ['itemType'] );
   Html::popFooter ();
} else if (isset ( $_GET ['action'] ) && $_GET ['action'] == 'deleteIssue') {
   Html::popHeader ( 'Mantis', $_SERVER ['PHP_SELF'] );
   
   $id_link = $_GET ['id'];
   $id_ticket = $_GET ['idTicket'];
   $id_mantis = $_GET ['idMantis'];
   
   $mantis->getFormToDelLinkOrIssue ( $id_link, $id_ticket, $id_mantis, $_GET ['itemType'] );
   Html::popFooter ();
}
