<?php

class Circulo
{
    private float $radio;

    public function __construct(float $radio)
    {
        $this->radio = $radio;
    }

    public function calcularArea()
    {
        return M_PI * ($this->radio * $this->radio);
    }

    public function calcularPerimetro()
    {
        return 2 * M_PI * $this->radio;
    }
}

$miCirculo = new Circulo(4);

echo "Área del círculo: " . number_format($miCirculo->calcularArea(), 2,".",",");
echo "<br>";
echo "Perímetro del círculo: " . number_format($miCirculo->calcularPerimetro(), 2,".",",");

?>