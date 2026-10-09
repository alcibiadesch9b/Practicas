<?php

class Coche
{
    protected $color;

    public function setColor($color)
    {
        $this->color = $color;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function printCaracteristicas()
    {
        echo "Color: " . $this->color;
    }
}


class CocheDeLujo extends Coche
{
    protected $extras;

    public function setExtras($extras)
    {
        $this->extras = $extras;
    }

    public function getExtras()
    {
        return $this->extras;
    }

    public function printCaracteristicas()
    {
        echo "Color: " . $this->color;
        echo "<br><br>";
        echo "Extras: " . $this->extras;
    }
}


$miCoche = new CocheDeLujo();

$miCoche->setColor("Negro");
$miCoche->setExtras("TV");

$miCoche->printCaracteristicas();

?>