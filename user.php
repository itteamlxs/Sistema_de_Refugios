<xaiArtifact artifact_id="d7a85a0c-f52a-4fdf-b15d-563df8e255f6" artifact_version_id="566726b4-69a6-4a87-b5f8-7edeebaa5c7d" title="hash_password.php" contentType="text/php">
<?php
$password = 'Temporal2025#';
$hashed = password_hash($password, PASSWORD_DEFAULT);
echo "Contraseña hasheada: $hashed";
?> 