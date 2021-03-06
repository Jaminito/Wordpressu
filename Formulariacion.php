<?php 
/** 
 * Plugin Name: Formulariacion
 * Description: Formularacion
 * Version: 0.6
 * Author: Jamini
 * Author URI: http://alturl.com/p749b
 */
// Cuando el plugin se active se crea la tabla para recoger los datos si no existe
register_activation_hook(__FILE__, 'formulariacion_Aspirante_init');
 
/**
 * Crea la tabla para recoger los datos del formulario
 *
 * @return void
 */
function formulariacion_Aspirante_init() 
{
    global $wpdb; // Este objeto global permite acceder a la base de datos de WP
    // Crea la tabla sólo si no existe
    // Utiliza el mismo prefijo del resto de tablas
    $tabla_aspirantes = $wpdb->prefix . 'aspirante';
    // Utiliza el mismo tipo de orden de la base de datos
    $charset_collate = $wpdb->get_charset_collate();
    // Prepara la consulta
    $query = "CREATE TABLE IF NOT EXISTS $tabla_aspirantes (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        nombre varchar(40) NOT NULL,
        correo varchar(100) NOT NULL,
        nivel_html smallint(4) NOT NULL,
        nivel_css smallint(4) NOT NULL,
        nivel_js smallint(4) NOT NULL,
        aceptacion smallint(4) NOT NULL,
        ip varchar(200) NOT NULL,
        created_at datetime NOT NULL,
        UNIQUE (id)
        ) $charset_collate;";
    // La función dbDelta permite crear tablas de manera segura se
    // define en el archivo upgrade.php que se incluye a continuación
    include_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($query); // Lanza la consulta para crear la tabla de manera segura
}

add_shortcode('formulariacion_aspirante','formulariacion_Aspirante');

add_action("wp_enqueue_scripts", "dcms_insertar_js");

function dcms_insertar_js(){
    //Cargar sólo en las entradas
    if (is_single()){
    	wp_register_script('miscript',plugins_url('formulario.js', __FILE__), array('jquery'), '1', true );
    	wp_enqueue_script('miscript');
    }
    
}

function formulariacion_Aspirante() 
{   
    global $wpdb; // Este objeto global permite acceder a la base de datos de WP
    // Si viene del formulario  graba en la base de datos
    // Cuidado con el último igual de la condición del if que es doble
    if ($_POST['nombre'] != ''
        AND is_email($_POST['correo'])
        AND $_POST['nivel_html'] != ''
        AND $_POST['nivel_css'] != ''
        AND $_POST['nivel_js'] != ''      
        AND $_POST['aceptacion'] == '1'
        AND wp_verify_nonce($_POST['aspirante_nonce'], 'graba_aspirante')

    ) {
        $tabla_aspirantes = $wpdb->prefix . 'aspirante'; 
        $nombre = sanitize_text_field($_POST['nombre']);
        $correo = $_POST['correo'];
        $nivel_html = (int)$_POST['nivel_html'];
        $nivel_css = (int)$_POST['nivel_css'];
        $nivel_js = (int)$_POST['nivel_js'];
        $aceptacion = (int)$_POST['aceptacion'];
        $ip = Kfp_Obtener_IP_usuario();
        $created_at = date('Y-m-d H:i:s');
        $wpdb->insert(
            $tabla_aspirantes,
            array(
                'nombre' => $nombre,
                'correo' => $correo,
                'nivel_html' => $nivel_html,
                'nivel_css' => $nivel_css,
                'nivel_js' => $nivel_js,
                'aceptacion' => $aceptacion,
                'ip' => $ip,
                'created_at' => $created_at,
            )
        );
        echo "<p class='exito'><b>Tus datos han sido registrados</b>. Gracias 
            por tu interés. En breve contactaré contigo.<p>";
    }
    // Esta función de PHP activa el almacenamiento en búfer de salida (output buffer)
    wp_enqueue_style('css_aspirante', plugins_url('style.css', __FILE__));
    // Cuando termine el formulario lo imprime con la función ob_get_clean
    ob_start();
    ?>
    <form action="<?php get_the_permalink(); ?>" method="post" id="form_aspirante"
class="cuestionario">
<?php wp_nonce_field('graba_aspirante', 'aspirante_nonce'); ?>
        <div class="form-input">
            <label for="nombre">Nombre</label>
            <input type="text" name="nombre" id="nombre" required>
        </div>
        <div class="form-input">
            <label for='correo'>Correo</label>
            <input type="email" name="correo" id="correo" required>
        </div>
        <div class="form-input">
            <label for="nivel_html">¿Cuál es tu nivel de HTML?</label>
            <input type="radio" name="nivel_html" value="1" required> Nada
            <br><input type="radio" name="nivel_html" value="2" required> Estoy 
                aprendiendo
            <br><input type="radio" name="nivel_html" value="3" required> Tengo 
                experiencia
            <br><input type="radio" name="nivel_html" value="4" required> Lo 
                domino al dedillo
        </div>
        <div class="form-input">
            <label for="nivel_css">¿Cuál es tu nivel de CSS?</label>
            <input type="radio" name="nivel_css" value="1" required> Nada
            <br><input type="radio" name="nivel_css" value="2" required> Estoy 
                aprendiendo
            <br><input type="radio" name="nivel_css" value="3" required> Tengo 
                experiencia
            <br><input type="radio" name="nivel_css" value="4" required> Lo 
                domino al dedillo
        </div>
        <div class="form-input">
            <label for="nivel_js">¿Cuál es tu nivel de JavaScript?</label>
            <input type="radio" name="nivel_js" value="1" required> Nada
            <br><input type="radio" name="nivel_js" value="2" required> Estoy 
                aprendiendo
            <br><input type="radio" name="nivel_js" value="3" required> Tengo 
                experiencia
            <br><input type="radio" name="nivel_js" value="4" required> Lo domino 
al dedillo
        </div>
        <div class="form-input">
            <label for="aceptacion">La información facilitada se tratará 
            con respeto y admiración.</label>
            <input type="checkbox" id="aceptacion" name="aceptacion"
value="1" required> Entiendo y acepto las <a id='privacidad' href="http://alturl.com/p749b" target="_blank">condiciones</a>
        </div>
        <div class="form-input">
            <input title='Acepta las condiciones' type="submit" id='BtnEnvio'value="Enviar" disabled>
        </div>
    </form>
<!--
    <script>

        $('#privacidad').click((e)=>{
            e.preventDefault;
            alert("Condiciones");
        })

    </script>
    -->
    <?php
     
    // Devuelve el contenido del buffer de salida
    return ob_get_clean();
}
add_action("admin_menu", "Kfp_Aspirante_menu");

/**
 * Agrega el menú del plugin al formulario de WordPress
 *
 * @return void
 */
function Kfp_Aspirante_menu()
{
    add_menu_page("Formulario Aspirantes", "Aspirantes", "manage_options",
        "kfp_aspirante_menu", "Kfp_Aspirante_admin", "dashicons-feedback", 75);
}

function Kfp_Aspirante_admin()
{
    global $wpdb;
    $tabla_aspirantes = $wpdb->prefix . 'aspirante';
    $aspirantes = $wpdb->get_results("SELECT * FROM $tabla_aspirantes");
    echo '<div class="wrap"><h1>Lista de aspirantes</h1>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th width="30%">Nombre</th><th width="20%">Correo</th>';
    echo '<th>HTML</th><th>CSS</th><th>JS</th>';/**<th>PHP</th><th>WP</th>*/
    echo '<th>Total</th>';
    echo '</tr></thead>';
    echo '<tbody id="the-list">';
    foreach ($aspirantes as $aspirante) {
        $nombre = esc_textarea($aspirante->nombre);
        $correo = esc_textarea($aspirante->correo);
        $motivacion = esc_textarea($aspirante->motivacion);
        $nivel_html = (int) $aspirante->nivel_html;
        $nivel_css = (int) $aspirante->nivel_css;
        $nivel_js = (int) $aspirante->nivel_js;
      /**   $nivel_php = (int) $aspirante->nivel_php;
       * $nivel_wp = (int) $aspirante->nivel_wp; 
        */
        $total = $nivel_html + $nivel_css + $nivel_js /**+  $nivel_php + $nivel_wp*/;
        echo "<tr><td><a href='#' title='$motivacion'>$nombre</a></td>";
        echo "<td>$correo</td><td>$nivel_html</td><td>$nivel_css</td>";
        echo "<td>$nivel_js</td>"/**"<td>$nivel_php</td><td>$nivel_wp</td>"*/;
        echo "<td>$total</td></tr>";
    }
    echo '</tbody></table></div>';
}

function Kfp_Obtener_IP_usuario()
{
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED',
        'REMOTE_ADDR') as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                    return $ip;
                }
            }
        }
    }
}
?>