<?php

// Aquí pones la contraseña que quieres hashear
$contrasena_plana = "100001";

$hash_generado = password_hash($contrasena_plana, PASSWORD_DEFAULT);

echo "Tu contraseña plana es: " . $contrasena_plana . "<br>";
echo "Pega este HASH en tu base de datos:";
echo "<hr>";
echo "<strong>" . $hash_generado . "</strong>";

?>