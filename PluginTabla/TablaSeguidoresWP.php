<?php
/*
Plugin Name: Resultados Torneo Robots
Description: Muestra y guarda los resultados del torneo de robots seguidores de línea.
Version: 1.1
Author: Nicolás
*/

register_activation_hook(__FILE__, 'crear_tabla_resultados');

function crear_tabla_resultados() {
    global $wpdb;
    $tabla = $wpdb->prefix . 'resultados_robots';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $tabla (
        id INT NOT NULL AUTO_INCREMENT,
        nombre_robot VARCHAR(100) NOT NULL,
        tiempo VARCHAR(20) NOT NULL,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

//Parte de JS
add_action('wp_enqueue_scripts', function () {
    wp_register_script('cronometro-js', plugins_url('cronometro.js', __FILE__), ['jquery'], '1.0', true);
});

//Reiniciar tabla
add_action('wp_ajax_reiniciar_tabla', 'reiniciar_tabla');
function reiniciar_tabla() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('No autorizado');
    }

    global $wpdb;
    $tabla = $wpdb->prefix . 'resultados_robots';
    $wpdb->query("TRUNCATE TABLE $tabla");

    if ($wpdb->last_error) {
        wp_send_json_error($wpdb->last_error);
    }

    wp_send_json_success('Tabla reiniciada');
}

//shortcode
add_shortcode('resultados_robots', 'mostrar_resultados_robots');

function mostrar_resultados_robots() {
    global $wpdb;
    $tabla = $wpdb->prefix . 'resultados_robots';

    //Formulario
    if (isset($_POST['nombre_robot']) && isset($_POST['tiempo'])) {
        $nombre = sanitize_text_field($_POST['nombre_robot']);
        $tiempo = sanitize_text_field($_POST['tiempo']);

        $wpdb->insert($tabla, [
            'nombre_robot' => $nombre,
            'tiempo' => $tiempo
        ]);
    }
        wp_enqueue_script('tr-cronometro', plugins_url('cronometro.js', __FILE__), array('jquery'), '1.0', true);
        wp_localize_script('tr-cronometro', 'tr_ajax_obj', [
            'ajax_url' => admin_url('admin-ajax.php')
        ]);

    ob_start();
    ?>
    <h2>Cronómetro</h2>
    <form method="post">
        <label for="nombre_robot">Nombre del robot:</label>
        <input type="text" name="nombre_robot" id="nombre_robot" required>
        <input type="text" name="tiempo" id="tiempo" readonly placeholder="00:00:00">
        <br><br>
        <div id="cronometro" style="font-size: 2em;">00:00:00</div>
        <button type="button" onclick="iniciarCronometro()">Iniciar</button>
        <button type="button" onclick="pararCronometro()">Parar</button>
        <button type="button" onclick="reiniciarTabla()">Reiniciar tabla</button>
        <button type="submit">Guardar resultado</button>
    </form>

    <br><h2>Resultados</h2>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>#</th>
            <th>Nombre del robot</th>
            <th>Tiempo</th>
            <th>Fecha</th>
        </tr>
        <?php
        $resultados = $wpdb->get_results("SELECT * FROM $tabla ORDER BY tiempo ASC");
        $i = 1;
        foreach ($resultados as $fila) {
            echo "<tr>
                    <td>{$i}</td>
                    <td>{$fila->nombre_robot}</td>
                    <td>{$fila->tiempo}</td>
                    <td>{$fila->fecha}</td>
                  </tr>";
            $i++;
        }
        ?>
    </table>
    <?php
    return ob_get_clean();
}
