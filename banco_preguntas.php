<?php
// Banco de preguntas del Árbol de Habilidades.
// Para agregar otro curso: copia el bloque 'MAT-01' con el código del curso (tabla cursos)
// y completa 12 preguntas por rama (3 por cada nivel 1-4; el examen del nivel 5 usa 10 al azar).
// Formato de pregunta: st_q('Enunciado', ['op1','op2','op3','op4'], índice_correcto_empezando_en_0)

const ST_APROBACION = 60; // % mínimo de aciertos para pasar un nivel
require_once __DIR__ . '/banco_preguntas_extra.php';

// Ramas por tema de un curso (en el orden del banco) y todas las ramas (con las rojas)
function st_negras(array $banco): array
{
    return array_keys($banco['ramas']);
}

function st_todas(array $banco): array
{
    return array_merge(array_keys($banco['ramas']), ['desafio', 'canje']);
}

function st_q(string $p, array $o, int $c): array
{
    return ['p' => $p, 'o' => $o, 'c' => $c];
}

function st_banco(string $codigo): ?array
{
    static $bancos = null;
    if ($bancos === null) {
        $bancos = [
            'MAT-01' => [
                'ramas' => [
                    'algebra' => [
                        'nombre' => 'Álgebra',
                        'consejos' => [
                            1 => 'Consejo: la ecuación es una balanza; lo que haces de un lado debes hacerlo del otro. Dato curioso: «álgebra» viene del árabe al-jabr, que significa «restauración».',
                            2 => 'Consejo: simplifica ambos lados antes de despejar x. Dato curioso: el signo «=» lo propuso Robert Recorde en 1557 porque nada es más igual que dos rectas paralelas.',
                            3 => 'Consejo: al factorizar, busca primero un factor común. Dato curioso: los babilonios ya resolvían ecuaciones cuadráticas hace unos 4000 años.',
                            4 => 'Consejo: en un sistema de ecuaciones, sumar o restar las ecuaciones elimina una variable. Dato curioso: Descartes popularizó el uso de x, y, z para las incógnitas.',
                        ],
                        'preguntas' => [
                            st_q('Resuelve: x + 7 = 12', ['3', '5', '7', '19'], 1),
                            st_q('¿Cuánto vale 3x si x = 4?', ['7', '12', '34', '43'], 1),
                            st_q('Simplifica: 2x + 3x', ['5x', '6x', '5x²', '6x²'], 0),
                            st_q('Resuelve: 2x − 6 = 10', ['2', '4', '8', '16'], 2),
                            st_q('Si a = 2 y b = 3, ¿cuánto vale a² + b?', ['5', '7', '8', '10'], 1),
                            st_q('Desarrolla: (x + 1)²', ['x² + 1', 'x² + 2x + 1', 'x² + x + 1', '2x + 1'], 1),
                            st_q('Factoriza: x² − 9', ['(x − 3)²', '(x + 3)(x − 3)', '(x − 9)(x + 1)', '(x + 9)(x − 1)'], 1),
                            st_q('Resuelve: 3(x − 2) = 12', ['2', '4', '6', '8'], 2),
                            st_q('¿Cuáles son las raíces de x² − 5x + 6 = 0?', ['1 y 6', '2 y 3', '−2 y −3', '5 y 1'], 1),
                            st_q('Resuelve el sistema: x + y = 10 ; x − y = 2', ['x = 6, y = 4', 'x = 8, y = 2', 'x = 5, y = 5', 'x = 7, y = 3'], 0),
                            st_q('¿Cuál es la solución de |x| = 4?', ['Solo 4', '4 y −4', 'Solo −4', '0'], 1),
                            st_q('Si 2ˣ = 32, ¿cuánto vale x?', ['4', '5', '6', '16'], 1),
                        ],
                    ],
                    'calculo' => [
                        'nombre' => 'Cálculo',
                        'consejos' => [
                            1 => 'Consejo: un límite dice a qué valor se acerca una función, no necesariamente su valor en el punto. Dato curioso: Newton y Leibniz desarrollaron el cálculo de forma independiente en el siglo XVII.',
                            2 => 'Consejo: la derivada de xⁿ es n·xⁿ⁻¹: baja el exponente y réstale 1. Dato curioso: la derivada mide la velocidad instantánea, lo que marca el velocímetro de un auto.',
                            3 => 'Consejo: la derivada de una suma es la suma de las derivadas. Dato curioso: la notación dy/dx es de Leibniz y se sigue usando hoy.',
                            4 => 'Consejo: la integral es la operación inversa de la derivada; no olvides la constante C. Dato curioso: el símbolo ∫ es una «S» alargada, de «summa» (suma).',
                        ],
                        'preguntas' => [
                            st_q('¿Cuál es la derivada de f(x) = x²?', ['x', '2x', 'x²', '2'], 1),
                            st_q('¿Cuál es la derivada de una constante, como 5?', ['5', '1', '0', 'x'], 2),
                            st_q('Calcula el límite de (x + 3) cuando x → 2', ['2', '3', '5', '∞'], 2),
                            st_q('¿Cuál es la derivada de f(x) = 3x?', ['3x', '3', 'x', '0'], 1),
                            st_q('¿Cuál es la derivada de f(x) = x³?', ['x²', '3x²', '3x³', 'x⁴/4'], 1),
                            st_q('Calcula el límite de (x² + 1) cuando x → 0', ['0', '1', '∞', 'No existe'], 1),
                            st_q('¿Cuál es la derivada de 5x² − 2x?', ['10x − 2', '5x − 2', '10x² − 2', '5x² − 2'], 0),
                            st_q('Calcula: ∫ 2x dx', ['x² + C', '2x² + C', 'x + C', '2 + C'], 0),
                            st_q('¿Cuál es la pendiente de la recta tangente a y = x² en x = 3?', ['3', '6', '9', '2'], 1),
                            st_q('Calcula: ∫ 1 dx', ['0', 'x + C', '1 + C', '1/x'], 1),
                            st_q('Calcula el límite de 1/x cuando x → ∞', ['∞', '1', '0', 'No existe'], 2),
                            st_q('¿Cuál es la derivada de sen(x)?', ['−cos(x)', 'cos(x)', '−sen(x)', 'tan(x)'], 1),
                        ],
                    ],
                    'geometria' => [
                        'nombre' => 'Geometría',
                        'consejos' => [
                            1 => 'Consejo: dibuja siempre la figura antes de resolver. Dato curioso: «geometría» significa «medida de la tierra»; nació para medir terrenos.',
                            2 => 'Consejo: el perímetro mide el contorno; el área, la superficie (en unidades al cuadrado). Dato curioso: las abejas usan hexágonos en sus panales porque aprovechan mejor el espacio.',
                            3 => 'Consejo: memoriza el trío 3-4-5; aparece en muchos ejercicios. Dato curioso: se cuenta que los agrimensores egipcios usaban cuerdas con nudos para formar ángulos rectos.',
                            4 => 'Consejo: π es la razón entre la circunferencia y su diámetro en cualquier círculo. Dato curioso: el 14 de marzo (3/14) se celebra el Día de π.',
                        ],
                        'preguntas' => [
                            st_q('Los ángulos internos de un triángulo suman:', ['90°', '180°', '270°', '360°'], 1),
                            st_q('¿Cuál es el área de un rectángulo de 5 por 4?', ['9', '18', '20', '40'], 2),
                            st_q('¿Cuántos lados tiene un hexágono?', ['5', '6', '7', '8'], 1),
                            st_q('¿Cuál es el perímetro de un cuadrado de lado 7?', ['14', '21', '28', '49'], 2),
                            st_q('¿Cuál es el área de un triángulo de base 10 y altura 6?', ['16', '30', '60', '36'], 1),
                            st_q('En un triángulo rectángulo con catetos 3 y 4, la hipotenusa mide:', ['5', '6', '7', '√7'], 0),
                            st_q('¿Cuál es el área de un círculo de radio 3?', ['6π', '9π', '3π', '18π'], 1),
                            st_q('Los ángulos internos de un cuadrilátero suman:', ['180°', '270°', '360°', '540°'], 2),
                            st_q('¿Cuál es la longitud de una circunferencia de radio 5?', ['5π', '10π', '25π', '20π'], 1),
                            st_q('¿Cuál es el volumen de un cubo de arista 3?', ['9', '18', '27', '81'], 2),
                            st_q('Dos ángulos complementarios suman:', ['90°', '180°', '270°', '360°'], 0),
                            st_q('¿Cuánto mide la diagonal de un cuadrado de lado 1?', ['1', '√2', '√3', '2'], 1),
                        ],
                    ],
                    'aritmetica' => [
                        'nombre' => 'Aritmética',
                        'consejos' => [
                            1 => 'Consejo: respeta el orden de las operaciones: paréntesis, potencias, × y ÷, luego + y −. Dato curioso: el sistema decimal nació en la India y lo difundieron los árabes.',
                            2 => 'Consejo: las cifras de los primeros múltiplos de 9 siempre suman 9. Dato curioso: los quipus incas registraban cantidades con nudos en un sistema decimal.',
                            3 => 'Consejo: para el MCD toma los factores primos comunes de menor exponente; para el MCM, todos con mayor exponente. Dato curioso: Euclides describió el algoritmo del MCD hace más de 2000 años.',
                            4 => 'Consejo: un porcentaje es una fracción sobre 100; 25 % es 1/4. Dato curioso: el 2 es el único número primo par.',
                        ],
                        'preguntas' => [
                            st_q('15 + 27 =', ['41', '42', '43', '52'], 1),
                            st_q('8 × 7 =', ['54', '56', '58', '63'], 1),
                            st_q('¿Cuál es el MCD de 12 y 18?', ['2', '3', '6', '36'], 2),
                            st_q('¿Cuál es el MCM de 4 y 6?', ['2', '12', '24', '10'], 1),
                            st_q('¿Cuánto es el 25 % de 80?', ['15', '20', '25', '40'], 1),
                            st_q('3/4 + 1/4 =', ['1/2', '1', '4/8', '3/16'], 1),
                            st_q('√144 =', ['11', '12', '14', '72'], 1),
                            st_q('¿Cuál de estos números es primo?', ['15', '21', '17', '27'], 2),
                            st_q('2³ × 2² =', ['16', '32', '64', '128'], 1),
                            st_q('3 cuadernos cuestan S/ 12. ¿Cuánto cuestan 5?', ['S/ 15', 'S/ 18', 'S/ 20', 'S/ 24'], 2),
                            st_q('0.5 expresado como fracción es:', ['1/5', '1/2', '5/100', '2/1'], 1),
                            st_q('7 − (−3) =', ['4', '10', '−4', '−10'], 1),
                        ],
                    ],
                ],
            ],
        ];
        $bancos = $bancos + st_banco_extra(); // Comunicación, Ciencia y Tecnología, Sociales, Inglés
    }
    return $bancos[$codigo] ?? null;
}

// Mezcla todas las preguntas de todas las ramas (para Desafío Supremo y Canje) y devuelve $n.
function st_sacar(array $banco, int $n): array
{
    $pool = [];
    foreach ($banco['ramas'] as $r) {
        foreach ($r['preguntas'] as $q) {
            $pool[] = $q;
        }
    }
    shuffle($pool);
    return array_slice($pool, 0, $n);
}
