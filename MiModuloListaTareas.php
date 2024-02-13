<?php
// Definición del nombre del módulo
define('MODULE_NAME', 'MiModuloListaTareas');

// Función para registrar el módulo en Dolibarr
function init_module() {
    global $db;

    // Crear la tabla para almacenar las tareas
    $sql = "CREATE TABLE IF NOT EXISTS `".MAIN_DB_PREFIX."mymodulotasklist` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `titulo` varchar(255) NOT NULL,
        `descripcion` text NOT NULL,
        `fecha_creacion` datetime NOT NULL,
        `fecha_vencimiento` datetime NOT NULL,
        `estado` int(1) NOT NULL DEFAULT 0,
        `usuario_creacion` int(11) NOT NULL,
        `usuario_asignado` int(11) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $db->query($sql);

    // Registrar el menú del módulo
    $menu = new Menu(MODULE_NAME, 'Mi Módulo Lista de Tareas', 'mymodulotasklist_index');
    $menu->addChild('Lista de Tareas', 'mymodulotasklist_list');
    $menu->addChild('Crear Tarea', 'mymodulotasklist_create');
    $menu->attachToMenu('Home');

    // Registrar las acciones del módulo
    hook_register('index', 'mymodulotasklist_index');
    hook_register('list', 'mymodulotasklist_list');
    hook_register('create', 'mymodulotasklist_create');
    hook_register('action', 'mymodulotasklist_action');

    return true;
}

// Función para mostrar la página de inicio del módulo
function mymodulotasklist_index() {
    echo '<p>Bienvenido al módulo de lista de tareas.</p>';
    echo '<p>Aquí puedes crear, editar y eliminar tareas.</p>';
    echo '<p><a href="'.dol_buildpath('mymodulotasklist', 'list').'">Ver lista de tareas</a></p>';
    echo '<p><a href="'.dol_buildpath('mymodulotasklist', 'create').'">Crear nueva tarea</a></p>';
}

// Función para mostrar la lista de tareas
function mymodulotasklist_list() {
    global $db;

    // Obtener las tareas del usuario actual
    $sql = "SELECT * FROM `".MAIN_DB_PREFIX."mymodulotasklist` WHERE `usuario_creacion` = ".$_SESSION['user']->id;
    $result = $db->query($sql);

    // Mostrar una tabla con las tareas
    echo '<table border="1">';
    echo '<tr><th>ID</th><th>Título</th><th>Descripción</th><th>Fecha Creación</th><th>Fecha Vencimiento</th><th>Estado</th><th>Usuario Asignado</th><th>Acciones</th></tr>';
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>'.$row['id'].'</td>';
        echo '<td>'.$row['titulo'].'</td>';
        echo '<td>'.$row['descripcion'].'</td>';
        echo '<td>'.$row['fecha_creacion'].'</td>';
        echo '<td>'.$row['fecha_vencimiento'].'</td>';
        echo '<td>'.($row['estado'] == 1 ? 'Completada' : 'Pendiente').'</td>';
        echo '<td>'.$row['usuario_asignado'].'</td>';
        echo '<td><a href="'.dol_buildpath('mymodulotasklist', 'action', array('do' => 'edit', 'id' => $row['id'])).'">Editar</a> | <a href="'.dol_buildpath('mymodulotasklist', 'action', array('do' => 'delete', 'id' => $row['id'])).'">Eliminar</a></td>';
        echo '</tr>';
    }
    echo '</table>';
}

// Función para mostrar el formulario de creación de tareas
function mymodulotasklist_create() {
    global $db;

    // Si se ha enviado el formulario para crear una nueva tarea
    if (isset($_POST['do']) && $_POST['do'] == 'create') {
        // Validar los datos del formulario
