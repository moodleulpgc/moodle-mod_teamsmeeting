<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     mod_teamsmeeting
 * @category    string
 * @copyright   2020 Enrique Castro <@ULPGC>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addrecording'] = 'Añadir grabación';

$string['backtocourse'] = 'Volver al curso';
$string['backtomodule'] = 'Volver';
$string['confirmrecordingdelete'] = 'Ha solicitado eliminar en enlace a la Grabación "{$a}" (La grabación en MS-Stream NO será borrada). <br /> 
¿Desea quitar este enlace? ';
$string['editrecording'] = 'Editar grabación';
$string['recording'] = 'Grabación';
$string['recording_help'] = 'La página de Añadir/Editar Grabaciones permite agregar enlaces a video-grabaciones ya existentes en MS-Stream, coo una url a dicho video. 

Debe proporcionar un nombre y opcionalmente una descripción para identificar la Grabación.
Los participantes podrán visualizar el vídeo en cualquier momento, salvo si se have invisible a los entudiantes. 

Por favor, verifique que el video de MS-Stream dispone de los permisos adecuados para ser visualizado por los participantes. 
El Campus moodle NO puede gestionar o sortear los permisos de MS-Stream. ';
$string['recordingmodified'] = '(modificado en {$a})';
$string['recordingsaved'] = 'La Grabación se ha añadido o modificado con éxito.';
$string['recordingnotsaved'] = 'No guardado. Los cambios en "{&a}" han falaldo y no se ha guardado el cambio. ';
$string['recoding_nonmsstream_error'] = 'URL ivnálida para MS-Stream. 
Por favor, añada una url de tipo microsoftstream.com ';
$string['recordings'] = 'Grabaciones';
$string['recordingname'] = 'Nombre';
$string['recordingname_help'] = 'Un nombre identificativo para esta vídeo-grabación en particular.';
$string['recordingurl'] = 'url en MS-Stream';
$string['recordingurl_help'] = 'La url en MS-Stream de la videograbación. Simplemenet copie-pegue la url del vídeo en MS-Stream. 

Por favor, asegúrese de que la entrada en MS-Stream dispone de los permisos adecuados para ser visualizado por los participantes. 
El Campus moodle NO puede gestionar o sortear los permisos de MS-Stream. ';
$string['allowedpresenters'] = 'Presentadores';
$string['allowedpresenters_help'] = 'Quien puede actuar como Moderador o Presentador 
inicialmente en la videoconferencia. 
    
 * Profesores del curso: Todos los profesores de la asignatura son Presentadores predeterminados.
 * Profesores del grupo: Los profesores del grupo son Presentadores predeterminados (incluyendo los que pueden acceder a todos los grupos).
 * Organizador: Solo el profesor creador de la videoconferencia en un Presentador inicialmente.
';


$string['accessasuser'] = 'Usuarios como Organizadores';
$string['accessasuser_desc'] = 'If enabled, then meetings will be created with a moodle user as Organizer. 
The user must have access to o365, and a user token must be provided (Explicit o365 login or credential renewal).';
$string['o365accessmode'] = 'o365 Access mode';
$string['o365accessmode_desc'] = 'Teamsmeeting create "institutional" o365 onlineMeetings preferentially. 
This is, the meeting organizer is an institutional user for all meetings. 
Once created, an o365 onlineMeetings can only be updated by the Organizer user. 
This is is a problem in sites where courses have frequently several teachers.

Institutional access needs a definite user in o365 with Privileges and Policies applied to manage onlineMeetings across organization tenant.
The Organizer user can update any onlineMeetings created by Teamsmeeting from moodle module instance updated by any course teacher. 
The meeting is thus a course meeting rather than a personal one.  
Teachers are added as Presenters to the meeting and has full presentation capacities during video meeting.  

If courses have only one teacher, and the same user will manage course meetings, 
or if you prefer explicit user authorization and personal meeting ownership, 
the access mode may be set to a user-based one. Enabling that mode hides the Organizer user settings.
';
$string['operationsettings'] = 'Opcioiones de operación';
$string['organizeruser'] = 'Usuario Organizador';
$string['organizeruser_desc'] = 'If filled, all teamsmeetings are created in o365 with this System user as "Organizer". 
This parameter is an azure ID for a user with Privileges and Policies manage onlineMeetings.';
$string['presenterany'] = 'Todos';
$string['presenterall'] = 'Miembros de la Institución ';
$string['presenterbychannel'] = 'Profesores del Canal';
$string['presenterbycourse'] = 'Profesores del curso';
$string['presenterbygroup'] = 'Profesores del grupo';
$string['presenterbyrole'] = 'Profesores del curso/grupo';
$string['organizeronly'] = 'Solo organizador';
$string['lobbybypass'] = 'Aceso sin Sala de Espera';
$string['lobbybypass_help'] = 'Algunos usuarios pueden acceder a la videoconferencia directamente, 
sin quedar colocados en una Sala de Espera virtual hasta que se les de paso.

 * Organizador: Solo el Organizador accede directamente, todos los demás usuarios quedan en la Sala de Espera y deben ser autorizados.
 * Miembros de Institución: Los usuarios que son miembros de la misma Intitución que el Organizador acceden directamente sin quedar retenidos en la Sala de Espera.
 * Intitución y Colaboradoras: los miembros de la Institución y otras Instituciones de confianza (definido por el Administrador de Office365).
 * Todos: todos los usuarios, de cualquier procedencia, acceden directamente sin ser retenidos en la Sala de Espera. 
';
$string['lobbynonorg'] = 'Miembros de la Institución';
$string['lobbynonfed'] = 'Miembros de Institución y Colaboradoras';
$string['lobbynone'] = 'Todos los usuarios';
$string['joinannounce'] = 'Publicita entradas y salidas';
$string['joinannounce_help'] = 'Si se anuncia a todos cuando un usuario entra o sale de la videoconferencia.';

$string['pluginname'] = 'Videoconferencia MS-Teams';
$string['pluginadministration'] = 'Administración de videoconferencia MS-Teams';
$string['teamsmeeting:addinstance'] = 'Añadir nueva Videoconferencia Teams';
$string['teamsmeeting:create'] = 'Crear conferencias en Office365';
$string['teamsmeeting:join'] = 'Unirse a la reunión';
$string['teamsmeeting:managerecordings'] = 'Gestionar grabaciones';

$string['closingtimeempty'] = 'La fecha de Finalización debe ser especificada si se indica una fecha de Inicio.';
$string['closingtimeearly'] = 'La fecha de Finalización debe ser posterior  ala fecha de Incio.';
$string['eventmeetingjoined'] = 'Se unió a Conferencia MS-Teams';
$string['forgroup'] = 'Para el grupo {$a}: ';
$string['groupsnoone'] = 'La videoconferencia se ha configurado para usar Grupos pero usted no pertenece a ningún grupo en este contexto. ';
$string['groupsmultiple'] = 'Hay videoconferencias separadas para cada grupo individual y usted tiene acceso a varios. 
Por favor, verifique que selecciona el grupo adecuado al propósito y audiencia deseados.';
$string['groupsnotvisible'] = 'Los estudiantes no pueden acceder a "Todos los participantes" en el modo de Grupos Separados.';

$string['waitteachers'] = 'Esperar a los profesores para entrar';
$string['modulename'] = 'Videoconf. MS-Teams';
$string['modulenameplural'] = 'Videoconferencia MS-Teams';

$string['teacher'] = 'Profesor';
$string['joinurllink'] = 'Enlace a Teams';
$string['joinbutton'] = 'Unirse a videoconferencia';
$string['createbutton'] = 'Crear videoconferencia';
$string['updatebutton'] = 'Actualizar videoconferencia';

$string['notifytime'] = 'Antelación de recordatorios';
$string['notifytime_help'] = 'Si es diferente de cero, la antelación con la que se enviarán recordatorios de la reunión a los participantes';

$string['meetingclosed'] = 'Videoconferencia finalizada el {$a}.';
$string['meetingcloseson'] = 'La videoconferencia finalizará el {$a}.';
$string['meetingopenedon'] = 'La videoconferencia comenzó el {$a}.';
$string['meetingnotavailable'] = 'Cideoconferencia no disponible hasta el {$a}.';
$string['memberauto'] = 'Automático';
$string['membermanual'] = 'Manual';
$string['membership'] = 'Audiencia';
$string['membership_help'] = 'Cómo se especifica a quien va dirigida la videoconferencia, 
quien puede participar o unirse a la misma. 

 * Auto: Controlado directamente por la matriculación en asignatura o grupos.
 * Manual: El profesor puede especificar qué usuarios pueden participar, esto es aquellos que invita. 
';

$string['override'] = 'Excepción de grupo';
$string['overrideadded'] = 'Se ha agregado una excepción para el grupo {$a}';
$string['overridedeleted'] = 'Se ha eliminado una excepción para el grupo {$a}';
$string['overrideerror'] = 'Se he producido un error al tratar de agregar, actualizar o borrar una excepción para el grupo {$a}. 
No se ha modificado ningún elemento.';
$string['overrideexisting'] = 'Ya existe una excepción para el grupo {$a}. No se ha añadido una nueva, deben ser únicas para cada grupo.';
$string['overrideaddnew'] = 'Agregar nueva excepción para grupo';
$string['overrideupdated'] = 'Se ha actualizado la excepción para el grupo {$a}.';
$string['overridedelconfirm'] = 'Ha solicitado eliminar la excepción paar el grupo {$a}. <br />
Esta acción también eliminará el código de sesión de e-Tutor, si es posible. <br />
¿Desea continuar?';
$string['overrideinactivehelp'] = 'Excepción inactiva';
$string['overridenot'] = 'No hay excepciones guardadas';
$string['overridesaveandstay'] = 'Guardar y crear otra nueva';

$string['overrideslink'] = 'Excepciones para grupos';
$string['overridesexplain'] = 'Las fechas de reunión se pueden especificar para cada grupo en {$a}.';
$string['reverttodefault'] = 'Volver a las fechas por defecto';
$string['notworkingo365'] = 'La conexión a Office365 NO está configurada, no es posible gestionar videoconferencias MS-Teams.';
$string['notokeno365'] = 'La conexion con Office365 ha caducado. 
Debe <strong>{$a}</strong> para poder gestionar videoconferencias. 
También puede renovar la conexión usando el bloque Microsoft del panel lateral. ';
$string['refreshtoken'] = 'renovar la conexión con Office365';

$string['teamsmeetingheader'] = 'Opciones de la Videoconferencia';
$string['teamsmeetingname'] = 'Nombre de videoconferencia';
$string['teamsmeetingname_help'] = "Este es el nombre que aparecerá en la página del curso 
y en la página de acceso a la videoreunión Teams en Office365.

Por favor, no use en este nomber caracteres como '\', que son borrados por por Office365, 
si quiere prservar la conistencia de nombres en ambos sistemas.";

$string['waitformoderator'] = 'Por favor, espere a que un profesor se una y active la reunión';
$string['waitforcreation'] = 'La reunión no está disponible en Office365. Por favor, espere a que la reunión sea creada en o365.';
$string['waitmoderator'] = 'Esperar al profesor';
$string['waitmoderator_help'] = 'Si se especifica un valor, los estudiantes deben esperar al profesor. 
Solo podrán entrar <strong>después</strong> de que un Profesor se haya unido a la reunión. Y solo durante el plazo indicado. 

Si se deja en 0 los estudiantes podrán unirse a la reunión en cualquier momento. 

Si se indica un valor, los estudiantes solo podrán entrar después del profesor y antes del plazo indicado. 
Después del plazo indicado los estudiantes tampoco podrán acceder a una reunión aunque ya esté en marcha. 
Es equivalente a cerrar la puerta del aula.';
$string['waitpastdue'] = 'Está fuera de plazo para unirse a la videoconferencia. 
Por favor, trate unirse a la sesión dentro del plazo establecido la próxima vez.';
$string['useafterwards'] = 'Keep available after closing';
$string['useafterwards_help'] = 'If enabled, the meeting will remain available for joining again while Office365 keeps the meeting live (usually 2 months).';
