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

if (isset ( $_POST ['action'] )) {
   
   global $CFG_GLPI;
   
   switch ($_POST ['action']) {
      
      // TEST DE LA CONNECTIVITE A MANTIS CONNECT
      case 'testConnexionMantisWS' :
         error_reporting ( 0 );
         $ws = new PluginMantisMantisws ();
         try {
            $res = $ws->testConnectionWS ( $_POST ['host'], $_POST ['url'], $_POST ['login'], $_POST ['pwd'] );
            if ($res) {
               echo "<img src='" . $CFG_GLPI ['root_doc'] . "/plugins/mantis/pics/check24.png'/>";
            } else {
               echo "<img src='" . $CFG_GLPI ['root_doc'] . "/plugins/mantis/pics/cross24.png'/>Access denied";
            }
         } catch ( Exception $e ) {
            echo "<img src='" . $CFG_GLPI ['root_doc'] . "/plugins/mantis/pics/cross24.png'/>Error IP or Path";
         }
         break;
      
      // FIND ISSUE BY ID
      case 'findIssueById' :
         $ws = new PluginMantisMantisws ();
         $ws->initializeConnection ();
         $res = $ws->existIssueWithId ( $_POST ['id'] );
         if ($res)
            echo "<img src='" . $CFG_GLPI ['root_doc'] . "/plugins/mantis/pics/check24.png' />";
         else
            echo "<img src='" . $CFG_GLPI ['root_doc'] . "/plugins/mantis/pics/cross24.png'/>";
         break;
      
      // FIND PROJECT BY NAME
      case 'findProjectByName' :
         $ws = new PluginMantisMantisws ();
         $ws->initializeConnection ();
         $res = $ws->existProjectWithName ( $_POST ['name'] );
         if ($res)
            echo "<img id='resultImg' src='" . $CFG_GLPI ['root_doc'] . "/plugins/mantis/pics/check24.png' />";
         else
            echo "<img id='resultImg' src='" . $CFG_GLPI ['root_doc'] . "/plugins/mantis/pics/cross24.png'/>";
         break;
      
      // GET MANTIS STATE
      case 'getStateMantis' :
         
         $ws = new PluginMantisMantisws ();
         $ws->getConnexion ( $_POST ['host'], $_POST ['url'], $_POST ['login'], $_POST ['pwd'] );
         $result = $ws->getStateMantis ();
         
         if (! $result) {
            echo false;
         } else {
            $states = "";
            $i = 0;
            foreach ( $result as &$state ) {
               if ($i == 0)
                  $states .= $state->name;
               else
                  $states .= "," . $state->name;
               $i ++;
            }
            echo $states;
         }
         break;
      
      // GET ATTACHMENT SENDING
      case 'getTicketAttachment' :
         
         $id_ticket = $_POST ['idTicket'];
         $itemType = $_POST ['itemType'];
         
         $ticket = new $itemType ();
         $ticket->getFromDB ( $id_ticket );
         
         $output .= getOutPutForticket ( $ticket, $itemType );
         
         if ($itemType != 'Problem') {
            $tickets = Ticket_Ticket::getLinkedTicketsTo ( $id_ticket );
            if (count ( $tickets )) {
               $output .= "<br/><DL><DT><STRONG>" . __ ( 'If you fowllow linked tickets', 'mantis' ) . "</STRONG><br>";
               foreach ( $tickets as $link_ticket ) {
                  $ticketLink = new Ticket ();
                  $ticketLink->getFromDB ( $link_ticket ['tickets_id'] );
                  $output .= getOutPutForticket ( $ticketLink, $itemType );
               }
            } else {
               $output .= "<DL><DT><STRONG>" . __ ( 'No tickets linked', 'mantis' );
            }
         }
         
         if ($output == "") {
            echo "<STRONG>" . __ ( 'No documents attached', 'mantis' ) . "<STRONG/>";
         } else {
            echo $output;
         }
         break;
      
      // GET CATEGORIE FROM PROJECT MANTIS
      case 'getCategoryFromProjectName' :
         $ws = new PluginMantisMantisws ();
         $ws->initializeConnection ();
         $result = $ws->getCategoryFromProjectName ( $_POST ['name'] );
         if (! $result)
            echo false;
         else
            echo json_encode ( $result );
         break;
      
      // GET ACTOR FROM PROJECT
      case 'getActorByProjectname' :
         $ws = new PluginMantisMantisws ();
         $ws->initializeConnection ();
         $result = $ws->getActorFromProjectName ( $_POST ['name'] );
         if (! $result)
            echo false;
         else
            echo json_encode ( $result );
         break;
      
      case 'getProjectName' :
         
         $idItem = $_POST ['idTicket'];
         $id_mantis_issue = $_POST ['idMantis'];
         $itemType = $_POST ['itemType'];
         
         $ws = new PluginMantisMantisws ();
         $ws->initializeConnection ();
         
         if (! $ws->existIssueWithId ( $id_mantis_issue )) {
            echo "ERROR :" . __ ( "MantisBT issue does not exist", "mantis" );
         } else {
            
            $mantis = new PluginMantisMantis ();
            // on verifie si un lien est deja creé
            if ($mantis->IfExistLink ( $idItem, $id_mantis_issue, $itemType )) {
               echo "<img src='" . $CFG_GLPI ['root_doc'] . "/plugins/mantis/pics/warning24.png'/> ERROR :" . __ ( "This Glpi item is already linked to this MantisBT ticket", "mantis" );
            } else {
               $result = $ws->getIssueById ( $id_mantis_issue );
               if ($result->status->id == 90) {
                  echo "ERROR :" . __ ( '"This issue is closed"', 'mantis' );
               } else {
                  echo $result->project->name;
               }
            }
         }
         break;
      
      // GET CUSTOM FIELD FROM PROJECT
      case 'getCustomFieldByProjectname' :
         $ws = new PluginMantisMantisws ();
         $ws->initializeConnection ();
         $result = $ws->getCustomFieldFromProjectName ( $_POST ['name'] );
         if (! $result)
            echo false;
         else
            echo json_encode ( $result );
         break;
      
      case 'LinkIssueGlpiToIssueMantis' :
         
         $id_ticket = $_POST ['items_id'];
         $id_mantis_issue = $_POST ['idMantis'];
         $itemType = $_POST ['itemtype'];
         
         $ws = new PluginMantisMantisws ();
         $ws->initializeConnection ();
         
         // on verifie que l'id du ticket mantis existe
         if (! $ws->existIssueWithId ( $id_mantis_issue )) {
            echo __ ( "MantisBT issue does not exist", "mantis" );
         } else {
            $mantis = new PluginMantisMantis ();
            // on verifie si un lien est deja creé
            if ($mantis->IfExistLink ( $id_ticket, $id_mantis_issue, $itemType )) {
               echo "<img src='" . $CFG_GLPI ['root_doc'] . "/plugins/mantis/pics/warning24.png'/>" . __ ( "This Glpi item is already linked to this MantisBT ticket", "mantis" );
            } else {
               
               $result = $ws->getIssueById ( $id_mantis_issue );
               if ($result->status->id == 90) {
                  echo "ERROR :" . __ ( '"This issue is closed"', 'mantis' );
               } else {
                  
                  $issue = new PluginMantisIssue ();
                  $res = $issue->addInfoToIssueMantis ( $id_ticket, $id_mantis_issue );
                  
                  if ($res) {
                     $mantis->add ( $_POST );
                     
                     $id_ticket = $_POST ['items_id'];
                     $ticket = new Ticket ();
                     $ticket->getFromDB ( $id_ticket );
                     
                     $conf = new PluginMantisConfig ();
                     $conf->getFromDB ( 1 );
                     
                     if ($_POST ['linkedTicket'] == 'true') {
                        
                        $tickets = Ticket_Ticket::getLinkedTicketsTo ( $id_ticket );
                        
                        foreach ( $tickets as $link_ticket ) {
                           $t = new ticket ();
                           $t->getFromDB ( $link_ticket ['tickets_id'] );
                           
                           $mantis1 = new PluginMantisMantis ();
                           $post ['items_id'] = $t->fields ['id'];
                           $post ['idMantis'] = $id_mantis_issue;
                           $post ['dateEscalade'] = $_POST ['dateEscalade'];
                           $post ['itemtype'] = $_POST ['itemType'];
                           $post ['user'] = $_POST ['user'];
                           
                           $id_mantis [] = $mantis1->add ( $post );
                           unset ( $post );
                        }
                     }
                     
                     if ($conf->fields ['status_after_escalation'] != 0) {
                        $res = $ticket->update ( array (
                              'id' => $ticket->fields ['id'],
                              'status' => $conf->fields ['status_after_escalation'] 
                        ) );
                        
                        if ($_POST ['linkedTicket'] == 'true' && $_POST ['itemType'] == 'Ticket') {
                           $tickets = Ticket_Ticket::getLinkedTicketsTo ( $id_ticket );
                           
                           foreach ( $tickets as $link_ticket ) {
                              $t = new ticket ();
                              $t->getFromDB ( $link_ticket ['tickets_id'] );
                              $t->update ( array (
                                    'id' => $t->fields ['id'],
                                    'status' => $conf->fields ['status_after_escalation'] 
                              ) );
                           }
                        }
                     }
                     echo true;
                  } else {
                     echo $res;
                  }
               }
            }
         }
         break;
      
      case 'LinkIssueGlpiToProjectMantis' :
         $issue = new PluginMantisIssue ();
         echo $issue->linkisuetoProjectMantis ();
         break;
      
      case 'deleteLinkMantis' :
         
         $mantis = new PluginMantisMantis ();
         $ws = new PluginMantisMantisws ();
         $ws->initializeConnection ();
         
         $res = $mantis->delete ( $_POST );
         
         if ($res)
            echo true;
         else
            echo __ ( "Error while deleting the link between Glpi ticket and MantisBT ticket", "mantis" );
         break;
      
      case 'deleteIssueMantisAndLink' :
         
         $mantis = new PluginMantisMantis ();
         $ws = new PluginMantisMantisws ();
         $ws->initializeConnection ();
         
         if ($ws->existIssueWithId ( $_POST ['idMantis'] )) {
            
            if ($del = $ws->deleteIssue ( $_POST ['idMantis'] )) {
               
               $res = $mantis->delete ( $_POST );
               if ($res)
                  echo true;
               else
                  echo __ ( "Error while deleting the link between Glpi ticket and MantisBT ticket", "mantis" );
            } else {
               echo __ ( "Error while deleting the mantisBD ticket", "mantis" );
            }
         } else {
            echo __ ( "The MantisBT ticket does not exist", "mantis" );
         }
         break;
      
      default :
         echo 0;
   }
} else {
   echo 0;
}

function getOutPutForticket($ticket, $itemType) {
   global $DB;
   $conf = new PluginMantisConfig ();
   $conf->getFromDB ( 1 );
   
   if ($conf->fields ['doc_categorie'] == 0) {
      $res = $DB->query ( "SELECT `glpi_documents_items`.*
            FROM `glpi_documents_items` WHERE `glpi_documents_items`.`itemtype` = '" . $itemType . "'
            AND `glpi_documents_items`.`items_id` = '" . Toolbox::cleanInteger ( $ticket->fields ['id'] ) . "'" );
   } else {
      $res = $DB->query ( "SELECT `glpi_documents_items`.*
            FROM `glpi_documents_items` ,`glpi_documents` WHERE `glpi_documents`.`id` =`glpi_documents_items`.`documents_id` and `glpi_documents`.`documentcategories_id` = '" . Toolbox::cleanInteger ( $conf->fields ['doc_categorie'] ) . "' and`glpi_documents_items`.`itemtype`  = '" . $itemType . "'
            AND `glpi_documents_items`.`items_id` = '" . Toolbox::cleanInteger ( $ticket->fields ['id'] ) . "'" );
   }
   
   $output = "";
   if ($res->num_rows > 0) {
      $output .= "<DL><DT><STRONG>" . $itemType . " -> " . $ticket->fields ['id'] . "</STRONG><br>";
      while ( $row = $res->fetch_assoc () ) {
         $doc = new Document ();
         $doc->getFromDB ( $row ["documents_id"] );
         $output .= "<DD>" . $doc->getDownloadLink ( '', strlen ( $doc->fields ['filename'] ) ) . "<br>";
      }
      $output .= "</DL>";
   } else {
      $output .= "0 document for ticket " . $ticket->fields ['id'];
   }
   
   return $output;
}

