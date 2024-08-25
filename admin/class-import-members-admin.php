<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.linkedin.com/in/arr-dev
 * @since      1.0.0
 *
 * @package    Import_Members
 * @subpackage Import_Members/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Import_Members
 * @subpackage Import_Members/admin
 * @author     Performance Team <performanceboldt@gmail.com>
 */

use IMQuery\Import_Members_Queries;
use League\Csv\Reader;

class Import_Members_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */


	private $table_name;

	private $query_class;
	public function __construct($plugin_name, $version)
	{
		require_once plugin_dir_path(__DIR__) . 'vendor/autoload.php';

		global $wpdb;

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->query_class = Import_Members_Queries::get_instance();

		$this->table_name = $wpdb->prefix . 'promo_members';

		add_action('init', function () {
			$this->init_actions();
		});
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/import-members-admin.css', array(), $this->version, 'all');
		wp_enqueue_style('bootstrap', plugins_url('css/bootstrap.min.css', __FILE__), array(), null, 'all'); // Assuming no external dependencies
		wp_enqueue_style('normalize', plugins_url('css/normalize.css', __FILE__), array(), null, 'all'); // Assuming no external dependencies
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script('jquery', plugins_url('js/jquery.min.js', __FILE__), array(), null, true); // Enqueue jQuery first (recommended)
		wp_enqueue_script('jquery-ui', plugins_url('js/jquery-ui.min.js', __FILE__), array('jquery'), null, true);
		wp_enqueue_script('bootstrap-bundle', plugins_url('js/bootstrap.bundle.min.js', __FILE__), array('bootstrap'), null, true); // Assuming bootstrap.bundle.min.js depends on bootstrap.min.js
		wp_enqueue_script('bootstrap', plugins_url('js/bootstrap.min.js', __FILE__), array('jquery'), null, true);
		// wp_enqueue_script('chart', plugins_url('js/chart.umd.js', __FILE__), array(), null, true); // Assuming chart.umd.js is not dependent on jQuery
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/import-members-admin.js', array('jquery'), $this->version, false);
	}

	public function init_actions()
	{
		add_menu_page(
			__('Importar Socios', 'textdomain'),
			'Importar socios',
			'manage_options',
			'import-members',
			array($this, 'define_csv_option_page'),
			'dashicons-upload', // Icon 
			6
		);
	}



	public function define_csv_option_page()
	{
		$nonce = wp_create_nonce('upload_csv_file'); // Crea un nonce único para la acción 'upload_file'

?>
		<div class="wrap border border-1 rounded p-3 gap-2">
			<h2>Importar miembros</h2>
			<form method="post" enctype="multipart/form-data" class="row align-items-start justify-content-start p-3 gap-1">
				<?php wp_nonce_field('upload_csv_file', 'gf_csv_nonce'); ?>

				<div class="col-12 col-md-4">
					<p class="fs-4">Ingresar ID del Formulario:</p>
					<div class="input-group">
						<label for="formId" class="input-group-text">Form ID:</label>
						<input type="text" class="form-input" id="formId" name="gfimp-form-id" value="" placeholder="Ejemplo: 2" required>
					</div>
				</div>
				<div class="col-12 col-md-6">
					<p class="fs-4">Seleccionar archivo CSV:</p>
					<div class="input-group">
						<input type="file" name="archivo_csv" class="form-control" id="archivo_csv" accept=".csv">
						<label class="input-group-text" for="archivo_csv">Cargar CSV</label>
					</div>
					<div class="form-text bg-warning-subtle p-1 px-2" id="inpHelper">ATENCIÓN! Unicamente se acepta formato CSV (Delimitado por comas)</div>
					<?php submit_button('Cargar CSV'); ?>
				</div>
			</form>
			<?php
			?>
	<?php
		if ($_SERVER['REQUEST_METHOD'] === 'POST') :
			$this->impmbr_import_csv();
		endif;
	}

	public function impmbr_import_csv()
	{
		if (isset($_POST['nonce']) && !wp_verify_nonce($_POST['nonce'], 'upload_csv_file')) {
			echo '<div class="notice notice-error is-dismissible"><p>Error sospechoso al intentar cargar el archivo...</p></div>';
			wp_die();
		}

		if (isset($_FILES['archivo_csv']) && $_FILES['archivo_csv']['error'] === UPLOAD_ERR_OK) {
			global $wpdb;
			$file = $_FILES['archivo_csv'];

			// Existing validation checks

			try {
				$wpdb->query('START TRANSACTION'); // Begin transaction

				$filePath = $file['tmp_name'];
				$table = $this->table_name;

				// Sanitize form ID before inserting into the query
				$formId = isset($_POST['gfimp-form-id']) ? sanitize_text_field($_POST['gfimp-form-id']) : '';

				// $this->handle_csv_file($filePath, $table, $formId);
				$this->handle_csv_raw($filePath);

				$wpdb->query('COMMIT'); // Commit transaction if successful

				echo '<div class="notice notice-success is-dismissible"><p>CSV importado correctamente.</p></div>';
			} catch (Exception $e) {
				$wpdb->query('ROLLBACK'); // Roll back transaction on error
				echo '<div class="notice notice-error is-dismissible"><p>Error al importar el archivo CSV: ' . $e->getMessage() . '</p></div>';
			}
		}
	}

	public function handle_csv_raw($csv_file)
	{
		$handle = fopen($csv_file, 'r');

		if ($handle !== false) {
			// Leer la primera línea y eliminar el BOM si está presente
			$line = fgets($handle);
			$bom = "\xef\xbb\xbf";
	
			if (fgets($handle, 4) !== $bom) {
				rewind($handle);
			}
	
			return $handle;

			// Procesar la primera línea sin BOM
			$csv_data = str_getcsv($line);
			print_r($csv_data);

			// Procesar el resto del archivo
			while (($line = fgets($handle)) !== false) {
				$csv_data = str_getcsv($line);
				print_r($csv_data);
			}
			fclose($handle);
		}

		wp_die();
	}

	function handle_csv_file($csv_file, $table_name, $form_id)
	{
		global $wpdb;

		$batchSize = 50000; // Cantidad de registros por lote
		$data = []; // Array para el almacenamiento de los datos


		try {
			if (($handle = fopen($csv_file, "r")) === false) {
				throw new Exception("Error al abrir el archivo CSV: $csv_file");
			}

			// $csvFile = $this->openCsv($csv_file);

			// echo "<pre>";
			// var_dump($csvFile);
			// echo "</pre>";
			// Ignorar el encabezado del csv (Asumiendo que headers se encuentra en la primer linea)
			$headers = fgetcsv($handle);
			$startTime = time();

			$total_rows = 0;
			while (($row = fgetcsv($handle, 50, ',')) !== false) {
				//  Configurar el conjunto de caracteres a UTF-8 para evitar problemas de codificación
				// $row = [$this->set_utf8_line($row[0])];
				echo "<pre>";
				var_dump($row);
				echo "</pre>";
				// Preparar datos para la inserción
				$prepared_data = [];
				foreach ($row as $value) {
					$value = $this->handle_csv_fields($value);
					$prepared_data[] = $value; // Sanitize data antes de insertar
				}

				// Agregar datos a la matriz para la inserción
				$data[] = array($headers, $prepared_data);


				// Procesar los datos en lotes de 50000
				if (count($data) === $batchSize) {
					$this->insertBatchData($data, $table_name, $form_id);
					$data = []; // Limpiar array para la siguiente iteración
				}

				$total_rows++;
			}


			// Insert registros faltantes si los hay
			if (count($data) > 0) {
				$this->insertBatchData($data, $table_name, $form_id);
			}

			fclose($handle);
			echo 'Archivo CSV importado correctamente. <br>';
			$endTime = time();

			$elapsedTime = $endTime - $startTime;
			echo 'Tiempo total: ' . $elapsedTime . 'seg - Cantidad de filas importadas: ' . $total_rows;
		} catch (Exception $e) {
			echo 'Error: ' . $e->getMessage();
		}
	}
	/**
	 * Open CSV file and handle byte-order mark, if it exists
	 */
	private function openCsv($path)
	{
		// see https://www.php.net/manual/en/function.fgetcsv.php#122696
		$bom = "\xef\xbb\xbf";
		$fp = fopen($path, 'r');

		if (fgets($fp, 4) !== $bom) {
			rewind($fp);
		}

		return $fp;
	}

	private function insertBatchData($data, $table_name, $form_id)
	{
		global $wpdb;
		// Asignar array con el nombre de las columnas que tiene promo members table
		$columns = ['headers', 'fields', 'form_id'];

		// Preparar INSERT statement con placeholders 
		$placeholders = array_fill(0, count($columns), '%s');
		$sql = "INSERT INTO $table_name (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";

		foreach ($data as $row) {
			$headers = str_replace(';', ',', $row[0][0]);
			// $fields = str_replace(';', ',', $row[1][0]);   
			$fields = $row[1][0];

			// $wpdb->query($wpdb->prepare($sql, $headers, $fields, $form_id));
		}
	}

	public function set_utf8_line(string $line)
	{
		$encoded_array = [];

		if (is_string($line)) {
			$encoded_array = htmlspecialchars(mb_convert_encoding($line, "UTF-8", "ISO-8859-1"));
		} else {
			$encoded_array = $line;
		}

		return $encoded_array;
	}

	public function handle_value_encode($row)
	{
		$encoded_array = [];
		$formId = isset($_POST['gfimp-form-id']) ? sanitize_text_field($_POST['gfimp-form-id']) : '';

		if (is_string($row['headers']) && is_string($row['fields'])) {
			$encoded_array['headers'] = htmlspecialchars(mb_convert_encoding($row['headers'], "UTF-8", "ISO-8859-1"));
			$encoded_array['fields'] = htmlspecialchars(mb_convert_encoding($row['fields'], "UTF-8", "ISO-8859-1"));
			$encoded_array['form_id'] = $formId;
		} else {
			$encoded_array = $row;
		}

		return $encoded_array;
	}


	public function handle_csv_fields($row)
	{
		$dataRow = explode(';', $row);
		$incorrectData = false;
		foreach ($dataRow as &$field) {
			$field = sanitize_text_field($field);
			// $field = str_replace(',','-',$field); // Remplazar coma por guion si existe en el campo

			if ($field === '') {
				$field = 'N/A';
			}
		}
		$value = implode(',', $dataRow);

		// $duplicated_data = Import_Members_Queries::column_duplicated_data('fields', '23143184');
		// $duplicated_data = $this->query_class->column_duplicated_data('fields', $value);

		// if(!$duplicated_data){
		// 	return $value;
		// }


		// if($incorrectData)
		return $value;
	}
}
