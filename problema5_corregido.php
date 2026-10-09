<?php
// problema5_corregido.php
// Registro de personas (Estudiantes y Docentes) usando herencia, polimorfismo y sesiones.
session_start();

/* ------------------------------------------------------------------
 * MODELO: clase base abstracta y sus dos hijas
 * ------------------------------------------------------------------ */
abstract class Persona
{
    protected $nombre;
    protected $apellido;
    protected $fechaNacimiento;
    protected $codigo;

    public function __construct($nombre, $apellido, $fechaNacimiento, $codigo)
    {
        $this->nombre          = $nombre;
        $this->apellido        = $apellido;
        $this->fechaNacimiento = $fechaNacimiento;
        $this->codigo          = $codigo;
    }

    // Cada hija declara su tipo y sus datos propios (polimorfismo)
    abstract public function getTipo();
    abstract protected function datosEspecificos();

    // Devuelve la ficha completa lista para mostrarse: [etiqueta => valor]
    public function aFicha()
    {
        return array_merge(
            [
                'Tipo'                => $this->getTipo(),
                'Nombre completo'     => $this->nombre . ' ' . $this->apellido,
                'Fecha de nacimiento' => $this->fechaNacimiento,
                'Código'              => $this->codigo,
            ],
            $this->datosEspecificos()
        );
    }
}

class Estudiante extends Persona
{
    private $indice;
    private $anioIngreso;
    private $estado;
    private $grado;
    private $modalidad;

    public function __construct($nombre, $apellido, $fechaNacimiento, $codigo,
                                $indice, $anioIngreso, $estado, $grado, $modalidad)
    {
        parent::__construct($nombre, $apellido, $fechaNacimiento, $codigo);
        $this->indice      = $indice;
        $this->anioIngreso = $anioIngreso;
        $this->estado      = $estado;
        $this->grado       = $grado;
        $this->modalidad   = $modalidad;
    }

    public function getTipo()
    {
        return 'Estudiante';
    }

    protected function datosEspecificos()
    {
        return [
            'Índice académico' => number_format((float) $this->indice, 2),
            'Año de ingreso'   => $this->anioIngreso,
            'Estado académico' => $this->estado,
            'Grado'            => $this->grado,
            'Modalidad'        => $this->modalidad,
        ];
    }
}

class Docente extends Persona
{
    private $departamento;
    private $categoria;
    private $titulo;
    private $contratacion;

    public function __construct($nombre, $apellido, $fechaNacimiento, $codigo,
                                $departamento, $categoria, $titulo, $contratacion)
    {
        parent::__construct($nombre, $apellido, $fechaNacimiento, $codigo);
        $this->departamento = $departamento;
        $this->categoria    = $categoria;
        $this->titulo       = $titulo;
        $this->contratacion = $contratacion;
    }

    public function getTipo()
    {
        return 'Docente';
    }

    protected function datosEspecificos()
    {
        return [
            'Departamento / Facultad' => $this->departamento,
            'Categoría'               => $this->categoria,
            'Máximo título'           => $this->titulo,
            'Contratación'            => $this->contratacion,
        ];
    }
}

/* ------------------------------------------------------------------
 * ALMACÉN: guarda las fichas en la sesión
 * ------------------------------------------------------------------ */
class Registro
{
    public static function agregar(Persona $p)
    {
        $_SESSION['registro'][] = $p->aFicha();
    }

    public static function todos()
    {
        return isset($_SESSION['registro']) ? $_SESSION['registro'] : [];
    }

    public static function vaciar()
    {
        $_SESSION['registro'] = [];
    }
}

/* ------------------------------------------------------------------
 * UTILIDADES
 * ------------------------------------------------------------------ */
// Escapa cualquier texto antes de imprimirlo (evita inyección de HTML/JS)
function e($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

// Lee un campo del formulario ya limpio de espacios
function campo($nombre)
{
    return isset($_POST[$nombre]) ? trim($_POST[$nombre]) : '';
}

// Valida los datos recibidos y devuelve la lista de errores encontrados
function validar()
{
    $errores = [];
    $tipo = campo('tipo');

    if (!in_array($tipo, ['Estudiante', 'Docente'], true)) {
        $errores[] = 'Seleccione el tipo de persona.';
    }
    foreach (['nombre' => 'El nombre', 'apellido' => 'El apellido', 'codigo' => 'El código'] as $k => $txt) {
        if (campo($k) === '') {
            $errores[] = $txt . ' es obligatorio.';
        }
    }

    $fecha = campo('fecha');
    $f = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$f || $f->format('Y-m-d') !== $fecha) {
        $errores[] = 'La fecha de nacimiento no es válida.';
    } elseif ($f > new DateTime('today')) {
        $errores[] = 'La fecha de nacimiento no puede ser futura.';
    }

    if ($tipo === 'Estudiante') {
        $indice = campo('indice');
        if (!is_numeric($indice) || $indice < 0 || $indice > 3) {
            $errores[] = 'El índice académico debe estar entre 0 y 3.';
        }
        $anio = campo('anio');
        if (!ctype_digit($anio) || $anio < 1950 || $anio > (int) date('Y')) {
            $errores[] = 'El año de ingreso no es válido.';
        }
        $grado = campo('grado');
        if (!ctype_digit($grado) || $grado < 1) {
            $errores[] = 'El grado debe ser un número positivo.';
        }
    }

    if ($tipo === 'Docente') {
        if (campo('departamento') === '') {
            $errores[] = 'El departamento o facultad es obligatorio.';
        }
        if (campo('titulo') === '') {
            $errores[] = 'El máximo título académico es obligatorio.';
        }
    }

    return $errores;
}

// Construye el objeto correcto según el tipo elegido
function crearPersona()
{
    if (campo('tipo') === 'Estudiante') {
        return new Estudiante(
            campo('nombre'), campo('apellido'), campo('fecha'), campo('codigo'),
            campo('indice'), campo('anio'), campo('estado'), campo('grado'), campo('modalidad')
        );
    }
    return new Docente(
        campo('nombre'), campo('apellido'), campo('fecha'), campo('codigo'),
        campo('departamento'), campo('categoria'), campo('titulo'), campo('contratacion')
    );
}

// Devuelve el valor previo de un campo para no perder lo escrito si hay errores
function previo($nombre)
{
    return e(campo($nombre));
}

// Marca como "selected" la opción que el usuario había elegido
function elegido($nombre, $valor)
{
    return campo($nombre) === $valor ? 'selected' : '';
}

/* ------------------------------------------------------------------
 * CONTROLADOR
 * ------------------------------------------------------------------ */
$errores = [];
$aviso   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = isset($_POST['accion']) ? $_POST['accion'] : 'guardar';

    if ($accion === 'vaciar') {
        Registro::vaciar();
        $_SESSION['aviso'] = 'Se vació la lista de personas.';
        header('Location: ' . basename($_SERVER['SCRIPT_NAME']));
        exit;
    }

    $errores = validar();
    if (empty($errores)) {
        Registro::agregar(crearPersona());
        $_SESSION['aviso'] = 'Persona registrada correctamente.';
        // Patrón Post/Redirect/Get: evita que F5 vuelva a enviar el formulario
        header('Location: ' . basename($_SERVER['SCRIPT_NAME']));
        exit;
    }
}

if (isset($_SESSION['aviso'])) {
    $aviso = $_SESSION['aviso'];
    unset($_SESSION['aviso']);
}

$personas = Registro::todos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Personas</title>
    <style>
        body      { font-family: Arial, sans-serif; background: #f2f4f8; margin: 0; padding: 24px; color: #222; }
        .contenedor { max-width: 760px; margin: 0 auto; }
        h1, h2    { color: #1f3a5f; }
        .tarjeta  { background: #fff; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 4px rgba(0,0,0,.12); }
        .fila     { display: flex; flex-direction: column; margin-bottom: 12px; }
        .fila label { font-weight: bold; margin-bottom: 4px; }
        input, select { padding: 8px; border: 1px solid #b8c0cc; border-radius: 4px; font-size: 14px; }
        fieldset  { border: 1px dashed #8aa0bd; border-radius: 6px; margin: 14px 0; padding: 12px; }
        legend    { color: #1f3a5f; font-weight: bold; }
        button    { padding: 9px 18px; border: 0; border-radius: 4px; cursor: pointer; font-size: 14px; color: #fff; background: #2d6cdf; }
        button.peligro { background: #c0392b; }
        .error    { background: #fdecea; border-left: 4px solid #c0392b; padding: 10px 14px; margin-bottom: 14px; }
        .ok       { background: #e8f6ec; border-left: 4px solid #2e8b57; padding: 10px 14px; margin-bottom: 14px; }
        .persona  { border: 1px solid #dde3ec; border-radius: 6px; padding: 12px; margin-bottom: 12px; }
        .persona.Estudiante { border-left: 5px solid #2d6cdf; }
        .persona.Docente    { border-left: 5px solid #e08e0b; }
        dl        { margin: 0; display: grid; grid-template-columns: 200px 1fr; row-gap: 4px; }
        dt        { font-weight: bold; }
        dd        { margin: 0; }
        @media (max-width: 520px) { dl { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="contenedor">

    <h1>Registro de Personas</h1>

    <div class="tarjeta">
        <?php if ($aviso !== ''): ?>
            <div class="ok"><?= e($aviso) ?></div>
        <?php endif; ?>

        <?php if (!empty($errores)): ?>
            <div class="error">
                <strong>Corrija lo siguiente:</strong>
                <ul>
                    <?php foreach ($errores as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="accion" value="guardar">

            <div class="fila">
                <label for="tipo">Tipo de persona</label>
                <select name="tipo" id="tipo" required>
                    <option value="">Seleccione...</option>
                    <option value="Estudiante" <?= elegido('tipo', 'Estudiante') ?>>Estudiante</option>
                    <option value="Docente" <?= elegido('tipo', 'Docente') ?>>Docente</option>
                </select>
            </div>

            <div class="fila">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="<?= previo('nombre') ?>" required>
            </div>

            <div class="fila">
                <label for="apellido">Apellido</label>
                <input type="text" id="apellido" name="apellido" value="<?= previo('apellido') ?>" required>
            </div>

            <div class="fila">
                <label for="fecha">Fecha de nacimiento</label>
                <input type="date" id="fecha" name="fecha" value="<?= previo('fecha') ?>" required>
            </div>

            <div class="fila">
                <label for="codigo">Código</label>
                <input type="text" id="codigo" name="codigo" value="<?= previo('codigo') ?>" required>
            </div>

            <!-- Datos propios del estudiante -->
            <fieldset data-seccion="Estudiante" hidden>
                <legend>Datos del estudiante</legend>

                <div class="fila">
                    <label for="indice">Índice académico (0 a 3)</label>
                    <input type="number" id="indice" name="indice" step="0.01" min="0" max="3"
                           value="<?= previo('indice') ?>" required>
                </div>
                <div class="fila">
                    <label for="anio">Año de ingreso</label>
                    <input type="number" id="anio" name="anio" min="1950" max="<?= date('Y') ?>"
                           value="<?= previo('anio') ?>" required>
                </div>
                <div class="fila">
                    <label for="estado">Estado académico</label>
                    <select id="estado" name="estado">
                        <option <?= elegido('estado', 'Activo') ?>>Activo</option>
                        <option <?= elegido('estado', 'Inactivo') ?>>Inactivo</option>
                        <option <?= elegido('estado', 'Retirado') ?>>Retirado</option>
                    </select>
                </div>
                <div class="fila">
                    <label for="grado">Grado</label>
                    <input type="number" id="grado" name="grado" min="1" value="<?= previo('grado') ?>" required>
                </div>
                <div class="fila">
                    <label for="modalidad">Modalidad</label>
                    <select id="modalidad" name="modalidad">
                        <option <?= elegido('modalidad', 'Presencial') ?>>Presencial</option>
                        <option <?= elegido('modalidad', 'Virtual') ?>>Virtual</option>
                        <option <?= elegido('modalidad', 'Híbrida') ?>>Híbrida</option>
                    </select>
                </div>
            </fieldset>

            <!-- Datos propios del docente -->
            <fieldset data-seccion="Docente" hidden>
                <legend>Datos del docente</legend>

                <div class="fila">
                    <label for="departamento">Departamento o Facultad</label>
                    <input type="text" id="departamento" name="departamento"
                           value="<?= previo('departamento') ?>" required>
                </div>
                <div class="fila">
                    <label for="categoria">Categoría</label>
                    <select id="categoria" name="categoria">
                        <option <?= elegido('categoria', 'Titular') ?>>Titular</option>
                        <option <?= elegido('categoria', 'Adjunto') ?>>Adjunto</option>
                        <option <?= elegido('categoria', 'Especial') ?>>Especial</option>
                        <option <?= elegido('categoria', 'Interino') ?>>Interino</option>
                    </select>
                </div>
                <div class="fila">
                    <label for="titulo">Máximo título académico</label>
                    <input type="text" id="titulo" name="titulo" value="<?= previo('titulo') ?>" required>
                </div>
                <div class="fila">
                    <label for="contratacion">Tipo de contratación</label>
                    <select id="contratacion" name="contratacion">
                        <option <?= elegido('contratacion', 'Tiempo Completo') ?>>Tiempo Completo</option>
                        <option <?= elegido('contratacion', 'Tiempo Parcial') ?>>Tiempo Parcial</option>
                        <option <?= elegido('contratacion', 'Por Horas') ?>>Por Horas</option>
                    </select>
                </div>
            </fieldset>

            <button type="submit">Guardar persona</button>
        </form>
    </div>

    <div class="tarjeta">
        <h2>Personas registradas (<?= count($personas) ?>)</h2>

        <?php if (empty($personas)): ?>
            <p>Todavía no hay personas registradas.</p>
        <?php else: ?>
            <?php foreach ($personas as $ficha): ?>
                <div class="persona <?= e($ficha['Tipo']) ?>">
                    <dl>
                        <?php foreach ($ficha as $etiqueta => $valor): ?>
                            <dt><?= e($etiqueta) ?></dt>
                            <dd><?= e($valor) ?></dd>
                        <?php endforeach; ?>
                    </dl>
                </div>
            <?php endforeach; ?>

            <form method="POST" action="">
                <input type="hidden" name="accion" value="vaciar">
                <button type="submit" class="peligro">Vaciar lista</button>
            </form>
        <?php endif; ?>
    </div>

</div>

<script>
    // Muestra solo la sección del tipo elegido y desactiva los campos de la otra,
    // así no se envían ni se exigen como obligatorios.
    const selectorTipo = document.getElementById('tipo');

    function actualizarSecciones() {
        document.querySelectorAll('[data-seccion]').forEach(function (seccion) {
            const activa = seccion.dataset.seccion === selectorTipo.value;
            seccion.hidden = !activa;
            seccion.querySelectorAll('input, select').forEach(function (campo) {
                campo.disabled = !activa;
            });
        });
    }

    selectorTipo.addEventListener('change', actualizarSecciones);
    actualizarSecciones();
</script>
</body>
</html>
