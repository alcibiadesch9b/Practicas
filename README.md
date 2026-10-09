# Laboratorio de Programación Orientada a Objetos en PHP

Práctica de Herencia, Métodos Estáticos y Programación Orientada a Objetos — Desarrollo Web. Universidad Tecnológica de Panamá.

- **Profesora:** Ing. Irina Fong

## Descripción

Conjunto de cinco problemas en PHP que ponen en práctica los conceptos de programación orientada a objetos vistos en clase: herencia y sobrescritura de métodos, métodos estáticos con `self::`, clases `final`, propiedades privadas con constructor, y un sistema de registro de personas que usa clases abstractas, polimorfismo y sesiones, sin usar base de datos.

## Estructura de carpetas

```
Laboratorio-POO/
├── problema1.php          (Herencia: Coche y CocheDeLujo)
├── problema2.php          (Métodos estáticos con self::)
├── problema3.php          (Clase final que no puede heredarse)
├── problema4.php          (Clase Circulo: área y perímetro)
├── problema5.php          (Registro de Estudiantes y Docentes)
└── README.md
```

## Tecnologías usadas

- PHP 7.4 o superior
- HTML5 y CSS para el formulario del problema 5
- JavaScript básico para mostrar u ocultar los campos según el tipo de persona

## Funcionalidades implementadas

- **Problema 1:** la clase `CocheDeLujo` extiende a `Coche`, agrega la propiedad `$extras` y sobrescribe `printCaracteristicas()` para mostrar el color y los extras. Salida: `Color: Negro` y `Extras: TV`.
- **Problema 2:** `B::otraFuncion()` ejecuta un método de la clase `A` que llama a `self::miFuncion()`. Como `self::` siempre apunta a la clase donde se escribió el código, la salida es `A`. Con `static::` (Late Static Binding) sería `B`.
- **Problema 3:** `Coche` se declara como `final class`, por lo que `CocheDeLujo` no puede extenderla. PHP lanza un error fatal; ese error es intencional y demuestra el funcionamiento de `final`.
- **Problema 4:** la clase `Circulo` recibe el radio en el constructor (propiedad `private float`) y calcula el área (π × r²) y el perímetro (2 × π × r). Con radio 4: área 50.27 y perímetro 25.13.
- **Problema 5:** registro de personas con una clase base abstracta `Persona` y dos clases hijas:
  - `Estudiante`: índice académico, año de ingreso, estado académico, grado y modalidad.
  - `Docente`: departamento o facultad, categoría, máximo título académico y tipo de contratación.
  - Los datos se guardan en la sesión (`$_SESSION`) mediante la clase `Registro` y se muestran en una lista.
  - El formulario muestra solo los campos del tipo de persona elegido.
  - Valida en el servidor: campos obligatorios, fecha de nacimiento válida y no futura, índice entre 0 y 3, año de ingreso y grado.
  - Escapa todo el texto con `htmlspecialchars()` para prevenir XSS.
  - Redirige después de guardar (Post/Redirect/Get) para que recargar la página no reenvíe el formulario.
  - Incluye un botón para vaciar la lista.

## Cómo ejecutarlo

1. Copia la carpeta `Laboratorio-POO/` dentro de tu servidor local (ej. `C:\wamp64\www\`).
2. Inicia WAMP/XAMPP.
3. Abre en el navegador, por ejemplo, `http://localhost/Laboratorio-POO/problema5.php`.
4. Los problemas 1 al 4 también se pueden ejecutar desde la terminal con `php problema1.php`.
